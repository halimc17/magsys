/**
 * Kebun Adjustment Brondolan
 */

function loadData(num){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1=document.getElementById('caritanggal1').value;
	caritanggal2=document.getElementById('caritanggal2').value;
	carijenis	=document.getElementById('carijenis').options[document.getElementById('carijenis').selectedIndex].value;
	kodeorg		=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='proses=loadData'+'&kodeorg='+kodeorg;
	param+='&carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carijenis='+carijenis;
	param+='&page='+num;
	tujuan='kebun_adjbrondol_slave.php';
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
	kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	kodeorglama	=document.getElementById('kodeorglama').value;
	tanggal  	=document.getElementById('tanggal').value;
	tanggallama	=document.getElementById('tanggallama').value;
	jenis	 	=document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	jenislama	=document.getElementById('jenislama').value;
	waktu	  	=document.getElementById('waktu').value;
	kg		  	=document.getElementById('kg').value;
	keterangan 	=document.getElementById('keterangan').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  tanggal=='' ||  jenis=='' ||  kg==0){
		alert('Fields are required');
	}else{
		param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&jenis='+jenis+'&waktu='+waktu+'&kg='+kg+'&keterangan='+keterangan;
		param+='&kodeorglama='+kodeorglama+'&tanggallama='+tanggallama+'&jenislama='+jenislama;
		param+='&addedit='+addedit+'&proses=saveData';
		tujuan='kebun_adjbrondol_slave.php';
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

function fillfield(kodeorg,tanggal,jenis,waktu,kg,keterangan){
	document.getElementById('unit').value=kodeorg.substring(0, 4);
	document.getElementById('divisi').value=kodeorg.substring(0, 6);
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeorglama').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tanggallama').value=tanggal;
	document.getElementById('jenis').value=jenis;
	document.getElementById('jenislama').value=jenis;
	document.getElementById('waktu').value=waktu;
	document.getElementById('kg').value=kg;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
}

function bersihkanForm(){
	document.getElementById('kodeorg').value='';
	document.getElementById('kodeorglama').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggallama').value='';
	document.getElementById('jenis').value='';
	document.getElementById('jenislama').value='';
	document.getElementById('waktu').value='';
	document.getElementById('kg').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,tanggal,jenis){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&jenis='+jenis;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'kebun_adjbrondol_slave.php';
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

function getDivisi(){
	unit=document.getElementById('unit').value; 
    param='unit='+unit;
    param+='&proses=getDivisi';
	tujuan='kebun_adjbrondol_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    //document.getElementById('namainv').innerHTML=con.responseText;
					if(con.responseText!=''){
						document.getElementById('divisi').innerHTML=con.responseText;
						getBlok();
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getBlok(){
	unit=document.getElementById('unit').value; 
	divisi=document.getElementById('divisi').value; 
    param='unit='+unit+'&divisi='+divisi;
    param+='&proses=getBlok';
	tujuan='kebun_adjbrondol_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					if(con.responseText!=''){
						//document.getElementById('kodeorg').value=con.responseText;
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

function preview_BAPDF(kodeorg,tanggal,jenis,ev){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&jenis='+jenis;
	tujuan='kebun_adjbrondol_BAPDF.php?'+param;
	title='Print BA '+kodeorg+' '+tanggal+' '+jenis;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}

function cariinventaris(type,ev){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1=document.getElementById('caritanggal1').value;
	caritanggal2=document.getElementById('caritanggal2').value;
	carijenis	=document.getElementById('carijenis').options[document.getElementById('carijenis').selectedIndex].value;
	page		=document.getElementById('page').value;
	param='carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carijenis='+carijenis+'&type='+type;
	tujuan='kebun_adjbrondol_showpopup.php?'+param;
	title='Adjustment Brondolam '+carikodeorg+' '+carijenis;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}
