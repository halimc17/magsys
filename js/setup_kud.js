function clearData(){
	document.getElementById('kodeblok').selectedIndex = 0;
	document.getElementById('kodeblok').disabled = false;
	document.getElementById('supplierid').selectedIndex = 0;
	document.getElementById('supplierid').disabled = false;
	document.getElementById('nosertifikat').value = '';
	document.getElementById('hiddenproses').value = 'simpan';
	document.getElementById('hiddensupplierid').value = '';
}

function getAfdeling(currEls,targetId) {
    var kebun = currEls;
    var afdeling = document.getElementById(targetId);
    
    // If blank, quit
    if(kebun.options[kebun.options.selectedIndex].value=='') {
        formKUD.style.display = 'none';
		KUDTable.style.display = 'none';
		afdeling.options.length=0;
		clearData();
		return;
    }
    
    // Clear Afdeling
    afdeling.options.length=0;
    
    var param = "kebun="+kebun.options[kebun.options.selectedIndex].value+
        "&afdelingId="+targetId;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    eval(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_blok_afdeling.php', param, respon);
}

function showData() {
    var KUDTable = document.getElementById('KUDTable');
    var kebun = document.getElementById('sKebun');
    var afdeling = document.getElementById('sAfdeling');
    var formKUD = document.getElementById('formKUD');
	
	if(kebun.options[kebun.options.selectedIndex].value=='') {
        alert('Kebun harus dipilih');
        return;
    }
    
    if(afdeling.options.length>0) {
        var param = "kebun="+kebun.options[kebun.options.selectedIndex].value+
            "&afdeling="+afdeling.options[afdeling.options.selectedIndex].value;
    } else {
        alert('Tidak ada afdeling pada kebun tersebut');
        return;
    }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
					document.getElementById('hiddenproses').value="simpan";
					KUDTable.innerHTML = con.responseText;
                    updBlokDropdown();
					formKUD.style.display = 'block';
					KUDTable.style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kud_data.php', param, respon);
}

function simpanData(){
	kodeblok=document.getElementById('kodeblok').value;
	supplierid=document.getElementById('supplierid').value;
	nosertifikat=document.getElementById('nosertifikat').value;
	afdeling = document.getElementById('sAfdeling').value;
	hiddensupplierid=document.getElementById('hiddensupplierid').value;
	proses=document.getElementById('hiddenproses').value;
	
	if(kodeblok=='') {
        alert('Kode Blok harus dipilih');
        return;
    }
	if(supplierid=='') {
        alert('Nama Supplier harus dipilih');
        return;
    }
	if(nosertifikat=='') {
        alert('No sertifikat harus diisi');
        return;
    }
	
	var param = "kodeblok="+kodeblok+"&supplierid="+supplierid+"&nosertifikat="+nosertifikat+"&afdeling="+afdeling+"&proses="+proses+'&hiddensupplierid='+hiddensupplierid;
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    clearData();
                    KUDTable.innerHTML = con.responseText;
                    //updBlokDropdown();
					formKUD.style.display = 'block';
					KUDTable.style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kud_data.php', param, respon);
}

function updBlokDropdown() {
    var kodeblok = document.getElementById('kodeblok');
    var afdeling = document.getElementById('sAfdeling');
    var param = "afdeling="+afdeling.options[afdeling.options.selectedIndex].value;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    kodeblok.options.length=0;
                    eval(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kud_blok.php', param, respon);
}

function editRow(kodeblok,supplierid,nosertifikat) {
	document.getElementById('hiddenproses').value = "update";
	document.getElementById('nosertifikat').value = nosertifikat;
	document.getElementById('kodeblok').disabled = true;
	document.getElementById('hiddensupplierid').value = supplierid;
	k=document.getElementById('kodeblok');
	l=document.getElementById('supplierid');
	
	for(a=0;a<k.length;a++)
    {
        if(k.options[a].value==kodeblok)
        {
            k.options[a].selected=true;
        }
    }
    
    for(a=0;a<l.length;a++)
    {
        if(l.options[a].value==supplierid)
        {
            l.options[a].selected=true;
        }
    }
}

function deleteitem(kodeblok,supplierid) {
	afdeling = document.getElementById('sAfdeling').value;
    var param = "kodeblok="+kodeblok+"&supplierid="+supplierid+"&afdeling="+afdeling;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    clearData();
                    KUDTable.innerHTML = con.responseText;
                    ormKUD.style.display = 'block';
					KUDTable.style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('setup_slave_kud_data.php?proses=delete', param, respon);
}