(function ($) {
	'use strict';

	var data = window.caasControlAdmin;
	if (!data) {
		return;
	}

	function ajaxAction(action, $result) {
		$result.text(data.i18n.working);
		$.post(data.ajaxUrl, {
			action: action,
			nonce: data.nonce,
		})
			.done(function (resp) {
				if (resp.success && resp.data && resp.data.message) {
					$result.text(resp.data.message);
				} else {
					$result.text('Error');
				}
			})
			.fail(function () {
				$result.text('Request failed');
			});
	}

	$(function () {
		$('.caas-control-scoop-revisions').on('click', function () {
			ajaxAction('caas_control_scoop_revisions', $('#caas-scoop-result'));
		});

		$('.caas-control-clear-cache').on('click', function () {
			ajaxAction('caas_control_clear_cache', $('#caas-cache-result'));
		});

		$('.caas-control-pro-upgrade').on('click', function () {
			window.alert(data.i18n.proSoon);
		});

		var frame;
		$('.caas-control-upload-image').on('click', function (e) {
			e.preventDefault();
			if (frame) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: 'Select chaos agent',
				button: { text: 'Use image' },
				multiple: false,
			});
			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				$('input[name="caas_control_settings[custom_image_url]"]').val(attachment.url);
				$('input[name="caas_control_settings[agent]"][value="custom"]').prop('checked', true);
			});
			frame.open();
		});
	});
})(jQuery);
