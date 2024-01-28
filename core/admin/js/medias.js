// for medias.php

'use strict';

(function(id) {
	const tbody = document.getElementById(id);
	const mo = document.getElementById("modal__overlay");
	// var mbox = document.getElementById("modal__box");
	const mb = document.getElementById("modal");
	const zoomboxImg = document.getElementById('zoombox-img');
	const loader = document.getElementById('loader');

	if(!tbody) {
		console.error(id + ' element not found');
		return;
	}

	function done() {
		mb.checked = false;
		zoomboxImg.src = '';
	}

	tbody.addEventListener('click', function(event) {
		// Zoom the image in the modal box
		if(event.target.classList.contains('thumb') && event.target.tagName ==  'IMG') {
			event.preventDefault();
			const src = event.target.src.replace(/\/.thumbs?\b/, '');
			const title = src.replace(/.*\/([^\/]*)$/, '$1');
			loader.classList.add('show');
			zoomboxImg.alt = title;
			zoomboxImg.title = title;
			mb.checked = true;
			const img = new Image;
			img.onload = function(ev) {
				console.log('image loaded : ' + ev.target.src);
				zoomboxImg.src = src;
				loader.classList.remove('show');
			}
			img.onerror = function(ev) {
				alert('Image not loaded from ' + src);
			}
			img.src = src;
			return;
		}

		// Copy the link into the clipboard
		if(event.target.hasAttribute('data-copy')) {
			event.preventDefault();
			const aux = document.getElementById('clipboard');
			if(aux == null) {
				console.error('#clipboard element not found');
				return;
			}

			aux.style.display = 'initial';
			aux.value = event.target.dataset.copy;
			aux.select();
			document.execCommand('copy');
			const notice = event.target.firstElementChild;
			notice.style.display = 'inline-block';
			var t = setTimeout(function() {
				aux.value = '';
				notice.style.display = 'none';
				clearTimeout(t);
			}, 1000);
			aux.value = '';
			aux.style.display = 'none';
			return;
		}

		// rename the filename of image
		if(event.target.hasAttribute('data-rename')) {
			event.preventDefault();
			document.getElementById('id_oldname').value = event.target.dataset.rename;
			dialogBox("dlgRenameFile");
			return;
		}
	});

	window.addEventListener("keydown", function (event) {
		// validate if the press key is the escape key
		if (event.code=="Escape" || event.key=="Escape" || event.keyCode==27) {
			event.preventDefault();
			done();
		}
	});

	mo.addEventListener("click", function (event) {
		event.preventDefault();
	   	done();
	});
})('medias-table-tbody');

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
	const input = document.getElementById("medias-search");
	input.value = localStorage.getItem("medias_search");
	plugFilter();
}

/* Tri tableau des medias - @author Jean-Pierre Pourrez "bazooka07" - 2020-05-12 */
(function() {
	const table = document.querySelector('table.sort');
	if(table != null) {
		table.addEventListener('click', function(event) {
			if(event.target.tagName == 'TH' && event.target.classList.contains('sort')) {
				event.preventDefault();
				const el = event.target;
				const isInteger = el.classList.contains('integer');
				const isReverse = el.classList.contains('reverse');
				const index = el.cellIndex;

				if(sessionStorage) { // Save the status
					sessionStorage.setItem('media-sort', isReverse ? -index : index);
				}

				// On enlève le marquage précédent
				const previous = el.parentElement.querySelector('th.sort.active');
				if(previous != null) {
					previous.classList.remove('active');
				}

				// On marque la colonne de tri
				el.classList.add('active');

				// flip-flop
				if(isReverse) {
					event.target.classList.remove('reverse');
				} else {
					event.target.classList.add('reverse');
				}

				const tBody = event.currentTarget.tBodies[0];
				const rows = Array.from(tBody.rows);
				rows.sort(function(row1, row2) {
					if(!('sort' in row1.cells[index].dataset)) { return 1; }
					if(!('sort' in row2.cells[index].dataset)) { return -1; }
					if(isInteger) {
						return (isReverse) ? (parseInt(row2.cells[index].dataset.sort) - parseInt(row1.cells[index].dataset.sort)) : (parseInt(row1.cells[index].dataset.sort) - parseInt(row2.cells[index].dataset.sort));
					}
					return (isReverse) ? row2.cells[index].dataset.sort.localeCompare(row1.cells[index].dataset.sort) : row1.cells[index].dataset.sort.localeCompare(row2.cells[index].dataset.sort);
				});

				tBody.contentText = '';
				rows.forEach(function(row) { tBody.appendChild(row); });
			}

		});

		if(sessionStorage) {
			const lastSort = sessionStorage.getItem('media-sort');
			if(lastSort != null) {
				const value = parseInt(lastSort);
				const cell = table.querySelector('th:nth-of-type(' + (Math.abs(value) + 1) + ')');
				if(value < 0) {
					cell.classList.add('reverse');
				} else {
					cell.classList.remove('reverse');
				}
				cell.click();
			}
		}
	}
})();

(function(containerId, selectId) {
	const el = document.getElementById(containerId);
	const select = document.getElementById(selectId);
	if(el != null) {
		el.onclick = function(event) {
			if(event.target.hasAttribute('data-folder')) {
				event.preventDefault();
				select.value = event.target.dataset.folder;
				select.form.submit();
			}
		}
	}

})('medias-breadcrumb', 'folder');
