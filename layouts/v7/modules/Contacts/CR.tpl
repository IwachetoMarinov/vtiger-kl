<!DOCTYPE html>
<html>

<head>
    <title>COLLECTION REQUEST FROM GPM </title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            src: url('layouts/v7/resources/fonts/OpenSans-Regular.woff') format('woff');
        }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 700;
            src: url('layouts/v7/resources/fonts/OpenSans-Bold.woff') format('woff');
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
            padding: 6mm 9mm;
            position: relative;
        }

        .text-content {
            font-size: 9.75pt !important;
        }

        .bottom-container {
            display: flex;
            gap: 10mm;
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
            font-size: 9.5;
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
            font-size: 9.5;
            background: #ECECEC;
            font-weight: bold;
            padding: 4px
        }

        table.content-table tr.footer th {
            color: #008ECA;
        }

        table.content-table td {
            border: none;
            font-size: 9.5;
            font-weight: normal;
            padding: 4px
        }

        table.graph-table td {
            width: 50%;
            font-size: 9.5;
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

        .signed-item {
            width: 30%;
        }

        .behalf-item {
            flex: 1;
        }

        table.activity-tbl th {
            background: #bca263;
        }

        input[type="checkbox"] {
            width: 5mm;
            height: 5mm;
            vertical-align: middle;
            margin-right: 1.5mm;
            accent-color: #000;
        }
    </style>
</head>

<body>
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                {if isset($smarty.request.tableName) && $smarty.request.tableName neq ''}
                    <a id="downloadBtn"
                        style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                        href="index.php?module=Contacts&view=ViewCR&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&tableName={$smarty.request.tableName}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                        Download
                    </a>
                {else}
                    <a id="downloadBtn"
                        style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                        href="index.php?module=Contacts&view=ViewCR&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                        Download
                    </a>
                {/if}
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
                                style="max-height: 100%; float:left;max-width: 154px;">
                            <div style="font-size: 11pt;margin-top: 20mm; float:right;">
                                <span>From: {$RECORD_MODEL->get('cf_898')}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm;text-align: left; font-size: 9.5pt;">
                            <div style="max-width:50%">
                                {if isset($COMPANY)}
                                    <div style="margin-top: 6mm;">To:
                                        <span style="font-weight: 700; text-transform: capitalize;">
                                            {$COMPANY->get('company_name')}
                                        </span>
                                    </div>
                                {/if}
                                <div>
                                    {if isset($COMPANY)}
                                        {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg. No.
                                        {$COMPANY->get('company_reg_no')})<br>{/if}
                                        {$COMPANY_FULL_ADDRESS}<br>
                                        T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                                        {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('email')}<br>
                                    {/if}
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 15mm; text-decoration: underline;text-align: right;">
                            <strong>COLLECTION REQUEST</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding:0; margin:0;">
                            <table class="activity-tbl"
                                style="width:85%; border-collapse:collapse; table-layout:fixed; margin-left:0;">
                                <tr>
                                    <th style="width:40%; text-align:center;">REFERENCE</th>
                                    <th style="width:30%; text-align:center;">CUSTOMER</th>
                                    <th style="width:30%; text-align:center;">ORDER</th>
                                </tr>
                                <tr>
                                    <td style="height:18px; text-align:center;">{$smarty.request.docNo}</td>
                                    <td style="height:18px; text-align:center;">{$RECORD_MODEL->get('cf_898')}</td>
                                    <td style="height:18px; text-align:center;">COLLECTION</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>


                {assign var="location" value=$ERP_DOCUMENT.barItems[0]->warehouse}

                <div style="margin-top: 3mm;">I/We hereby wish to collect the Stored Metal detailed below at the following
                    location:
                    <p style="font-style: italic;font-weight: 600;">{$location}</p>
                </div>

                <table class="print-tbl" style="margin-top: 3mm;">
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
                                                <pre>{$barItem->serialNumbers}</pre>
                                            </span>
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

                <div class="text-content">

                    <div style="margin-top: 4mm;">I/We would like the Collection to take place on:
                        <span>...................</span>
                    </div>

                    <div style="margin-top: 3mm;">
                        {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                            <input type="checkbox" name="id_option">
                        {else}
                            <span
                                style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">{if isset($ID_OPTION) && $ID_OPTION eq 1}✔{/if}</span>
                        {/if}
                        <span>I/We will personally collect the Stored Metal at the Storage Facility and will be holding
                            ID/Passport number</span>
                        <span> .......................</span>
                    </div>

                    <div style="margin-top: 3mm;">
                        {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                            <input type="checkbox" name="company_option">
                        {else}
                            <span
                                style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">{if isset($COMPANY_OPTION) && $COMPANY_OPTION eq 1}✔{/if}</span>
                        {/if}

                        <span>I/We hereby authorise Mr/Mrs/Representatives of the company </span>
                        <span> .......................</span>
                        (<span>holding ID/Passport number</span>
                        <span> .......................</span>)
                        <span> to collect the Stored Metal on my/our behalf. This
                            authorisation is only valid for the collection of the Stored Metal specified above and shall not
                            be
                            extended
                            to any other services covered under the Customer Metal Agreement.</span>
                    </div>


                    <p style="margin-top: 4mm;font-style: italic;font-weight: bold;">I/We hereby enclose a photocopy of the
                        passport of the person(s) who will collect the Stored Metal. The
                        original passport(s) will need to be presented prior to Collection at the Storage Facility</p>

                    <div style="margin-top: 4mm;">This Collection Order is subject to and governed by the terms and
                        conditions
                        of the Customer Metal Agreement
                        executed and entered into by and between me/us and {if isset($COMPANY)}
                            <span style="text-transform: capitalize;">{$COMPANY->get('company_name')}</span>
                        {/if}
                    </div>
                </div>
                <table style="width:100%; margin-top:4mm; border-collapse:collapse;">
                    <tr>
                        <td style="width:50%;"></td>
                        <td>Date:</td>
                    </tr>
                </table>

                <table style="width:100%; margin-top:4mm; border-collapse:collapse;">
                    <tr>
                        <td style="width:50%;">Signed by:</td>
                        <td>On behalf of:</td>
                    </tr>
                </table>

                <table style="width:100%; margin-top:3mm; border-collapse:collapse;">
                    <tr>
                        <td style="width:48%; vertical-align:top;">
                            <div style="border-bottom:1px solid #000; height:80px; margin-bottom:2mm;"></div>
                            <p>Signature</p>
                        </td>
                        <td style="width:52%;"></td>
                    </tr>
                </table>


            </div>
        </div>

    {/for}

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function(e) {
            const companyOption = document.querySelector('input[name="company_option"]');
            const idOption = document.querySelector('input[name="id_option"]');

            const url = new URL(this.href);

            if (companyOption && companyOption.checked) {
                url.searchParams.set('companyOption', "1");
            } else {
                url.searchParams.delete('companyOption');
            }

            if (idOption && idOption.checked) {
                url.searchParams.set('idOption', "1");
            } else {
                url.searchParams.delete('idOption');
            }

            this.href = url.toString();
        });
    </script>
</body>

</html>