<!--
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
-->