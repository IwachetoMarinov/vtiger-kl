<!DOCTYPE html>
<html>

<head>
    <title>STATEMENT OF HOLDING AND VALUATION</title>
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

        table.activity-tbl tr.no-border td {
            border-bottom: none;
            border-top: none;
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
    {if $ENABLE_DOWNLOAD_BUTTON}
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
                    href="index.php?module=Contacts&view=HoldingPrintPreview&record={$RECORD_MODEL->getId()}&PDFDownload=true">Download</a>
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
                        <strong>STATEMENT OF HOLDINGS AND VALUATION</strong>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        <table class="activity-tbl" style="margin-top:5mm; width: 40%">
                            <tr>
                                <th colspan="2">REFERENCE VALUE AS PER THE</th>
                            </tr>
                            <tr>
                                <th>LONDON FIX ON</th>
                                <td style="text-align:center">{$LBMA_DATE}</td>
                            </tr>
                            {foreach from=$ERP_HOLDINGMETALS item=metal}
                                <tr>
                                    <th>Gold</th>
                                    {* <th>{vtranslate($metal,'MetalPrice')}</th> *}
                                    {assign var="rate" value=$metal.spot_price|default:0}
                                    <td style="text-align:center">US$ {number_format($rate, 2, '.', ',')} / Oz..</td>
                                </tr>
                            {/foreach}
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        All amounts in USD
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 9pt; height: 168mm; vertical-align: top;">
                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th style="width:10%;">QTY</th>
                                <th style="width:50%;">DESCRIPTION</th>
                                <th style="width:20%;text-align:center">FINE WEIGHT(OZ.)</th>
                                <th style="width:20%;text-align:center">TOTAL</th>
                            </tr>
                            {foreach item=HOLDINGS key=location from=$ERP_HOLDINGS}
                                <tr class="no-border">
                                    <td></td>
                                    <td><strong>{vtranslate($location,'MetalPrice')}</strong></td>
                                    <td style='text-align:right'></td>
                                    <td style='text-align:right'></td>
                                </tr>
                                {foreach item=HOLDING from=$HOLDINGS}
                                    <tr class="no-border">
                                        <td style="vertical-align: top;">{number_format($HOLDING->quantity,0)}</td>

                                        <td>
                                            {$HOLDING->longDesc} <br>
                                            <span style="font-size: smaller;font-style: italic;">
                                                {$HOLDING->serials}
                                            </span>
                                        </td>

                                        <td style='vertical-align: top;text-align:right'>
                                            {number_format($HOLDING->pureOz * $HOLDING->quantity,2)}
                                        </td>
                                        {* {assign var=CRYPTO value=['MBTC','ETH']}
                                        {if in_array(strtoupper($HOLDING->metal),$CRYPTO) }
                                            <td style='vertical-align: top;text-align:right'>{number_format($HOLDING->pureOz,8)}
                                            </td>
                                        {else}
                                            <td style='vertical-align: top;text-align:right'>{number_format($HOLDING->pureOz,2)}
                                            </td>
                                        {/if} *}
                                        <td style='vertical-align: top;text-align:right'>
                                            {CurrencyField::convertToUserFormat($HOLDING->total)}</td>
                                    </tr>
                                {/foreach}
                            {/foreach}
                            <tr>
                                <th colspan="3">TOTAL MARKET VALUE</th>
                                <td style='text-align:right'><strong>US$
                                        {CurrencyField::convertToUserFormat($TOTAL)}</strong></td>
                            </tr>
                        </table>
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