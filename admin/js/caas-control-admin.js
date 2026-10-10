(function ($) {
	'use strict';

	var data = window.caasControlAdmin;

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
		$(document).on('click', '.caas-control-tip__trigger', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $tip = $(this).closest('.caas-control-tip');
			$('.caas-control-tip.is-open').not($tip).removeClass('is-open');
			$tip.toggleClass('is-open');
		});

		$(document).on('click', function () {
			$('.caas-control-tip.is-open').removeClass('is-open');
		});

		if (data) {
			$('.caas-control-scoop-revisions').on('click', function () {
				ajaxAction('caas_control_scoop_revisions', $('#caas-scoop-result'));
			});

			$('.caas-control-clear-cache').on('click', function () {
				ajaxAction('caas_control_clear_cache', $('#caas-cache-result'));
			});

			$('.caas-control-pro-upgrade').on('click', function () {
				window.alert(data.i18n.proSoon);
			});
		}

		var $agentForm = $('.caas-control-form');
		var agentSelector = 'input[type="radio"][name="caas_control_settings[agent]"]';
		var customUrlSelector = 'input[name="caas_control_settings[custom_image_url]"]';

		function syncCustomAgentFields() {
			var $fields = $agentForm.find('.caas-control-custom-agent-fields');
			if (!$fields.length) {
				return;
			}
			var isCustom = $agentForm.find(agentSelector).filter(function () {
				return this.value === 'custom';
			}).is(':checked');
			$fields.toggleClass('is-hidden', !isCustom);
		}

		function selectCustomAgent() {
			$agentForm.find(agentSelector).filter('[value="custom"]').prop('checked', true);
			syncCustomAgentFields();
		}

		syncCustomAgentFields();
		$agentForm.on('change', agentSelector, syncCustomAgentFields);

		function syncChaosConfigFromForm() {
			var chaos = window.caasControlChaos;
			if (!chaos || !$agentForm.length) {
				return;
			}
			var $checked = $agentForm.find(agentSelector).filter(':checked');
			if ($checked.length) {
				chaos.agent = $checked.val();
			}
			var customUrl = $agentForm.find(customUrlSelector).val();
			if (typeof customUrl === 'string') {
				chaos.customImageUrl = customUrl;
			}
			var nameInput = $agentForm.find('input[name="caas_control_settings[companion_name]"]');
			if (nameInput.length && nameInput.val()) {
				chaos.companionName = nameInput.val();
			}
		}

		$(document).on('click', '.caas-control-behavior-preview', function (e) {
			e.preventDefault();
			syncChaosConfigFromForm();
			var behavior = this.getAttribute('data-caas-behavior');
			if (typeof window.caasControlPreviewBehavior !== 'function') {
				if (data && data.i18n && data.i18n.previewUnavailable) {
					window.alert(data.i18n.previewUnavailable);
				}
				return;
			}
			if (!window.caasControlPreviewBehavior(behavior)) {
				if (data && data.i18n && data.i18n.previewUnavailable) {
					window.alert(data.i18n.previewUnavailable);
				}
			}
		});

		var frame;
		$(document).on('click', '.caas-control-upload-image', function (e) {
			e.preventDefault();
			if (typeof wp === 'undefined' || !wp.media) {
				if (data && data.i18n && data.i18n.mediaMissing) {
					window.alert(data.i18n.mediaMissing);
				}
				return;
			}
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
				$agentForm.find(customUrlSelector).val(attachment.url);
				selectCustomAgent();
			});
			frame.open();
		});

		if (!data) {
			return;
		}
	});
})(jQuery);
