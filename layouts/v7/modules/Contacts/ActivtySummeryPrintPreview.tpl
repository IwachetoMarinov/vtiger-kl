<!DOCTYPE html>
<html>

<head>
    <title>TRANSACTION HISTORY</title>
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
        }

        .printAreaContainer {
            height: 297mm;
            width: 230mm;
            border: 1px solid #fff;
            margin: auto;
            margin-top: 10px;
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

<body style="margin: 0px;">
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
                    href="index.php?module=Contacts&view=ActivtySummeryPrintPreview&record={$RECORD_MODEL->getId()}&ActivtySummeryDate={$smarty.request.ActivtySummeryDate}&PDFDownload=true">Download</a>
            </li>
        </ul>
    {/if}
    {assign var="grandTotal" value=0}
    {assign var="movementTotal" value=0}
    {assign var="balanceAmount" value=0}
    {assign var="start" value=0}
    {assign var="end" value=1}
    {for $page=1 to $PAGES}
        {if $page eq 1}
            {assign var="end" value=25}
        {else}
            {assign var="end" value=($end+30)}
        {/if}
        <div class="printAreaContainer">
            <div class="full-width">
                <table class="print-tbl">
                    <tr>
                        <td style="height: 144px;vertical-align: top;">
                            <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                                style="height: 103px; margin-top: -14px;float:right;width: 154px;">
                            <div style="font-size:11pt;">
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
                    {if $page eq 1}
                        <tr>
                            <td style="height: 10mm; text-decoration: underline;text-align: center">
                                <strong>TRANSACTION HISTORY</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="height: 10mm;text-align: left;font-size:11pt">
                                Date: {date('d-M-y')}
                            </td>
                        </tr>
                    {/if}
                    <tr>
                        <td style="text-align: right;font-size: 9pt">
                            All amounts in currency
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 10pt; height: {if $page eq 1}203{else}224{/if}mm; vertical-align: top;">
                            <table class="activity-tbl">
                                <tr>
                                    <th>DOCUMENT NO.</th>
                                    <th style="width: 22mm;text-align: center;min-width: 28mm;">DATE</th>
                                    <th>DESCRIPTION</th>
                                    <th style="width: 22mm;">DEPOSIT/(WITHDRAWAL)</th>
                                    <th style="text-align: center">BALANCE</th>
                                </tr>

                                {for $loopStart=$start to $end}
                                    {if $loopStart >= count($TRANSACTIONS)}{break}{/if}

                                    {assign var="start" value=($loopStart+1)}
                                    {assign var="TRANSACTION" value=$TRANSACTIONS[$loopStart]}

                                    {* Asign description *}
                                    {assign var="description" value=$TRANSACTION['description']|default:''}


                                    {* Normalize values to avoid null warnings *}
                                    {assign var="docNo" value=$TRANSACTION['voucher_no']|default:''}
                                    {assign var="usdVal" value=$TRANSACTION['amount_in_account_currency']|default:0}

                                    {* Determine direction based on type *}
                                    {if in_array($TRANSACTION['voucher_type'], ['PUR','PAY'])}
                                        {assign var="usdVal" value=$usdVal * -1}
                                    {/if}

                                    {* Record movement *}
                                    {assign var="movementTotal" value=$movementTotal + $usdVal}
                                    {assign var="balanceAmount" value=$balanceAmount + $usdVal}

                                    {assign var="transDate" value=$TRANSACTION['document_date']|default:''}

                                    {* Skip FX / MP transactions *}
                                    {if in_array($docNo|substr:0:3, array('FXP','FXR','MPD','MRD'))}
                                        {continue}
                                    {/if}

                                    {* Grand total always accumulates *}
                                    {assign var="grandTotal" value=$grandTotal+$usdVal}

                                    {* Opening balance row *}
                                    {if $docNo|substr:0:3 eq 'AOP'}
                                        <tr>
                                            <td colspan="4"><strong>OPENING BALANCE</strong></td>
                                            <td style="text-align:right">
                                                <strong>
                                                    {if $usdVal > 0}
                                                        {number_format($usdVal, 2, '.', ',')}
                                                    {else}
                                                        ({number_format($usdVal*-1, 2, '.', ',')})
                                                    {/if}
                                                </strong>
                                            </td>
                                        </tr>
                                        {assign var="balanceAmount" value=$usdVal}
                                        {continue}
                                    {/if}

                                    {* Movement + running balance *}
                                    {* {assign var="movementTotal" value=$movementTotal+$usdVal}
                                    {assign var="balanceAmount" value=$balanceAmount+$usdVal} *}

                                    <tr>
                                        <td>{$docNo}</td>
                                        <td>
                                            {if $transDate ne ''}
                                                {$transDate|date_format:"%d-%b-%y"}
                                            {/if}
                                        </td>
                                        <td>{$description}</td>
                                        <td style="text-align:right">
                                            {if $usdVal > 0}
                                                {number_format($usdVal, 2, '.', ',')}
                                            {else}
                                                ({number_format($usdVal*-1, 2, '.', ',')})
                                            {/if}
                                        </td>
                                        <td style="text-align:right">
                                            {if $balanceAmount gte 0}
                                                {number_format($balanceAmount, 2, '.', ',')}
                                            {else}
                                                ({number_format($balanceAmount*-1, 2, '.', ',')})
                                            {/if}
                                        </td>
                                    </tr>
                                {/for}


                                {if $PAGES eq $page}
                                    <tr>
                                        <td><strong>Total Movement</strong></td>
                                        <td></td>
                                        <td></td>
                                        <td style="text-align:right">
                                            <strong>{if $movementTotal gte 0 }{number_format($movementTotal,2, '.', ',')}{else}({number_format($movementTotal*-1,2, '.', ',')}){/if}</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>Ending Balance</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th style="text-align:right">{if $grandTotal gte 0 }
                                                {if $grandTotal eq 0}
                                                    --
                                                {else}
                                                    {number_format($grandTotal,2, '.', ',')}
                                                {/if}
                                            {else}
                                                ({number_format($grandTotal*-1,2, '.', ',')})

                                            {/if}
                                        </th>
                                    </tr>
                                {/if}
                            </table>
                            {if $PAGES eq $page}
                                <div style="text-align: right;font-size: 9pt;margin-top: 2mm">
                                    {if $grandTotal > 0 } This amount is owed to you by GPM.{/if}
                                    {if $grandTotal < 0 } This amount is owed from you to GPM.{/if}
                                </div>
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td style='font-size: 8pt;font-weight: bold;'>
                            <div>
                                <div style="float:left">
                                    {if isset($COMPANY)}
                                        {$COMPANY->get('company_name')} {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg.
                                        No. {$COMPANY->get('company_reg_no')}){/if}<br>
                                        {$COMPANY->get('company_address')}<br>
                                        T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                                        {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                                    {/if}
                                </div>
                                <div style="float:right;"><br><br>Page {$page} | {$PAGES}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    {/for}
</body>

</html>