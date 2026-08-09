/*
var popupCss = '\
.aside {\
	display: none;\
}\
.section .action-bar {\
	left: 0;\
	right: 0;\
	margin-right: 0;\
	margin-left: 0;\
}\
@media (min-width: 768px) {\
	.col.lrg-offset-2 {\
		margin-left: 10px;\
		margin-right: 20px;\
		width: 100%;\
	}\
}';
*/

'uses strict';

var mediasManager = {

	addText: function(cibleId, txt, replace) {
		txt = txt.replace(this.opts.racine, '');
		const cible = window.opener.document.getElementById(cibleId);
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
					const startPos = cible.selectionStart;
					const endPos = cible.selectionEnd;
					const restoreTop = cible.scrollTop;
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
			console.error('Element #' + cibleId + ' introuvable - ' + txt);
		}
		return false;
	},

	updImg: function(cibleId, imgPath) {
		const id = window.opener.document.getElementById(cibleId);
		if (id) {
			id.innerHTML = '<img src="' + imgPath + '" alt="" />';
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
						const launcher = window.opener.mediasManager;
						const replace = launcher.replace;
						const cibleId = launcher.cibleId;
						const fallback = launcher.fallback;
						const fn = window[fallback];
						if (typeof fn === "function") {
							const fnparams = [cibleId, target.href, replace];
							fn.apply(null, fnparams);
						}
						else {
							mediasManager.addText(cibleId, target.href, replace);
							mediasManager.updImg(cibleId + '_img', target.href);
						}
						window.close();
						cibleId.focus();
					}
				});
			}
		}
	},

	openPopup: function(cibleId, replace, fallback) {
		const width = this.opts.width ? this.opts.width : 950;
		var height = this.opts.height ? this.opts.height : 580;
		const left = parseInt((screen.width - width) / 2);
		const top = parseInt((screen.height - height) / 2);
		const options = 'directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width=' + width + ' , height=' + height + ', left=' + left + ', top=' + top;
		this.cibleId = cibleId;
		this.replace = (typeof replace == 'boolean') ? replace : false;
		this.fallback = fallback;
		popup = window.open(unescape(this.opts.urlManager), this.opts.windowName, options);
		if (popup) {
			popup.focus();
		} else {
			alert('Popup window is disabled');
		}
		return false;
	}

}
