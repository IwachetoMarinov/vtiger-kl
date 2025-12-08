<form id="detailView" method="POST">
    {include file='DetailViewBlockView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}

    {* Related Products List *}
    {include file='partials/LineItemsDetail.tpl'|@vtemplate_path:$MODULE}
</form>