// JavaScript Document
function batal()
{
	document.getElementById('method').value='insert';
	document.getElementById("kdKlBarang").selectedIndex = "0";
	document.getElementById("kdKlBarang").disabled = false;
	document.getElementById('kdSubKl').value='';
	document.getElementById('namaSubKl').value='';
}

function getKodeSubKelompok(){
	kdKlBarang=document.getElementById('kdKlBarang').options[document.getElementById('kdKlBarang').selectedIndex].value;
	
	param='kdKlBarang='+kdKlBarang+'&method=getKodeSub';
	tujuan='log_slave_5subkelompokbarang.php';
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
							document.getElementById('kdSubKl').value=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function loadData(){
	param='method=loaddata';
	tujuan='log_slave_5subkelompokbarang.php';
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
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(kelompok,kode,namaSubKl){
	Lkd_org=document.getElementById('kdKlBarang');
    for(ard=0;ard<Lkd_org.length;ard++)
    {
        if(Lkd_org.options[ard].value==kelompok)
            {
                Lkd_org.options[ard].selected=true;
            }
    }
	document.getElementById('kdKlBarang').disabled=true;
	document.getElementById('kdSubKl').disabled=true;
	document.getElementById('kdSubKl').value=kode;
	document.getElementById('namaSubKl').value=namaSubKl;
	document.getElementById('method').value='edit';
}

function simpan()
{
	kdKlBarang=document.getElementById('kdKlBarang').options[document.getElementById('kdKlBarang').selectedIndex].value;
	kdSubKl=trim(document.getElementById('kdSubKl').value);
	namaSubKl=trim(document.getElementById('namaSubKl').value);
	method=trim(document.getElementById('method').value);
	
	param='kdKlBarang='+kdKlBarang+'&kdSubKl='+kdSubKl+'&namaSubKl='+namaSubKl+'&method='+method;
	tujuan='log_slave_5subkelompokbarang.php';
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
							document.getElementById('container').innerHTML=con.responseText;
							batal();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function deletefield(kode){
	param='kdSubKl='+kode+'&method=delete';
	tujuan='log_slave_5subkelompokbarang.php';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
}