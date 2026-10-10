(function () {
	'use strict';

	var cfg = window.caasControlChaos;
	if (!cfg) {
		return;
	}

	var AGENT_EMOJI = {
		cat: '🐈',
		dog: '🐕',
		mouse: '🐁',
		horse: '🐎',
		rat: '🐀',
		custom: '🖼️',
	};

	var creatureEl = null;
	var followRaf = null;
	var chaosTimer = null;
	var meowAudio = null;

	function creatureImageUrl() {
		if (cfg.agent === 'custom' && cfg.customImageUrl) {
			return cfg.customImageUrl;
		}
		if (cfg.agent === 'cat' && cfg.catImageUrl) {
			return cfg.catImageUrl;
		}
		return '';
	}

	function agentGlyph() {
		if (creatureImageUrl()) {
			return null;
		}
		return AGENT_EMOJI[cfg.agent] || AGENT_EMOJI.cat;
	}

	function ensureCreature() {
		if (creatureEl) {
			return creatureEl;
		}
		creatureEl = document.createElement('div');
		creatureEl.className = 'caas-control-creature';
		creatureEl.setAttribute('role', 'img');
		creatureEl.setAttribute('aria-label', cfg.companionName || 'Chaos agent');

		var glyph = agentGlyph();
		if (glyph) {
			creatureEl.textContent = glyph;
		} else {
			var img = document.createElement('img');
			img.src = creatureImageUrl();
			img.alt = cfg.companionName || '';
			creatureEl.appendChild(img);
		}

		document.body.appendChild(creatureEl);
		return creatureEl;
	}

	function removeCreature() {
		if (followRaf) {
			cancelAnimationFrame(followRaf);
			followRaf = null;
		}
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
		meow();
	}

	function behaviorSit() {
		var el = ensureCreature();
		el.className = 'caas-control-creature caas-control-creature--sit';
		el.style.transform = 'translate(' + (window.innerWidth / 2 - 30) + 'px, ' + (window.innerHeight / 2) + 'px)';
		toast((cfg.companionName || 'Companion') + ' is judging you.', 2500);
	}

	function behaviorFollow() {
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
		meow();
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
		pool[Math.floor(Math.random() * pool.length)]();
		setTimeout(removeCreature, ((cfg.durationSeconds || 5) + 1) * 1000);
	}

	function scheduleChaos() {
		var minutes = cfg.frequencyMinutes;
		var ms;
		if (minutes === 0) {
			ms = (120 + Math.random() * 180) * 1000;
		} else {
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
				var msg = cfg.strings.publishGate || 'Cat is reviewing your changes.';
				toast(msg, 10000);
				behaviorSit();
				setTimeout(function () {
					btn.click();
				}, 10000);
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
			meow();
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
		if (cfg.agent === 'cat' && cfg.catImageUrl) {
			var peekImg = document.createElement('img');
			peekImg.src = cfg.catImageUrl;
			peekImg.alt = '';
			peekImg.width = 56;
			peekImg.height = 56;
			peek.appendChild(peekImg);
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
				meow();
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
			p.textContent = '🐾';
			p.style.left = e.clientX + 'px';
			p.style.top = e.clientY + 'px';
			document.body.appendChild(p);
			setTimeout(function () {
				if (p.parentNode) {
					p.parentNode.removeChild(p);
				}
			}, 1200);
		});
	}

	function scrollPost() {
		if (!cfg.features || !cfg.features.scrollPost) {
			return;
		}
		var btn = document.querySelector('.caas-control-scroll-post');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
			toast('Scramble!', 1500);
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

	function init() {
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
