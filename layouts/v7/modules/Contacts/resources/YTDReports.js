function generateYTDReport(contactId) {
  AppConnector.request({
    module: "YTDReports",
    action: "Generate",
    contact_id: contactId,
  }).then(
    function (response) {
      console.log(response);

      if (response && response.result && response.result.message) {
        alert(response.result.message);
      } else {
        alert("YTD Report generated");
      }

      //   reload the page to show the new report in the related list
      location.reload();
    },
    function (error) {
      alert("YTD Report generated");

      location.reload();
    },
  );
}
