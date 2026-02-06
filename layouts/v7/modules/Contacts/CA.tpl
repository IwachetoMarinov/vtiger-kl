<!DOCTYPE html>
<html>

<head>
    <title>COLLECTION ACKNOWLEDGEMENT FROM GPM </title>
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
            padding: 10mm 10mm;
            position: relative;
        }

        .bottom-container {
            display: flex;
            gap: 8mm;
            font-size: 9.5pt;
        }

        .bottom-container-item {
            flex: 1;
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
    </style>
</head>

<body>
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                <a style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=CollectionAcknowledgement&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&tableName={$smarty.request.tableName}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a>
            </li>
        </ul>
    {/if}

    {assign var="start" value=0}
    {assign var="end" value=1}
    {for $page=1 to $PAGES}
        {if $page eq 1}
            {assign var="end" value=14}
        {else}
            {assign var="end" value=($end+14)}
        {/if}
        <div class="printAreaContainer">
            <div class="full-width">
                <table class="print-tbl">
                    <tr>
                        <td style="height: 28mm;">
                            <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                                style="max-height: 100%; float:left;width: 192px;">
                            <div style="font-size: 11pt;margin-top: 20mm; float:right;">
                                <span>From: {$RECORD_MODEL->get('cf_898')}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm;text-align: left">
                            <div style="max-width:50%">
                                {if isset($COMPANY)}
                                    <div style="margin-top: 10mm;">To:
                                        <span style="font-weight: 700; text-transform: capitalize;">
                                            {$COMPANY->get('company_name')}
                                        </span>
                                    </div>
                                {/if}

                                <div>
                                    {if isset($COMPANY)}
                                        {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg. No.
                                        {$COMPANY->get('company_reg_no')})<br>{/if}
                                        {$COMPANY->get('company_address')}<br>
                                        T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                                        {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                                    {/if}
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm; text-decoration: underline;text-align: right">
                            <strong>COLLECTION ACKNOWLEDGEMENT</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding:0; margin:0;">
                            <table class="activity-tbl"
                                style="width:60%; border-collapse:collapse; table-layout:fixed; margin-left:0;">
                                <tr>
                                    <th style="width:70%; text-align:center;">REFERENCE</th>
                                    <th style="width:30%; text-align:center;">CUSTOMER</th>
                                </tr>
                                <tr>
                                    <td style="height:18px; text-align:center;">{$smarty.request.docNo}</td>
                                    <td style="height:18px; text-align:center;">{$RECORD_MODEL->get('cf_898')}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>


                {assign var="location" value=$ERP_DOCUMENT.barItems[0]->warehouse}

                <div style="margin-top: 5mm;">I/We hereby confirm that I/we have duly collected the Stored Metal detailed
                    below at the following location:
                    <span style="font-style: italic;font-weight: 600;"> {$location}</span>.
                </div>

                <table class="print-tbl" style="margin-top:5mm;">
                    <tr>
                        {assign var="metalPrice" value=$ERP_DOCUMENT.barItems[0]->price}
                        <td style="font-size: 9pt; vertical-align: top;">

                            <table class="activity-tbl">
                                <tr>
                                    <th style="width:10%;">QTY</th>
                                    <th style="width:70%;">DESCRIPTION</th>
                                    <th style="width:20%;text-align:center">FINE OZ.</th>
                                </tr>
                                {{assign var="total_value" value=0}}
                                {{assign var="total_oz" value=0}}

                                {foreach item=barItem from=$ERP_DOCUMENT.barItems}

                                    {* add to total_value *}
                                    {assign var="total_value" value=$total_value+$barItem->quantity}
                                    {assign var="total_oz" value=$total_oz+$barItem->totalFineOz}

                                    <tr>
                                        <td>{number_format($barItem->quantity,0)}</td>

                                        <td>
                                            <span>{$barItem->itemDescription}</span> <br />
                                            <span style="font-size:smaller;font-style:italic;">
                                                {implode(", ", $barItem->serials)}</span>
                                        </td>

                                        <td style="text-align:right;"> {number_format($barItem->totalFineOz,4)}</td>
                                    </tr>

                                {/foreach}

                                {if $PAGES eq $page}
                                    <tr>
                                        <td style="width:100%;" colspan="3">
                                            <strong>{number_format($total_value,0)} </strong>
                                            <strong style="float: right;">{number_format($total_oz,4)}</strong>
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </td>
                    </tr>

                </table>

                <div style="margin-top: 6mm;">This Collection Acknowledgement is subject to and governed by the terms and
                    conditions of the Customer
                    Metal Agreement. The liability of GPM in respect to the Stored Metal to be collected from the Storage
                    Facility
                    shall cease when the Customer or its authorised representative has acknowledged receipt of the goods by
                    signing this Collection Acknowledgement receipt.</div>

                <div style="margin-top: 8mm; font-size: 9.5pt;">
                    <span>Date:</span>
                    <span>.........................................</span>
                </div>

                <div style="margin-top: 5mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <span>Signed by: </span>
                        <span>................................</span>
                    </div>
                    <div class="bottom-container-item">
                        <span>Signed by: </span>
                        <span>................................</span>
                    </div>
                </div>

                <div style="margin-top: 5mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <span>On behalf of: </span>
                        <span>...........................</span>
                    </div>
                    <div class="bottom-container-item">
                        <span>On behalf of:</span>

                        {if isset($COMPANY)}
                            <span style="font-weight: 700; text-transform: capitalize;">
                                {$COMPANY->get('company_name')}
                            </span>
                        {/if}
                    </div>
                </div>

                <div style="margin-top:5mm;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td width="48%" valign="top">
                                <div style="height:100px;"></div>
                                <div style="border-bottom:1px solid #000; margin-bottom:2mm;"></div>
                                <p style="margin:0;">Signature</p>
                            </td>

                            <td width="4%"></td>

                            <td width="48%" valign="top">
                                <div style="height:100px;"></div>
                                <div style="border-bottom:1px solid #000; margin-bottom:2mm;"></div>
                                <p style="margin:0;">Signature</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    {/for}
</body>

</html>