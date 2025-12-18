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
                    <th class="lineItemBlockHeader">Premium</th>
                    <th class="lineItemBlockHeader">Value</th>
                </tr>
            </thead>

            <tbody>
                {if $RELATED_PRODUCTS && is_array($RELATED_PRODUCTS) && count($RELATED_PRODUCTS) > 0}
                    {foreach $RELATED_PRODUCTS as $LINE}

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
                                {$LINE->get('fine_oz')}
                            </td>

                            <td>
                                {$LINE->get('premium_or_discount')}
                            </td>

                            <td>
                                $ {$LINE->get('premium_or_discount_usd')|number_format:2}
                            </td>

                            <td>
                                $ {$LINE->get('value_usd')|number_format:2}
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

                  
                {else}
                    <tr>
                        <td colspan="6" style="text-align:center;padding:20px;">
                            No products found
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
{/strip}