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
					document.getElementById('nokontrak').value=con.responseText;
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
	param='proses=genNo';
	tujuan='pmn_bajualbeli_slave';
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
					document.getElementById('nokontrak').value='';
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
        param+='&nokontrakCr='+ntrs;
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
    tujuan='pmn_bajualbeli_slave.php';
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

function fillField(nokontrak){
    param='proses=getData'+'&nokontrak='+nokontrak;
    tujuan='pmn_bajualbeli_slave.php';
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
					document.getElementById('nokontrak').value=isis[0];
					kdorg=document.getElementById('kodeorg');
					for(a=0;a<kdorg.length;a++){
						if(kdorg.options[a].value==isis[1]){
							kdorg.options[a].selected=true;
						}
					}
					document.getElementById('tanggalkontrak').value=isis[2];
					kdcst=document.getElementById('koderekanan');
					for(a=0;a<kdcst.length;a++){
						if(kdcst.options[a].value==isis[3]){
							kdcst.options[a].selected=true;
						}
					}
					kdfrc=document.getElementById('franco');
					for(a=0;a<kdfrc.length;a++){
						if(kdfrc.options[a].value==isis[4]){
							kdfrc.options[a].selected=true;
						}
					}
					document.getElementById('tanggalkirim').value=isis[5];
					document.getElementById('sdtanggal').value=isis[6];
					kdterm=document.getElementById('kdtermim');
					document.getElementById('kdtermin').value=isis[7];
					document.getElementById('rekening').value=isis[8];
					document.getElementById('matauang').value=isis[9];
					document.getElementById('kurs').value=isis[10];
					document.getElementById('kualitas10').value=isis[11];
					document.getElementById('kualitas11').value=isis[12];
					document.getElementById('kualitas12').value=isis[13];
					document.getElementById('kualitas20').value=isis[14];
					document.getElementById('kualitas21').value=isis[15];
					document.getElementById('kualitas22').value=isis[16];
					document.getElementById('kualitas30').value=isis[17];
					document.getElementById('kualitas31').value=isis[18];
					document.getElementById('kualitas32').value=isis[19];
					document.getElementById('penandatangan').value=isis[20];
					document.getElementById('formDetail').style.display='block';
					loadDetail();
				}
			}else {
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function popUpPosting(title,nokontrak,content,ev){
	width='400';
	height='100';
	showDialog2(title,content,width,height,ev);
	getFormAfiliasi(nokontrak);
	//alert('test masuk');
}

function getKursInvoice(){
	matauang=document.getElementById('matauang').value;
	tanggal=document.getElementById('tanggalkontrak').value;
	param='proses=getKursInvoice'+'&matauang='+matauang+'&tanggalkontrak='+tanggal;
	tujuan='pmn_bajualbeli_slave.php';
	post_response_text(tujuan+'?'+'', param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					document.getElementById('tanggalkontrak').value='';
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
	//nilaikontrak=document.getElementById('nilaikontrak').value;
    nilaikontrak=document.getElementById('nilaikontrak');
	nilaikontrak.value=remove_comma_var(nilaikontrak.value);
	nilaikontrak=nilaikontrak.value;
	kurs=document.getElementById('kurs').value;
	matauang=document.getElementById('matauang').value;
	param='proses=getNilai&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&nilaikontrak='+nilaikontrak+'&kurs='+kurs+'&matauang='+matauang;
	tujuan='pmn_bajualbeli_slave.php';
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
					document.getElementById('nilaikontrak').value=ar[1]; 
					document.getElementById('nilaipph').value=ar[2]; 
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getFormAfiliasi(nokontrak){
	param='proses=getFormAfiliasi&nokontrak='+nokontrak;
	tujuan='pmn_bajualbeli_slave.php';
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

function inputAfiliasi(nokontrak){
	noafiliasi=trim(document.getElementById('noafiliasi').value);
	param='noafiliasi='+noafiliasi+'&nokontrak='+nokontrak+'&proses=inputNoAfiliasi';
	tujuan='pmn_bajualbeli_slave.php';
	if(noafiliasi==''){
		alert("No Contract Afiliasi is obligatory");
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

	document.getElementById('nokontrak').value='';
	//document.getElementById('kodeorg').value='';
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('tanggalkontrak').value='';
	document.getElementById('koderekanan').value='';
	document.getElementById('koderekanan').disabled=false;
	document.getElementById('franco').value='';
	document.getElementById('tanggalkirim').value='';
	document.getElementById('sdtanggal').value='';
	document.getElementById('kdtermin').value='100';
	document.getElementById('rekening').value='';
	document.getElementById('matauang').value='IDR';
	document.getElementById('matauang').disabled=false;
	document.getElementById('kurs').value=1;
	document.getElementById('kurs').disabled=false;
	//document.getElementById('penandatangan').value=1;
	document.getElementById('formDetail').style.display='none';
}

function delData(nokontrak){
	param='nokontrak='+nokontrak+'&proses=delData';
	tujuan='pmn_bajualbeli_slave.php';
	if(confirm("Anda yakin menghapus No Kontrak ini? "+ nokontrak)){
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

function postingData(nokontrak){
	param='nokontrak='+nokontrak+'&proses=postingData';
	tujuan='pmn_bajualbeli_slave.php';
	if(confirm("Anda yakin memposting no Kontrak ini? "+nokontrak)){
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
	nilaikontrak=parseFloat(remove_comma_var(document.getElementById('nilaikontrak').value));
	nilaippn=parseFloat(remove_comma_var(document.getElementById('nilaippn').value));
	nilaipph=parseFloat(remove_comma_var(document.getElementById('nilaipph').value));
	ongkoskirim=parseFloat(remove_comma_var(document.getElementById('ongkoskirim').value));
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	pajak=0;
	if(stsppn=='2'){//Excl PPN
		document.getElementById('nilaippn').value=(nilaikontrak*0.11).toFixed(2);
	}else if(stsppn=='1'){//Incl PPN
		document.getElementById('nilaippn').value=(nilaikontrak*0.11).toFixed(2);
	}else{//Non PPN
		document.getElementById('nilaippn').value=pajak.toFixed(2);
	}

	if(stspph=='1160200'){//PPh Pasal 22
		document.getElementById('nilaipph').value=(nilaikontrak*0.0025).toFixed(2);
	}else if(stspph=='1160400'){//PPh Pasal 23
		document.getElementById('nilaipph').value=(nilaikontrak*0.02).toFixed(2);
	}else if(stspph=='1160500'){//PPh Pasal 4 Ayat 2
		document.getElementById('nilaipph').value=(nilaikontrak*0.05).toFixed(2);
	}else{//Non PPh
		document.getElementById('nilaipph').value=pajak.toFixed(2);
	}
	document.getElementById('totalkontrak').value=(nilaikontrak+parseFloat(document.getElementById('nilaippn').value)-parseFloat(document.getElementById('nilaipph').value)+ongkoskirim).toFixed(2);
}

function loadDetail(){
    nokontrak=document.getElementById('nokontrak').value;
	param='nokontrak='+nokontrak;
	param+='&proses=loadDetail';
	tujuan='pmn_bajualbeli_slave.php';
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

function simpandetail(nokontrak){
	kodebarang=document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
	nilaiinventory=document.getElementById('nilaiinventory').value;
	hargasatuan=document.getElementById('hargasatuan').value;
	nilaitransaksi=document.getElementById('nilaitransaksi').value;
	param='proses=simpandetail'+'&nokontrak='+nokontrak+'&kodebarang='+kodebarang+'&nilaiinventory='+nilaiinventory+'&hargasatuan='+hargasatuan+'&nilaitransaksi='+nilaitransaksi;
	//alert(param);
	tujuan='pmn_bajualbeli_slave.php';
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

function deldetail(nokontrak,kodebarang){
	param='proses=deldetail'+'&nokontrak='+nokontrak+'&kodebarang='+kodebarang;
	tujuan='pmn_bajualbeli_slave.php';
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

function editdetail(nokontrak,no){
	document.getElementById('kodebarang'+no).disabled=false;
	document.getElementById('nilaiinventory'+no).disabled=false;
	document.getElementById('hargasatuan'+no).disabled=false;
}

function savedetail(nokontrak,oldkodebarang,no){
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
		param='proses=savedetail'+'&nokontrak='+nokontrak+'&oldkodebarang='+oldkodebarang+'&kodebarang='+kodebarang+'&nilaiinventory='+nilaiinventory+'&hargasatuan='+hargasatuan+'&nilaitransaksi='+nilaitransaksi;
		tujuan='pmn_bajualbeli_slave.php';
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
	tujuan='pmn_bajualbeli_slave.php';
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

function saveFoot(nokontrak){
	cnilaikontrak=document.getElementById('nilaikontrak').value;
	nilaikontrak=remove_comma_var(cnilaikontrak);
	cnilaippn=document.getElementById('nilaippn').value;
	nilaippn=remove_comma_var(cnilaippn);
	cnilaipph=document.getElementById('nilaipph').value;
	nilaipph=remove_comma_var(cnilaipph);
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	congkoskirim=document.getElementById('ongkoskirim').value;
	ongkoskirim=remove_comma_var(congkoskirim);
	catatan=document.getElementById('catatan').value;
	param='proses=saveFoot'+'&nokontrak='+nokontrak+'&nilaikontrak='+nilaikontrak+'&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&ongkoskirim='+ongkoskirim;
	param+='&stsppn='+stsppn+'&stspph='+stspph+'&catatan='+catatan;
	tujuan='pmn_bajualbeli_slave.php';
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

function saveBaru(nokontrak){
	cnilaikontrak=document.getElementById('nilaikontrak').value;
	nilaikontrak=remove_comma_var(cnilaikontrak);
	cnilaippn=document.getElementById('nilaippn').value;
	nilaippn=remove_comma_var(cnilaippn);
	cnilaipph=document.getElementById('nilaipph').value;
	nilaipph=remove_comma_var(cnilaipph);
	stsppn=document.getElementById('stsppn').value;
	stspph=document.getElementById('stspph').value;
	congkoskirim=document.getElementById('ongkoskirim').value;
	ongkoskirim=remove_comma_var(congkoskirim);
	catatan=document.getElementById('catatan').value;
	param='proses=saveFoot'+'&nokontrak='+nokontrak+'&nilaikontrak='+nilaikontrak+'&nilaippn='+nilaippn+'&nilaipph='+nilaipph+'&ongkoskirim='+ongkoskirim;
	param+='&stsppn='+stsppn+'&stspph='+stspph+'&catatan='+catatan;
	tujuan='pmn_bajualbeli_slave.php';
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
