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
	if(kodeorg=='' ||  stasiun=='' ||  mesin=='' ||  tanggal==''  || jam==0 || jam==null || jam==''){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&stasiun='+stasiun+'&mesin='+mesin+'&tanggal='+tanggal;
		param+='&hmawal='+hmawal+'&hmakhir='+hmakhir+'&jam='+jam+'&tipeservice='+tipeservice;
		param+='&keterangan='+keterangan+'&addedit='+addedit+'&proses=saveData';
		tujuan='pabrik_hm_slave.php';
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
}

function bersihkanForm(){
	//document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('stasiun').value='';
	document.getElementById('mesin').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tipeservice').value=0;
	document.getElementById('hmawal').value=0;
	document.getElementById('hmakhir').value=0;
	document.getElementById('jam').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
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
		}
	}
	if (num==2){
		if (hmakhir<=hmawal){
			document.getElementById('hmakhir').value = hmawal+jam;
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
