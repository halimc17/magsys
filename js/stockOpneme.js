/**
 * @author repindra.ginting
 */
function searchBarang(title,content,ev) {
	width='500';
	height='400';
	showDialog1(title,content,width,height,ev);
}

function findBarang() {
	txt=trim(document.getElementById('namabrg').value);
	if(txt.length<3) {
        alert('Minimum text is 3 char');
    } else {
		param='txtfind='+txt;
		tujuan='log_slave_get_barang.php';
		post_response_text(tujuan, param, respog);
	}
	
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
            } else {
				busy_off();
				error_catch(con.status);
			}
        }	
    }  	
}

function setKodeBarang(kelompok,kode,nama,satuan) {
	document.getElementById('namadisabled').value=nama;
	document.getElementById('sat').innerHTML=satuan;
	document.getElementById('kodebarang').innerHTML=kode;
	checkChkNol();
	closeDialog();
}

function saveAdjustment() {
    kodebarang=document.getElementById('kodebarang').innerHTML;
    kodegudang=getValue('kodegudang');
    jumlah=getValue('jumlah');
    harga=getValue('harga');
    chkNol=getValue('chkNol')
    
    
    jenisAdjust=getValue('jenisAdjust');
    
    if(harga=='') {
        harga=0;
    }
    if(jumlah=='') {
        jumlah=0;
    }
    
    if(!kodegudang || kodebarang=='') {
        alert('Data incomplete');
    }
    //jika masuk
    else if (jenisAdjust=='in' && harga==0)
    {
        alert('Harga harus di-isi');
    }
    else if(jenisAdjust=='out' && harga==0 && chkNol=='0')
    {
        alert('Harga harus di-isi');
    }
    else {
        param='kodebarang='+kodebarang+'&kodegudang='+kodegudang+'&harga='+harga+
			'&jumlah='+jumlah+'&jenis='+getValue('jenisAdjust')+
			'&notransreferensi='+getValue('notransreferensi')+
			'&keterangan='+getValue('keterangan');
        tujuan='log_slave_stockOpname.php';
        if(confirm('Update material balance..?')){
			post_response_text(tujuan, param, respog);
		}
    }
    
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alert('Done');
					document.getElementById('namadisabled').value='';
					document.getElementById('sat').innerHTML='';
					document.getElementById('kodebarang').innerHTML='';
					document.getElementById('jumlah').value=0;
					document.getElementById('harga').value=0;
					setValue('notransreferensi','');
					setValue('keterangan','');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
        }	
    }  	
}

/**
 * changeJenis
 * onChange field Jenis, jika transaksi keluar maka freeze rupiah
 */
function changeJenis() {
	var jenis = getValue('jenisAdjust'),
		harga = getById('harga');
	if(jenis=='in') {
		harga.disabled = false;
		document.getElementById('divChkNol').style.display = 'none';
	} else {
		harga.disabled = true;
		document.getElementById('divChkNol').style.display = 'block';
		harga.value = 0;
	}
	checkChkNol();
}

function getHargaTerakhir(){
	kodebarang=document.getElementById('kodebarang').innerHTML;
	kodegudang=getValue('kodegudang');
	jenisAdjust=getValue('jenisAdjust');
	if(jenisAdjust=='out'){
		if(kodebarang != ''){
			param='kodebarang='+kodebarang+'&kodegudang='+kodegudang;
			tujuan='log_slave_stockOpnameHarga.php';
			post_response_text(tujuan, param, respog);
		}
	}else{
		document.getElementById('harga').value=0;
	}
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('harga').value=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	} 	
	
}

function checkChkNol(){
	chkNol=document.getElementById('chkNol');
	if(chkNol.checked==true){
        document.getElementById('harga').value=0;
	}else{
		getHargaTerakhir();
	}
}