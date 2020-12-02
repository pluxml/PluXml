'use strict';

/*
// Deprecated

function dateNow(field,delta) {
	var d = new Date();
	// convert to msec, add local time zone offset
	// get UTC time in msec
	var utc = d.getTime() + (d.getTimezoneOffset() * 60000);
	// create new Date object for different city using supplied offset
	var now = new Date(utc + (1000*delta));
	var y = now.getFullYear();
	var m = now.getMonth();
	var d = now.getDate();
	var h = now.getHours();
	var i = now.getMinutes();
	if(i <= 9){i = '0'+i;}
	if(h <= 9){h = '0'+h;}
	if(d <= 9){d = '0'+d;}
	m = m+1;
	if(m <= 9){m = '0'+m;}
	document.getElementsByName(field+'_day')['0'].value = d;
	document.getElementsByName(field+'_time')['0'].value = h+":"+i;
	document.getElementsByName(field+'_month')['0'].value = m;
	document.getElementsByName(field+'_year')['0'].value = y;
}
* */

function answerCom(where,id,author) {
	document.getElementById('id_parent').value=id;
	//addText(where, '<a href="#c'+id+'">@'+author+'</a> :\n');
	scrollTo(0,0);
}
function addText(where, open, close) {
	close = close==undefined ? '' : close;
	var formfield = document.getElementsByName(where)['0'];
	// IE support
	if (document.selection && document.selection.createRange) {
		formfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		formfield.focus();
	}
	// Moz support
	else if (formfield.selectionStart || formfield.selectionStart == '0') {
		var startPos = formfield.selectionStart;
		var endPos = formfield.selectionEnd;
		var restoreTop = formfield.scrollTop;
		formfield.value = formfield.value.substring(0, startPos) + open + formfield.value.substring(startPos, endPos) + close + formfield.value.substring(endPos, formfield.value.length);
		formfield.selectionStart = formfield.selectionEnd = endPos + open.length + close.length;
		if (restoreTop > 0) formfield.scrollTop = restoreTop;
		formfield.focus();
	}
	// Fallback support for other browsers
	else {
		formfield.value += open + close;
		formfield.focus();
	}
	return;
}

/*
// Deprecated
function checkAll(inputs, field) {
	for(var i = 0; i < inputs.elements.length; i++) {
		if(inputs[i].type == "checkbox" && inputs[i].name==field) {
			inputs[i].checked = !inputs[i].checked ;
		}
	}
}

// deprecated
function confirmAction(inputs, selfield, selvalue, field, msg) {
	if(document.getElementById(selfield).value==selvalue) {
		var action = false;
		for(var i = 0; i < inputs.elements.length; i++) {
			if(inputs[i].type == "checkbox" && inputs[i].name==field) {
				if(inputs[i].checked) { action=true }
			}
		}
		return (action ? confirm(msg) : false);
	}
}
*/

(function() {
	// gestion des cases à cocher dans un tableau pour envoi avec un formulaire
	// les boutons avec attribut "data-lang" seront désactivés si aucune case cochée.
	const myForm = document.querySelector('form[data-chk]');

	if(myForm != null) {
		const selectionBtns = myForm.querySelectorAll('button[data-lang]');
		if(selectionBtns.length > 0) {
			const chks = myForm.elements[myForm.dataset.chk];
			myForm.addEventListener('change', function(event) {
				if(event.target.tagName == 'INPUT' && event.target.type == 'checkbox' && event.target.name == myForm.dataset.chk) {
					var disabled = true;
					if(typeof chks.length != 'undefined') {
						if(typeof chks.length == 'number') {
							for(var i=0, iMax = chks.length; i < iMax; i++) {
								if(chks[i].checked) {
									disabled = false;
									break;
								}
							}
						}
					} else {
						// only one checkbox
						disabled = !chks.checked;
					}

					for(var i=0, iMax=selectionBtns.length; i<iMax; i++) {
						selectionBtns[i].disabled = disabled;
					}
				}
			});

			for(var i=0, iMax=selectionBtns.length; i<iMax; i++) {
				selectionBtns[i].onclick = function(event,i, j) {
					var cnt = 0;
					if(typeof chks.length != 'undefined') {
						if(typeof chks.length == 'number') {
							for(var i=0, iMax = chks.length; i < iMax; i++) {
								if(chks[i].checked) {
									cnt++;
								}
							}
						}
					} else {
						// only one checkbox
						cnt = chks.checked ? 1 : 0;
					}
					return confirm(event.target.dataset.lang.replace(/\b999\b/, cnt));
				}
			}

			const chkAll = myForm.querySelector('th:first-of-type input[type="checkbox"]');
			if(chkAll != null) {
				chkAll.onclick = function(event) {
					var cnt = 0;
					if(typeof chks.length != 'undefined') {
						if(typeof chks.length == 'number') {
							for(var i=0, iMax = chks.length; i < iMax; i++) {
								chks[i].checked = !chks[i].checked;
								if(chks[i].checked) {
									cnt++;
								}
							}
						}
					} else {
						// only one checkbox
						chks.checked = !chks.checked;
						cnt = chks.checked ? 1 : 0;
					}

					const disabled = (cnt == 0);
					for(var i=0, iMax=selectionBtns.length; i<iMax; i++) {
						selectionBtns[i].disabled = disabled;
					}
				}
			}
		} else {
			console.error('<button[data-lang] /> not found');
		}
	}
})();

