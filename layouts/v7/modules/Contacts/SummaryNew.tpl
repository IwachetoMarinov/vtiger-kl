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
    <div class="summaryWidgetContainer">
        <div class="customwidgetContainer_ widgetContentBlock">
            <div class="widget_header row-fluid">
                <div class="widget_header row-fluid">
                    <span class="span11 margin0px">
                        <div class="row-fluid">
                            <h4 class="display-inline-block" style="width: 12em;">ACTIVITY SUMMARY</h4>
                            <div class="pull-right span2">
                                {*<label class=" pull-left ">Date</label>
                            <div class="input-append date">
                              <input type="text" style="width: 90px" data-date-format="yyyy-mm-dd" name="FDate"
                                 value="{$smarty.request.FDate}" class="dateField" id="FDate">
                              <span class="add-on"><i class="icon-calendar"></i></span>
                           </div> *}
                                <label for="currencySelect" style="font-family: 'OpenSans-Semibold', 'ProximaNova-Semibold', sans-serif;
                               font-weight: normal;
                               font-size: 1.1em;
                               margin-top: 7px;
                               margin-right: 7px;">Currency</label>
                                <select id="currencySelect" style="width: 100px;" class="inputElement select2">
                                    <option value="">Select Currency</option>
                                    {foreach item=CURRENCY from=$CLIENT_CURRENCY}
                                        {if !empty($ACTIVITY_SUMMERY_CURRENCY) && $ACTIVITY_SUMMERY_CURRENCY == $CURRENCY}
                                            <option value="{$CURRENCY}" selected="selected">
                                                {$CURRENCY}
                                            </option>
                                        {elseif empty($ACTIVITY_SUMMERY_CURRENCY) && $CURRENCY == 'USD'}
                                            <option value="USD" selected="selected">USD</option>
                                        {else}
                                            <option value="{$CURRENCY}">
                                                {$CURRENCY}
                                            </option>
                                        {/if}
                                    {/foreach}
                                </select>
                                
                            <label style="font-family: 'OpenSans-Semibold', 'ProximaNova-Semibold', sans-serif;
                               font-weight: normal;
                               font-size: 1.1em;
                               margin-top: 7px;
                               margin-right: 7px; margin-left: 7px">By Year</label>
                            <select id="ActivtySummeryDate" style="width: 100px; margin-right: 7px" class="inputElement select2">
                               <option value="">Current Year</option>
                               <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-1 year"))}
                               selected {/if} value="{date("Y",strtotime("-1 year"))}">
                               {date("Y",strtotime("-1 year"))}</option>
                               <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-2 year"))}
                               selected {/if} value="{date("Y",strtotime("-2 year"))}">
                               {date("Y",strtotime("-2 year"))}</option>
                               <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-3 year"))}
                               selected {/if} value="{date("Y",strtotime("-3 year"))}">
                               {date("Y",strtotime("-3 year"))}</option>
                               <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-4 year"))}
                               selected {/if} value="{date("Y",strtotime("-4 year"))}">
                               {date("Y",strtotime("-4 year"))}</option>
                               <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-5 year"))}
                               selected {/if} value="{date("Y",strtotime("-5 year"))}">
                               {date("Y",strtotime("-5 year"))}</option>
                            </select> 
                                <a href="index.php?module=Contacts&view=ActivtySummeryPrintPreview&record={$RECORD->getId()}&ActivtySummeryDate={$smarty.request.ActivtySummeryDate}&ActivtySummeryCurrency={$ACTIVITY_SUMMERY_CURRENCY}"
                                    target="_blank">
                                    <button class="btn btn-default vteWidgetCreateButton" type="button">
                                        <span class="fa fa-download"></span>
                                        &nbsp;Download
                                    </button>
                                </a>
                            </div>
                        </div>
                    </span>
                </div>
            </div>
            <div class="widget_contents">
                <div class="relatedContents contents-bottomscroll" style="border: none;width: 100%;">
                    <table class="table table-strip listViewEntriesTable">
                        <thead>
                            <tr class="listViewHeaders">
                                <th nowrap="">
                                    DOCUMENT NO
                                </th>
                                <th style="width: 5px;"></th>
                                <th style="width: 5px;"></th>
                                <th style="width: 5px;"></th>
                                <th nowrap="">
                                    DATE
                                </th>
                                <th nowrap="">
                                    DESCRIPTION
                                </th>
                                <th nowrap="">
                                    DEPOSIT
                                </th>
                                <!-- th colspan="2" nowrap="">
                               BALANCE
                               </th-->
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=TRANSACTION from=$OROSOFT_TRANSACTION}
                                {if $TRANSACTION['0'] eq 'Total'}{continue}{/if}
                                <tr class="listViewEntries1" data-id="" data-recordurl="">
                                    <td class="fieldValue" style="width: 140px;vertical-align: inherit;">
                                        <span data-field-type="string">
                                            {$TRANSACTION['voucher_no']}
                                        </span>
                                    </td>
                                    {if in_array($TRANSACTION['doctype'],array('Purchase Invoice','Sales Invoice'))}
                                        <td>
                                            <a href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TRANSACTION['voucher_no']}&recordType={$TRANSACTION['doctype']}"
                                                target="_blank">
                                                <button id="" type="button" class="btn addButton btn-default module-buttons">
                                                    <div class="fa fa-download" aria-hidden="true"></div>
                                                    &nbsp;&nbsp;INV
                                                </button>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="index.php?module=Contacts&view=TCPrintPreview&record={$RECORD->getId()}&docNo={$TRANSACTION['voucher_no']}&recordType={$TRANSACTION['doctype']}"
                                                target="_blank">
                                                <button id="" type="button" class="btn addButton btn-default module-buttons">
                                                    <div class="fa fa-download" aria-hidden="true"></div>
                                                    &nbsp;&nbsp;TC
                                                </button>
                                            </a>
                                        </td>
                                        <td></td>
                                    {else}
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    {/if}
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {$TRANSACTION['posting_date']}
                                        </span>
                                    </td>
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {$TRANSACTION['voucher_type']}
                                        </span>
                                    </td>
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {number_format($TRANSACTION['amount_in_account_currency'],2, '.', ',')}
                                        </span>
                                    </td>
                                    <!-- td class=" fieldValue" data-field-type="currency" nowrap="" title="943.62">
                               <span class="value" data-field-type="currency">
                               {$TRANSACTION->usdVal}
                               </span>
                               </td-->
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

{/strip}