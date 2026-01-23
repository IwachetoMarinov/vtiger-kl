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
    {assign var="START_DATE" value=$smarty.request.start_date|default:""}
    {assign var="END_DATE" value=$smarty.request.end_date|default:""}

    {assign var="ASCURRENCY" value=$ACTIVITY_SUMMERY_CURRENCY|default:""}
    {assign var="RECID" value=$RECORD->getId()|default:0}

    <style>
        div.inputElement {
            min-width: 100px;
            max-width: 140px;
            margin-right: 10px;
        }

        .transaction-link {
            text-decoration: none;
            color: #15c;
        }

        /* vTiger switch */
        .vt-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            vertical-align: middle;
        }

        .vt-switch-label {
            position: absolute;
            top: -20px;
            left: -10px;
        }

        .vt-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .vt-switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #c8c8c8;
            transition: .2s;
            border-radius: 24px;
        }

        .vt-switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            top: 3px;
            background: #fff;
            transition: .2s;
            border-radius: 50%;
        }

        .vt-switch input:checked+.vt-switch-slider {
            background: #2f80ed;
        }

        .vt-switch input:checked+.vt-switch-slider:before {
            transform: translateX(20px);
        }

        .vt-switch input:focus+.vt-switch-slider {
            box-shadow: 0 0 0 2px rgba(47, 128, 237, .25);
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 3mm;
        }
    </style>

    <div class="summaryWidgetContainer">
        <div class="customwidgetContainer_ widgetContentBlock">

            <!-- ===================== HEADER ===================== -->
            <div class="widget_header row-fluid">
                <span class="span12 margin10px">
                    <h4 class="display-inline-block" style="font-size: 18px; margin: 0; padding: 0;">
                        TRANSACTION HISTORY
                    </h4>
                </span>

                <span class="span12 margin0px" style="margin-left: 0;">
                    <div class="row-fluid">

                        <!-- NEW CLEAN FLEX CONTAINER -->
                        <div style="" class="filter-container">

                            {* Start date *}
                            <label style="margin: 0; font-size: 13px;">Start date</label>

                            <div class="input-group inputElement">
                                <input type="text" id="start_date" class="dateField form-control"
                                    data-date-format="yyyy-mm-dd" autocomplete="off" value="{$START_DATE}">
                                <span class="input-group-addon js-open-start"><i class="fa fa-calendar"></i></span>
                            </div>

                            {* End date *}
                            <label style="margin: 0; font-size: 13px;">End date</label>
                            <div class="input-group inputElement">
                                <input type="text" id="end_date" class="dateField form-control"
                                    data-date-format="yyyy-mm-dd" autocomplete="off" value="{$END_DATE}">
                                <span class="input-group-addon js-open-end"><i class="fa fa-calendar"></i></span>
                            </div>

                            <button class="btn btn-success btn-sm date-range-button" type="button" data-mode="add">
                                Save
                            </button>

                            <label style="margin: 0; font-size: 13px;">Sort by date</label>
                            <label class="vt-switch">
                                <span class="vt-switch-label">
                                    {if $ORDER_BY eq 'desc'}
                                        Descending &nbsp;&nbsp;
                                    {else}
                                        Ascending &nbsp;&nbsp;
                                    {/if}

                                </span>
                                <input type="checkbox" name="summary_toggle" class="summary_toggle" value="1"
                                    {if $ORDER_BY eq 'desc'}checked{/if}>
                                <span class="vt-switch-slider"></span>
                            </label>

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
                                <th>AMOUNT</th>
                            </tr>
                        </thead>

                        <tbody>
                            {foreach item=TX from=$OROSOFT_TRANSACTION}

                                <tr class="listViewEntries1">
                                    <!-- Document number new hyperlink for different type of transaction -->
                                    <td style="width: 140px; text-align: center; vertical-align: middle;">
                                        {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD'])}
                                            <a class="transaction-link"
                                                href="index.php?module=Contacts&view=TCPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                {$TX.voucher_no}
                                            </a>

                                        {else if in_array($TX.voucher_type, ['MPD', 'MRD'])}
                                            {assign var="docLabel" value="Metal Payment Delivery"}
                                            {if $TX.voucher_type == 'MRD'}
                                                {assign var="docLabel" value="Metal Receipt Delivery"}
                                            {/if}
                                            <a class="transaction-link"
                                                href="index.php?module=Contacts&view=MPDPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                {$TX.voucher_no}
                                            </a>

                                        {else if in_array($TX.voucher_type, ['DN'])}
                                            {if $TX.table_name == 'DW_DocSTI'}
                                                <a class="transaction-link"
                                                    href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                    target="_blank">
                                                    {$TX.voucher_no}
                                                </a>
                                            {else}
                                                <a class="transaction-link"
                                                    href="index.php?module=Contacts&view=NotePrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                    target="_blank">
                                                    {$TX.voucher_no}
                                                </a>
                                            {/if}

                                        {else if in_array($TX.voucher_type, ['CN'])}
                                            <a class="transaction-link"
                                                href="index.php?module=Contacts&view=NotePrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                {$TX.voucher_no}
                                            </a>

                                        {else if in_array($TX.voucher_type, ['FCT'])}
                                            <a class="transaction-link" href="#">
                                                {$TX.voucher_no}
                                            </a>
                                        {else}
                                            <a class="transaction-link" href="#">
                                                {$TX.voucher_no}
                                            </a>
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
                                    {if in_array($TX.voucher_type, ['SAL','PUR', 'SWD', 'PWD'])}
                                        <td>
                                            <a href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TX.voucher_no}&recordType={$TX.doctype}&tableName={$TX.table_name}"
                                                target="_blank">
                                                <button type="button" class="btn btn-default module-buttons">
                                                    <span class="fa fa-download"></span>&nbsp;INV
                                                </button>
                                            </a>
                                        </td>
                                    {else if in_array($TX.voucher_type, ['MPD', 'MRD'])}
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
                                        {if in_array($TX.voucher_type, ['MPD', 'MRD', 'SAL'])}
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