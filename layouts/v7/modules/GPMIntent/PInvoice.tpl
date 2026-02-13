<!DOCTYPE html>
<html>

<head>
    <title>PROFORMA INVOICE</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {include file="partials/printCSS.tpl"|vtemplate_path:GPMIntent}
</head>

<body>
    {if $ENABLE_DOWNLOAD_BUTTON}
        {include file="partials/PiDownload.tpl"|vtemplate_path:GPMIntent}
    {/if}

    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">

                <!-- Customer header -->
                <tr>
                    <td style="height: 28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height:100%;float:right;width:154px;">
                        {if isset($RECORD_MODEL)}
                            <div style="font-size:11pt;margin-top:14px;margin-bottom:32px">
                                {$INTENT->get('cf_898')}<br>
                                {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}<br>
                                {if $RECORD_MODEL->get('cf_968')} {$RECORD_MODEL->get('cf_968')}<br>{/if}
                                {if $RECORD_MODEL->get('mailingstreet')} {$RECORD_MODEL->get('mailingstreet')}<br>{/if}
                                {if $RECORD_MODEL->get('cf_970')} {$RECORD_MODEL->get('cf_970')}<br>{/if}
                                {if !$RECORD_MODEL->get('mailingpobox')}
                                    {if $RECORD_MODEL->get('mailingcity') && $RECORD_MODEL->get('mailingzip')}
                                        {$RECORD_MODEL->get('mailingcity')} {$RECORD_MODEL->get('mailingzip')}<br>
                                    {elseif $RECORD_MODEL->get('mailingcity')}
                                        {$RECORD_MODEL->get('mailingcity')}<br>
                                    {else}
                                        {$RECORD_MODEL->get('mailingzip')}<br>
                                    {/if}
                                    {$RECORD_MODEL->get('mailingcountry')}
                                {else}
                                    {if $RECORD_MODEL->get('mailingcity')}
                                        P.O. Box {$RECORD_MODEL->get('mailingpobox')}, {$RECORD_MODEL->get('mailingcity')}<br>
                                    {else}
                                        P.O. Box {$RECORD_MODEL->get('mailingpobox')}<br>
                                    {/if}
                                    {if $RECORD_MODEL->get('mailingstate')}
                                        {$RECORD_MODEL->get('mailingstate')}, {$RECORD_MODEL->get('mailingcountry')}
                                    {else}
                                        {$RECORD_MODEL->get('mailingcountry')}
                                    {/if}
                                {/if}
                            </div>
                        {/if}
                    </td>
                </tr>

                <!-- Invoice header -->
                <tr>
                    <td style="height:10mm;text-decoration:underline;text-align:center"><strong>
                            PROFORMA INVOICE
                        </strong></td>
                </tr>

                {if isset($COMPANY) && $COMPANY->get('company_gst_no')}
                    <tr style="font-weight:bold;font-size:9pt">
                        <td>GST Reg No.: {$COMPANY->get('company_gst_no')}</td>
                    </tr>
                {/if}

                <!-- Invoice meta -->
                <tr>
                    <td style="text-align:right;font-size:9pt">
                        <table class="activity-tbl" style="margin-bottom:5mm;margin-top:5mm">
                            <tr>
                                <th style="text-align: center;">INVOICE NO</th>
                                <th style="text-align: center;">INVOICE DATE</th>
                                <th style="text-align: center;">DELIVERY DATE</th>
                                <th style="text-align: center;">ORDER</th>
                            </tr>
                            <tr>
                                <td style="text-align:center">PI/{date('Y')}/{$INTENT->get('intent_no')}</td>
                                <td style="text-align:center">{date('Y-m-d',strtotime($INTENT->get('modifiedtime')))}
                                </td>
                                <td style="text-align:center">{date('Y-m-d',strtotime($INTENT->get('modifiedtime')))}
                                </td>
                                <td style="text-align:center">{$INTENT->get('gpm_order_type')}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="text-align:right;font-size:9pt">All amounts in {$INTENT_CURRENCY}</td>
                </tr>

                <!-- Product table -->
                <tr>
                    <td style="font-size:9pt;height:168mm;vertical-align:top;">
                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th>QTY</th>
                                <th>DESCRIPTION</th>
                                <th>{$INTENT_CURRENCY} / UNIT</th>
                                <th>FINE OZ.</th>
                                <th>TOTAL {$INTENT_CURRENCY}</th>
                            </tr>

                            {foreach item=PRODUCT key=cnt from=$RELATED_PRODUCTS}
                                {assign var=METAL value=Vtiger_Record_Model::getInstanceById($PRODUCT->get('gpmmetalid'), 'Assets')}
                                <tr class="no-border">
                                    <td>{if $METAL->get('assetname') ne 'Storage Charges'}{number_format($PRODUCT->get('qty'),0)}{/if}
                                    </td>
                                    <td>{$METAL->get('assetname')}</td>
                                    <td style="text-align:center">
                                        {if $METAL->get('assetname') ne 'Storage Charges'}
                                            {number_format($PRODUCT->get('value_usd')/$PRODUCT->get('qty'),2)}
                                        {/if}
                                    </td>
                                    {if $METAL->get('gpm_metal_type') eq 'CRYPTO'}
                                        <td style="text-align:center">{number_format($PRODUCT->get('qty'),0)}</td>
                                    {else}
                                        <td style="text-align:center">
                                            {if $METAL->get('assetname') ne 'Storage Charges'}
                                                {number_format($PRODUCT->get('fine_oz'),3)}
                                            {/if}
                                        </td>
                                    {/if}
                                    <td style="text-align:right">{number_format($PRODUCT->get('value_usd'),2)}</td>
                                </tr>
                            {/foreach}

                            <tr>
                                <th colspan="4">TOTAL INVOICE AMOUNT</th>
                                <td style="text-align:right"><strong>{$INTENT_CURRENCY}
                                        {number_format($INTENT->get('total_amount'),2)}</strong></td>
                            </tr>
                        </table>

                        {assign var="exchangeRateInfo" value=MASForex_Record_Model::getLatestExchangeRateByCurrency($INTENT->get('modifiedtime'), $INTENT_CURRENCY)}

                        <!-- Remarks -->
                        {* {if isset($COMPANY) && $COMPANY->get('company_gst_no')} *}

                            {if !empty($exchangeRateInfo) && isset($exchangeRateInfo['rate'])}
                                <div style="margin-bottom: 2mm;">
                                    {if $INTENT_CURRENCY eq 'SGD'}
                                        *Remarks: USD/SGD exchange rate at SGD {$exchangeRateInfo['rate']} / USD
                                    {else}
                                        *Remarks: {$INTENT_CURRENCY}/SGD exchange rate at SGD
                                        {$exchangeRateInfo['rate']} / {$INTENT_CURRENCY}
                                    {/if}
                                </div>
                            {/if}
                        {* {/if} *}

                        <!-- Bank info -->
                        {if isset($SELECTED_BANK) && $SELECTED_BANK}
                            {assign var=ROUTING value=$SELECTED_BANK->get('bank_routing_no')}
                        {else}
                            {assign var=ROUTING value=''}
                        {/if}
                        <div>
                            Please transfer the payment net of charges to our bank account:<br><br>
                            Beneficiary: {if $SELECTED_BANK}{$SELECTED_BANK->get('beneficiary_name')}{/if}<br>
                            Account No: {if $SELECTED_BANK}{$SELECTED_BANK->get('account_no')}
                            {$SELECTED_BANK->get('account_currency')}{/if}<br>
                            {if $ROUTING && trim(count_chars(strtolower($ROUTING),3)) == 'x'}
                                Bank Code: {$SELECTED_BANK->get('bank_code')}<br>
                                Branch Code: {$SELECTED_BANK->get('branch_code')}<br>
                            {else}
                                Routing No: {$ROUTING}<br>
                            {/if}
                            Bank: {if $SELECTED_BANK}{$SELECTED_BANK->get('bank_name')}{/if}<br>
                            Bank Address: {if $SELECTED_BANK}{$SELECTED_BANK->get('bank_address')}{/if}<br>
                            Swift Code: {if $SELECTED_BANK}{$SELECTED_BANK->get('swift_code')}{/if}<br>
                        </div>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="font-size:8pt;font-weight:bold;position:absolute;bottom:14px;">
                        {if isset($COMPANY)}
                            {$COMPANY->get('company_name')}
                            {if $COMPANY->get('company_reg_no')}(Co. Reg. No. {$COMPANY->get('company_reg_no')}){/if}<br>
                            {$COMPANY->get('company_address')}

                            {if $COMPANY->get('city')}, {$COMPANY->get('city')}{/if}
                            {if $COMPANY->get('state')}, {$COMPANY->get('state')}{/if}
                            {if $COMPANY->get('code')}, {$COMPANY->get('code')}{/if}
                            {if $COMPANY->get('country')}, {$COMPANY->get('country')}{/if}
                            <br>
                            T: {$COMPANY->get('company_phone')}
                            {if $COMPANY->get('company_fax')} | Fax: {$COMPANY->get('company_fax')}{/if}
                            | {$COMPANY->get('company_website')}<br>
                        {/if}
                    </td>
                </tr>

            </table>
        </div>
    </div>
</body>

</html>