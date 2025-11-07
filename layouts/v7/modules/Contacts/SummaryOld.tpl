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

        <div class="customwidgetContainer_ widgetContentBlock"
            data-url="module=VTEWidgets&view=SummaryWidget&record=5&mode=showRelatedWidget&relatedModule=Quotes&page=1&limit=5&vtewidgetid=10&sourcemodule=Accounts"
            data-name="Quotes" data-type="RelatedModule">

            <div class="widget_header row-fluid">
                <input type="hidden" class="relatedlimit" name="relatedlimit" value="5" />
                <input type="hidden" class="relatedModuleName" name="relatedModule" value="Quotes" />
                <input type="hidden" name="columnslist"
                    value="[&quot;subject&quot;,&quot;quotestage&quot;,&quot;hdnGrandTotal&quot;]" />
                <input type="hidden" name="sortby" value="" /> <input type="hidden" name="sorttype" value="" />
                <span class="span11 margin0px">

                    <div class="row-fluid">
                        <h4 class="display-inline-block" style="width:12em;">ACTIVITY SUMMARY</h4>
                        <div class="pull-right span2">
                            <label style="font-family: 'OpenSans-Semibold', 'ProximaNova-Semibold', sans-serif;
                                       font-weight: normal;
                                       font-size: 1.1em;
                                       margin-top: 7px;
                                       margin-right: 7px">By Year</label>
                            <select id='ActivtySummeryDate' style="width:100px;" class="inputElement select2">
                                <option value="">Current Year</option>
                                <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-1 year"))} selected
                                    {/if} value="{date("Y",strtotime("-1 year"))}">
                                    {date("Y",strtotime("-1 year"))}</option>
                                <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-2 year"))} selected
                                    {/if} value="{date("Y",strtotime("-2 year"))}">
                                    {date("Y",strtotime("-2 year"))}</option>
                                <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-3 year"))} selected
                                    {/if} value="{date("Y",strtotime("-3 year"))}">
                                    {date("Y",strtotime("-3 year"))}</option>
                                <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-4 year"))} selected
                                    {/if} value="{date("Y",strtotime("-4 year"))}">
                                    {date("Y",strtotime("-4 year"))}</option>
                                <option {if $smarty.request.ActivtySummeryDate eq date("Y",strtotime("-5 year"))} selected
                                    {/if} value="{date("Y",strtotime("-5 year"))}">
                                    {date("Y",strtotime("-5 year"))}</option>
                            </select>

                            <a href="index.php?module=Contacts&view=ActivtySummeryPrintPreview&record={$RECORD->getId()}&ActivtySummeryDate={$smarty.request.ActivtySummeryDate}"
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
            <div class="widget_contents">
                <div class="relatedContents contents-bottomscroll" style="border: none;width: 100%;">
                    <table class="table table-strip listViewEntriesTable">
                        <thead>
                            <tr class="listViewHeaders">
                                <th nowrap="">
                                    DOCUMENT NO
                                </th>
                                <th></th>
                                <th></th>
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
                                {if in_array(substr($TRANSACTION->docNo,0,3),array('FXP','FXR'))}
                                    {continue}
                                {/if}
                                <tr class="listViewEntries1" data-id="" data-recordurl="">
                                    <td class="fieldValue" style="width: 140px;vertical-align: inherit;"
                                        data-field-type="string" nowrap="" title="New Setup">
                                        <span class="value" data-field-type="string">
                                            {if substr($TRANSACTION->docNo,0,3) eq 'AOP'}
                                                {$TRANSACTION->docNo}
                                            {else}
                                                <a href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TRANSACTION->docNo}"
                                                    target="_blank">{$TRANSACTION->docNo}</a>
                                            {/if}
                                        </span>
                                    </td>
                                    {if in_array(substr($TRANSACTION->docNo,0,3),array('SAL','SWD','PUR','PWD'))}
                                        <td><a href="index.php?module=Contacts&view=DocumentPrintPreview&record={$RECORD->getId()}&docNo={$TRANSACTION->docNo}"
                                                target="_blank"><button id="" type="button"
                                                    class="btn addButton btn-default module-buttons">
                                                    <div class="fa fa-download" aria-hidden="true"></div>&nbsp;&nbsp;INV
                                                </button></a></td>
                                        <td><a href="index.php?module=Contacts&view=TCPrintPreview&record={$RECORD->getId()}&docNo={$TRANSACTION->docNo}"
                                                target="_blank"><button id="" type="button"
                                                    class="btn addButton btn-default module-buttons">
                                                    <div class="fa fa-download" aria-hidden="true"></div>&nbsp;&nbsp;TC
                                                </button></a></td>
                                    {else}
                                        <td></td>
                                        <td></td>
                                    {/if}
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {date('d-M-Y',strtotime($TRANSACTION->transDate))}
                                        </span>
                                    </td>
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {vtranslate(substr($TRANSACTION->docNo,0,3),$MODULE_NAME)}
                                        </span>
                                    </td>
                                    <td class=" fieldValue" style="vertical-align: inherit;" data-field-type="picklist"
                                        nowrap="" title="Reviewed">
                                        <span class="value" data-field-type="picklist">
                                            {$TRANSACTION->usdVal}
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