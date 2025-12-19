<!DOCTYPE html>
<html>

<head>
    <title>SALES INVOICE</title>
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
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <script type="text/javascript" src="layouts/v7/lib/jquery/jquery.min.js"></script>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
        <script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script>


        <ul style="list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background-color: #333;">

            {if $SELECTED_BANK}
                <li style="float:right">
                    <a style="display: block;color: white;text-align: center;padding: 14px 16px;text-decoration: none;background-color: #bea364;"
                        href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo}&tableName={$smarty.request.tableName}&PDFDownload=true&bank={$SELECTED_BANK->getId()}{if $INTENT}&fromIntent={$smarty.request.fromIntent}&hideCustomerInfo={$smarty.request.hideCustomerInfo}{/if}">Download</a>
                </li>
            {/if}
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
    {assign var="start" value=0}
    {assign var="end" value=1}
    {assign var="calcTotal" value=0}
    {assign var="SUB_TOTAL" value=0}
    {for $page=1 to $PAGES}
        {if $page eq 1}
            {assign var="end" value=6}
        {else}
            {assign var="end" value=($end+6)}
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

                            <strong>{if isset($COMPANY) && !empty($COMPANY->get('company_gst_no'))}TAX INVOICE
                                {else}SALES
                                INVOICE{/if}</strong>
                        </td>
                    </tr>
                    {if isset($COMPANY) && !empty($COMPANY->get('company_gst_no'))}
                        <tr style="font-weight: bold;font-size: 9pt">
                            <td>GST Reg No.: {$COMPANY->get('company_gst_no')}</td>
                        </tr>
                    {/if}
                    <tr>
                        <td style="text-align: right;font-size: 9pt">
                            All amounts in {$ERP_DOCUMENT->currency}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 9pt; height: 168mm; vertical-align: top;">
                            <table class="activity-tbl" style="margin-bottom:5mm">
                                <tr>
                                    <th colspan="2" style="width:25%;text-align:center">INVOICE NO</th>
                                    <th style="width:25%;text-align:center">INVOICE DATE</th>
                                    <th style="width:25%;text-align:center">DELIVERY DATE</th>
                                    <th style="width:25%;text-align:center">ORDER</th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:center">{$smarty.request.docNo}</td>
                                    <td style="text-align:center">{$ERP_DOCUMENT->documentDate}</td>
                                    <td style="text-align:center">{$ERP_DOCUMENT->postingDate}</td>
                                    <td style="text-align:center">Purchase & Delivery</td>
                                </tr>
                            </table>
                            <table class="activity-tbl">
                                <tr>
                                    <th style="width:10%;">QTY</th>
                                    <th style="width:40%;">DESCRIPTION</th>
                                    <th style="width:13%;text-align:center">{$ERP_DOCUMENT->currency}/UNIT</th>
                                    <th style="width:12%;text-align:center">FINE OZ.</th>
                                    <th style="width:30%;text-align:center">TOTAL {$ERP_DOCUMENT->currency}</th>
                                </tr>
                                {assign var="metalPrice" value=($ERP_DOCUMENT->xauPrice)+($ERP_DOCUMENT->mbtcPrice)+($ERP_DOCUMENT->xagPrice)+($ERP_DOCUMENT->xptPrice)+($ERP_DOCUMENT->xpdPrice)}
                                {assign var="balanceAmount" value=($balanceAmount)+($TRANSACTION->usdVal)}
                                {assign var="serials" value=""}
                                {assign var="GST_ITEM" value=false}
                                {for $loopStart=$start to $end}

                                    {assign var="barItem" value=$ERP_DOCUMENT->barItems[$loopStart]}
                                    {assign var="start" value=($loopStart+1)}

                                    {if $barItem->quantity eq 1}
                                        {assign var="serialPart" value=explode('-',$barItem->serials[0])}
                                        {assign var="serials" value=$serials|cat:$serialPart[0]|cat:', '}
                                    {else}
                                        {assign var="serials" value=$serials|cat:$barItem->serials|cat:', '}
                                    {/if}
                                    {* (metalPrice x pureOz) + othercharge *}
                                    {assign var="total" value=$barItem->totalItemAmount}
                                    {assign var="calcTotal" value=$calcTotal+round($total,2)}
                                    {assign var="SUB_TOTAL" value=$SUB_TOTAL+round($total,2)}
                                    {if $loopStart eq count($ERP_DOCUMENT->barItems)}
                                        {break}
                                    {/if}
                                    {if empty($barItem->quantity) and empty($barItem->longDesc)}
                                        {if strpos($barItem->narration,'GST') === 0 ||strpos($barItem->narration,'GST') > 0 }
                                            {assign var="GST_ITEM" value=$barItem}
                                        {else}
                                            <tr>
                                                <td style="vertical-align: top"></td>
                                                <td style="border-bottom:none;vertical-align: top">Storage Charge</td>
                                                <td style="text-align:right;vertical-align: top"></td>
                                                <td style="text-align:right;vertical-align: top"></td>
                                                <td style="text-align:right;vertical-align: top">{number_format($total,2)}</td>
                                            </tr>
                                        {/if}
                                    {else}
                                        <tr>
                                            <td style="vertical-align: top">{$barItem->quantity}</td>
                                            <td style="border-bottom:none;vertical-align: top">
                                                {$barItem->itemDescription} <br><span
                                                    style="font-size: smaller;font-style: italic;">{$barItem->serials[0]}</span>
                                            </td>

                                            <td style="text-align:right;vertical-align: top">
                                                {$barItem->unitPrice}
                                            </td>

                                            <td style="text-align:right;vertical-align: top">
                                                {number_format($barItem->totalFineOz,3)}
                                            </td>

                                            <td style="text-align:right;vertical-align: top">
                                                {number_format($barItem->totalItemAmount,3)}
                                            </td>
                                        </tr>
                                    {/if}
                                {/for}

                                {if $PAGES eq $page}
                                    {if $GST_ITEM}
                                        <tr>
                                            <td style="width:75%;" colspan="4">SUBTOTAL:</td>
                                            <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                                    {number_format($SUB_TOTAL,2)}</strong></td>
                                        </tr>
                                        <tr>
                                            <td style="width:75%;" colspan="4">GST on Storage charge in Singapore (7%)</td>
                                            <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                                    {number_format($GST_ITEM->otherCharge,2)}</strong></td>
                                        </tr>
                                    {/if}
                                    {if isset($COMPANY) && !empty($COMPANY->get('company_gst_no')) && empty($GST_ITEM)}
                                        <tr>
                                            <td style="width:75%;" colspan="4">SUBTOTAL:</td>
                                            <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                                    {number_format($SUB_TOTAL,2)}</strong></td>
                                        </tr>
                                        <tr>
                                            <td style="width:75%;" colspan="4">GST on Storage charge (0%)</td>
                                            <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                                    {number_format(0,2)}</strong></td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <th style="width:75%;" colspan="4">TOTAL INVOICE AMOUNT:</th>
                                        <td style="text-align:right"><strong>{$ERP_DOCUMENT->currency}
                                                {number_format($calcTotal ,2)}</strong></td>
                                    </tr>
                                    {if $INTENT}
                                        <tr>
                                            <th style="width:75%;" colspan="4">TOTAL INVOICE AMOUNT IN
                                                {$INTENT->get('package_currency')}: {if $INTENT->get('package_currency') eq 'EUR'}
                                                (EUR/USD{else}(USD/{$INTENT->get('package_currency')}
                                                {/if} RATE
                                                {$INTENT->get('fx_spot_price')})</th>
                                            <td style="text-align:right"><strong>{$INTENT->get('package_currency')}
                                                    {number_format($INTENT->get('total_foreign_amount'),2)}</strong></td>
                                        </tr>
                                    {/if}
                                {/if}

                            </table>
                            <br>
                            <br>
                            {if isset($COMPANY) && !empty($COMPANY->get('company_gst_no'))}
                                <div>
                                    {if $INTENT && $INTENT->get('package_currency') eq 'SGD'}
                                        *Remarks: USD/SGD exchange rate at SGD {$INTENT->get('fx_spot_price')} / USD
                                    {else}
                                        *Remarks: USD/SGD exchange rate at SGD
                                        {$ERP_DOCUMENT->exchange_rates} / USD
                                    {/if}
                                </div>
                            {/if}
                            <br>
                            <br>
                            {if $SELECTED_BANK}
                                {assign var=iban value=$SELECTED_BANK->get('iban_no')|lower|replace:' ':''}
                                {assign var=bank_routing_no value=$SELECTED_BANK->get('bank_routing_no')|lower|replace:' ':''}

                                <div>
                                    Please transfer the payment net of charges to our bank account:<br>
                                    Beneficiary: {$SELECTED_BANK->get('beneficiary_name')}<br>
                                    Account No: {$SELECTED_BANK->get('account_no')}
                                    {$SELECTED_BANK->get('account_currency')}<br>

                                    {if $iban neq 'x'}
                                        IBAN: {$SELECTED_BANK->get('iban_no')}<br>
                                    {/if}

                                    Bank: {$SELECTED_BANK->get('bank_name')}<br>
                                    Bank Address: {$SELECTED_BANK->get('bank_address')}<br>
                                    Swift Code: {$SELECTED_BANK->get('swift_code')}<br>

                                    {if $bank_routing_no neq 'x'}
                                        Bank Code: {$SELECTED_BANK->get('bank_code')}<br>
                                        Branch Code: {$SELECTED_BANK->get('branch_code')}<br>
                                    {else}
                                        Routing No: {$SELECTED_BANK->get('bank_routing_no')}<br>
                                    {/if}

                                    <br><br>

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
    {/for}
</body>

</html>