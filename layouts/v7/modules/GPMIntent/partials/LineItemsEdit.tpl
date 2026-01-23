{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}

{strip}
    <div class="fieldBlockContainer" data-block="ITEM INFORMATION">
        <h4 class="fieldBlockHeader">Item Information</h4>
        <hr>
        <div class="">
            <div class="col-md-12 item_infromation_lable ">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-3 col-sm-3 col-xs-2">
                        <label>Product</label>
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-2">
                        <label>Quantity</label>
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-2">
                        <label>Fine Oz</label>
                    </div>
                    <div class="col-md-2 col-sm-3 col-xs-2">
                        <label class='pre_disc'>Premium/Discount (%)</label>
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-2" style="padding: 0;padding-left: 15px;">
                        <label class='pre_disc_usd'></label>
                    </div>
                    <div class="col-md-2 col-sm-3 col-xs-2">
                        <label>Value</label>
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-2">
                    </div>
                </div>
            </div>
            <div id="item_raw" style="display: none;">
                <div class="col-md-12 item_infromation_input item_raw">
                    <div class="col-md-12" style="margin-top: 10px;"></div>
                    <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-1">
                            <button type="button" class='delete-row' style="height: 30px;width: 30px;">
                                <i class="fa fa-trash delete-row" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <select class="inputElement item_metal" type="picklist" id="metal_raw" name="metal[]">
                                <option value="">Select a Metal</option>
                                {literal}
                                    {{xxx}}
                                {/literal}
                            </select>
                            <input type="textarea" class="form-control item_remark" placeholder="Request on each bar items" style="height: 50px;margin-top: 2px;" id="item_remark_raw" name="item_remark[]" value="">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_qty" id="qty_raw" name="qty[]" value="0" required="">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="text" class="form-control item_fineoz" id="fineoz_raw" name="fineoz[]" value="" readonly=true>
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_premium" id="premium_raw" name="premium[]" value="0" required="">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_premium_usd" id="premium_usd_raw" name="premium_usd[]" value="0" required="">
                        </div>
                        <div class="col-md-2 col-sm-3 col-xs-12">
                            <input type="text" class="form-control item_value_usd" id="value_usd_raw" name="value_usd[]" value="" readonly=true>
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                        </div>
                    </div>
                </div>
            </div>
            <div id='item_container'>
            {foreach key=INDEX item=LINE from=$RELATED_PRODUCTS}
                <div class="col-md-12 item_infromation_input item_{$INDEX+1}">
                    <div class="col-md-12" style="margin-top: 10px;"></div>
                    <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-1">
                            <button type="button" class='delete-row' style="height: 30px;width: 30px;">
                                <i class="fa fa-trash delete-row" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <select class="inputElement item_metal select2"   readonly=true  type="picklist" id="metal_{$INDEX+1}" name="metal[]">
                                <option value="{$LINE->get('gpmmetalid')}">{$LINE->getMetalName()}</option>
                            </select>
                            <input type="textarea" class="form-control item_remark" placeholder="Request on each bar items" style="height: 50px;margin-top: 2px;" id="item_remark_{$INDEX+1}" name="item_remark[]" value="{$LINE->get('remark')}">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_qty" min="0" id="qty_{$INDEX+1}" name="qty[]" value="{$LINE->get('qty')}" required="">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="text" class="form-control item_fineoz"  id="fineoz_{$INDEX+1}" name="fineoz[]" value="{$LINE->get('fine_oz')}" readonly=true>
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_premium" id="premium_{$INDEX+1}" name="premium[]" value="{$LINE->get('premium_or_discount')}" required="">
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                            <input type="number" class="form-control item_premium_usd" id="premium_usd_{$INDEX+1}" name="premium_usd[]" value="{$LINE->get('premium_or_discount_usd')}" required="">
                        </div>
                        <div class="col-md-2 col-sm-3 col-xs-12">
                            <input type="text" class="form-control item_value_usd"  id="value_usd_{$INDEX+1}" name="value_usd[]" value="{$LINE->get('value_usd')}" readonly=true>
                        </div>
                        <div class="col-md-1 col-sm-3 col-xs-12">
                        </div>
                    </div>
                </div>
                {/foreach}
            </div>
            <hr>
            <div class="col-md-12 item_infromation_total" >
                <div class="col-md-12" style="margin-top: 20px;"></div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-12">
                        <button style="width: 100%;" type="button" class="btn btn-primary  " id="add-btn">Add</button>
                    </div>
                    <div class="col-md-2">
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-12">
                        <h5>Total</h5>
                    </div>
                    <div class="col-md-2 col-sm-3 col-xs-12">
                    {if $MODE eq 'edit'}
                        <input type="text" class="form-control" id="total_oz" name="total_oz" value="{$RECORD->get('total_oz')}" readonly required="">
                    {else}
                        <input type="text" class="form-control" id="total_oz" name="total_oz" value="" readonly required="">
                    {/if}
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-12">
                    </div>
                    <div class="col-md-2 col-sm-3 col-xs-12">
                    {if $MODE eq 'edit'}
                        <input type="text" class="form-control" id="total_amount" name="total_amount" value="{$RECORD->get('total_amount')}" readonly required="">
                    {else}
                        <input type="text" class="form-control" id="total_amount" name="total_amount" value="" readonly required="">
                    {/if}
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-12">
                        <label style="display: block;" for="usr">&nbsp;</label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
            </div>
            <div class="col-md-12 item_infromation_fx_total" style="margin-bottom: 36px">
                <div class="col-md-12" ></div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-1">
                    </div>
                    
                    
                    </div>
                    <div class="col-md-1 col-sm-3 col-xs-12">
                        <label style="display: block;" for="usr">&nbsp;</label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
            </div>
        </div>
    </div>
{/strip}