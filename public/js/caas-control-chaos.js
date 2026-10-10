(function () {
	'use strict';

	var cfg = window.caasControlChaos;
	if (!cfg) {
		return;
	}
	if (window.__caasControlChaosInit) {
		return;
	}
	window.__caasControlChaosInit = true;

	var AGENT_EMOJI = {
		cat: '🐈',
	};

	var creatureEl = null;
	var followRaf = null;
	var chaosTimer = null;
	var chaosFirstSchedule = true;
	var meowAudio = null;
	var sitActive = false;
	var sitAutoTimer = null;
	var behaviorEndTimer = null;
	var publishGateResumeTimer = null;
	var publishGateBtn = null;
	var sitMediaWrap = null;
	var sitCountdownInterval = null;

	function creatureMediaUrl() {
		if (cfg.agent === 'custom' && cfg.customImageUrl) {
			return cfg.customImageUrl;
		}
		if (cfg.catImageUrl) {
			return cfg.catImageUrl;
		}
		return '';
	}

	function sitJudgingMediaUrl() {
		if (cfg.judgingVideoUrl) {
			return cfg.judgingVideoUrl;
		}
		return creatureMediaUrl();
	}

	function mediaUrlIsVideo(url) {
		return /\.(mp4|webm|ogg)(\?|#|$)/i.test(url);
	}

	function appendAgentMedia(parent, url, alt, size) {
		alt = alt || '';
		size = size || 'creature';
		if (mediaUrlIsVideo(url)) {
			var video = document.createElement('video');
			video.src = url;
			video.muted = true;
			video.playsInline = true;
			video.autoplay = true;
			video.loop = size !== 'judging';
			video.setAttribute('playsinline', '');
			video.setAttribute('webkit-playsinline', '');
			if (size === 'creature') {
				video.setAttribute('width', '56');
				video.setAttribute('height', '56');
			}
			video.setAttribute('aria-label', alt);
			parent.appendChild(video);
			var playPromise = video.play();
			if (playPromise && typeof playPromise.catch === 'function') {
				playPromise.catch(function () {});
			}
			return video;
		}
		var img = document.createElement('img');
		img.src = url;
		img.alt = alt;
		parent.appendChild(img);
		return img;
	}

	function agentGlyph() {
		if (creatureMediaUrl()) {
			return null;
		}
		return AGENT_EMOJI[cfg.agent] || AGENT_EMOJI.cat;
	}

	function paintCreatureMedia(el) {
		while (el.firstChild) {
			el.removeChild(el.firstChild);
		}
		var glyph = agentGlyph();
		if (glyph) {
			el.textContent = glyph;
			return;
		}
		el.textContent = '';
		appendAgentMedia(el, creatureMediaUrl(), cfg.companionName || '');
	}

	function ensureCreature() {
		if (!creatureEl) {
			creatureEl = document.createElement('div');
			creatureEl.className = 'caas-control-creature';
			creatureEl.setAttribute('role', 'img');
			document.body.appendChild(creatureEl);
		}
		creatureEl.setAttribute('aria-label', cfg.companionName || 'Chaos agent');
		paintCreatureMedia(creatureEl);
		return creatureEl;
	}

	function isSitJudgmentActive() {
		return sitActive || !!publishGateResumeTimer;
	}

	var JUDGING_MEDIA_FADE_MS = 450;

	function clearSitUi(immediate) {
		if (sitAutoTimer) {
			window.clearTimeout(sitAutoTimer);
			sitAutoTimer = null;
		}
		removeSitMedia(!!immediate);
	}

	function sitJudgingAriaLabel() {
		return (
			(cfg.strings && cfg.strings.judgingYou) ||
			(cfg.companionName || 'Companion') + ' is judging you.'
		);
	}

	function detachSitMediaWrap(wrap) {
		if (wrap && wrap.parentNode) {
			wrap.parentNode.removeChild(wrap);
		}
	}

	function removeSitMedia(immediate) {
		clearSitCountdown();
		var wrap = sitMediaWrap;
		sitMediaWrap = null;
		if (!wrap) {
			if (creatureEl) {
				creatureEl.style.display = '';
			}
			return;
		}
		if (creatureEl) {
			creatureEl.style.display = '';
		}
		if (immediate || !wrap.parentNode) {
			detachSitMediaWrap(wrap);
			return;
		}
		wrap.classList.remove('is-visible');
		var removed = false;
		function finishRemove() {
			if (removed) {
				return;
			}
			removed = true;
			detachSitMediaWrap(wrap);
		}
		wrap.addEventListener(
			'transitionend',
			function (e) {
				if (e.target === wrap && e.propertyName === 'opacity') {
					finishRemove();
				}
			}
		);
		window.setTimeout(finishRemove, JUDGING_MEDIA_FADE_MS + 50);
	}

	function chaosDurationMs() {
		return (cfg.durationSeconds || 5) * 1000;
	}

	function clearSitCountdown() {
		if (sitCountdownInterval) {
			window.clearInterval(sitCountdownInterval);
			sitCountdownInterval = null;
		}
	}

	function formatCountdownLabel(totalSeconds) {
		totalSeconds = Math.max(0, totalSeconds);
		var strings = cfg.strings || {};
		if (totalSeconds >= 60) {
			var mins = Math.floor(totalSeconds / 60);
			var secs = totalSeconds % 60;
			var template = strings.countdownMinsSeconds || '%1$s mins %2$s sec left';
			return template.replace('%1$s', String(mins)).replace('%2$s', String(secs));
		}
		var secTemplate = strings.countdownSeconds || '%s sec left';
		return secTemplate.replace('%s', String(totalSeconds));
	}

	function updateSitCountdownDisplay(el, endsAt) {
		var leftMs = Math.max(0, endsAt - Date.now());
		var totalSeconds = Math.ceil(leftMs / 1000);
		var label = formatCountdownLabel(totalSeconds);
		el.textContent = label;
		el.setAttribute('aria-label', label);
	}

	function startSitCountdown(durationMs) {
		clearSitCountdown();
		if (!durationMs || durationMs <= 0 || !sitMediaWrap) {
			return;
		}
		var wrap = sitMediaWrap;
		var el = wrap.querySelector('.caas-control-judging-countdown');
		if (!el) {
			return;
		}
		var endsAt = Date.now() + durationMs;
		updateSitCountdownDisplay(el, endsAt);
		sitCountdownInterval = window.setInterval(function () {
			if (sitMediaWrap !== wrap) {
				clearSitCountdown();
				return;
			}
			if (Date.now() >= endsAt) {
				updateSitCountdownDisplay(el, endsAt);
				clearSitCountdown();
				return;
			}
			updateSitCountdownDisplay(el, endsAt);
		}, 1000);
	}

	function bindSitEndToChaosDuration(video) {
		if (sitAutoTimer) {
			window.clearTimeout(sitAutoTimer);
			sitAutoTimer = null;
		}
		video.loop = false;

		var durationMs = chaosDurationMs();
		startSitCountdown(durationMs);
		sitAutoTimer = window.setTimeout(function () {
			if (!sitActive) {
				return;
			}
			try {
				video.pause();
			} catch (err) {
				// Ignore pause errors on detached or unsupported elements.
			}
			removeCreature();
		}, durationMs);
	}

	function playSitJudgingVideo() {
		var url = sitJudgingMediaUrl();
		if (!url) {
			return null;
		}
		removeSitMedia(true);
		var wrap = document.createElement('div');
		wrap.className = 'caas-control-judging-media';
		wrap.setAttribute('role', 'group');
		wrap.setAttribute('aria-label', sitJudgingAriaLabel());

		var countdownEl = document.createElement('div');
		countdownEl.className = 'caas-control-judging-countdown';
		countdownEl.setAttribute('role', 'timer');
		countdownEl.setAttribute('aria-live', 'polite');
		countdownEl.setAttribute('aria-atomic', 'true');
		wrap.appendChild(countdownEl);

		var shooBtn = document.createElement('button');
		shooBtn.type = 'button';
		shooBtn.className = 'caas-control-shoo-btn';
		shooBtn.textContent = (cfg.strings && cfg.strings.shoo) || 'Shoo!';
		shooBtn.addEventListener('click', shooAwayJudging);
		wrap.appendChild(shooBtn);

		var media = appendAgentMedia(wrap, url, sitJudgingAriaLabel(), 'judging');
		document.body.appendChild(wrap);
		sitMediaWrap = wrap;
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				if (sitMediaWrap === wrap) {
					wrap.classList.add('is-visible');
				}
			});
		});
		if (creatureEl) {
			creatureEl.style.display = 'none';
		}
		return media;
	}

	function cancelPublishGate() {
		if (publishGateResumeTimer) {
			window.clearTimeout(publishGateResumeTimer);
			publishGateResumeTimer = null;
		}
		publishGateBtn = null;
	}

	function cancelBehaviorEndTimer() {
		if (behaviorEndTimer) {
			window.clearTimeout(behaviorEndTimer);
			behaviorEndTimer = null;
		}
	}

	function shooAwayJudging() {
		if (!sitMediaWrap && !sitActive) {
			return;
		}
		if (sitAutoTimer) {
			window.clearTimeout(sitAutoTimer);
			sitAutoTimer = null;
		}
		clearSitCountdown();
		cancelBehaviorEndTimer();
		cancelPublishGate();
		sitActive = false;
		removeCreature();
		toast((cfg.strings && cfg.strings.shooDismissed) || 'Fine, I\'ll leave.', 2000);
	}

	function removeCreature() {
		if (followRaf) {
			cancelAnimationFrame(followRaf);
			followRaf = null;
		}
		clearSitUi();
		sitActive = false;
		if (creatureEl && creatureEl.parentNode) {
			creatureEl.parentNode.removeChild(creatureEl);
		}
		creatureEl = null;
	}

	function toast(msg, ms) {
		var t = document.createElement('div');
		t.className = 'caas-control-toast';
		t.textContent = msg;
		document.body.appendChild(t);
		setTimeout(function () {
			if (t.parentNode) {
				t.parentNode.removeChild(t);
			}
		}, ms || 3000);
	}

	function playMeowSound() {
		if (!cfg.meowSoundUrl) {
			return;
		}
		try {
			if (!meowAudio) {
				meowAudio = new Audio(cfg.meowSoundUrl);
				meowAudio.volume = 0.85;
			}
			meowAudio.currentTime = 0;
			var playPromise = meowAudio.play();
			if (playPromise && typeof playPromise.catch === 'function') {
				playPromise.catch(function () {});
			}
		} catch (err) {
			// Autoplay policies may block sound until user interaction.
		}
	}

	function meow() {
		if (!cfg.behaviors || !cfg.behaviors.meow) {
			return;
		}
		playMeowSound();
		toast((cfg.i18n && cfg.i18n.meow) || 'Meow!', 2000);
	}

	function behaviorWalk() {
		clearSitUi(true);
		var el = ensureCreature();
		el.className = 'caas-control-creature caas-control-creature--walk';
		var start = window.innerWidth + 40;
		el.style.transform = 'translate(' + start + 'px, ' + (window.innerHeight - 120) + 'px)';
		var x = start;
		var id = window.setInterval(function () {
			x -= 4;
			el.style.transform = 'translate(' + x + 'px, ' + (window.innerHeight - 120) + 'px)';
			if (x < -80) {
				window.clearInterval(id);
				removeCreature();
			}
		}, 16);
	}

	function behaviorSit(opts) {
		opts = opts || {};
		clearSitUi(true);
		sitActive = true;
		var el = ensureCreature();
		el.className = 'caas-control-creature caas-control-creature--sit';
		el.style.transform =
			'translate(' + (window.innerWidth / 2 - 30) + 'px, ' + (window.innerHeight / 2) + 'px)';
		var judgingMedia = playSitJudgingVideo();
		if (!judgingMedia) {
			toast(sitJudgingAriaLabel(), 2500);
			if (!opts.skipAutoTimer) {
				sitAutoTimer = window.setTimeout(removeCreature, (cfg.durationSeconds || 5) * 1000);
			}
			return;
		}

		if (!opts.skipAutoTimer) {
			var durationMs = chaosDurationMs();
			if (judgingMedia.tagName === 'VIDEO') {
				bindSitEndToChaosDuration(judgingMedia);
			} else {
				startSitCountdown(durationMs);
				sitAutoTimer = window.setTimeout(removeCreature, durationMs);
			}
		}
	}

	function behaviorFollow() {
		clearSitUi(true);
		var el = ensureCreature();
		el.className = 'caas-control-creature caas-control-creature--follow';
		function onMove(e) {
			if (!creatureEl) {
				return;
			}
			creatureEl.style.transform = 'translate(' + (e.clientX + 12) + 'px, ' + (e.clientY + 12) + 'px)';
		}
		document.addEventListener('mousemove', onMove);
		setTimeout(function () {
			document.removeEventListener('mousemove', onMove);
			removeCreature();
		}, (cfg.durationSeconds || 5) * 1000);
	}

	function behaviorBlockMouse() {
		var blocker = document.createElement('div');
		blocker.className = 'caas-control-mouse-block';
		document.body.appendChild(blocker);
		setTimeout(function () {
			if (blocker.parentNode) {
				blocker.parentNode.removeChild(blocker);
			}
		}, 1200);
	}

	function behaviorRandomBlock() {
		var overlay = document.createElement('div');
		overlay.className = 'caas-control-screen-block';
		overlay.innerHTML = '<p>' + (cfg.companionName || 'Chaos') + '</p>';
		document.body.appendChild(overlay);
		setTimeout(function () {
			if (overlay.parentNode) {
				overlay.parentNode.removeChild(overlay);
			}
		}, (cfg.durationSeconds || 5) * 1000);
	}

	function runRandomBehavior() {
		if (isSitJudgmentActive()) {
			return;
		}
		var pool = [];
		var b = cfg.behaviors || {};
		if (b.walk) {
			pool.push(behaviorWalk);
		}
		if (b.sit) {
			pool.push(behaviorSit);
		}
		if (b.follow) {
			pool.push(behaviorFollow);
		}
		if (b.blockMouse) {
			pool.push(behaviorBlockMouse);
		}
		if (b.randomBlock) {
			pool.push(behaviorRandomBlock);
		}
		if (!pool.length) {
			return;
		}
		cancelBehaviorEndTimer();
		var chosen = pool[Math.floor(Math.random() * pool.length)];
		chosen();
		if (chosen !== behaviorSit) {
			behaviorEndTimer = window.setTimeout(function () {
				behaviorEndTimer = null;
				removeCreature();
			}, ((cfg.durationSeconds || 5) + 1) * 1000);
		}
	}

	function scheduleChaos() {
		var ms;
		if (chaosFirstSchedule) {
			chaosFirstSchedule = false;
			var appearSecs = cfg.appearAfterSeconds;
			if (appearSecs === undefined || appearSecs === null) {
				appearSecs = (cfg.frequencyMinutes || 15) * 60;
			}
			ms = Math.max(0, appearSecs) * 1000;
		} else {
			var minutes = cfg.frequencyMinutes || 15;
			ms = minutes * 60 * 1000;
		}
		if (chaosTimer) {
			clearTimeout(chaosTimer);
		}
		chaosTimer = setTimeout(function () {
			runRandomBehavior();
			scheduleChaos();
		}, ms);
	}

	function loginHiss() {
		var el = ensureCreature();
		el.className = 'caas-control-creature caas-control-creature--hiss';
		el.style.transform = 'translate(-50%, 0) translate(' + window.innerWidth / 2 + 'px, ' + (window.innerHeight - 20) + 'px)';
		setTimeout(removeCreature, 2500);
	}

	function publishGatekeeper() {
		if (!cfg.features || !cfg.features.publishGate) {
			return;
		}
		document.addEventListener(
			'click',
			function (e) {
				var t = e.target;
				if (!t || !t.closest) {
					return;
				}
				var btn = t.closest('.editor-post-publish-button, #publish, .editor-post-publish-panel__toggle');
				if (!btn) {
					return;
				}
				var seoOk = true;
				var seoField = document.querySelector('#yoast-meta-description, textarea[name="rank_math_description"]');
				if (seoField && !seoField.value.trim()) {
					seoOk = false;
				}
				if (!seoOk) {
					e.preventDefault();
					e.stopPropagation();
					toast(cfg.strings.seoRejection || 'SEO meta required.', 4000);
					meow();
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				cancelPublishGate();
				var msg = cfg.strings.publishGate || 'Cat is reviewing your changes.';
				behaviorSit({ skipAutoTimer: true });
				publishGateBtn = btn;

				function resumePublish(gateMs) {
					toast(msg, gateMs);
					startSitCountdown(gateMs);
					if (publishGateResumeTimer) {
						window.clearTimeout(publishGateResumeTimer);
					}
					publishGateResumeTimer = window.setTimeout(function () {
						publishGateResumeTimer = null;
						var target = publishGateBtn;
						publishGateBtn = null;
						removeCreature();
						if (target) {
							target.click();
						}
					}, gateMs);
				}

				resumePublish(chaosDurationMs());
			},
			true
		);
	}

	function idleJudgment() {
		if (!cfg.features || !cfg.features.idleJudgment) {
			return;
		}
		var last = Date.now();
		['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function (ev) {
			document.addEventListener(ev, function () {
				last = Date.now();
			});
		});
		setInterval(function () {
			if (Date.now() - last > 15 * 60 * 1000) {
				toast(cfg.strings.idleJudgment || 'Move on, fella.', 5000);
				behaviorSit();
				last = Date.now();
			}
		}, 60000);
	}

	function keyboardTrample() {
		if (!cfg.features || !cfg.features.keyboardTrample) {
			return;
		}
		var idle = Date.now();
		document.addEventListener('keydown', function () {
			idle = Date.now();
		});
		setInterval(function () {
			var editor = document.querySelector('.block-editor-writing-flow, #content');
			if (!editor || Date.now() - idle < 90000) {
				return;
			}
			editor.dispatchEvent(
				new InputEvent('input', {
					bubbles: true,
					data: 'asdfghjkl;;;;;;;;;;',
				})
			);
			toast('Paws on keyboard!', 2000);
			idle = Date.now();
		}, 30000);
	}

	function redDotTorture() {
		if (!cfg.features || !cfg.features.redDot) {
			return;
		}
		var dot = document.createElement('div');
		dot.className = 'caas-control-red-dot';
		document.body.appendChild(dot);
		function moveDot() {
			dot.style.left = Math.random() * (window.innerWidth - 20) + 'px';
			dot.style.top = Math.random() * (window.innerHeight - 20) + 'px';
		}
		moveDot();
		setInterval(moveDot, 800);
	}

	function fakeSecurity() {
		if (!cfg.features || !cfg.features.fakeSecurity) {
			return;
		}
		document.addEventListener('mousemove', function throttle(e) {
			if (Math.random() > 0.002) {
				return;
			}
			document.removeEventListener('mousemove', throttle);
			toast(cfg.strings.fakeSecurity || 'Suspicious mouse movement!', 3000);
			behaviorBlockMouse();
			setTimeout(function () {
				document.addEventListener('mousemove', throttle);
			}, 15000);
		});
	}

	function footerPeekaboo() {
		if (!cfg.features || !cfg.features.footerPeekaboo) {
			return;
		}
		var peek = document.createElement('div');
		peek.className = 'caas-control-peekaboo';
		var peekUrl =
			cfg.agent === 'custom' && cfg.customImageUrl
				? cfg.customImageUrl
				: cfg.agent === 'cat' && cfg.catImageUrl
					? cfg.catImageUrl
					: '';
		if (peekUrl) {
			appendAgentMedia(peek, peekUrl, cfg.companionName || '');
		} else {
			peek.textContent = '🐱';
		}
		document.body.appendChild(peek);
		var shown = false;
		window.addEventListener('scroll', function () {
			var nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 80;
			if (nearBottom && !shown) {
				shown = true;
				peek.classList.add('is-visible');
				setTimeout(function () {
					peek.classList.remove('is-visible');
					shown = false;
				}, 2500);
			}
		});
	}

	function pawTrail() {
		if (!cfg.features || !cfg.features.pawTrail) {
			return;
		}
		document.addEventListener('mousemove', function (e) {
			var p = document.createElement('span');
			p.className = 'caas-control-paw';
			p.setAttribute('aria-hidden', 'true');
			p.textContent = '🐾';
			p.style.left = e.clientX + 'px';
			p.style.top = e.clientY + 'px';
			p.style.setProperty('--caas-paw-rot', Math.floor(Math.random() * 50 - 25) + 'deg');
			document.body.appendChild(p);
			setTimeout(function () {
				if (p.parentNode) {
					p.parentNode.removeChild(p);
				}
			}, 1400);
		});
	}

	function scrollPost() {
		if (!cfg.features || !cfg.features.scrollPost) {
			return;
		}
		var wrap = document.querySelector('.caas-control-scroll-post-wrap');
		var btn = document.querySelector('.caas-control-scroll-post');
		if (!btn) {
			return;
		}

		var showThreshold = 280;
		function updateVisibility() {
			var visible = window.scrollY > showThreshold;
			if (wrap) {
				wrap.classList.toggle('is-visible', visible);
			} else {
				btn.classList.toggle('is-visible', visible);
			}
		}

		window.addEventListener('scroll', updateVisibility, { passive: true });
		updateVisibility();

		var scratching = false;
		var scratchInterval = null;
		var endCheck = null;
		var scratchTimeout = null;

		function stopScratch() {
			scratching = false;
			btn.classList.remove('is-scratching');
			if (scratchInterval) {
				window.clearInterval(scratchInterval);
				scratchInterval = null;
			}
			if (endCheck) {
				window.clearInterval(endCheck);
				endCheck = null;
			}
			if (scratchTimeout) {
				window.clearTimeout(scratchTimeout);
				scratchTimeout = null;
			}
		}

		function spawnScratchMark() {
			var rect = btn.getBoundingClientRect();
			var mark = document.createElement('span');
			mark.className = 'caas-control-scratch-flake';
			mark.setAttribute('aria-hidden', 'true');
			mark.textContent = '✦';
			mark.style.left = rect.left + 10 + Math.random() * (rect.width - 20) + 'px';
			mark.style.top = rect.top + 18 + Math.random() * (rect.height - 28) + 'px';
			document.body.appendChild(mark);
			window.setTimeout(function () {
				if (mark.parentNode) {
					mark.parentNode.removeChild(mark);
				}
			}, 600);
		}

		btn.addEventListener('click', function () {
			if (scratching) {
				return;
			}
			scratching = true;
			btn.classList.add('is-scratching');
			playMeowSound();
			toast((cfg.strings && cfg.strings.scrollPost) || 'Scramble!', 1500);
			spawnScratchMark();
			scratchInterval = window.setInterval(spawnScratchMark, 110);
			window.scrollTo({ top: 0, behavior: 'smooth' });

			endCheck = window.setInterval(function () {
				if (window.scrollY <= 2) {
					stopScratch();
				}
			}, 40);
			scratchTimeout = window.setTimeout(stopScratch, 8000);
		});
	}

	function wooCartWiggle() {
		if (!cfg.features || !cfg.features.wooCartWiggle) {
			return;
		}
		var cart = document.querySelector('.cart-contents, .wc-block-mini-cart');
		if (!cart) {
			return;
		}
		setInterval(function () {
			cart.classList.add('caas-control-wiggle');
			setTimeout(function () {
				cart.classList.remove('caas-control-wiggle');
			}, 600);
		}, 45000);
	}

	function wooCelebrate() {
		toast('Purr-chase complete! Order #' + (cfg.orderId || ''), 5000);
		behaviorWalk();
	}

	function previewMeowDirect() {
		playMeowSound();
		toast((cfg.i18n && cfg.i18n.meow) || 'Meow!', 2000);
	}

	var previewHandlers = {
		walk: behaviorWalk,
		sit: behaviorSit,
		follow: behaviorFollow,
		blockMouse: behaviorBlockMouse,
		randomBlock: behaviorRandomBlock,
		meow: previewMeowDirect,
	};

	window.caasControlPreviewBehavior = function (name) {
		var fn = previewHandlers[name];
		if (!fn) {
			return false;
		}
		cancelBehaviorEndTimer();
		cancelPublishGate();
		removeCreature();
		fn();
		return true;
	};

	function init() {
		if (cfg.previewHarness) {
			return;
		}
		if (cfg.loginFailed) {
			loginHiss();
			meow();
			return;
		}
		if (cfg.context === 'woo_thankyou') {
			wooCelebrate();
			return;
		}

		if (cfg.context === 'frontend' || cfg.context === 'backend' || cfg.context === 'block-editor') {
			scheduleChaos();
		}

		publishGatekeeper();
		idleJudgment();
		keyboardTrample();
		redDotTorture();
		fakeSecurity();
		footerPeekaboo();
		pawTrail();
		scrollPost();
		wooCartWiggle();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
