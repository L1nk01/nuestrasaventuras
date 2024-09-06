$(document).ready(function () {

  // Opened image id
  var currentImageId = null;

  $("#submit-picture-form").on('click', function () {
    $("#upload-picture-form").submit();
  });

  $("#upload-picture-button").on('change', function () {
    $(".file-status").text(this.files[0].name);

    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $("#uploaded-picture-preview img")
          .attr('src', e.target.result)
          .css('display', 'initial');

        $("#uploaded-picture-preview button").addClass('show-preview-button');
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  $("#uploaded-picture-preview button").on('click', function () {
    $("#upload-picture-button").val("");

    $("#uploaded-picture-preview img")
      .attr('src', "")
      .css('display', 'none');

    $("#uploaded-picture-preview button").removeClass('show-preview-button');

    $(".file-status").text("No se ha elegido un archivo");
  });

  $("#viewPictureModal").on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var imageId = button.data('image-id');
    var modal = $(this);

    currentImageId = imageId;

    if (imageId) {
      $.ajax({
        url: '/image/fetch',
        type: 'GET',
        data: { id: imageId },
        dataType: 'json',
        success: function (data) {
          if (data.error) {
            console.error(data.error);
            return;
          }

          modal.find('.modal-body img').attr('src', '../model/uploads/images/' + data.file_name + data.file_extension);
          modal.find('.image-title').text(data.title);
          modal.find('.image-description').text(data.description);
          modal.find('.image-updated-at').html(`Actualizado por ultima vez <strong>${data.updated_at}<strong>`);
          modal.find('.image-created-at').html(`Creado el <strong>${data.created_at}</strong>`);

          $(".download-image-button").attr('href', '/image/download?id=' + imageId);
          $(".delete-image-button").data('image-id', imageId);
        },
        error: function (xhr, status, error) {
          console.error('Error fetching image information: ' + error);
          console.log('Response: ', xhr.responseText);
        }
      });
    }
  });

  $("#deletePictureModal").on('show.bs.modal', function (event) {
    $("#confirm-delete-button").data('image-id', currentImageId);
  });

  $("#confirm-delete-button").on('click', function () {
    var imageId = $(this).data('image-id');

    if (imageId) {
      $.ajax({
        url: '/image/delete',
        type: 'POST',
        data: { id: imageId },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            $('img[data-image-id="' + imageId + '"]').remove();
          } else {
            console.error(response.error);
          }
        },
        error: function (xhr, status, error) {
          console.error('Error deleting image from database: ' + error);
        }
      });
    }
  });
});