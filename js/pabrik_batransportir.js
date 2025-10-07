/*
 * jakarta indonesia
 */

function loadData(){
    param='method=loadData';
    tujuan='pabrik_slave_batransportir.php';
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

function getSPP(){
    trpcode=document.getElementById('trpcode').value;
    param='trpcode='+trpcode+'&method=getSPP';
	//alert(param);
    tujuan='pabrik_slave_batransportir.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('nospp').innerHTML=con.responseText;
					//document.getElementById('nospp').value=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function getNo(){
    notransaksi=document.getElementById('notransaksi').value;
    tanggal=document.getElementById('tanggal').value;
    trpcode=document.getElementById('trpcode').value;
    nospp=document.getElementById('nospp').value;
	if(tanggal=='' || trpcode=='' || nospp=='' || notransaksi!=''){
	}else{
		param='nospp='+nospp+'&method=getNo'+'&tanggal='+tanggal+'&trpcode='+trpcode;
		//alert(param);
		tujuan='pabrik_slave_batransportir.php';
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('notransaksi').innerHTML=con.responseText;
						document.getElementById('notransaksi').value=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		}	
	}
}

function getJmlBA(){
    jumlahkg=document.getElementById('jumlahkg').value;
    hargakg=document.getElementById('hargakg').value;
	document.getElementById('jmlharga').value=(jumlahkg*hargakg).toFixed(2);
	document.getElementById('ppnba').value=(jumlahkg*hargakg*0.11).toFixed(2);
	document.getElementById('pphba').value=(jumlahkg*hargakg*0.02).toFixed(2);
	document.getElementById('ttlharga').value=((jumlahkg*hargakg)+(jumlahkg*hargakg*0.11)-(jumlahkg*hargakg*0.02)).toFixed(2);
	getNo();
}

function getTtlBA(){
    jumlahkg=document.getElementById('jumlahkg').value;
    hargakg=document.getElementById('hargakg').value;
    jmlharga=document.getElementById('jmlharga').value;
    ppnba=document.getElementById('ppnba').value;
    pphba=document.getElementById('pphba').value;
	ttlharga=parseFloat(jmlharga)+parseFloat(ppnba)-parseFloat(pphba);
	document.getElementById('ttlharga').value=ttlharga.toFixed(2);
	getNo();
}

