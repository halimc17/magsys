/**
 * Pabrik Invetaris Branag
 */

function loadData(num){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	carinamainv	=document.getElementById('carinamainv').value;
	carikaryawan=document.getElementById('carikaryawan').value;
	//carikaryawan=document.getElementById('carikaryawan').options[document.getElementById('carikaryawan').selectedIndex].value;
	kodeorg		=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	cariruangan	=document.getElementById('cariruangan').value;
	param='proses=loadData'+'&kodeorg='+kodeorg;
	param+='&carikodeorg='+carikodeorg+'&carinamainv='+carinamainv+'&carikaryawan='+carikaryawan+'&cariruangan='+cariruangan;
	param+='&page='+num;
	tujuan='log_invbarang_slave.php';
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
	kodebarang	=document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
	kodebrglama	=document.getElementById('kodebrglama').value;
	kodeinv  	=document.getElementById('kodeinv').value;
	kodeinvlama	=document.getElementById('kodeinvlama').value;
	namainv  	=document.getElementById('namainv').value;
	merkinv  	=document.getElementById('merkinv').value;
	tipeinv  	=document.getElementById('tipeinv').value;
	ketinv  	=document.getElementById('ketinv').value;
	ukuraninv  	=document.getElementById('ukuraninv').value;
	warnainv  	=document.getElementById('warnainv').value;
	bahaninv  	=document.getElementById('bahaninv').value;
	tglbeli  	=document.getElementById('tglbeli').value;
	hrgbeli  	=document.getElementById('hrgbeli').value;
	nopo  		=document.getElementById('nopo').value;
	kodesupplier=document.getElementById('kodesupplier').options[document.getElementById('kodesupplier').selectedIndex].value;
	nik			=document.getElementById('nik').options[document.getElementById('nik').selectedIndex].value;
	namakaryawan=document.getElementById('namakaryawan').value;
	tgldiuser  	=document.getElementById('tgldiuser').value;
	kondisi		=document.getElementById('kondisi').options[document.getElementById('kondisi').selectedIndex].value;
	divisi  	=document.getElementById('divisi').value;
	lokasi  	=document.getElementById('lokasi').value;
	ruangan  	=document.getElementById('ruangan').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  kodeinv=='' ||  namainv=='' ||  kondisi==''){
		alert('Fields are required');
	}else{
		param='kodeorg='+kodeorg+'&kodebarang='+kodebarang+'&kodeinv='+kodeinv+'&namainv='+namainv+'&merkinv='+merkinv+'&tipeinv='+tipeinv+'&ketinv='+ketinv;
		param+='&ukuraninv='+ukuraninv+'&warnainv='+warnainv+'&bahaninv='+bahaninv+'&tglbeli='+tglbeli+'&hrgbeli='+hrgbeli+'&nopo='+nopo;
		param+='&kodesupplier='+kodesupplier+'&nik='+nik+'&tgldiuser='+tgldiuser+'&kondisi='+kondisi+'&divisi='+divisi+'&lokasi='+lokasi+'&ruangan='+ruangan;
		param+='&namakaryawan='+namakaryawan;
		param+='&addedit='+addedit+'&proses=saveData'+'&kodeorglama='+kodeorglama+'&kodebrglama='+kodebrglama+'&kodeinvlama='+kodeinvlama;
		tujuan='log_invbarang_slave.php';
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

function fillfield(kodeorg,kodebarang,kodeinv,namainv,merkinv,tipeinv,ketinv,ukuraninv,warnainv,bahaninv,tglbeli,hrgbeli,nopo,kodesupplier,nik,namakaryawan,tgldiuser,kondisi,divisi,lokasi,ruangan){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeorglama').value=kodeorg;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('kodebrglama').value=kodebarang;
	document.getElementById('kodeinv').value=kodeinv;
	document.getElementById('kodeinvlama').value=kodeinv;
	document.getElementById('namainv').value=namainv;
	document.getElementById('merkinv').value=merkinv;
	document.getElementById('tipeinv').value=tipeinv;
	document.getElementById('ketinv').value=ketinv;
	document.getElementById('ukuraninv').value=ukuraninv;
	document.getElementById('warnainv').value=warnainv;
	document.getElementById('bahaninv').value=bahaninv;
	document.getElementById('tglbeli').value=tglbeli;
	document.getElementById('hrgbeli').value=hrgbeli;
	document.getElementById('nopo').value=nopo;
	document.getElementById('kodesupplier').value=kodesupplier;
	document.getElementById('nik').value=nik;
	document.getElementById('namakaryawan').value=namakaryawan;
	document.getElementById('tgldiuser').value=tgldiuser;
	document.getElementById('kondisi').value=kondisi;
	document.getElementById('divisi').value=divisi;
	document.getElementById('lokasi').value=lokasi;
	document.getElementById('ruangan').value=ruangan;
	document.getElementById('addedit').value='update';
}

function bersihkanForm(){
	//document.getElementById('kodeorg').value='';
	//document.getElementById('kodeorglama').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('kodebrglama').value='';
	document.getElementById('kodeinv').value='';
	document.getElementById('kodeinvlama').value='';
	document.getElementById('namainv').value='';
	document.getElementById('merkinv').value='';
	document.getElementById('tipeinv').value='';
	document.getElementById('ketinv').value='';
	document.getElementById('ukuraninv').value='';
	document.getElementById('warnainv').value='';
	document.getElementById('bahaninv').value='';
	document.getElementById('tglbeli').value='';
	document.getElementById('hrgbeli').value=0;
	document.getElementById('nopo').value='';
	document.getElementById('kodesupplier').value='';
	document.getElementById('nik').value='';
	document.getElementById('namakaryawan').value='';
	document.getElementById('tgldiuser').value='';
	//document.getElementById('kondisi').value='Baik';
	document.getElementById('divisi').value='';
	document.getElementById('lokasi').value='';
	document.getElementById('ruangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,kodebarang,kodeinv){
	param='kodeorg='+kodeorg+'&kodebarang='+kodebarang+'&kodeinv='+kodeinv;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'log_invbarang_slave.php';
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

function getBarang(){
	kodebarang=document.getElementById('kodebarang').value; 
    param='kodebarang='+kodebarang;
    param+='&proses=getBarang';
	tujuan='log_invbarang_slave.php';
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
						document.getElementById('namainv').value=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getKaryawan(){
	nik=document.getElementById('nik').value; 
    param='nik='+nik;
    param+='&proses=getKaryawan';
	tujuan='log_invbarang_slave.php';
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
						document.getElementById('namakaryawan').value=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function showpopup(kodeorg,kodebarang,kodeinv,namainv,type,ev){
	param='kodeorg='+kodeorg+'&kodebarang='+kodebarang+'&kodeinv='+kodeinv+'&type='+type;
	tujuan='log_invbarang_showpopup.php'+"?"+param;
	width='1200';
	height='470';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1('Invetaris Barang '+namainv+' '+kodeorg+' '+kodebarang+' '+kodeinv,content,width,height,ev); 
}

function previewQRCode(kodeorg,kodebarang,kodeinv,namainv,nik,ruangan,type,ev){
	param='kodeorg='+kodeorg+'&kodebarang='+kodebarang+'&kodeinv='+kodeinv+'&namainv='+namainv+'&nik='+nik+'&ruangan='+ruangan+'&type='+type;
	tujuan='log_invbarang_pdfqrcode.php?'+param;
	title='QR Code '+namainv+' '+kodeorg+' '+kodebarang+' '+kodeinv;
	width='700';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}

function cariinventaris(type,ev){
	carikodeorg	=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	carinamainv	=document.getElementById('carinamainv').value;
	carikaryawan=document.getElementById('carikaryawan').value;
	//carikaryawan=document.getElementById('carikaryawan').options[document.getElementById('carikaryawan').selectedIndex].value;
	cariruangan	=document.getElementById('cariruangan').value;
	page		=document.getElementById('page').value;
	if(type=='pdf'){
		previewQRCode(carikodeorg,'','',carinamainv,carikaryawan,cariruangan,type,ev);
	}else if(type=='qrxls'){
		param='&carikodeorg='+carikodeorg+'&carinamainv='+carinamainv+'&carikaryawan='+carikaryawan+'&cariruangan='+cariruangan+'&page='+page+'&type=excel';
		tujuan='log_invbarang_excelqrcode.php?'+param;
		title='QR Code Excel '+carikaryawan+' '+carinamainv+' '+carikodeorg+' '+cariruangan;
		width='1180';
		height='400';
		content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
		showDialog1(title,content,width,height,ev);
	}else{
		param='&carikodeorg='+carikodeorg+'&carinamainv='+carinamainv+'&carikaryawan='+carikaryawan+'&cariruangan='+cariruangan+'&type='+type;
		tujuan='log_invbarang_showpopup.php?'+param;
		title='BA Inventaris '+carikaryawan+' '+carinamainv+' '+carikodeorg+' '+cariruangan;
		width='1180';
		height='400';
		content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
		showDialog1(title,content,width,height,ev);
	}
}
