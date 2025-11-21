<!DOCTYPE html>
<html>

<head>
    <title>STORAGE INVOICE</title>
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
            background: #bca264;
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
                    href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo}&PDFDownload=true&bank={$SELECTED_BANK->getId()}&hideCustomerInfo={$smarty.request.hideCustomerInfo}">Download</a>
            </li>
            <li id='printConf' style="float:right">
                <span style="float: right;margin-right: 1px;color: white;background-color: #bea364;text-decoration: none;
                display: block;
                text-align: center;
                padding: 14px;cursor: pointer;">Settings</span>
            </li>
        </ul>
        <script type="text/javascript" src="layouts/v7/modules/Contacts/resources/PrintConf.js"></script>
        {include file='printConf.tpl'|vtemplate_path:'Contacts'}

    {/if}
    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">
                <tr>
                    <td style="height: 28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height: 100%; float:right;width: 154px;">
                        <div style="font-size:11pt;margin-top: 14px;margin-bottom: 32px;">
                            {$RECORD_MODEL->get('cf_898')}<br>
                            {if !$HIDE_BP_INFO}
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
                            {/if}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height: 10mm; text-decoration: underline;text-align: center">
                        <strong>{if !empty($COMPANY->get('company_gst_no'))}TAX INVOICE
                            {else}STORAGE
                            INVOICE{/if}</strong>
                    </td>
                </tr>
                {if !empty($COMPANY->get('company_gst_no'))}
                    <tr style="font-weight: bold;font-size: 9pt">
                        <td>GST Reg No.: {$COMPANY->get('company_gst_no')}</td>
                    </tr>
                {/if}
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        All amounts in US Dollars
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 9pt; height: 168mm; vertical-align: top;">
                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th colspan="2" style="width:25%;text-align: center;">INVOICE NO</th>
                                <th style="width:25%;text-align: center;">DATE</th>
                                <th colspan="2" style="width:50%;text-align: center;">AVERAGE LONDON FIX</th>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">{$OROSOFT_DOCUMENT['docno']}</td>
                                <td style="text-align: center;">{$OROSOFT_DOCUMENT['docDate']}</td>
                                <td style="width:25%;text-align: center;">{$OROSOFT_DOCUMENT['metal']}</td>
                                <td style="width:25%;text-align: center;">US$ {$OROSOFT_DOCUMENT['metalOz']} / Oz.</td>
                            </tr>
                        </table>
                        <table class="activity-tbl">
                            <tr>
                                <th style="width:75%;">DESCRIPTION</th>
                                <th style="width:25%;text-align: center">TOTAL US$</th>
                            </tr>
                            <tr>
                                <td style="height:50mm;border-bottom:none;vertical-align: top;line-height: 2">Storage
                                    charge for {$OROSOFT_DOCUMENT['metal']} for the period from
                                    {$OROSOFT_DOCUMENT['fromDate']} to {$OROSOFT_DOCUMENT['toDate']}:<br>
                                    {foreach from=$OROSOFT_DOCUMENT['priceDate']['charge'] item=charge}
                                        &nbsp;&nbsp;&nbsp;&nbsp;- {$charge['label']}<br>
                                    {/foreach}
                                </td>
                                <td style="text-align:right;vertical-align: top;line-height: 2"><br>
                                    {foreach from=$OROSOFT_DOCUMENT['priceDate']['charge'] item=charge}
                                        {number_format($charge['amount'],2)}<br>
                                    {/foreach}
                                </td>
                            </tr>
                            {if $OROSOFT_DOCUMENT['GST']}
                                <tr>
                                    <td style="width:75%;">SUBTOTAL:</td>
                                    <td style="text-align:right"><strong>US$
                                            {number_format($OROSOFT_DOCUMENT['priceDate']['subtotal'],2)}</strong></td>
                                </tr>
                                <tr>
                                    <td style="width:75%;">{$OROSOFT_DOCUMENT['priceDate']['GST']['label']}</td>
                                    <td style="text-align:right"><strong>US$
                                            {number_format($OROSOFT_DOCUMENT['priceDate']['GST']['amount'],2)}</strong></td>
                                </tr>
                            {/if}
                            {if !empty($COMPANY->get('company_gst_no')) && empty($OROSOFT_DOCUMENT['GST'])}
                                <tr>
                                    <td style="width:75%;">SUBTOTAL:</td>
                                    <td style="text-align:right"><strong>US$ {$OROSOFT_DOCUMENT['amount']}</strong></td>
                                </tr>
                                <tr>
                                    <td style="width:75%;">GST on Storage charge (0%)</td>
                                    <td style="text-align:right"><strong>US$ {number_format(0,2)}</strong></td>
                                </tr>
                            {/if}
                            <tr>
                                <th style="width:75%;">TOTAL STORAGE FEE:</th>
                                <td style="text-align:right"><strong>US$ {$OROSOFT_DOCUMENT['amount']}</strong></td>
                            </tr>
                        </table>

                        <br>
                        <br>
                        {if !empty($COMPANY->get('company_gst_no'))}
                            <div>
                                *Remarks: USD/SGD exchange rate at SGD
                                {MASForex_Record_Model::getExchangeRate($OROSOFT_DOCUMENT['rawDocDate'], 'usd_sgd')} / USD
                            </div>
                        {/if}
                        <br>
                        <br>
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
                    </td>
                </tr>
                <tr>
                    <td style='font-size: 8pt;font-weight: bold;position: absolute;bottom: 14px;'>
                        {$COMPANY->get('company_name')} {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg. No.
                        {$COMPANY->get('company_reg_no')}){/if}<br>
                        {$COMPANY->get('company_address')}<br>
                        T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                        {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
