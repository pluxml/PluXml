(function() {
	'use strict'

	const imgPopup = document.getElementById('img_popup');
	if(!imgPopup) {
		console.error('#ImgPopup element not found');
		return;
	}

	const query = 'a > img.art_thumbnail';
	const thumbnails = document.querySelectorAll(query);
	if(thumbnails.length > 0) {
		const dialog = document.getElementById('img_dialog');

		thumbnails.forEach(function(el) {
			el.addEventListener('click', function(ev){
				ev.preventDefault();
				imgPopup.src = ev.target.src.replace(/\.tb\.(jpe?g|webp|png|gif)$/, '.$1');
				dialog.showModal();
			});
		});

		const btnClose = dialog.querySelector('button');
		btnClose.addEventListener('click', function(ev) {
			dialog.close();
		});
	}
})();
