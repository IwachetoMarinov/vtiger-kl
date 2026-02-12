<!DOCTYPE html>
<html>

<head>
    <title>SHIPMENT & STORAGE ORDER</title>
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
            font-size: 9pt;
            color: #666;
        }

        #downloadBtn,
        .select2-container {
            font-size: 12pt;
        }

        .printAreaContainer {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 6mm;
            padding-top: 3mm;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 1mm;
        }

        .table-heading {
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
            margin: 2mm 0;
            line-height: 1.4;
        }

        .country-options {
            display: flex;
            gap: 5mm;
            margin-top: 2mm;
            align-items: center;
        }

        input[type="checkbox"] {
            width: 5mm;
            height: 5mm;
            vertical-align: middle;
            margin-right: 1.5mm;
            accent-color: #000;
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

        /* Signature Section */
        .signature-section {
            margin-top: 2mm;
            padding: 0 4mm;
        }

        .signature-section-item {
            display: flex;
            justify-content: space-between;
            margin-top: 2mm;
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
            max-height: 183px;
            height: 183px;
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
            margin-top: 3.5mm;
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

        .custom-country {
            display: flex;
            align-items: center;
            margin-top: 2mm;
            gap: 2mm;
        }

        .custom-country-input {
            border: none;
            possition: relative;
            padding-bottom: 1mm;
            border-bottom: 1px solid #000;
        }

        .bank-half-item {
            width: 48%;
        }

        /* Remove focus outline from custom country input */
        .custom-country-input:focus {
            outline: none;
        }

        .editable-input-wrapper {
            display: flex;
            align-items: center;
            gap: 2mm;
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

        .pdf-checkbox {
            display: inline-block;
            width: 5mm;
            height: 5mm;
            border: 0.3mm solid transparent;
            vertical-align: middle;
            position: relative;
            margin-right: 2mm;
            box-sizing: border-box;
        }

        .pdf-checkbox-label {
            display: inline-block;
            vertical-align: middle;
            margin-right: 5mm;
        }
    </style>
</head>

<body>
    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}

        {assign var=selected_bank value=""}

        {if isset($SELECTED_BANK) && $SELECTED_BANK && method_exists($SELECTED_BANK, 'getId')}
            {assign var=selected_bank value=$SELECTED_BANK->getId()}
        {/if}

        <script type="text/javascript" src="layouts/v7/lib/jquery/jquery.min.js"></script>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
        <script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script>

        <ul style="list-style-type:none;margin:0;padding:0;overflow:hidden;background-color:#333;">
            <li style="float:right">
                <a id="downloadBtn"
                    style="display:block;color:white;text-align:center;padding:14px 16px;text-decoration:none;background-color:#bea364;"
                    href="index.php?module=Contacts&view=StockTransferOrderView&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo|default:''}&bank={$selected_bank}&PDFDownload=true&hideCustomerInfo={$smarty.request.hideCustomerInfo|default:0}">
                    Download
                </a>
            </li>

            <li style="float: right;margin-top: 5px;margin-right: 5px;width: 198px;">
                <select class="inputElement select2" name="bank_accounts" id="bank_accounts">
                    <option value="">Select Bank Account</option>
                    {foreach item=account from=$ALL_BANK_ACCOUNTS}
                        <option {if $smarty.request.bank  eq $account->getId() } selected {/if} value="{$account->getId()}">
                            {$account->get('bank_alias_name')}</option>
                    {/foreach}
                </select>
            </li>
        </ul>

        {literal}
            <style>
                .select2-container .select2-choice>.select2-chosen {
                    width: 171px;
                }
            </style>
            <script>
                $(document).ready(function() {
                    $('.select2').select2();
                });
                jQuery("body").on('change', '#bank_accounts', function(e) {
                    var element = jQuery(e.currentTarget);
                    var bankId = Number(element.val());
                    window.location.replace(window.location.href.split('&bank=')[0] + '&bank=' + bankId);
                });
            </script>
        {/literal}
    {/if}

    <div class="printAreaContainer">

        <!-- HEADER -->
        <table>
            <tr>
                <td class="logo">
                    {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                        <img src="layouts/v7/modules/Contacts/resources/gpm-new-logo.png" width="100%">
                    {else}
                        <img src="file:///var/www/html/layouts/v7/modules/Contacts/resources/gpm-new-logo.png" width="100%">
                    {/if}
                </td>
                <td class="title"></td>
                <td style="width:25mm;"></td>
            </tr>
        </table>

        <table class="header-table table-heading">
            <tr>
                <td class="title" style="text-decoration: underline; text-align: center;">SHIPMENT & STORAGE ORDER</td>
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
                            <br />
                            {if isset($COMPANY)}
                                {if !empty($COMPANY->get('city'))}
                                    {$COMPANY->get('city')},
                                {/if}
                                {$COMPANY->get('state')} {$COMPANY->get('code')}<br>
                                {$COMPANY->get('country')}
                            {/if}
                        </div>
                    </div>
                    <div class="number-container">
                        {if isset($COMPANY)}
                            {if !empty($COMPANY->get('email'))}
                                <p>EMAIL:<span style="font-style: italic;"> {$COMPANY->get('email')}</span></p>
                            {/if}
                            {if !empty($COMPANY->get('company_phone'))} <p>or PHONE: <span
                                    style="font-style: italic;">{$COMPANY->get('company_phone')}</span> or</p> {/if}
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 1 -->
        <section class="main-table">
            <div class="additional-section bolder-element" style="margin-top: 0mm;">
                1. I/We hereby instruct GPM:
            </div>


            <div class="section-title">
                (a) <span class="bolder-element"> to ship</span> in its name and on my/our behalf the following physical
                precious metals (please indicate the quantity of bars in the
                relevant box):
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

                {foreach from=$metals item=m key=mi}
                    <tr>
                        <td class="metal-row-label">{$m}</td>

                        {foreach from=$weights item=w key=wi}
                            <td>
                                <input type="text" class="custom-editable-input custom-editable-table-input"
                                    name="metal_{$mi}_weight_{$wi}"
                                    style="width:100%; border:0; outline:none; background:transparent;" />
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}
            </table>

            <!-- SERIALS BOX -->
            <div class="serials-box">
                Description of the precious metals <span style="font-style: italic;">(Please specify type, refiner,
                    serial numbers, fineness):</span>
                <div style="margin-top: 2mm;">
                    <input type="text" name="description" class="custom-editable-input full-width" />
                </div>
            </div>

            <!-- SECTION 3 -->
            <div class="additional-section" style="margin-left:2mm;">
                <div style="margin-top:1mm;">
                    From <span style="font-style: italic;">(Please specify pick-up location):</span>
                    <div style="margin-top: 2mm;">
                        <input type="text" name="from_location" class="custom-editable-input full-width" />
                    </div>
                </div>

                <div style="margin-top:3mm;">
                    To <span style="font-style: italic;">(Please specify delivery location):</span>
                    <div style="margin-top: 2mm;">
                        <input type="text" name="to_location" class="custom-editable-input full-width" />
                    </div>
                </div>

                <div style="margin-top:2mm;">
                    (b) <span class="bolder-element">and thereafter to store the above precious metal in a segregated
                        storage in:</span>
                    <div class="country-options">
                        <div>

                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input class="country-checkbox" type="checkbox" name="1"> Singapore
                            {else}
                                <span class="pdf-checkbox"></span>
                                <span class="pdf-checkbox-label">Singapore</span>
                            {/if}
                        </div>
                        <div>
                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input class="country-checkbox" type="checkbox" name="2"> Switzerland
                            {else}
                                <span class="pdf-checkbox"></span>
                                <span class="pdf-checkbox-label">Switzerland</span>
                            {/if}
                        </div>
                        <div>
                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input class="country-checkbox" type="checkbox" name="3"> Hong Kong
                            {else}
                                <span class="pdf-checkbox"></span>
                                <span class="pdf-checkbox-label">Hong Kong</span>
                            {/if}
                        </div>
                        <div>
                            {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                <input class="country-checkbox" type="checkbox" name="4"> Dubai
                            {else}
                                <span class="pdf-checkbox"></span>
                                <span class="pdf-checkbox-label">Dubai</span>
                            {/if}
                        </div>
                    </div>

                    <div>
                        <div>
                            <div class="custom-country">
                                {if !isset($smarty.request.PDFDownload) || $smarty.request.PDFDownload neq true}
                                    <input class="country-checkbox" type="checkbox" name="5">
                                    <div>
                                        Other country or location (Please specify):
                                        <input type="text" class="custom-country-input" value="{$CUSTOM_COUNTRY|default:''}"
                                            style="width:60mm; margin-left:2mm;" />
                                    </div>
                                {else}
                                    <span class="pdf-checkbox"></span>
                                    <span class="pdf-checkbox-label">Other country or location (Please specify):
                                        {$CUSTOM_COUNTRY|default:''}</span>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2 -->
            <div class="additional-section ">
                <div style="margin-bottom:2mm;" class="bolder-element">
                    2. I/We agree that: (1) the above shipment will be effected by GPM after the Shipment Fee has been
                    agreed by and between me/us; and (2) GPM will serve me/us an invoice for the payment of the Storage
                    Fees upon delivery of the above bullion at the elected storage location (if applicable).
                </div>
            </div>

            <!-- SECTION 3 -->
            <div class="additional-section ">
                <div style="margin-bottom:2mm;" class="bolder-element">
                    3. I/We make the payment of the Shipping and Storage Fees:
                </div>

                <div style="margin-left: 2mm;">
                    <p class="bolder-element">(a) from the following jurisdiction:</p>
                    <div style="margin-left: 5mm; margin-top:2mm; font-weight: normal;">
                        <span> Country:</span>
                        <input type="text" name="country" class="custom-editable-input" />
                    </div>
                </div>

                <div style="margin-left: 2mm; margin-top:2mm;">
                    <p>(b) to <span class="bolder-element">GPM’s bank account</span> as follows:</p>
                    <!-- BANK DETAILS -->
                    <div style="padding-left: 5mm;">
                        {if $SELECTED_BANK}
                            {assign var=iban value=$SELECTED_BANK->get('iban_no')|lower|replace:' ':''}
                            {assign var=bank_routing_no value=$SELECTED_BANK->get('bank_routing_no')|lower|replace:' ':''}

                            {if isset($SELECTED_BANK) && $SELECTED_BANK && method_exists($SELECTED_BANK, 'getId')}
                                <input type="hidden" class="selected-bank" value="{$SELECTED_BANK->getId()}">
                            {/if}

                            <div>
                                Please transfer the payment net of charges to our bank account:<br>
                                Beneficiary: {$SELECTED_BANK->get('beneficiary_name')}<br>
                                Account No: {$SELECTED_BANK->get('account_no')}
                                {$SELECTED_BANK->get('account_currency')}<br>

                                {if $iban neq 'x'}
                                    IBAN: {$SELECTED_BANK->get('iban_no')}<br>
                                {/if}

                                Bank: {$SELECTED_BANK->get('bank_name')}<br>
                                Bank Address: {$SELECTED_BANK->get('bank_address')}<br>
                                Swift Code: {$SELECTED_BANK->get('swift_code')}<br>

                                {if $bank_routing_no neq 'x'}
                                    Bank Code: {$SELECTED_BANK->get('bank_code')}<br>
                                    Branch Code: {$SELECTED_BANK->get('branch_code')}<br>
                                {else}
                                    Routing No: {$SELECTED_BANK->get('bank_routing_no')}<br>
                                {/if}

                                <br>

                                {if !empty($SELECTED_BANK->get('intermediary_bank'))}
                                    Intermediary Bank: {$SELECTED_BANK->get('intermediary_bank')}<br>
                                    Swift Code: {$SELECTED_BANK->get('intermediary_swift_code')}<br>
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>


            <!-- SECTION 2 -->
            <div class="additional-section ">
                <div style="margin-bottom:2mm;" class="bolder-element">
                    4. This Shipment & Storage Order and any agreement with GPM resulting therefrom shall be subject to
                    and governed by the terms and conditions of the Customer Metal Agreement executed and entered into
                    by and between me/us and Global Precious Metals Pte. Ltd.
                </div>
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

                <div style="margin-top:10mm;">
                    <div class="signature-line">...............................................</div><br>
                    Signature
                </div>
            </div>
    </div>


    </section>
    </div>

    <script>
        const checkboxes = document.querySelectorAll('.country-checkbox');

        if (checkboxes?.length) {
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('click', function(e) {
                    const name = e.target?.getAttribute('name');

                    const customInput = document.querySelector('.custom-country-input');

                    if (name == '5') {
                        if (customInput) customInput.focus();
                    } else {
                        if (customInput) customInput.value = '';
                    }

                    // Remove checked state from other checkboxes
                    checkboxes.forEach(function(box) {
                        if (box !== e.target) box.checked = false;
                    });
                });
            });
        }


        document.getElementById('downloadBtn').addEventListener('click', function(e) {

            const checked = document.querySelector('input.country-checkbox:checked');

            const countryType = checked ? checked.getAttribute('name') : null;

            const customInput = document.querySelector('.custom-country-input');

            const url = new URL(this.href);

            if (countryType) {
                url.searchParams.set('countryOption', countryType);

                if (countryType == '5' && customInput) {
                    url.searchParams.set('customCountry', customInput.value || '');
                }
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