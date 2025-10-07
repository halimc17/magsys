function saveData(fileTarget,passParam) {
    var passP = passParam.split('##');
    var param = ""
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	//alert(param);
    function respon() {
		if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    //loadData();
                    //clearData();
					//alert(con.responseText);
					document.getElementById('noinvoice').value=con.responseText;
					document.getElementById('formDetail').style.display='block';
                    loadDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function displayFormInput(){
	clearData();
	param='proses=genNo';
	tujuan='keu_invoice_komoditi_slave';
	post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                        document.getElementById('formInput').style.display='block';
                        document.getElementById('listData').style.display='none';
                        document.getElementById('noinvoice').value='';
						clearData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function loadData(page){
    ntrs=document.getElementById('txtsearch').value;
	tglcr=document.getElementById('tgl_cari').value;
	kdbar=document.getElementById('ptKomoditi').value;
	ncust=document.getElementById('ptCust').value;
    param='proses=loadData'+'&page='+page;
    if(ntrs!=''){
        param+='&noinvoiceCr='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    if(kdbar!=''){
        param+='&kodebarangCr='+kdbar;
    }
    if(ncust!=''){
        param+='&kodecustomerCr='+ncust;
    }
    tujuan='keu_invoice_komoditi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else {
					isdt=con.responseText.split("####");
					document.getElementById('formInput').style.display='none';
					document.getElementById('listData').style.display='block';
					document.getElementById('continerlist').innerHTML=isdt[0];
					document.getElementById('footData').innerHTML=isdt[1];
					document.getElementById('formDetail').style.display='none';
					//clearData();
					// closeDialog();
				}
            }else {
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function fillField(noinv){
    param='proses=getData'+'&noinvoice='+noinv;
    tujuan='keu_invoice_komoditi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else {
					//alert(con.responseText);
					document.getElementById('formInput').style.display='block';
					document.getElementById('listData').style.display='none';
					isis=con.responseText.split("###");
					document.getElementById('noinvoice').value=isis[0];
					document.getElementById('kodeorganisasi').value=isis[1];
					byrke=document.getElementById('bayarke');
					for(a=0;a<byrke.length;a++){
						if(byrke.options[a].value==isis[2]){
							byrke.options[a].selected=true;
						}
					}
					document.getElementById('tanggal').value=isis[3];
					document.getElementById('jatuhtempo').value=isis[4];
					kdcst=document.getElementById('kodecustomer');
					for(a=0;a<kdcst.length;a++){
						if(kdcst.options[a].value==isis[5]){
							kdcst.options[a].selected=true;
						}
					}
					document.getElementById('nokontrak').value=isis[6];
					if(isis[6]!=''){
						document.getElementById('nokontrak').disabled=true;
						document.getElementById('kodeorganisasi').disabled=true;
						document.getElementById('kodecustomer').disabled=true;
						document.getElementById('matauang').disabled=true;
						document.getElementById('kurs').disabled=true;
					}else{
						document.getElementById('nokontrak').disabled=false;
						document.getElementById('kodeorganisasi').disabled=false;
						document.getElementById('kodecustomer').disabled=false;
						document.getElementById('matauang').disabled=false;
						document.getElementById('kurs').disabled=false;
					}
					document.getElementById('matauang').value=isis[7];
					document.getElementById('kurs').value=isis[8];
					document.getElementById('ttd').value=isis[9];
					document.getElementById('jenis').value=isis[10];
					dbt=document.getElementById('debet');
					for(a=0;a<dbt.length;a++){
						if(dbt.options[a].value==isis[11]){
							dbt.options[a].selected=true;
						}
					}
					kridit=document.getElementById('kredit');
					for(a=0;a<kridit.length;a++){
						if(kridit.options[a].value==isis[12]){
							kridit.options[a].selected=true;
						}
					}
					document.getElementById('keterangan').value=isis[13];
					document.getElementById('formDetail').style.display='block';
					loadDetail();
					//document.getElementById('stsppn').value=isis[13];
					//document.getElementById('nilaippn').value=isis[14];
					//document.getElementById('stspph').value=isis[15];
					//document.getElementById('nilaipph').value=isis[16];
					//document.getElementById('ongkoskirim').value=isis[17];
					//getPajak();
				}
			}else {
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function popUpPosting(title,noinvoice,content,ev){
	width='400';
	height='100';
	showDialog2(title,content,width,height,ev);
	getFormAfiliasi(noinvoice);
	//alert('test masuk');
}

function getKursInvoice(){
	matauang=document.getElementById('matauang').value;
	tanggal=document.getElementById('tanggal').value;
	param='proses=getKursInvoice'+'&matauang='+matauang+'&tanggal='+tanggal;
	tujuan='keu_invoice_komoditi_slave.php';
	post_response_text(tujuan+'?'+'', param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					document.getElementById('tanggal').value='';
                                        document.getElementById('matauang').value='IDR';
				}else {
					// alert(con.responseText);
					document.getElementById('kurs').value=con.responseText; 
					//getNilai();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getNilai(){
	nilaippn=document.getElementById('nilaippn').value;
	nilaipph=document.getElementById('nilaipph').value;
	//nilaiinvoice=document.getElementById('nilaiinvoice').value;
    nilaiinvoice=document.getElementById('nilaiinvoice');
	nilaiinvoice.value=remove_comma_var(nilaiinvoice.value);
	nilaiinvoice=nilaiinvoice.value;
	kurs=document.getElementById('kurs').value;
	matauang=document.getElementById('matauang').value;
	param='proses=getNilai&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&nilaiinvoice='+nilaiinvoice+'&kurs='+kurs+'&matauang='+matauang;
	tujuan='keu_invoice_komoditi_slave.php';
	if(confirm('anda yankin mengganti mata uang??'))
		post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					// alert(con.responseText);
					ar=con.responseText.split("###");
					document.getElementById('nilaippn').value=ar[0]; 
					document.getElementById('nilaiinvoice').value=ar[1]; 
					document.getElementById('nilaipph').value=ar[2]; 
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getFormAfiliasi(noinvoice){
	param='proses=getFormAfiliasi&noinvoice='+noinvoice;
	tujuan='keu_invoice_komoditi_slave.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					//alert(con.responseText);
					document.getElementById('formaAfiliasi').innerHTML=con.responseText;
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function inputAfiliasi(noinvoice){
	noafiliasi=trim(document.getElementById('noafiliasi').value);
	param='noafiliasi='+noafiliasi+'&noinvoice='+noinvoice+'&proses=inputNoAfiliasi';
	tujuan='keu_invoice_komoditi_slave.php';
	if(noafiliasi==''){
		alert("No Invoice Afiliasi is obligatory");
	} else {
		post_response_text(tujuan, param, respog);
	}
        
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					getPage();
					closeDialog2();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancelData(){
	document.getElementById('formInput').style.display='none';
	document.getElementById('listData').style.display='block';
	clearData();
}

function clearData(){
	document.getElementById('txtsearch').value="";
	document.getElementById('tgl_cari').value="";
	document.getElementById('ptKomoditi').value="";
	document.getElementById('ptCust').value="";

	document.getElementById('noinvoice').value='';
	//document.getElementById('kodeorganisasi').value='';
	document.getElementById('kodeorganisasi').disabled=false;
	document.getElementById('bayarke').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('jatuhtempo').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('kodecustomer').disabled=false;
	document.getElementById('nokontrak').value='';
	document.getElementById('nokontrak').disabled=false;
	document.getElementById('matauang').value='IDR';
	document.getElementById('matauang').disabled=false;
	document.getElementById('kurs').value=1;
	document.getElementById('kurs').disabled=false;
	//document.getElementById('ttd').value="";
	//setValue('ttd',"Rizki Hernanda Daslia");
	//document.getElementById('jenis').value="";
	document.getElementById('debet').value='';
	document.getElementById('kredit').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('formDetail').style.display='none';
}

function delData(notrans){
	param='noinvoice='+notrans+'&proses=delData';
	tujuan='keu_invoice_komoditi_slave.php';
	if(confirm("Anda yakin menghapus no invoice ini?"+ notrans)){
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					//alert(con.responseText);
					getPage();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingData(notrans){
	param='noinvoice='+notrans+'&proses=postingData';
	tujuan='keu_invoice_komoditi_slave.php';
	if(confirm("Anda yakin memposting no invoice ini?"+ notrans)){
		post_response_text(tujuan, param, respog);
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					//alert(con.responseText);
					getPage();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPajak(){
	nilaiinvoice=parseFloat(remove_comma_var(document.getElementById('nilaiinvoice').value));
	nilaippn=parseFloat(remove_comma_var(document.getElementById('nilaippn').value));
	nilaipph=parseFloat(remove_comma_var(document.getElementById('nilaipph').value));
	ongkoskirim=parseFloat(remove_comma_var(document.getElementById('ongkoskirim').value));
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	pajak=0;
	if(stsppn=='2'){//Excl PPN
		document.getElementById('nilaippn').value=(nilaiinvoice*0.11).toFixed(2);
	}else if(stsppn=='1'){//Incl PPN
		document.getElementById('nilaippn').value=(nilaiinvoice*0.11).toFixed(2);
	}else{//Non PPN
		document.getElementById('nilaippn').value=pajak.toFixed(2);
	}

	if(stspph=='1160200'){//PPh Pasal 22
		document.getElementById('nilaipph').value=(nilaiinvoice*0.0025).toFixed(2);
	}else if(stspph=='1160400'){//PPh Pasal 23
		document.getElementById('nilaipph').value=(nilaiinvoice*0.02).toFixed(2);
	}else if(stspph=='1160500'){//PPh Pasal 4 Ayat 2
		document.getElementById('nilaipph').value=(nilaiinvoice*0.05).toFixed(2);
	}else{//Non PPh
		document.getElementById('nilaipph').value=pajak.toFixed(2);
	}
	document.getElementById('totalinvoice').value=(nilaiinvoice+parseFloat(document.getElementById('nilaippn').value)-parseFloat(document.getElementById('nilaipph').value)+ongkoskirim).toFixed(2);
	//alert(stsppn+' '+nilaiinvoice);
}

function loadDetail(){
    noinvoice=document.getElementById('noinvoice').value;
	param='noinvoice='+noinvoice;
	param+='&proses=loadDetail';
	tujuan='keu_invoice_komoditi_slave.php';
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

function simpandetail(noinvoice,nokontrak){
	kodebarang=document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
	nilaiinventory=document.getElementById('nilaiinventory').value;
	hargasatuan=document.getElementById('hargasatuan').value;
	nilaitransaksi=document.getElementById('nilaitransaksi').value;
	param='proses=simpandetail'+'&noinvoice='+noinvoice+'&nokontrak='+nokontrak+'&kodebarang='+kodebarang+'&nilaiinventory='+nilaiinventory+'&hargasatuan='+hargasatuan+'&nilaitransaksi='+nilaitransaksi;
	//alert(param);
	tujuan='keu_invoice_komoditi_slave.php';
    post_response_text(tujuan, param, respog);		
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loadDetail();
					getPajak();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function deldetail(noinvoice,kodebarang){
	param='proses=deldetail'+'&noinvoice='+noinvoice+'&kodebarang='+kodebarang;
	tujuan='keu_invoice_komoditi_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loadDetail();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function editdetail(noinvoice,no){
	document.getElementById('kodebarang'+no).disabled=false;
	document.getElementById('nilaiinventory'+no).disabled=false;
	document.getElementById('hargasatuan'+no).disabled=false;
}

function savedetail(noinvoice,oldkodebarang,no){
	if(!document.getElementById('kodebarang'+no).disabled){
		kodebarang=document.getElementById('kodebarang'+no).value;
		snilaiinventory=document.getElementById('nilaiinventory'+no).value;
		//nilaiinventory=snilaiinventory.replace(",", "");
		nilaiinventory=remove_comma_var(snilaiinventory);
		shargasatuan=document.getElementById('hargasatuan'+no).value;
		//hargasatuan=shargasatuan.replace(",", "");
		hargasatuan=remove_comma_var(shargasatuan);
		snilaitransaksi=document.getElementById('nilaitransaksi'+no).value;
		//nilaitransaksi=snilaitransaksi.replace(",", "");
		nilaitransaksi=remove_comma_var(snilaitransaksi);
		param='proses=savedetail'+'&noinvoice='+noinvoice+'&oldkodebarang='+oldkodebarang+'&kodebarang='+kodebarang+'&nilaiinventory='+nilaiinventory+'&hargasatuan='+hargasatuan+'&nilaitransaksi='+nilaitransaksi;
		tujuan='keu_invoice_komoditi_slave.php';
		post_response_text(tujuan, param, respog);		
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						loadDetail();
						getPajak();
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}

function getSatuan(no){
	kodebarang=document.getElementById('kodebarang'+no).value; 
    param='kodebarang='+kodebarang;
    param+='&proses=getSatuan';
	tujuan='keu_invoice_komoditi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
					if(con.responseText!=''){
						document.getElementById('satuan'+no).value=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getNilaiTransaksi(no){
	nilaiinventory=document.getElementById('nilaiinventory'+no).value;
	nilaiinventory=remove_comma_var(nilaiinventory);
	hargasatuan=document.getElementById('hargasatuan'+no).value;
	hargasatuan=remove_comma_var(hargasatuan);
	if(nilaiinventory==''){
		nilaiinventory=0;
	}
	if(hargasatuan==''){
		hargasatuan=0;
	}
	nilaitransaksi=parseFloat(nilaiinventory)*parseFloat(hargasatuan);
	document.getElementById('nilaitransaksi'+no).value=nilaitransaksi.toFixed(2);
}

function saveFoot(noinvoice){
	cnilaiinvoice=document.getElementById('nilaiinvoice').value;
	nilaiinvoice=remove_comma_var(cnilaiinvoice);
	cnilaippn=document.getElementById('nilaippn').value;
	nilaippn=remove_comma_var(cnilaippn);
	cnilaipph=document.getElementById('nilaipph').value;
	nilaipph=remove_comma_var(cnilaipph);
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	congkoskirim=document.getElementById('ongkoskirim').value;
	ongkoskirim=remove_comma_var(congkoskirim);
	catatan=document.getElementById('catatan').value;
	param='proses=saveFoot'+'&noinvoice='+noinvoice+'&nilaiinvoice='+nilaiinvoice+'&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&ongkoskirim='+ongkoskirim;
	param+='&stsppn='+stsppn+'&stspph='+stspph+'&catatan='+catatan;
	//alert(cnilaiinvoice+'   '+nilaiinvoice);
	tujuan='keu_invoice_komoditi_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					getPage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function saveBaru(noinvoice){
	cnilaiinvoice=document.getElementById('nilaiinvoice').value;
	nilaiinvoice=remove_comma_var(cnilaiinvoice);
	cnilaippn=document.getElementById('nilaippn').value;
	nilaippn=remove_comma_var(cnilaippn);
	cnilaipph=document.getElementById('nilaipph').value;
	nilaipph=remove_comma_var(cnilaipph);
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	congkoskirim=document.getElementById('ongkoskirim').value;
	ongkoskirim=remove_comma_var(congkoskirim);
	catatan=document.getElementById('catatan').value;
	param='proses=saveFoot'+'&noinvoice='+noinvoice+'&nilaiinvoice='+nilaiinvoice+'&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&ongkoskirim='+ongkoskirim;
	param+='&stsppn='+stsppn+'&stspph='+stspph+'&catatan='+catatan;
	//alert(cnilaiinvoice+'   '+nilaiinvoice);
	tujuan='keu_invoice_komoditi_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					displayFormInput();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getKontrakData(){
	nokontrak=document.getElementById('nokontrak').value;
	param='proses=getKontrakData'+'&nokontrak='+nokontrak;
	tujuan='keu_invoice_komoditi_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					// alert(con.responseText);
					ar=con.responseText.split("###");
					kdorg=document.getElementById('kodeorganisasi');
					for(a=0;a<kdorg.length;a++){
						if(kdorg.options[a].value==ar[0]){
							kdorg.options[a].selected=true;
						}
					}
					kdcust=document.getElementById('kodecustomer');
					for(a=0;a<kdcust.length;a++){
						if(kdcust.options[a].value==ar[1]){
							kdcust.options[a].selected=true;
						}
					}
					mtuang=document.getElementById('matauang');
					for(a=0;a<mtuang.length;a++){
						if(mtuang.options[a].value==ar[2]){
							mtuang.options[a].selected=true;
						}
					}
					document.getElementById('kurs').value=ar[3];
					if(nokontrak!=''){
						document.getElementById('kodeorganisasi').disabled=true;
						document.getElementById('kodecustomer').disabled=true;
						document.getElementById('matauang').disabled=true;
						document.getElementById('kurs').disabled=true;
					}else{
						document.getElementById('kodeorganisasi').disabled=false;
						document.getElementById('kodecustomer').disabled=false;
						document.getElementById('matauang').disabled=false;
						document.getElementById('kurs').disabled=false;
					}
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
