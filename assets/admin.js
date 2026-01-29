/* global jQuery, wp, BPTSliderAdmin */
jQuery(function ($) {
	'use strict';

	var frame;
	var $choose = $('#bp_tslider_choose_image');
	var $remove = $('#bp_tslider_remove_image');
	var $id = $('#bp_tslider_bg_image_id');
	var $preview = $('.bp-tslider-preview');

	function setPreview(url, hasImage) {
		if (url) {
			$preview.css('background-image', 'url(' + url + ')');
		} else {
			$preview.css('background-image', 'none');
		}
		if (hasImage) {
			$remove.show();
		} else {
			$remove.hide();
		}
	}

	$choose.on('click', function (e) {
		e.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: (BPTSliderAdmin && BPTSliderAdmin.strings && BPTSliderAdmin.strings.choose) ? BPTSliderAdmin.strings.choose : 'Elegir imagen',
			button: {
				text: (BPTSliderAdmin && BPTSliderAdmin.strings && BPTSliderAdmin.strings.use) ? BPTSliderAdmin.strings.use : 'Usar esta imagen'
			},
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			if (attachment && attachment.id) {
				$id.val(attachment.id);
				setPreview(attachment.url, true);
			}
		});

		frame.open();
	});

	$remove.on('click', function (e) {
		e.preventDefault();
		$id.val('');
		setPreview('', false);
	});
});
