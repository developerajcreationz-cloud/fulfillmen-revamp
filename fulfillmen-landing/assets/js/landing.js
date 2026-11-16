/* Fulfillmen Landing v2 — behaviour + motion.
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
			header.classList.toggle('is-hidden', y > 600 && y > lastY + 4);
			if (y < lastY - 4 || y < 600) header.classList.remove('is-hidden');
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
				var card = form.closest('.fm-formcard');
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

	/* ---------- Dashboard: dotted map + live order feed ---------- */
	var dots = $('.fm-routes__dots');
	if (dots) {
		var ns = 'http://www.w3.org/2000/svg', frag = document.createDocumentFragment();
		for (var y = 10; y < 200; y += 12) {
			for (var x = 10; x < 420; x += 12) {
				// cheap "continent" mask: a few soft blobs
				var on = [[110, 60, 70], [170, 120, 55], [300, 90, 95], [360, 175, 40], [70, 150, 40]].some(function (b) { var dx = x - b[0], dy = (y - b[1]) * 1.3; return dx * dx + dy * dy < b[2] * b[2]; });
				if (!on) continue;
				var c = document.createElementNS(ns, 'circle');
				c.setAttribute('cx', x); c.setAttribute('cy', y); c.setAttribute('r', 1.6);
				frag.appendChild(c);
			}
		}
		dots.appendChild(frag);
	}
	var feed = $('[data-fm-feed]');
	if (feed && !reduce) {
		var n = 20482, cc = ['US', 'UK', 'DE', 'AU', 'CA', 'FR', 'AE', 'NL'], ch = ['Shopify', 'Amazon FBA', 'WooCommerce', 'eBay', 'TikTok Shop'];
		var st = [['s-qc', 'QC passed'], ['s-pack', 'Packed'], ['s-ship', 'Shipped'], ['s-done', 'Delivered']];
		setInterval(function () {
			if (document.hidden) return;
			// advance existing rows one stage
			$$('li', feed).forEach(function (li) {
				var em = $('em', li), idx = st.findIndex(function (s) { return em.classList.contains(s[0]); });
				if (idx > -1 && idx < st.length - 1 && Math.random() > 0.4) { em.className = st[idx + 1][0]; em.textContent = st[idx + 1][1]; }
			});
			var li = document.createElement('li');
			li.className = 'is-new';
			li.innerHTML = '<span class="fm-cc">' + cc[n % cc.length] + '</span><span>#FM-' + n + ' · ' + ch[n % ch.length] + '</span><em class="s-qc">QC passed</em>';
			n++;
			feed.insertBefore(li, feed.firstChild);
			if (feed.children.length > 4) feed.removeChild(feed.lastElementChild);
		}, 2600);
	}

	if (!anim) return;

	/* Parcels along routes */
	$$('.fm-routes__parcels rect').forEach(function (p, i) {
		var path = document.getElementById(p.getAttribute('data-route'));
		if (!path || !path.getTotalLength) return;
		var len = path.getTotalLength(), o = { t: 0 };
		gsap.to(o, {
			t: 1, duration: 2.8 + (i % 3) * 0.5, ease: 'power1.inOut', repeat: -1, delay: 0.6 + i * 0.5, repeatDelay: 0.5,
			onUpdate: function () {
				var pt = path.getPointAtLength((1 - o.t) * len); // from Huizhou outwards
				p.setAttribute('x', pt.x - 4); p.setAttribute('y', pt.y - 4);
				p.style.opacity = o.t < 0.08 ? o.t / 0.08 : (o.t > 0.92 ? (1 - o.t) / 0.08 : 1);
			}
		});
	});

	/* Count-up */
	function countUp(el, delay) {
		var end = parseFloat(el.getAttribute('data-fm-count'));
		if (isNaN(end)) return;
		var dec = parseInt(el.getAttribute('data-fm-dec') || '0', 10), o = { v: 0 };
		gsap.to(o, { v: end, duration: 1.8, delay: delay || 0, ease: 'power3.out', onUpdate: function () {
			el.textContent = end >= 1000 ? Math.round(o.v).toLocaleString('en-US') : o.v.toFixed(dec);
		} });
	}
	$$('.fm-kpi [data-fm-count]').forEach(function (el) { countUp(el, 0.9); });

	/* Floating hero tiles follow the cursor */
	var tiles = $$('.fm-tile');
	if (fine && tiles.length) {
		var movers = tiles.map(function (t) {
			var d = parseFloat(t.getAttribute('data-depth')) || 1;
			return { x: gsap.quickTo(t, 'x', { duration: 1, ease: 'power3' }), y: gsap.quickTo(t, 'y', { duration: 1, ease: 'power3' }), d: d };
		});
		hero.addEventListener('pointermove', function (e) {
			var cx = e.clientX / window.innerWidth - 0.5, cy = e.clientY / window.innerHeight - 0.5;
			movers.forEach(function (m) { m.x(cx * 40 * m.d); m.y(cy * 30 * m.d); });
		});
	}

	if (!ST) return;

	/* Dashboard tilts up into place */
	var stage = $('[data-fm-stage]');
	if (stage) {
		gsap.fromTo(stage, { rotationX: 14, scale: 0.94, transformPerspective: 1600, transformOrigin: '50% 0%' }, {
			rotationX: 0, scale: 1, ease: 'none',
			scrollTrigger: { trigger: stage, start: 'top 92%', end: 'top 25%', scrub: true }
		});
	}
	gsap.to('.fm-tiles', { yPercent: -18, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });

	/* Statement: words un-blur as you scroll */
	var words = $$('[data-fm-words] .fm-w');
	if (words.length) {
		gsap.fromTo(words, { opacity: 0.12, filter: 'blur(6px)' }, {
			opacity: 1, filter: 'blur(0px)', stagger: 0.08, ease: 'none',
			scrollTrigger: { trigger: '[data-fm-words]', start: 'top 85%', end: 'bottom 45%', scrub: 0.6 }
		});
	}

	/* Generic reveals */
	var reveals = $$('.fm-reveal', root);
	gsap.set(reveals, { autoAlpha: 0, y: 34, filter: 'blur(6px)' });
	ST.batch(reveals, {
		start: 'top 90%', once: true,
		onEnter: function (b) { gsap.to(b, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.9, ease: 'power3.out', stagger: 0.09, overwrite: true, clearProps: 'filter' }); }
	});

	/* Node flow: each step lights up and gets its "Completed" badge in order */
	var flow = $('[data-fm-nodes]');
	if (flow) {
		var nodes = $$('.fm-node', flow), lines = $$('.fm-node__ln i', flow), split = $('.fm-node__split', flow);
		gsap.set($$('.fm-node__done', flow), { scale: 0, autoAlpha: 0 });
		gsap.set(nodes, { autoAlpha: 0.35, y: 12 });
		gsap.set(lines, { scaleY: 0 });
		if (split) gsap.set(split, { clipPath: 'inset(0 50% 0 50%)' });
		var tl = gsap.timeline({ paused: true });
		nodes.forEach(function (nd, k) {
			var at = k * 0.55;
			tl.to(nd, { autoAlpha: 1, y: 0, duration: 0.4, ease: 'power2.out', onStart: function () { nd.classList.add('is-on'); } }, at)
			  .to($('.fm-node__done', nd), { scale: 1, autoAlpha: 1, duration: 0.35, ease: 'back.out(3)' }, at + 0.3)
			  .add(function () { nd.classList.remove('is-on'); }, at + 0.9);
			if (lines[k]) tl.to(lines[k], { scaleY: 1, duration: 0.25, ease: 'none' }, at + 0.35);
			if (split && k === lines.length - 1) tl.to(split, { clipPath: 'inset(0 0% 0 0%)', duration: 0.3 }, at + 0.55);
		});
		ST.create({ trigger: flow, start: 'top 70%', once: true, onEnter: function () { tl.play(); } });
	}

	/* Path cards: 3D tilt + cursor spotlight; magnetic buttons */
	if (fine) {
		$$('.fm-path').forEach(function (c) {
			var rx = gsap.quickTo(c, 'rotationX', { duration: 0.6, ease: 'power3' }), ry = gsap.quickTo(c, 'rotationY', { duration: 0.6, ease: 'power3' });
			gsap.set(c, { transformPerspective: 1000 });
			c.addEventListener('pointermove', function (e) {
				var r = c.getBoundingClientRect(), px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
				rx((0.5 - py) * 7); ry((px - 0.5) * 9);
				c.style.setProperty('--mx', (px * 100) + '%'); c.style.setProperty('--my', (py * 100) + '%');
			});
			c.addEventListener('pointerleave', function () { rx(0); ry(0); });
		});
		$$('.fm-magnetic').forEach(function (b) {
			var xTo = gsap.quickTo(b, 'x', { duration: 0.5, ease: 'power3' }), yTo = gsap.quickTo(b, 'y', { duration: 0.5, ease: 'power3' });
			b.addEventListener('pointermove', function (e) { var r = b.getBoundingClientRect(); xTo((e.clientX - r.left - r.width / 2) * 0.2); yTo((e.clientY - r.top - r.height / 2) * 0.35); });
			b.addEventListener('pointerleave', function () { xTo(0); yTo(0); });
		});
	}

	/* QC photo thumbs pop in like a shutter roll */
	var thumbs = $$('.fm-qc__thumbs i');
	if (thumbs.length) gsap.fromTo(thumbs, { scale: 0.5, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, stagger: 0.12, duration: 0.4, ease: 'back.out(2)', scrollTrigger: { trigger: '.fm-qc', start: 'top 70%', once: true } });
	var qcl = $$('.fm-qc__list li');
	if (qcl.length) gsap.fromTo(qcl, { autoAlpha: 0, x: -14 }, { autoAlpha: 1, x: 0, stagger: 0.25, duration: 0.45, delay: 0.6, ease: 'power2.out', scrollTrigger: { trigger: '.fm-qc', start: 'top 70%', once: true } });

	/* Proof: counters, delivery track, comparison rows, receipt bars */
	$$('.fm-stat [data-fm-count]').forEach(function (el) { ST.create({ trigger: el, start: 'top 92%', once: true, onEnter: function () { countUp(el); } }); });
	var fill = $('.fm-track__fill'), pin = $('.fm-track__pin');
	if (fill && pin) {
		gsap.timeline({ scrollTrigger: { trigger: '.fm-track', start: 'top 80%', once: true } })
			.fromTo(fill, { scaleX: 0 }, { scaleX: 1, duration: 2, ease: 'power2.inOut' }, 0)
			.fromTo(pin, { left: '0%' }, { left: '100%', duration: 2, ease: 'power2.inOut' }, 0)
			.fromTo('.fm-track__stops li', { autoAlpha: 0, y: 8 }, { autoAlpha: 1, y: 0, stagger: 0.65, duration: 0.4 }, 0.1);
	}
	var rows = $$('.fm-vs__row:not(.fm-vs__row--h)');
	if (rows.length) gsap.fromTo(rows, { autoAlpha: 0, x: 24 }, { autoAlpha: 1, x: 0, stagger: 0.1, duration: 0.55, ease: 'power2.out', scrollTrigger: { trigger: '.fm-vs', start: 'top 80%', once: true } });
	var bars = $$('.fm-receipt li i');
	if (bars.length) gsap.fromTo(bars, { scaleX: 0 }, { scaleX: 1, duration: 0.7, stagger: 0.15, ease: 'power3.out', scrollTrigger: { trigger: '.fm-receipt', start: 'top 85%', once: true } });

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
