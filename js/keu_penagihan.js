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
	tujuan='keu_slave_penagihan';
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

// function getBayarKe(){setValue
	// debet=document.getElementById('debet').value;
	// param='proses=getBayarKe';
	// tujuan='keu_slave_penagihan';
	// post_response_text(tujuan+'.php', param, respon);
	// function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert('ERROR TRANSACTION,\n' + con.responseText);
                // } else {
                    // // Success Response
                        // document.getElementById('formInput').style.display='block';
                        // document.getElementById('listData').style.display='none';
                        // document.getElementById('noinvoice').value=con.responseText;
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

function cariData(pg){
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+pg;
    if(ntrs!=''){
        param+='&noinvoice='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    tujuan='keu_slave_penagihan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                        isdt=con.responseText.split("####");
                        document.getElementById('formInput').style.display='none';
                        document.getElementById('listData').style.display='block';
                        document.getElementById('continerlist').innerHTML=isdt[0];
                        document.getElementById('footData').innerHTML=isdt[1];
                        
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
function loadData(page){
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+page;
    if(ntrs!=''){
        param+='&noinvoice='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    tujuan='keu_slave_penagihan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                        isdt=con.responseText.split("####");
                        document.getElementById('formInput').style.display='none';
                        document.getElementById('listData').style.display='block';
                        document.getElementById('continerlist').innerHTML=isdt[0];
                        document.getElementById('footData').innerHTML=isdt[1];
                        clearData();
                        // closeDialog();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
function fillField(noinv){
    param='proses=getData'+'&noinvoice='+noinv;
    tujuan='keu_slave_penagihan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                        document.getElementById('formInput').style.display='block';
                        document.getElementById('listData').style.display='none';
                        isis=con.responseText.split("###");
                        document.getElementById('noinvoice').value=isis[0];
                        document.getElementById('kodeorganisasi').value=isis[1];
                        document.getElementById('tanggal').value=isis[2];
                        document.getElementById('noorder').value=isis[3];
                        document.getElementById('noorder').disabled=true;
                        kdcst=document.getElementById('kodecustomer');
                        for(a=0;a<kdcst.length;a++){
                            if(kdcst.options[a].value==isis[4]){
                                    kdcst.options[a].selected=true;
                                }
                        }
						kdcst.disabled=true;
                        document.getElementById('nilaiinvoice').value=isis[5];
                        document.getElementById('nofakturpajak').value=isis[11];
                        document.getElementById('nilaippn').value=isis[6];
                        document.getElementById('jatuhtempo').value=isis[7];
                        byrke=document.getElementById('bayarke');
                        for(a=0;a<byrke.length;a++){
                            if(byrke.options[a].value==isis[8]){
                                    byrke.options[a].selected=true;
                                }
                        }
                        dbt=document.getElementById('debet');
                        for(a=0;a<dbt.length;a++){
                            if(dbt.options[a].value==isis[9]){
                                    dbt.options[a].selected=true;
                                }
                        }
                        kridit=document.getElementById('kredit');
                        for(a=0;a<kridit.length;a++){
                            if(kridit.options[a].value==isis[10]){
                                    kridit.options[a].selected=true;
                                }
                        }
                        document.getElementById('keterangan1').value=isis[12];
                        document.getElementById('keterangan2').value=isis[13];
                        document.getElementById('keterangan3').value=isis[14];
                        document.getElementById('keterangan4').value=isis[15];
                        document.getElementById('keterangan5').value=isis[16];
                        document.getElementById('rupiah1').value=isis[17];
                        document.getElementById('rupiah2').value=isis[18];
                        document.getElementById('rupiah3').value=isis[19];
                        document.getElementById('rupiah4').value=isis[20];
                        document.getElementById('rupiah5').value=isis[21];
                        document.getElementById('matauang').value=isis[22];
                        document.getElementById('kurs').value=isis[23];
                        document.getElementById('keterangan6').value=isis[24];
                        document.getElementById('rupiah6').value=isis[25];
                        
                        document.getElementById('keterangan7').value=isis[26];
                        document.getElementById('rupiah7').value=isis[27];
                        document.getElementById('keterangan8').value=isis[28];
                        document.getElementById('rupiah8').value=isis[29];
                        document.getElementById('ttd').value=isis[30];
                        document.getElementById('jenis').value=isis[31];
                        document.getElementById('kuantitas').value=isis[32];
                        
                        
                        
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
//jamhari
function searchNosibp(title,content,ev){
	width='400';
	height='520';
	showDialog1(title,content,width,height,ev);
        getFormNosibp();
	//alert('asdasd');
}

function popUpPosting(title,noinvoice,content,ev){
	width='400';
	height='100';
	showDialog2(title,content,width,height,ev);
        getFormAfiliasi(noinvoice);
	//alert('asdasd');
}

function getBayarKe(){
	debet=document.getElementById('debet').options[document.getElementById('debet').selectedIndex].value;
	bayarke=document.getElementById('bayarke');
	param='proses=getBayarKe&debet='+debet;
	tujuan='keu_slave_penagihan.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					bayarke.options.length=0;
                    eval(con.responseText);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				}
		}
	}
}

function getKursInvoice(){
	noorder=document.getElementById('noorder').value;
	matauang=document.getElementById('matauang').value;
	tanggal=document.getElementById('tanggal').value;
	param='proses=getKursInvoice&noorder='+noorder+'&matauang='+matauang+'&tanggal='+tanggal;
	tujuan='keu_slave_penagihan.php';
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					document.getElementById('tanggal').value='';
                                        document.getElementById('matauang').value='IDR';
				}
				else {
					// alert(con.responseText);
					document.getElementById('kurs').value=con.responseText; 
                                        getNilai();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				}
		}
	}
}

