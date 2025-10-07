function displayFormInput(){
	clearData();
	param='proses=genNo';
	tujuan='log_brgjadi_slave';
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
					document.getElementById('formDetail').style.display='none';
					document.getElementById('listData').style.display='none';
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
    carinotransaksi=document.getElementById('carinotransaksi').value;
	caritanggal=document.getElementById('caritanggal').value;
	caribarang=document.getElementById('caribarang').value;
    param='proses=loadData'+'&page='+page;
    if(carinotransaksi!=''){
        param+='&carinotransaksi='+carinotransaksi;
    }
    if(caritanggal!=''){
        param+='&caritanggal='+caritanggal;
    }
    if(caribarang!=''){
        param+='&caribarang='+caribarang;
    }
    tujuan='log_brgjadi_slave.php';
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
					document.getElementById('formDetail').style.display='none';
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

function saveData(fileTarget,passParam) {
    var passP = passParam.split('##');
    var param = ""
	document.getElementById('proses').value='insert';
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
                    document.getElementById('formDetail').style.display='block';
					document.getElementById('notransaksi').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function cancelData(){
	document.getElementById('formInput').style.display='none';
	document.getElementById('formDetail').style.display='none';
	document.getElementById('listData').style.display='block';
	loadData();
	clearData();
}

function clearData(){
	document.getElementById('carinotransaksi').value="";
	document.getElementById('caritanggal').value="";
	document.getElementById('caribarang').value="";

	document.getElementById('notransaksi').value='';
	//document.getElementById('kodegudang').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('proses').value='insert';

	document.getElementById('kodebarang1').value="";
	document.getElementById('jumlah1').value=0;
	document.getElementById('kodebarang2').value="";
	document.getElementById('jumlah2').value=0;
	document.getElementById('listDetail1').innerHTML='';
	document.getElementById('listDetail2').innerHTML='';
}

function saveDetail1(fileTarget,passParam) {
    var passP = passParam.split('##');
    var param = ""
	document.getElementById('proses').value='saveDetail1';
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	//alert('Barang 1 '+param);
    function respon() {
		if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
					//if(num==1){
						loadDetail1();
					//}else{
					//	loadDetail2();
					//}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function saveDetail2(fileTarget,passParam) {
    var passP = passParam.split('##');
    var param = ""
	document.getElementById('proses').value='saveDetail2';
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	//alert('Barang 2 '+param);
    function respon() {
		if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
					//if(num==1){
					//	loadDetail1();
					//}else{
						loadDetail2();
					//}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function CsaveDetail(num){
	notransaksi=document.getElementById('notransaksi').value;
	if(num==1){
		kodebarang=document.getElementById('kodebarang1').value;
		jumlah=document.getElementById('jumlah1').value;
	}else{
		kodebarang=document.getElementById('kodebarang2').value;
		jumlah=document.getElementById('jumlah2').value;
	}
    param='proses=saveDetail'+'&notransaksi='+notransaksi+'&kodebarang='+kodebarang+'&tipetransaksi='+tipetransaksi+'&jumlah='+jumlah;
	alert(param);
    tujuan='log_brgjadi_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else {
					if(num==1){
						loadDetail1();
					}else{
						loadDetail2();
					}
				}
			}else {
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function cancelDetail(num){
	if(num==1){
		document.getElementById('kodebarang1').value='';
		document.getElementById('jumlah1').value=0;
	}else{
		document.getElementById('kodebarang2').value='';
		document.getElementById('jumlah2').value=0;
	}
}

function fillField(notransaksi){
    param='proses=getData'+'&notransaksi='+notransaksi;
    tujuan='log_brgjadi_slave.php';
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
					document.getElementById('formDetail').style.display='block';
					document.getElementById('listData').style.display='none';
					isis=con.responseText.split("###");
					document.getElementById('notransaksi').value=isis[0];
					document.getElementById('kodegudang').value=isis[1];
					document.getElementById('tanggal').value=isis[2];
					document.getElementById('keterangan').value=isis[3];
					document.getElementById('listDetail1').innerHTML='';
					document.getElementById('listDetail2').innerHTML='';
					loadDetail1();
					alert('No.Transaksi: '+isis[0]);

					//var waktu = new Date().getTime();
					//var detik = 100;
					//while (new Date().getTime() < waktu + detik){
					//	alert('');
					//}

					loadDetail2();
					sleep(1);
				}
			}else {
				busy_off();
				error_catch(con.status);
            }
		}
	}
}

