// zoombox
var all = document.querySelectorAll(".overlay");
var mo = document.getElementById("modal__overlay");
var mbox = document.getElementById("modal__box");
var mb = document.getElementById("modal");
for (var i = 0, nb = all.length; i < nb; i++) {
	all[i].addEventListener('click', function(e) {
		e.preventDefault();
		mbox.innerHTML = '<img src="'+this.href+'" alt="" /><label for="modal">&#10006;</label>';
		mb.click();
	},false);
}
window.addEventListener("keydown", function (event) {
	// validate if the press key is the escape key
	if (event.code=="Escape" || event.key=="Escape" || event.keyCode==27) {
    	mbox.innerHTML = "";
    	if (mb.checked === true) {
    		mb.click();
    	}
    }
});
mo.addEventListener("click", function (event) {
   	mbox.innerHTML = "";
   	if (mb.checked === true) {
   		mb.click();
   	}
});

function toggle_divs(){
	var uploader = document.getElementById('files_uploader');
	var manager = document.getElementById('files_manager');
	if(uploader.style.display == 'none') {
		uploader.style.display = 'block';
		manager.style.display = 'none';
	} else {
		uploader.style.display = 'none';
		manager.style.display = 'block';
	}
}
function copy(elt, data) {
	try {
		var div = elt.querySelector("div");
		var aux = document.createElement("input");
		aux.setAttribute("value", data);
		document.body.appendChild(aux);
		aux.select();
		document.execCommand("copy");
		document.body.removeChild(aux);
		div.setAttribute("style", "display:inline-block");
		t = setTimeout(function(){
			div.setAttribute("style", "display:none");
			clearTimeout(t);
		}, 1000);
	} catch (err) {
		alert('<?php echo L_MEDIAS_LINK_COPYCLP_ERR ?>');
	}
}
function plugFilter() {
	var input, filter, table, tr, td, i;
	filter = document.getElementById("medias-search").value;
	filter = filter.replace(/\s/g, '');
	table = document.getElementById("medias-table");
	tr = table.getElementsByTagName("tr");
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[2];
		if (td != undefined) {
			if (td.innerHTML.toLowerCase().indexOf(filter.toLowerCase()) > -1) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
		}
	}
	if (typeof(Storage) !== "undefined" && filter !== "undefined") {
		localStorage.setItem("medias_search", filter);
	}
}
if (typeof(Storage) !== "undefined" && localStorage.getItem("medias_search") !== "undefined") {
	input = document.getElementById("medias-search");
	input.value = localStorage.getItem("medias_search");
	plugFilter();
}

function ImageRename(oldimg) {
	document.getElementById('id_oldname').value = oldimg;
	dialogBox("dlgRenameFile");
}