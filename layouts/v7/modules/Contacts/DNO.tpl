<!DOCTYPE html>
<html>

<head>
    <title>DEBIT NOTE {$smarty.request.docNo}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            src: local('Open Sans'), local('OpenSans'), url(https://themes.googleusercontent.com/static/fonts/opensans/v6/cJZKeOuBrn4kERxqtaUH3T8E0i7KZn-EPnyo3HZu7kw.woff) format('woff');
        }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 700;
            src: local('Open Sans Bold'), local('OpenSans-Bold'), url(https://themes.googleusercontent.com/static/fonts/opensans/v6/k3k702ZOKiLJc3WVjuplzHhCUOGz7vYGh680lGh-uXM.woff) format('woff');
        }

        * {
            box-sizing: border-box;
            margin: 0px;
        }

        .printAreaContainer {
            height: 297mm;
            width: 210mm;
            border: 1px solid #fff;
            margin: auto;
            padding: 15mm 15mm;
            position: relative;
        }

        .printAreaContainer * {
            box-sizing: border-box;
            font-family: 'Open Sans';
            color: #666;

        }

        .printAreaContainer .full-width {
            width: 100%;
        }

        .printAreaContainer .header-logo {
            width: 50%;
            height: 11mm;
        }

        .printAreaContainer .header-text {
            width: 50%;
            height: 11mm;
            color: #fff;
            background: #008ECA;
            font-weight: bold;
            font-size: 10pt;
        }

        .printAreaContainer .header-text span {
            font-weight: normal;
            color: #fff;
        }

        .printAreaContainer .print-tbl {
            border-collapse: collapse;
            width: 100%;
            border: none;
        }

        .print-txt-center {
            text-align: center;
        }

        .print-txt-left {
            text-align: left;
        }

        .print-txt-right {
            text-align: right;
        }

        .print-footer {
            height: 20mm;
            background: #008ECA;
        }

        .big-label {
            color: #cd3330;
            font-weight: bold;
            font-size: 20pt;
        }

        .hidden {
            /*display: none;*/
        }

        table.content-table th {
            border: 1px dotted #666666;
            font-size: 10pt;
            background: #ECECEC;
            font-weight: bold;
            padding: 4px
        }

        table.content-table tr.footer th {
            color: #008ECA;
        }

        table.content-table td {
            border: none;
            font-size: 10pt;
            font-weight: normal;
            padding: 4px
        }

        table.graph-table td {
            width: 50%;
            font-size: 10pt;
            padding: 4px 0px;
        }

        .graph-bar {
            height: 1.5mm
        }

        .graph-bar.blue {
            background: #008ECA;
        }

        .graph-bar.green {
            background: #BACE10;
        }

        .doted-bg {
            background: url(graph_bg.jpg);
            background-size: 338px auto;
        }

        table.activity-tbl {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #333;
        }

        table.activity-tbl td,
        table.activity-tbl th {
            border: 1px solid #000;
            padding: 1mm 2mm;
            text-align: left;
        }

        table.activity-tbl th {
            background: #bca263;
        }

        .logo-bg-bottom {
            background: url(layouts/v7/modules/Contacts/resources/Gold_Logo_Higher_Res.png) no-repeat;
            background-size: 244mm;
            height: 70mm;
            width: 82mm;
            position: absolute;
            bottom: 0px;
            right: 0px;
            opacity: .4;
        }
    </style>
</head>

