{* Balance By Wallet *}
<div class="summaryWidgetContainer" style="margin-bottom:20px;">
    <div class="widget_header clearfix">
        <h4 class="display-inline-block">Balance By Wallet</h4>
    </div>

    <div class="widget_contents" style="padding:15px;">
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
                <b>{$CURRENCY}</b>
                <div style="margin-top:10px;font-size:20px;">
                    {$BALANCES.available}
                </div>
            </div>

            <div style="font-size:14px;color:#444;">
                <b>Pending:</b> {$BALANCES.pending}
            </div>
        </div>

        <div style="margin-top:20px;">
            <a href="#" class="btn btn-primary">
                <i class="fa fa-download"></i> Download All
            </a>
        </div>
    </div>
</div>