/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js(
  "GPMIntent_Edit_Js",
  {},
  {
    itemIndex: 1,
    itemList: false,
    metalOption: Array,
    selectedMetal: false,

    formatter: new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",

      // These options are needed to round to whole numbers if that's what you want.
      minimumFractionDigits: 3, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
      //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
    }),

    setSpotPrice: function (selectedMetal) {
      var thisInstance = this;
      console.log("setSpotPrice", thisInstance, selectedMetal);

      //if (jQuery('input[name="indicative_spot_price"]').val() == '') {
      thisInstance
        .getCurruntMetalSpotPrice(selectedMetal)
        .then(function (data) {
          data = JSON.parse(data);
          jQuery(
            'input[name="indicative_spot_price"], input[name="cf_1136"]'
          ).val(data.price);
        });
      //}
    },
    setupMetalOption: function (selectedMetal) {
      var thisInstance = this;
      if (thisInstance.itemList === false) {
        thisInstance.getAvailableMetals().then(function (data) {
          thisInstance.itemList = JSON.parse(data);
          thisInstance.createMetalOption(selectedMetal);
        });
      } else {
        thisInstance.createMetalOption(selectedMetal);
      }
    },
    createMetalOption: function (selectedMetal) {
      var thisInstance = this;
      if (
        (thisInstance.metalOption === false ||
          typeof thisInstance.metalOption[selectedMetal] == "undefined") &&
        thisInstance.itemList[selectedMetal]
      ) {
        Object.keys(thisInstance.itemList[selectedMetal]).forEach(
          (item, index) => {
            thisInstance.metalOption[selectedMetal] =
              thisInstance.metalOption[selectedMetal] +
              '<option value="' +
              item +
              '">' +
              thisInstance.itemList[selectedMetal][item]["product_name"] +
              "</option>";
          }
        );
      }
    },
    getCurruntMetalSpotPrice: function (metal) {
      var aDeferred = jQuery.Deferred();
      var params = {
        module: "MetalPrice",
        action: "GetMetalPrice",
        metal: metal,
      };

      app.helper.showProgress();
      app.request.post({ data: params }).then(function (error, data) {
        app.helper.hideProgress();
        if (error == null) {
          aDeferred.resolve(data);
        } else {
          aDeferred.reject(error);
        }
      });
      return aDeferred.promise();
    },
    getAvailableMetals: function () {
      var aDeferred = jQuery.Deferred();
      var params = {
        // module: "GPMMetal",
        module: "Assets",
        action: "GetAllMetals",
      };

      app.helper.showProgress();
      app.request.post({ data: params }).then(function (error, data) {
        app.helper.hideProgress();
        if (error == null) {
          aDeferred.resolve(data);
        } else {
          aDeferred.reject(error);
        }
      });
      return aDeferred.promise();
    },

    registerMetalTypeChangeEvent: function () {
      var thisInstance = this;

      jQuery('select[name="gpm_metal_type"]').on(
        "change.select2",
        function (e) {
          selectedMetal = jQuery(e.currentTarget).val();
          thisInstance.setSpotPrice(selectedMetal);
          thisInstance.setupMetalOption(selectedMetal);
          thisInstance.selectedMetal = selectedMetal;
        }
      );
    },

    registerAddButton: function () {
      var thisInstance = this;
      jQuery("#add-btn").on("click", function () {
        console.log("thisInstance", thisInstance);

        if (
          thisInstance.selectedMetal === false ||
          jQuery('select[name="gpm_order_type"]').val() == "" ||
          jQuery('select[name="cf_1132"]').val() == ""
        ) {
          app.helper.showErrorNotification({
            message: "Please select the Metal Type, Currency and Order Type!",
          });
        } else {
          itemLine = jQuery("#item_raw")
            .html()
            .replaceAll("raw", thisInstance.itemIndex)
            .replaceAll(
              "{{xxx}}",
              thisInstance.metalOption[thisInstance.selectedMetal]
            );

          jQuery("#item_container").append(itemLine);
          jQuery("#metal_" + thisInstance.itemIndex).select2();
          thisInstance.itemIndex++;
          jQuery('select[name="gpm_metal_type"]').select2("readonly", true);
          jQuery('select[name="gpm_order_type"]').select2("readonly", true);
        }
      });
    },

    registerLineItemDelete: function () {
      var thisInstance = this;
      jQuery("body").on("click", ".delete-row", function (e) {
        jQuery(e.currentTarget).closest("div.item_infromation_input").remove();
        if (jQuery("#item_container").children().length == 0) {
          jQuery('select[name="gpm_metal_type"]').select2("readonly", false);
          jQuery('select[name="gpm_order_type"]').select2("readonly", false);
        }
        thisInstance.calculateTotal();
        thisInstance.calculateForeignValue();
      });
    },
    registerOrderTypeChange: function () {
      jQuery('select[name="gpm_order_type"]').on(
        "change.select2",
        function (e) {
          if (jQuery('select[name="gpm_order_type"]').val() == "Sale") {
            jQuery(".pre_disc").html("Discount (%)");
          } else {
            jQuery(".pre_disc").html("Premium (%)");
          }
        }
      );
    },
    calculateTheCurrentLineItem: function (line) {
      var thisInstance = this;
      thisInstance.calculateTheCurrentLineItemFineOz(line);
      thisInstance.calculateTheCurrentLineItemUSD(line);
    },
    calculateTheCurrentLineItemFineOz: function (line) {
      var thisInstance = this;
      selectedItem = line.find(".item_metal option:selected").val();

      selectedItemFineOz =
        thisInstance.itemList[thisInstance.selectedMetal][selectedItem][
          "fine_oz"
        ];
      if (selectedItemFineOz != 0) {
        selectedItemQty = line.find(".item_qty").val();
        itemTotalFineOz = selectedItemFineOz * selectedItemQty;
        line.find(".item_fineoz").val(itemTotalFineOz.toFixed(8));
      }
    },
    calculateTheCurrentLineItemUSD: function (line) {
      var thisInstance = this;
      itemTotalOz = line.find(".item_fineoz").val();
      indicativeSpotPrice = jQuery('input[name="indicative_spot_price"]').val();
      exactSpotPrice = jQuery('input[name="spot_price"]').val();
      currentSpotPrice =
        parseFloat(exactSpotPrice) > 0 ? exactSpotPrice : indicativeSpotPrice;
      item_pre_disc = line.find(".item_premium").val();
      item_pre_disc_usd = line.find(".item_premium_usd").val();
      prem_disc = 0;
      if (jQuery('select[name="gpm_order_type"]').val() == "Sale") {
        prem_disc = 1 - item_pre_disc / 100;
      } else {
        prem_disc = 1 + item_pre_disc / 100;
      }

      console.log(item_pre_disc_usd);
      if (item_pre_disc_usd != 0) {
        itemUSD =
          itemTotalOz * currentSpotPrice + parseFloat(item_pre_disc_usd);
      } else {
        itemUSD = itemTotalOz * currentSpotPrice * prem_disc;
        line
          .find(".item_premium_usd")
          .val(itemUSD - itemTotalOz * currentSpotPrice);
      }

      line.find(".item_value_usd").val(itemUSD.toFixed(2));
    },
    calculateTotal: function () {
      totalOz = 0;
      totalUsd = 0;
      jQuery(".item_fineoz").each(function () {
        itemOz = parseFloat(jQuery(this).val());
        totalOz = isNaN(itemOz) ? totalOz + 0 : totalOz + itemOz;
      });
      jQuery(".item_value_usd").each(function () {
        itemUsd = parseFloat(jQuery(this).val());
        totalUsd = isNaN(itemUsd) ? totalUsd + 0 : totalUsd + itemUsd;
      });
      jQuery("#total_oz").val(totalOz.toFixed(8));
      jQuery("#total_amount").val(totalUsd.toFixed(2));
    },
    registerItemChanges: function () {
      var thisInstance = this;
      jQuery("body").on(
        "change",
        ".item_metal, .item_qty, .item_premium, .item_premium_usd",
        function (e) {
          line = jQuery(e.currentTarget).closest("div.item_infromation_input");
          thisInstance.calculateTheCurrentLineItem(line);
          thisInstance.calculateTotal();
          thisInstance.calculateForeignValue();
        }
      );
    },
    registerCurrencyChangeEvent: function () {
      var thisInstance = this;
      jQuery('select[name="package_currency"]').on(
        "change.select2",
        function (e) {
          forexDate = JSON.parse(jQuery("#GPMForexValue").val());
          selectedCurrency = jQuery(e.currentTarget).val();
          if (selectedCurrency == "USD") {
            jQuery('input[name="indicative_fx_spot"]').val(1);
          } else if (selectedCurrency == "SGD") {
            jQuery('input[name="indicative_fx_spot"]').val(forexDate.usd_sgd);
          } else if (selectedCurrency == "EUR") {
            sgd_usdspot = forexDate.usd_sgd;
            selectedCurrencySGDSpotName =
              selectedCurrency.toLowerCase() + "_sgd";
            selectedCurrencySGDSpot = forexDate[selectedCurrencySGDSpotName];
            selectedCurrencyUSDSpot =
              (selectedCurrencySGDSpot * 1) / sgd_usdspot;
            jQuery('input[name="indicative_fx_spot"]').val(
              selectedCurrencyUSDSpot.toFixed(4)
            );
          } else {
            sgd_usdspot = forexDate.usd_sgd;
            selectedCurrencySGDSpotName =
              selectedCurrency.toLowerCase() + "_sgd";
            selectedCurrencySGDSpot = forexDate[selectedCurrencySGDSpotName];
            selectedCurrencyUSDSpot =
              (sgd_usdspot * 1) / selectedCurrencySGDSpot;
            jQuery('input[name="indicative_fx_spot"]').val(
              selectedCurrencyUSDSpot.toFixed(4)
            );
          }
          jQuery('input[name="package_price"]').trigger("keydown");
        }
      );
    },
    registerPackageAmountChange: function () {
      jQuery('input[name="package_price"]').on(
        "keydown keypress keyup blur",
        function (e) {
          FxSpot = jQuery('input[name="indicative_fx_spot"]').val();
          selectedCurrency = jQuery('select[name="package_currency"]').val();

          if (selectedCurrency == "") {
            return;
          } else if (
            selectedCurrency == "SGD" ||
            selectedCurrency == "CHF" ||
            selectedCurrency == "HKD"
          ) {
            FxSpot = 1 / FxSpot;
          }
          amount = jQuery(e.currentTarget).val();
          if (parseFloat(FxSpot) == 0) {
            FxSpot = 1;
          }
          calCulatedAmount = FxSpot * amount;

          jQuery('input[name="package_price_usd"]').val(
            calCulatedAmount.toFixed(2)
          );
        }
      );
    },
    registerEditOrCreate: function () {
      var thisInstance = this;
      if (jQuery('input[name="record"]').val() > 0) {
        selectedMetal = jQuery('select[name="gpm_metal_type"]').val();
        thisInstance.setupMetalOption(selectedMetal);
        thisInstance.selectedMetal = selectedMetal;
        if (jQuery("#item_container").children().length > 0) {
          jQuery('select[name="gpm_metal_type"]').select2("readonly", true);
          jQuery('select[name="gpm_order_type"]').select2("readonly", true);
        }

        thisInstance.itemIndex =
          jQuery("#item_container").children().length + 1;
      }
      //jQuery('input[name="indicative_fx_spot"]').attr("readonly", true);
      jQuery('input[name="package_price_usd"]').attr("readonly", true);
    },
    registerSpotPriceChange: function () {
      var thisInstance = this;
      jQuery(
        'input[name="spot_price"], input[name="fx_spot_price"],input[name="indicative_fx_spot"],#GPMIntent_editView_fieldName_spot_price, #GPMIntent_editView_fieldName_fx_spot_price, #GPMIntent_editView_fieldName_indicative_spot_price, #GPMIntent_editView_fieldName_indicative_fx_spot'
      ).on("change keydown keypress keyup blur", function (e) {
        jQuery("#item_container > div.item_infromation_input").each(function (
          i
        ) {
          line = jQuery(this);
          thisInstance.calculateTheCurrentLineItem(line);
          thisInstance.calculateTotal();
          thisInstance.calculateForeignValue();
        });
      });
    },
    calculateForeignValue: function () {
      // EUR / USD
      // GBP / USD
      // USD / SGD
      // USD / CHF
      // USD / MYR
      // USD / HKD

      FxSpot =
        parseFloat(jQuery('input[name="fx_spot_price"]').val()) > 0
          ? parseFloat(jQuery('input[name="fx_spot_price"]').val())
          : parseFloat(jQuery('input[name="indicative_fx_spot"]').val());
      if (parseFloat(FxSpot) == 0) {
        FxSpot = 1;
      }
      selectedCurrency = jQuery('select[name="package_currency"]').val();

      totalUsdVal = jQuery("#total_amount").val();
      calCulatedAmount = 0;
      if (totalUsdVal > 0) {
        if (selectedCurrency == "") {
          return;
        } else if (
          selectedCurrency == "SGD" ||
          selectedCurrency == "CHF" ||
          selectedCurrency == "HKD"
        ) {
          calCulatedAmount = totalUsdVal * FxSpot;
        } else {
          calCulatedAmount = totalUsdVal / FxSpot;
        }
      }

      jQuery('input[name="total_foreign_amount"]').val(
        calCulatedAmount.toFixed(2)
      );
    },
    setFineOzForNoneStrdBars: function () {
      var thisInstance = this;
      jQuery("body").on("click keypress", ".item_fineoz", function (e) {
        var curElmVal = parseFloat(jQuery(this).val());

        jQuery(this).prop("readonly", false);

        var key = e.which;
        if (key == 13) {
          jQuery(this).prop("readonly", true);
          line = jQuery(e.currentTarget).closest("div.item_infromation_input");
          selectedItem = line.find(".item_metal option:selected").val();
          newVal = parseFloat(jQuery(this).val());

          //thisInstance.itemList[thisInstance.selectedMetal][selectedItem]['fine_oz'] = newVal;
          line.find(".item_premium_usd").val("");
          thisInstance.calculateTheCurrentLineItem(line);
          thisInstance.calculateTotal();
          thisInstance.calculateForeignValue();

          app.helper.showAlertBox({
            title: "Success!",
            message:
              "<b>For item " +
              thisInstance.itemList[thisInstance.selectedMetal][selectedItem][
                "product_name"
              ] +
              ", Total " +
              newVal +
              " is set as FineOz.</b>",
          });
          return false;
        }
      });
    },
    /**
     * Function which will register basic events which will be used in quick create as well
     *
     */
    registerBasicEvents: function (container) {
      this._super(container);
      this.registerMetalTypeChangeEvent();
      this.registerAddButton();
      this.registerLineItemDelete();
      this.registerOrderTypeChange();
      this.registerItemChanges();
      this.registerCurrencyChangeEvent();
      this.registerPackageAmountChange();
      this.registerEditOrCreate();
      this.registerSpotPriceChange();
      this.setFineOzForNoneStrdBars();
    },
  }
);
