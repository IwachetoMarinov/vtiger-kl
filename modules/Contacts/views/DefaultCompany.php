<?php

class CompanyFallbackModel
{
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? '';
    }

    public function getData(): array
    {
        return $this->data;
    }
}

class Contacts_DefaultCompany_View
{
    public static function process()
    {
        $adb = PearDatabase::getInstance();
        $res = $adb->pquery('SELECT * FROM vtiger_organizationdetails LIMIT 1', []);
        $org = $adb->fetchByAssoc($res) ?: [];

        $companyDetails = new CompanyFallbackModel([
            'company_name'     => $org['organizationname'] ?? '',
            'company_reg_no'   => $org['registration_number'] ?? '',
            'company_address'  => $org['address'] ?? '',
            'company_phone'    => $org['phone'] ?? '',
            'company_fax'      => $org['fax'] ?? '',
            'company_website'  => $org['website'] ?? '',
            'vat_id'           => $org['vatid'] ?? '',
            'company_email'    => $org['email1'] ?? '',
        ]);

        return $companyDetails;
    }
}