function getNilai(){
    
    
   
	nilaippn=document.getElementById('nilaippn').value;
	//nilaiinvoice=document.getElementById('nilaiinvoice').value;
       
    nilaiinvoice=document.getElementById('nilaiinvoice');
                nilaiinvoice.value=remove_comma_var(nilaiinvoice.value);
                nilaiinvoice=nilaiinvoice.value;
                
	kurs=document.getElementById('kurs').value;
        matauang=document.getElementById('matauang').value;
	param='proses=getNilai&nilaippn='+nilaippn+'&nilaiinvoice='+nilaiinvoice+'&kurs='+kurs+'&matauang='+matauang;
       
	tujuan='keu_slave_penagihan.php';
       
        if(confirm('anda yankin mengganti mata uang??'))
	post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
					
				}
				else {
					// alert(con.responseText);
                                        ar=con.responseText.split("###");
					document.getElementById('nilaippn').value=ar[0]; 
                                        document.getElementById('nilaiinvoice').value=ar[1]; 
                                       
				}
			}
			else {
				busy_off();
				error_catch(con.status);
				}
		}
	}
}


function getFormNosibp(){
        param='proses=getFormNosipb';
        tujuan='keu_slave_penagihan.php';
        post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
              if(con.readyState==4){
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                document.getElementById('formPencariandata').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
	 }
} 
function getFormAfiliasi(noinvoice){
        param='proses=getFormAfiliasi&noinvoice='+noinvoice;
        tujuan='keu_slave_penagihan.php';
        post_response_text(tujuan+'?'+'', param, respog);
	
	function respog(){
              if(con.readyState==4){
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                document.getElementById('formaAfiliasi').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
	 }
} 
function findNosipb(){
	txt=trim(document.getElementById('nosipbcr').value);
	idcust=document.getElementById('custId');
	idcust=idcust.options[idcust.selectedIndex].value;
	param='txtfind='+txt+'&proses=getnosibp'+'&custId='+idcust;
        tujuan='keu_slave_penagihan.php';
        if(txt==''){
            alert("Nokontrak is obligatory");
        } else {
            post_response_text(tujuan, param, respog);
        }
        
	function respog()
	{
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    document.getElementById('container2').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}
function inputAfiliasi(noinvoice){
	noafiliasi=trim(document.getElementById('noafiliasi').value);
	param='noafiliasi='+noafiliasi+'&noinvoice='+noinvoice+'&proses=inputNoAfiliasi';
        tujuan='keu_slave_penagihan.php';
        if(noafiliasi==''){
            alert("No Invoice Afiliasi is obligatory");
        } else {
				post_response_text(tujuan, param, respog);
        }
        
	function respog()
	{
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    getPage();
									closeDialog2();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}
function setData(nosibp,kdcust,kdHo,matauang,nilInvoice,noaknbyr,ppn,hKuantitas){
    document.getElementById('noorder').value=nosibp;
    document.getElementById('matauang').value=matauang;
    document.getElementById('nilaiinvoice').value=nilInvoice;
    document.getElementById('kodeorganisasi').value=kdHo;
    document.getElementById('kuantitas').value=hKuantitas;
	kridit=document.getElementById('kodecustomer');
    for(a=0;a<kridit.length;a++){
        if(kridit.options[a].value==kdcust){
                kridit.options[a].selected=true;
            }
    }
	bayar=document.getElementById('bayarke');
    for(a=0;a<bayar.length;a++){
        if(bayar.options[a].value==noaknbyr){
                bayar.options[a].selected=true;
            }
    }
    kridit.disabled=true;
	bayar.disabled=true;
    
   document.getElementById('nilaippn').value=ppn;
        
        
    closeDialog();
}
function cancelData(){
//    $arr="##noinvoice##jatuhtempo##kodeorganisasi##nofakturpajak##tanggal##bayarke";
//    $arr.="##kodecustomer##uangmuka##noorder##nilaippn##keterangan##nilaiinvoice##debet##kredit";
document.getElementById('formInput').style.display='none';
document.getElementById('listData').style.display='block';
clearData();
}
function clearData(){
//$arr="##noinvoice##jatuhtempo##kodeorganisasi##nofakturpajak##tanggal##bayarke##proses";
//$arr.="##kodecustomer##noorder##nilaippn##nilaiinvoice##debet##kredit##keterangan1##keterangan2##keterangan3";
//$arr.="##keterangan4##keterangan5##rupiah1##rupiah2##rupiah3##rupiah4##rupiah5##matauang##kurs";
 
document.getElementById('jatuhtempo').value='';
document.getElementById('nofakturpajak').value='';
document.getElementById('tanggal').value='';
document.getElementById('tanggal').disabled=false;
document.getElementById('bayarke').value='';
document.getElementById('kodecustomer').value='';
document.getElementById('kodeorganisasi').value='';
document.getElementById('matauang').value='IDR';
document.getElementById('kurs').value='0';
document.getElementById('noorder').value='';
document.getElementById('noorder').disabled=false;
document.getElementById('nilaippn').value='';
document.getElementById('nilaiinvoice').value='';
document.getElementById('debet').value='';
document.getElementById('kredit').value='';
document.getElementById('txtsearch').value="";
document.getElementById('tgl_cari').value="";
document.getElementById('rupiah1').value="0";
document.getElementById('rupiah2').value="0";
document.getElementById('rupiah3').value="0";
document.getElementById('rupiah4').value="0";
document.getElementById('rupiah5').value="0";
document.getElementById('rupiah6').value="0";
document.getElementById('rupiah7').value="0";
document.getElementById('rupiah8').value="0";

setValue('ttd',"Rizki Daslia");
document.getElementById('jenis').value="";
document.getElementById('kuantitas').value="0";

}
function delData(notrans){
        param='noinvoice='+notrans+'&proses=delData';
        tujuan='keu_slave_penagihan.php';  
        if(confirm("Anda yakin menghapus no invoice ini?"+ notrans)){
            post_response_text(tujuan, param, respog);
        }
	function respog()
	{
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    getPage();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}
function postingData(notrans){
        param='noinvoice='+notrans+'&proses=postingData';
        tujuan='keu_slave_penagihan.php';  
        if(confirm("Anda yakin memposting no invoice ini?"+ notrans)){
            post_response_text(tujuan, param, respog);
        }
	function respog()
	{
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    getPage();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
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