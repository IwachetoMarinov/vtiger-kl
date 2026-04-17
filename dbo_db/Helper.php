<?php

class Helper
{
    public static function getCompanyFullAddress($company)
    {
        $parts = [];

        if ($company->get('company_address')) {
            $parts[] = $company->get('company_address');
        }

        if ($company->get('city')) {
            $parts[] = $company->get('city');
        }

        if ($company->get('state')) {
            $parts[] = $company->get('state');
        }

        if ($company->get('code')) {
            $parts[] = $company->get('code');
        }

        if ($company->get('country')) {
            $parts[] = $company->get('country');
        }

        return implode(', ', $parts);
    }

    public static function getCompanyFullAddressWithoutCommas($company)
    {
        $parts = [];

        if ($company->get('company_address')) {
            $parts[] = $company->get('company_address');
        }

        if ($company->get('city')) {
            $parts[] = $company->get('city');
        }

        if ($company->get('state')) {
            $parts[] = $company->get('state');
        }

        if ($company->get('code')) {
            $parts[] = $company->get('code');
        }

        if ($company->get('country')) {
            $parts[] = $company->get('country');
        }

        return implode(" ", $parts);
    }
}
