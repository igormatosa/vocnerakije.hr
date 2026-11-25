(function ($) {
  'use strict';

  $('#vrp-select-product-image').on('click', function (event) {
    event.preventDefault();
    var frame = wp.media({
      title: 'Odaberite sliku proizvoda',
      button: { text: 'Koristi ovu sliku' },
      multiple: false
    });

    frame.on('select', function () {
      var image = frame.state().get('selection').first().toJSON();
      $('#vrp-product-image-id').val(image.id);
      $('#vrp-product-image-preview').html(
        '<img src="' + (image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url) + '" alt="" />'
      );
      $('#vrp-remove-product-image').prop('hidden', false);
    });
    frame.open();
  });

  $('#vrp-remove-product-image').on('click', function () {
    $('#vrp-product-image-id').val('');
    $('#vrp-product-image-preview').empty();
    $(this).prop('hidden', true);
  });
})(jQuery);
