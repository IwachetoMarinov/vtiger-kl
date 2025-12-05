{* Balance By Wallet *}
<div class="summaryWidgetContainer" style="margin-bottom:20px;">
    <div class="widget_header clearfix">
        <h4 class="display-inline-block">Balance By Wallet</h4>
    </div>

    <div class="widget_contents" style="display:flex;flex-wrap:wrap;">
        {foreach item=BALANCE from=$BALANCES}
            <div style="display:flex;align-items:center;">
                <div style="
                width:120px;
                height:120px;
                background:#f1f1f1;
                border:1px solid #ddd;
                display:flex;
                flex-direction:column;
                justify-content:center;
                align-items:center;
                font-size:16px;
                color:#444;
                margin-right:30px;
            ">
                    {if isset($BALANCE.Curr_Code)}
                        <b>{$BALANCE.Curr_Code}</b>
                    {/if}

                    {if isset($BALANCE.Balance)}
                        <div style="margin-top:10px;font-size:17px;">
                            {$BALANCE.Balance}
                        </div>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>

    {* Check if Holdings is Array *}
    {if is_array($HOLDINGS) && count($HOLDINGS) > 0}
        <div style="margin-top:20px;text-align:center;">
            <a href="#" class="btn btn-primary">
                <i class="fa fa-download"></i> Download All
            </a>
        </div>
    {/if}
</div>