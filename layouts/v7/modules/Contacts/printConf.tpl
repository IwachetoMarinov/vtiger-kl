<style>
    /* The Modal (background) */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 100px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 25%;
    }

    /* The Close Button */
    .printConfClose {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .printConfClose:hover,
    .printConfClose:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- The Modal -->
<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="printConfClose">&times;</span>
        <h2>Print Settings</h2>
        <br>
        Select a Bank : <select class="inputElement select2" name="bank_accounts" id="bank_accounts">
            <option value="">Select Bank Account</option>
            {foreach item=account from=$ALL_BANK_ACCOUNTS}
                <option {if $smarty.request.bank  eq $account->getId() } selected {/if} value="{$account->getId()}">
                    {$account->get('bank_alias_name')}</option>

            {/foreach}
        </select>
        <br>
        <span style="margin-top: 10px; display:block">Hide Customer Info : <input type="checkbox" id="hideCustomerInfo"
                name="hideCustomerInfo" {if $smarty.request.hideCustomerInfo}value="1" checked 
                {else}value="0"
                {/if}></span>
        <br>

        {assign var="view" value=$smarty.request.view|default:'DocumentPrintPreview'}

        {if isset($SELECTED_BANK) && $SELECTED_BANK && method_exists($SELECTED_BANK, 'getId')}
            <span>
                <a id="printConfSave"
                    style="color: white;text-align: center;padding: 10px;text-decoration: none;background-color: #bea364;"
                    href="index.php?module=Contacts&view={$view}&record={$RECORD_MODEL->getId()}&tableName={$smarty.request.tableName}&docNo={$smarty.request.docNo}&bank={$SELECTED_BANK->getId()}{if $INTENT}&fromIntent={$smarty.request.fromIntent}{/if}&docType={$smarty.request.docType}&hideCustomerInfo={$smarty.request.hideCustomerInfo}">Save</a>
            </span>
        {/if}
    </div>
</div>
<style>
    .select2-container .select2-choice>.select2-chosen {
        width: 171px;
    }
</style>