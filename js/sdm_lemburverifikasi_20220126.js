/**
 * SDM Verifikasi Lembur
 */

function bersihkanForm(){
	//document.getElementById('kdUnit').value='';
	//document.getElementById('periode').value='';
}

function loadData(){
	kdUnit	=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	periode =document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	param='kdUnit='+kdUnit+'&periode='+periode;
	param+='&proses=loadData';
	tujuan='sdm_lemburverifikasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function deldata(kodeorg,tanggal,karyawanid){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&karyawanid='+karyawanid;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'sdm_lemburverifikasi_slave.php';
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

function SaveCell(kodeorg,tanggal,karyawanid,cellname,cellvalue){
	cellvalue2=document.getElementById(cellname+kodeorg+tanggal+karyawanid).value;
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&karyawanid='+karyawanid+'&cellname='+cellname+'&cellvalue='+cellvalue2;
	param+='&proses=SimpanCell';
	//alert(param);
	tujuan = 'sdm_lemburverifikasi_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					//document.getElementById(cellname+idbgtunit).innerHTML=con.responseText;
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function postingdata(kodeorg,tanggal,karyawanid){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&karyawanid='+karyawanid;
	param+='&proses=postingData';
	tujuan = 'sdm_lemburverifikasi_slave.php';
	post_response_text(tujuan, param, respog);
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
