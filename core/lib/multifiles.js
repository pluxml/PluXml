<!--
var MultiSelector  =  {

	init: function(racine) {
		this.count = 0;
		this.racine = racine;
		this.files_list = document.getElementById("files_list");
		this.selector = document.getElementById("selector_0");
		this.selector.addEventListener("change", this, false);

	},

	handleEvent: function(e) {
		switch(e.type) {
			case "change":
				event.stopPropagation();
				event.preventDefault();
				this.handleChange();
				break;
		}
	},

	handleChange: function() {

		this.selector = document.getElementById("selector_" + this.count++);
		this.selector.style.position = 'absolute';
		this.selector.style.left = '-1000px';

		var new_element = document.createElement('input');
		new_element.type = "file";
		new_element.multiple = "multiple";
		new_element.id = "selector_"+this.count;
		new_element.name = "selector_"+this.count+"[]";
		new_element.addEventListener("change", this, false);
		this.selector.parentNode.insertBefore(new_element, this.selector)

		for(i=0;i<this.selector.files.length;i++) {
			this.addListRow(this.selector, (this.count-1)+'_'+i, this.selector.files[i].name);
			this.selector.parentNode.insertBefore(new_element, this.selector);
		}
	},

	addListRow: function(element, i, filename) {
		// Row div
		var new_row = document.createElement('div');
		new_row.setAttribute("id","rowfile_"+i);
		new_row.setAttribute("class","rowfile");
		new_row.setAttribute("className","rowfile"); /* Hack IE */
		// Input hidden input
		new_hidden = document.createElement('input');
		new_hidden.type = "checkbox";
		new_hidden.name = "myfiles[]";
		new_hidden.checked = "checked";
		new_hidden.value = i;
		new_hidden.style.position = 'absolute';
		new_hidden.style.left = '-1000px';
		// Delete link
		var new_lnkDelete = document.createElement('a');
		new_lnkDelete.setAttribute('href', 'javascript:void(0)');
		new_lnkDelete.innerHTML = '<img src="'+this.racine+'core/admin/theme/images/delete.gif" alt="" />';
		new_lnkDelete.setAttribute("class","btnDelete");
		new_lnkDelete.setAttribute("className","btnDelete"); /* Hack IE */
		// div with filename
		var new_row_div = document.createElement('div');
		new_row_div.innerHTML = '&nbsp;'+this.basename(filename);
		new_row_div.setAttribute("class","divtitle");
		new_row_div.setAttribute("className","divtitle"); /* Hack IE */
		// References
		new_row.element = element;
		// Delete function
		new_lnkDelete.onclick = function(){
			var div = document.getElementById('rowfile_'+i);
			div.parentNode.removeChild(div);
		}
		// Add hidden input
		new_row.appendChild(new_hidden);
		// Add delete link
		new_row.appendChild(new_lnkDelete);
		// Add filename
		new_row.appendChild(new_row_div);
		// Add it to the list
		this.files_list.appendChild(new_row);
	},

	basename: function(path) {
		return path.replace(/\\/g,'/').replace( /.*\//, '' );
	}

};
-->