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
    console.log("trujillo");
    $("#upload-picture-button").val("");

    $("#uploaded-picture-preview img")
      .attr('src', "")
      .css('display', 'none');

    $("#uploaded-picture-preview button").removeClass('show-preview-button');

    $(".file-status").text("No se ha elegido un archivo");
  });
});