(function() {
	// garantie l'unicité d'une page statique comme page d'accueil
	const staticForm = document.getElementById('form_statics');
	if(staticForm != null) {
		const chks = staticForm.elements['homeStatic[]']
		if(typeof chks.length == 'number') {
			const table = staticForm.querySelector('table');
			if(table != null) {
				table.addEventListener('change', function(event) {
					if(event.target.tagName == 'INPUT' && event.target.name == 'homeStatic[]') {
						for(var i=0, iMax=chks.length; i<iMax; i++) {
							if(chks[i].checked && chks[i] != event.target) {
								chks[i].checked = false;
							}
						}
					}
				});
			}
		} else {
			console.error('Something is wrong for homeStatic[]');
		}
	}

})();

(function() {
	// Contrôle la sernière version de PluXml sur le dépôt
	const latestVersion = document.getElementById('latest-version');
	if(latestVersion != null && 'url_version' in latestVersion.dataset) {
		if('version' in latestVersion.dataset) {
			const currentVersion = latestVersion.dataset.version;

           function compareVersion(v1, v2) {
                if (typeof v1 != 'string' || typeof v2 != 'string') {
                    return;
                }

                const t1 = v1.split('.');
                const t2 = v2.split('.');
                for (let i = 0, iMax = (t1.length < t2.length) ? t1.length : t2.length; i < iMax; i++) {
                    const n1 = parseInt(t1[i]);
                    const n2 = parseInt(t2[i]);
                    if (n1 == n2) {
                        continue;
                    }
                    return (n1 < n2) ? -1 : 1;
                }
                return (t1.length == t2.length) ? 0 : (t1.length < t2.length) ? -1 : 1;
            }

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (this.readyState === XMLHttpRequest.DONE) {
                    if (this.status === 200) {
                        console.log('Available version :', this.responseText);
                        // tester si responseText est au bon format
						latestVersion.classList.add('success');
                        if (compareVersion(currentVersion, this.responseText) < 0) {
							// new version available
							latestVersion.classList.add('available');
                        }
                        return;
                    }
                    console.error('[check update]', this.status, this.statusText);
                }
            };
            xhr.open('GET', latestVersion.dataset.url_version);
            xhr.send();
		} else {
			console.error('Unknown version for this firmwre');
		}
	}
})();

(function() {
	// Search a plugin in the table

	const input = document.getElementById('plugins-search');
	if(input == null) { return; }

	const tbody = input.form.querySelector('tbody');
	if(tbody == null) { return; }

	const rows = tbody.rows;

	function search(query) {
		const MASK = 'hide';
		var count = 0;
		for(var i=0, iMax=rows.length; i<iMax; i++) {
			if(query.trim().length > 0) {
				const chk = rows[i].cells[0].querySelector('input[name="plugName[]"]');
				const description = rows[i].cells[2].textContent;
				if((chk != null && chk.value.toLowerCase().indexOf(query) >= 0) || description.toLowerCase().indexOf(query) >= 0) {
					rows[i].classList.remove(MASK);
					count++;
				} else {
					rows[i].classList.add(MASK);
				}
			} else {
				rows[i].classList.remove(MASK);
				if(i == 0) { count = iMax; }
			}
		}
		return (i > 0);
	}

	const KEY = 'plugins_search';

	input.onkeyup = function(event) {
		const value = event.target.value.toLowerCase();
		if(search(value)) {
			if(typeof localStorage === 'object') {
				localStorage.setItem(KEY, value);
			}
		}
	}

	if(typeof localStorage === 'object') {
		const value = localStorage.getItem(KEY);
		if(value != null) {
			search(value);
			input.value = value;
		}
	}
})();

function insTag(where, tag) {
	var formfield = document.getElementsByName(where)['0'];
	var tags = formfield.value.split(', ');
	if (tags.indexOf(tag) != -1) return;
	if(formfield.value=='')
		formfield.value=tag;
	else
		formfield.value = formfield.value+', '+tag;
}

(function() {
	// animation du burger par Knacss. Récupérer le CSS et supprimer
	const toggleMenu = document.getElementById('toggle-menu');
	if(toggleMenu != null) {
		toggleMenu.onchange = function(event) {
			const el = event.target;
			const label = toggleMenu.labels[0];
			if(toggleMenu.checked) {
				label.classList.add('is-active');
			} else {
				label.classList.remove('is-active');
			}
		}
	}
})();

// -------- core/admin/article.php -------------

(function() {
	// Met la date du jour dans les <input type="date"> et <input type="time">
	const wrapper = document.getElementById('calendar');
	if(wrapper != null) {
		wrapper.addEventListener('click', function(event) {
			if('datetime5' in event.target.dataset) {
				event.preventDefault();
				const dt = new Date();
				[0, 1].forEach(function(value) {
					const query = 'input[name="' + event.target.dataset.datetime5 + '[' + value + ']"]';
					// console.log(query);
					const el = wrapper.querySelector(query);
					if(el != null) {
						switch(el.type) {
							case 'date': el.value = dt.toJSON().substr(0, 10) ; break;
							case 'time': el.value = dt.toTimeString().substr(0, 5); break;
						}
					}
				});
			}
		});
	}
})();

// -------- mobile device ---------------
(function() {
	if (typeof window.orientation !== 'undefined' || /(?:iphone|ipad|android)/i.test(navigator.userAgent)) {
		document.body.classList.add('mobile');
	}
})();
