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
    tujuan='pmn_slave_suratperintahpengiriman.php';
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

function cancelData(){
document.getElementById('formInput').style.display='none';
document.getElementById('listData').style.display='block';
clearData();
}

function clearData(){
document.getElementById('nokontrak').value='';
document.getElementById('nokontrak').disabled=false;
document.getElementById('nokontrakInternal').disabled=true;
document.getElementById('nokontrakInternal').value='';
document.getElementById('nodo').value='';
document.getElementById('kodecustomer').value='';
document.getElementById('kepada').value='';
document.getElementById('tanggalsurat').value='';
document.getElementById('waktupenyerahan').value='';
document.getElementById('tempatpenyerahan').value='';
document.getElementById('dibuat').value='';
document.getElementById('lain').value='';
document.getElementById('jabatan').value='';
document.getElementById('ttd').value='';
document.getElementById('qty').value='0';
}

function searchKontrak(title,status,content,ev){
	width='600';
	height='520';
	showDialog1(title,content,width,height,ev);
    getFormNosibp(status);
}

function getFormNosibp(status){
        param='status='+status+'&proses=getFormNosipb';
        tujuan='pmn_slave_suratperintahpengiriman.php';
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

function findNosipb(status){
	txt=trim(document.getElementById('nosipbcr').value);
	param='txtfind='+txt+'&status='+status+'&proses=getnosibp';
        tujuan='pmn_slave_suratperintahpengiriman.php';
        if(txt==''){
            alert("Nosipb is obligatory");
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

function setData(nokontrak,kdcust,kodept,kodeorg,dari,sampai,kuantitaskontrak,status,franco){
    document.getElementById('nokontrakInternal').value = '';
	if(status == 'Internal'){
		document.getElementById('nokontrakInternal').value=nokontrak;
	}else{
		if(kodept == 'AMP'){
			document.getElementById('nokontrakInternal').disabled = false;
		}else{
			document.getElementById('nokontrakInternal').disabled = true;
		}
		document.getElementById('nokontrak').value=nokontrak;
		kridit=document.getElementById('kodecustomer');
		for(a=0;a<kridit.length;a++){
			if(kridit.options[a].value==kdcust){
					kridit.options[a].selected=true;
				}
		}
		kridit.disabled=true;
		document.getElementById('kepada').value=kodept;
		if(sampai == '00-00-0000'){
			document.getElementById('waktupenyerahan').value=dari;
		}else{
			document.getElementById('waktupenyerahan').value=dari+' s/d '+sampai;
		}
                
                document.getElementById('tempatpenyerahan').value=franco;
		
		param='proses=getQty'+'&nokontrak='+nokontrak+'&kuantitaskontrak='+kuantitaskontrak;
		tujuan='pmn_slave_suratperintahpengiriman.php';
		
		function respog()
		{
			if(con.readyState==4)
			{
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						document.getElementById('qty').value=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
	post_response_text(tujuan, param, respog);
    closeDialog();
}

function saveData(fileTarget,passParam) {
	if(document.getElementById('nokontrak').value==''){
		alert('No Kontrak harus diisi.');
		exit(0);
	}
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+remove_comma_var(getValue(passP[i]));
        } else {
            param += "&"+passP[i]+"="+remove_comma_var(getValue(passP[i]));
        }
    }
	param += '&proses=insert';
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    loadData();
                    cancelData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);

}

function fillField(nodo){
  
    param='proses=getData'+'&nodo='+nodo;
    tujuan='pmn_slave_suratperintahpengiriman.php';
   
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
						document.getElementById('formInput').style.display='block';
                        document.getElementById('listData').style.display='none';
                        isis=con.responseText.split("###");
                        document.getElementById('nokontrak').value=isis[0];
						document.getElementById('nokontrak').disabled=true;
                        document.getElementById('nodo').value=isis[1];
						kdcst=document.getElementById('kodecustomer');
                        for(a=0;a<kdcst.length;a++){
                            if(kdcst.options[a].value==isis[2]){
                                    kdcst.options[a].selected=true;
                                }
                        }
                        document.getElementById('kepada').value=isis[10];
                        document.getElementById('tanggalsurat').value=isis[4];
                        document.getElementById('waktupenyerahan').value=isis[5];
                        document.getElementById('tempatpenyerahan').value=isis[6];
                        document.getElementById('dibuat').value=isis[7];
                        document.getElementById('lain').value=isis[8];
                        document.getElementById('jabatan').value=isis[9];
                        document.getElementById('ttd').value=isis[11];
                        document.getElementById('qty').value=isis[12];
                        document.getElementById('nokontrakInternal').value=isis[13];
						if(isis[3]=='AMP'){
							document.getElementById('nokontrakInternal').disabled = false;
						}else{
							document.getElementById('nokontrakInternal').disabled = true;
						}
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}

function delData(nodo){
        param='nodo='+nodo+'&proses=delData';
        tujuan='pmn_slave_suratperintahpengiriman.php';  
        if(confirm("Anda yakin menghapus no do ini? "+nodo)){
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

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function displayFormInput(){
        clearData();
		document.getElementById('formInput').style.display='block';
		document.getElementById('listData').style.display='none';
}

function cariData(pg){
    
    nokontrak=document.getElementById('txtsearchkontrak').value;
    
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+pg;
    if(ntrs!=''){
        param+='&nodo='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    if(nokontrak!=''){
        param+='&nokontrak='+nokontrak;
    }
  
    tujuan='pmn_slave_suratperintahpengiriman.php';
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