/* Visual Effects */
function setOpacity(obj, opacity) {
	obj.style.minHeight = obj.style.minHeight; // hack IE
	opacity = (opacity == 100)?99.999:opacity;
	obj.style.filter = "alpha(opacity="+opacity+")"; // IE/Win
	obj.style.KHTMLOpacity = opacity/100; // Safari<1.2, Konqueror
	obj.style.MozOpacity = opacity/100; // Older Mozilla and Firefox
	obj.style.opacity = opacity/100; // Safari 1.2, newer Firefox and Mozilla, CSS3
}
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
			}
		}
	}
}
function setMsg() {
	if(document.getElementById('msg')) {
		objDiv = document.getElementById('msg');
		objSidebar = document.getElementById('sidebar')
		if (typeof window.innerWidth != 'undefined') {
			wndWidth = window.innerWidth;
		}
		else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth !='undefined' && document.documentElement.clientWidth != 0) {
			wndWidth = document.documentElement.clientWidth;
		}
		else {
			wndWidth = document.getElementsByTagName('body')[0].clientWidth;
		}
		xpos = Math.round((wndWidth-objDiv.offsetWidth)/2);
		objDiv.style.left=xpos+'px';
		fadeOut('msg');
	}
}
function pwdStrength(id, s) {
	// Colors: white = empty, red = very weak, orange = weak, yellow = good, green = strong
	var color = ['#fff', '#ff0000', '#ff9900', '#ffcc00', '#33cc33'];
	var val = document.getElementById(id).value;
	var no=0;
	// If the password length is less than or equal to 6
	if(val.length>0 && val.length<=6) no=1;
	// If the password length is greater than 6 and contain any lowercase alphabet or any number or any special character
	if(val.length>6 && (val.match(/[a-z]/) || val.match(/\d+/) || val.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))) no=2;
	// If the password length is greater than 6 and contain alphabet,number,special character respectively
	if(val.length>6 && ((val.match(/[a-z]/) && val.match(/\d+/)) || (val.match(/\d+/) && val.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) || (val.match(/[a-z]/) && val.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)))) no=3;
	// If the password length is greater than 6 and must contain alphabets,numbers and special characters
	if(val.length>6 && val.match(/[a-z]/) && val.match(/\d+/) && val.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) no=4;
	// Change password background color
	document.getElementById(id).style.backgroundColor=color[no];
	// Change label strenght password
	var pwdstr=document.getElementById(id+'_strenght');
	pwdstr.innerHTML='';if(no>0){pwdstr.innerHTML=s[no-1]};
}
function dialogBox(name) {
	this.dlg = document.getElementById("dlg"+name);
	this.btn = document.getElementById("btn"+name);
	this.span = document.querySelector('#dlg'+name+' .dialog-close');
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
	this.addEvent(this.btn, 'click', this.open);
	this.addEvent(this.span, 'click', this.close);
}