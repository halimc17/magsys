// JavaScript Document

function add_new_data(){
	document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	//document.getElementById('detailEntry').style.display="none";
	//document.getElementById('tmbLheader').innerHTML='<button class=mybutton id=dtlAbn onclick=loadDetailData()>'+nmTmblSave+'</button><button class=mybutton id=cancelAbn onclick=cancelAbsn()>'+nmTmblCancel+'</button>';
	bersihkanForm(1);
	statFrm=0;
}

function displayList(){
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	//document.getElementById('detailEntry').style.display='none';
	document.getElementById('tgl_cari').value='';
	document.getElementById('sts_cari').value='';
	loadData();
}

function loadData(num){
	tgl_cari=document.getElementById('tgl_cari').value;
	sts_cari=document.getElementById('sts_cari').value;
	param='proses=loadNewData';
	param+='&tgl_cari='+tgl_cari;
	param+='&sts_cari='+sts_cari;
	param+='&page='+num;
	tujuan='sdm_pekerjaanharian_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('contain').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function loadDetailData(){
	tanggal=document.getElementById('tanggal').value;
	param='proses=loadDetailData'+'&tanggal='+tanggal;
	//alert(param);
	tujuan='sdm_pekerjaanharian_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('contentDetail').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function showpopup(karyawanid,namakaryawan,tanggal1,type,ev){
   param='karyawanid='+karyawanid+'&namakaryawan='+namakaryawan+'&tanggal1='+tanggal1+'&type='+type;
   tujuan='sdm_pekerjaanharian_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Pencapaian Dan Target Pekerjaan '+karyawanid+' '+namakaryawan+' '+tanggal1,content,width,height,ev); 
}

function simpanData(){
	nomor	  	=document.getElementById('nomor').value;
	tanggal  	=document.getElementById('tanggal').value;
	pekerjaan	=document.getElementById('pekerjaan').value;
	target		=document.getElementById('target').value;
	aktual		=document.getElementById('aktual').value;
	correction	=document.getElementById('correction').value;
	rencanakerja=document.getElementById('rencanakerja').value;
	catatan		=document.getElementById('catatan').value;
	atasan		=document.getElementById('atasan').value;
	stspekerjaan=document.getElementById('stspekerjaan').value;
	addedit		=document.getElementById('addedit').value;
	if(tanggal=='' ||  pekerjaan=='' ||  target=='' ||  aktual=='' ||  atasan=='' ||  stspekerjaan==''){
		alert('All fields are required');
	}else{
		param='nomor='+nomor+'&tanggal='+tanggal+'&pekerjaan='+pekerjaan+'&target='+target+'&aktual='+aktual+'&correction='+correction;
		param+='&rencanakerja='+rencanakerja+'&catatan='+catatan+'&atasan='+atasan+'&stspekerjaan='+stspekerjaan+'&addedit='+addedit+'&proses=saveData';
		tujuan='sdm_pekerjaanharian_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadDetailData();
					bersihkanForm(0);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function bersihkanForm(num){
	if(num==1){
		document.getElementById('tanggal').value='';
		document.getElementById('contentDetail').innerHTML='';
	}
	document.getElementById('nomor').value='';
	document.getElementById('pekerjaan').value='';
	document.getElementById('target').value='';
	document.getElementById('aktual').value='';
	document.getElementById('correction').value='';
	document.getElementById('rencanakerja').value='';
	document.getElementById('catatan').value='';
	document.getElementById('atasan').value='';
	document.getElementById('stspekerjaan').value='';
	document.getElementById('addedit').value='insert';
}

function fillfield(nomor,tanggal,pekerjaan,target,aktual,correction,rencanakerja,catatan,atasan,stspekerjaan){
	document.getElementById('nomor').value=nomor;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('pekerjaan').value=pekerjaan;
	document.getElementById('target').value=target;
	document.getElementById('aktual').value=aktual;
	document.getElementById('correction').value=correction;
	document.getElementById('rencanakerja').value=rencanakerja;
	document.getElementById('catatan').value=catatan;
	document.getElementById('atasan').value=atasan;
	document.getElementById('stspekerjaan').value=stspekerjaan;
	document.getElementById('addedit').value='update';
}

function delDetail(nomor,karyawanid,tanggal){
	param='nomor='+nomor+'&karyawanid='+karyawanid+'&tanggal='+tanggal;
	param+='&proses=delDetail';
	if (confirm('Delete Data..?')) {
		tujuan = 'sdm_pekerjaanharian_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadDetailData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function postDetail(nomor,karyawanid,tanggal){
	param='nomor='+nomor+'&karyawanid='+karyawanid+'&tanggal='+tanggal;
	param+='&proses=postDetail';
	if (confirm('Posting Data..?')) {
		tujuan = 'sdm_pekerjaanharian_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadDetailData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function editData(karyawanid,tanggal){
	document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('pekerjaan').value='';
	document.getElementById('target').value='';
	document.getElementById('aktual').value='';
	document.getElementById('correction').value='';
	document.getElementById('rencanakerja').value='';
	document.getElementById('catatan').value='';
	document.getElementById('atasan').value='';
	document.getElementById('stspekerjaan').value='';
	document.getElementById('contentDetail').innerHTML='';
	document.getElementById('addedit').value='insert';
	loadDetailData();
}

function delData(karyawanid,tanggal){
	param='karyawanid='+karyawanid+'&tanggal='+tanggal;
	param+='&proses=delData';
	if (confirm('Delete Data Tanggal '+tanggal+'..?')) {
		tujuan = 'sdm_pekerjaanharian_slave.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}
