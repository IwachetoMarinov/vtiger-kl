{assign var="transactionWarningExcludes" value=$TRANSACTION_WARNING_EXCLUDES|default:[]}
{assign var="barItemWarningExcludes" value=$BARITEM_WARNING_EXCLUDES|default:[]}

{assign var="transactionWarningsCount" value=0}
{assign var="itemWarningsCount" value=0}

{foreach from=$ERP_DOCUMENT->_warnings|default:[] item=warning}
    {assign var="warningField" value=$warning.field|default:''}

    {if !$warningField || !in_array($warningField, $transactionWarningExcludes)}
        {assign var="transactionWarningsCount" value=$transactionWarningsCount+1}
    {/if}
{/foreach}

{foreach from=$ERP_DOCUMENT->items|default:[] item=item}
    {foreach from=$item._warnings|default:[] item=warning}
        {assign var="warningField" value=$warning.field|default:''}

        {if !$warningField || !in_array($warningField, $barItemWarningExcludes)}
            {assign var="itemWarningsCount" value=$itemWarningsCount+1}
        {/if}
    {/foreach}
{/foreach}

{assign var="totalWarnings" value=$transactionWarningsCount+$itemWarningsCount}

{if $totalWarnings gt 0}

    <li style="float:right">
        <span id="tcWarningsBtn"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$totalWarnings})
        </span>
    </li>

    <div id="tcWarningsModal"
        style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);overflow:auto;">

        <div style="background:white;margin:5% auto;padding:20px;width:850px;max-width:95%;border-radius:4px;color:#333;">

            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;">Mapping Warnings</h3>
                <span id="tcWarningsClose" style="font-size:22px;cursor:pointer;font-weight:bold;">&times;</span>
            </div>

            <hr>

            {if $transactionWarningsCount gt 0}
                <div style="margin-bottom:25px;">
                    <h4 style="margin-top:0;color:#b94a48;">
                        Transaction Warnings ({$transactionWarningsCount})
                    </h4>

                    <ul style="margin:0;padding-left:20px;">
                        {foreach from=$ERP_DOCUMENT->_warnings|default:[] item=warning}
                            {assign var="warningField" value=$warning.field|default:''}

                            {if !$warningField || !in_array($warningField, $transactionWarningExcludes)}
                                <li style="margin-bottom:6px;">
                                    {$warning.message|default:$warning}
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            {/if}

            {if $itemWarningsCount gt 0}
                <div>
                    <h4 style="margin-top:0;color:#b94a48;">
                        Item Warnings ({$itemWarningsCount})
                    </h4>

                    {foreach from=$ERP_DOCUMENT->items|default:[] item=item name=itemLoop}
                        {assign var="visibleItemWarningCount" value=0}

                        {foreach from=$item._warnings|default:[] item=warning}
                            {assign var="warningField" value=$warning.field|default:''}

                            {if !$warningField || !in_array($warningField, $barItemWarningExcludes)}
                                {assign var="visibleItemWarningCount" value=$visibleItemWarningCount+1}
                            {/if}
                        {/foreach}

                        {if $visibleItemWarningCount gt 0}
                            <div style="border:1px solid #ddd;padding:12px;margin-bottom:15px;border-radius:4px;">
                                <div style="margin-bottom:8px;">
                                    <strong>Item #{$smarty.foreach.itemLoop.iteration}</strong>

                                    {if isset($item.description) && $item.description neq ''}
                                        - {$item.description}
                                    {/if}
                                </div>

                                <ul style="margin:0;padding-left:20px;">
                                    {foreach from=$item._warnings|default:[] item=warning}
                                        {assign var="warningField" value=$warning.field|default:''}

                                        {if !$warningField || !in_array($warningField, $barItemWarningExcludes)}
                                            <li style="margin-bottom:5px;">
                                                {$warning.message|default:$warning}
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            {/if}

        </div>
    </div>

{/if}

<script>
    $(document).ready(function() {
        $('#tcWarningsBtn').on('click', function() {
            $('#tcWarningsModal').show();
        });

        $('#tcWarningsClose').on('click', function() {
            $('#tcWarningsModal').hide();
        });

        $('#tcWarningsModal').on('click', function(e) {
            if (e.target.id === 'tcWarningsModal') {
                $('#tcWarningsModal').hide();
            }
        });
    });
</script>