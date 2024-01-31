/* Visual Effects */

// Deprecated
function setOpacity(obj, opacity) {
	obj.style.minHeight = obj.style.minHeight; // hack IE
	opacity = (opacity == 100)?99.999:opacity;
	obj.style.filter = "alpha(opacity="+opacity+")"; // IE/Win
	obj.style.KHTMLOpacity = opacity/100; // Safari<1.2, Konqueror
	obj.style.MozOpacity = opacity/100; // Older Mozilla and Firefox
	obj.style.opacity = opacity/100; // Safari 1.2, newer Firefox and Mozilla, CSS3
}

// Deprecated
function fadeOut(objId,opacity) {
	var obj = document.getElementById(objId);
	if(obj) {
		if(opacity==undefined) {
			window.setTimeout("fadeOut('"+objId+"',"+100+")", 3000);
		} else {
			if (opacity >=0) {
				setOpacity(obj, opacity);
				opacity -= 10;
				window.setTimeout("fadeOut('"+objId+"',"+opacity+")", 100);
			} else {
				obj.style.display = 'none';
				obj.style.opacity= '';
			}
		}
	}
}

function setMsg() {
	objDiv = document.getElementById('msg');
	if(objDiv) {
			// fadeOut('msg');
			objDiv.addEventListener('animationend', function(event) {
					event.target.classList.add('hide');
			}, false);
	}
}

function dialogBox(dlg) {
	this.dlg = document.getElementById(dlg);
	this.span = document.querySelector('#'+dlg+' .dialog-close');
	var self = this;
	this.open = function() {
		self.dlg.style.display = "block";
	}
	this.close = function() {
		self.dlg.style.display = "none";
	}
	this.addEvent = function (element, evnt, funct){
		if (element.attachEvent)
			return element.attachEvent('on'+evnt, funct);
		else
		return element.addEventListener(evnt, funct, false);
	}
	this.addEvent(this.span, 'click', this.close);
	this.open();
}
var DragDrop = {
	isbefore: function(a, b) {
		if (a.parentNode == b.parentNode) {
			for (var cur = a; cur; cur = cur.previousSibling) {
				if (cur === b) return true;
			}
		}
		return false;
	},
	dragenter: function(e) {
		var targetelem = e.target;
		try {
			if (targetelem.nodeName == "TD") {
				targetelem = targetelem.parentNode;
			}
			if (this.isbefore(this.source, targetelem)) {
				targetelem.parentNode.insertBefore(this.source, targetelem);
			} else {
				targetelem.parentNode.insertBefore(this.source, targetelem.nextSibling);
			}
		}
		catch(e) {}
	},
	dragstart: function(e) {
		this.source = e.target;
		e.dataTransfer.effectAllowed = 'move';
		e.dataTransfer.setData('clipBoard', e.target);//old Browser
	},
	dragend: function(e,tb) {
		e.preventDefault();
		var numcol = document.querySelectorAll('#'+tb+' thead th[data-id="order"]')[0].cellIndex;
		var rows = document.querySelectorAll('#'+tb+' tbody tr');
		for(var i=0;i<rows.length;i++) {
			if(rows[i].cells[numcol].getElementsByTagName('input')[0])
				rows[i].cells[numcol].getElementsByTagName('input')[0].value = i+1;
		}
	}
};

(function() {
	// Checks password

	// Disable for auth.php + ?p= but active on auth.php?.* (plugins & action=changepassword)
	if(/((?:\/auth\.php)(?:\?p=)?)(?!\?.*=)/.test(document.URL)) {
		return
	}

	const pwds = document.querySelectorAll('input[type="password"]');
	if (pwds.length == 0) {
		return;
	}

	// Testing strength of password
	const label = pwds[0].parentElement.querySelector('[data-lang]');
	const words = (label) ? label.dataset.lang.split('|') : null;
	pwds[0].addEventListener('keyup', function(ev) {
		const val = ev.target.value;
		if(val == '') {
			if (label) {
				label.textContent = '';
			}
			ev.target.style.backgroundColor = '';
			return;
		}

		var no = 0;
		no++;
		if(val.length >= 6) {
			if(val.match(/[a-z]+/i)) {
				no++;
				if(val.match(/\d+/)) {
					no++;
					if(val.match(/[!@#$%&*?_~()-]+/)) {
						no++;
					}
				}
			}
		}

		// Change password background color
		// Colors: white = empty, red = very weak, orange = weak, yellow = good, green = strong
		const colors = ['#fff', '#f00', '#f90', '#fc0', '#3c3'];
		ev.target.style.backgroundColor = colors[no];
		// Change label strenght password
		if(words) {
			label.textContent = (no > 0 && no <= words.length) ? words[no-1] : '';
		}
	});

	if(pwds.length == 1) {
		return;
	}

	// Checks if confirmation password is right
	const label1 = pwds[1].parentElement.querySelector('[data-lang]');
	const words1 = (label1) ? label1.dataset.lang.split('|') : null;
	if(words1 && words1.length >= 2) {
		// disabled last input["submit"]
		pwds[1].addEventListener('change', function(ev){
			if(pwds[0].value.length == 0 || pwds[1].value.length == 0) {
				label1.textContent = '';
			} else {
				label1.textContent = words1[(pwds[0].value == pwds[1].value) ? 1: 0];
			}
		});
	}

	const form1 = pwds[0].form;
	form1.addEventListener('submit', function(ev) {
		const val = pwds[0].value;
		if(val.length == 0) {
			return;
		}
		if(val != pwds[1].value) {
			event.preventDefault();
			if(words1 && words1.length > 0) {
				label1.textContent = words1[0];
			}
			pwds[1].focus();
		}
	});
})();
