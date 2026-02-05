<!DOCTYPE html>
<html>

<head>
    <title>SALE ORDER</title>
    <meta charset="UTF-8">

    <style>
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            src: local('Open Sans'), local('OpenSans'),
                url(https://themes.googleusercontent.com/static/fonts/opensans/v6/cJZKeOuBrn4kERxqtaUH3T8E0i7KZn-EPnyo3HZu7kw.woff) format('woff');
        }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 700;
            src: local('Open Sans Bold'), local('OpenSans-Bold'),
                url(https://themes.googleusercontent.com/static/fonts/opensans/v6/k3k702ZOKiLJc3WVjuplzHhCUOGz7vYGh680lGh-uXM.woff) format('woff');
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Open Sans';
            font-size: 11pt;
        }

        .printAreaContainer {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 6mm;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 4mm;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 52mm;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            padding-top: 3mm;
        }

        /* From / To Section */
        .from-to-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }

        .from-to-table td {
            border: 1px solid #000;
            padding: 2mm;
            vertical-align: top;
        }

        .from-label {
            width: 50mm;
            height: 30mm;
        }

        .from-box {
            width: 65mm;
            height: 30mm;
            background: repeating-linear-gradient(transparent,
                    transparent 3.5mm,
                    #000 3.5mm,
                    #000 3.6mm);
        }

        .to-box {
            width: 85mm;
            line-height: 1.3;
        }

        .customer-label {
            width: 50mm;
            height: 8mm;
        }

        /* Section 2 */
        .section-title {
            margin: 2mm 0;
        }

        /* Metals Table */
        .metals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 8.5pt;
            margin-bottom: 2mm;
        }

        .metals-table th,
        .metals-table td {
            border: 1px solid #000;
            padding: 1mm;
            text-align: center;
        }

        .metals-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        td.metal-row-label {
            text-align: left;
            font-weight: bold;
            padding-left: 2mm;
        }

        /* Serials Box */
        .serials-box {
            min-height: 15mm;
            padding: 2mm;
            margin-bottom: 2mm;
        }

        /* Additional Sections */
        .additional-section {
            margin: 3mm 0;
            line-height: 1.4;
        }

        .additional-section strong {
            font-weight: bold;
        }

        .indent {
            margin-left: 5mm;
        }

        .line {
            display: inline-block;
        }

        .long-line {
            display: inline-block;
        }

        /* Bank Details Section */
        .bank-details {
            margin: 2mm 0;
        }

        .bank-row {
            margin-bottom: 1.5mm;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 8mm;
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

        .signature-line {
            display: inline-block;
            margin-top: 8mm;
        }

        .company-data {
            display: flex;
            border: 1px solid #000;
        }

        .company-data-item {
            width: 50%;
            font-size: 10pt;
            line-height: 1.2;
        }

        .company-data-item-to {
            display: flex;
        }

        .company-data-item-from {
            border-right: 1px solid #000;
        }

        .company-data-item-to,
        .from-container {
            display: flex;
        }

        .place-container {
            padding: 2mm;
            border-right: 1px solid #000;
            width: 25%;
        }

        .company-container {
            width: 75%;
        }

        .number-container {
            padding: 2mm;
            border-top: 1px solid #000;
        }

        .main-table {
            border: 1px solid #000;
            margin-top: 5mm;
            padding: 3.5mm 2mm;
        }

        .bolder-element {
            font-weight: bold;
        }

        .details-container {
            padding: 0 4mm;
        }

        .from-container-wrapper {
            display: flex;
        }

        .custom-editable-input {
            border: none;
            possition: relative;
            padding-bottom: 1mm;
            width: 100%;
            border-bottom: 1px dotted #000;
        }

        .custom-editable-input:focus {
            outline: none;
        }
    </style>
</head>

<body>
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                <a id="downloadBtn"
                    style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=SaleOrderView&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a>
            </li>
        </ul>
    {/if}

    <div class="printAreaContainer">

        <!-- HEADER -->
        <table class="header-table" style="margin-bottom: 1mm;">
            <tr>
                <td class="logo">
                    <img src="layouts/v7/modules/Contacts/resources/gpm-new-logo.png" width="100%">
                </td>
                <td class="title"></td>
                <td style="width:25mm;"></td>
            </tr>
        </table>

        <table class="header-table">
            <tr>
                <td class="title" style="text-decoration: underline; text-align: center;">SALE ORDER</td>
            </tr>
        </table>

        <!-- FROM / TO SECTION -->
        <div class="company-data">
            {* Left Column *}
            <div class="company-data-item company-data-item-from">
                <div class="from-container-wrapper">
                    <div class="place-container from-container">
                        <div><strong>From:</strong></div>
                    </div>
                    <div class="company-container" style="min-height: 24mm; padding:2mm;">
                        <div>
                            {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}<br>
                        </div>

                        <div>
                            {if !empty($RECORD_MODEL->get('mailingstreet'))}
                                {$RECORD_MODEL->get('mailingstreet')}<br>
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
                        </div>
                    </div>
                </div>
                <div class="number-container" style="padding-bottom: 6mm;">Customer number:
                    <span style="font-weight: 600;"> {$RECORD_MODEL->get('cf_898')}</span>
                </div>
            </div>

            {* Right column *}
            <div class="company-data-item company-data-item-to">
                <div class="place-container"><strong>To:</strong></div>
                <div class="company-container">
                    <div style="padding:2mm;min-height:27mm;">
                        <div style="text-transform: capitalize; font-weight: 600;">
                            {if isset($COMPANY)}
                                {$COMPANY->get('company_name')}
                            {/if}
                        </div>
                        <div style="margin-top: 1.5mm;">
                            {if isset($COMPANY)}
                                {$COMPANY->get('company_address')}
                            {/if}
                        </div>
                    </div>
                    <div class="number-container">
                        {if isset($COMPANY)}
                            {if !empty($COMPANY->get('company_fax'))} <p>Fax no: <span
                                    style="font-style: italic;">{$COMPANY->get('company_fax')}</span> or</p> {/if}
                            <p>Email:<span style="font-style: italic;"> {$COMPANY->get('company_website')}</span></p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1 -->
        <section class="main-table">
            <div class="additional-section bolder-element">
                <strong>1.</strong> This Sale Order is subject to and governed by the terms and conditions of the
                Customer Metal Agreement (CMA) executed and entered into by and between me/us and GPM.
            </div>

            <!-- SECTION 2 -->
            <div class="section-title bolder-element">
                <strong>2.</strong> I/We hereby wish to sell to
                <span style="text-transform: capitalize;">
                    {if isset($COMPANY)}
                        {$COMPANY->get('company_name')}
                    {else }.................................................
                    {/if}
                </span>
                the following precious metals:
            </div>

            <!-- METALS TABLE -->
            {assign var="metals" value=[
            'Gold 999.9',
            'Silver 999.0',
            'Platinum 999.5',
            'Palladium 999.5'
        ]}

            {assign var="weights" value=[
            ["label" => "1000oz", "grams" => "31,103g"],
            ["label" => "400oz", "grams" => "12,441g"],
            ["label" => "100oz", "grams" => "3,110g"],
            ["label" => "32.15oz", "grams" => "1,000g"],
            ["label" => "16.08oz", "grams" => "500g"],
            ["label" => "10oz", "grams" => "311g"],
            ["label" => "3.22oz", "grams" => "100g"],
            ["label" => "1oz", "grams" => "31g"],
            ["label" => "Other", "grams" => ""]
        ]}

            <table class="metals-table">
                <tr>
                    <th style="width:18%;">Metal</th>
                    {foreach from=$weights item=w}
                        <th style="width:9%;">
                            {$w.label}<br>
                            {if $w.grams}{$w.grams}{/if}
                        </th>
                    {/foreach}
                </tr>

                {foreach from=$metals item=m}
                    <tr>
                        <td class="metal-row-label">{$m}</td>
                        {foreach from=$weights item=w}
                            <td></td>
                        {/foreach}
                    </tr>
                {/foreach}
            </table>

            <!-- SERIALS BOX -->
            <div class="serials-box">
                If applicable, please specify the serial numbers of the items to be sold:
                <div style="margin-top: 2mm;">
                    .............................................................................................................................................................................
                </div>
                {* <input type="text" name="serials" value="{$SERIALS|default:''}"
                    style="width: 100%; border: 0; border-bottom: 1px solid #000; font-size: 12pt; padding: 2mm;" /> *}

            </div>

            <!-- SECTION 3 -->
            <div class="additional-section">
                If the metal to be sold is not currently in storage with GPM, please specify the
                exact pick-up location and the details of the person authorised to release the metal to GPM (if
                applicable, IC/Passport number):
                <div style="margin-top:2mm;">
                    Pick-up location:
                    <span>................................................................................................................................</span>
                </div>

                <div style="margin-top:2mm;">
                    <span>Authorised person: Full name:</span>
                    <span>..................................................................... </span>
                    <span> IC/Passport No:</span>
                    <span>..............</span>
                </div>
            </div>

            <!-- SECTION 4 -->
            <div class="additional-section bolder-element">
                <strong>3.</strong> I/We acknowledge that:
                <div class="indent">
                    - GPM shall quote a sale price, which is to be agreed and confirmed in writing (e.g. email, telafax)
                    to GPM.
                </div>
                <div class="indent">
                    - In the absence of the above written confirmation within 10 Business Days from the date hereof,
                    this Sale Order shall be null and void.
                </div>
            </div>

            <!-- SECTION 5 -->
            <div class="additional-section bolder-element">
                <strong>4.</strong> The sales proceeds agreed upon shall be transferred to my/our bank account:
            </div>

            <!-- BANK DETAILS -->
            <div class="details-container">
                <div class="bank-details">
                    <div class="bank-row">
                        Bank Name: <span
                            class="long-line">...........................................................................................................................................</span>
                    </div>
                    <div class="bank-row">
                        Bank Address: <span
                            class="long-line">........................................................................................................................................</span>
                    </div>
                    <div class="bank-row">
                        Swift Code: <span
                            class="line">.....................................................................</span>
                        &nbsp;&nbsp;&nbsp;
                        Swift Code: <span class="line">...............................................</span>
                    </div>
                    <div class="bank-row">
                        Account No: <span
                            class="line">...................................................................</span>
                        &nbsp;&nbsp;&nbsp;
                        Account Currency: <span class="line">...................................</span>
                    </div>
                </div>

                <!-- SIGNATURE SECTION -->
                <div class="signature-section">
                    <div class="signature-section-item">
                        <div class="signature-section-left">Place: <span class="line"
                                style="font-style: italic;">{$RECORD_MODEL->get('mailingcountry')}</span></div>
                        <div class="signature-section-right">
                            Date: <span class="line">................................</span>
                        </div>
                    </div>

                    {assign var="ON_BEHALF_OF" value=""}
                    {assign var="SIGNED_BY" value=""}

                    {if isset($CLIENT_TYPE) }

                        {if $CLIENT_TYPE == 'Corporate Entity' || $CLIENT_TYPE == 'Trust'  || $CLIENT_TYPE == 'Foundation' }
                            {assign var="ON_BEHALF_OF" value="{$RECORD_MODEL->get('lastname')}"}

                        {else if $CLIENT_TYPE == 'Individual' || $CLIENT_TYPE == 'Sole Proprietor' }
                            {assign var="SIGNED_BY" value="{$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}"}
                        {/if}

                    {/if}

                    <div class="signature-section-item">
                        <div class="signature-section-left">
                            Signed by: <span class="long-line" style="font-style: italic;">
                                {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                    <input type="text" style="border:none;width:40mm; padding:1mm;margin:0;"
                                        class="input-name" value="{$SIGNED_BY}" />
                                {else}
                                    {if isset($CLIENT_NAME) && !empty($CLIENT_NAME) && $CLIENT_NAME neq ''}
                                        {$CLIENT_NAME}
                                    {else}
                                        {$SIGNED_BY}
                                    {/if}
                                {/if}
                            </span>
                        </div>
                        <div class="signature-section-right">
                            On behalf of: <span class="line">{$ON_BEHALF_OF}</span>
                        </div>
                    </div>

                    <div style="margin-top:10mm;">
                        <div class="signature-line">...............................................</div><br>
                        Signature
                    </div>
                </div>
            </div>

        </section>
    </div>

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function(e) {

            const name = document.querySelector('.input-name')?.value;

            const url = new URL(this.href);

            if (name) {
                url.searchParams.set('clientName', name);
            } else {
                url.searchParams.delete('clientName');
            }

            this.href = url.toString();
        });
    </script>

</body>

</html>