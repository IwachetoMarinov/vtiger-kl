jQuery(function () {
  function initDatePickers() {
    if (typeof jQuery.fn.datepicker === "undefined") return;

    if (!jQuery("#start_date").length || !jQuery("#end_date").length) return;

    try {
      jQuery("#start_date").datepicker("destroy");
    } catch (e) {}
    try {
      jQuery("#end_date").datepicker("destroy");
    } catch (e) {}

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
        jQuery(this).datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    jQuery("#end_date")
      .off(".printconfOpen")
      .on("click.printconfOpen focus.printconfOpen", function () {
        jQuery(this).datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    // Icon click (if present)
    jQuery(".js-open-start")
      .off(".printconfOpen")
      .on("click.printconfOpen", function () {
        jQuery("#start_date").datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    jQuery(".js-open-end")
      .off(".printconfOpen")
      .on("click.printconfOpen", function () {
        jQuery("#end_date").datepicker("show");
        jQuery(".datepicker").css("z-index", 200000);
      });

    // Range constraint
    jQuery("#start_date")
      .off(".printconfRange")
      .on("changeDate.printconfRange", function (e) {
        jQuery("#end_date").datepicker("setStartDate", e.date);
      });

    jQuery("#end_date")
      .off(".printconfRange")
      .on("changeDate.printconfRange", function (e) {
        jQuery("#start_date").datepicker("setEndDate", e.date);
      });
  }

  function openModal() {
    jQuery("#myModal").show();

    // init after visible
    setTimeout(initDatePickers, 50);
  }

  function closeModal() {
    jQuery("#myModal").hide();
  }

  // OPEN
  jQuery("body").on("click", "#printConf", function (e) {
    e.preventDefault();
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
    jQuery(this).datepicker("hide");
  });

  jQuery("#end_date").on("changeDate", function () {
    jQuery(this).datepicker("hide");
  });

  // RESET
  jQuery("body").on("click", "#resetDatesButton", function (e) {
    e.preventDefault();
    jQuery("#start_date").val("");
    jQuery("#end_date").val("");
  });

  // Download button functionality
  jQuery("body").on("click", "#downloadBtn", function (e) {
    e.preventDefault();
    const buttonUrl = jQuery(this).find("a").attr("href");

    const url = window.location.href;

    // Get start and end dates from url parameters
    const urlParams = new URLSearchParams(url.split("?")[1]);
    const startDate = urlParams.get("start_date");
    const endDate = urlParams.get("end_date");

    let downloadUrl = buttonUrl;

    if (startDate && endDate) {
      downloadUrl += "&start_date=" + encodeURIComponent(startDate);
      downloadUrl += "&end_date=" + encodeURIComponent(endDate);
    }

    console.log("downloadUrl: ", downloadUrl);

    window.location.href = downloadUrl;
  });

  // SAVE
  jQuery("body").on("click", "#printConfSave", function (e) {
    console.log("Save button clicked");

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
