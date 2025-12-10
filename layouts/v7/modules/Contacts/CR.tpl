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
            padding: 15mm 15mm;
            position: relative;
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
                {if isset($smarty.request.tableName) && $smarty.request.tableName neq ''}
                    <a style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                        href="index.php?module=Contacts&view=ViewCR&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&tableName={$smarty.request.tableName}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                        Download
                    </a>
                {else}
                    <a style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                        href="index.php?module=Contacts&view=ViewCR&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                        Download
                    </a>
                {/if}
            </li>
        </ul>
    {/if}

    {assign var="start" value=0}
    {assign var="end" value=1}
    {assign var="calcTotal" value=0}
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
                                style="max-height: 100%; float:left;width: 154px;">
                            <div style="font-size: 11pt;margin-top: 14px;margin-bottom: 32px;float:right;">
                                <span>From: {$RECORD_MODEL->get('cf_898')}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm;text-align: left">
                            <div style="margin-top: 5mm;">To:</div>
                            <div style="font-weight: 700;">Global Precious Metals Pte. Ltd.</div>
                            <div>143 Cecil Street</div>
                            <div>#07-01 GB Building</div>
                            <div style="margin-bottom: 1mm;">Singapore 069542</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 30mm; text-decoration: underline;text-align: right;">
                            <strong>COLLECTION REQUEST</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding:0; margin:0;">
                            <table class="activity-tbl"
                                style="width:75%; border-collapse:collapse; table-layout:fixed; margin-left:0;">
                                <tr>
                                    <th style="width:33.33%; text-align:center;">REFERENCE</th>
                                    <th style="width:33.33%; text-align:center;">CUSTOMER</th>
                                    <th style="width:33.33%; text-align:center;">ORDER</th>
                                </tr>
                                <tr>
                                    <td style="height:18px;">{$smarty.request.docNo}</td>
                                    <td style="height:18px;">{$RECORD_MODEL->get('cf_898')}</td>
                                    <td style="height:18px;">COLLECTION</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>


                {assign var="location" value=$ERP_DOCUMENT.barItems[0]->warehouse}

                <div style="margin-top: 5mm;">I/We hereby wish to collect the Stored Metal detailed below at the following
                    location:
                    <p>{$location}</p>
                </div>

                <table class="print-tbl" style="margin-top:5mm;">
                    <tr>
                        {assign var="metalPrice" value=$ERP_DOCUMENT.barItems[0]->price}
                        <td style="font-size: 9pt; vertical-align: top;">

                            <table class="activity-tbl">
                                <tr>
                                    <th style="width:10%;">QTY</th>
                                    <th style="width:40%;">DESCRIPTION</th>
                                    <th style="width:40%;">SERIAL NUMBERS</th>
                                    <th style="width:12.5%;text-align:center">FINE OZ.</th>
                                </tr>
                                {{assign var="total_value" value=0}}

                                {foreach item=barItem from=$ERP_DOCUMENT.barItems}

                                    {* add to total_value *}
                                    {assign var="total_value" value=$total_value+$barItem->totalFineOz}

                                    <tr>
                                        <td>{number_format($barItem->quantity,0)}</td>

                                        <td>{$barItem->itemDescription}</td>

                                        <td><span style="font-size:smaller;font-style:italic;">
                                                {implode(", ", $barItem->serials)}</span></td>
                                        <td style="text-align:right;"> {number_format($barItem->totalFineOz,3)}</td>
                                    </tr>

                                {/foreach}

                                {if $PAGES eq $page}
                                    <tr>
                                        <th style="width:75%;" colspan="2">TOTAL QUANTITY:</th>
                                        <td style="text-align:right" colspan="2"><strong>{number_format($calcTotal,3)}</strong>
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </td>
                    </tr>

                </table>

                <div style="margin-top: 6mm;">I/We would like the Collection to take place on:
                    <span>...................</span>
                </div>

                <div style="margin-top: 4mm;">
                    <input type="checkbox" style="transform: scale(1.2); margin-right: 2mm;" />
                    <span>I/We will personally collect the Stored Metal at the Storage Facility and will be holding
                        ID/Passport number</span>
                    <span> .......................</span>
                </div>

                <div style="margin-top: 4mm;">
                    <input type="checkbox" style="transform: scale(1.2); margin-right: 2mm;" />
                    <span>I/We hereby authorise Mr/Mrs/Representatives of the company </span>
                    <span> .......................</span>
                    (<span>holding ID/Passport number</span>
                    <span> .......................</span>)
                    <span> to collect the Stored Metal on my/our behalf. This
                        authorisation is only valid for the collection of the Stored Metal specified above and shall not be
                        extended
                        to any other services covered under the Customer Metal Agreement.</span>
                </div>


                <p style="margin-top: 4mm;font-style: italic;font-weight: bold;">I/We hereby enclose a photocopy of the
                    passport of the person(s) who will collect the Stored Metal. The
                    original passport(s) will need to be presented prior to Collection at the Storage Facility</p>

                <div style="margin-top: 5mm;">This Collection Order is subject to and governed by the terms and conditions
                    of the Customer Metal Agreement
                    executed and entered into by and between me/us and Global Precious Metals Pte. Ltd.</div>

                <div style="margin-top: 5mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <span>Place: </span>
                        <span>................................</span>
                    </div>
                    <div class="bottom-container-item">
                        <span>Date: </span>
                        <span>{$smarty.now|date_format:"%d-%m-%Y"}</span>
                    </div>
                </div>

                <div style="margin-top: 5mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <span>Signed by: </span>
                        <span style="font-style: italic;font-weight: bold;">{$RECORD_MODEL->get('firstname')}
                            {$RECORD_MODEL->get('lastname')}</span>
                    </div>
                    <div class="bottom-container-item">
                        <span>On behalf of:</span>
                        <span style="font-weight: 700;">Global Precious Metals Pte Ltd</span>
                    </div>
                </div>

                <div style="margin-top: 5mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <div
                            style="border-bottom: 1px solid #000;margin-bottom:2mm;height: 100px;background-color:#dce6f9;">
                        </div>
                        <p>Signature</p>
                    </div>
                    <div class="bottom-container-item"></div>
                </div>
            </div>
        </div>
    {/for}
</body>

</html>