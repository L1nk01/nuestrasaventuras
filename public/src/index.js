$(document).ready(function () {
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

    if (imageId) {
      $.ajax({
        url: '/image/fetch',
        type: 'GET',
        data: { id: imageId },
        dataType: 'json',
        success: function (data) {
          console.log('AJAX Response: ', data);

          if (data.error) {
            console.error(data.error);
            return;
          }

          modal.find('.modal-title').text(data.title);
          modal.find('.modal-body img').attr('src', '../model/uploads/images/' + data.file_name + data.file_extension);
          modal.find('.modal-body p').text(data.description);
        },
        error: function (xhr, status, error) {
          console.error('Error fetching image data: ' + error);
          console.log('Response: ', xhr.responseText);
        }
      });
    }
  });
});