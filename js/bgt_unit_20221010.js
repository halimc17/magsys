/**
 * Pabrik Budget Unit
 */

function bersihkanForm(){
	//document.getElementById('kdPT').value='';
	//document.getElementById('kdUnit').value='';
	//document.getElementById('kdDivisi').value='';
	//document.getElementById('Tahun').value='';
	document.getElementById('kdBudget').value='';
	document.getElementById('kdKegiatan').value='';
	document.getElementById('kdBarang').value='';
	document.getElementById('kdVhc').value='';
	document.getElementById('addedit').value='insert';
}

function loadData(){
	kdPT 		=document.getElementById('kdPT').options[document.getElementById('kdPT').selectedIndex].value;
	kdUnit		=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	kdDivisi 	=document.getElementById('kdDivisi').options[document.getElementById('kdDivisi').selectedIndex].value;
	Tahun  		=document.getElementById('Tahun').value;
	param='kdPT='+kdPT+'&kdUnit='+kdUnit+'&kdDivisi='+kdDivisi+'&Tahun='+Tahun;
	param+='&proses=loadData';
	tujuan='bgt_unit_slave.php';
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
	kdPT 		=document.getElementById('kdPT').options[document.getElementById('kdPT').selectedIndex].value;
	kdUnit		=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	kdDivisi 	=document.getElementById('kdDivisi').options[document.getElementById('kdDivisi').selectedIndex].value;
	Tahun	 	=document.getElementById('Tahun').options[document.getElementById('Tahun').selectedIndex].value;
	kdBudget	=document.getElementById('kdBudget').value;
	kdKegiatan	=document.getElementById('kdKegiatan').value;
	kdBarang	=document.getElementById('kdBarang').value;
	kdVhc		=document.getElementById('kdVhc').value;
	if(kdPT=='' ||  kdUnit=='' ||  kdDivisi=='' ||  Tahun=='' ||  kdBudget=='' ||  kdKegiatan==''){
		alert('All fields are required');
	}else{
		param='kdPT='+kdPT+'&kdUnit='+kdUnit+'&kdDivisi='+kdDivisi+'&Tahun='+Tahun;
		param+='&kdBudget='+kdBudget+'&kdKegiatan='+kdKegiatan+'&kdBarang='+kdBarang+'&kdVhc='+kdVhc;
		param+='&proses=saveData';
		tujuan='bgt_unit_slave.php';
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

function fillfield(idbgtunit,Tahun,kdUnit,kdDivisi){
	document.getElementById('idbgtunit').value=idbgtunit;
	document.getElementById('kdUnit').value=kdDivisi.substr(0,4);
	document.getElementById('kdDivisi').value=kdDivisi;
	document.getElementById('Tahun').value=Tahun;
	document.getElementById('addedit').value='update';
}

function deldata(idbgtunit){
	param='idbgtunit='+idbgtunit;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'bgt_unit_slave.php';
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

function SaveCell(idbgtunit,cellname,cellvalue){
	cellvalue2=document.getElementById(cellname+idbgtunit).value;
	param='idbgtunit='+idbgtunit+'&cellname='+cellname+'&cellvalue='+cellvalue2;
	param+='&proses=SimpanCell';
	//alert(param);
	tujuan = 'bgt_unit_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//document.getElementById('container').innerHTML=con.responseText;
					//document.getElementById(cellname+idbgtunit).innerHTML=con.responseText;
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}

function postingdata(idbgtunit){
	param='idbgtunit='+idbgtunit;
	param+='&proses=postingData';
	tujuan = 'bgt_unit_slave.php';
	post_response_text(tujuan, param, respog);
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

function getUnit(){
    kdPT=document.getElementById('kdPT').options[document.getElementById('kdPT').selectedIndex].value;
    param='kdPT='+kdPT+'&proses=getUnit';
    tujuan='bgt_unit_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//alert(con.responseText);
					document.getElementById('kdUnit').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  	
}

function getDivisi(){
    kdPT=document.getElementById('kdPT').options[document.getElementById('kdPT').selectedIndex].value;
    kdUnit=document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
    param='kdPT='+kdPT;
    param+='&kdUnit='+kdUnit;
    param+='&proses=getDivisi';
    tujuan='bgt_unit_slave.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('kdDivisi').innerHTML=con.responseText;
					loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function showpopup(kodemesin,tanggal,kdorg,stasiun,type,ev){
   param='kdPT='+kdPT+'&kdUnit='+kdUnit+'&kdDivisi='+kdDivisi+'&tahun='+tahun+'&type='+type;
   tujuan='bgt_unit_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Laporan Budget Unit '+kdorg+' '+stasiun+' '+kodemesin+' '+tanggal,content,width,height,ev); 
}
