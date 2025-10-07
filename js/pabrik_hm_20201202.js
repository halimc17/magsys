/**
 * Pabrik HM
 */

function loadData(){
	mesin=document.getElementById('mesin').value;
	param='proses=loadData'+'&mesin='+mesin;
	tujuan='pabrik_hm_slave.php';
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

function getData(){
	kodeorg=document.getElementById('kodeorg').value;
    tanggal=document.getElementById('tanggal').value;
    param='kodeorg='+kodeorg+'&proses=getData'+'&tanggal='+tanggal;
    tujuan='pabrik_hm_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					arr=con.responseText.split("###");
					document.getElementById('airkemarin').value=arr[0];
					hitungsisaair();
					//document.getElementById('airkemarin').value=con.responseText;
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
	stasiun		=document.getElementById('stasiun').options[document.getElementById('stasiun').selectedIndex].value;
	mesin 		=document.getElementById('mesin').options[document.getElementById('mesin').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;
	hmawal		=document.getElementById('hmawal').value;
	hmakhir		=document.getElementById('hmakhir').value;
	jam			=document.getElementById('jam').value;
	tipeservice	=document.getElementById('tipeservice').value;
	keterangan	=document.getElementById('keterangan').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  stasiun=='' ||  mesin=='' ||  tanggal==''){
		alert('All fields are required');
	}else{
		if (parseFloat(hmakhir)>=parseFloat(hmawal) && parseFloat(jam)>=0){
			param='kodeorg='+kodeorg+'&stasiun='+stasiun+'&mesin='+mesin+'&tanggal='+tanggal;
			param+='&hmawal='+hmawal+'&hmakhir='+hmakhir+'&jam='+jam+'&tipeservice='+tipeservice;
			param+='&keterangan='+keterangan+'&addedit='+addedit+'&proses=saveData';
			tujuan='pabrik_hm_slave.php';
			post_response_text(tujuan, param, respog);
		}else{
			alert('HM Akhir atau Jam lebih kecil..!');
		}
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
					getHM();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 		
}

function fillfield(kodeorg,mesin,tanggal,tipeservice,hmawal,hmakhir,jam,keterangan){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('stasiun').value=mesin.substr(0,6);
	document.getElementById('mesin').value=mesin;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tipeservice').value=tipeservice;
	document.getElementById('hmawal').value=hmawal;
	document.getElementById('hmakhir').value=hmakhir;
	document.getElementById('jam').value=jam;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
	if(tipeservice==0){
		document.getElementById('hmawal').setAttribute('disabled', 'disabled');
	}else{
		document.getElementById('hmawal').removeAttribute('disabled');
	}
}

function bersihkanForm(){
	//document.getElementById('kodeorg').value=kodeorg;
	//document.getElementById('stasiun').value='';
	//document.getElementById('mesin').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tipeservice').value=0;
	document.getElementById('hmawal').value=0;
	document.getElementById('hmakhir').value=0;
	document.getElementById('jam').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
	document.getElementById('hmawal').setAttribute('disabled', 'disabled');
}

function deldata(kodeorg,mesin,tanggal,tipeservice){
	param='kodeorg='+kodeorg+'&mesin='+mesin+'&tanggal='+tanggal+'&tipeservice='+tipeservice;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'pabrik_hm_slave.php';
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

function hitungjam(num){
	hmawal=parseFloat(document.getElementById('hmawal').value);
	hmakhir=parseFloat(document.getElementById('hmakhir').value);
	jam=parseFloat(document.getElementById('jam').value);
	if (num==1){
		if (hmakhir>=hmawal){
			document.getElementById('jam').value = hmakhir-hmawal;
		}else{
			if(hmakhir!=0){
				alert('HM Akhir tidak boleh lebih kecil dari HM Awal');
				document.getElementById('hmakhir').focus();
			}
		}
	}
	if (num==2){
		if (jam>=0){
			document.getElementById('hmakhir').value = hmawal+jam;
		}else{
			alert('Jam tidak boleh minus');
			document.getElementById('jam').focus();
		}
	}	
}

function showDetail(tgl,kdorg,ev)
{
        title="Data Detail";
        content="<fieldset><legend>Unit : "+kdorg+", Date "+tgl+"</legend><div id=contDetail style='overflow:auto; width:890px; height:320px;' ></div></fieldset>";
        width='920';
        height='370';
        showDialog1(title,content,width,height,ev);	
}

function previewDetail(tgl,kdorg,ev)
{
        showDetail(tgl,kdorg,ev);
        param='kdorg='+kdorg+'&proses=getDetailPA'+'&tgl='+tgl;
        tujuan='pabrik_hm_slave.php';
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
                                                        document.getElementById('contDetail').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  

}

function getStasiun(){
	kodeorg=document.getElementById('kodeorg').value; 
    param='kodeorg='+kodeorg;
    param+='&proses=getStasiun';
	tujuan='pabrik_hm_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('stasiun').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getMesin(){
	kodeorg=document.getElementById('kodeorg').value; 
    stasiun=document.getElementById('stasiun').value; 
    param='kodeorg='+kodeorg;
    param+='&stasiun='+stasiun;
    param+='&proses=getMesin';
    tujuan='pabrik_hm_slave.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('mesin').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getHM(){
    mesin=document.getElementById('mesin').value; 
    param='mesin='+mesin;
    param+='&proses=getHM';
    tujuan='pabrik_hm_slave.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('hmawal').value=con.responseText;
					if(con.responseText==0){
						document.getElementById('hmawal').removeAttribute('disabled');
					} else {
						document.getElementById('hmawal').setAttribute('disabled', 'disabled');
					}
					loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getEnable(){
    tipeservice=document.getElementById('tipeservice').value; 
	if(tipeservice==0){
		document.getElementById('hmawal').setAttribute('disabled', 'disabled');
	}else{
		document.getElementById('hmawal').removeAttribute('disabled');
	}
}

function showpopup(kodemesin,tanggal,kdorg,stasiun,ev)
{
   param='kodemesin='+kodemesin+'&tanggal='+tanggal+'&kdorg='+kdorg+'&stasiun='+stasiun;
   tujuan='pabrik_laphm_showpopup.php'+"?"+param;
   width='1200';
   height='470';
  
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Laporan HM '+kdorg+' '+stasiun+' '+kodemesin+' '+tanggal,content,width,height,ev); 
}
