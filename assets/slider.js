(function () {
	'use strict';

	function clamp(n, min, max) {
		return Math.max(min, Math.min(max, n));
	}

	function initSlider(root) {
		var track = root.querySelector('.bp-tslider__track');
		var slides = Array.prototype.slice.call(root.querySelectorAll('.bp-tslider__slide'));
		var prevBtn = root.querySelector('.bp-tslider__btn--prev');
		var nextBtn = root.querySelector('.bp-tslider__btn--next');
		var dotsWrap = root.querySelector('.bp-tslider__dots');

		if (!track || slides.length < 2) {
			// Still build dots if 1 slide? Not needed.
			return;
		}

		var autoplay = parseInt(root.getAttribute('data-autoplay'), 10);
		if (!autoplay || autoplay < 1500) autoplay = 5000;

		var index = 0;
		var timer = null;
		var isPaused = false;

		function renderDots() {
			dotsWrap.innerHTML = '';
			for (var i = 0; i < slides.length; i++) {
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'bp-tslider__dot';
				b.setAttribute('aria-label', 'Ir al testimonio ' + (i + 1));
				b.setAttribute('aria-current', (i === index) ? 'true' : 'false');
				(function (n) {
					b.addEventListener('click', function () {
						goTo(n, true);
					});
				})(i);
				dotsWrap.appendChild(b);
			}
		}

		function update() {
			track.style.transform = 'translateX(' + (-index * 100) + '%)';
			var dots = dotsWrap.querySelectorAll('.bp-tslider__dot');
			for (var i = 0; i < dots.length; i++) {
				dots[i].setAttribute('aria-current', (i === index) ? 'true' : 'false');
			}
		}

		function goTo(n, userAction) {
			index = (n + slides.length) % slides.length;
			update();
			if (userAction) restart();
		}

		function next(userAction) {
			goTo(index + 1, userAction);
		}

		function prev(userAction) {
			goTo(index - 1, userAction);
		}

		function stop() {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		}

		function start() {
			stop();
			timer = setInterval(function () {
				if (!isPaused) next(false);
			}, autoplay);
		}

		function restart() {
			start();
		}

		// Controls.
		if (nextBtn) nextBtn.addEventListener('click', function () { next(true); });
		if (prevBtn) prevBtn.addEventListener('click', function () { prev(true); });

		// Pause on hover / focus within.
		root.addEventListener('mouseenter', function () { isPaused = true; });
		root.addEventListener('mouseleave', function () { isPaused = false; });

		root.addEventListener('focusin', function () { isPaused = true; });
		root.addEventListener('focusout', function () { isPaused = false; });

		// Keyboard support (left/right arrows) when focused.
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') {
				e.preventDefault();
				next(true);
			}
			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				prev(true);
			}
		});

		renderDots();
		update();
		start();
	}

	function boot() {
		var sliders = document.querySelectorAll('.bp-tslider');
		for (var i = 0; i < sliders.length; i++) {
			initSlider(sliders[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
