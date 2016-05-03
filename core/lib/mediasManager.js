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

var mediasManager = {

	addText: function(cibleId, txt, replace) {
		var txt = txt.replace(this.opts.racine, '');
		var cible = window.opener.document.getElementById(cibleId);
		if(cible) {
			cible.focus();
			if(replace) {
				cible.value = txt;
			} else {
				if(window.opener.document.selection && window.opener.document.selection.createRange) {
					sel = window.opener.document.selection.createRange();
					sel.text = sel.text + txt;
				}
				// Moz support
				else if(cible.selectionStart || cible.selectionStart == '0') {
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
			console.log('Element #'+cibleId+' introuvable - ' + txt);
		}
		return false;
	},

	updImg: function(cibleId, imgPath) {
		var id = window.opener.document.getElementById(cibleId);
		if(id) {
			id.innerHTML = '<img src="'+imgPath+'" alt="" />';
		}
	},

	construct: function(options) {

		this.opts = options;

		if(window.name == this.opts.windowName) {

			// ajout des règles CSS pour masquer les parties inutiles du gestionnaire de médias
			var textNode = document.createTextNode(popupCss);
			var style = document.createElement('style');
			style.setAttribute('type', 'text/css');
			style.appendChild(textNode);
			document.getElementsByTagName('head')[0].appendChild(style);

			// ajout des évenements onclick pour récuper le lien de l'image
			var tbody = document.querySelector('#medias-table tbody');
			if (tbody) {
				tbody.addEventListener('click', function (event) {
					var target = event.target;
					if (target.tagName == 'A') {
						event.preventDefault();
						var	launcher = window.opener.mediasManager;
						var replace = launcher.replace;
						var cibleId = launcher.cibleId;
						var fallback = launcher.fallback;
						var fn = window[fallback];
						window.close();
						if (typeof fn === "function") {
							var fnparams = [cibleId, target.href, replace];
							fn.apply(null, fnparams);
						} else {
							mediasManager.addText(cibleId, target.href, replace);
							mediasManager.updImg(cibleId+'_img', target.href);
						}
						cibleId.focus();
					}
				});
			}
		}
	},

	openPopup: function(cibleId, replace, fallback) {
		var replace = replace==undefined ? false : true;
		var width = this.opts.width ? this.opts.width : 950;
		var height = this.opts.height ? this.opts.height : 580;
		var left = parseInt((screen.width - width) / 2);
		var top = parseInt((screen.height - height) / 2);
		var options = 'directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width='+width+' , height='+height+', left='+left+', top='+top;
		this.cibleId=cibleId;
		this.replace=replace;
		this.fallback=fallback;
		popup = window.open(unescape(this.opts.racine + this.opts.urlManager), this.opts.windowName, options);
		if(popup) {
			popup.focus();
		} else {
			alert('Ouverture de la fenêtre bloquée par un anti-popup!');
		}
		return false;
	}

}