(function ($) {
	'use strict';

	function syncIds($field) {
		var ids = $field.find('.tdp-gallery-item').map(function () {
			return $(this).data('id');
		}).get();
		$field.find('.tdp-gallery-ids').val(ids.join(','));
	}

	$(function () {
		$('[data-gallery-field]').each(function () {
			var $field = $(this);
			var $list = $field.find('.tdp-gallery-list');

			$list.sortable({ items: '.tdp-gallery-item', update: function () { syncIds($field); } });

			$field.on('click', '.tdp-gallery-remove', function () {
				$(this).closest('.tdp-gallery-item').remove();
				syncIds($field);
			});

			$field.on('click', '.tdp-gallery-select', function (event) {
				event.preventDefault();
				var frame = wp.media({
					title: 'Select case-study images',
					button: { text: 'Use selected images' },
					multiple: true
				});

				frame.on('select', function () {
					frame.state().get('selection').each(function (attachment) {
						var image = attachment.toJSON();
						if ($list.find('[data-id="' + image.id + '"]').length) return;
						var thumb = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
						$list.append('<li class="tdp-gallery-item" data-id="' + image.id + '"><img src="' + thumb + '" alt=""><button type="button" class="button-link-delete tdp-gallery-remove" aria-label="Remove image">Remove</button></li>');
					});
					syncIds($field);
				});

				frame.open();
			});
		});
	});
})(jQuery);
