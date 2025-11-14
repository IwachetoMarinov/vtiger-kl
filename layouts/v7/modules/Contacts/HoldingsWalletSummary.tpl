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
                <b>{$TEST_CURRENCY}</b>
                <div style="margin-top:10px;font-size:20px;">
                    {$TEST_BALANCES.available}
                </div>
            </div>

            <div style="font-size:14px;color:#444;">
                <b>Pending:</b> {$TEST_BALANCES.pending}
            </div>
        </div>

        <div style="margin-top:20px;">
            <a href="#" class="btn btn-primary">
                <i class="fa fa-download"></i> Download All
            </a>
        </div>
    </div>
</div>


{* Holdings Section *}
{* <div class="summaryWidgetContainer" style="margin-bottom:20px;">
    <div class="widget_header clearfix">
        <h4 class="display-inline-block">Holdings</h4>

        <div class="pull-right">
            <a href="#" class="btn btn-default">
                <i class="fa fa-certificate"></i> Certificate
            </a>
            <a href="#" class="btn btn-default">
                <i class="fa fa-download"></i> Download
            </a>
        </div>
    </div>

    <div class="widget_contents" style="padding:10px;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$TEST_HOLDINGS item=HOLD}
                <tr>
                    <td>{$HOLD.metal}</td>
                    <td>{$HOLD.oz}</td>
                    <td>{$HOLD.location}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div> *}
