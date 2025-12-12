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
    {assign var="ASCURRENCY" value=$ACTIVITY_SUMMERY_CURRENCY|default:"USD"}
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
                                <option value="">Current Year</option>
                                {foreach from=$YEARS item=YEAR}
                                    <option value="{$YEAR}">{$YEAR}</option>
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
                                <th></th>
                                <th>DATE</th>
                                <th>DESCRIPTION</th>
                                <th>DEPOSIT</th>
                            </tr>
                        </thead>

                        <tbody>
                            {foreach item=TX from=$OROSOFT_TRANSACTION}

                                <tr class="listViewEntries1">
                                    <!-- Document number -->
                                    <td style="width: 140px;">
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
                                    </td>

                                    <!-- INV button (only for Sales/Purchase Invoice) -->
                                    {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD'])}
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
                                            <a href="index.php?module=Contacts&view=CollectionAcknowledgement&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;CA
                                                </button>
                                            </a>
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

                                    <!-- TC button -->
                                    {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD', 'MPD', 'MRD', 'FCT'])}
                                        <td>
                                            <a href="index.php?module=Contacts&view=TCPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;TC
                                                </button>
                                            </a>
                                        </td>
                                    {else}
                                        <td></td>
                                    {/if}

                                    {*  New MPD button *}
                                    {if in_array($TX.voucher_type, ['MRD', 'MPD'])}
                                        <td>
                                            <a href="index.php?module=Contacts&view=MPDPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;MPD
                                                </button>
                                            </a>
                                        </td>
                                    {else}
                                        <td></td>
                                    {/if}

                                    <!-- Date -->
                                    <td nowrap> {$TX.posting_date}</td>

                                    <!-- Type -->
                                    <td nowrap> {$TX.description}</td>

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