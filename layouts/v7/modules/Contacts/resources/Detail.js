/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js(
  "Contacts_Detail_Js",
  {},
  {
    registerAjaxPreSaveEvents: function (container) {
      var thisInstance = this;

      app.event.on(Vtiger_Detail_Js.PreAjaxSaveEvent, function (e) {
        if (!thisInstance.checkForPortalUser(container)) {
          e.preventDefault();
        }
      });
    },
    /**
     * Function to check for Portal User
     */
    checkForPortalUser: function (form) {
      var element = jQuery('[name="portal"]', form);
      var response = element.is(":checked");

      if (response) {
        var primaryEmailField = jQuery('[data-name="email"]');

        if (primaryEmailField.length == 0) {
          app.helper.showErrorNotification({
            message: app.vtranslate("JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS"),
          });
          return false;
        }

        var primaryEmailValue = primaryEmailField["0"].data("value");
        if (primaryEmailValue == "") {
          app.helper.showErrorNotification({
            message: app.vtranslate(
              "JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER"
            ),
          });
          return false;
        }
      }
      return true;
    },
    registerActivtySummeryDateChange: function () {
      jQuery("#ActivtySummeryDate").on("change", function (e) {
        var selectElm = jQuery("#ActivtySummeryDate option:selected").val();
        window.location.replace(
          window.location.href.split("&ActivtySummeryDate=")[0] +
            "&ActivtySummeryDate=" +
            selectElm
        );
      });
    },

    registerCertificateClick: function () {
      jQuery("#generateHoldingCertificate").on("click", function (e) {
        certificateId = jQuery("#generateHoldingCertificate").data(
          "certificateid"
        );
        if (certificateId > 1) {
          var params = {
            module: "Documents",
            view: "FilePreview",
            record: certificateId,
          };
          app.request.post({ data: params }).then(function (err, data) {
            app.helper.showModal(data);
          });
        } else {
          app.helper
            .showConfirmationBox({
              message: "Would you like to create a new Holding Certificate?",
            })
            .then(
              function (data) {
                app.helper.showProgress();
                var params = {
                  module: "HoldingCertificate",
                  action: "GenerateCertificate",
                  contactId: app.getRecordId(),
                };

                app.request.post({ data: params }).then(function (err, data) {
                  console.log("registerCertificateClick", data);
                  docuemtntId = data.notes_id.split("x");
                  console.log("docuemtntId: ", docuemtntId);

                  jQuery("#generateHoldingCertificate").data(
                    "certificateid",
                    docuemtntId[1]
                  );
                  certificateId = docuemtntId[1];
                  app.helper.hideProgress();
                  var params = {
                    module: "Documents",
                    view: "FilePreview",
                    record: certificateId,
                  };

                  console.log('Params for FilePreview: ', params);
                  
                  app.request.post({ data: params }).then(function (err, data) {
                    console.log('FilePreview data: ', data);
                    app.helper.showModal(data);
                  });
                });
              },
              function (error, err) {}
            );
        }
      });
    },

    registerActivitySummaryCurrencyChange: function () {
      // var thisInstance = this;

      jQuery("#currencySelect").on("change", function () {
        var selectedCurrency = jQuery(this).val();

        // Build new AJAX URL (keeps record/module params)
        var baseUrl = window.location.href.split("&ActivtySummeryCurrency=")[0];

        var url = baseUrl + "&ActivtySummeryCurrency=" + selectedCurrency;

        // Trigger AJAX reload like native widgets
        // thisInstance.getDetailViewContents(url);
        // window.location.replace(url);
        window.location.href = url;
      });
    },
    /**
     * Function which will register all the events
     */
    registerEvents: function () {
      var form = this.getForm();
      this._super();
      this.registerAjaxPreSaveEvents(form);
      this.registerActivtySummeryDateChange();
      this.registerCertificateClick();
      this.registerActivitySummaryCurrencyChange();
    },
  }
);
