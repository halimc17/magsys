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
                    loadData();
                    clearData();
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
	tujuan='keu_invoice_nonkontrak_slave';
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
    tujuan='keu_invoice_nonkontrak_slave.php';
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
					clearData();
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
    tujuan='keu_invoice_nonkontrak_slave.php';
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
					document.getElementById('kodebarang').value=isis[5];
					document.getElementById('kuantitas').value=isis[6];
					document.getElementById('nofakturpajak').value=isis[7];
					kdcst=document.getElementById('kodecustomer');
					for(a=0;a<kdcst.length;a++){
						if(kdcst.options[a].value==isis[8]){
							kdcst.options[a].selected=true;
						}
					}
					nilaiinvoice=isis[9].replace(/,/g, "");
					document.getElementById('harga').value=(parseFloat(nilaiinvoice)/parseFloat(isis[6])).toFixed(2);
					document.getElementById('nilaiinvoice').value=isis[9];
					document.getElementById('stsppn').value=isis[10];
					document.getElementById('nilaippn').value=isis[11];
					document.getElementById('stspph').value=isis[12];
					document.getElementById('nilaipph').value=isis[13];
					document.getElementById('matauang').value=isis[14];
					document.getElementById('kurs').value=isis[15];
					document.getElementById('ttd').value=isis[16];
					document.getElementById('jenis').value=isis[17];
					dbt=document.getElementById('debet');
					for(a=0;a<dbt.length;a++){
						if(dbt.options[a].value==isis[18]){
							dbt.options[a].selected=true;
						}
					}
					kridit=document.getElementById('kredit');
					for(a=0;a<kridit.length;a++){
						if(kridit.options[a].value==isis[19]){
							kridit.options[a].selected=true;
						}
					}
					document.getElementById('keterangan').value=isis[20];
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
	kodebarang=document.getElementById('kodebarang').value;
	matauang=document.getElementById('matauang').value;
	tanggal=document.getElementById('tanggal').value;
	param='proses=getKursInvoice&kodebarang='+kodebarang+'&matauang='+matauang+'&tanggal='+tanggal;
	tujuan='keu_invoice_nonkontrak_slave.php';
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
					getNilai();
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
	tujuan='keu_invoice_nonkontrak_slave.php';
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
	tujuan='keu_invoice_nonkontrak_slave.php';
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
	tujuan='keu_invoice_nonkontrak_slave.php';
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
	document.getElementById('bayarke').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('jatuhtempo').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('kuantitas').value=0;
	document.getElementById('nofakturpajak').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('harga').value=0;
	document.getElementById('nilaiinvoice').value=0;
	document.getElementById('stsppn').value='0';
	document.getElementById('nilaippn').value=0;
	document.getElementById('stspph').value='';
	document.getElementById('nilaipph').value=0;
	document.getElementById('matauang').value='IDR';
	document.getElementById('kurs').value=1;
	//document.getElementById('ttd').value="";
	//setValue('ttd',"Rizki Hernanda Daslia");
	//document.getElementById('jenis').value="";
	document.getElementById('debet').value='';
	document.getElementById('kredit').value='';
	document.getElementById('keterangan').value='';
}

function delData(notrans){
	param='noinvoice='+notrans+'&proses=delData';
	tujuan='keu_invoice_nonkontrak_slave.php';  
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
	tujuan='keu_invoice_nonkontrak_slave.php';  
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

function getSubTotal(num){
	kuantitas=document.getElementById('kuantitas').value;
	harga=document.getElementById('harga').value;
	nilaiinvoice=document.getElementById('nilaiinvoice').value;
	if(kuantitas==''){
		kuantitas=0;
	}
	if(harga==''){
		harga=0;
	}
	if(nilaiinvoice==''){
		nilaiinvoice=0;
	}
	if(nilaiinvoice!=0){
		if(num==1){
			harga=parseFloat(nilaiinvoice)/parseFloat(kuantitas);
			document.getElementById('harga').value=harga.toFixed(2);
		}
	}
	if(harga!=0){
		if(num==0){
			nilaiinvoice=parseFloat(harga)*parseFloat(kuantitas);
			document.getElementById('nilaiinvoice').value=nilaiinvoice.toFixed(2);
		}
	}
	stsppn=document.getElementById('stsppn').value;
	if(stsppn=='0'){
		document.getElementById('nilaippn').value=0;
	}else{
		document.getElementById('nilaippn').value=(nilaiinvoice*0.1).toFixed(2);
	}
	stspph=document.getElementById('stspph').value;
	if(stspph=='1160200'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.0025).toFixed(2);
	}else if(stspph=='1160400'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.012).toFixed(2);
	}else if(stspph=='1160500'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.05).toFixed(2);
	}else{
		document.getElementById('nilaipph').value=0;
	}
}

function getPajak(){
	kuantitas=parseFloat(document.getElementById('kuantitas').value);
	harga=parseFloat(document.getElementById('harga').value);
	nilaiinvoice=kuantitas*harga;
	stsppn=document.getElementById('stsppn').value;
	nilaippn=parseFloat(document.getElementById('nilaippn').value);
	if(stsppn=='0'){
		document.getElementById('harga').value=harga.toFixed(2);
		document.getElementById('nilaiinvoice').value=nilaiinvoice.toFixed(2);
		document.getElementById('nilaippn').value=0;
	}else if(stsppn=='1'){
		document.getElementById('harga').value=(harga/1.1).toFixed(2);
		document.getElementById('nilaiinvoice').value=(nilaiinvoice/1.1).toFixed(2);
		document.getElementById('nilaippn').value=((nilaiinvoice/1.1)*0.1).toFixed(2);
	}else{
		document.getElementById('harga').value=harga.toFixed(2);
		document.getElementById('nilaiinvoice').value=nilaiinvoice.toFixed(2);
		document.getElementById('nilaippn').value=(nilaiinvoice*0.1).toFixed(2);
	}

	nilaiinvoice=parseFloat(document.getElementById('nilaiinvoice').value);
	stspph=document.getElementById('stspph').value;
	nilaipph=parseFloat(document.getElementById('nilaipph').value);
	if(stspph=='1160200'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.0025).toFixed(2);
	}else if(stspph=='1160400'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.012).toFixed(2);
	}else if(stspph=='1160500'){
		document.getElementById('nilaipph').value=(nilaiinvoice*0.05).toFixed(2);
	}else{
		document.getElementById('nilaipph').value=0;
	}
}

function detailPDF(numRow,ev) {
    // Prep Param
    var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    var noakun = document.getElementById('noakun_'+numRow).getAttribute('value');
    var tipetransaksi = document.getElementById('tipetransaksi_'+numRow).getAttribute('value');
    var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipetransaksi="+tipetransaksi+"&noakun="+noakun;
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='keu_slave_kasbank_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
