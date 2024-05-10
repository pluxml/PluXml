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
function checkAll(inputs, field) {
	for(var i = 0; i < inputs.elements.length; i++) {
		if(inputs[i].type == "checkbox" && inputs[i].name==field) {
			inputs[i].checked = !inputs[i].checked ;
		}
	}
}
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
function insTag(where, tag) {
	var formfield = document.getElementsByName(where)['0'];
	var tags = formfield.value.split(', ');
	if (tags.indexOf(tag) != -1) return;
	if(formfield.value=='')
		formfield.value=tag;
	else
		formfield.value = formfield.value+', '+tag;
}

(function(id) {
	'use strict';

	const el = document.getElementById(id);
	if(el == null || !('infos' in el.dataset)) {
		// contrôle de la dernière version gérée par le serveur dans plxAdmin::checkMaj()
		return;
	}

	const infos = JSON.parse(el.dataset.infos);

	function compareVersion(v1, v2) {
		if(typeof v1 != 'string' || typeof v2 != 'string') { return; }

		const t1 = v1.split('.');
		const t2 = v2.split('.');
		for(let i=0, iMax=(t1.length < t2.length) ? t1.length : t2.length; i<iMax; i++) {
			const n1 = parseInt(t1[i]);
			const n2 = parseInt(t2[i]);
			if(n1 == n2) { continue; }
			return (n1 < n2) ? -1 : 1;
		}
		return (t1.length == t2.length) ? 0 : (t1.length < t2.length) ? -1 : 1;
	}

	console.log('Current version of PluXml : ' + infos.currentVersion);
	const xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState === XMLHttpRequest.DONE) {
			if(this.status === 200) {
				console.log('Available version :', this.responseText);
				el.classList.remove('red');
				if(compareVersion(infos.currentVersion, this.responseText) < 0) {
					el.innerHTML = infos.available + '<a href="' + infos.urlRepo + '">' + infos.urlRepo + '</a>';
					el.classList.add('orange');
				} else {
					el.innerHTML = infos.uptodate;
					el.classList.add('green');
				}
				return;
			}
			console.error('[check update]', this.status, this.statusText);
		}
	};

	xhr.open('GET', infos.urlVersion);
	xhr.send();
}('latest-version'));
