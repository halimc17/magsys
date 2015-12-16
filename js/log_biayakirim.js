
//JS 

function tambahBarang(title,ev)
{
    content= "<div id=formBarang style=\"height:300px;width:400px;overflow:scroll;\"></div>";
    title='Material';
    width='400';
    height='300';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang()
{
	param='method=getListBarang';
	//alert(param);
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
	function respog(){
			if (con.readyState == 4) {
					if (con.status == 200) {
							busy_off();
							if (!isSaveResponse(con.responseText)) {
									alert('ERROR TRANSACTION,\n' + con.responseText);
							}
							else {
								//alert(con.responseText);
									document.getElementById('formBarang').innerHTML=con.responseText;
							}
					}
					else {
							busy_off();
							error_catch(con.status);
					}
			}
	} 
		
}

function cariListBarang()
{
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
                    if (con.readyState == 4) {
                                    if (con.status == 200) {
                                                    busy_off();
                                                    if (!isSaveResponse(con.responseText)) {
                                                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                                                    }
                                                    else {
                                                            //alert(con.responseText);
                                                                    document.getElementById('formBarang').innerHTML=con.responseText;
                                                    }
                                    }
                                    else {
                                                    busy_off();
                                                    error_catch(con.status);
                                    }
                    }
    } 
		
}


function moveDataBarang(kodebarang,namabarang)
{
    document.getElementById('kodebarang').value=kodebarang;
    document.getElementById('namabarang').value=namabarang;
    closeDialog();
}



/////////////////////////////
////// document
/////////////////////////////////////

function tambahDok(title,ev)
{
    content= "<div id=formDok style=\"height:300px;width:400px;overflow:scroll;\"></div>";
    title='Document';
    width='400';
    height='300';
    showDialog1(title,content,width,height,ev);	
    getListDok();
}

function getListDok()
{
	param='method=getListDok';
	//alert(param);
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					//alert(con.responseText);
					document.getElementById('formDok').innerHTML=con.responseText;
				}
		}
		else {
				busy_off();
				error_catch(con.status);
			}
		}
	} 
		
}

function cariListDok()
{
    namaDokCari=document.getElementById('namaDokCari').value;
    param='method=getListDok'+'&namaDokCari='+namaDokCari;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					//alert(con.responseText);
					document.getElementById('formDok').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    } 
		
}


function moveDataDok(nodok)
{
	param='method=getBarang'+'&nopo='+nodok;
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					var res = JSON.parse(con.responseText),
						el = document.getElementById('kodebarang');
					el.options.length=0;
					for(i in res) {
						el.options[el.options.length] = new Option(res[i],i);
					}
					
					document.getElementById('nodok').value=nodok;
					closeDialog();
					
					// Load Gudang
					getGudang();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function cariBast(num)
{
    param='method=loadData';
    param+='&page='+num;
    tujuan = 'log_slave_biayakirim.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
						//displayList();

						document.getElementById('container').innerHTML=con.responseText;
						//loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }	
}

function simpan()
{
    kodebarang = getValue('kodebarang');
	kodegudang = getValue('kodegudang');
    nodok=document.getElementById('nodok').value;
    jumlah=document.getElementById('jumlah').value;
    method=document.getElementById('method').value;

    if(kodebarang=='' || nodok=='' || jumlah=='' || kodegudang=='')
    {
		alert('Semua field harus diisi');
		return;
    }

    param='kodebarang='+kodebarang+'&nodok='+nodok+'&kodegudang='+kodegudang+
		'&jumlah='+jumlah+'&kodetrp='+getValue('transporter')+'&method='+method;
    tujuan='log_slave_biayakirim.php';
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
					cancel();
					loadData();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function cancel()
{
    document.getElementById('kodebarang').innerHTML='';
	document.getElementById('kodegudang').innerHTML='';
	document.getElementById('transporter').selectedIndex=0;
    document.getElementById('nodok').value='';
    document.getElementById('jumlah').value='';
    document.getElementById('method').value='insert';
    document.getElementById('tmblCariNoDok').disabled=false;
	document.getElementById('kodebarang').disabled=false;
	document.getElementById('kodegudang').disabled=false;
    //document.getElementById('tmblCariNoGudang').disabled=false;
}

function loadData () 
{
	param='method=loadData';
	tujuan='log_slave_biayakirim.php';
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
                                   // alert(con.responseText);
                                    document.getElementById('container').innerHTML=con.responseText;
									
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function edit(kodebarang,namabarang,nodok,kodegudang,kodetrp,jumlah)
{
	param='method=getBarang'+'&nopo='+nodok;
	tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					var res = JSON.parse(con.responseText),
						el = document.getElementById('kodebarang'),
						selIndex = 0;
					for(i in res) {
						el.options[el.options.length] = new Option(res[i],i);
						if(i==kodebarang) selIndex = el.options.length+1;
					}
					
					document.getElementById('jumlah').value=jumlah;
					document.getElementById('nodok').value=nodok;
					document.getElementById('method').value='update';
					document.getElementById('tmblCariNoDok').disabled=true;
					document.getElementById('kodebarang').disabled=true;
					document.getElementById('kodegudang').disabled=true;
					setValue('transporter',kodetrp);
					//document.getElementById('tmblCariNoGudang').disabled=true;
					
					getGudang(kodegudang);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function del(kodebarang,nodok,kodegudang)
{
	param='method=delete'+'&kodebarang='+kodebarang+'&nodok='+nodok+'&kodegudang='+kodegudang;
	tujuan='log_slave_biayakirim.php';
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
					else 
					{
						loadData();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}



function cari()
{
    nodoksch=document.getElementById('nodoksch').value;
    if(nodoksch=='')
    {
            alert('Field Was Empty');
            return;
    }
    param='method=loadData'+'&nodoksch='+nodoksch;
    tujuan='log_slave_biayakirim.php';
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
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function posting(kodebarang, namabarang, nopo, kodegudang) {
	param='method=posting'+'&nopo='+nopo+'&kodebarang='+kodebarang+'&kodegudang='+kodegudang;
	tujuan = 'log_slave_biayakirim.php';
	if(confirm("Anda akan melakukan posting biaya kirim untuk PO "+nopo+
			   " pada Barang "+namabarang+"\nAnda yakin?"))
		post_response_text(tujuan, param, respog);
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					var icon = document.getElementById(kodebarang+nopo),
						iconEdit = document.getElementById(kodebarang+nopo+'_edit'),
						iconDel = document.getElementById(kodebarang+nopo+'_delete');
					icon.removeAttribute('src');
					icon.setAttribute('src','images/buttongreen.png')
					icon.removeAttribute('onclick');
					iconEdit.style.display = 'none';
					iconEdit.removeAttribute('onclick');
					iconDel.style.display = 'none';
					iconDel.removeAttribute('onclick');
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}

function getGudang(gudang) {
	if(typeof gudang=='undefined') gudang='';
	var kodebarang = getValue('kodebarang'),
		nodok = getValue('nodok'),
		param='method=getGudang'+'&nopo='+nodok+'&kodebarang='+kodebarang,
		tujuan = 'log_slave_biayakirim.php';
	post_response_text(tujuan, param, respog);		
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					var res = JSON.parse(con.responseText),
						el = document.getElementById('kodegudang'),
						selIndex = 0;
					el.options.length = 0;
					for(i in res) {
						if(gudang == i) selIndex = el.options.length;
						el.options[el.options.length] = new Option(res[i],i);
					}
					el.selectedIndex = selIndex;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
    }
}