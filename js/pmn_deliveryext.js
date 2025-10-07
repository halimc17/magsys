// JavaScript Document

function displayList(num){
	if (num==1){
		cariBast(num);
		//document.getElementById('kdUnitCr').value='';
		//document.getElementById('kdCustCr').value='';
		//document.getElementById('kdBrgCr').value='';
		//document.getElementById('noKontrakCr').value='';
	}else{
		document.getElementById('kdUnitCr').value='';
		document.getElementById('kdCustCr').value='';
		document.getElementById('kdBrgCr').value='';
		document.getElementById('noKontrakCr').value='';
	}
    //loadHead();
	document.location.reload();
}

function cariBast(num){
    //kdUnitCr=document.getElementById('kdUnitCr').options[document.getElementById('kdUnitCr').selectedIndex].value;
    //kdCustCr=document.getElementById('kdCustCr').options[document.getElementById('kdCustCr').selectedIndex].value;
    //kdBrgCr=document.getElementById('kdBrgCr').options[document.getElementById('kdBrgCr').selectedIndex].value;
    //noKontrakCr=document.getElementById('noKontrakCr').options[document.getElementById('noKontrakCr').selectedIndex].value;

    kdUnitCr=document.getElementById('kdUnitCr').value;
    kdCustCr=document.getElementById('kdCustCr').value;
    kdBrgCr=document.getElementById('kdBrgCr').value;
    noKontrakCr=document.getElementById('noKontrakCr').value;

	param='method=loadHead';
	if(kdUnitCr!=''){
        param+='&kdUnitCr='+kdUnitCr;
    }
	if(kdCustCr!=''){
        param+='&kdCustCr='+kdCustCr;
    }
	if(kdBrgCr!=''){
        param+='&kdBrgCr='+kdBrgCr;
    }
	if(noKontrakCr!=''){
        param+='&noKontrakCr='+noKontrakCr;
    }
	param+='&page='+num;
	//alert(param);
	tujuan='pmn_slave_deliveryext.php';
	post_response_text(tujuan, param, respog);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('boxhead').innerHTML=con.responseText;
					document.getElementById('container').innerHTML='';
				}
	        }else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadHead (kode,kodeorg,kodecustomer,kodebarang){
	document.getElementById('kodedetail').value=kode;
	param='method=loadHead'+'&kode='+kode+'&kodeorg='+kodeorg+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
    tujuan='pmn_slave_deliveryext.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					// alert(con.responseText);
					document.getElementById('boxhead').innerHTML=con.responseText;
                }
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function loadData (kode,kodeorg,kodecustomer,kodebarang){
	document.getElementById('kodedetail').value=kode;
	param='method=loadData'+'&kode='+kode+'&kodeorg='+kodeorg+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
    tujuan='pmn_slave_deliveryext.php';
    post_response_text(tujuan, param, respog);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					// alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
                }
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}  
}

function deldetail(kode,kodeorg,kodecustomer,kodebarang,notransaksidet){
	param='method=deldetail'+'&notransaksidet='+notransaksidet;
	//alert(notransaksidet);
	tujuan='pmn_slave_deliveryext.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loadData(kode,kodeorg,kodecustomer,kodebarang);
					//cariBast();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function simpandetail(kode,kodeorg,kodecustomer,kodebarang){
	nokontrakextdet=document.getElementById('nokontrakextdet').value;
	nokontrakdet=document.getElementById('nokontrakdet').value;
	//nosipbdet=document.getElementById('nosipbdet').value;
	tanggaldet=document.getElementById('tanggaldet').value;
	beratbersihdet=document.getElementById('beratbersihdet').value;
	//notransaksidet=document.getElementById('notransaksidet').value;
	//keterangandet=document.getElementById('keterangandet').value;
	param='method=simpandetail'+'&nokontrakextdet='+nokontrakextdet+'&nokontrakdet='+nokontrakdet+'&tanggaldet='+tanggaldet+'&beratbersihdet='+beratbersihdet+'&kodecustomer='+kodecustomer;
	tujuan='pmn_slave_deliveryext.php';
    post_response_text(tujuan, param, respog);		
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//cariBast();
					loadData(kode,kodeorg,kodecustomer,kodebarang);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function editdetail(kode,kodeorg,kodecustomer,kodebarang,notransaksidet,no){
	//document.getElementById('nokontrakextdet'+no).disabled=false;
	document.getElementById('nokontrakdet'+no).disabled=false;
	document.getElementById('tanggaldet'+no).disabled=false;
	document.getElementById('beratbersihdet'+no).disabled=false;
}

function simpanedit(kode,kodeorg,kodecustomer,kodebarang,notransaksidet,no){
	if(!document.getElementById('nokontrakdet'+no).disabled){
		nokontrakextdet=document.getElementById('nokontrakextdet'+no).value;
		nokontrakdet=document.getElementById('nokontrakdet'+no).value;
		//nosipbdet=document.getElementById('nosipbdet'+no).value;
		tanggaldet=document.getElementById('tanggaldet'+no).value;
		str=document.getElementById('beratbersihdet'+no).value;
		beratbersihdet=str.replace(",", "");
		//notransaksidet=document.getElementById('notransaksidet'+no).value;
		//keterangandet=document.getElementById('keterangandet'+no).value;
		param='method=simpanedit'+'&nokontrakextdet='+nokontrakextdet+'&nokontrakdet='+nokontrakdet+'&tanggaldet='+tanggaldet+'&beratbersihdet='+beratbersihdet+'&kodecustomer='+kodecustomer+'&notransaksidet='+notransaksidet;
		tujuan='pmn_slave_deliveryext.php';
		post_response_text(tujuan, param, respog);		
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//cariBast();
						loadData(kode,kodeorg,kodecustomer,kodebarang);
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}

function postingdetail(kode,kodeorg,kodecustomer,kodebarang,notransaksidet,no){
	nokontrakextdet=document.getElementById('nokontrakextdet'+no).value;
	nokontrakdet=document.getElementById('nokontrakdet'+no).value;
	//nosipbdet=document.getElementById('nosipbdet'+no).value;
	tanggaldet=document.getElementById('tanggaldet'+no).value;
	beratbersihdet=document.getElementById('beratbersihdet'+no).value;
	//notransaksidet=document.getElementById('notransaksidet'+no).value;
	//keterangandet=document.getElementById('keterangandet'+no).value;
	param='method=postingdetail'+'&nokontrakextdet='+nokontrakextdet+'&nokontrakdet='+nokontrakdet+'&tanggaldet='+tanggaldet+'&beratbersihdet='+beratbersihdet+'&kodeorg='+kodeorg+'&kodecustomer='+kodecustomer+'&notransaksidet='+notransaksidet;
	tujuan='pmn_slave_deliveryext.php';
	post_response_text(tujuan, param, respog);		
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					//cariBast();
					loadData(kode,kodeorg,kodecustomer,kodebarang);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
