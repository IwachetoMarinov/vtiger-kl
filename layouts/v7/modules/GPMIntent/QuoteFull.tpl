<!DOCTYPE html>
<html>

<head>
    <title>GPM QUOTE</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {include file="partials/printCSS.tpl"|vtemplate_path:GPMIntent}
</head>

<body>
    {if $ENABLE_DOWNLOAD_BUTTON}
        {include file="partials/download.tpl"|vtemplate_path:GPMIntent}
    {/if}
    <div class="printAreaContainer">
        <div class="full-width">
            <table class="print-tbl">
                <tr>
                    <td style="height: 28mm;">
                        <img src='layouts/v7/modules/Contacts/resources/gpm-new-logo.png'
                            style="max-height: 100%; float:right;width: 154px;">
                        <div style="font-size:11pt;margin-top: 14px;margin-bottom: 32px">
                            {$INTENT->get('contact_erp_no')}<br>
                            {$RECORD_MODEL->get('firstname')} {$RECORD_MODEL->get('lastname')}<br>
                            {if !empty($RECORD_MODEL->get('cf_968'))} {$RECORD_MODEL->get('cf_968')}<br>{/if}
                            {if !empty($RECORD_MODEL->get('mailingstreet'))}
                                {$RECORD_MODEL->get('mailingstreet')}<br>
                            {/if}
                            {if !empty($RECORD_MODEL->get('cf_970'))} {$RECORD_MODEL->get('cf_970')}<br>{/if}
                            {if empty($RECORD_MODEL->get('mailingpobox'))}
                                {if !empty($RECORD_MODEL->get('mailingcity')) && !empty($RECORD_MODEL->get('mailingzip')) }
                                    {$RECORD_MODEL->get('mailingcity')} {$RECORD_MODEL->get('mailingzip')}<br>
                                {else if !empty($RECORD_MODEL->get('mailingcity'))}
                                    {$RECORD_MODEL->get('mailingcity')}<br>
                                {else}
                                    {if !empty($RECORD_MODEL->get('mailingzip'))}{$RECORD_MODEL->get('mailingzip')}<br>{/if}
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
                            {if !empty($RECORD_MODEL->get('lane'))}
                                {$RECORD_MODEL->get('lane')}, {$RECORD_MODEL->get('city')}<br>
                                {if !empty($RECORD_MODEL->get('code'))}
                                    P.O. Box {$RECORD_MODEL->get('code')}<br>
                                {/if}
                                {$RECORD_MODEL->get('country')}
                            {/if}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height: 10mm; text-decoration: underline;text-align: center">
                        <strong>QUOTATION</strong>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        <table class="activity-tbl" style="margin-bottom:5mm;margin-top:5mm">
                            <tr>
                                <th style="width:25%;text-align:center">DATE</th>
                                <th style="width:25%;text-align:center">ORDER</th>
                                <th style="width:25%;text-align:center">LOCATION</th>
                            </tr>
                            <tr>
                                <td style="text-align:center">{date('Y-m-d',strtotime($INTENT->get('modifiedtime')))}
                                </td>
                                <td style="text-align:center">{$INTENT->get('gpm_order_type')}</td>
                                <td style="text-align:center">{$INTENT->get('gpm_order_location')}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                        <table class="activity-tbl" style="width: 40%">
                            <tr>
                                <th colspan="2">REFERENCE VALUE</th>
                            </tr>
                            <tr>
                                <th>{vtranslate($INTENT->get('gpm_metal_type'),'MetalPrice')}</th>
                                <td style="text-align:center">
                                    {$INTENT_CURRENCY}
                                    {number_format($INTENT->get('indicative_spot_price'),2, '.', ',')} / oz.
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;font-size: 9pt">
                       All amounts in {if isset($INTENT_CURRENCY)}{$INTENT_CURRENCY}{else}currency{/if}
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 9pt; height: 168mm; vertical-align: top;">
                        <table class="activity-tbl" style="margin-bottom:5mm">
                            <tr>
                                <th style="width:10%;">QTY</th>
                                <th style="width:40%;">DESCRIPTION</th>
                                <th style="width:10%;">{$DISCOUNT}</th>
                                <th style="width:12%;text-align:center"> {$INTENT_CURRENCY} / UNIT</th>
                                <th style="width:8%;text-align:center">FINE OZ.</th>
                                <th style="width:20%;text-align:center">TOTAL</th>
                            </tr>
                            {foreach item=PRODUCT key=cnt from=$RELATED_PRODUCTS}
                                {assign var=METAL value=Vtiger_Record_Model::getInstanceById($PRODUCT->get('gpmmetalid'), 'Assets')}

                                <tr class="no-border">
                                    <td style="vertical-align: top;">
                                        {if $METAL->get('assetname') != 'Storage Charges'}{number_format($PRODUCT->get('qty'),0)}{/if}
                                    </td>
                                    <td style='vertical-align: top;'>
                                        {$METAL->get('assetname')}
                                    </td>
                                    <td style='vertical-align:top;text-align:center'>
                                        {if $METAL->get('assetname') != 'Storage Charges'}
                                        {$PRODUCT->get('premium_or_discount')}%{/if}
                                    </td>
                                    <td style='vertical-align:top;text-align:center'>
                                        {if $METAL->get('assetname') != 'Storage Charges'}
                                        {number_format($PRODUCT->get('value_usd')/$PRODUCT->get('qty'),2)}{/if}
                                    </td>
                                    {if $METAL->get('gpm_metal_type') eq 'CRYPTO'}
                                        <td style='vertical-align: top;text-align:center'>
                                            {number_format($PRODUCT->get('qty'),0)}
                                        </td>
                                    {else}
                                        <td style='vertical-align: top;text-align:center'>
                                            {if $METAL->get('assetname') != 'Storage Charges'}
                                            {number_format($PRODUCT->get('fine_oz'),4)}{/if}
                                        </td>
                                    {/if}
                                    {if count($RELATED_PRODUCTS) lt 5 && $cnt eq count($RELATED_PRODUCTS)-1}
                                        <td style='height:50mm;vertical-align: top;text-align:right'>
                                            {number_format($PRODUCT->get('value_usd'),2)}

                                        {else}
                                        <td style='vertical-align: top;text-align:right'>
                                            {number_format($PRODUCT->get('value_usd'),2)}

                                        {/if}
                                    </td>
                                </tr>

                            {/foreach}
                            <tr>
                                <th colspan="5">TOTAL QUOTE VALUE</th>
                                <td style='text-align:right'><strong>
                                        {$INTENT_CURRENCY}
                                        {number_format($INTENT->get('total_amount'),2)}</strong></td>
                            </tr>
                        </table>
                        <br>
                        <div>
                            The above quote is indicative and is based on the current spot price. Any final quote will
                            depend on the prevailing market price and conditions.
                            If there are any aspects about our offering or pricing you would like to discuss, please do
                            not hesitate to contact us.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style='font-size: 8pt;font-weight: bold;position: absolute;bottom: 14px;'>
                        {if isset($COMPANY)}
                            {$COMPANY->get('company_name')} {if !empty($COMPANY->get('company_reg_no'))}(Co. Reg. No.
                            {$COMPANY->get('company_reg_no')}){/if}<br>
                            {$COMPANY->get('company_address')}<br>
                            T: {$COMPANY->get('company_phone')} {if !empty($COMPANY->get('company_fax'))}| Fax:
                            {$COMPANY->get('company_fax')} {/if} | {$COMPANY->get('company_website')}<br>
                        {/if}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>