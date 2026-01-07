{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}

    {assign var="ASYEAR" value=$smarty.request.ActivtySummeryDate|default:""}
    {assign var="ASCURRENCY" value=$ACTIVITY_SUMMERY_CURRENCY|default:""}
    {assign var="RECID" value=$RECORD->getId()|default:0}

    <div class="summaryWidgetContainer">
        <div class="customwidgetContainer_ widgetContentBlock">

            <!-- ===================== HEADER ===================== -->
            <div class="widget_header row-fluid">
                <span class="span11 margin0px">
                    <div class="row-fluid">
                        <h4 class="display-inline-block" style="font-size: 18px; margin: 0; padding: 0;">
                            TRANSACTION HISTORY
                        </h4>

                        <!-- NEW CLEAN FLEX CONTAINER -->
                        <div style="float: right;display: flex;align-items: center;gap: 12px;">

                            <!-- Currency -->
                            <label style="margin: 0; font-size: 13px;">Currency</label>
                            <select id="currencySelect" class="inputElement select2" style="width: 110px;">
                                <option value="" {if $ASCURRENCY eq ''}selected{/if}>Select</option>

                                {foreach item=CURRENCY from=$CLIENT_CURRENCY}
                                    <option value="{$CURRENCY}" {if $ASCURRENCY eq $CURRENCY}selected{/if}>{$CURRENCY}</option>
                                {/foreach}

                            </select>

                            <!-- Year -->
                            <label style="margin: 0; font-size: 13px;">By Year</label>
                            <select id="ActivtySummeryDate" class="inputElement select2" style="width: 110px;">
                                <option value="">Select Year</option>
                                {foreach from=$YEARS item=YEAR}
                                    <option value="{$YEAR}" {if $ASYEAR eq $YEAR}selected{/if}>{$YEAR}</option>
                                {/foreach}
                            </select>

                            <!-- Download button -->
                            <a href="index.php?module=Contacts&view=ActivtySummeryPrintPreview&record={$RECID}&ActivtySummeryDate={$ASYEAR}&ActivtySummeryCurrency={$ASCURRENCY}"
                                target="_blank">
                                <button class="btn btn-default" type="button">
                                    <span class="fa fa-download"></span>&nbsp;Download
                                </button>
                            </a>

                        </div> <!-- END FLEX -->

                    </div>
                </span>
            </div>


            <!-- ===================== CONTENT TABLE ===================== -->
            <div class="widget_contents">
                <div class="relatedContents contents-bottomscroll" style="border: none; width: 100%;">

                    <table class="table table-strip listViewEntriesTable">
                        <thead>
                            <tr class="listViewHeaders">
                                <th>DOCUMENT NO</th>
                                <th></th>
                                <th></th>
                                <th>DATE</th>
                                <th>DESCRIPTION</th>
                                <th>DEPOSIT</th>
                            </tr>
                        </thead>

                        <tbody>
                            {foreach item=TX from=$OROSOFT_TRANSACTION}

                                <tr class="listViewEntries1">
                                    <!-- Document number new hyperlink for different type of transaction -->
                                    <td style="width: 140px;">
                                        {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD'])}
                                            <a href="index.php?module=Contacts&view=TCPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    Trade Confirmation
                                                </button>
                                            </a>

                                        {else if in_array($TX.voucher_type, ['MPD', 'MRD'])}
                                            {assign var="docLabel" value="Metal Payment Delivery"}
                                            {if $TX.voucher_type == 'MRD'}
                                                {assign var="docLabel" value="Metal Receipt Delivery"}
                                            {/if}
                                            <a href="index.php?module=Contacts&view=MPDPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    {$docLabel}
                                                </button>
                                            </a>

                                        {else if in_array($TX.voucher_type, ['DN', 'CN'])}
                                            {assign var="debitName" value="Debit Note"}
                                            {if $TX.voucher_type == 'CN'}
                                                {assign var="debitName" value="Credit Note"}
                                            {/if}

                                            <a href="index.php?module=Contacts&view=NotePrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    {$debitName}
                                                </button>
                                            </a>

                                        {else if in_array($TX.voucher_type, ['FCT'])}
                                            {* todo FCT logic *}
                                            <button type="button" class="btn btn-default module-buttons">
                                                Forex Confirmation
                                            </button>

                                        {else}
                                            <button type="button" class="btn btn-default module-buttons">
                                                {$TX.voucher_no}
                                            </button>
                                        {/if}

                                    </td>



                                    {* <td style="width: 140px;">
                                        {if $TX.voucher_type == 'SAL'}
                                            <a href="index.php?module=Contacts&view=CollectionAcknowledgement&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    {$TX.voucher_no}
                                                </button>
                                            </a>
                                        {else}
                                            {$TX.voucher_no}
                                        {/if}
                                    </td> *}

                                    <!-- INV button (only for Sales/Purchase Invoice) -->
                                    {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD', 'DN'])}
                                        <td>
                                            <a href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;INV
                                                </button>
                                            </a>
                                        </td>
                                    {else if in_array($TX.voucher_type, ['MPD'])}
                                        <td>
                                            <a href="index.php?module=Contacts&view=ViewCR&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;CR
                                                </button>
                                            </a>
                                        </td>
                                    {else}
                                        <td></td>
                                    {/if}

                                    {* Collection Acknowlegement button *}
                                    <td>
                                        {if in_array($TX.voucher_type, ['MPD', 'SAL'])}
                                            <a href="index.php?module=Contacts&view=CollectionAcknowledgement&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;CA
                                                </button>
                                            </a>
                                        {/if}
                                    </td>

                                    <!-- Date -->
                                    <td nowrap> {$TX.document_date}</td>

                                    <!-- Type -->
                                    <td nowrap>
                                        {$TX.description}
                                        {* - {$TX.voucher_type} *}
                                        {* - {$TX.table_name} *}
                                    </td>

                                    <!-- Amount -->
                                    <td nowrap>
                                        {number_format($TX.amount_in_account_currency, 2, '.', ',')}
                                    </td>
                                </tr>

                            {/foreach}
                        </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>

{/strip}