<!DOCTYPE html>
<html>

<head>
    <title>CERTIFICATE OF OWNERSHIP</title>

    <style>
        /* ================== FONTS ================== */
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            src: local('Open Sans'),
                url(https://themes.googleusercontent.com/static/fonts/opensans/v6/cJZKeOuBrn4kERxqtaUH3T8E0i7KZn-EPnyo3HZu7kw.woff) format('woff');
        }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 700;
            src: local('Open Sans Bold'),
                url(https://themes.googleusercontent.com/static/fonts/opensans/v6/k3k702ZOKiLJc3WVjuplzHhCUOGz7vYGh680lGh-uXM.woff) format('woff');
        }

        /* ================== GLOBAL RESET ================== */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: 'Open Sans', Arial, sans-serif;
        }

        /* ================== A4 PRINT CANVAS ================== */
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

            body {
                overflow: hidden;
            }
        }

        /* ================== MAIN CONTAINER ================== */
        .printAreaContainer {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            position: relative;
            font-size: 10pt;
            overflow: hidden;
        }

        .printAreaContainer * {
            font-family: 'Open Sans';
            color: #666;
        }

        /* ================== TABLE ================== */
        .print-tbl {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .print-tbl td {
            vertical-align: top;
        }

        /* ================== TEXT HELPERS ================== */
        .print-txt-center {
            text-align: center;
        }

        .print-txt-left {
            text-align: left;
        }

        .print-txt-right {
            text-align: right;
        }

        /* ================== HEADING ================== */
        .cerHeading {
            text-align: center;
            margin: 12px 0;
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
            font-size: 14pt;
            color: #9e9d9d;
        }

        /* ================== QR CODE ================== */
        #QRCode {
            text-align: center;
        }

        #QRCode img {
            display: block;
            margin: 0 auto;
            width: 60mm;
            /* HARD LIMIT */
            max-width: 100%;
            height: auto;
        }

        #QRCode a {
            display: inline-block;
            max-width: 100%;
            font-size: 9pt;
            color: black;
            text-decoration: none;
            word-break: break-all;
            /* prevents page overflow */
        }

        /* ================== CONTENT ================== */
        #content p {
            color: black;
            text-align: justify;
            margin-bottom: 10px;
        }

        /* ================== SIGNATURE ================== */
        #signature img {
            height: 80px;
            margin-top: 30px;
            display: block;
        }

        #signature p {
            color: black;
            margin-top: 5px;
        }

        /* ================== FOOTER (PINNED, NO FLOATS) ================== */
        #companyInfo {
            position: absolute;
            left: 10mm;
            bottom: 10mm;
            font-size: 8pt;
            max-width: 150mm;
        }

        #companyInfo p {
            margin: 0;
        }

        #logo {
            position: absolute;
            right: 10mm;
            bottom: 10mm;
        }

        #logo img {
            width: 72px;
            height: auto;
            display: block;
        }

        /* ================== PAGE BREAK SAFETY ================== */
        table,
        tr,
        td,
        #printAreaContainer,
        #QRCode,
        #heading,
        #allContent,
        #content,
        #signature,
        #companyInfo,
        #logo {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    </style>

</head>

<body style="margin: 0px;">
    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">
                <tr>
                    <td colspan="2">
                        <div id="QRCode" style="width: 100%">
                            <img src="{$site_URL}modules/HoldingCertificate/{$GUID}.png" alt="Please scan to verify"
                                style='margin: auto;display: block;'>
                            <p style="text-align: center;"><a
                                    style="text-decoration: none;color: black;text-align: center" target="_blank"
                                    href='https://certificates.global-precious-metals.com/id/{$GUID}'>https://certificates.global-precious-metals.com/id/{$GUID}</a>
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="heading">
                            <p class="cerHeading">CERTIFICATE OF OWNERSHIP</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style='height:170mm; vertical-align: top'>
                        <div id='allContent'>
                            <div id='content'>
                                <p style='text-align: justify;color: black;'>
                                    This certificate attests that {$RECORD_MODEL->get('salutationtype')}
                                    {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')} is the rightful
                                    owner of the following metals:<br><br>
                                    {foreach item=HOLDINGS key=location from=$ERP_HOLDINGS}
                                        {foreach item=HOLDING from=$HOLDINGS}
                                            <span style="display:inline-block; width: 20px;"></span>-<span
                                                style="display:inline-block; width: 20px;"></span>
                                            {number_format($HOLDING.quantity,0)} {$HOLDING.description}
                                            {if !empty($HOLDING.serial_no)}- Serial
                                            {$HOLDING.serial_no}{/if}<br>
                                        {/foreach}
                                    {/foreach}
                                    <br>
                                    Global Precious Metals is acting as an adviser to custodize the assets and that
                                    while the validity of this certificate if affirmed on our website, we can attest
                                    rightful ownership free of lien or pledges to us or any of our vendors. The scope of
                                    this certificate does not include any agreement between the rightful owner and
                                    parties unrelated to Global Precious Metals.<br><br>


                                    How to verify certificate validity:<br><br>
                                    <span style="display:inline-block; width: 20px;"></span>1.<span
                                        style="display:inline-block; width: 20px;"></span>Scan QR code or click on url
                                    on the top right of this document. The linked page will indicate the status of the
                                    certificate.<br>
                                    <span style="display:inline-block; width: 20px;"></span>2.<span
                                        style="display:inline-block; width: 20px;"></span>The page will also indicate
                                    the file checksum of the certificate (SHA256 protocol). If you have received this
                                    document as a PDF, use an online or offline SHA256 calculator to confirm that the
                                    validity refers to the document in your possession. You can also download the
                                    document from the page.<br>
                                </p>
                            </div>
                            <div id='signature'>
                                {* <img src="layouts/v7/modules/HoldingCertificate/nico_sig.png"
                                    alt="Please scan to verify" style='height:80px;margin-top: 40px;display: block; '> *}
                                <img src="{$site_URL}layouts/v7/modules/HoldingCertificate/nico_sig.png"
                                    alt="Please scan to verify" style='height:80px;margin-top: 40px;display: block; '>
                                <p style="color:black;">Nicolas Mathier<br>
                                    CEO</p>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style='width:75%;'>
                        <div id='companyInfo' style="font-size: 8pt; float:left;">
                            {if isset($COMPANY)}
                                <p style="font-size: 10pt;font-weight: bold;margin-bottom: 0px; margin-top: 0px">
                                    {$COMPANY->get('company_name')} {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg.
                                        No.
                                    {$COMPANY->get('company_reg_no')}){/if}</p>
                                <p style='margin-top: 0px; color: #9e9d9d;'>{$COMPANY->get('company_address')}<br>
                                    T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                                    {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                                </p>
                            {/if}
                        </div>
                    </td>
                    <td>
                        <div id='logo' style='float: right;'>
                            <img src="{$site_URL}layouts/v7/modules/HoldingCertificate/logo.png" style='width: 72px;' />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>