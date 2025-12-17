<!DOCTYPE html>
<html>

<head>
    <title>PURCHASE & STORAGE ORDER</title>
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
            padding: 8mm 10mm;
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
            width: 35mm;
        }

        .title {
            text-align: center;
            font-size: 13pt;
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
            margin: 2mm 0 2mm 2mm;
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

        .metal-row-label {
            text-align: left;
            font-weight: bold;
            padding-left: 2mm;
        }

        /* Serials Box */
        .serials-box {
            /* min-height: 15mm; */
            padding: 2mm;
            /* margin-bottom: 1mm; */
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

        input[type="checkbox"] {
            width: 5mm;
            height: 5mm;
            vertical-align: middle;
            margin-right: 1.5mm;
            accent-color: #000;
        }

        .bank-codes-container {
            display: flex;
            gap: 10mm;
        }

        .bank-details-container {
            display: flex;
        }

        .bank-details-item {
            width: 50%;
        }

        .bank-item {
            margin-bottom: 1mm;
        }
    </style>


</head>

<body>
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                {* <a style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=PurchaseOrderView&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a> *}
                <a id="downloadBtn"
                    style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=PurchaseOrderView&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a>
            </li>
        </ul>
    {/if}

    <div class="printAreaContainer">

        <!-- HEADER -->
        <table class="header-table">
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
                <td class="logo"></td>
                <td class="title" style="text-decoration: underline;">PURCHASE & STORAGE ORDER</td>
                <td style="width:25mm;"></td>
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
                        <div>
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
                <strong>1.</strong> I/We hereby instruct GPM:
            </div>

            <!-- SECTION 2 -->
            <div class="section-title ">
                (a) <span class="bolder-element"> to purchase</span> in its name and on my/our behalf the following
                physical precious
                metals (Please indicate the quantity and type
                of bars required):
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
            ["label" => "Other", "grams" => "(pls specify)"]
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
                (b) <span class="bolder-element"> for the sum of
                    {if isset($SELECTED_BANK)}
                        {$SELECTED_BANK->get('account_currency')}
                    {/if}
                </span>
                <span>
                    .................................................................................
                    <span style="font-style: italic;"> (the “Purchase Amount”)</span>
                </span>
            </div>

            <div class="serials-box">(c) <span class="bolder-element">and thereafter to</span></div>

            <div style="margin-left: 5mm;">
                <div style="margin-top: 2mm;padding-left: 2mm;">
                    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                        <input type="checkbox" name="country_option">
                    {else}
                        {if isset($COUNTRY_OPTION)}
                            <span
                                style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">✔</span>
                        {/if}
                    {/if}
                    <span>deliver & store the above metal in a facility located in:</span>
                    <span> ...........................</span>
                    <span style="font-style: italic;">(Please specify country)</span>
                </div>

                <div style="margin-top: 2mm;padding-left: 2mm;">
                    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                        <input type="checkbox" name="address_option">
                    {else}
                        {if isset($ADDRESS_OPTION)}
                            <span
                                style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">✔</span>
                        {/if}
                    {/if}
                    <span>deliver the above metal to:</span>
                    <span> ....................................................................</span>
                    <span style="font-style: italic;">(Please specify full address)</span>
                </div>
            </div>

            <!-- SECTION 2 -->
            <div class="additional-section ">
                <strong>2.</strong><span class="bolder-element"> I/We make the payment of the above Purchase Amount:
                </span>
                <div style="padding-left: 5mm;margin-top:1.5mm">
                    <div>(a) <span class="bolder-element">from the following jurisdiction:</span></div>

                    <div style="padding-left: 5mm;">Country:
                        <span>..............................................................................................................................................</span>
                    </div>


                    <div style="margin:1.5mm 0;">(b) <span class="bolder-element">to GPM’s bank account </span>as
                        follows:</div>

                    <div style="padding-left: 5mm;">
                        <p class="bank-item">Bank Name:
                            <span style="font-style: italic;">
                                {if isset($SELECTED_BANK)}
                                    {$SELECTED_BANK->get('bank_name')}
                                {/if}
                            </span>
                        </p>
                        <p class="bank-item">Bank Address:
                            <span style="font-style: italic;">
                                {if isset($SELECTED_BANK)}
                                    {$SELECTED_BANK->get('bank_address')}
                                {/if}
                            </span>
                        </p>
                        <div class="bank-codes-container bank-item">
                            <p>Bank Code:
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('bank_code')}
                                    {/if}
                                </span>
                            </p>
                            <p>(Branch Code:
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('branch_code')}
                                    {/if}
                                </span>)
                            </p>
                            <p>Swift Code:
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('swift_code')}
                                    {/if}
                                </span>
                            </p>
                        </div>

                        <div class="bank-details-container bank-item">
                            <div class="bank-details-item">
                                <span>Account No:</span>
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('account_no')}
                                    {/if}
                                </span>
                            </div>
                            <div class="bank-details-item">
                                <span>Name of Account Holder:</span>
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('beneficiary_name')}
                                    {/if}
                                </span>
                            </div>
                        </div>

                        <div class="bank-details-container bank-item">
                            <div class="bank-details-item">
                                <span>Intermediary Bank:</span>
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('intermediary_bank')}
                                    {/if}
                                </span>
                            </div>
                            <div class="bank-details-item">
                                <span>Swift Code:</span>
                                <span style="font-style: italic;">
                                    {if isset($SELECTED_BANK)}
                                        {$SELECTED_BANK->get('intermediary_swift_code')}
                                    {/if}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION 3 -->
            <div class="additional-section">
                <span class="bolder-element">
                    3. I/We hereby elect the following Pricing Option (Please select one option):
                </span>

                <div style="margin-left: 5mm; margin-top:2mm;">

                    <div>
                        <label>
                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input type="radio" name="pricing_option" value="1" {if $PRICING_OPTION eq '1'}checked{/if}>
                            {else}
                                {if $PRICING_OPTION eq '1'}
                                    <span
                                        style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">✔</span>
                                {/if}
                            {/if}
                            Pricing Option 1 (as defined in Clause 3.3.1)
                        </label>
                    </div>

                    <div>
                        <label>
                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input type="radio" name="pricing_option" value="2" {if $PRICING_OPTION eq '1'}checked{/if}>
                            {else}
                                {if $PRICING_OPTION eq '1'}
                                    <span
                                        style="font-size: 3.5mm; border:1px solid #000; padding:2px 2px; display:inline-block;height:5mm;width:5mm;line-height:3.5mm;">✔</span>
                                {/if}
                            {/if}
                            Pricing Option 2 (as defined in Clause 3.3.2)
                        </label>
                    </div>
                </div>

                <div class="additional-section" style="margin-top:4mm;">
                    <span class="bolder-element">4. This Purchase & Storage Order and any agreement with GPM resulting
                        therefrom shall be subject to and governed by
                        the terms and conditions of the Customer Metal Agreement executed and entered into by and
                        between
                        me/us and
                        Global Precious Metals Pte. Ltd.:</span>
                </div>

                <!-- BANK DETAILS -->
                <div class="details-container">

                    <!-- SIGNATURE SECTION -->
                    <div class="signature-section">
                        <div class="signature-section-item">
                            <div class="signature-section-left">Place: <span class="line"
                                    style="font-style: italic;">{$RECORD_MODEL->get('mailingcountry')}</span></div>
                            <div class="signature-section-right">
                                Date: <span style="font-style: italic;">{$smarty.now|date_format:"%m/%d/%Y"}</span>
                            </div>
                        </div>

                        <div class="signature-section-item">
                            <div class="signature-section-left">
                                Signed by: <span class="long-line" style="font-style: italic;">
                                    {$RECORD_MODEL->get('firstname')}
                                    {$RECORD_MODEL->get('lastname')}</span>
                            </div>
                            <div class="signature-section-right">
                                On behalf of: <span class="line">................................</span>
                            </div>
                        </div>

                        <div style="margin-top:10mm;">
                            <div class="signature-line">...............................................</div><br>
                            Signature
                        </div>

                    </div>

        </section>
    </div>


    <script>
        document.getElementById('downloadBtn').addEventListener('click', function(e) {

            const checked = document.querySelector('input[name="pricing_option"]:checked');

            const countryOption = document.querySelector('input[name="country_option"]');
            const addressOption = document.querySelector('input[name="address_option"]');

            // if (!checked) return;

            const url = new URL(this.href);
            if (checked) url.searchParams.set('pricing_option', checked.value);
            if (countryOption && countryOption.checked) {
                url.searchParams.set('countryOption', '1');
            } else {
                url.searchParams.delete('countryOption');
            }

            if (addressOption && addressOption.checked) {
                url.searchParams.set('addressOption', '1');
            } else {
                url.searchParams.delete('addressOption');
            }

            this.href = url.toString();
        });
    </script>
</body>

</html>