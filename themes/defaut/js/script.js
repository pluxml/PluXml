(function() {
	'use strict';

	// -------------- scroll to top smoothly -------------------
	const topBtn = document.body.querySelector('.footer a[href$="#top"]');
	if(topBtn != null) {
		topBtn.onclick = function(e) {
			const top = document.getElementById('top');
			if(top != null) {
				e.preventDefault();
				top.scrollIntoView({ behavior: 'smooth' });
			}
		}
	} else {
		console.error('The top button is missing.');
	}

	// --------- Footnotes -----------

	const article = document.querySelector('.article[id^="post-"]');
	if(article != null) {
		const footnotes = article.querySelectorAll('a[data-footnote]');
		if(footnotes.length > 0) {
			const el = document.createElement('UL');
			el.className = 'footnotes';
			Array.from(footnotes).forEach(function(item, x) {
				const i = x+1;
				const idSrc = `footnote-${i}`;
				const idTarget = `note-${i}`;
				item.innerHTML = `<sup>(${i})</sup>`;
				item.id = idSrc
				item.href = `#${idTarget}`;
				const note = document.createElement('LI');
				note.innerHTML = `<a href="#${idSrc}">${i}. </a>${item.dataset.footnote}`;
				note.id = idTarget;
				el.appendChild(note);
				item.title = note.textContent.replace(/^\d+\./, '');
			});

			article.appendChild(el);
		}
	}

	// ------- chapters ----------

	const newPages = [...document.body.querySelectorAll('.new-page > h2')];
	if(newPages.length != 0) {
		if(newPages.length > 1) {
			// On crée une barre de navigation s'il y a plus de 1 chapitre
			var innerHTML = '';
			newPages.forEach((item, i) => {
			  const caption = item.textContent;
			  innerHTML += `<button data-page="${i}">${caption}</button>`;
			});

			// On crée la barre de navigation
			const pagination_numbers_container = document.createElement('NAV');
			pagination_numbers_container.className = 'art-nav center';
			pagination_numbers_container.innerHTML = innerHTML;
			const page0 = newPages[0].parentElement;
			page0.parentElement.insertBefore(pagination_numbers_container, page0);

			// On gére le click sur la barre de navigation
			pagination_numbers_container.addEventListener('click', (evt) => {
			  if(evt.target.hasAttribute('data-page')) {
				evt.preventDefault();
				// On affiche uniquement le chapitre demandé
				[...document.body.querySelectorAll('.new-page.active')].forEach((item) => {
				  item.classList.remove('active');
				});
				const i = parseInt(evt.target.dataset.page);
				newPages[i].parentElement.classList.add('active');
				// On met en évidence uniquement le bouton du chapitre affiché
				[...pagination_numbers_container.querySelectorAll('.active')].forEach((item) => {
				  item.classList.remove('active');
				});
				event.target.classList.add('active');
			  }
			});
		}

		// On allume sur le premier .new-page ( Fire up )
		newPages[0].parentElement.classList.add('active');
		const btn = document.body.querySelector('.art-nav button');
		if(btn != null) {
		btn.classList.add('active');
		}
	}

	// --------- slideshow ------------

	const slideshow = document.getElementById('slideshow');
	if(slideshow != null) {
		const TAG = 'slideshow-thumb';
		var range = 0;
		var position = 0;
		var sliding = null;
		var leaving = null;

		var computed;
		const tempImg = new Image();
		// tempImg.onloadend ne fonctionne qu'avec Firefox
		tempImg.onload = function(event) {
			var w, h;
			const maxWidth = parseInt(computed.maxWidth);
			const maxHeight = parseInt(computed.maxHeight);
			const ratio = tempImg.width / tempImg.height;
			if(ratio > maxWidth / maxHeight) {
				// priorité à width
				w = (tempImg.width < maxWidth) ? tempImg.width : maxWidth;
				h = parseInt(w / ratio);
			} else {
				// priorité à height
				h = (tempImg.height < maxHeight) ? tempImg.height : maxHeight;
				w = parseInt(h * ratio);
			}
			// Si les dimensions des images sont identiques : pas de transition sur les dimensions !
			// console.log('Image size : ', w, 'x', h);
			slideshowImg.style.width = (slideshowImg.width != w) ? w + 'px' : '';
			slideshowImg.style.height = (slideshowImg.height != h) ? h + 'px' : '';
			slideshowImg.src = tempImg.src;
		}

		function displaySlide(id) {
			if(typeof id == 'number') { position = id; }

			if(position < 0) {
				position = range - 1;
			} else if(position >= range) {
				position = 0;
			}
			const thumbnail = document.getElementById(TAG + '-'  + position)
			thumbnail.click();
			thumbnail.scrollIntoView({behavior: 'smooth'});
		}

		const slideshowImg = document.getElementById('slideshow-img');
		const caption = document.getElementById('slideshow-caption');
		const counter = document.getElementById('slideshow-counter');

		function slideshowImgShow(src) {
			computed = getComputedStyle(slideshowImg);
			slideshowImg.style.width = computed.width;
			slideshowImg.style.height = computed.height;
			tempImg.src = src.replace(/\.tb\.(jpe?g|png|gif|webp)$/, ".$1");

			var timer1 = setTimeout(function() {
				clearTimeout(timer1);
			}, 20);
		}

		// gestion du click dans la galerie
		const gallery = document.getElementById('slideshow-gallery');
		if(gallery != null) {
			gallery.onclick = function(event) {
				if(event.target.id.startsWith(TAG)) {
					event.preventDefault();
					const previous = gallery.querySelector('img.active');
					if(previous != null) {
						previous.classList.remove('active');
					}
					caption.textContent = event.target.dataset.title;
					position = parseInt(event.target.id.replace(/.*-(\d+)$/, '$1'));
					counter.textContent = (position + 1) + ' / ' + range;
					event.target.classList.add('active');

					slideshowImgShow(event.target.src);
				}
			}

			const content = document.querySelector('[data-gallery]');
			if(content != null) {
				content.addEventListener('click', function(event) {
					if(range >= 0 && event.target.tagName == 'IMG') {
						if(event.target.src.match(/\.tb\.(?:jpe?g|png|gif|webp)/)) {
							event.preventDefault();
							if(!('thumb' in event.target.dataset)) {
								// Construction de la galerie
								gallery.textContent = '';
								range = 0;
								const imgs = document.querySelectorAll('img[src*=".tb."]');
								for(var i=0, iMax=imgs.length; i<iMax; i++) {
									const el = imgs[i];
									const src = el.src;
									// Pas d'image en double dans la galerie
									const imgsList = [];
									if(/\.(?:jpe?g|png|gif|webp)/.test(src) && imgsList.indexOf(src) < 0) {
										const img = document.createElement('IMG');
										img.src = src;
										img.alt = el.alt;
										img.id = TAG + '-' + range;
										var title = el.title.trim();
										if(title.length == 0) {
											title = el.parentElement.title.trim();
											if(title.length == 0) {
												title = el.alt;
											}
										}
										img.setAttribute('data-title', title);
										gallery.appendChild(img);
										el.dataset['thumb'] = i;
										range++;
									}
								}
								if(range == 0) {
									// no picture found
									range = -1;
								}
							}

							if(range > 0) {
								if(range == 1) {
									slideshow.classList.remove('with-gallery');
								} else {
									slideshow.classList.add('with-gallery');
								}
								// We have pictures
								slideshowImg.style.width = event.target.width + 'px';
								slideshowImg.style.height = event.target.height + 'px';
								document.body.classList.add('slideshow');
								displaySlide(parseInt(event.target.dataset.thumb));
							}

							if('ontouchstart' in document.documentElement && !document.documentElement.fullscreenElement && navigator.userAgent.match(/(?:android|iphone|ipad|webos|mobi)/i)) {
								slideshow.requestFullscreen();
							}
						} else {
							if(event.target.src.match(/\.tb\.(?:jpe?g|png|gif|webp)/)) {
								// Pour article.php: zoom pour les vignettes de l'article
								event.preventDefault();
								alert('ok');
							}
						}
					}
				});
			}

			document.getElementById('slideshow-prev').onclick = function(event) {
				event.preventDefault();
				position--;
				displaySlide();
			}

			document.getElementById('slideshow-next').onclick = function(event) {
				event.preventDefault();
				position++;
				displaySlide();
			}
		}

		// Fin de la transition
		slideshowImg.ontransitionend = function(event) {
			if(event.propertyName == 'width' || event.propertyName == 'height') {
				slideshowImg.style.width = '';
				slideshowImg.style.height = '';
				if(leaving === true) {
					document.body.classList.remove('slideshow');
					slideshow.style.opacity = '';
					leaving = null;
				}
				return;
			}
		}

		const closeBtn = document.getElementById('slideshow-close');
		closeBtn.onclick = function(event) {
			event.preventDefault();
			slideshowImg.style.width = slideshowImg.width + 'px';
			slideshow.style.opacity = '1';
			var timer1 = setTimeout(function() {
				leaving = true;
				slideshowImg.style.width = parseInt(slideshowImg.width / 4) + 'px';
				slideshow.style.opacity = '0.05';
				clearTimeout(timer1);
			}, 20);
			if(sliding == null) {
				clearInterval(sliding);
				sliding = null;
			}
		}

		// The keyboard
		window.addEventListener('keydown', function(event) {
			if(
				!event.shiftKey &&
				!event.ctrlKey &&
				!event.altKey &&
				document.body.classList.contains('slideshow') &&
				(range > 1 || event.key.startsWith('Esc'))
			) {
				switch(event.key) {
					case ' ':
					case 'Enter':
					case 'Right':
					case 'ArrowRight':
					case 'n':
					case 'N':
						position++;
						displaySlide();
						break;
					case 'Backspace':
					case 'Left':
					case 'ArrowLeft':
					case 'b':
					case 'B':
						position--;
						displaySlide();
						break;
					case 'Esc':
					case 'Escape':
						closeBtn.click();
						break;
					case 'Home':
						position = 0;
						displaySlide();
						break;
					case 'End':
						position = range - 1;
						displaySlide();
						break;
					case 'p':
					case 'P':
						if(sliding == null) {
							// starting
							position++;
							displaySlide();

							// For next slide
							sliding = setInterval(function() {
								position++;
								displaySlide();
							}, slideshow.dataset.interval);
						} else {
							clearInterval(sliding);
							sliding = null;
						}
						break;
					default: return;
				}
				event.preventDefault();
			}
		});
	} else {
		// console.error('#slideshow element not found');
	}

	/* Hack against PluCss */
	
	if(window.matchMedia('(min-width: 768px)').matches) {
		const articleGridCol = document.querySelector('.grid .col > article');
		if(articleGridCol != null) {
			const aside = document.querySelector('.grid aside.col');
			if(aside != null) {
				articleGridCol.style.setProperty('--height', aside.offsetHeight + 'px');
			}
		}
	}

})();
