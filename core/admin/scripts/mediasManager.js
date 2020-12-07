(function() {
	const WINDOW_NAME = 'MEDIAS-MANAGER';
	const MEDIAS_SCRIPT_URI = 'medias.php';

	var errorMsg = 'Ouverture de la fenêtre bloquée par un anti-popup!';

	function addText(name, uri, replace) {
		const cible = window.opener.document.getElementById('id_' + name);
		if (cible) {
			const txt = uri.replace(new RegExp('^' + document.body.dataset.root), '');
			cible.focus();
			if (replace) {
				cible.value = txt;
			} else {
				if (window.opener.document.selection && window.opener.document.selection.createRange) {
					sel = window.opener.document.selection.createRange();
					sel.text = sel.text + txt;
				}
				// Moz support
				else if (cible.selectionStart || cible.selectionStart == '0') {
					var startPos = cible.selectionStart;
					var endPos = cible.selectionEnd;
					var restoreTop = cible.scrollTop;
					cible.value = cible.value.substring(0, startPos) + txt + cible.value.substring(startPos, endPos) + cible.value.substring(endPos, cible.value.length);
					cible.selectionStart = cible.selectionEnd = endPos + txt.length;
					if (restoreTop > 0) cible.scrollTop = restoreTop;
				}
				// Fallback support for other browsers
				else {
					cible.value += txt;
				}
			}
			cible.focus();
		} else {
			console.error('Element #' + cible + ' not found - ' + txt);
		}
		return false;
	}

	function updImg(name, imgPath) {
		var id = window.opener.document.getElementById(name + '-wrapper');
		if (id) {
			id.innerHTML = '<img src="' + imgPath + '" />';
		}
	}

	function openPopup(name, replace, fallback) {
		this.replace = (typeof replace == 'boolean') ? replace : true;
		var width = 1024,
			left = 0;
		if(width > screen.width) {
			width = screen.width;
		} else {
			left = parseInt((screen.width - width) / 2)
		}
		var height = 580,
			top = 0;
		if(height > screen.height) {
			height = screen.height;
		} else {
			top = parseInt((screen.height - height) / 2);
		}
		const options = 'directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width=' + width + ' , height=' + height + ', left=' + left + ', top=' + top;
		popup = window.open(MEDIAS_SCRIPT_URI, WINDOW_NAME, options);
		if (popup) {
			window.cible = name;
			window.replace = replace;
			window.fallback = fallback;
			popup.focus();
		}
		else {
			alert(errorMsg);
		}
		return false;
	}

	if(document.location.pathname.endsWith('medias.php')) {
		if (window.name == WINDOW_NAME) {

			if('root' in document.body.dataset) {
				// ajout d'une class à <body> pour masquer les parties inutiles du gestionnaire de médias
				document.body.classList.add('mediasManager');

				// ajout des évenements onclick pour récuper le lien de l'image
				const tbody = document.querySelector('#medias-table tbody');
				if (tbody) {
					tbody.addEventListener('click', function(event) {
						const target = event.target;
						if (target.tagName == 'A') {
							event.preventDefault();
							const replace = (typeof window.opener.replace == 'boolean') ? window.opener.replace : true;
							const cible = window.opener.cible;
							addText(cible, target.href, replace);
							updImg(cible, target.href);
							window.close();
							cible.focus();
						}
					});
				}

				if('errormsg' in document.body.dataset) {
					errorMsg = document.body.dataset.errormsg;
				}
			} else {
				console.error('Root of this site is missing');
			}
		}
	} else {
		const button = document.querySelector('i[data-preview].icon-picture');
		if(button != null) {
			button.onclick = function(event) {
				event.preventDefault();
				openPopup(button.dataset.preview);
			}
		}
	}
})();
