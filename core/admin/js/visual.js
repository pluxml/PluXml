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

	const pwd1 = document.querySelector('input[type="password"][name="password1"]');
	if(pwd1) {
		// for lost password in auth.php, profil.php, install.php

		// Testing strength of password
		const label = pwd1.form.querySelector('[data-lang]');
		const words = (label) ? label.dataset.lang.split('|') : null;
		pwd1.addEventListener('keyup', function(ev) {
			const val = ev.target.value;
			if(val.trim().length < 6) {
				if (label) {
					label.textContent = '';
				}
				ev.target.style.backgroundColor = '';
				return;
			}

			var no = 0;
			if(val.match(/[a-z]+/i)) {
				no++;
				if(val.match(/\d+/)) {
					no++;
					if(val.match(/[!@#$%&*?_~()-]+/)) {
						no++;
					}
				}
			}

			// Change password background color
			// Colors: red = very weak, orange = weak, yellow = good, green = strong
			const colors = ['#f00', '#f90', '#fc0', '#3c3'];
			ev.target.style.backgroundColor = colors[no];
			// Change label strenght password
			if(words) {
				if(no >= words.length) {
					no = words.length - 1
				}
				label.textContent = words[no];
			}
		});

		// https://stackoverflow.com/questions/21727317/how-to-check-confirm-password-field-in-form-without-reloading-page
		const pwd2 = pwd1.form.querySelector('input[type="password"][name="password2"]');
		if(!pwd2) {
			return;
		}

		// Checks if confirmation password is right
		function checkPassword(ev) {
			const confirmation = (pwd1.value == pwd2.value);
			if(confirmation) {
				pwd2.setCustomValidity('');
			} else {
				const msg = pwd2.dataset.mismatch;
				pwd2.setCustomValidity(msg ? msg : 'Passwords do not match');
			}
			pwd2.reportValidity();
		}

		pwd1.addEventListener('change', checkPassword);
		pwd2.addEventListener('change', checkPassword);
	}


	// New user
	const users = document.getElementById('form_users');
	if(users) {
		users.addEventListener('submit', function(ev) {
			alert('Controler nouvel utilisateur');
		});
	}
})();
