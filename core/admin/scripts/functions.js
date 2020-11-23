'use strict';

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

(function() {
	// gestion des cases à cocher dans un tableau pour envoi avec un formulaire
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

function insTag(where, tag) {
	var formfield = document.getElementsByName(where)['0'];
	var tags = formfield.value.split(', ');
	if (tags.indexOf(tag) != -1) return;
	if(formfield.value=='')
		formfield.value=tag;
	else
		formfield.value = formfield.value+', '+tag;
}