function delData(notransaksi){
	param='notransaksi='+notransaksi+'&proses=delData';
	tujuan='log_brgjadi_slave.php';  
	if(confirm("Anda yakin menghapus transaksi ini? "+ notransaksi)){
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

function loadDetail(num){
    notransaksi=document.getElementById('notransaksi').value;
	document.getElementById('proses').value='saveDetail';
    if(notransaksi!=''){
		param='proses=loadDetail';
		param+='&notransaksi='+notransaksi;
		param+='&tipetransaksi='+num;
		//alert(param+' '+num);
	    tujuan='log_brgjadi_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
				    busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
		            }else {
						if(num==1){
							document.getElementById('listDetail1').innerHTML=con.responseText;
						}else{
							document.getElementById('listDetail2').innerHTML=con.responseText;
						}
					}
	            }else {
					busy_off();
					error_catch(con.status);
				}
            }
		}
	}
}

function loadDetail1(){
    notransaksi=document.getElementById('notransaksi').value;
	document.getElementById('proses').value='saveDetail1';
    if(notransaksi!=''){
		param='proses=loadDetail1';
		param+='&notransaksi='+notransaksi;
		param+='&tipetransaksi=1';
		//alert(param);
	    tujuan='log_brgjadi_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
				    busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
		            }else {
						document.getElementById('listDetail1').innerHTML=con.responseText;
					}
	            }else {
					busy_off();
					error_catch(con.status);
				}
            }
		}
	}
}

function loadDetail2(){
    notransaksi=document.getElementById('notransaksi').value;
	document.getElementById('proses').value='saveDetail2';
    if(notransaksi!=''){
		param='proses=loadDetail2';
		param+='&notransaksi='+notransaksi;
		param+='&tipetransaksi=2';
		//alert(param);
	    tujuan='log_brgjadi_slave.php';
		post_response_text(tujuan, param, respog);
	    function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
				    busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
		            }else {
						document.getElementById('listDetail2').innerHTML=con.responseText;
					}
	            }else {
					busy_off();
					error_catch(con.status);
				}
            }
		}
	}
}

function dfillField(notransaksi,kodebarang,tipetransaksi,jumlah){
	if(tipetransaksi==1){
		document.getElementById('kodebarang1').value=kodebarang;
		document.getElementById('jumlah1').value=jumlah;
	}else{
		document.getElementById('kodebarang2').value=kodebarang;
		document.getElementById('jumlah2').value=jumlah;
	}
}

function delDetail(notransaksi,kodebarang,tipetransaksi,jumlah){
	param='proses=delDetail'+'&notransaksi='+notransaksi+'&tipetransaksi='+tipetransaksi+'&kodebarang='+kodebarang;
	tujuan='log_brgjadi_slave.php';  
	if(confirm("Anda yakin menghapus barang ini? "+ kodebarang)){
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
					if(tipetransaksi==1){
						loadDetail1();
					}else{
						loadDetail2();
					}
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingData(notransaksi){
	param='notransaksi='+notransaksi+'&proses=postingData';
	tujuan='log_brgjadi_slave.php';  
	if(confirm("Anda yakin memposting no notransaksi ini? "+ notransaksi)){
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

function preview_PDF(notransaksi,ev){
	param='notransaksi='+notransaksi;
	tujuan='log_brgjadi_PDF.php?'+param;
	//alert(tujuan);
	title='Print Detail '+notransaksi;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}
