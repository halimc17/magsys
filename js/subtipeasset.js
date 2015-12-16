function simpanSubTipeAset()
{
	tipeasset = getValue('tipeasset');
	kodesubasset = getValue('kodesubasset');
	namasubasset = getValue('namasubasset');
	umurpenyusutan = getValue('umurpenyusutan');
	proses = getValue('save');
	
	if(trim(kodesubasset)=='' || trim(namasubasset)=='' || trim(umurpenyusutan)=='')
	{
		alert('All fields must be filled');
		document.getElementById('kodesubasset').focus();
	}else if(kodesubasset.length <= 1){
		alert('Field kode sub asset must be 2 character');
		document.getElementById('kodesubasset').focus();
	}
	else
	{
		kodesubasset=trim(kodesubasset);
		namasubasset=trim(namasubasset);
		umurpenyusutan=trim(umurpenyusutan);
		param='tipeasset='+tipeasset+'&kodesubasset='+kodesubasset+'&namasubasset='+namasubasset+'&umurpenyusutan='+umurpenyusutan+'&proses='+proses;
		tujuan='sdm_slave_5subtipeasset.php';
		post_response_text(tujuan, param, respog);		
	}

	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
					cancelSubTipeAsset();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function editSubTipeAset(kodesubasset,namasubasset,umurpenyusutan,tipeasset)
{
	setValue('save','edit');
	document.getElementById('kodesubasset').value=kodesubasset;
	document.getElementById('kodesubasset').disabled=true;
	document.getElementById('namasubasset').value=namasubasset;
	document.getElementById('umurpenyusutan').value=umurpenyusutan;
	x=document.getElementById('tipeasset');
	for(a=0;a<x.length;a++)
	{
		if(x.options[a].value==tipeasset)
		{
				x.options[a].selected=true;
		}
	}
	document.getElementById('tipeasset').disabled=true;
}

function cancelSubTipeAsset()
{
	//document.location.reload();
	document.getElementById('tipeasset').options[0].selected=true;
	document.getElementById('tipeasset').disabled=false;
	document.getElementById('kodesubasset').value='';
	document.getElementById('kodesubasset').disabled=false;
	document.getElementById('namasubasset').value='';
	document.getElementById('umurpenyusutan').value='';	
}
