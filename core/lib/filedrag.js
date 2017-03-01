(function() {
	
	var usrFiles = [];
	var fileSelect = $id("myfiles");
	var fileDrag = $id("filedrag");
	var btnUpload = $id("btn_upload");
	var btnReset = $id("btn_reset");
	var Token = $$id('token');
	
	var resize = $$id("resize");
	var user_w = $$id("user_w");
	var user_h = $$id("user_h");
	var thumb = $$id("thumb");
	var thumb_w = $$id("thumb_w");
	var thumb_h = $$id("thumb_h");	

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}
	
	// getElementById
	function $$id(id) {
		return document.form_uploader.elements[id].value;
	}

	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}
	
	// file selection
	function FileSelectHandler(e) {

		// cancel event and hover styling
		FileDragHover(e);
		
		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// unique id
		var uid = (new Date().getTime()).toString(10);

		// process all File objects
		for (var i = 0; i < files.length; i++) {
			var f = { 
				file : files[i], 
				fid : 'p_' + uid + '_' + i 
			};
			div = document.createElement("div");
			div.setAttribute("id", f.fid);
			div.setAttribute("class", "progress");
			$id("progress").appendChild(div);
			usrFiles.push(f);	
			ParseFile(f);
		}
		
		btnUpload.style.display = "inline-block";
		btnReset.style.display = "inline-block";
		
	}
	
	// output file information
	function ParseFile(f) {

		// display image file
		var reader = new FileReader();
		reader.onload = function(e) {
			// image
			var img = document.createElement("img");	
			img.setAttribute("src", f.file.type.indexOf("image") == 0 ? e.target.result : 'theme/images/file.png');
			img.setAttribute("title", f.file.name);
			$id(f.fid).appendChild(img);
			// delete link
			var lnkDelete = document.createElement('a');
			lnkDelete.setAttribute('href', 'javascript:void(0)');
			lnkDelete.innerHTML = '&#10006';
			lnkDelete.setAttribute("class","btn_delete");
			lnkDelete.setAttribute("className","btn_delete"); /* Hack IE */
			lnkDelete.onclick = function(){
				var div = $id(f.fid);
				div.parentNode.removeChild(div);
				// remove file from usrFile array
				for(var i=0;i<usrFiles.length;i++) {
					if(usrFiles[i].fid==f.fid) {
						usrFiles.splice(i,1);
					}
				}
				// hide Upload & reset button
				if(usrFiles.length==0) {
					btnUpload.style.display = "none";
					btnReset.style.display = "none";
				}
			};
			$id(f.fid).appendChild(lnkDelete);
			// filename
			var s = f.file.name;
			if(s.length > 10) {
				s = s.substring(0,5) + '...' + getFilePathExtension(s);
			}
			$id(f.fid).appendChild(document.createTextNode(s));
		}
		reader.readAsDataURL(f.file);
	}

	function getFilePathExtension(path) {
		var filename = path.split('\\').pop().split('/').pop();
		return filename.substr(( Math.max(0, filename.lastIndexOf(".")) || Infinity) + 1);
	}

	function UploadFiles(f) {

		var xhr = new XMLHttpRequest();
		if (xhr.upload) {

			var progress = $id(f.fid).appendChild(document.createElement("p"));
			progress.setAttribute('class', 'progress');
			
			// progress bar
			xhr.upload.addEventListener("progress", function(e) {
				var pc = parseInt(100 - (e.loaded / e.total * 100));
				progress.style.backgroundPosition = pc + "% 0";
			}, false);

			xhr.onreadystatechange = function(){
				if(xhr.readyState == 4){
					try {
						var resp = JSON.parse(xhr.response);
					} catch (e){
						var resp = {
							status: 'error',
							data: 'Unknown error occurred: [' + xhr.responseText + ']'
						};
					}
					progress.className = (resp.status=='error' ? "failed": "success");
					console.log(resp.status + ': ' + resp.data);
				}
			};

			var data = new FormData();
			data.append('myfiles', f.file);
			data.append('token', Token);
			data.append('resize', resize);
			data.append('user_w', user_w);
			data.append('user_h', user_h);
			data.append('thumb', thumb);
			data.append('thumb_w', thumb_w);
			data.append('thumb_h', thumb_h);	
			
			// start upload
			xhr.open("POST", 'upload.php', true);
			xhr.send(data);

		} 

	}
	
	// initialize
	function Init() {

		// file select
		fileSelect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {
			fileDrag.addEventListener("dragover", FileDragHover, false);
			fileDrag.addEventListener("dragleave", FileDragHover, false);
			fileDrag.addEventListener("drop", FileSelectHandler, false);
			fileDrag.addEventListener("click", function(){ fileSelect.click() }, false);
		} 
		
		btnUpload.addEventListener('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			for (var i = 0; i < usrFiles.length; i++) {
				UploadFiles(usrFiles[i]);
			}
		});		
		
		btnReset.addEventListener('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			usrFiles.splice(0); // reset array
			$id('progress').innerHTML = '';
			btnUpload.style.display = "none";
			btnReset.style.display = "none";
		});			

	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}

})();