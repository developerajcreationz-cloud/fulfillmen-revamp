/* Fulfillmen Landing — behaviour + GSAP animation layer.
 * Progressive enhancement: every section is fully visible and the form works
 * (via admin-post.php) even if this file or GSAP fails to load. */
(function () {
	'use strict';

	var root = document.querySelector('[data-fm-lp]');
	if (!root) return;

	var CFG = window.FM_LP || { endpoint: '', types: {} };
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	var dl = function (o) { window.dataLayer = window.dataLayer || []; window.dataLayer.push(o); };
	var store = {
		get: function (s, k) { try { return window[s].getItem(k); } catch (e) { return null; } },
		set: function (s, k, v) { try { window[s].setItem(k, v); } catch (e) {} }
	};

	/* ---------- Header shadow ---------- */
	var header = $('[data-fm-header]');
	if (header) {
		var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ---------- Attribution: first-touch UTMs survive page hops (30 days) ---------- */
	var form = $('[data-fm-form]');
	var KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
	(function attribution() {
		var params = new URLSearchParams(window.location.search);
		var saved = {};
		try { saved = JSON.parse(store.get('localStorage', 'fm_attr') || '{}'); } catch (e) { saved = {}; }
		if (saved.t && Date.now() - saved.t > 30 * 864e5) saved = {};
		var fresh = KEYS.some(function (k) { return params.get(k); });
		if (fresh) {
			saved = { t: Date.now() };
			KEYS.forEach(function (k) { if (params.get(k)) saved[k] = params.get(k).slice(0, 200); });
			store.set('localStorage', 'fm_attr', JSON.stringify(saved));
		}
		if (!form) return;
		KEYS.forEach(function (k) {
			var el = form.elements[k];
			if (el && !el.value && saved[k]) el.value = saved[k];
		});
		form.elements.landing_url.value = window.location.href.split('#')[0].slice(0, 500);
		form.elements.referrer.value = (document.referrer || '').slice(0, 500);
	})();

	/* ---------- Pick your path → hidden lead_type ---------- */
	var leadInput = form && form.elements.lead_type;
	var pathLine = $('[data-fm-path-line]');
	function setPath(key, fromClick) {
		if (!CFG.types[key] || !leadInput) return;
		leadInput.value = key;
		store.set('sessionStorage', 'fm_path', key);
		$$('[data-fm-path]').forEach(function (c) { c.classList.toggle('is-selected', c.getAttribute('data-fm-path') === key); });
		if (pathLine) {
			$('[data-fm-path-empty]', pathLine).hidden = true;
			$('[data-fm-path-set]', pathLine).hidden = false;
			$('[data-fm-path-label]', pathLine).textContent = CFG.types[key];
			if (fromClick) {
				pathLine.classList.remove('is-flash');
				void pathLine.offsetWidth;
				pathLine.classList.add('is-flash');
			}
		}
	}
	if (leadInput && !leadInput.value) {
		var remembered = store.get('sessionStorage', 'fm_path');
		if (remembered) setPath(remembered, false);
	}
	$$('[data-fm-path]').forEach(function (card) {
		card.addEventListener('click', function (e) {
			if (!form) return;
			e.preventDefault();
			var key = card.getAttribute('data-fm-path');
			setPath(key, true);
			dl({ event: 'fm_path_select', lead_type: key });
			scrollToEl($('#quote'), function () { var n = $('#fm-name'); if (n) n.focus({ preventScroll: true }); });
		});
	});

	function scrollToEl(el, done) {
		if (!el) return;
		el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
		if (done) setTimeout(done, reduce ? 0 : 700);
	}

	/* CTA clicks → analytics */
	$$('[data-fm-cta]').forEach(function (a) {
		a.addEventListener('click', function () { dl({ event: 'fm_cta_click', cta: a.getAttribute('data-fm-cta') }); });
	});

	/* ---------- Form: inline validation + async submit ---------- */
	if (form) {
		var rules = {
			name: function (v) { return v.trim().length >= 2 || 'Please enter your name.'; },
			email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Please enter a valid email address.'; },
			phone: function (v) { return v.replace(/\D/g, '').length >= 6 || 'Please enter a WhatsApp or phone number with country code.'; },
			links: function (v) { return v.trim().length >= 3 || 'Add your store link or a product link.'; },
			volume: function (v) { return !!v || 'Choose your monthly order volume.'; }
		};
		var showErr = function (name, msg) {
			var el = form.elements[name];
			if (!el) return;
			var wrap = el.closest('.fm-field');
			var slot = $('[data-err="' + name + '"]', form);
			wrap.classList.toggle('is-invalid', !!msg);
			wrap.classList.toggle('is-valid', !msg);
			el.setAttribute('aria-invalid', msg ? 'true' : 'false');
			if (slot) { slot.textContent = msg || ''; slot.id = 'fm-err-' + name; el.setAttribute('aria-describedby', slot.id); }
		};
		var check = function (name) {
			var r = rules[name](form.elements[name].value);
			showErr(name, r === true ? '' : r);
			return r === true;
		};
		Object.keys(rules).forEach(function (n) {
			var el = form.elements[n];
			el.addEventListener('blur', function () { if (el.value) check(n); });
			el.addEventListener('input', function () { if (el.closest('.fm-field').classList.contains('is-invalid')) check(n); });
			el.addEventListener('change', function () { check(n); });
		});

		var started = false;
		form.addEventListener('focusin', function () {
			if (!started) { started = true; dl({ event: 'fm_form_start', lead_type: leadInput.value || 'unknown' }); }
		});

		var alertBox = $('[data-fm-alert]', form);
		var btn = $('[data-fm-submit]', form);

		form.addEventListener('submit', function (e) {
			var ok = Object.keys(rules).map(check).every(Boolean);
			if (!ok) {
				e.preventDefault();
				var first = $('.is-invalid input, .is-invalid textarea, .is-invalid select', form);
				if (first) first.focus();
				form.closest('.fm-formcard').classList.remove('fm-shake');
				void form.offsetWidth;
				form.closest('.fm-formcard').classList.add('fm-shake');
				return;
			}
			if (!window.fetch || !CFG.endpoint) return; // native POST fallback
			e.preventDefault();
			btn.classList.add('is-loading');
			$('.fm-btn__label', btn).textContent = 'Sending…';
			alertBox.hidden = true;

			var data = {};
			new FormData(form).forEach(function (v, k) { data[k] = v; });

			fetch(CFG.endpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'omit',
				body: JSON.stringify(data)
			}).then(function (r) {
				return r.json().then(function (j) { return { status: r.status, body: j }; });
			}).then(function (res) {
				if (res.body && res.body.ok && res.body.redirect) {
					dl({ event: 'fm_form_submit', lead_type: data.lead_type || 'unknown' });
					$('.fm-btn__label', btn).textContent = 'Done! Redirecting…';
					window.location.href = res.body.redirect;
					return;
				}
				var errs = (res.body && res.body.errors) || {};
				Object.keys(errs).forEach(function (k) { if (rules[k]) showErr(k, errs[k]); });
				alertBox.textContent = errs._ || 'Please check the highlighted fields.';
				alertBox.hidden = false;
				reset();
			}).catch(function () {
				// Network / JSON problem: let the browser post the form normally.
				form.submit();
			});
		});
		function reset() {
			btn.classList.remove('is-loading');
			$('.fm-btn__label', btn).textContent = 'Get my free quote';
		}
		window.addEventListener('pageshow', function (e) { if (e.persisted) reset(); });
	}

	/* ---------- Sticky mobile CTA: show after hero, hide while form is on screen ---------- */
	var sticky = $('[data-fm-sticky]');
	var hero = $('.fm-hero', root);
	var quote = $('#quote');
	if (sticky && hero && quote && 'IntersectionObserver' in window) {
		var heroOut = false, quoteIn = false;
		var upd = function () {
			var on = heroOut && !quoteIn;
			sticky.classList.toggle('is-on', on);
			sticky.setAttribute('aria-hidden', on ? 'false' : 'true');
			var a = $('a', sticky); if (a) a.tabIndex = on ? 0 : -1;
		};
		new IntersectionObserver(function (en) { heroOut = !en[0].isIntersecting; upd(); }).observe(hero);
		new IntersectionObserver(function (en) { quoteIn = en[0].isIntersecting; upd(); }, { threshold: 0.15 }).observe(quote);
	}

	/* ---------- Lazy warehouse video ---------- */
	var vids = $$('[data-fm-lazy-video]');
	if (vids.length && 'IntersectionObserver' in window) {
		var vo = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				var v = en.target;
				if (en.isIntersecting) {
					$$('source[data-src]', v).forEach(function (s) { s.src = s.getAttribute('data-src'); s.removeAttribute('data-src'); v.load(); });
					if (!reduce) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
				} else if (!v.paused) { v.pause(); }
			});
		}, { rootMargin: '200px' });
		vids.forEach(function (v) { vo.observe(v); });
	}

	/* ---------- Animation layer (GSAP) ---------- */
	var gsap = window.gsap;
	if (!gsap || reduce) return;
	var ST = window.ScrollTrigger;
	if (ST) gsap.registerPlugin(ST);

	/* Parcels travelling China → destinations */
	$$('.fm-parcel').forEach(function (p, i) {
		var path = document.getElementById(p.getAttribute('data-route'));
		if (!path || !path.getTotalLength) return;
		var len = path.getTotalLength();
		var o = { t: 0 };
		gsap.set(p, { opacity: 1 });
		gsap.to(o, {
			t: 1, duration: 3.2 + (i % 3) * 0.6, ease: 'power1.inOut', repeat: -1, delay: 0.8 + i * 0.55, repeatDelay: 0.6,
			onUpdate: function () {
				var pt = path.getPointAtLength(o.t * len);
				p.setAttribute('x', pt.x - 6);
				p.setAttribute('y', pt.y - 6);
				p.style.opacity = o.t < 0.06 ? o.t / 0.06 : (o.t > 0.92 ? (1 - o.t) / 0.08 : 1);
			}
		});
	});

	/* Count-up numbers */
	function countUp(el) {
		var end = parseFloat(el.getAttribute('data-fm-count'));
		if (isNaN(end)) return;
		var dec = parseInt(el.getAttribute('data-fm-dec') || '0', 10);
		var pre = el.getAttribute('data-fm-prefix') || '';
		var o = { v: 0 };
		gsap.to(o, { v: end, duration: 1.8, ease: 'power3.out', onUpdate: function () { el.textContent = pre + o.v.toFixed(dec); } });
	}
	var heroCount = $('.fm-float--cost [data-fm-count]');
	if (heroCount) gsap.delayedCall(1, function () { countUp(heroCount); });

	if (!ST) return;

	/* Hero parallax */
	gsap.to('.fm-float--qc', { yPercent: -60, ease: 'none', scrollTrigger: { trigger: '.fm-hero', start: 'top top', end: 'bottom top', scrub: true } });
	gsap.to('.fm-float--track', { yPercent: -120, ease: 'none', scrollTrigger: { trigger: '.fm-hero', start: 'top top', end: 'bottom top', scrub: true } });
	gsap.to('.fm-map__svg', { yPercent: 10, scale: 0.96, ease: 'none', scrollTrigger: { trigger: '.fm-hero', start: 'top top', end: 'bottom top', scrub: true } });

	/* Generic reveals (batched) — states set by JS so content is visible without it */
	var reveals = $$('.fm-reveal', root);
	gsap.set(reveals, { autoAlpha: 0, y: 36 });
	ST.batch(reveals, {
		start: 'top 88%',
		once: true,
		onEnter: function (batch) { gsap.to(batch, { autoAlpha: 1, y: 0, duration: 0.85, ease: 'power3.out', stagger: 0.1, overwrite: true }); }
	});

	/* How it works: line draws with scroll (scrub), steps pop in once, in order */
	var line = $('.fm-steps__line path');
	var steps = $$('.fm-step');
	if (line && line.getBoundingClientRect().width > 0) {
		gsap.fromTo(line, { strokeDasharray: 1000, strokeDashoffset: 1000 }, { strokeDashoffset: 0, ease: 'none', scrollTrigger: { trigger: '.fm-steps', start: 'top 80%', end: 'bottom 60%', scrub: 0.8 } });
	}
	if (steps.length) {
		gsap.set(steps, { autoAlpha: 0, y: 40 });
		ST.create({
			trigger: '.fm-steps', start: 'top 82%', once: true,
			onEnter: function () {
				var tl = gsap.timeline();
				steps.forEach(function (s, i) {
					tl.to(s, { autoAlpha: 1, y: 0, duration: 0.7, ease: 'power2.out' }, i * 0.28)
					  .fromTo($('.fm-step__ico', s), { scale: 0.4, rotate: -20 }, { scale: 1, rotate: 0, duration: 0.8, ease: 'back.out(2)' }, i * 0.28);
				});
			}
		});
	}

	/* Path cards: staggered entry + 3D tilt & cursor glow */
	var cards = $$('.fm-path');
	if (finePointer) {
		cards.forEach(function (c) {
			var rx = gsap.quickTo(c, 'rotationX', { duration: 0.5, ease: 'power3' });
			var ry = gsap.quickTo(c, 'rotationY', { duration: 0.5, ease: 'power3' });
			gsap.set(c, { transformPerspective: 900 });
			c.addEventListener('pointermove', function (e) {
				var r = c.getBoundingClientRect();
				var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
				rx((0.5 - py) * 8); ry((px - 0.5) * 10);
				c.style.setProperty('--mx', (px * 100) + '%');
				c.style.setProperty('--my', (py * 100) + '%');
			});
			c.addEventListener('pointerleave', function () { rx(0); ry(0); });
		});

		/* Magnetic CTAs */
		$$('.fm-magnetic').forEach(function (b) {
			var xTo = gsap.quickTo(b, 'x', { duration: 0.4, ease: 'power3' });
			var yTo = gsap.quickTo(b, 'y', { duration: 0.4, ease: 'power3' });
			b.addEventListener('pointermove', function (e) {
				var r = b.getBoundingClientRect();
				xTo((e.clientX - r.left - r.width / 2) * 0.18);
				yTo((e.clientY - r.top - r.height / 2) * 0.3);
			});
			b.addEventListener('pointerleave', function () { xTo(0); yTo(0); });
		});
	}

	/* Photo inspection: checklist ticks in after the "shutter" */
	var checks = $$('.fm-inspect__list li');
	if (checks.length) {
		gsap.fromTo(checks, { autoAlpha: 0, x: -16 }, {
			autoAlpha: 1, x: 0, stagger: 0.35, duration: 0.5, ease: 'back.out(2)',
			scrollTrigger: { trigger: '.fm-inspect', start: 'top 70%', once: true }
		});
	}

	/* Proof: counters + delivery truck */
	$$('.fm-stat [data-fm-count]').forEach(function (el) {
		ST.create({ trigger: el, start: 'top 90%', once: true, onEnter: function () { countUp(el); } });
	});
	var fill = $('.fm-track__fill'), truck = $('.fm-track__truck');
	if (fill && truck) {
		var ttl = gsap.timeline({ scrollTrigger: { trigger: '.fm-delivery', start: 'top 75%', once: true } });
		ttl.fromTo(fill, { scaleX: 0 }, { scaleX: 1, duration: 2.2, ease: 'power2.inOut' }, 0)
		   .fromTo(truck, { left: '0%' }, { left: '100%', duration: 2.2, ease: 'power2.inOut' }, 0)
		   .fromTo('.fm-track__stops li', { autoAlpha: 0, y: 10 }, { autoAlpha: 1, y: 0, stagger: 0.7, duration: 0.4 }, 0.1);
	}

	/* Comparison rows slide in */
	var rows = $$('.fm-compare__row:not(.fm-compare__row--head)');
	if (rows.length) {
		gsap.fromTo(rows, { autoAlpha: 0, x: 30 }, { autoAlpha: 1, x: 0, stagger: 0.12, duration: 0.6, ease: 'power2.out', scrollTrigger: { trigger: '.fm-compare', start: 'top 75%', once: true } });
	}

	/* Quote receipt: lines "print" in */
	var bars = $$('.fm-receipt li i');
	if (bars.length) {
		gsap.fromTo(bars, { scaleX: 0 }, { scaleX: 1, duration: 0.7, stagger: 0.18, ease: 'power3.out', scrollTrigger: { trigger: '.fm-receipt', start: 'top 80%', once: true } });
	}

	/* FAQ: animate open/close height */
	$$('.fm-acc__item').forEach(function (d) {
		var sum = $('summary', d), body = $('.fm-acc__body', d);
		sum.addEventListener('click', function (e) {
			e.preventDefault();
			if (d.open) {
				gsap.to(body, { height: 0, duration: 0.35, ease: 'power2.inOut', onComplete: function () { d.open = false; gsap.set(body, { clearProps: 'height' }); } });
			} else {
				d.open = true;
				gsap.fromTo(body, { height: 0 }, { height: body.scrollHeight, duration: 0.4, ease: 'power2.out', onComplete: function () { gsap.set(body, { clearProps: 'height' }); } });
				dl({ event: 'fm_faq_open', q: sum.textContent.trim().slice(0, 80) });
			}
		});
	});

	/* Recalculate once fonts/images settle */
	window.addEventListener('load', function () { ST.refresh(); });
})();
