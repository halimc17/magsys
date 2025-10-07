// JavaScript Document
function btldendapanen()
{
	document.getElementById('editBPJS').style.display='none';
}

function loadData(){
	param='method=loaddata';
	tujuan='sdm_slave_5bpjs.php';
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
							btldendapanen();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}

function fillfield(lokasibpjs,jenisbpjs,bebankaryawan,bebanperusahaan,maxgaji){
	document.getElementById('editBPJS').style.display='';
	document.getElementById('bebankaryawan').value=bebankaryawan;
	document.getElementById('bebanperusahaan').value=bebanperusahaan;
	document.getElementById('maxgaji').value=maxgaji;
	LLokasi=document.getElementById('lokasibpjs');
    for(ard=0;ard<LLokasi.length;ard++)
    {
        if(LLokasi.options[ard].value==lokasibpjs)
            {
                LLokasi.options[ard].selected=true;
            }
    }
	LJenis=document.getElementById('jenisbpjs');
	for(ard=0;ard<LJenis.length;ard++){
		if(LJenis.options[ard].value==jenisbpjs){
			LJenis.options[ard].selected=true;
		}
	}
}

function simpadendapanen()
{
	lokasibpjs=document.getElementById('lokasibpjs').options[document.getElementById('lokasibpjs').selectedIndex].value;
	jenisbpjs=document.getElementById('jenisbpjs').options[document.getElementById('jenisbpjs').selectedIndex].value;
	bebankaryawan=document.getElementById('bebankaryawan').value;
	bebanperusahaan=document.getElementById('bebanperusahaan').value;
	maxgaji=document.getElementById('maxgaji').value;
	method=trim(document.getElementById('method').value);
	
	param='lokasibpjs='+lokasibpjs+'&jenisbpjs='+jenisbpjs+'&bebankaryawan='+bebankaryawan+'&bebanperusahaan='+bebanperusahaan+'&maxgaji='+maxgaji+'&method='+method;
	tujuan='sdm_slave_5bpjs.php';
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
							btldendapanen();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
}