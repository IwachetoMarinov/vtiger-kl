{* TC warnings modal placeholder *}

{if isset($ERP_DOCUMENT->_warnings) && $ERP_DOCUMENT->_warnings|@count gt 0}
    <li style="float:right">
        <span id="tcWarningsBtn"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$ERP_DOCUMENT->_warnings|@count})
        </span>
    </li>
{/if}


{if isset($ERP_DOCUMENT->_warnings) && $ERP_DOCUMENT->_warnings|@count gt 0}
    <li style="float:right">
        <span id="tcWarningsBtn"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$ERP_DOCUMENT->_warnings|@count})
        </span>
    </li>

    <div id="tcWarningsModal"
        style="display:none;position:fixed;z-index:99999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);">
        <div style="background:white;margin:8% auto;padding:20px;width:650px;max-width:90%;border-radius:4px;color:#333;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;">Mapping Warnings</h3>
                <span id="tcWarningsClose" style="font-size:22px;cursor:pointer;">&times;</span>
            </div>

            <hr>

            <ul style="margin:0;padding-left:20px;">
                {foreach from=$ERP_DOCUMENT->_warnings item=warning}
                    <li style="margin-bottom:6px;">
                        {$warning.message|default:$warning}
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}