/**
 * Kebun Kontrol Ancak Panen
 */

function loadData(num){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1=document.getElementById('caritanggal1').value;
	caritanggal2=document.getElementById('caritanggal2').value;
	carikary	=document.getElementById('carikary').options[document.getElementById('carikary').selectedIndex].value;
	kodeorg		=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='proses=loadData'+'&kodeorg='+kodeorg;
	param+='&carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikary='+carikary;
	param+='&page='+num;
	tujuan='kebun_cekancak_slave.php';
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
	diperiksa 	=document.getElementById('diperiksa').options[document.getElementById('diperiksa').selectedIndex].value;
	diperiksalama=document.getElementById('diperiksalama').value;
	pokok	  	=document.getElementById('pokok').value;
	brondolan  	=document.getElementById('brondolan').value;
	janjang  	=document.getElementById('janjang').value;
	keterangan 	=document.getElementById('keterangan').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  tanggal=='' ||  diperiksa=='' ||  diperiksa==0){
		alert('Fields are required');
	}else{
		param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&diperiksa='+diperiksa+'&pokok='+pokok+'&brondolan='+brondolan+'&janjang='+janjang;
		param+='&keterangan='+keterangan;
		param+='&kodeorglama='+kodeorglama+'&tanggallama='+tanggallama+'&diperiksalama='+diperiksalama;
		param+='&addedit='+addedit+'&proses=saveData';
		tujuan='kebun_cekancak_slave.php';
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

function fillfield(kodeorg,tanggal,diperiksa,pokok,brondolan,janjang,keterangan){
	document.getElementById('unit').value=kodeorg.substring(0, 4);
	document.getElementById('divisi').value=kodeorg.substring(0, 6);
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeorglama').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tanggallama').value=tanggal;
	document.getElementById('diperiksa').value=diperiksa;
	document.getElementById('diperiksalama').value=diperiksa;
	document.getElementById('pokok').value=pokok;
	document.getElementById('brondolan').value=brondolan;
	document.getElementById('janjang').value=janjang;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
	unit=document.getElementById('unit').value; 
	divisi=document.getElementById('divisi').value; 
    param='unit='+unit+'&divisi='+divisi;
    param+='&proses=getBlok';
	tujuan='kebun_cekancak_slave.php';
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
						document.getElementById('kodeorg').value=kodeorg;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function bersihkanForm(){
	document.getElementById('kodeorg').value='';
	document.getElementById('kodeorglama').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggallama').value='';
	document.getElementById('diperiksa').value='';
	document.getElementById('diperiksalama').value='';
	document.getElementById('pokok').value=0;
	document.getElementById('brondolan').value=0;
	document.getElementById('janjang').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,tanggal,diperiksa){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&diperiksa='+diperiksa;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'kebun_cekancak_slave.php';
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
	tujuan='kebun_cekancak_slave.php';
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
						//document.getElementById('carikodeorg').innerHTML=con.responseText;
						getBlok();
						getKary();
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
	tujuan='kebun_cekancak_slave.php';
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

function getKary(){
	unit=document.getElementById('unit').value; 
    param='unit='+unit;
    param+='&proses=getKary';
	tujuan='kebun_cekancak_slave.php';
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
						document.getElementById('diperiksa').innerHTML=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function preview_PDF(kodeorg,tanggal,diperiksa,ev){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&diperiksa='+diperiksa;
	tujuan='kebun_cekancak_PDF.php?'+param;
	title='Kontrol Ancak Panen '+kodeorg+' '+tanggal+' '+diperiksa;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}

function cariinventaris(type,ev){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1=document.getElementById('caritanggal1').value;
	caritanggal2=document.getElementById('caritanggal2').value;
	carikary	=document.getElementById('carikary').options[document.getElementById('carikary').selectedIndex].value;
	page		=document.getElementById('page').value;
	param='carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikary='+carikary+'&type='+type;
	tujuan='kebun_cekancak_showpopup.php?'+param;
	title='Kontrol Ancak Panen '+carikodeorg+' '+carikary;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}
