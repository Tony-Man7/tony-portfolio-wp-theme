document.addEventListener('DOMContentLoaded', function () {
	var toggle = document.querySelector('.tdp-nav-toggle');
	var links = document.querySelector('.tdp-nav-links');

	if (toggle && links) {
		toggle.addEventListener('click', function () {
			links.classList.toggle('is-open');
		});

		links.querySelectorAll('a').forEach(function (a) {
			a.addEventListener('click', function () {
				links.classList.remove('is-open');
			});
		});
	}

	var prefersReducedMotion = window.matchMedia &&
		window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function typeText(el, speed) {
		if (el.dataset.typed === 'true') return;
		el.dataset.typed = 'true';

		var text = el.textContent;

		if (prefersReducedMotion) {
			el.textContent = text;
			return;
		}

		el.textContent = '';
		var i = 0;
		(function step() {
			el.textContent = text.slice(0, i);
			i++;
			if (i <= text.length) {
				setTimeout(step, speed);
			}
		})();
	}

	function typeHTML(el, speed, callback) {
		if (el.dataset.typed === 'true') { if (callback) callback(); return; }
		el.dataset.typed = 'true';

		if (prefersReducedMotion) { if (callback) callback(); return; }

		var original = el.cloneNode(true);
		var originalTextNodes = [];
		(function collect(node, list) {
			node.childNodes.forEach(function (child) {
				if (child.nodeType === Node.TEXT_NODE) list.push(child);
				else collect(child, list);
			});
		})(original, originalTextNodes);

		var skeleton = original.cloneNode(true);
		var skeletonTextNodes = [];
		(function collect(node, list) {
			node.childNodes.forEach(function (child) {
				if (child.nodeType === Node.TEXT_NODE) { list.push(child); child.textContent = ''; }
				else collect(child, list);
			});
		})(skeleton, skeletonTextNodes);

		while (el.firstChild) el.removeChild(el.firstChild);
		Array.from(skeleton.childNodes).forEach(function (n) { el.appendChild(n); });

		var flatChars = [];
		originalTextNodes.forEach(function (tn, idx) {
			for (var i = 0; i < tn.textContent.length; i++) {
				flatChars.push({ idx: idx, char: tn.textContent[i] });
			}
		});

		var pos = 0;
		(function step() {
			if (pos >= flatChars.length) { if (callback) callback(); return; }
			var item = flatChars[pos];
			skeletonTextNodes[item.idx].textContent += item.char;
			pos++;
			setTimeout(step, speed);
		})();
	}

	function animateCounter(el) {
		if (el.dataset.counted === 'true') return;
		el.dataset.counted = 'true';

		var target, suffix;

		if (el.dataset.countTarget) {
			target = parseInt(el.dataset.countTarget, 10);
			suffix = el.dataset.countSuffix || '';
		} else {
			var text = el.textContent.trim();
			var match = text.match(/^(\d+)(.*)$/);
			if (!match) return;
			target = parseInt(match[1], 10);
			suffix = match[2];
		}

		if (prefersReducedMotion) {
			el.textContent = target + suffix;
			return;
		}

		el.textContent = '0' + suffix;

		var duration = 900;
		var startTime = null;

		function step(ts) {
			if (!startTime) startTime = ts;
			var progress = Math.min((ts - startTime) / duration, 1);
			el.textContent = Math.floor(progress * target) + suffix;
			if (progress < 1) requestAnimationFrame(step);
			else el.textContent = target + suffix;
		}
		requestAnimationFrame(step);
	}

	var heroEyebrow = document.querySelector('#hero .tdp-eyebrow');
	var heroHeading = document.querySelector('#hero h1');
	var heroRole = document.querySelector('#hero .tdp-hero-role');
	var heroStats = document.querySelectorAll('.tdp-hero-meta strong');

	if (heroEyebrow) typeText(heroEyebrow, 70);

	if (heroRole) {
		typeHTML(heroRole, 12, function () {
			heroStats.forEach(function (el) { animateCounter(el); });
		});
	} else {
		heroStats.forEach(function (el) { animateCounter(el); });
	}

	/* ---------------------------------------------------------------
	 * Scroll reveal (repeats every time an element enters/leaves the
	 * viewport) + terminal-style typing for the other eyebrow labels.
	 * ------------------------------------------------------------- */
	function setupScrollEffects(scope) {
		var eyebrowEls = scope.querySelectorAll('.tdp-eyebrow');

		function applyStagger(list, step) {
			list.forEach(function (el, index) {
				el.classList.add('tdp-reveal');
				el.style.setProperty('--tdp-reveal-delay', (index % 6) * step + 's');
			});
			return Array.from(list);
		}

		var revealEls = []
			.concat(applyStagger(scope.querySelectorAll('.tdp-card'), 0.15))
			.concat(applyStagger(scope.querySelectorAll('.tdp-project-card'), 0.15))
			.concat(applyStagger(scope.querySelectorAll('.tdp-timeline-item'), 0.08));

		if (!('IntersectionObserver' in window)) {
			revealEls.forEach(function (el) { el.classList.add('is-visible'); });
			eyebrowEls.forEach(function (el) { if (el !== heroEyebrow) typeText(el, 70); });
			return;
		}

		var revealObserver = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					revealObserver.unobserve(entry.target);
				}
			});
		}, { threshold: 0.15 });
		revealEls.forEach(function (el) { revealObserver.observe(el); });

		var eyebrowObserver = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting && entry.target !== heroEyebrow) {
					typeText(entry.target, 70);
					eyebrowObserver.unobserve(entry.target);
				}
			});
		}, { threshold: 0.5 });
		eyebrowEls.forEach(function (el) {
			if (el !== heroEyebrow) eyebrowObserver.observe(el);
		});
	}

	function setGridRowSpans(grid) {
		if (!grid || !grid.classList.contains('tdp-work-grid')) {
			grid = grid.querySelector ? grid.querySelector('.tdp-work-grid') : null;
		}
		if (!grid) return;

		var styles = window.getComputedStyle(grid);
		var rowHeight = parseInt(styles.getPropertyValue('grid-auto-rows'), 10) || 8;
		var rowGap = parseInt(styles.getPropertyValue('row-gap') || styles.getPropertyValue('gap'), 10) || 20;

		var cards = grid.querySelectorAll('.tdp-project-card');
		cards.forEach(function (card) {
			var contentHeight = card.getBoundingClientRect().height;
			var span = Math.ceil((contentHeight + rowGap) / (rowHeight + rowGap));
			card.style.gridRowEnd = 'span ' + span;
		});
	}

	function initMasonryGrid() {
		var grid = document.getElementById('tdp-work-grid');
		if (!grid) return;

		setGridRowSpans(grid);

		// Recompute after web fonts finish loading — text height can shift.
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(function () { setGridRowSpans(grid); });
		}

		// Recompute on resize — column width changes affect text wrapping/height.
		var resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () { setGridRowSpans(grid); }, 150);
		});
	}
	
	initMasonryGrid();
	setupScrollEffects(document);

	/* ---------------------------------------------------------------
	 * "See more projects" — AJAX load, no page reload.
	 * ------------------------------------------------------------- */
	var loadMoreBtn = document.getElementById('tdp-load-more');
	var grid = document.getElementById('tdp-work-grid');

	if (loadMoreBtn && grid && typeof tdpAjax !== 'undefined') {
		loadMoreBtn.addEventListener('click', function () {
			var page = parseInt(loadMoreBtn.dataset.page, 10) || 2;

			loadMoreBtn.disabled = true;
			loadMoreBtn.textContent = 'Loading...';

			var body = new URLSearchParams();
			body.append('action', 'tdp_load_more_projects');
			body.append('nonce', tdpAjax.nonce);
			body.append('page', page);

			fetch(tdpAjax.url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			})
				.then(function (res) { return res.json(); })
				.then(function (res) {
					if (res.success) {
						grid.insertAdjacentHTML('beforeend', res.data.html);
						setupScrollEffects(grid);
						setGridRowSpans(grid);

						if (res.data.has_more) {
							loadMoreBtn.dataset.page = page + 1;
							loadMoreBtn.disabled = false;
							loadMoreBtn.textContent = 'See more projects';
						} else {
							loadMoreBtn.remove();
						}
					} else {
						loadMoreBtn.textContent = 'Something went wrong';
					}
				})
				.catch(function () {
					loadMoreBtn.textContent = 'Something went wrong';
					loadMoreBtn.disabled = false;
				});
		});
	}

