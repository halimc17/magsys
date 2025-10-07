/**
 * Pabrik Limbah B3
 */

function loadData(num){
	carikodeorg		=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1	=document.getElementById('caritanggal1').value;
	caritanggal2	=document.getElementById('caritanggal2').value;
	carikodebarang	=document.getElementById('carikodebarang').options[document.getElementById('carikodebarang').selectedIndex].value;
	kodeorg			=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='proses=loadData'+'&kodeorg='+kodeorg;
	param+='&carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikodebarang='+carikodebarang;
	param+='&page='+num;
	tujuan='pabrik_limbahb3_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('page').value=num;
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
	kodeorg 		=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	kodeorglama		=document.getElementById('kodeorglama').value;
	tanggal  		=document.getElementById('tanggal').value;
	tanggallama		=document.getElementById('tanggallama').value;
	kodemesin 		=document.getElementById('kodemesin').options[document.getElementById('kodemesin').selectedIndex].value;
	kodemesinlama	=document.getElementById('kodemesinlama').value;
	kodebarang 		=document.getElementById('kodebarang').options[document.getElementById('kodebarang').selectedIndex].value;
	kodebaranglama	=document.getElementById('kodebaranglama').value;
	qtymasuk	  	=document.getElementById('qtymasuk').value;
	qtykeluar	  	=document.getElementById('qtykeluar').value;
	keterangan 		=document.getElementById('keterangan').value;
	addedit			=document.getElementById('addedit').value;
	page			=document.getElementById('page').value;
	if(kodeorg=='' || tanggal=='' || kodebarang==''){
		alert('Fields are required');
	}else{
		param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&kodemesin='+kodemesin+'&kodebarang='+kodebarang;
		param+='&qtymasuk='+qtymasuk+'&qtykeluar='+qtykeluar+'&keterangan='+keterangan;
		param+='&kodeorglama='+kodeorglama+'&tanggallama='+tanggallama+'&kodemesinlama='+kodemesinlama+'&kodebaranglama='+kodebaranglama;
		param+='&addedit='+addedit+'&page='+page+'&proses=saveData';
		tujuan='pabrik_limbahb3_slave.php';
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

function fillfield(kodeorg,tanggal,kodemesin,kodebarang,qtymasuk,qtykeluar,keterangan){
	document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kodeorglama').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('tanggallama').value=tanggal;
	document.getElementById('kodemesin').value=kodemesin;
	document.getElementById('kodemesinlama').value=kodemesin;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('kodebaranglama').value=kodebarang;
	document.getElementById('qtymasuk').value=qtymasuk;
	document.getElementById('qtykeluar').value=qtykeluar;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('addedit').value='update';
}

function bersihkanForm(){
	document.getElementById('kodeorg').value='';
	document.getElementById('kodeorglama').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggallama').value='';
	document.getElementById('kodemesin').value='';
	document.getElementById('kodemesinlama').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('kodebaranglama').value='';
	document.getElementById('qtymasuk').value=0;
	document.getElementById('qtykeluar').value=0;
	document.getElementById('keterangan').value='';
	document.getElementById('addedit').value='insert';
}

function deldata(kodeorg,tanggal,kodemesin,kodebarang,page){
	param='kodeorg='+kodeorg+'&tanggal='+tanggal+'&kodemesin='+kodemesin+'&kodebarang='+kodebarang;
	param+='&proses=delData';
	if (confirm('Delete ..?')) {
		tujuan = 'pabrik_limbahb3_slave.php';
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
					loadData(page);
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
    param='kodeorg='+kodeorg;
    param+='&proses=getMesin';
	tujuan='pabrik_limbahb3_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    //document.getElementById('namainv').innerHTML=con.responseText;
					if(con.responseText!=''){
						document.getElementById('kodemesin').innerHTML=con.responseText;
					}
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function cariinventaris(type,ev){
	carikodeorg		=document.getElementById('carikodeorg').options[document.getElementById('carikodeorg').selectedIndex].value;
	caritanggal1	=document.getElementById('caritanggal1').value;
	caritanggal2	=document.getElementById('caritanggal2').value;
	carikodebarang	=document.getElementById('carikodebarang').options[document.getElementById('carikodebarang').selectedIndex].value;
	page		=document.getElementById('page').value;
	param='carikodeorg='+carikodeorg+'&caritanggal1='+caritanggal1+'&caritanggal2='+caritanggal2+'&carikodebarang='+carikodebarang+'&type='+type;
	tujuan='pabrik_limbahb3_showpopup.php?'+param;
	title='Pabrik Limbah B3 '+carikodeorg+' '+carikodebarang;
	width='720';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}
