<!--
var mediasManager  =  {

	construct: function(opts) {
		this.width = opts.width ? opts.width : 800;
		this.height = opts.height ? opts.height : 580;
		this.windowName = opts.windowName;
		this.racine = opts.racine;
		this.root = opts.root;
		this.urlManager = opts.urlManager;
		this.replace = true;
		this.init();
	},
	openPopup: function(cibleId, replace, fallback) {
		this.cibleId = cibleId;
		this.replace = replace==undefined ? this.replace : replace;
		this.fallback = fallback==undefined ? '' : fallback;
		var left = (screen.width - this.width) / 2;
		var top = (screen.height - this.height) / 2;
		var options = 'directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width='+this.width+' , height='+this.height+', left='+left+', top='+top;
		popup = window.open(unescape(this.urlManager), this.windowName, options);
		if(popup) {
			popup.focus();
		} else {
			alert('Ouverture de la fenêtre bloquée par un anti-popup!');
		}
		return false;
	},

	init: function() {
		if(window.name == this.windowName) {
			this.css();
			var replace = window.opener.mediasManager.replace;
			var racine = window.opener.mediasManager.racine;
			var root = window.opener.mediasManager.root;
			var cibleId = window.opener.mediasManager.cibleId;
			var fallback = window.opener.mediasManager.fallback;
			var anchors = document.querySelectorAll('#medias-table tbody td:nth-of-type(3) a');
			for (var i=0, j=anchors.length; i<j; i++) {
				var anchor = anchors.item(i);
				var imgUrl = anchor.href.replace(racine, '');
				if(fallback=='') {
					anchor.setAttribute("onclick", "mediasManager.addText('"+cibleId+"', '"+imgUrl+"', "+replace+");mediasManager.updImg('"+cibleId+"_img', '"+root+imgUrl+"');return false;");
				} else {
					anchor.setAttribute("onclick", fallback+'("'+cibleId+'", "'+root+'", "'+imgUrl+'", "'+replace+'")');
				}
			}
		}
	},

	css: function() {
		var style = document.createElement('style');
		style.type = 'text/css';
		style.innerHTML = '\
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
		document.getElementsByTagName('head')[0].appendChild(style);
	},

	addText: function(cibleId, txt, replace) {

		var cible = window.opener.document.getElementById(cibleId);
		if(cible) {
			cible.focus();
			if(replace) {
				cible.value = txt;
			} else {
				if(document.selection && document.selection.createRange) {
					sel = document.selection.createRange();
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
			console.log('Element #'+cibleId+' introuvable');
		}
		close();
		return false;
	},

	updImg: function(cibleId, imgPath) {
		window.opener.document.getElementById(cibleId).innerHTML = '<img src="'+imgPath+'" alt="" />';
	}

};
-->