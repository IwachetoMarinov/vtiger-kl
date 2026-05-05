<?php
/* modules/Contacts/cron/StatementOfHoldingsService.php */

include_once 'CronHelpers.php';
include_once 'dbo_db/Helper.php';
require_once 'data/CRMEntity.php';
include_once 'dbo_db/HoldingsDB.php';
require_once 'modules/Documents/Documents.php';

class Contacts_StatementOfHoldingsService
{
    public function __construct() {}

    public function processClient(string $client_id, array $date_range)
    {
        // Init HoldingsDB
        $holding = new dbo_db\HoldingsDB();
        $start_date = !empty($date_range) ? $date_range[0] : date('Y-m-01');
        $end_date = !empty($date_range) ? $date_range[1] : date('Y-m-t');

        if (Contacts_CronHelpers::ytdReportExists(
            $client_id,
            $start_date,
            $end_date,
            'Statement of Holdings'
        )) {
            echo "Statement of Holdings already exists for client {$client_id}, period {$start_date} to {$end_date}\n";
            return 0;
        }

        // 2. Fetch Statement of Holdings data for the client and date range
        $holdings = $this->fetchHoldings($client_id, $date_range, $holding);

        echo "Fetched ->>>>>>>>>>>" . count($holdings) . " holdings for client ID $client_id\n";

        if (!is_array($holdings) || count($holdings) === 0) return;

        // 3. Get metals for every holding in the date range
        $metals = $holding->getHoldingsMetalsByDateRange($client_id, $date_range[0], $date_range[1]);

        // 4. Calculate total value of holdings based on metal prices and quantities
        $total = $this->calculateSpotTotal($holdings);

        // 5 Build LBMA date from the first holding record (assuming all records have the same spot date) and format it as 'd-M-y'
        $LBMA_DATE = isset($holdings[0]['spot_date']) && is_array($holdings) ? date('d-M-y', strtotime($holdings[0]['spot_date'])) : '';

        // 6. Group holdings by location and prepare data for storage
        $grouped_holdings = $this->groupHoldingsByLocation($holdings);

        // 7. Get Contact record model (used for template rendering)
        $contactRecord = Contacts_CronHelpers::getContactRecordByClientId($client_id);

        // 8. Get company information (used in PDF header)
        $company_record = Contacts_DefaultCompany_View::process();

        // 9. Initialize Smarty template engine
        $smarty = new Smarty();
        $smarty->setCompileDir(dirname(__DIR__, 3) . '/test/templates_c/');
        $smarty->setCacheDir(dirname(__DIR__, 3) . '/test/cache/');
        $smarty->setConfigDir(dirname(__DIR__, 3) . '/test/config/');

        // 10. Register custom template resolver for vTiger templates
        $templateRoot = dirname(__DIR__, 3) . '/layouts/v7/modules';
        $smarty->registerPlugin('modifier', 'vtemplate_path', function ($templateName, $moduleName) use ($templateRoot) {
            return $templateRoot . '/' . $moduleName . '/' . $templateName;
        });

        // 11. Assign all variables required by the template
        $ROOT_DIRECTORY = getenv('ROOT_DIRECTORY') ?: ($ROOT_DIRECTORY ?? null);
        $smarty->assign('ROOT_DIRECTORY', $ROOT_DIRECTORY);
        $smarty->assign('RECORD_MODEL', $contactRecord);
        $smarty->assign('LBMA_DATE', $LBMA_DATE);
        $smarty->assign('TOTAL', $total);
        $smarty->assign('METALS', $metals);
        $smarty->assign('ERP_HOLDINGS', $grouped_holdings);
        $smarty->assign('ERP_HOLDINGMETALS', $holdings);
        $smarty->assign('COMPANY', $company_record);
        // $smarty->assign('COMPANY_FULL_ADDRESS', $company_full_address);

        // 12. Render HTML from Smarty template
        $templatePath = dirname(__DIR__, 3) . '/layouts/v7/modules/Contacts/HoldingPrintPreview.tpl';
        $html = $smarty->fetch('file:' . $templatePath);

        // echo $html;

        // 13. Generate PDF from HTML and store it in Documents module
        $pdfPath = Contacts_CronHelpers::generatePdf($html, $client_id, $date_range, 'Monthly Statement of Holdings - %s - %s%s');

        // 16. If PDF generation failed → stop here
        if (!file_exists($pdfPath)) return;

        // 17. Store generated PDF in vTiger Documents module
        $selected_year = date('Y', strtotime($date_range[0]));
        $holdingsDocId = Contacts_CronHelpers::storePdfInDocuments($pdfPath, $client_id, $selected_year, "USD", 'Statement of Holdings - %s - %s to %s');
        Contacts_CronHelpers::createYTDReportRecord(
            $client_id,
            $start_date,
            $end_date,
            $holdingsDocId,
            'Statement of Holdings'
        );

        // 18. Log the generated report in vtiger_ytdreports_log table
        Contacts_CronHelpers::logYTDReportHoldings(
            $client_id,
            $start_date,
            $end_date,
            $holdingsDocId
        );

        // 19. Cleanup generated PDF file
        if (file_exists($pdfPath)) unlink($pdfPath);
    }

    private function groupHoldingsByLocation(array $holdings): array
    {
        $grouped = [];
        foreach ($holdings as $item) {
            $location = $item['location'];

            $grouped[$location][] = (object) [
                'metal' => $item['description'] ?? '',
                'location' => $item['location'] ?? '',
                'serials' => $item['serial_no'] ?? '',
                'pureOz' => isset($item['fine_oz']) ? (float) $item['fine_oz'] : 0.00,
                'total' => isset($item['total']) ? (float) $item['total'] : 0.00,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                'spot_price' => isset($item['spot_price']) ? (int) $item['spot_price'] : 0,
                'longDesc' => $item['description'],
            ];
        }
        return $grouped;
    }

    private function fetchHoldings(string $client_id, array $date_range, dbo_db\HoldingsDB $holdings)
    {
        $holdings_data = $holdings->getHoldingsByDateRange($client_id, $date_range[0], $date_range[1]);

        return $holdings_data;
    }

    protected function calculateSpotTotal(array $holdings_data)
    {
        $total = 0.00;
        foreach ($holdings_data as $item) {
            $total += isset($item['total']) ? (float) $item['total'] : 0.00;
        }
        return $total;
    }
}
