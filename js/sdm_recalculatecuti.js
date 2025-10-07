/**
 * Pabrik Limbah B3
 */

function loadData(num){
	carikodeorg		=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1	=document.getElementById('caritanggal1').value;
	caritanggal2	=document.getElementById('caritanggal2').value;
	carikaryawanid	=document.getElementById('carikaryawanid').options[document.getElementById('carikaryawanid').selectedIndex].value;
	kodeorg			=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='proses=loadData'+'&kodeorg='+kodeorg;
	param+='&carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikaryawanid='+carikaryawanid;
	param+='&page='+num;
	tujuan='sdm_recalculatecuti_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('page').value=num;
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function simpanData(){
	kodeorg 		=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	kodeorglama		=document.getElementById('kodeorglama').value;
	karyawanid 		=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
	karyawanidlama	=document.getElementById('karyawanidlama').value;
	tanggal  		=document.getElementById('tanggal').value;
	tglkeluar  		=document.getElementById('tglkeluar').value;
	keterangan 		=document.getElementById('keterangan').value;
	addedit			=document.getElementById('addedit').value;
	page			=document.getElementById('page').value;
	if(kodeorg=='' || tanggal=='' || karyawanid==''){
		alert('Fields are required');
	}else{
		param='kodeorg='+kodeorg+'&karyawanid='+karyawanid+'&tanggal='+tanggal+'&tglkeluar='+tglkeluar+'&keterangan='+keterangan;
		param+='&kodeorglama='+kodeorglama+'&karyawanidlama='+karyawanidlama;
		param+='&addedit='+addedit+'&page='+page+'&proses=saveData';
		tujuan='sdm_recalculatecuti_slave.php';
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
					bersihkanForm();
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function fillfield(kodeorg,karyawanid,tanggal,tglkeluar,keterangan){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeorglama').value=kodeorg;
	document.getElementById('karyawanid').value=karyawanid;
	document.getElementById('karyawanidlama').value=karyawanid;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tglkeluar').value=tglkeluar;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
}

function bersihkanForm(){
	document.getElementById('kodeorg').value='';
	document.getElementById('kodeorglama').value='';
	document.getElementById('karyawanid').value='';
	document.getElementById('karyawanidlama').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tglkeluar').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,karyawanid,page){
	param='kodeorg='+kodeorg+'&karyawanid='+karyawanid;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'sdm_recalculatecuti_slave.php';
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
					loadData(page);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function calcdata(kodeorg,karyawanid,tglkeluar,page){
	param='kodeorg='+kodeorg+'&karyawanid='+karyawanid+'&tglkeluar='+tglkeluar;
	param+='&proses=calcData';
	if (confirm('Re Calculate Cuti..?')) {
		tujuan = 'sdm_recalculatecuti_slave.php';
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
					loadData(page);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function getKary(){
	kodeorg=document.getElementById('kodeorg').value; 
    param='kodeorg='+kodeorg;
    param+='&proses=getKary';
	tujuan='sdm_recalculatecuti_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					if(con.responseText!=''){
						document.getElementById('karyawanid').innerHTML=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getUnit(){
	karyawanid=document.getElementById('karyawanid').value; 
    param='karyawanid='+karyawanid;
    param+='&proses=getUnit';
	tujuan='sdm_recalculatecuti_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					if(con.responseText!=''){
						document.getElementById('kodeorg').innerHTML=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function carikaryawan(type,ev){
	carikodeorg		=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1	=document.getElementById('caritanggal1').value;
	caritanggal2	=document.getElementById('caritanggal2').value;
	carikaryawanid	=document.getElementById('carikaryawanid').options[document.getElementById('carikaryawanid').selectedIndex].value;
	page		=document.getElementById('page').value;
	param='carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikaryawanid='+carikaryawanid+'&type='+type;
	tujuan='sdm_recalculatecuti_showpopup.php?'+param;
	title='Data Karyawan Resign/PHK '+carikodeorg+' '+carikaryawanid;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}
