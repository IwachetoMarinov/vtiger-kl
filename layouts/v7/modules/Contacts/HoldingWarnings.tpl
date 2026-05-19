{assign var="holdingWarningExcludes" value=$HOLDING_WARNING_EXCLUDES|default:[]}

{assign var="uniqueWarnings" value=[]}
{assign var="uniqueWarningFields" value=[]}
{assign var="warningsCount" value=0}

{if isset($HOLDINGS) && is_array($HOLDINGS)}

    {foreach from=$HOLDINGS key=locationName item=holdingItems}

        {if is_array($holdingItems)}

            {foreach from=$holdingItems item=holdingItem}

                {assign var="itemWarnings" value=[]}

                {if is_object($holdingItem)}
                    {assign var="itemWarnings" value=$holdingItem->_warnings|default:[]}
                {elseif is_array($holdingItem)}
                    {assign var="itemWarnings" value=$holdingItem._warnings|default:[]}
                {/if}

                {if is_array($itemWarnings)}

                    {foreach from=$itemWarnings item=warning}

                        {assign var="warningField" value=''}
                        {assign var="warningMessage" value=''}

                        {if is_array($warning)}
                            {assign var="warningField" value=$warning.field|default:''}
                            {assign var="warningMessage" value=$warning.message|default:''}
                        {elseif is_object($warning)}
                            {assign var="warningField" value=$warning->field|default:''}
                            {assign var="warningMessage" value=$warning->message|default:''}
                        {else}
                            {assign var="warningMessage" value=$warning}
                        {/if}

                        {if $warningField neq ''
                                                    && !in_array($warningField, $holdingWarningExcludes)
                                                    && !in_array($warningField, $uniqueWarningFields)}

                        {append var="uniqueWarningFields" value=$warningField}
                        {append var="uniqueWarnings" value=$warningMessage}

                        {assign var="warningsCount" value=$warningsCount+1}

                    {/if}

                {/foreach}

            {/if}

        {/foreach}

    {/if}

{/foreach}

{/if}

{if $warningsCount gt 0}

    <li style="float:right">
        <span id="holdingWarningsBtn" onclick="document.getElementById('holdingWarningsModal').style.display='block';"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$warningsCount})
        </span>
    </li>

    <div id="holdingWarningsModal" onclick="if(event.target.id === 'holdingWarningsModal') this.style.display='none';"
        style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);overflow:auto;">

        <div style="background:white;margin:5% auto;padding:20px;width:850px;max-width:95%;border-radius:4px;color:#333;">

            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;">
                    Holding Warnings
                </h3>

                <span onclick="document.getElementById('holdingWarningsModal').style.display='none';"
                    style="font-size:22px;cursor:pointer;font-weight:bold;">
                    &times;
                </span>
            </div>

            <hr>

            <div>
                <h4 style="margin-top:0;color:#b94a48;">
                    Unique Mapping Warnings ({$warningsCount})
                </h4>

                <ul style="margin:0;padding-left:20px;">
                    {foreach from=$uniqueWarnings item=warningMessage}
                        <li style="margin-bottom:6px;">
                            {$warningMessage|escape:'html'}
                        </li>
                    {/foreach}
                </ul>
            </div>

        </div>

    </div>

{/if}