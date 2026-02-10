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

        body {
            font-family: 'Open Sans';
            font-size: 10pt;
            color: #666;
        }

        .printAreaContainer {
            height: 297mm;
            width: 210mm;
            border: 1px solid #fff;
            margin: auto;
            padding: 6mm;
            padding-top: 2mm;
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

        .editable-input-wrapper {
            margin-top: 3mm;
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        .editable-input-wrapper--nested {
            flex-wrap: wrap;
        }

        .editable-input {
            border: none;
            width: 40mm;
            min-width: 40mm;
            border-bottom: 1px dotted #000;
        }

        .custom-checkbox {
            font-size: 3.5mm;
            border: 1px solid #000;
            padding: 2px 2px;
            display: inline-block;
            height: 5mm;
            width: 5mm;
            line-height: 3.5mm;
            display: inline-block;
            margin-bottom: 0.8mm;
        }

        .editable-input:focus {
            outline: none;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 2mm;
            padding: 0 4mm;
        }

        .signature-section-item {
            display: flex;
            justify-content: space-between;
            margin-top: 4mm;
        }

        .signature-section-left {
            width: 40%;
        }

        .signature-section-right {
            width: 57%;
        }

        .custom-editable-input {
            border: none;
            possition: relative;
            padding-bottom: 1mm;
            flex: 1;
            min-width: 40mm;
            border-bottom: 1px dotted #000;
        }

        .custom-editable-input:focus {
            outline: none;
        }

        .full-width {
            width: 100%;
        }

        .custom-editable-table-input {
            min-width: auto;
        }

        .input-without-border {
            border: none;
            width: 100%;
        }

        @media print {

            @page {
                size: A4;
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                height: 297mm;
            }
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
                        href="index.php?module=Contacts&view=ViewCRNew&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&tableName={$smarty.request.tableName}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                        Download
                    </a>
                {else}
                    <a id="downloadBtn"
                        style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                        href="index.php?module=Contacts&view=ViewCRNew&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
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
                        <td>
                            {if isset($smarty.request.PDFDownload) && $smarty.request.PDFDownload eq true}
                                <img src="file:///var/www/html/layouts/v7/modules/Contacts/resources/gpm-new-logo.png"
                                    style="max-height: 100%; float:left;width: 196px;" />
                            {else}
                                <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                                    style="max-height: 100%; float:left;width: 196px;" />
                            {/if}
                            <div style="font-size: 11pt;margin-top: 27mm; float:right;">
                                <span>From: {$RECORD_MODEL->get('cf_898')}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm;text-align: left">
                            <div style="max-width:50%">
                                {if isset($COMPANY)}
                                    <div style="margin-top: 4mm;">To:
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
                                        {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('email')}<br>
                                    {/if}
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 20mm; text-decoration: underline;text-align: right;">
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
                                    <td style="height:18px; text-align:center;">
                                        <input type="text" name="reference"
                                            class="custom-editable-input input-without-border" />
                                    </td>
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

                {assign var="targetRows" value=[0, 1, 2, 3, 4, 5, 6, 7, 8, 9]}

                <table class="print-tbl" style="margin-top:5mm;">
                    <tr>

                        <td style="font-size: 9pt; vertical-align: top;">

                            <table class="activity-tbl">
                                <tr>
                                    <th style="width:5%;">QTY</th>
                                    <th style="width:65%;">DESCRIPTION</th>
                                    <th style="width:20%;">SERIAL NUMBERS</th>
                                    <th style="width:10%;text-align:center">FINE OZ.</th>
                                </tr>

                                {if isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload eq true}

                                    {foreach item=item from=$targetRows name=rowloop}

                                        <tr>
                                            <td>
                                                <input type="text" name="qty_{$smarty.foreach.rowloop.iteration}"
                                                    style="width:100%; border:0;" />
                                            </td>
                                            <td style="height:12mm; vertical-align:top;">
                                                <textarea name="desc_{$smarty.foreach.rowloop.iteration}"
                                                    style="width:100%; height:12mm; border:0; resize:none; overflow:hidden;"></textarea>
                                            </td>
                                            <td>
                                                <input type="text" name="serial_{$smarty.foreach.rowloop.iteration}"
                                                    style="width:100%; border:0;" />
                                            </td>
                                            <td style="text-align:right;">
                                                <input type="text" name="fine_oz_{$smarty.foreach.rowloop.iteration}"
                                                    style="width:100%; border:0; text-align:right;" />
                                            </td>
                                        </tr>

                                    {/foreach}

                                {/if}

                                {if $PAGES eq $page}
                                    <tr>
                                        <td style="width:100%;" colspan="4">
                                            {if isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload eq true}
                                                <input type="text" name="total_value"
                                                    style="width:35mm; border:0; font-weight:bold;" />

                                                <input type="text" name="total_oz"
                                                    style="width:35mm; border:0; font-weight:bold; float:right; text-align:right;" />
                                            {else}
                                                <strong>{number_format($total_value,0)} </strong>
                                                <strong style="float: right;">{number_format($total_oz,4)}</strong>
                                            {/if}
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </td>
                    </tr>

                </table>

                <div class="editable-input-wrapper">I/We would like the Collection to take place on:
                    <input type="text" name="collection_date" class="editable-input" />
                </div>

                <div style="margin-top: 3mm;">
                    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                        <input type="checkbox" name="id_option">
                    {else}
                        <span class="custom-checkbox">{if isset($ID_OPTION) && $ID_OPTION eq 1}✔{/if}</span>
                    {/if}
                    <span>I/We will personally collect the Stored Metal at the Storage Facility and will be holding
                        ID/Passport number</span>
                </div>
                <div>
                    <input type="text" name="passport_number" class="editable-input" />
                </div>


                <div class="editable-input-wrapper editable-input-wrapper--nested">
                    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                        <input type="checkbox" name="company_option">
                    {else}
                        <span class="custom-checkbox">{if isset($COMPANY_OPTION) && $COMPANY_OPTION eq 1}✔{/if}</span>
                    {/if}

                    <span>I/We hereby authorise Mr/Mrs/Representatives of the company </span>
                    <input type="text" name="company_input" class="editable-input" />
                    (<span>holding ID/Passport number</span>
                    <input type="text" name="holding_passport_number" class="editable-input" />)
                    <span> to collect the Stored Metal on my/our behalf. This
                        authorisation is only valid for the collection of the Stored Metal specified above and shall not
                        be
                        extended
                        to any other services covered under the Customer Metal Agreement.</span>
                </div>


                <p style="margin-top: 3mm;font-style: italic;font-weight: bold;">I/We hereby enclose a photocopy of the
                    passport of the person(s) who will collect the Stored Metal. The
                    original passport(s) will need to be presented prior to Collection at the Storage Facility</p>

                <div style="margin-top: 3mm;">This Collection Order is subject to and governed by the terms and conditions
                    of the Customer Metal Agreement executed and entered into by and between me/us and {if isset($COMPANY)}
                        <span style="text-transform: capitalize;">{$COMPANY->get('company_name')}</span>
                    {/if}
                </div>

                <!-- SIGNATURE SECTION -->
                <div class="signature-section">
                    <div class="signature-section-item">
                        <div class="signature-section-left">
                            <div class="editable-input-wrapper">
                                <span> Place:</span> <input type="text" name="place_input" class="custom-editable-input" />
                            </div>
                            <div class="editable-input-wrapper" style="margin-top: 4.5mm;">
                                <span>Date:</span> <input type="text" name="date_input" class="custom-editable-input" />
                            </div>
                        </div>

                        <div class="signature-section-right">
                            <div class="editable-input-wrapper">
                                <span> Signed by: </span>
                                <input type="text" name="signed_by" class="custom-editable-input" />
                            </div>
                            <div class="editable-input-wrapper" style="margin-top: 4.5mm;">
                                <span> On behalf of:</span>
                                <input type="text" name="on_behalf_of" class="custom-editable-input" />
                            </div>
                        </div>
                    </div>
                </div>

                {* <div style="margin-top: 3mm;" class="bottom-container">
                    <div class="bottom-container-item">
                        <div style="border-bottom: 1px solid #000;margin-bottom:2mm;height: 50px;background-color:#dce6f9;">
                        </div>
                        <p>Signature</p>
                    </div>
                    <div class="bottom-container-item"></div>
                </div> *}
            </div>
        </div>

    {/for}

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function(e) {
            const companyName = document.querySelector('input[name="company_option"]');
            const idOption = document.querySelector('input[name="id_option"]');

            const url = new URL(this.href);

            if (companyName && companyName.checked) {
                url.searchParams.set('companyName', "1");
            } else {
                url.searchParams.delete('companyName');
            }

            if (idOption && idOption.checked) {
                url.searchParams.set('idOption', "1");
            } else {
                url.searchParams.delete('idOption');
            }

            // check for new inputs and append to URL if needed
            const collectionDateInput = document.querySelector('input[name="collection_date"]');
            const passportNumberInput = document.querySelector('input[name="passport_number"]');
            const companyInput = document.querySelector('input[name="company_input"]');
            const holdingPassportInput = document.querySelector('input[name="holding_passport_number"]');

            if (companyInput && companyInput.value.trim() !== '') {
                url.searchParams.set('companyInput', companyInput.value.trim());
            } else {
                url.searchParams.delete('companyInput');
            }

            if (holdingPassportInput && holdingPassportInput.value.trim() !== '') {
                url.searchParams.set('holdingPassportInput', holdingPassportInput.value.trim());
            } else {
                url.searchParams.delete('holdingPassportInput');
            }

            if (collectionDateInput && collectionDateInput.value.trim() !== '') {
                url.searchParams.set('collectionDateInput', collectionDateInput.value.trim());
            } else {
                url.searchParams.delete('collectionDateInput');
            }

            if (passportNumberInput && passportNumberInput.value.trim() !== '') {
                url.searchParams.set('passportNumberInput', passportNumberInput.value.trim());
            } else {
                url.searchParams.delete('passportNumberInput');
            }
            // Get all custom-editable-input values and append to URL as query parameters
            document.querySelectorAll('.custom-editable-input').forEach(input => {
                if (!input.name) return;

                const val = (input.value ?? '').trim();
                if (val) url.searchParams.set(input.name, val);
                else url.searchParams.delete(input.name);
            });

            this.href = url.toString();
        });
    </script>
</body>

</html>