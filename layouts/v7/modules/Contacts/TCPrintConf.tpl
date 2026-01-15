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
        cursor: pointer;
    }
</style>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="printConfClose">&times;</span>

        <h2>Print Settings</h2>
        <br>

        {assign var=hideInfo value=$smarty.request.hideCustomerInfo|default:0}

        <span style="margin-top: 10px; display:block">
            Hide Customer Info :
            <input type="checkbox" id="hideCustomerInfo" name="hideCustomerInfo" value="1" {if $hideInfo}checked{/if}>
        </span>

        <br>

        <span>
            <a id="printConfSave"
                style="color: white;text-align: center;padding: 10px;text-decoration: none;background-color: #bea364;"
                href="#" onclick="
     var hide = document.getElementById('hideCustomerInfo')?.checked ? 1 : 0;
     window.location.href='index.php?module=Contacts&view=TCPrintPreview&record={$RECORD_MODEL->getId()}&tableName={$smarty.request.tableName|escape:'url'}&docNo={$smarty.request.docNo|escape:'url'}{if $INTENT}&fromIntent={$smarty.request.fromIntent|escape:'url'}{/if}&hideCustomerInfo=' + hide;
     return false;
   ">
                Save
            </a>

        </span>

    </div>
</div>

<style>
    .select2-container .select2-choice>.select2-chosen {
        width: 171px;
    }
</style>