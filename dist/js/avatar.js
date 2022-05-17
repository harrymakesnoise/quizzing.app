var cropper;
var imageData;

function setImageData(blob) {
  imageData = blob;
}

function previewImage() {
  URL.revokeObjectURL(renderSrc);
  renderSrc = window.URL.createObjectURL($("#profilePictureInput")[0].files[0]);
  $(".image-preview").attr("src", renderSrc);
}

function updateProfilePicture() {
  canvas = cropper.getCroppedCanvas({
    width: 160,
    height: 160,
  });

  canvas.toBlob(function(blob) {
      url = URL.createObjectURL(blob);
      var reader = new FileReader();
       reader.readAsDataURL(blob); 
       reader.onloadend = function() {
          var base64data = reader.result;  
          
          $.ajax({
              type: "POST",
              dataType: "json",
              url: "/api/user/update",
              data: {image: base64data, userid: thisUserId},
              success: function(data){
                var json = data;
                resetSaveImgBtn();
                if(json.type == "error") {
                  alert(json.message);
                } else {
                  $(".profile-img-container img").attr("src", json.url);
                  $(".image-preview").attr("src", json.url);
                  $("#modal-image-edit").modal("hide");
                  $(".profile-img-container button[data-target='#modal-image-delete']").show();
                  $("#edit-image-imageeditor-tab").parent().show();
                  $("#edit-image-upload-tab").parent().removeClass("col-12").addClass("col-6")
                }
              }
            });
       }
  });
}

function saveImageChanges() {
  if(renderSrc == "") {
    return false;
  }
  $('#modal-image-edit').data('bs.modal')._config.backdrop = "static";
  $('#modal-image-edit').data('bs.modal')._config.keyboard = false;
  $("#modal-image-edit [data-dismiss]").fadeOut();
  $("#image-edit-save-btn").css("width", $("#image-edit-save-btn").outerWidth() + "px").html('<i class="fa fa-spin fa-spinner"></i>');

  var formData = new FormData();
  if($("#edit-image-mode").val() == "upload") {
    setImageData($("#profilePictureInput")[0].files[0]);
  } else {
    updateProfilePicture();
    return false;
  }
  formData.append("image", imageData);
  formData.append("userid", thisUserId);
  $.ajax({
    url: '/api/user/' + $("#edit-image-mode").val(),
    data: formData,
    processData: false,
    contentType: false,
    type: 'POST',
    success: function(data){
      var json = data;
      resetSaveImgBtn();
      if(json.type == "error") {
        alert(json.message);
      } else {
        $(".profile-img-container img").attr("src", json.url);
        $(".image-preview").attr("src", json.url);
        $("#modal-image-edit").modal("hide");
        $(".profile-img-container button[data-target='#modal-image-delete']").show();
        $("#edit-image-imageeditor-tab").parent().show();
        $("#edit-image-upload-tab").parent().removeClass("col-12").addClass("col-6")
      }
    }
  });
}

function clearProfilePicture() {
  $('#modal-image-delete').data('bs.modal')._config.backdrop = "static";
  $('#modal-image-delete').data('bs.modal')._config.keyboard = false;
  $("#modal-image-delete [data-dismiss]").fadeOut();
  $("#image-delete-btn").css("width", $("#image-delete-btn").outerWidth() + "px").html('<i class="fa fa-spin fa-spinner"></i>');

  $.ajax({
    url: '/api/user/delete',
    data: "",
    processData: false,
    contentType: false,
    type: 'GET',
    success: function(data){
      var json = data;
      resetSaveImgBtn();
      if(json.type == "error") {
        alert(json.message);
      } else {
        $(".profile-img-container img").attr("src", json.url);
        $(".image-preview").attr("src", json.url);
        $("#modal-image-delete").modal("hide");
        $(".profile-img-container button[data-target='#modal-image-delete']").hide();
        $("#edit-image-imageeditor-tab").parent().hide();
        $("#edit-image-upload-tab").click().parent().removeClass("col-6").addClass("col-12");
      }
    }
  });
}

function resetSaveImgBtn() {
  if(typeof $('#modal-image-delete').data('bs.modal') !== "undefined") {
    $('#modal-image-delete').data('bs.modal')._config.backdrop = true;
    $('#modal-image-delete').data('bs.modal')._config.keyboard = true;
    $("#modal-image-delete [data-dismiss]").fadeIn();
    $("#image-delete-btn").css("width", "auto").html('Confirm');
  }

  if(typeof $('#modal-image-edit').data('bs.modal') !== "undefined") {
    $('#modal-image-edit').data('bs.modal')._config.backdrop = true;
    $('#modal-image-edit').data('bs.modal')._config.keyboard = true;
    $("#modal-image-edit [data-dismiss]").fadeIn();
    $("#image-edit-save-btn").css("width", "auto").html('Save Changes');
  }
  cropper.destroy();
  cropper = new Cropper($("#image-edit-image")[0], {autocrop:false, minCanvasWidth: "100%"});
}

$(document).ready(function() {
  $("#image-edit-save-btn").click(saveImageChanges);
  $("#image-delete-btn").click(clearProfilePicture);
  cropper = new Cropper($("#image-edit-image")[0], {autocrop:false, minCanvasWidth: "100%"});
});