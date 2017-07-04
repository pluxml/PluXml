var dragAndDrop = function(selector, reOrder1) {

	'use strict';

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

		if(typeof(reOrder) == 'string') {
			var stickers = document.querySelectorAll(reOrder);
			if((stickers != null) && (stickers.length > 1)) {
				var counter = 0;
				stickers.forEach(function(item) {
					counter++;
					if(item.hasAttribute('value')) {
						item.value = counter;
					} else {
						item.innerHTML = counter;
					}
				});
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

	var cols = document.querySelectorAll(selector);
	var reOrder = (reOrder1 != undefined) ? reOrder1 : null;

	if((cols != null) && (cols.length > 1)) {
		cols.forEach(addDnDHandlers);

		var innerHTML = '';
		innerHTML += selector + '.dragElem' + ' { opacity: 0.3; }';
		innerHTML += selector + '.over' + ' { background-color: #aaa; }';
		var style = document.createElement('STYLE');
		style.type = 'text/css';
		style.innerHTML = innerHTML;

		document.head.appendChild(style);
	}
};