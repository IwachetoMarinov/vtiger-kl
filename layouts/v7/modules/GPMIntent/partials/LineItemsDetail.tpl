


{strip}

    <div class="block" data-block="ITEM INFORMATION">
        <div>
            <h4 class="textOverflowEllipsis maxWidth50">
                <img class="cursorPointer alignMiddle blockToggle hide" src="layouts/v7/skins/images/arrowRight.png"
                    data-mode="hide" data-id="141">
                <img class="cursorPointer alignMiddle blockToggle" src="layouts/v7/skins/images/arrowdown.png"
                    data-mode="show" data-id="141">
                &nbsp;Item Information
            </h4>
        </div>

        <hr>

        <table class="table table-bordered lineItemsTable" style="margin-top:15px">
            <thead>
                <tr>
                    <th class="lineItemBlockHeader">Product</th>
                    <th class="lineItemBlockHeader">Quantity</th>
                    <th class="lineItemBlockHeader">Fine Oz</th>
                    <th class="lineItemBlockHeader">Premium (%)</th>
                    <th class="lineItemBlockHeader">Premium (USD)</th>
                    <th class="lineItemBlockHeader">Value (USD)</th>
                </tr>
            </thead>

            <tbody>
                {foreach item=LINE from=$RELATED_PRODUCTS}
                    <tr>
                        <td>
                            <div>
                                <h5>
                                    <a class="fieldValue"
                                        href="index.php?module=GPMMetal&view=Detail&record={$LINE->get('gpmmetalid')}"
                                        target="_blank">
                                        {$LINE->getMetalName()}
                                    </a>
                                </h5>
                                <div><i>{$LINE->get('remark')}</i></div>
                            </div>
                        </td>

                        <td>{$LINE->get('qty')}</td>

                        <td style="white-space:nowrap;">
                            <div>{$LINE->get('fine_oz')}</div>
                        </td>

                        <td>
                            <div>{$LINE->get('premium_or_discount')}</div>
                        </td>

                        <td>
                            <div>${$LINE->get('premium_or_discount_usd')|number_format:2}</div>
                        </td>

                        <td>
                            <div>${$LINE->get('value_usd')|number_format:2}</div>
                        </td>
                    </tr>
                {/foreach}

                <tr>
                    <td colspan="2" style="text-align:center;font-size:10pt;font-weight:bold;">
                        Total
                    </td>
                    <td style="font-size:10pt;font-weight:bold;">
                        {$RECORD->get('total_oz')}
                    </td>
                    <td></td>
                    <td></td>
                    <td style="font-size:10pt;font-weight:bold;">
                        ${$RECORD->get('total_amount')|number_format:2}
                    </td>
                </tr>

                <tr>
                    <td colspan="5" style="text-align:center;font-size:10pt;font-weight:bold;">
                        Total Amount (Foreign)
                    </td>
                    <td style="font-size:10pt;font-weight:bold;">
                        {$RECORD->get('package_currency')}
                        {$RECORD->get('total_foreign_amount')|number_format:2}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
{/strip}