/* ---------------------------------------------------------------
 * AI chat widget — toggle, tabs, and both chat + job-match modes.
 * ------------------------------------------------------------- */
function initAiWidget() {
	var toggle   = document.getElementById('tdp-ai-toggle');
	var panel    = document.getElementById('tdp-ai-panel');
	var closeBtn = document.getElementById('tdp-ai-close');
	var tabs     = document.querySelectorAll('.tdp-ai-tab');
	var viewAsk  = document.getElementById('tdp-ai-view-ask');
	var viewJob  = document.getElementById('tdp-ai-view-jobmatch');

	if (!toggle || !panel || typeof tdpAi === 'undefined') return;

	toggle.addEventListener('click', function () {
		panel.hidden = !panel.hidden;
		toggle.classList.remove('tdp-ai-toggle--pulse');
	});

	// Auto-open once per browser session so first-time visitors notice it
	// right away, without annoying repeat visitors on every page load.
	var hasAutoOpened = sessionStorage.getItem('tdpAiAutoOpened');
	if (!hasAutoOpened) {
		setTimeout(function () {
			panel.hidden = false;
			sessionStorage.setItem('tdpAiAutoOpened', 'true');
		}, 1500);
	} else {
		toggle.classList.add('tdp-ai-toggle--pulse');
	}
	closeBtn.addEventListener('click', function () {
		panel.hidden = true;
	});

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			tabs.forEach(function (t) { t.classList.remove('is-active'); });
			tab.classList.add('is-active');

			var mode = tab.dataset.mode;
			viewAsk.hidden = mode !== 'ask';
			viewJob.hidden = mode !== 'job-match';
		});
	});

	/* ---- Chat mode ---- */
	var thread = document.getElementById('tdp-ai-thread');
	var form   = document.getElementById('tdp-ai-form');
	var input  = document.getElementById('tdp-ai-input');
	var sendBtn = form.querySelector('.tdp-ai-send');

	// Escapes HTML first (so raw AI text can never inject real tags),
	// then converts a small safe subset of markdown: **bold** and
	// [label](https://url) links. Only http(s) links are allowed.
	function renderMarkdownLite(text) {
		var escaped = text
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');

		escaped = escaped.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, function (match, label, url) {
			return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + label + '</a>';
		});

		escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

		return escaped;
	}

	function appendMessage(text, type) {
		var el = document.createElement('div');
		el.className = 'tdp-ai-message tdp-ai-message--' + type;
		if (type === 'bot') {
			el.innerHTML = renderMarkdownLite(text);
		} else {
			el.textContent = text;
		}
		thread.appendChild(el);
		thread.scrollTop = thread.scrollHeight;
		return el;
	}

	function appendActions(actions) {
		var row = document.createElement('div');
		row.className = 'tdp-ai-actions';

		actions.forEach(function (action) {
			var btn = document.createElement('a');
			btn.className = 'tdp-ai-action-btn';
			btn.href = action.url;
			btn.target = '_blank';
			btn.rel = 'noopener noreferrer';
			btn.textContent = action.label;
			row.appendChild(btn);
		});

		thread.appendChild(row);
		thread.scrollTop = thread.scrollHeight;
	}

	function showTyping() {
		var el = document.createElement('div');
		el.className = 'tdp-ai-typing';
		el.id = 'tdp-ai-typing-indicator';
		el.innerHTML = '<span></span><span></span><span></span>';
		thread.appendChild(el);
		thread.scrollTop = thread.scrollHeight;
	}

	function hideTyping() {
		var el = document.getElementById('tdp-ai-typing-indicator');
		if (el) el.remove();
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var question = input.value.trim();
		if (!question) return;

		appendMessage(question, 'user');
		input.value = '';
		input.disabled = true;
		sendBtn.disabled = true;
		showTyping();

		fetch(tdpAi.restUrl + 'ask', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ question: question })
		})
			.then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
			.then(function (result) {
				hideTyping();
				if (result.ok && result.data.answer) {
					appendMessage(result.data.answer, 'bot');
					if (result.data.actions && result.data.actions.length) {
						appendActions(result.data.actions);
					}
				} else {
					appendMessage(result.data.message || 'Something went wrong. Please try again.', 'error');
				}
			})
			.catch(function () {
				hideTyping();
				appendMessage('Could not reach the AI assistant. Please check your connection.', 'error');
			})
			.finally(function () {
				input.disabled = false;
				sendBtn.disabled = false;
				input.focus();
			});
	});

	/* ---- Job-match mode ---- */
	var jobInput  = document.getElementById('tdp-ai-job-input');
	var jobSubmit = document.getElementById('tdp-ai-job-submit');
	var jobResult = document.getElementById('tdp-ai-job-result');

	jobSubmit.addEventListener('click', function () {
		var jobDescription = jobInput.value.trim();
		if (!jobDescription) {
			jobResult.textContent = 'Please paste a job description first.';
			jobResult.classList.add('is-error');
			return;
		}

		jobSubmit.disabled = true;
		jobSubmit.textContent = 'Analyzing...';
		jobResult.textContent = '';
		jobResult.classList.remove('is-error');

		fetch(tdpAi.restUrl + 'job-match', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ job_description: jobDescription })
		})
			.then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
			.then(function (result) {
				if (result.ok && result.data.summary) {
					jobResult.innerHTML = renderMarkdownLite(result.data.summary);
				} else {
					jobResult.textContent = result.data.message || 'Something went wrong. Please try again.';
					jobResult.classList.add('is-error');
				}
			})
			.catch(function () {
				jobResult.textContent = 'Could not reach the AI assistant. Please check your connection.';
				jobResult.classList.add('is-error');
			})
			.finally(function () {
				jobSubmit.disabled = false;
				jobSubmit.textContent = 'Check fit';
			});
	});
}

initAiWidget();
});
