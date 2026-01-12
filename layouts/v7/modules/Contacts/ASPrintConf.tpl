{* ========= Correct vTiger datepicker assets (Eternicode) ========= *}

<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/bootstrap.min.css' />
<script type="text/javascript" src="libraries/bootstrap/js/bootstrap.min.js"></script>

<link rel="stylesheet"
    href="libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/bootstrap-datepicker3.css?v=8.4.0">
<script src="libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/bootstrap-datepicker.js?v=8.4.0"></script>


<style>
    #myModal {
        display: none;
        position: fixed;
        z-index: 9999 !important;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
    }

    #myModal .modal-content {
        background: #fff;
        width: 520px;
        max-width: 92%;
        margin: 120px auto;
        padding: 20px;
        border-radius: 6px;
        position: relative;
        z-index: 10000;
    }

    .printConfClose {
        float: right;
        font-size: 26px;
        cursor: pointer;
        line-height: 1;
    }

    .fieldRow {
        margin-bottom: 12px;
    }

    #myModal label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    /* make sure datepicker popup is above the overlay */
    .datepicker {
        z-index: 200000 !important;
    }

    #printConfSave {
        background: #bea364;
        color: #fff;
        padding: 10px 14px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }

    .datepicker-days {
        padding: 2mm;
    }

    .datepicker-days th,
    .datepicker-days td {
        cursor: pointer;
    }

    /* Keep the picker layout intact even if page CSS messes with spans/tables */
    .datepicker table {
        width: 100% !important;
    }

    .datepicker-months span.month,
    .datepicker-years span.year,
    .datepicker-decades span.decade,
    .datepicker-centuries span.century {
        display: inline-block !important;
        width: 25% !important;
        /* 4 per row */
        margin: 1% 0 !important;
        white-space: nowrap !important;
        float: none !important;
    }

    .datepicker-months td,
    .datepicker-years td,
    .datepicker-decades td,
    .datepicker-centuries td {
        white-space: normal !important;
    }
</style>

<div id="myModal">
    <div class="modal-content">
        <span class="printConfClose">&times;</span>

        <h3 style="margin-top:0;">Print Settings</h3>

        <div class="fieldRow">
            <label for="start_date">Start Date</label>
            <div class="input-group inputElement">
                <input type="text" id="start_date" class="dateField form-control" data-date-format="yyyy-mm-dd"
                    autocomplete="off">
                <span class="input-group-addon js-open-start"><i class="fa fa-calendar"></i></span>
            </div>
        </div>

        <div class="fieldRow">
            <label for="end_date">End Date</label>
            <div class="input-group inputElement">
                <input type="text" id="end_date" class="dateField form-control" data-date-format="yyyy-mm-dd"
                    autocomplete="off">
                <span class="input-group-addon js-open-end"><i class="fa fa-calendar"></i></span>
            </div>
        </div>


        {* Hide customer info *}
        <div class="fieldRow">
            <label style="font-weight:normal;">
                <input type="checkbox" id="hideCustomerInfo" value="1">
                Hide Customer Info
            </label>
        </div>

        {* Save link: base only, JS adds dates *}
        <a id="printConfSave"
            href="index.php?module=Contacts&view=ActivtySummeryPrintPreview&record={$RECORD_MODEL->getId()}&docNo={$smarty.request.docNo}">
            Save
        </a>
    </div>
</div>