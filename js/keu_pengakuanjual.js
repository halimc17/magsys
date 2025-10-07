function list() {
	var param = 'tanggal1='+getValue('tanggal_from')+'&tanggal2='+getValue('tanggal_until')+'&pabrik='+getValue('pabrik')+'&komoditi='+getValue('komoditi'),
		tujuan = 'keu_slave_pengakuanjual.php?proses=list';
	param += "&nokontrak="+getValue('nokontrak')+"&kdpt="+getValue('kdpt');
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					getById('containerList').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function showDetail(noTiket,millCode,ev){
        title="Detail Nokontrak";
        content="<fieldset><legend>"+noTiket+"</legend><div id=contDetail style='overflow:auto; width:350px; height:100px;' ></div></fieldset><input type=hidden id=tanggal_"+noTiket+" name=tanggal_"+noTiket+" value="+getValue('tanggal_'+noTiket)+" />";
        width='450px';
        height='250px';
        showDialog1(title,content,width,height,ev);	
}
function pilKontrak(obj,noTiket,millCode,rw,event){
	if(rw==0){
		post(obj,noTiket,millCode,rw);//jika tidak ada detail dari nokontrak langsung terposting
	}else{
		showDetail(noTiket,millCode,event);//memunculkan pilihan kontrak 
		var param='proses=getForm';
		param+='&notiket='+noTiket+'&millcode='+millCode+'&tanggal='+getValue('tanggal_'+noTiket)+'&obc='+obj+'&rw='+rw,
		tujuan = 'keu_slave_pengakuanjual.php?proses=pilKontrak';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					getById('contDetail').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function post(obj,noTiket,millCode,rw) {
	var param = 'notiket='+noTiket+'&millcode='+millCode+'&tanggal='+getValue('tanggal_'+noTiket)+'&rowKntrk='+rw,
		tujuan = 'keu_slave_pengakuanjual.php?proses=post';
		
	if((getValue('tanggal_'+noTiket))=='') {
		alert("Warning: Tanggal Pengakuan Tiket "+noTiket+" belum diisi");
		return;
	}
	if(rw!=0){
		if(getValue('nokontrakDt')==''){
			alert("Warning: No kontrak harus di pilih");
			return;
		}
		btn=document.getElementById('imgPost_'+noTiket);
		param+='&nokontrakDt='+getValue('nokontrakDt');
	}else{
		btn = obj;
	}
	if(confirm("Anda akan melakukan pengakuan atas No. Tiket "+noTiket+"\nAnda yakin?"))
		post_response_text(tujuan, param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					var fieldTgl = getById('tanggal_'+noTiket+'_'+millCode);
					fieldTgl.disabled = true;
					btn.removeAttribute('onclick');
					btn.setAttribute('src','images/skyblue/posted.png');
					if(rw!=0){
						closeDialog();
					}
					// getById('containerList').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPtkntrk(){
	var param = 'tanggal1='+getValue('tanggal_from')+'&tanggal2='+getValue('tanggal_until')+'&pabrik='+getValue('pabrik'),
		tujuan = 'keu_slave_pengakuanjual.php?proses=getPt';
		if((getValue('tanggal_from')=='')||(getValue('tanggal_until')=='')){
			alert("Warning: Tanggal harus lengkap!!");
			return;
		}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					 getById('kdpt').innerHTML = con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function getExcel(ev,tujuan){
	var param = 'tanggal1='+getValue('tanggal_from')+'&tanggal2='+getValue('tanggal_until')+'&pabrik='+getValue('pabrik')+'&komoditi='+getValue('komoditi');
	    param += "&nokontrak="+getValue('nokontrak')+"&kdpt="+getValue('kdpt')+"&proses=listExcel";
	  judul='Report Ms.Excel';	
	  printFile(param,tujuan,judul,ev);	
}
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='450';
   height='450';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev); 	
}