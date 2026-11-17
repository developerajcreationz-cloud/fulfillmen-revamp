/* Fulfillmen Landing v3 — behaviour + motion.
 * Progressive enhancement: all content is visible and the form posts via
 * admin-post.php even if this file, Lenis or GSAP fail to load. */
(function () {
	'use strict';

	var root = document.querySelector('[data-fm-lp]');
	if (!root) return;

	var CFG = window.FM_LP || { endpoint: '', types: {} };
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	var dl = function (o) { window.dataLayer = window.dataLayer || []; window.dataLayer.push(o); };
	var store = {
		get: function (s, k) { try { return window[s].getItem(k); } catch (e) { return null; } },
		set: function (s, k, v) { try { window[s].setItem(k, v); } catch (e) {} }
	};
	var gsap = window.gsap;
	var ST = window.ScrollTrigger;
	var anim = !!gsap && !reduce;
	if (anim && ST) gsap.registerPlugin(ST);

	/* ---------- Smooth scroll (Lenis, desktop wheel only; touch stays native) ---------- */
	var lenis = null;
	if (window.Lenis && !reduce) {
		lenis = new window.Lenis({ duration: 1.15, easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); }, smoothWheel: true });
		if (anim && ST) {
			lenis.on('scroll', ST.update);
			gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
			gsap.ticker.lagSmoothing(0);
		} else {
			(function raf(t) { lenis.raf(t); requestAnimationFrame(raf); })(0);
		}
	}
	function scrollToEl(el, done) {
		if (!el) return;
		if (lenis) lenis.scrollTo(el, { offset: -90, onComplete: done });
		else { el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); if (done) setTimeout(done, reduce ? 0 : 700); }
	}
	document.addEventListener('click', function (e) {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey) return;
		var a = e.target.closest && e.target.closest('a[href^="#"]');
		if (!a) return;
		var id = a.getAttribute('href').slice(1);
		var t = id === 'top' ? document.body : document.getElementById(id);
		if (!t) return;
		e.preventDefault();
		if (id === 'top' && lenis) lenis.scrollTo(0); else scrollToEl(t);
		if (history.replaceState) history.replaceState(null, '', '#' + id);
	});

	/* ---------- Header: glass on scroll, hide on scroll down, active link ---------- */
	var header = $('[data-fm-header]');
	if (header) {
		var lastY = 0;
		var onScroll = function () {
			var y = window.scrollY;
			header.classList.toggle('is-scrolled', y > 10);
			lastY = y;
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
		var links = $$('.fm-header__nav a');
		if (links.length && 'IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (en) {
				en.forEach(function (x) {
					if (!x.isIntersecting) return;
					links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + x.target.id); });
				});
			}, { rootMargin: '-45% 0px -50% 0px' });
			links.forEach(function (l) { var s = document.getElementById(l.getAttribute('href').slice(1)); if (s) io.observe(s); });
		}
	}

	/* ---------- Attribution: first-touch UTMs survive page hops (30 days) ---------- */
	var form = $('[data-fm-form]');
	var KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid'];
	(function () {
		var params = new URLSearchParams(window.location.search);
		var saved = {};
		try { saved = JSON.parse(store.get('localStorage', 'fm_attr') || '{}'); } catch (e) { saved = {}; }
		if (saved.t && Date.now() - saved.t > 30 * 864e5) saved = {};
		if (KEYS.some(function (k) { return params.get(k); })) {
			saved = { t: Date.now() };
			KEYS.forEach(function (k) { if (params.get(k)) saved[k] = params.get(k).slice(0, 200); });
			store.set('localStorage', 'fm_attr', JSON.stringify(saved));
		}
		if (!form) return;
		KEYS.forEach(function (k) { var el = form.elements[k]; if (el && !el.value && saved[k]) el.value = saved[k]; });
		form.elements.landing_url.value = window.location.href.split('#')[0].slice(0, 500);
		form.elements.referrer.value = (document.referrer || '').slice(0, 500);
	})();

	/* ---------- Pick your path → hidden lead_type ---------- */
	var leadInput = form && form.elements.lead_type;
	var pathLine = $('[data-fm-path-line]');
	function setPath(key, flash) {
		if (!CFG.types[key] || !leadInput) return;
		leadInput.value = key;
		store.set('sessionStorage', 'fm_path', key);
		$$('[data-fm-path]').forEach(function (c) { c.classList.toggle('is-selected', c.getAttribute('data-fm-path') === key); });
		if (!pathLine) return;
		$('[data-fm-path-empty]', pathLine).hidden = true;
		$('[data-fm-path-set]', pathLine).hidden = false;
		$('[data-fm-path-label]', pathLine).textContent = CFG.types[key];
		if (flash) { pathLine.classList.remove('is-flash'); void pathLine.offsetWidth; pathLine.classList.add('is-flash'); }
	}
	if (leadInput && !leadInput.value) { var rem = store.get('sessionStorage', 'fm_path'); if (rem) setPath(rem, false); }
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
	$$('[data-fm-cta]').forEach(function (a) { a.addEventListener('click', function () { dl({ event: 'fm_cta_click', cta: a.getAttribute('data-fm-cta') }); }); });

	/* ---------- Form: inline validation + async submit ---------- */
	if (form) {
		var val = function (n) { var el = form.elements[n]; return el ? (el.value || '') : ''; };
		var rules = {
			name: function (v) { return v.trim().length >= 2 || 'Please enter your name.'; },
			email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Please enter a valid email address.'; },
			phone: function (v) { return v.replace(/\D/g, '').length >= 6 || 'Please enter a WhatsApp or phone number with country code.'; },
			links: function (v) { return v.trim().length >= 3 || 'Add your store link or a product link.'; },
			volume: function (v) { return !!v || 'Choose your monthly order volume.'; }
		};
		var showErr = function (name, msg) {
			var slot = $('[data-err="' + name + '"]', form);
			if (!slot) return;
			var wrap = slot.closest('.fm-field');
			wrap.classList.toggle('is-invalid', !!msg);
			slot.textContent = msg || '';
			slot.id = 'fm-err-' + name;
			var el = form.elements[name];
			var els = el && el.length && !el.tagName ? Array.prototype.slice.call(el) : [el];
			els.forEach(function (x) { if (x && x.setAttribute) { x.setAttribute('aria-invalid', msg ? 'true' : 'false'); x.setAttribute('aria-describedby', slot.id); } });
		};
		var check = function (n) { var r = rules[n](val(n)); showErr(n, r === true ? '' : r); return r === true; };
		['name', 'email', 'phone', 'links'].forEach(function (n) {
			var el = form.elements[n];
			el.addEventListener('blur', function () { if (el.value) check(n); });
			el.addEventListener('input', function () { if (el.closest('.fm-field').classList.contains('is-invalid')) check(n); });
		});
		$$('input[name="volume"]', form).forEach(function (r) { r.addEventListener('change', function () { check('volume'); }); });

		var started = false;
		form.addEventListener('focusin', function () { if (!started) { started = true; dl({ event: 'fm_form_start', lead_type: leadInput.value || 'unknown' }); } });

		var alertBox = $('[data-fm-alert]', form);
		var btn = $('[data-fm-submit]', form);
		var label = $('.fm-btn__label', btn);
		var reset = function () { btn.classList.remove('is-loading'); label.textContent = 'Get my free quote'; };

		form.addEventListener('submit', function (e) {
			if (!Object.keys(rules).map(check).every(Boolean)) {
				e.preventDefault();
				var first = $('.is-invalid input, .is-invalid textarea', form);
				if (first) first.focus();
				var card = form.closest('.fm-formdoc');
				card.classList.remove('fm-shake'); void card.offsetWidth; card.classList.add('fm-shake');
				return;
			}
			if (!window.fetch || !CFG.endpoint) return; // native POST fallback
			e.preventDefault();
			btn.classList.add('is-loading');
			label.textContent = 'Sending…';
			alertBox.hidden = true;
			var data = {};
			new FormData(form).forEach(function (v, k) { data[k] = v; });
			fetch(CFG.endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'omit', body: JSON.stringify(data) })
				.then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
				.then(function (res) {
					if (res.body && res.body.ok && res.body.redirect) {
						dl({ event: 'fm_form_submit', lead_type: data.lead_type || 'unknown' });
						label.textContent = 'Done. Redirecting…';
						window.location.href = res.body.redirect;
						return;
					}
					var errs = (res.body && res.body.errors) || {};
					Object.keys(errs).forEach(function (k) { if (rules[k]) showErr(k, errs[k]); });
					alertBox.textContent = errs._ || 'Please check the highlighted fields.';
					alertBox.hidden = false;
					reset();
				})
				.catch(function () { form.submit(); });
		});
		window.addEventListener('pageshow', function (e) { if (e.persisted) reset(); });
	}

	/* ---------- Sticky mobile CTA: after hero, hidden while form is on screen ---------- */
	var sticky = $('[data-fm-sticky]');
	var hero = $('.fm-hero', root);
	var quote = $('#quote');
	if (sticky && hero && quote && 'IntersectionObserver' in window) {
		var heroOut = false, quoteIn = false;
		var upd = function () {
			var on = heroOut && !quoteIn;
			sticky.classList.toggle('is-on', on);
			sticky.setAttribute('aria-hidden', on ? 'false' : 'true');
			$('a', sticky).tabIndex = on ? 0 : -1;
		};
		new IntersectionObserver(function (en) { heroOut = !en[0].isIntersecting; upd(); }).observe(hero);
		new IntersectionObserver(function (en) { quoteIn = en[0].isIntersecting; upd(); }, { threshold: 0.1 }).observe(quote);
	}

	/* ---------- Lazy warehouse video ---------- */
	var vids = $$('[data-fm-lazy-video]');
	if (vids.length && 'IntersectionObserver' in window) {
		var vo = new IntersectionObserver(function (en) {
			en.forEach(function (x) {
				var v = x.target;
				if (x.isIntersecting) {
					$$('source[data-src]', v).forEach(function (s) { s.src = s.getAttribute('data-src'); s.removeAttribute('data-src'); v.load(); });
					if (!reduce) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
				} else if (!v.paused) v.pause();
			});
		}, { rootMargin: '200px' });
		vids.forEach(function (v) { vo.observe(v); });
	}

	/* ---------- Waybill: "ship to" cycles through real destinations ---------- */
	var dest = $('[data-fm-dest-c]');
	if (dest && !reduce) {
		var places = ['Los Angeles, US', 'London, UK', 'Sydney, AU', 'Toronto, CA', 'Frankfurt, DE', 'Dubai, AE', 'New York, US'], di = 0;
		setInterval(function () {
			if (document.hidden) return;
			dest.style.opacity = 0;
			setTimeout(function () { di = (di + 1) % places.length; dest.textContent = places[di]; dest.style.opacity = 1; }, 300);
		}, 2600);
	}

	/* ---------- Departure board: rows flip to the next lane ---------- */
	var boardRows = $('[data-fm-board]');
	var boardData = $('[data-fm-board-data]');
	if (boardRows && boardData && !reduce) {
		var lanes = [];
		try { lanes = JSON.parse(boardData.textContent); } catch (e) { lanes = []; }
		var next = 3, rowI = 0;
		setInterval(function () {
			if (document.hidden || !lanes.length) return;
			var row = boardRows.children[rowI % boardRows.children.length];
			var lane = lanes[next % lanes.length];
			$$('.fm-flap', row).forEach(function (c, k) {
				setTimeout(function () {
					c.classList.remove('is-flip'); void c.offsetWidth; c.classList.add('is-flip');
					setTimeout(function () {
						if (k === 0) c.innerHTML = lane[0] + ' <em>' + lane[1] + '</em>';
						else if (k === 3) { c.textContent = lane[4]; c.className = 'fm-flap fm-st ' + lane[5] + ' is-flip'; }
						else c.textContent = lane[k + 1];
					}, 250);
				}, k * 90);
			});
			next++; rowI++;
		}, 2400);
	}

	if (!anim) return;

	/* ---------- Map parcels (only animate while the map is on screen) ---------- */
	$$('.fm-world').forEach(function (svg) {
		var tweens = [];
		$$('.fm-world__parcels circle', svg).forEach(function (p, i) {
			var path = document.getElementById(p.getAttribute('data-route'));
			if (!path || !path.getTotalLength) return;
			var len = path.getTotalLength(), o = { t: 0 };
			tweens.push(gsap.to(o, {
				t: 1, duration: 3 + (i % 4) * 0.6, ease: 'power1.inOut', repeat: -1, delay: 0.4 + i * 0.45, repeatDelay: 0.8,
				onUpdate: function () {
					var pt = path.getPointAtLength(o.t * len);
					p.setAttribute('cx', pt.x); p.setAttribute('cy', pt.y);
					p.style.opacity = o.t < 0.06 ? o.t / 0.06 : (o.t > 0.94 ? (1 - o.t) / 0.06 : 1);
				}
			}));
		});
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (en) { tweens.forEach(function (t) { if (en[0].isIntersecting) t.resume(); else t.pause(); }); }).observe(svg);
		}
	});
	gsap.from('.fm-world--hero .fm-world__routes path', { opacity: 0, duration: 1.2, stagger: 0.12, delay: 0.5 });

	/* Waybill tilts toward the cursor */
	var waybill = $('[data-fm-waybill]');
	if (waybill && fine && hero) {
		var wx = gsap.quickTo(waybill, 'rotationY', { duration: 0.8, ease: 'power3' }), wy = gsap.quickTo(waybill, 'rotationX', { duration: 0.8, ease: 'power3' });
		hero.addEventListener('pointermove', function (e) {
			wx((e.clientX / window.innerWidth - 0.5) * 14);
			wy((0.5 - e.clientY / window.innerHeight) * 10);
		});
	}

	/* Count-up */
	function countUp(el) {
		var end = parseFloat(el.getAttribute('data-fm-count'));
		if (isNaN(end)) return;
		var dec = parseInt(el.getAttribute('data-fm-dec') || '0', 10), o = { v: 0 };
		gsap.to(o, { v: end, duration: 1.8, ease: 'power3.out', onUpdate: function () { el.textContent = o.v.toFixed(dec); } });
	}

	/* Magnetic CTAs */
	if (fine) {
		$$('.fm-magnetic').forEach(function (b) {
			var xTo = gsap.quickTo(b, 'x', { duration: 0.5, ease: 'power3' }), yTo = gsap.quickTo(b, 'y', { duration: 0.5, ease: 'power3' });
			b.addEventListener('pointermove', function (e) { var r = b.getBoundingClientRect(); xTo((e.clientX - r.left - r.width / 2) * 0.15); yTo((e.clientY - r.top - r.height / 2) * 0.3); });
			b.addEventListener('pointerleave', function () { xTo(0); yTo(0); });
		});
	}

	if (!ST) return;

	/* Generic reveals */
	var reveals = $$('.fm-reveal', root);
	gsap.set(reveals, { autoAlpha: 0, y: 30 });
	ST.batch(reveals, {
		start: 'top 90%', once: true,
		onEnter: function (b) { gsap.to(b, { autoAlpha: 1, y: 0, duration: 0.85, ease: 'power3.out', stagger: 0.08, overwrite: true }); }
	});

	/* Conveyor: the carton rides the line as you scroll and changes at each station */
	var line = $('[data-fm-line]');
	var box = $('[data-fm-box]');
	var stations = $$('.fm-station');
	if (line && stations.length) {
		var fillEl = $('.fm-line__fill', line);
		var qc = box && $('.b-qc', box), lbl = box && $('.b-label', box), tape = box && $('.b-tape', box);
		var setState = function (p) {
			var idx = Math.min(stations.length - 1, Math.floor(p * stations.length * 0.999));
			stations.forEach(function (s, k) { s.classList.toggle('is-on', k === idx); });
			if (qc) qc.style.opacity = p > 0.3 ? 1 : 0;
			if (tape) tape.style.opacity = p > 0.55 ? 1 : 0;
			if (lbl) lbl.style.opacity = p > 0.8 ? 1 : 0;
		};
		setState(0);
		if (fillEl) fillEl.style.transform = 'scaleX(0)';
		ST.create({
			trigger: line, start: 'top 75%', end: 'bottom 45%', scrub: 0.6,
			onUpdate: function (self) {
				var p = self.progress;
				if (box && box.offsetParent) box.style.left = (p * 100) + '%';
				if (fillEl) fillEl.style.transform = 'scaleX(' + p + ')';
				setState(p);
			}
		});
	}

	/* Inspection report: polaroids drop, then APPROVED slams down */
	var report = $('.fm-report');
	if (report) {
		var stamp = $('[data-fm-stamp]', report);
		gsap.set(stamp, { autoAlpha: 0, scale: 2.6 });
		var rtl = gsap.timeline({ scrollTrigger: { trigger: report, start: 'top 65%', once: true } });
		rtl.from($$('.fm-polaroid', report), { y: -30, autoAlpha: 0, stagger: 0.1, duration: 0.5, ease: 'back.out(1.6)' })
		   .from($$('.fm-report__list li', report), { autoAlpha: 0, x: -12, stagger: 0.15, duration: 0.35 }, '-=0.1')
		   .to(stamp, { autoAlpha: 0.9, scale: 1, duration: 0.28, ease: 'power4.in' })
		   .to(report, { x: 3, duration: 0.05, yoyo: true, repeat: 3 }, '>-0.02');
	}

	/* Proof: counters, tracking milestones, comparison rows, receipt lines */
	$$('.fm-manifest [data-fm-count]').forEach(function (el) { ST.create({ trigger: el, start: 'top 92%', once: true, onEnter: function () { countUp(el); } }); });
	var trk = $$('[data-fm-track] li');
	if (trk.length) {
		trk.forEach(function (li) { li.style.setProperty('--p', 0); });
		var po = { v: 0 };
		gsap.to(po, {
			v: trk.length - 1, duration: (trk.length - 1) * 0.4, ease: 'none', delay: 0.15,
			scrollTrigger: { trigger: '[data-fm-track]', start: 'top 75%', once: true },
			onUpdate: function () { trk.forEach(function (li, k) { li.style.setProperty('--p', Math.max(0, Math.min(1, po.v - k))); }); }
		});
	}
	var rows = $$('.fm-vs__row:not(.fm-vs__row--h)');
	if (rows.length) gsap.from(rows, { autoAlpha: 0, x: 24, stagger: 0.1, duration: 0.5, ease: 'power2.out', scrollTrigger: { trigger: '.fm-vs', start: 'top 80%', once: true } });
	var dots = $$('.fm-receipt li i');
	if (dots.length) gsap.from(dots, { scaleX: 0, duration: 0.5, stagger: 0.12, ease: 'power2.out', scrollTrigger: { trigger: '.fm-receipt', start: 'top 85%', once: true } });

	/* FAQ: smooth open/close */
	$$('.fm-acc__item').forEach(function (d) {
		var sum = $('summary', d), body = $('.fm-acc__body', d);
		sum.addEventListener('click', function (e) {
			e.preventDefault();
			if (d.open) {
				gsap.to(body, { height: 0, duration: 0.35, ease: 'power2.inOut', onComplete: function () { d.open = false; gsap.set(body, { clearProps: 'height' }); ST.refresh(); } });
			} else {
				d.open = true;
				gsap.fromTo(body, { height: 0 }, { height: body.scrollHeight, duration: 0.45, ease: 'power3.out', onComplete: function () { gsap.set(body, { clearProps: 'height' }); ST.refresh(); } });
				dl({ event: 'fm_faq_open', q: sum.textContent.trim().slice(0, 80) });
			}
		});
	});

	window.addEventListener('load', function () { ST.refresh(); });
})();
