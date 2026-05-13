{* TC warnings modal placeholder *}

{if isset($ERP_DOCUMENT->_warnings) && $ERP_DOCUMENT->_warnings|@count gt 0}
    <li style="float:right">
        <span id="tcWarningsBtn"
            style="float:right;margin-right:1px;color:white;background-color:#b94a48;text-decoration:none;display:block;text-align:center;padding:14px;cursor:pointer;">
            Warnings ({$ERP_DOCUMENT->_warnings|@count})
        </span>
    </li>
{/if}