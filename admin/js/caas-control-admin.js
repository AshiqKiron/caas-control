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

		var durationRangeSelector = 'input.caas-control-duration-range[name="caas_control_settings[duration_seconds]"]';
		var appearAfterRangeSelector = 'input.caas-control-appear-after-range[name="caas_control_settings[appear_after_seconds]"]';

		function chaosDurationSliderLabel(seconds) {
			var i18n = data && data.i18n ? data.i18n : {};
			var maxSeconds = i18n.durationSecondsMax != null ? parseInt(i18n.durationSecondsMax, 10) : 600;
			if (!isNaN(maxSeconds) && seconds >= maxSeconds) {
				var untilCry = i18n.durationUntilYourCry || 'Until your cry - 10 mins';
				return {
					text: untilCry,
					useSecondsSuffix: false,
					ariaText: untilCry,
				};
			}
			return durationSliderLabel(seconds);
		}

		function durationSliderLabel(seconds) {
			var i18n = data && data.i18n ? data.i18n : {};
			if (seconds < 60) {
				return {
					text: String(seconds),
					useSecondsSuffix: true,
					ariaText: String(seconds) + ' ' + (i18n.durationSeconds || 'seconds'),
				};
			}
			var mins = Math.floor(seconds / 60);
			var secs = seconds % 60;
			var text;
			if (secs === 0) {
				var minsOnly = i18n.durationMinsOnly || '%s mins';
				text = minsOnly.replace('%s', String(mins));
			} else {
				var template = i18n.durationMinsSeconds || '%1$s mins %2$s seconds';
				text = template.replace('%1$s', String(mins)).replace('%2$s', String(secs));
			}
			return {
				text: text,
				useSecondsSuffix: false,
				ariaText: text,
			};
		}

		function appearAfterSliderLabel(seconds) {
			var i18n = data && data.i18n ? data.i18n : {};
			var mins = Math.floor(seconds / 60);
			var above =
				i18n.appearAfterHourLabelAboveMinutes != null
					? parseInt(i18n.appearAfterHourLabelAboveMinutes, 10)
					: 55;
			if (!isNaN(above) && mins > above) {
				var hours = Math.floor(mins / 60);
				var remMins = mins % 60;
				var hourTemplate = i18n.appearAfterHoursMins || '%1$s hour %2$s mins';
				var text = hourTemplate.replace('%1$s', String(hours)).replace('%2$s', String(remMins));
				return {
					text: text,
					useSecondsSuffix: false,
					ariaText: text,
				};
			}
			var minsOnly = i18n.durationMinsOnly || '%s mins';
			var minsText = minsOnly.replace('%s', String(mins));
			return {
				text: minsText,
				useSecondsSuffix: false,
				ariaText: minsText,
			};
		}

		function syncRangeSliderUi(rangeSelector, labelFn) {
			var $range = $agentForm.find(rangeSelector);
			if (!$range.length) {
				return;
			}
			var seconds = parseInt($range.val(), 10);
			if (isNaN(seconds)) {
				return;
			}
			var label = labelFn(seconds);
			var $slider = $range.closest('.caas-control-duration-slider');
			var $output = $slider.find('.caas-control-duration-value');
			var $num = $output.find('.caas-control-duration-value__num');
			var $suffix = $output.find('.caas-control-duration-value__suffix');

			$range.attr('aria-valuenow', String(seconds));
			$range.attr('aria-valuetext', label.ariaText);
			$num.text(label.text);
			$suffix.toggleClass('is-hidden', !label.useSecondsSuffix);
		}

		function syncDurationSliderUi() {
			syncRangeSliderUi(durationRangeSelector, chaosDurationSliderLabel);
		}

		function syncAppearAfterSliderUi() {
			syncRangeSliderUi(appearAfterRangeSelector, appearAfterSliderLabel);
		}

		$agentForm.on('input change', durationRangeSelector, syncDurationSliderUi);
		$agentForm.on('input change', appearAfterRangeSelector, syncAppearAfterSliderUi);
		syncDurationSliderUi();
		syncAppearAfterSliderUi();

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
			var catMediaUrl = $agentForm.attr('data-caas-cat-media-url');
			if (typeof catMediaUrl === 'string' && catMediaUrl) {
				chaos.catImageUrl = catMediaUrl;
			}
			var judgingMediaUrl = $agentForm.attr('data-caas-judging-media-url');
			if (typeof judgingMediaUrl === 'string') {
				chaos.judgingVideoUrl = judgingMediaUrl;
			}
			var nameInput = $agentForm.find('input[name="caas_control_settings[companion_name]"]');
			if (nameInput.length && nameInput.val()) {
				chaos.companionName = nameInput.val();
			}
			var $durationRange = $agentForm.find(durationRangeSelector);
			if ($durationRange.length) {
				var durationSeconds = parseInt($durationRange.val(), 10);
				if (!isNaN(durationSeconds)) {
					chaos.durationSeconds = durationSeconds;
				}
			}
			var $appearRange = $agentForm.find(appearAfterRangeSelector);
			if ($appearRange.length) {
				var appearSeconds = parseInt($appearRange.val(), 10);
				if (!isNaN(appearSeconds)) {
					chaos.appearAfterSeconds = appearSeconds;
				}
			}
			if (!chaos.features) {
				chaos.features = {};
			}
			function featureChecked(fieldKey) {
				var selector =
					'input[name="caas_control_settings[' + fieldKey + ']"]';
				var $input = $agentForm.find(selector);
				return $input.length > 0 && $input.is(':checked');
			}
			chaos.features.publishGate = featureChecked('enable_publish_gate');
			chaos.features.idleJudgment = featureChecked('enable_idle_judgment');
			chaos.features.keyboardTrample = featureChecked('enable_keyboard_trample');
			chaos.features.redDot = featureChecked('enable_red_dot');
			chaos.features.fakeSecurity = featureChecked('enable_fake_security');
			chaos.features.footerPeekaboo = featureChecked('enable_footer_peekaboo');
			chaos.features.pawTrail = featureChecked('enable_paw_trail');
			chaos.features.scrollPost = featureChecked('enable_scroll_post');
			chaos.features.wooCartWiggle = featureChecked('enable_woo_cart_wiggle');
		}

		window.__caasControlSyncChaosFromForm = syncChaosConfigFromForm;
		syncChaosConfigFromForm();

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
