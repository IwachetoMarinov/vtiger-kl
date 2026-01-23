jQuery(function () {
  // Contacts only
  if (
    typeof app === "undefined" ||
    app.getModuleName() !== "Contacts" ||
    !app.getRecordId()
  ) {
    return;
  }

  console.log("Init Multiple file upload");

  // Date range functionality Transaction history section
  jQuery("body").on("click", ".date-range-button", function (e) {
    console.log("Save button clicked");

    e.preventDefault();

    var start = jQuery("#start_date").val();
    var end = jQuery("#end_date").val();

    if (!start || !end) {
      alert("Please select Start Date and End Date");
      return;
    }

    const originalUrl = window.location.href;

    // Remove existing parameters if present
    let baseUrl = originalUrl.split("&start_date=")[0];
    baseUrl = baseUrl.split("&end_date=")[0];

    window.location.href =
      baseUrl +
      "&start_date=" +
      encodeURIComponent(start) +
      "&end_date=" +
      encodeURIComponent(end);
  });

  // Switch for order by balance
  jQuery(document).on("change", ".summary_toggle", function () {
    const isOn = jQuery(this).is(":checked");
    const orderByParam = isOn ? "desc" : "asc";

    const url = new URL(window.location.href);

    // Set or update the param
    url.searchParams.set("orderBy", orderByParam);

    // Redirect
    window.location.href = url.toString();
  });

  function asArray(fileList) {
    return Array.prototype.slice.call(fileList || []);
  }

  jQuery(document).on("shown.bs.modal", ".modal", function () {
    var $modal = jQuery(this);

    // Identify the Documents upload modal
    var $file = $modal.find('input[type="file"][name="filename"]');
    if (!$file.length) return;

    // prevent double init per modal instance
    if ($modal.data("multiUploadInit")) return;
    $modal.data("multiUploadInit", true);

    // enable multiple file selection
    $file.attr("multiple", "multiple");

    // Store selected/dropped files here until user clicks Upload/Save
    $modal.data("pendingFiles", []);

    // Status area
    var $status = $modal.find(".js-multi-status");
    if (!$status.length) {
      $modal
        .find("#dragandrophandler")
        .append(
          '<div class="js-multi-status" style="margin-top:10px;font-size:13px;color:#555;"></div>',
        );
      $status = $modal.find(".js-multi-status");
    }

    function showSelectedCount() {
      var files = $modal.data("pendingFiles") || [];
      if (!files.length) {
        $status.html("No files selected.");
      } else if (files.length === 1) {
        $status.html("<b>Selected:</b> 1 file<br>" + files[0].name);
      } else {
        $status.html("<b>Selected:</b> " + files.length + " files");
      }
    }

    function setPendingFiles(files) {
      $modal.data("pendingFiles", files);
      showSelectedCount();
    }

    // When user selects files using the vtiger button -> DO NOT upload, just store
    $file.off("change.multi").on("change.multi", function () {
      var files = asArray(this.files);
      setPendingFiles(files);

      // reset input so selecting same files again triggers change event
      jQuery(this).val("");
    });

    // Drag & drop: store files, don't upload
    var $dropArea = $modal.find("#dragandrophandler");
    if ($dropArea.length) {
      $dropArea.off("dragover.multi drop.multi");

      $dropArea.on("dragover.multi", function (e) {
        e.preventDefault();
        e.stopPropagation();
      });

      $dropArea.on("drop.multi", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files.length) {
          setPendingFiles(asArray(dt.files));
        }
      });
    }

    // Build FormData from existing form fields (except the file input)
    function buildBaseFormData() {
      var fd = new FormData();
      var $form = $modal.find("form[name='upload']");

      $form.find("input, textarea, select").each(function () {
        var $el = jQuery(this);
        var name = $el.attr("name");
        if (!name) return;

        if ($el.attr("type") === "file") return;
        if (name === "filename") return;

        if (
          ($el.attr("type") === "checkbox" || $el.attr("type") === "radio") &&
          !$el.is(":checked")
        ) {
          return;
        }

        fd.append(name, $el.val());
      });

      return fd;
    }

    // Upload queue (called ONLY on click)
    function uploadPendingFiles() {
      var files = $modal.data("pendingFiles") || [];
      if (!files.length) {
        alert("Please select files first.");
        return;
      }

      // Disable button while uploading
      var $btn = $modal.find("#js-upload-document");
      $btn.prop("disabled", true).addClass("disabled");

      var i = 0,
        ok = 0,
        fail = 0;

      function next() {
        if (i >= files.length) {
          $status.html(
            "<b>Finished</b><br>Uploaded: " + ok + "<br>Failed: " + fail,
          );

          setTimeout(function () {
            try {
              $modal.modal("hide");
            } catch (e) {}
            window.location.reload();
          }, 900);

          return;
        }

        var file = files[i];
        var fd = buildBaseFormData();

        // Required fields fallback (based on your payload)
        if (!fd.has("module")) fd.append("module", "Documents");
        if (!fd.has("action")) fd.append("action", "SaveAjax");
        if (!fd.has("document_source")) fd.append("document_source", "Vtiger");
        if (!fd.has("relationOperation"))
          fd.append("relationOperation", "true");
        if (!fd.has("sourceModule")) fd.append("sourceModule", "Contacts");
        if (!fd.has("sourceRecord"))
          fd.append("sourceRecord", app.getRecordId());
        if (!fd.has("filelocationtype")) fd.append("filelocationtype", "I");
        if (!fd.has("notecontent")) fd.append("notecontent", "");

        // Title per file
        fd.set("notes_title", file.name);

        // Add file
        fd.append("filename", file);

        $status.html(
          "<b>Uploading</b> " +
            (i + 1) +
            " / " +
            files.length +
            "<br>" +
            file.name,
        );

        jQuery
          .ajax({
            url: "index.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
          })
          .done(function () {
            ok++;
            i++;
            next();
          })
          .fail(function () {
            fail++;
            i++;
            next();
          });
      }

      next();
    }

    // IMPORTANT:
    // Intercept the default vtiger submit and replace with our "upload on button click"
    $modal
      .find("form[name='upload']")
      .off("submit.multi")
      .on("submit.multi", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        uploadPendingFiles();
        return false;
      });

    // Footer Upload button triggers our upload
    $modal
      .find("#js-upload-document")
      .off("click.multi")
      .on("click.multi", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        uploadPendingFiles();
        return false;
      });

    // Initial status
    showSelectedCount();
  });
});
