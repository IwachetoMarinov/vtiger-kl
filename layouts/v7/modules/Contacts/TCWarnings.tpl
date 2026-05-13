{assign var="transactionWarnings" value=$ERP_DOCUMENT->_warnings|default:[]}

{assign var="itemWarningsCount" value=0}

{foreach from=$ERP_DOCUMENT->items item=item}
    {if isset($item._warnings) && $item._warnings|@count gt 0}
        {assign var="itemWarningsCount" value=$itemWarningsCount+$item._warnings|@count}
    {/if}
{/foreach}

{assign var="totalWarnings" value=$transactionWarnings|@count + $itemWarningsCount}

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
                <span id="tcWarningsClose"
                    style="font-size:22px;cursor:pointer;font-weight:bold;">&times;</span>
            </div>

            <hr>

            {* TRANSACTION WARNINGS *}
            {if $transactionWarnings|@count gt 0}

                <div style="margin-bottom:25px;">

                    <h4 style="margin-top:0;color:#b94a48;">
                        Transaction Warnings ({$transactionWarnings|@count})
                    </h4>

                    <ul style="margin:0;padding-left:20px;">

                        {foreach from=$transactionWarnings item=warning}

                            <li style="margin-bottom:6px;">
                                {$warning.message|default:$warning}
                            </li>

                        {/foreach}

                    </ul>

                </div>

            {/if}

            {* ITEM WARNINGS *}
            {if $itemWarningsCount gt 0}

                <div>

                    <h4 style="margin-top:0;color:#b94a48;">
                        Item Warnings ({$itemWarningsCount})
                    </h4>

                    {foreach from=$ERP_DOCUMENT->items item=item name=itemLoop}

                        {if isset($item._warnings) && $item._warnings|@count gt 0}

                            <div style="border:1px solid #ddd;padding:12px;margin-bottom:15px;border-radius:4px;">

                                <div style="margin-bottom:8px;">

                                    <strong>
                                        Item #{$smarty.foreach.itemLoop.iteration}
                                    </strong>

                                    {if isset($item.description) && $item.description neq ''}
                                        - {$item.description}
                                    {/if}

                                </div>

                                <ul style="margin:0;padding-left:20px;">

                                    {foreach from=$item._warnings item=warning}

                                        <li style="margin-bottom:5px;">
                                            {$warning.message|default:$warning}
                                        </li>

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