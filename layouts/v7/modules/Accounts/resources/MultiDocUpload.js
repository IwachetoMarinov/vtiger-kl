jQuery(function () {
  if (typeof app === "undefined" || !app.getModuleName || !app.getRecordId)
    return;

  var moduleName = app.getModuleName();
  var recordId = app.getRecordId();

  // Enable multi-upload for these modules
  var MULTI_UPLOAD_MODULES = ["Contacts", "Leads", "Accounts"];

  if (!recordId || MULTI_UPLOAD_MODULES.indexOf(moduleName) === -1) return;

  console.log("Init Multiple file upload for:", moduleName, recordId);

  function asArray(fileList) {
    return Array.prototype.slice.call(fileList || []);
  }

  jQuery(document).on("shown.bs.modal", ".modal", function () {
    var $modal = jQuery(this);

    // Documents upload modal
    var $file = $modal.find('input[type="file"][name="filename"]');
    if (!$file.length) return;

    if ($modal.data("multiUploadInit")) return;
    $modal.data("multiUploadInit", true);

    $file.attr("multiple", "multiple");
    $modal.data("pendingFiles", []);

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
      if (!files.length) $status.html("No files selected.");
      else if (files.length === 1)
        $status.html("<b>Selected:</b> 1 file<br>" + files[0].name);
      else $status.html("<b>Selected:</b> " + files.length + " files");
    }

    function setPendingFiles(files) {
      $modal.data("pendingFiles", files);
      showSelectedCount();
    }

    $file.off("change.multi").on("change.multi", function () {
      setPendingFiles(asArray(this.files));
      jQuery(this).val("");
    });

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

    function uploadPendingFiles() {
      var files = $modal.data("pendingFiles") || [];
      if (!files.length) {
        alert("Please select files first.");
        return;
      }

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

        // required fallbacks
        if (!fd.has("module")) fd.append("module", "Documents");
        if (!fd.has("action")) fd.append("action", "SaveAjax");
        if (!fd.has("document_source")) fd.append("document_source", "Vtiger");
        if (!fd.has("relationOperation"))
          fd.append("relationOperation", "true");
        if (!fd.has("sourceModule")) fd.append("sourceModule", moduleName);
        if (!fd.has("sourceRecord")) fd.append("sourceRecord", recordId);
        if (!fd.has("filelocationtype")) fd.append("filelocationtype", "I");
        if (!fd.has("notecontent")) fd.append("notecontent", "");

        fd.set("notes_title", file.name);
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

    $modal
      .find("form[name='upload']")
      .off("submit.multi")
      .on("submit.multi", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        uploadPendingFiles();
        return false;
      });

    $modal
      .find("#js-upload-document")
      .off("click.multi")
      .on("click.multi", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        uploadPendingFiles();
        return false;
      });

    showSelectedCount();
  });
});
