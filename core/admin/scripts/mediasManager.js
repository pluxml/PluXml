var mediasManager = {

	addText: function(name, txt, replace) {
		var txt = txt.replace(this.opts.racine, '');
		var cible = window.opener.document.getElementById('id_' + name);
		if (cible) {
			cible.focus();
			if (replace) {
				cible.value = txt;
			}
			else {
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
		}
		else {
			console.log('Element #' + cibleId + ' introuvable - ' + txt);
		}
		return false;
	},

	updImg: function(name, imgPath) {
		var id = window.opener.document.getElementById(name + '-wrapper');
		if (id) {
			id.innerHTML = '<img src="' + imgPath + '" />';
		}
	},

	construct: function(options) {

		this.opts = options;

		if (window.name == this.opts.windowName) {

			// ajout d'une class à <body> pour masquer les parties inutiles du gestionnaire de médias
			document.body.classList.add('mediasManager');

			// ajout des évenements onclick pour récuper le lien de l'image
			const tbody = document.querySelector('#medias-table tbody');
			if (tbody) {
				tbody.addEventListener('click', function(event) {
					var target = event.target;
					if (target.tagName == 'A') {
						event.preventDefault();
						var launcher = window.opener.mediasManager;
						var replace = launcher.replace;
						var cible = launcher.cible;
						var fallback = launcher.fallback;
						var fn = window[fallback];
						if (typeof fn === "function") {
							var fnparams = [cibleId, target.href, replace];
							fn.apply(null, fnparams);
						}
						else {
							mediasManager.addText(cible, target.href, replace);
							mediasManager.updImg(cible, target.href);
						}
						window.close();
						cibleId.focus();
					}
				});
			}
		} else {
			console.log('Window name: ' + window.name);
		}
	},

	openPopup: function(name, replace, fallback) {
		this.replace = (typeof replace == 'boolean') ? replace : true;
		var width = this.opts.width ? this.opts.width : 1024,
			left = 0;
		if(width > screen.width) {
			width = screen.width;
		} else {
			left = parseInt((screen.width - width) / 2)
		}
		var height = this.opts.height ? this.opts.height : 580,
			top = 0;
		if(height > screen.height) {
			height = screen.height;
		} else {
			top = parseInt((screen.height - height) / 2);
		}
		const options = 'directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width=' + width + ' , height=' + height + ', left=' + left + ', top=' + top;
		this.cible = name;
		this.replace = replace;
		this.fallback = fallback;
		popup = window.open(unescape(this.opts.urlManager), this.opts.windowName, options);
		if (popup) {
			popup.focus();
		}
		else {
			alert('Ouverture de la fenêtre bloquée par un anti-popup!');
		}
		return false;
	}
}

if('medias_path' in document.body.dataset) {
	mediasManager.construct({
		windowName : document.body.dataset.medias_title,
		racine:	document.body.dataset.root,
		urlManager: document.body.dataset.medias_path
	});
}
