function replyCom(idCom) {
	document.getElementById('id_answer').innerHTML=document.getElementById("REPLY_TO").value+' :';
	document.getElementById('id_answer').innerHTML+=document.getElementById('com-'+idCom).innerHTML;
	document.getElementById('id_answer').innerHTML+='<a rel="nofollow" href="'+document.getElementById("ART_URL").value+'#form" onclick="cancelCom()">'+document.getElementById("CANCEL").value+'</a>';
	document.getElementById('id_answer').style.display='inline-block';
	document.getElementById('id_parent').value=idCom;
	document.getElementById('id_content').focus();
}
function cancelCom() {
	document.getElementById('id_answer').style.display='none';
	document.getElementById('id_parent').value='';
	document.getElementById('com_message').innerHTML='';
}
var parent = document.getElementById('id_parent').value;
if(parent!='') { replyCom(parent) }
