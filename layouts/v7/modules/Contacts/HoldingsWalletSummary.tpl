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
                            {number_format($BALANCE.Balance,2)}
                        </div>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
</div>