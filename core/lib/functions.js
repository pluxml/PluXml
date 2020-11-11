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
function checkAll(form, field) {
	const chks = form.elements[field];
	for(var i = 0, iMax=chks.length; i < iMax; i++) {
		if(chks[i].type == 'checkbox') {
			chks[i].checked = !chks[i].checked;
		}
	}
}
function confirmAction(form, selfield, selvalue, field, msg) {
	if(document.getElementById(selfield).value == selvalue) {
		const chks = form.elements[field];
		var cnt = 0;
		for(var i = 0, iMax=chks.length; i < iMax; i++) {
			if(chks[i].type == 'checkbox' && chks[i].checked) {
				cnt++;
			}
		}
		return (cnt > 0) ? confirm(msg.replace(/##/, cnt)) : false;
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