<body>
    {if $smarty.request.PDFDownload neq true}
        <script type="text/javascript" src="layouts/v7/lib/jquery/jquery.min.js"></script>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
        <script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script>
        <ul style="list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background-color: #333;">
            <li style="float:right">
                <a style="display: block;color: white;text-align: center;padding: 14px 16px;text-decoration: none;background-color: #bea364;"
                    href="index.php?module=Contacts&view=NotePrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo}&tableName={$smarty.request.tableName}&PDFDownload=true&bank={$SELECTED_BANK->getId()}&hideCustomerInfo={$smarty.request.hideCustomerInfo}">Download</a>
            </li>
            <li style="float: right;margin-top: 5px;margin-right: 5px;width: 198px;">
                <select class="inputElement select2" name="bank_accounts" id="bank_accounts">
                    <option value="">Select Bank Account</option>
                    {foreach item=account from=$ALL_BANK_ACCOUNTS}
                        <option {if $smarty.request.bank  eq $account->getId() } selected {/if} value="{$account->getId()}">
                            {$account->get('bank_alias_name')}</option>
                    {/foreach}
                </select>
            </li>
        </ul>
        {literal}
            <style>
                .select2-container .select2-choice>.select2-chosen {
                    width: 171px;
                }
            </style>
            <script>
                $(document).ready(function() {
                    $('.select2').select2();
                });
                jQuery("body").on('change', '#bank_accounts', function(e) {
                    var element = jQuery(e.currentTarget);
                    var bankId = Number(element.val());
                    window.location.replace(window.location.href.split('&bank=')[0] + '&bank=' + bankId);
                });
            </script>
        {/literal}
    {/if}
    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">
                <tr>
                    <td style="height: 28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height: 100%; float:right;width: 154px;">
                        <div style="font-size:11pt;margin-top: 14px;margin-bottom: 32px">
                            {$RECORD_MODEL->get('cf_898')}<br>

                            {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}<br>
                            {if !empty($RECORD_MODEL->get('cf_968'))} {$RECORD_MODEL->get('cf_968')}<br>{/if}
                            {if !empty($RECORD_MODEL->get('mailingstreet'))}
                            {$RECORD_MODEL->get('mailingstreet')}<br>{/if}
                            {if !empty($RECORD_MODEL->get('cf_970'))} {$RECORD_MODEL->get('cf_970')}<br>{/if}
                            {if empty($RECORD_MODEL->get('mailingpobox'))}
                                {if !empty($RECORD_MODEL->get('mailingcity')) && !empty($RECORD_MODEL->get('mailingzip')) }
                                    {$RECORD_MODEL->get('mailingcity')} {$RECORD_MODEL->get('mailingzip')}<br>
                                {else if !empty($RECORD_MODEL->get('mailingcity'))}
                                    {$RECORD_MODEL->get('mailingcity')}<br>
                                {else}
                                    {$RECORD_MODEL->get('mailingzip')}<br>
                                {/if}
                                {$RECORD_MODEL->get('mailingcountry')}
                            {else}
                                {if !empty($RECORD_MODEL->get('mailingcity'))}
                                    P.O. Box {$RECORD_MODEL->get('mailingpobox')}, {$RECORD_MODEL->get('mailingcity')}<br>
                                {else}
                                    P.O. Box {$RECORD_MODEL->get('mailingpobox')}<br>
                                {/if}
                                {if !empty($RECORD_MODEL->get('mailingstate'))}
                                    {$RECORD_MODEL->get('mailingstate')}, {$RECORD_MODEL->get('mailingcountry')}
                                {else}
                                    {$RECORD_MODEL->get('mailingcountry')}
                                {/if}
                            {/if}

                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height: 10mm; text-decoration: underline;text-align: center">
                        <strong>DEBIT NOTE</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 9pt;vertical-align: top;">
                        <table class="activity-tbl" style="margin-bottom:1mm;width:40%;margin-top:2mm;">
                            <tr>
                                <th style="text-align:center;width:50%;">DEBIT NOTE NO</th>
                                <th style="text-align:center;width:50%;">DATE</th>
                            </tr>
                            <tr>
                                <td style="text-align:center">{$smarty.request.docNo}</td>
                                <td style="text-align:center">{$ERP_DOCUMENT->documentDate}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        All amounts in {$ERP_DOCUMENT->currency}
                    </td>
                </tr>
                <tr>
                    <td style="width:25%;font-size: 9pt; height: 138mm; vertical-align: top;">
                        {assign var="description" value=$ERP_DOCUMENT->barItems[0]->description|default:""}

                        <table class="activity-tbl">
                            <tr>
                                <th style="width:70%;">DESCRIPTION</th>
                                <th style="width:30%;text-align:center">TOTAL {$ERP_DOCUMENT->currency}</th>
                            </tr>
                            <tr>
                                <td style="height: 30mm;border-bottom:none;vertical-align: top">
                                    {$description}
                                </td>
                                <td style="text-align:right;vertical-align: top">
                                    {CurrencyField::convertToUserFormat($ERP_DOCUMENT->grandTotal)}
                                </td>
                            </tr>
                            <tr>
                                <th>TOTAL DEBIT AMOUNT:</th>
                                <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                        {number_format($ERP_DOCUMENT->grandTotal, 2, '.', ',')}
                                    </strong>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        {if $SELECTED_BANK}
                            {if isset($SELECTED_BANK) && $SELECTED_BANK && method_exists($SELECTED_BANK, 'getId')}
                                <input type="hidden" class="selected-bank" value="{$SELECTED_BANK->getId()}">
                            {/if}
                            <div>
                                Please transfer the payment net of charges to our bank account:<br>
                                Beneficiary: {$SELECTED_BANK->get('beneficiary_name')}<br>
                                Account No: {$SELECTED_BANK->get('account_no')}
                                {$SELECTED_BANK->get('account_currency')}<br>
                                {if trim(count_chars(strtolower($SELECTED_BANK->get('iban_no')),3)) != 'x'}
                                    IBAN: {$SELECTED_BANK->get('iban_no')}<br>
                                {/if}
                                Bank: {$SELECTED_BANK->get('bank_name')}<br>
                                Bank Address: {$SELECTED_BANK->get('bank_address')}<br>
                                Swift Code: {$SELECTED_BANK->get('swift_code')}<br>
                                {if trim(count_chars(strtolower($SELECTED_BANK->get('bank_routing_no')),3)) == 'x'}
                                    Bank Code: {$SELECTED_BANK->get('bank_code')}<br>
                                    Branch Code: {$SELECTED_BANK->get('branch_code')}<br>
                                {else}
                                    Routing No: {$SELECTED_BANK->get('bank_routing_no')}<br>
                                {/if}
                                <br>
                                <br>
                                {if !empty($SELECTED_BANK->get('intermediary_bank'))}
                                    Intermediary Bank: {$SELECTED_BANK->get('intermediary_bank')}<br>
                                    Swift Code: {$SELECTED_BANK->get('intermediary_swift_code')}<br>
                                {/if}
                            </div>
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td style='font-size: 8pt;font-weight: bold;position: absolute;bottom: 14px;'>
                        {if isset($COMPANY)}
                            {$COMPANY->get('company_name')} {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg. No.
                            {$COMPANY->get('company_reg_no')}){/if}<br>
                            {$COMPANY->get('company_address')}<br>
                            T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                            {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                        {/if}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>