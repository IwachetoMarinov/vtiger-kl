jQuery("body").on("click", "#printConf", function (e) {
  var modal = document.getElementById("myModal");

  var selectedBank = jQuery(".selected-bank")?.val();

  if (selectedBank)
    jQuery("#bank_accounts").val(selectedBank).trigger("change");

  modal.style.display = "block";
});

jQuery("body").on("click", ".printConfClose", function (e) {
  var modal = document.getElementById("myModal");
  modal.style.display = "none";
});

jQuery("body").on("click", "#printConfSave", function (e) {
  var modal = document.getElementById("myModal");
  modal.style.display = "none";
});

jQuery(document).ready(function () {
  jQuery(".select2")?.select2();
});

jQuery("body").on("change", "#bank_accounts", function (e) {
  var element = jQuery(e.currentTarget);
  var bankId = Number(element.val());
  saveUrl = jQuery("#printConfSave").attr("href");
  splitSaveUrl = saveUrl.split("&bank");
  newSaveUrl =
    splitSaveUrl[0] +
    "&bank=" +
    bankId +
    splitSaveUrl[1].substr(splitSaveUrl[1].indexOf("&"));
  jQuery("#printConfSave").attr("href", newSaveUrl);
});

jQuery("body").on("change", "#hideCustomerInfo", function (e) {
  var element = jQuery(e.currentTarget);
  var hideCustomerInfoInit = jQuery("#hideCustomerInfo").val();

  if (hideCustomerInfoInit == 1) {
    jQuery("#hideCustomerInfo").val(0);
    hideCustomerInfo = 0;
  } else {
    jQuery("#hideCustomerInfo").val(1);
    hideCustomerInfo = 1;
  }

  saveUrl = jQuery("#printConfSave").attr("href");
  splitSaveUrl = saveUrl.split("&hideCustomerInfo");
  newSaveUrl = splitSaveUrl[0] + "&hideCustomerInfo=" + hideCustomerInfo;
  jQuery("#printConfSave").attr("href", newSaveUrl);
});
