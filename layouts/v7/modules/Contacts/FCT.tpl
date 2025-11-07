<!DOCTYPE html>
<html>

<head>
    <title>FOREX CONFIRMATION {$smarty.request.docNo} </title>
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
            margin: 0px
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
        <ul style="list-style-type: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background-color: #333;">
            <li style="float:right"><a style="display: block;
                color: white;
                text-align: center;
                padding: 14px 16px;
                text-decoration: none;
                background-color: #bea364;"
                    href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo}">Download</a>
            </li>
        </ul>
    {/if}
    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">
                <tr>
                    <td style="height: 28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height: 100%; float:right;width: 154px;">
                        <div style="font-size: 11pt;margin-top: 14px;margin-bottom: 32px">
                            {$RECORD_MODEL->get('cf_950')}<br>
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
                        <strong>FOREX CONFIRMATION</strong>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                    </td>
                </tr>
                <tr>
                    {assign var="metalPrice" value=($OROSOFT_DOCUMENT->barItems[0]->price)}
                    <td style="font-size: 9pt; height: 168mm; vertical-align: top;">
                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th colspan="2" style="width:25%;text-align:center">TRANSACTION ID</th>
                                <th style="width:25%;text-align:center">TRANSACTION DATE</th>
                                <th style="width:25%;text-align:center">VALUE DATE</th>
                                <th style="width:25%;text-align:center">TRANSACTION TYPE</th>
                            </tr>
                            <tr>
                                <td colspan="2" style='text-align:center;'>{$smarty.request.docNo}</td>
                                <td style='text-align:center;'>{$OROSOFT_DOCUMENT->documentDate}</td>
                                <td style='text-align:center;'>{$OROSOFT_DOCUMENT->deliveryDate}</td>
                                <td style='text-align:center;'>FX Spot</td>
                            </tr>
                        </table>
                        <table class="activity-tbl">
                            <tr>
                                <th style="width:50%;">DESCRIPTION</th>
                                <th style="width:25%;text-align:center">CURRENCY</th>
                                <th style="width:25%;text-align:center">AMOUNT</th>
                            </tr>
                            {foreach item=barItem from=$OROSOFT_DOCUMENT->barItems}
                                {assign var="metal" value="-"|explode:$barItem->metal}
                                {assign var="spotPrice" value=$barItem->price}
                                {if $metal[0] eq 'USD'}
                                    {assign var="FIRST_PRICE" value=$OROSOFT_DOCUMENT->totalusdVal}
                                    {assign var="SECOND_PRICE" value=$OROSOFT_DOCUMENT->totalusdVal*$spotPrice}
                                {else}
                                    {assign var="FIRST_PRICE" value=$OROSOFT_DOCUMENT->totalusdVal/$spotPrice}
                                    {assign var="SECOND_PRICE" value=$OROSOFT_DOCUMENT->totalusdVal}
                                {/if}
                                <tr>
                                    <td style="border-bottom:none;vertical-align: top">
                                        We {strtolower($OROSOFT_DOCUMENT->direction)}:<br><br>
                                        We {if strtolower($OROSOFT_DOCUMENT->direction) eq 'sell'}buy:{else}sell:{/if}
                                        <br><br>
                                    </td>
                                    <td style="text-align:right;vertical-align: top">
                                        {$metal[0]}<br><br>
                                        {$metal[1]}
                                    </td>
                                    <td style="text-align:right;vertical-align: top">
                                        {number_format($FIRST_PRICE,2)}<br><br>
                                        {number_format($SECOND_PRICE,2)}
                                    </td>
                                </tr>
                            {/foreach}
                            <tr>
                                <td colspan="3"><b>FX details:</b><br>Spot Rate ({$metal[0]}/{$metal[1]}) : {$spotPrice}
                                </td>
                            </tr>
                        </table>
                        <br>
                        <div>
                            If you have any questions concerning these transactions, please contact
                            {$COMPANY->get('company_name')} at <br>Tel: {$COMPANY->get('company_phone')} or by email:
                            relationship@global-precious-metals.com.
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