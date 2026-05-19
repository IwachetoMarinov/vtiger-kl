{assign var="transactionWarningExcludes" value=$TRANSACTION_WARNING_EXCLUDES|default:[]}
{assign var="barItemWarningExcludes" value=$BARITEM_WARNING_EXCLUDES|default:[]}

{assign var="transactionWarningsCount" value=0}
{assign var="barItemWarningsCount" value=0}
{assign var="missingBarItemsWarning" value=false}

{assign var="erpWarnings" value=[]}
{assign var="barItems" value=[]}
{assign var="firstBarItem" value=null}
{assign var="barItemWarnings" value=[]}
{assign var="barItemDescription" value=''}

{* Read ERP warnings and bar items *}
{if isset($ERP_DOCUMENT)}
    {if is_object($ERP_DOCUMENT)}
        {if isset($ERP_DOCUMENT->_warnings)}
            {assign var="erpWarnings" value=$ERP_DOCUMENT->_warnings}
        {/if}

        {if isset($ERP_DOCUMENT->barItems)}
            {assign var="barItems" value=$ERP_DOCUMENT->barItems}
        {/if}
    {elseif is_array($ERP_DOCUMENT)}
        {if isset($ERP_DOCUMENT['_warnings'])}
            {assign var="erpWarnings" value=$ERP_DOCUMENT['_warnings']}
        {/if}

        {if isset($ERP_DOCUMENT['barItems'])}
            {assign var="barItems" value=$ERP_DOCUMENT['barItems']}
        {/if}
    {/if}
{/if}

{* Missing bar items warning *}
{if is_array($barItems) && $barItems|@count eq 0}
    {assign var="missingBarItemsWarning" value=true}
{/if}

{* First bar item *}
{if is_array($barItems) && $barItems|@count gt 0}
    {assign var="firstBarItem" value=$barItems[0]}
{/if}

{* First bar item warnings and description *}
{if $firstBarItem}
    {if is_object($firstBarItem)}
        {if isset($firstBarItem->_warnings)}
            {assign var="barItemWarnings" value=$firstBarItem->_warnings}
        {/if}

        {if isset($firstBarItem->description)}
            {assign var="barItemDescription" value=$firstBarItem->description}
        {/if}
    {elseif is_array($firstBarItem)}
        {if isset($firstBarItem['_warnings'])}
            {assign var="barItemWarnings" value=$firstBarItem['_warnings']}
        {/if}

        {if isset($firstBarItem['description'])}
            {assign var="barItemDescription" value=$firstBarItem['description']}
        {/if}
    {/if}
{/if}

{* Count transaction warnings *}
{if is_array($erpWarnings)}
    {foreach from=$erpWarnings item=warning}
        {assign var="warningField" value=''}

        {if is_object($warning)}
            {assign var="warningField" value=$warning->field|default:''}
        {elseif is_array($warning)}
            {assign var="warningField" value=$warning.field|default:''}
        {/if}

        {if !$warningField || !in_array($warningField, $transactionWarningExcludes)}
            {assign var="transactionWarningsCount" value=$transactionWarningsCount+1}
        {/if}
    {/foreach}
{/if}

{* Count bar item warnings *}
{if is_array($barItemWarnings)}
    {foreach from=$barItemWarnings item=warning}
        {assign var="warningField" value=''}

        {if is_object($warning)}
            {assign var="warningField" value=$warning->field|default:''}
        {elseif is_array($warning)}
            {assign var="warningField" value=$warning.field|default:''}
        {/if}

        {if !$warningField || !in_array($warningField, $barItemWarningExcludes)}
            {assign var="barItemWarningsCount" value=$barItemWarningsCount+1}
        {/if}
    {/foreach}
{/if}

{assign var="totalWarnings" value=$transactionWarningsCount+$barItemWarningsCount}

{if $missingBarItemsWarning}
    {assign var="totalWarnings" value=$totalWarnings+1}
{/if}

{if $totalWarnings gt 0}

    <li style="float:right">
        <span id="tcWarningsBtn"
            onclick="document.getElementById('tcWarningsModal').style.display='block';"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$totalWarnings})
        </span>
    </li>

    <div id="tcWarningsModal"
        onclick="if(event.target.id === 'tcWarningsModal') this.style.display='none';"
        style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);overflow:auto;">

        <div style="background:white;margin:5% auto;padding:20px;width:850px;max-width:95%;border-radius:4px;color:#333;">

            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;">Mapping Warnings</h3>

                <span onclick="document.getElementById('tcWarningsModal').style.display='none';"
                    style="font-size:22px;cursor:pointer;font-weight:bold;">
                    &times;
                </span>
            </div>

            <hr>

            {if $transactionWarningsCount gt 0}
                <div style="margin-bottom:25px;">
                    <h4 style="margin-top:0;color:#b94a48;">
                        Transaction Warnings ({$transactionWarningsCount})
                    </h4>

                    <ul style="margin:0;padding-left:20px;">
                        {foreach from=$erpWarnings item=warning}
                            {assign var="warningField" value=''}
                            {assign var="warningMessage" value=''}

                            {if is_object($warning)}
                                {assign var="warningField" value=$warning->field|default:''}
                                {assign var="warningMessage" value=$warning->message|default:$warning}
                            {elseif is_array($warning)}
                                {assign var="warningField" value=$warning.field|default:''}
                                {assign var="warningMessage" value=$warning.message|default:$warning}
                            {else}
                                {assign var="warningMessage" value=$warning}
                            {/if}

                            {if !$warningField || !in_array($warningField, $transactionWarningExcludes)}
                                <li style="margin-bottom:6px;">
                                    {$warningMessage|escape:'html'}
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            {/if}

            {if $missingBarItemsWarning}
                <div style="margin-bottom:25px;">
                    <h4 style="margin-top:0;color:#b94a48;">
                        Bar Item Warnings (1)
                    </h4>

                    <div style="border:1px solid #ddd;padding:12px;margin-bottom:15px;border-radius:4px;">
                        <ul style="margin:0;padding-left:20px;">
                            <li style="margin-bottom:5px;">
                                Missing bar item mapping. No bar items were found for this transaction.
                            </li>
                        </ul>
                    </div>
                </div>
            {/if}

            {if $barItemWarningsCount gt 0 && $firstBarItem}
                <div>
                    <h4 style="margin-top:0;color:#b94a48;">
                        Bar Item Warnings ({$barItemWarningsCount})
                    </h4>

                    <div style="border:1px solid #ddd;padding:12px;margin-bottom:15px;border-radius:4px;">
                        <div style="margin-bottom:8px;">
                            <strong>Bar Item Mapping</strong>

                            {if $barItemDescription neq ''}
                                - {$barItemDescription|escape:'html'}
                            {/if}
                        </div>

                        <ul style="margin:0;padding-left:20px;">
                            {foreach from=$barItemWarnings item=warning}
                                {assign var="warningField" value=''}
                                {assign var="warningMessage" value=''}

                                {if is_object($warning)}
                                    {assign var="warningField" value=$warning->field|default:''}
                                    {assign var="warningMessage" value=$warning->message|default:$warning}
                                {elseif is_array($warning)}
                                    {assign var="warningField" value=$warning.field|default:''}
                                    {assign var="warningMessage" value=$warning.message|default:$warning}
                                {else}
                                    {assign var="warningMessage" value=$warning}
                                {/if}

                                {if !$warningField || !in_array($warningField, $barItemWarningExcludes)}
                                    <li style="margin-bottom:5px;">
                                        {$warningMessage|escape:'html'}
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/if}

        </div>
    </div>

{/if}