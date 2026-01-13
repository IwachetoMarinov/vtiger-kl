<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once 'dbo_db/ActivitySummary.php';
include_once 'dbo_db/HoldingsDB.php';

class Contacts_TCPrintPreview_View extends Vtiger_Index_View
{

    protected $record = null;

    public function preProcess(Vtiger_Request $request, $display = false)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        // Getting model to reuse it in parent
        if (!$this->record)  $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
    }

    public function process(Vtiger_Request $request)
    {
        include_once 'modules/Contacts/models/MetalsAPI.php';
        $docNo = $request->get('docNo');
        $tableName = $request->get('tableName');
        $docType = substr($docNo, 0, 3);
        $moduleName = $request->getModule();
        $recordModel = $this->record->getRecord();
        $companyId = $recordModel->get('company_id');

        $companyRecord = null;

        if (!empty($companyId))
            $companyRecord = Vtiger_Record_Model::getInstanceById($companyId, 'GPMCompany');

        $activity = new dbo_db\ActivitySummary();
        $activity_data = $activity->getDocumentPrintPreviewData($docNo, $tableName);

        // echo "<pre>";
        // print_r($activity_data);
        // echo "</pre>";

        $erpDoc = (object) $activity_data;
        $pages = $this->makeDataPages($erpDoc->barItems);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('ERP_DOCUMENT', $erpDoc);
        $viewer->assign('OROSOFT_DOCTYPE', $docType);
        $viewer->assign('HIDE_BP_INFO', $request->get('hideCustomerInfo'));
        $viewer->assign('COMPANY', $companyRecord);
        $viewer->assign('PAGES', $pages);
        $viewer->assign('PAGE_COUNT', count($pages));

        if ($request->get('PDFDownload')) {
            $html = $viewer->view("TC.tpl", $moduleName, true);
            $this->downloadPDF($html, $request);
        } else {
            $viewer->view("TC.tpl", $moduleName);
        }
    }

    function makeDataPages($barItems)
    {
        // Tunable budgets (in "row units")
        $firstPageBudget = 14;
        $nextPageBudget  = 14;

        // How many characters roughly fit in one wrapped line in your DESCRIPTION column.
        // (Adjust if needed; 55–75 is typical depending on font/column width)
        $charsPerLine = 65;

        // Every extra wrapped line consumes extra "row units"
        // 1 unit = normal row height. Each extra line adds 1 unit.
        $pages = [];
        $budget = $firstPageBudget;
        $used = 0;
        $countOnPage = 0;

        foreach ($barItems as $item) {
            $desc  = isset($item->itemDescription) ? $item->itemDescription : '';
            // use serialNumbers if you have it, otherwise join serials
            $serialText = '';
            if (!empty($item->serialNumbers)) {
                $serialText = $item->serialNumbers;
            } elseif (!empty($item->serials) && is_array($item->serials)) {
                $serialText = implode(', ', $item->serials);
            }

            $text = trim($desc . ' ' . $serialText);
            $len = mb_strlen($text);

            // Estimate wrapped lines in the DESCRIPTION cell
            $lines = max(1, (int)ceil($len / $charsPerLine));

            // Convert lines -> "row units":
            // normal rows ~ 1 unit; each extra wrapped line adds 1 unit
            $units = 1 + ($lines - 1);

            // If this item doesn't fit on current page, close page and start a new one
            if ($countOnPage > 0 && ($used + $units) > $budget) {
                $pages[] = $countOnPage;
                $budget = $nextPageBudget;
                $used = 0;
                $countOnPage = 0;
            }

            $used += $units;
            $countOnPage++;
        }

        // last page
        if ($countOnPage > 0)  $pages[] = $countOnPage;

        return $pages;
    }


    function makeDataPage($transaction)
    {
        $totalPage = 1;
        $recordCount = 15;
        if (count($transaction) > $recordCount) {
            $totaldataAfterFirstPage = count($transaction) - $recordCount;
            $totalPage = ceil($totaldataAfterFirstPage / $recordCount) + 1;
        }
        return $totalPage;
    }

    public function postProcess(Vtiger_Request $request) {}

    function downloadPDF($html, Vtiger_Request $request)
    {
        global $root_directory;
        $recordModel = $this->record->getRecord();
        $clientID = $recordModel->get('cf_898');

        $fileName = $clientID . '-' . str_replace('/', '-', $request->get('docNo')) . "-TC";
        $handle = fopen($root_directory . $fileName . '.html', 'a') or die('Cannot open file:  ');
        fwrite($handle, $html);
        fclose($handle);

        exec("wkhtmltopdf --enable-local-file-access  -L 0 -R 0 -B 0 -T 0 --disable-smart-shrinking " . $root_directory . "$fileName.html " . $root_directory . "$fileName.pdf");
        unlink($root_directory . $fileName . '.html');

        header("Content-type: application/pdf");
        header("Cache-Control: private");
        header("Content-Disposition: attachment; filename=$fileName.pdf");
        header("Content-Description: Global Precious Metals CRM Data");
        ob_clean();
        flush();
        readfile($root_directory . "$fileName.pdf");
        unlink($root_directory . "$fileName.pdf");
        exit;
    }
}