function getKG(){
    tanggal=document.getElementById('tanggal').value;
    trpcode=document.getElementById('trpcode').value;
    nospp=document.getElementById('nospp').value;
    param='nospp='+nospp+'&method=getKG'+'&tanggal='+tanggal+'&trpcode='+trpcode;
	//alert(param);
    tujuan='pabrik_slave_batransportir.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('jumlahkg').innerHTML=con.responseText;
					document.getElementById('jumlahkg').value=con.responseText;
					document.getElementById('jmlharga').value=(document.getElementById('jumlahkg').value*document.getElementById('hargakg').value).toFixed(2);
					jmlharga=document.getElementById('jmlharga').value;
					ppnba=document.getElementById('ppnba').value;
					pphba=document.getElementById('pphba').value;
					ttlharga=parseFloat(jmlharga)+parseFloat(ppnba)-parseFloat(pphba);
					document.getElementById('ttlharga').value=ttlharga.toFixed(2);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function bersihkanForm(){
	var d = new Date();
    var tgl = d.getDate();
    var bln = d.getMonth() + 1;
    var thn = d.getFullYear();
    if (tgl < 10) {
        tgl = "0" + tgl;
    }
    if (bln < 10) {
        bln = "0" + bln;
    }
    var tanggal = tgl + "-" + bln + "-" + thn;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('trpcode').value='';
	document.getElementById('nospp').value='';
	document.getElementById('jumlahkg').value=0;
	document.getElementById('hargakg').value=0;
	document.getElementById('jmlharga').value=0;
	document.getElementById('ppnba').value=0;
	document.getElementById('pphba').value=0;
	document.getElementById('ttlharga').value=0;
	document.getElementById('notransaksi').value='';
}

function fillfield(kodeorg,notransaksi,tanggal,koderekanan,nokontrak,keterangan,hasilkerjarealisasi,jumlahrealisasi,nilaippn,nilaipph){
	if(nilaippn==''){
		nilaippn=0
	}
	if(nilaipph==''){
		nilaipph=0
	}
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('trpcode').value=koderekanan;
	getSPP(keterangan);
	//document.getElementById('nospp').options[document.getElementById('nospp').selectedIndex].value=keterangan;
	document.getElementById('nospp').value=keterangan;
	//document.getElementById('jumlahkg').value=(hasilkerjarealisasi*1).toLocaleString();
	//document.getElementById('hargakg').value=(jumlahrealisasi/hasilkerjarealisasi).toLocaleString();
	//document.getElementById('jmlharga').value=(jumlahrealisasi*1).toLocaleString();
	document.getElementById('jumlahkg').value=(hasilkerjarealisasi*1).toFixed();
	document.getElementById('hargakg').value=(jumlahrealisasi/hasilkerjarealisasi).toFixed();
	document.getElementById('jmlharga').value=(jumlahrealisasi*1).toFixed(2);
	document.getElementById('ppnba').value=(nilaippn*1).toFixed(2);
	document.getElementById('pphba').value=(nilaipph*1).toFixed(2);
	document.getElementById('ttlharga').value=(parseFloat(jumlahrealisasi)+parseFloat(nilaippn)-parseFloat(nilaipph)).toFixed(2);
}

function deldata(notransaksi){
	param='notransaksi='+notransaksi;
	param+='&del=true';
	if (confirm('Delete No. '+notransaksi+' ?')){
		tujuan = 'pabrik_slave_save_batransportir.php';
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					loadData();
					bersihkanForm();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function simpanData(){
	getNo();
	notransaksi	=document.getElementById('notransaksi').value;
	kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;
	trpcode 	=document.getElementById('trpcode').options[document.getElementById('trpcode').selectedIndex].value;
	nospp	 	=document.getElementById('nospp').options[document.getElementById('nospp').selectedIndex].value;
	jumlahkg	=document.getElementById('jumlahkg').value;
	hargakg		=document.getElementById('hargakg').value;
	jmlharga	=document.getElementById('jmlharga').value;
	ppnba		=document.getElementById('ppnba').value;
	pphba		=document.getElementById('pphba').value;
	if(notransaksi=='' || kodeorg=='' || tanggal=='' || trpcode=='' || nospp=='' || jumlahkg=='' || jumlahkg==0 || hargakg=='' || hargakg==0 || jmlharga=='' || jmlharga==0){
		alert('All fields are required');
	}else{
		param='notransaksi='+notransaksi+'&kodeorg='+kodeorg+'&tanggal='+tanggal;
		param+='&trpcode='+trpcode+'&nospp='+nospp+'&jumlahkg='+jumlahkg+'&hargakg='+hargakg+'&jmlharga='+jmlharga+'&ppnba='+ppnba+'&pphba='+pphba;
		tujuan='pabrik_slave_save_batransportir.php';
		post_response_text(tujuan, param, respog);		
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
					loadData();
					bersihkanForm();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function cariSPP(num){
	noKontrakCr=document.getElementById('noKontrakCr').value;
    nosppCr=document.getElementById('nosppCr').value;
    trpcodeCr=document.getElementById('trpcodeCr').value;
    notransaksiCr=document.getElementById('notransaksiCr').value;

	param='method=loadData';
	if(noKontrakCr!=''){
        param+='&noKontrakCr='+noKontrakCr;
    }
	if(nosppCr!=''){
        param+='&nosppCr='+nosppCr;
    }
	if(trpcodeCr!=''){
        param+='&trpcodeCr='+trpcodeCr;
    }
	if(notransaksiCr!=''){
        param+='&notransaksiCr='+notransaksiCr;
    }
	param+='&page='+num;
	tujuan='pabrik_slave_batransportir.php';
	post_response_text(tujuan, param, respog);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
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

function displatList(){
	document.getElementById('noKontrakCr').value='';
    document.getElementById('nosppCr').value='';
    document.getElementById('trpcodeCr').value='';
    document.getElementById('notransaksiCr').value='';
    loadData();
}

function showDetail(kdorg,notransaksi,nospp,trpname,ev){
	title="Data Detail";
    content="<fieldset><legend>Unit : "+kdorg+", No. Transaksi "+notransaksi+", No. SPP "+nospp+", Transportir "+trpname+"</legend><div id=contDetail style='overflow:auto; width:970px; height:365px;' ></div></fieldset>";
	width='1000';
	height='400';
	showDialog1(title,content,width,height,ev);	
}

function showDetail2(kdorg,notransaksi,nospp,trpname,ev){
	title="Data Detail";
    content="<fieldset><legend>Unit : "+kdorg+", No. Transaksi "+notransaksi+", No. SPP "+nospp+", Transportir "+trpname+"</legend><div id=contDetail style='overflow:auto; width:970px; height:365px;' ></div></fieldset>";
	width='1000';
	height='400';
	showDialog2(title,content,width,height,ev);	
}

function previewDetail(kdorg,notransaksi,nospp,trpcode,trpname,ev,vw){
	if(vw=='excel'){
		showDetail2(kdorg,notransaksi,nospp,trpname,ev);
	}else{
		showDetail(kdorg,notransaksi,nospp,trpname,ev);
	}
	param='kdorg='+kdorg+'&notransaksi='+notransaksi+'&nospp='+nospp+'&trpcode='+trpcode+'&method=getDetailBA'+'&vw='+vw;
	tujuan='pabrik_slave_batransportir.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('contDetail').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function previewBA(kodeorg,notransaksi,ev){
	param='kodeorg='+kodeorg+'&notransaksi='+notransaksi;
	tujuan = 'pabrik_slave_BA_print_pdf.php?'+param;
	title=notransaksi;
	width='700';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}

function postingData(kodeorg,koderekanan,notransaksi,blok,kodesegment,kodekegiatan,blokalokasi,tanggal,hasilkerjarealisasi,jumlahrealisasi){
    var param = "kodeorg="+kodeorg+"&koderekanan="+koderekanan;
    param += "&notransaksi="+notransaksi+"&kodeblok="+blok+"&kodesegment="+kodesegment+"&kodekegiatan="+kodekegiatan;
    param += "&blokalokasi="+blokalokasi;
    param += "&tanggal="+tanggal;
    param += "&hasilkerjarealisasi="+hasilkerjarealisasi;
    param += "&jumlahrealisasi="+jumlahrealisasi;
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
					document.getElementById('container').innerHTML=con.responseText;
					loadData();
					bersihkanForm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Akan dilakukan posting untuk No. '+notransaksi+' Onces posted the data can not be changed, are you sure?')) {
        post_response_text('log_slave_realisasispk_posting.php', param, respon);
    }
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}

function dataKeExcel(kdorg,notransaksi,nospp,trpcode,trpname,ev,vw){
	param='kdorg='+kdorg+'&notransaksi='+notransaksi+'&nospp='+nospp+'&trpcode='+trpcode+'&method=dataDetail'+'&vw='+vw;
	tujuan='pabrik_slave_batransportir_excel.php';
    judul='Data Detail';	
    printFile(param,tujuan,judul,ev)	
}
