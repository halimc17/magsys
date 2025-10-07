/**
 * Pabrik HM Setup
 */

function loadData(){
	param='proses=loadData';
	tujuan='pabrik_hm_setup_slave.php';
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

function simpanData(){
	kodeorg 	=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	stasiun		=document.getElementById('stasiun').options[document.getElementById('stasiun').selectedIndex].value;
	mesin 		=document.getElementById('mesin').options[document.getElementById('mesin').selectedIndex].value;
	jamganti1	=document.getElementById('jamganti1').value;
	jamganti2	=document.getElementById('jamganti2').value;
	jamganti3	=document.getElementById('jamganti3').value;
	hmakhir		=document.getElementById('hmakhir').value;
	keterangan	=document.getElementById('keterangan').value;
	addedit		=document.getElementById('addedit').value;
	//if(kodeorg=='' || stasiun=='' || mesin=='' || jamganti1=='' || jamganti1==0 || jamganti1==null || jamganti2=='' || jamganti2==0 || jamganti2==null || jamganti3=='' || jamganti3==0 || jamganti3==null){
	if(kodeorg=='' || stasiun=='' || mesin==''){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&stasiun='+stasiun+'&mesin='+mesin+'&jamganti1='+jamganti1+'&jamganti2='+jamganti2+'&jamganti3='+jamganti3+'&hmakhir='+hmakhir;
		param+='&keterangan='+keterangan+'&addedit='+addedit+'&proses=saveData';
		tujuan='pabrik_hm_setup_slave.php';
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

function fillfield(mesin,jamganti1,jamganti2,jamganti3,hmakhir,keterangan){
	document.getElementById('kodeorg').value=mesin.substr(0,4);
	document.getElementById('stasiun').value=mesin.substr(0,6);
	getMesin();
	alert('Update Mesin : '+mesin);
	document.getElementById('mesin').value=mesin;
	document.getElementById('jamganti1').value=jamganti1;
	document.getElementById('jamganti2').value=jamganti2;
	document.getElementById('jamganti3').value=jamganti3;
	document.getElementById('hmakhir').value=hmakhir;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
	document.getElementById('jamganti1').focus();
}

function bersihkanForm(){
	//document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('stasiun').value='';
	document.getElementById('mesin').value='';
	document.getElementById('jamganti1').value=0;
	document.getElementById('jamganti2').value=0;
	document.getElementById('jamganti3').value=0;
	document.getElementById('hmakhir').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(mesin){
	param='mesin='+mesin;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'pabrik_hm_setup_slave.php';
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

function getStasiun(){
	kodeorg=document.getElementById('kodeorg').value; 
    param='kodeorg='+kodeorg;eee
    param+='&proses=getStasiun';
	tujuan='pabrik_hm_setup_slave.php';
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
    tujuan='pabrik_hm_setup_slave.php';
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

function cekjam(num){
	jamganti1=parseFloat(document.getElementById('jamganti1').value);
	jamganti2=parseFloat(document.getElementById('jamganti2').value);
	jamganti3=parseFloat(document.getElementById('jamganti3').value);
	if(jamganti1>jamganti2 && jamganti2!=0){
		alert('Jam ke-1 tidak boleh lebih besar dari Jam ke-2 !');
		if(num==1){
			document.getElementById('jamganti1').value=0;
			document.getElementById('jamganti1').focus();
		}else{
			document.getElementById('jamganti2').value=0;
			document.getElementById('jamganti2').focus();
		}
	}
	if(jamganti2>jamganti3 && jamganti3!=0){
		alert('Jam ke-1 tidak boleh lebih besar dari Jam ke-2 !');
		if(num==2){
			document.getElementById('jamganti2').value=0;
			document.getElementById('jamganti2').focus();
		}else{
			document.getElementById('jamganti3').value=0;
			document.getElementById('jamganti3').focus();
		}
	}
}
