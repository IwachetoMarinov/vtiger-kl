{assign var="activityWarningExcludes" value=$ACTIVITY_WARNING_EXCLUDES|default:[]}

{assign var="uniqueWarnings" value=[]}
{assign var="uniqueWarningFields" value=[]}
{assign var="warningsCount" value=0}

{if isset($TRANSACTIONS) && is_array($TRANSACTIONS)}

    {foreach from=$TRANSACTIONS item=transaction}

        {assign var="transactionWarnings" value=[]}

        {if is_array($transaction) && isset($transaction._warnings)}
            {assign var="transactionWarnings" value=$transaction._warnings}
        {/if}

        {if is_array($transactionWarnings)}

            {foreach from=$transactionWarnings item=warning}

                {assign var="warningField" value=''}
                {assign var="warningMessage" value=''}

                {if is_array($warning)}
                    {assign var="warningField" value=$warning.field|default:''}
                    {assign var="warningMessage" value=$warning.message|default:''}
                {elseif is_object($warning)}
                    {assign var="warningField" value=$warning->field|default:''}
                    {assign var="warningMessage" value=$warning->message|default:''}
                {/if}

                {if $warningField neq ''
                    && !in_array($warningField, $activityWarningExcludes)
                    && !in_array($warningField, $uniqueWarningFields)}

                    {append var="uniqueWarningFields" value=$warningField}
                    {append var="uniqueWarnings" value=$warningMessage}

                    {assign var="warningsCount" value=$warningsCount+1}

                {/if}

            {/foreach}

        {/if}

    {/foreach}

{/if}

{if $warningsCount gt 0}

    <li style="float:right">
        <span id="activityWarningsBtn"
            onclick="document.getElementById('activityWarningsModal').style.display='block';"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">

            Warnings ({$warningsCount})
        </span>
    </li>

    <div id="activityWarningsModal"
        onclick="if(event.target.id === 'activityWarningsModal') this.style.display='none';"
        style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);overflow:auto;">

        <div style="background:white;margin:5% auto;padding:20px;width:850px;max-width:95%;border-radius:4px;color:#333;">

            <div style="display:flex;justify-content:space-between;align-items:center;">

                <h3 style="margin:0;">
                    Activity Summary Warnings
                </h3>

                <span onclick="document.getElementById('activityWarningsModal').style.display='none';"
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