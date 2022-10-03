"use strict";

$(document).on('change', '.ps-file-input', function () {
  readURL(this);
  $(this).prev('.ps-trigger-file').addClass('d-none');
  // $(this).closest("span.input-group-prepend").find(".remove_file_field").val('no');
  $(this).next('.htr-remove-file').removeClass('d-none');
});

$(document).on('click', '.ps-trigger-file', function () {
  $(this).next('input').trigger('click');
});

$(document).on('click', '.img-preview', function () {
  $(this).parent().next('div').find('input').trigger('click');
});

$(document).on('click', '.htr-remove-file', function () {
  $(this).parent().find('input:file').val('');
  $(this).closest('.file-upload-box').find('div.main-img-preview').addClass('d-none');
  $(this).addClass('d-none');
  $(this).next('a').hide();
  $(this).prev().prev().removeClass('d-none');
  $(this).closest("span.input-group-prepend").find(".remove_file_field").val('yes');
});

function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      $(input).parent().next('input').val(input.value.substring(12));
      $(input).closest('.file-upload-box').find('div.main-img-preview').removeClass('d-none').find('.img-preview').attr('src', e.target.result);
    };
    reader.readAsDataURL(input.files[0]);
  }
}
