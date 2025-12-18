<!DOCTYPE html>
<html>

<head>
    <title>METAL PAYMENT DELIVERY TO GPM </title>
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

        .printAreaContainer .print-tbl {
            border-collapse: collapse;
            width: 100%;
            border: none;
        }

        .print-footer {
            height: 20mm;
            background: #008ECA;
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
    </style>
</head>

<body>

    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                <a style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=MPDPrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&tableName={$smarty.request.tableName}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a>
            </li>
        </ul>
    {/if}

    {assign var="start" value=0}
    {assign var="end" value=1}

    {for $page=1 to $PAGES}

        {if $page eq 1}
            {assign var="end" value=26}
        {else}
            {assign var="end" value=($end+26)}
        {/if}

        <div class="printAreaContainer">

            <table class="print-tbl">
                <tr>
                    <td style="height:28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height:100%; float:right;width:154px;">

                        <div style="font-size:11pt;margin-top:14px;margin-bottom:32px">

                            {$RECORD_MODEL->get('cf_898')}<br>

                            {if !$HIDE_BP_INFO}

                                {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}<br>

                                {if !empty($RECORD_MODEL->get('cf_968'))}
                                    {$RECORD_MODEL->get('cf_968')}<br>
                                {/if}

                                {if !empty($RECORD_MODEL->get('mailingstreet'))}
                                    {$RECORD_MODEL->get('mailingstreet')}<br>
                                {/if}

                                {if !empty($RECORD_MODEL->get('cf_970'))}
                                    {$RECORD_MODEL->get('cf_970')}<br>
                                {/if}

                                {if empty($RECORD_MODEL->get('mailingpobox'))}

                                    {if !empty($RECORD_MODEL->get('mailingcity')) && !empty($RECORD_MODEL->get('mailingzip'))}
                                        {$RECORD_MODEL->get('mailingcity')} {$RECORD_MODEL->get('mailingzip')}<br>
                                    {elseif !empty($RECORD_MODEL->get('mailingcity'))}
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
                    <td style="height:20mm;text-decoration:underline;text-align:center">
                        <strong>METAL PAYMENT DELIVERY</strong>
                    </td>
                </tr>

                <tr>
                    {assign var="metalPrice" value=$ERP_DOCUMENT.barItems[0]->price}
                    <td style="font-size:9pt;height:168mm;vertical-align:top;">

                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th colspan="2" style="width:25%;text-align:center">DOCUMENT NO</th>
                                <th style="width:25%;text-align:center">DOCUMENT DATE</th>
                                <th style="width:25%;text-align:center">DELIVERY DATE</th>
                                <th style="width:25%;text-align:center">LOCATION</th>
                            </tr>

                            {assign var="location" value=$ERP_DOCUMENT.barItems[0]->warehouse}

                            <tr>
                                <td colspan="2" style="text-align:center;">{$smarty.request.docNo}</td>
                                <td style="text-align:center;">{$ERP_DOCUMENT['documentDate']}</td>
                                <td style="text-align:center;">{$ERP_DOCUMENT['postingDate']}</td>

                                <td style="text-align:center;">
                                    {$location}
                                </td>
                            </tr>
                        </table>

                        <table class="activity-tbl">
                            <tr>
                                <th style="width:10%;">QTY</th>
                                <th style="width:40%;">DESCRIPTION</th>
                                <th style="width:12.5%;text-align:center">FINE OZ.</th>
                            </tr>
                            {{assign var="total_value" value=0}}


                            {foreach item=barItem from=$ERP_DOCUMENT.barItems}

                                {* add to total_value *}
                                {assign var="total_value" value=$total_value+$barItem->totalFineOz}

                                <tr>
                                    <td>{number_format($barItem->quantity,0)}</td>

                                    <td>
                                        {$barItem->itemDescription}
                                        <br>
                                        <span style="font-size:smaller;font-style:italic;">
                                            {implode(", ", $barItem->serials)}
                                        </span>
                                    </td>

                                    <td style="text-align:right;">
                                        {number_format($barItem->totalFineOz,3)}
                                    </td>
                                </tr>

                            {/foreach}

                            <tr>
                                <th colspan="2">TOTAL FINE OZ:</th>
                                <td style="text-align:right">
                                    <strong>{number_format($total_value,3)}</strong>
                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>

                <tr>
                    <td style="font-size:8pt;font-weight:bold;position:absolute;bottom:14px;width:85%;">

                        <div>
                            {if isset($COMPANY)}
                                <div style="float:left">
                                    {$COMPANY->get('company_name')}
                                    {if $COMPANY->get('company_reg_no')} (Co. Reg. No. {$COMPANY->get('company_reg_no')})
                                    {/if}<br>
                                    {$COMPANY->get('company_address')}<br>
                                    T: {$COMPANY->get('company_phone')}
                                    {if $COMPANY->get('company_fax')} | Fax: {$COMPANY->get('company_fax')} {/if}
                                    | {$COMPANY->get('company_website')}<br>
                                </div>
                            {/if}

                            <div style="float:right;">
                                <br><br>
                                Page {$page} | {$PAGES}
                            </div>

                        </div>

                    </td>
                </tr>

            </table>

        </div>

    {/for}

</body>

</html>