/*
 * Please, add the "data-rows-num"  attribute of the <table> tag for sorting his rows with the mouse
 * Only one table is allowed.
 * */

(function() {

	'use strict';

	const dndTable = document.querySelector('table[data-rows-num]');

	if(dndTable != null) {
		const attr = dndTable.getAttribute('data-rows-num').trim();
		const reOrder = (attr.length > 0) ? 'tbody tr:not(.new) input[' + attr + ']' : null;
		const dragEvents = 'start enter over leave end'.split(' ').map(function(item) {
			return 'ondrag' + item;
		});
		var dragSrcEl = null;

		function handleDragStart(e) {
			// Target (this) element is the source node.
			dragSrcEl = this;
			dragSrcEl.classList.add('dragElem');

			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/html', dragSrcEl.outerHTML);

		}

		function handleDragOver(e) {
			if (e.preventDefault) {
				e.preventDefault(); // Necessary. Allows us to drop.
			}
			this.classList.add('over');

			e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

			return false;
			}

		function handleDragEnter(e) {
			// this / e.target is the current hover target.
		}

		function handleDragLeave(e) {
			this.classList.remove('over');  // this / e.target is previous target element.
		}

		function handleDrop(e) {
			// this/e.target is current target element.

			if (e.stopPropagation) {
				e.stopPropagation(); // Stops some browsers from redirecting.
			}

			// Don't do anything if dropping the same column we're dragging.
			if (dragSrcEl != this) {
				this.parentNode.removeChild(dragSrcEl);
				this.parentNode.insertBefore(dragSrcEl, this);
			}
			this.classList.remove('over');

			if(reOrder != null) {
				// On renumérote les rangées du tableau
				var stickers = dndTable.querySelectorAll(reOrder);
				if((stickers != null) && (stickers.length > 1)) {
					for(var i=0, iMax=stickers.length; i<iMax; i++) {
						var node = stickers[i];
						var counter = i+1;
						if(node.hasAttribute('value')) {
							node.value = counter;
						} else {
							node.textContent = counter;
						}
					}
				}
			}
			return false;
		}

		function handleDragEnd(e) {
			// this/e.target is the source node.
			this.classList.remove('over');
			if(dragSrcEl != null) {
				dragSrcEl.classList.remove('dragElem');
				dragSrcEl = null;
			}
		}

		function addDnDHandlers(elem) {
			elem.setAttribute('draggable', true);
			elem.addEventListener('dragstart', handleDragStart, false);
			elem.addEventListener('dragenter', handleDragEnter, false)
			elem.addEventListener('dragover', handleDragOver, false);
			elem.addEventListener('dragleave', handleDragLeave, false);
			elem.addEventListener('drop', handleDrop, false);
			elem.addEventListener('dragend', handleDragEnd, false);
		}

		function setup(node) {
			var rows = node.querySelectorAll('tbody tr:not(.new)');
			if((rows != null) && (rows.length > 1)) {
				for(var i=0, iMax=rows.length; i<iMax; i++) {
					addDnDHandlers(rows[i]);
				}
			}
		};

		setup(dndTable);

	}

})();