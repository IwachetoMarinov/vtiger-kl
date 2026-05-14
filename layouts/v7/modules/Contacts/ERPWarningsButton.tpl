{* ERPWarningsButton.tpl *}

{assign var="erpWarningExcludes" value=$ERP_WARNING_EXCLUDES|default:[]}
{assign var="uniqueWarningFields" value=[]}
{assign var="uniqueWarnings" value=[]}

{foreach from=$ERP_WARNING_DATA item=row}
    {assign var="rowWarnings" value=[]}

    {if isset($row->_warnings)}
        {assign var="rowWarnings" value=$row->_warnings}
    {elseif isset($row['_warnings'])}
        {assign var="rowWarnings" value=$row['_warnings']}
    {/if}

    {foreach from=$rowWarnings item=warning}
        {assign var="warningField" value=$warning.field|default:''}
        {assign var="warningMessage" value=$warning.message|default:$warning}

        {if !$warningField || !in_array($warningField, $erpWarningExcludes)}
            {if !in_array($warningField, $uniqueWarningFields)}
                {append var="uniqueWarningFields" value=$warningField}
                {append var="uniqueWarnings" value=$warningMessage}
            {/if}
        {/if}
    {/foreach}
{/foreach}

{assign var="erpWarningsCount" value=$uniqueWarnings|@count}

{if $erpWarningsCount gt 0}
    <button type="button" class="erp-warning-btn {$ERP_WARNING_CLASS|default:'erp-fields-warning'}"
        data-warning-title="{$ERP_WARNING_TITLE|default:'ERP Mapping Warnings'|escape:'html'}" data-warning-message="
            <div>
                <h4 style='margin-top:0;color:#b94a48;'>
                    {$ERP_WARNING_TITLE|default:'ERP Mapping Warnings'|escape:'html'} ({$erpWarningsCount})
                </h4>

                <ul style='margin:0;padding-left:20px;'>
                    {foreach from=$uniqueWarnings item=warningMessage}
                        <li style='margin-bottom:6px;'>
                            {$warningMessage|escape:'html'}
                        </li>
                    {/foreach}
                </ul>
            </div>
        ">
        {$ERP_WARNING_BUTTON_LABEL|default:'ERP Mapping Warnings'|escape:'html'} ({$erpWarningsCount})
    </button>
{/if}