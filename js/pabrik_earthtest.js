/**
 * Pabrik Earth Test
 */

function loadData(){
	kodeorg=document.getElementById('kodeorg').value;
	mesin=document.getElementById('mesin').value;
	param='proses=loadData'+'&kodeorg='+kodeorg+'&mesin='+mesin;
	tujuan='pabrik_earthtest_slave.php';
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
	mesin 		=document.getElementById('mesin').options[document.getElementById('mesin').selectedIndex].value;
	tanggal  	=document.getElementById('tanggal').value;
	ukur1		=document.getElementById('ukur1').value;
	ukur2		=document.getElementById('ukur2').value;
	ukur3		=document.getElementById('ukur3').value;
	standard	=document.getElementById('standard').value;
	keterangan	=document.getElementById('keterangan').value;
	stdlama		=document.getElementById('stdlama').value;
	tgllama		=document.getElementById('tgllama').value;
	ketlama		=document.getElementById('ketlama').value;
	addedit		=document.getElementById('addedit').value;
	if(kodeorg=='' ||  mesin=='' ||  tanggal==''){
		alert('All fields are required');
	}else{
		param='kodeorg='+kodeorg+'&mesin='+mesin+'&tanggal='+tanggal;
		param+='&ukur1='+ukur1+'&ukur2='+ukur2+'&ukur3='+ukur3;
		param+='&standard='+standard+'&stdlama='+stdlama+'&tgllama='+tgllama+'&ketlama='+ketlama;
		param+='&keterangan='+keterangan+'&addedit='+addedit+'&proses=saveData';
		tujuan='pabrik_earthtest_slave.php';
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

function fillfield(kodeorg,mesin,tanggal,standard,ukur1,ukur2,ukur3,keterangan){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('mesin').value=mesin;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tgllama').value=tanggal;
	document.getElementById('standard').value=standard;
	document.getElementById('stdlama').value=standard;
	document.getElementById('ukur1').value=ukur1;
	document.getElementById('ukur2').value=ukur2;
	document.getElementById('ukur3').value=ukur3;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('ketlama').value=keterangan;
	document.getElementById('addedit').value='update';
}

function bersihkanForm(){
	//document.getElementById('kodeorg').value=kodeorg;
	//document.getElementById('mesin').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tgllama').value='';
	document.getElementById('standard').value=0;
	document.getElementById('stdlama').value=0;
	document.getElementById('ukur1').value=0;
	document.getElementById('ukur2').value=0;
	document.getElementById('ukur3').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('ketlama').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,mesin,tanggal,standard,keterangan){
	param='kodeorg='+kodeorg+'&mesin='+mesin+'&tanggal='+tanggal+'&standard='+standard+'&keterangan='+keterangan;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'pabrik_earthtest_slave.php';
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

function showpopup(kodemesin,tanggal,kdorg,type,ev){
   param='kodemesin='+kodemesin+'&tanggal='+tanggal+'&kdorg='+kdorg+'&type='+type;
   tujuan='pabrik_lap_earthtest_showpopup.php'+"?"+param;
   width='1200';
   height='470';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Laporan Earth Test '+kdorg+' '+kodemesin+' '+tanggal,content,width,height,ev); 
}
