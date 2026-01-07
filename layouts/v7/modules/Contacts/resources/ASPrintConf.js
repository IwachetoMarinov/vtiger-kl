jQuery(function () {
  console.log("[PrintConf] JS loaded");

  function initDatePickers() {
    console.log("[PrintConf] initDatePickers() called");

    console.log("[PrintConf] datepicker type:", typeof jQuery.fn.datepicker);
    console.log("[PrintConf] start exists:", jQuery("#start_date").length);
    console.log("[PrintConf] end exists:", jQuery("#end_date").length);

    if (typeof jQuery.fn.datepicker === "undefined") {
      console.error("[PrintConf] datepicker not loaded");
      return;
    }

    if (!jQuery("#start_date").length || !jQuery("#end_date").length) {
      console.error("[PrintConf] Inputs not found in DOM");
      return;
    }

    const format = jQuery("#start_date").data("date-format") || "mm-dd-yyyy";
    console.log("[PrintConf] format:", format);

    // Hard reset
    try {
      jQuery("#start_date").datepicker("destroy");
    } catch (e) {}
    try {
      jQuery("#end_date").datepicker("destroy");
    } catch (e) {}

    // IMPORTANT: Use body container (most reliable in fixed modals)
    jQuery("#start_date")
      .datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        container: "body",
      })
      .on("changeDate", function () {
        jQuery(this).datepicker("hide");
      });

    jQuery("#end_date")
      .datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        container: "body",
      })
      .on("changeDate", function () {
        jQuery(this).datepicker("hide");
      });

    console.log(
      "[PrintConf] datepicker inited. start data:",
      jQuery("#start_date").data("datepicker")
    );

    // Force z-index always
    jQuery(document)
      .off("mousedown.printconfZ")
      .on("mousedown.printconfZ", function () {
        jQuery(".datepicker").css("z-index", 200000);
      });

    // Force open on click/focus (not just focus)
    jQuery("#start_date")
      .off(".printconfOpen")
      .on("click.printconfOpen focus.printconfOpen", function () {
        console.log("[PrintConf] open start");
        jQuery(this).datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    jQuery("#end_date")
      .off(".printconfOpen")
      .on("click.printconfOpen focus.printconfOpen", function () {
        console.log("[PrintConf] open end");
        jQuery(this).datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    // Icon click (if present)
    jQuery(".js-open-start")
      .off(".printconfOpen")
      .on("click.printconfOpen", function () {
        console.log("[PrintConf] icon open start");
        jQuery("#start_date").datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    jQuery(".js-open-end")
      .off(".printconfOpen")
      .on("click.printconfOpen", function () {
        console.log("[PrintConf] icon open end");
        jQuery("#end_date").datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    // Range constraint
    jQuery("#start_date")
      .off(".printconfRange")
      .on("changeDate.printconfRange", function (e) {
        console.log("[PrintConf] start changed", e.date);
        jQuery("#end_date").datepicker("setStartDate", e.date);
      });

    jQuery("#end_date")
      .off(".printconfRange")
      .on("changeDate.printconfRange", function (e) {
        console.log("[PrintConf] end changed", e.date);
        jQuery("#start_date").datepicker("setEndDate", e.date);
      });
  }

  function openModal() {
    console.log("[PrintConf] openModal()");
    jQuery("#myModal").show();

    // Make sure modal is actually visible
    console.log(
      "[PrintConf] modal visible:",
      jQuery("#myModal").is(":visible")
    );

    // init after visible
    setTimeout(initDatePickers, 50);
  }

  function closeModal() {
    console.log("[PrintConf] closeModal()");
    jQuery("#myModal").hide();
  }

  // OPEN
  jQuery("body").on("click", "#printConf", function (e) {
    e.preventDefault();
    console.log("[PrintConf] #printConf clicked");
    openModal();
  });

  // CLOSE (X)
  jQuery("body").on("click", ".printConfClose", function (e) {
    e.preventDefault();
    closeModal();
  });

  // CLOSE overlay
  jQuery("body").on("click", "#myModal", function (e) {
    if (e.target && e.target.id === "myModal") {
      closeModal();
    }
  });

  jQuery("#start_date").on("changeDate", function () {
    console.log("Close Datepicker on start date");

    jQuery(this).datepicker("hide");
  });

  jQuery("#end_date").on("changeDate", function () {
    console.log("Close Datepicker on end date");
    jQuery(this).datepicker("hide");
  });

  // SAVE
  jQuery("body").on("click", "#printConfSave", function (e) {
    e.preventDefault();

    var start = jQuery("#start_date").val();
    var end = jQuery("#end_date").val();
    var hide = jQuery("#hideCustomerInfo").is(":checked") ? 1 : 0;

    if (!start || !end) {
      alert("Please select Start Date and End Date");
      return;
    }

    var baseUrl = jQuery(this).attr("href");

    window.location.href =
      baseUrl +
      "&start_date=" +
      encodeURIComponent(start) +
      "&end_date=" +
      encodeURIComponent(end) +
      "&hideCustomerInfo=" +
      hide;
  });
});
