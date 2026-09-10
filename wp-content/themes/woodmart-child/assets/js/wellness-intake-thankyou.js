/**
 * The Wellness Hub — client intake form on the thank-you (order-received) page.
 *
 * Drives the 5-step wizard and submits over admin-ajax so the page never
 * reloads. Every failure mode falls back to a normal POST of the same form:
 * the server handles that native path too, so the client's answers are never
 * lost. With no JavaScript at all every step stays visible on one page.
 *
 * Config is provided by wp_localize_script() as window.wellnessIntake.
 *
 * @package woodmart-child
 */
(function () {
	'use strict';

	var cfg = window.wellnessIntake;

	if (!cfg || !cfg.ajaxUrl) {
		return;
	}

	function init() {
		var form = document.getElementById('wellness-thankyou-intake-form');

		if (!form) {
			return;
		}

		var section = document.getElementById('wellness-thankyou-intake');
		var progress = section ? section.querySelector('.wellness-progress') : null;
		var steps = form.querySelectorAll('.wellness-intake-th-step');
		var dots = section ? section.querySelectorAll('.wellness-progress__dot') : [];
		var btn = document.getElementById('wellness-intake-th-submit');
		var err = document.getElementById('wellness-intake-th-error');
		var ok = document.getElementById('wellness-intake-th-success');
		var sending = false;
		var current = 1;

		/* ── Small helpers ─────────────────────────────────────────────── */

		function hide(el) {
			if (el) {
				el.hidden = true;
			}
		}

		function show(el, msg) {
			if (!el) {
				return;
			}
			el.hidden = false;
			if (msg) {
				el.textContent = msg;
			}
			el.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		function busy(state) {
			sending = state;
			if (!btn) {
				return;
			}
			btn.disabled = state;
			btn.textContent = state ? cfg.i18n.saving : cfg.i18n.submit;
		}

		function closestMatch(node, selector) {
			while (node && node !== form) {
				if (node.matches && node.matches(selector)) {
					return node;
				}
				node = node.parentNode;
			}

			return null;
		}

		function stepOf(node) {
			var step = closestMatch(node, '.wellness-intake-th-step');

			return step ? parseInt(step.getAttribute('data-step'), 10) || 0 : 0;
		}

		/** Label text for a field, without its required asterisk. */
		function fieldLabel(field) {
			var label = form.querySelector('label[for="' + field.id + '"]');
			var text = label ? label.textContent.replace('*', '').trim() : '';

			return text || cfg.i18n.required;
		}

		function scrollToTop() {
			if (!section) {
				return;
			}

			var top = section.getBoundingClientRect().top + window.pageYOffset - 80;

			window.scrollTo({ top: top > 0 ? top : 0, behavior: 'smooth' });
		}

		function markEntering(step) {
			step.classList.add('wellness-step-entering');
			window.setTimeout(function () {
				step.classList.remove('wellness-step-entering');
			}, 400);
		}

		function goTo(step, skipScroll) {
			if (!steps.length) {
				return;
			}

			current = Math.min(Math.max(step, 1), steps.length);

			Array.prototype.forEach.call(steps, function (el) {
				var isCurrent = parseInt(el.getAttribute('data-step'), 10) === current;

				el.classList.toggle('is-current', isCurrent);

				if (isCurrent) {
					markEntering(el);
				}
			});

			Array.prototype.forEach.call(dots, function (dot) {
				var index = parseInt(dot.getAttribute('data-dot'), 10);

				dot.classList.toggle('wellness-progress__dot--active', index === current);
				dot.classList.toggle('wellness-progress__dot--done', index < current);
			});

			hide(err);

			if (!skipScroll) {
				scrollToTop();
			}
		}

		/* ── Validation ────────────────────────────────────────────────── */

		/**
		 * The form is marked novalidate: we own the checks so the client gets
		 * one clear message instead of the browser's per-field bubbles.
		 *
		 * @param {Element} [scope] Element to check; defaults to the whole form.
		 * @return {Object|null} { step, message } for the first empty required
		 *                       field, or null when everything is filled in.
		 */
		function invalid(scope) {
			var fields = (scope || form).querySelectorAll('[required]');

			for (var i = 0; i < fields.length; i++) {
				if (!fields[i].value.trim()) {
					return {
						step: stepOf(fields[i]),
						message: cfg.i18n.requiredField.replace('%s', fieldLabel(fields[i]))
					};
				}
			}

			return null;
		}

		/* ── Step navigation ───────────────────────────────────────────── */

		function onStepClick(event) {
			var next = closestMatch(event.target, '.wellness-btn-next[data-next]');

			if (next) {
				event.preventDefault();

				var problem = invalid(steps[current - 1]);

				if (problem) {
					show(err, problem.message);
					return;
				}

				goTo(parseInt(next.getAttribute('data-next'), 10));
				return;
			}

			var back = closestMatch(event.target, '.wellness-btn-back[data-back]');

			if (back) {
				event.preventDefault();
				goTo(parseInt(back.getAttribute('data-back'), 10));
			}
		}

		function onStepKeydown(event) {
			// Enter advances within the wizard; on the last step it submits.
			if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') {
				return;
			}

			var step = steps[current - 1];
			var next = step ? step.querySelector('.wellness-btn-next[data-next]') : null;

			if (next) {
				event.preventDefault();
				next.click();
			}
		}

		/* ── Submission ────────────────────────────────────────────────── */

		/**
		 * Hand the submission to the server as a plain form POST. The form's
		 * own action is the order-received URL, which is handled natively.
		 */
		function nativeSubmit() {
			busy(false);
			form.removeEventListener('submit', onSubmit);
			form.submit();
		}

		function done(message) {
			form.hidden = true;
			hide(progress);
			show(ok, message || cfg.i18n.success);
		}

		function onSubmit(event) {
			event.preventDefault();

			if (sending) {
				return;
			}

			hide(err);

			var problem = invalid();

			if (problem) {
				if (problem.step && problem.step !== current) {
					goTo(problem.step, true);
				}

				show(err, problem.message);
				return;
			}

			var data = new FormData(form);
			data.set('action', cfg.action);

			busy(true);

			fetch(cfg.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: data
			})
				.then(function (response) {
					// Read as text: an error page, a firewall block or a
					// wp_die() body is not JSON and must not throw here.
					return response.text();
				})
				.then(function (raw) {
					var res = null;

					try {
						res = JSON.parse(raw);
					} catch (parseError) {
						res = null;
					}

					if (!res) {
						nativeSubmit();
						return;
					}

					if (res.success) {
						done(res.data && res.data.message);
						return;
					}

					busy(false);
					show(err, (res.data && res.data.message) || cfg.i18n.error);
				})
				.catch(nativeSubmit);
		}

		/* ── Wire up ───────────────────────────────────────────────────── */

		form.addEventListener('click', onStepClick);
		form.addEventListener('keydown', onStepKeydown);

		if (steps.length && section) {
			// Enhanced mode: collapse the steps into a wizard. Without this
			// class every step stays visible and the form submits in one page.
			section.classList.add('wellness-intake-th--js');
			goTo(1, true);
		}

		form.addEventListener('submit', onSubmit);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
