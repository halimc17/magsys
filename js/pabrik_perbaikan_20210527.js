function tambahBarang(title,ev)
{
    
    content= "<div id=formBarang style=\"height:250px;width:350;overflow:scroll;\"></div>";
    title='Add Material';
    height='250';
    width='350';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang()
{
    param='method=getListBarang';
    tujuan = 'pabrik_slave_perbaikan.php';
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
    pabrik=document.getElementById('pabrik').value;
    tglOrder=document.getElementById('tglOrder').value;
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari+'&pabrik='+pabrik+'&tglOrder='+tglOrder;
  
    tujuan = 'pabrik_slave_perbaikan.php';
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



function moveDataBarang(kodebarang,namabarang,satuanbarang,hargabarang)
{
    document.getElementById('kodeBarang').value=kodebarang;
    document.getElementById('namaBarang').value=namabarang;
    document.getElementById('satuanBarang').value=satuanbarang;
    document.getElementById('hargabarang').value=hargabarang;
    //document.getElementById('').innerHTML=con.responseText;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
	
}




function getMesin(station,mesin)
{
    station=document.getElementById('station').value; 
    param='method=getMesin'+'&station='+station+'&mesin='+mesin;
    tujuan='pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('mesin').innerHTML=con.responseText;
                    getNodok();
                    //.value=trim(con.responseText);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function getNodok()
{
    station=document.getElementById('station').value; 
    tglOrder=document.getElementById('tglOrder').value; 
    param='method=getNodok'+'&station='+station+'&tglOrder='+tglOrder;
    tujuan='pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('nodok').value=trim(con.responseText);
                    //.value=trim(con.responseText);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}


function get_isi(kdorg)
{
	//param='kdorg='+kdorg'+;
	param='method=getnomor'+'&kdorg='+kdorg;
	tujuan='pabrik_slave_perbaikan.php';
	post_response_text(tujuan, param, respog);
	//alert(param);
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
							//alert(con.responseText);
							document.getElementById('notran').value=trim(con.responseText);
							//document.getElementById('dtl_pem').disabled=false;
						   
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
		tujuan = 'pabrik_slave_perbaikan.php';
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
						
						document.getElementById('contain').innerHTML=con.responseText;
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

function cari()
{
	schNodok=trim(document.getElementById('schNodok').value);
	schTgl=trim(document.getElementById('schTgl').value);
	param='schNodok='+schNodok;
	param+='&schTgl='+schTgl;
	param+='&method=loadData';//loadSch
	//alert(param);
	tujuan = 'pabrik_slave_perbaikan.php';
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
							document.getElementById('listData').style.display='block';
							document.getElementById('headher').style.display='none';
							document.getElementById('detailEntry').style.display='none';
							document.getElementById('contain').innerHTML=con.responseText;	
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }	
}


function loadData()
{
		param='method=loadData';
		tujuan = 'pabrik_slave_perbaikan.php';
                //alert(param);
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
						
						document.getElementById('contain').innerHTML=con.responseText;
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

function Lock(notran)
{
	param='method=lock'+'&notran='+notran;
	//alert(param);
	tujuan='pabrik_slave_perbaikan.php';
	if(confirm("Anda yakin ingin mengunci ??"))
	{
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
					}
					else 
					{
						 document.getElementById('contain').innerHTML=con.responseText;
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


//function uuntuk delet headernya yg ada di list tampilan data
function Del(notran)
{
	param='method=delete'+'&notran='+notran;
	//alert(param);
	tujuan='pabrik_slave_perbaikan.php';
	if(confirm(' Anda yakin ingin menghapus nomor transaksi '+notran+' '))
	{
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
					}
					else 
					{
						 document.getElementById('contain').innerHTML=con.responseText;
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

function add_new_data()//indra
{
	//alert('MASUK COI');
	//alert(con.responseText);
	document.getElementById('headher').style.display='block';
	document.getElementById('listData').style.display='none';
	document.getElementById('detailEntry').style.display='none';
        cancelHead();
	//document.getElementById('contentDetail').innerHTML='';
	//bukaform();
        document.getElementById('method').value='insert';
        
        
}


function displayList()
{
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	document.getElementById('detailEntry').style.display='none';
	document.getElementById('schTgl').value='';
        document.getElementById('schNodok').value='';
	loadData();
}



function saveHeader()//save header + buka input detail di sini
{
    nodok=document.getElementById('nodok').value;
    tglOrder=document.getElementById('tglOrder').value;
    jmOrder=document.getElementById('jmOrder').value;
    mnOrder=document.getElementById('mnOrder').value;
    namaPemohon=document.getElementById('namaPemohon').value;
    statusPemohon=document.getElementById('statusPemohon').value;
    pabrik=document.getElementById('pabrik').value;
    station=document.getElementById('station').value;
    mesin=document.getElementById('mesin').value;
    shift=document.getElementById('shift').value;
    tipePerbaikan=document.getElementById('tipePerbaikan').value;
    uraianKerusakan=document.getElementById('uraianKerusakan').value;
    tglMulai=document.getElementById('tglMulai').value;
    jmMulai=document.getElementById('jmMulai').value;
    mnMulai=document.getElementById('mnMulai').value;
    tglSelesai=document.getElementById('tglSelesai').value;
    jmSelesai=document.getElementById('jmSelesai').value;
    mnSelesai=document.getElementById('mnSelesai').value;
    jumlahJamPerbaikan=document.getElementById('jumlahJamPerbaikan').value;
    statusKetuntasan=document.getElementById('statusKetuntasan').value;
    hasilKerja=document.getElementById('hasilKerja').value;
    komMain=document.getElementById('komMain').value;
    komPros=document.getElementById('komPros').value;
    method=document.getElementById('method').value;
 
 
    if(nodok=='' || tglOrder=='' || pabrik=='' || station=='' || mesin=='' || tglMulai=='')
    {
        alert('please compleate the form');return;
    }
 

    param='nodok='+nodok+'&tglOrder='+tglOrder+'&jmOrder='+jmOrder+'&mnOrder='+mnOrder+'&namaPemohon='+namaPemohon;
    param+='&statusPemohon='+statusPemohon+'&pabrik='+pabrik+'&station='+station+'&mesin='+mesin+'&shift='+shift;
    param+='&tipePerbaikan='+tipePerbaikan+'&uraianKerusakan='+uraianKerusakan;
    param+='&tglMulai='+tglMulai+'&jmMulai='+jmMulai+'&mnMulai='+mnMulai;
    param+='&tglSelesai='+tglSelesai+'&jmSelesai='+jmSelesai+'&mnSelesai='+mnSelesai;
    param+='&jumlahJamPerbaikan='+jumlahJamPerbaikan+'&statusKetuntasan='+statusKetuntasan+'&hasilKerja='+hasilKerja;
     param+='&komMain='+komMain+'&komPros='+komPros;
    param+='&method='+method;

   

    tujuan='pabrik_slave_perbaikan.php';
    //if(confirm('Anda yakin menyimpan no. transaksi '+notran+' ?\nPeriksa kembali inputan anda\nkarena tidak bisa di edit untuk header ! '))
    //{
    post_response_text(tujuan, param, respon);	
    //}
    function respon()
    {
        if (con.readyState == 4) 
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) 
                {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else 
                {
                    document.getElementById('detailEntry').style.display='block';
                }
            }   
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function saveBarang()
{
    nodok=document.getElementById('nodok').value;
    kodeBarang=document.getElementById('kodeBarang').value;
    jumlahBarang=document.getElementById('jumlahBarang').value;
    satuanBarang=document.getElementById('satuanBarang').value;
    keteranganBarang=document.getElementById('keteranganBarang').value;
    hargabarang=document.getElementById('hargabarang').value;
    param='nodok='+nodok+'&kodeBarang='+kodeBarang+'&jumlahBarang='+jumlahBarang+'&hargabarang='+hargabarang;
    param+='&satuanBarang='+satuanBarang+'&keteranganBarang='+keteranganBarang+'&mnMulai='+mnMulai;
    param+="&method=saveBarang";
    tujuan='pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //bersihdetail();
                    bersihFormBarang();
                    loadDetailBarang();
                    //document.getElementById('containListBarang').style.display='block';
                    //document.getElementById('contentDetail').innerHTML=con.responseText;
                    // Success Response
                    //alert(con.responseText);
                    //document.getElementById('detailEntry').style.display='block';
                    //document.getElementById('detailIsi').innerHTML=con.responseText;
                    //document.getElementById('tmbLheader').innerHTML='';
                    //lockForm();		
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}


function bersihFormBarang()
{
    document.getElementById('kodeBarang').value='';
    document.getElementById('jumlahBarang').value='';
    document.getElementById('satuanBarang').value='';
    document.getElementById('keteranganBarang').value='';
    document.getElementById('namaBarang').value='';
}

function deleteBarang(nodok,kodeBarang)
{
    param='method=deleteBarang'+'&nodok='+nodok+'&kodeBarang='+kodeBarang;
    //alert(param);
    tujuan='pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);	
    //}
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
                                            loadDetailBarang();
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              }	
    }
	
}


function loadDetailBarang(firstload)
{
    if(typeof firstload=='undefined')
    {
        firstload=false;
    }
    nodok=document.getElementById('nodok').value;
    param='nodok='+nodok;
    param+='&method=loadDetailBarang';
    //alert(param);
    tujuan='pabrik_slave_perbaikan.php';
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
                                          //alert(con.responseText);
                                          //return;
                                          //document.getElementById('contentDetail').innerHTML=con.responseText;
                                          document.getElementById('containListBarang').innerHTML=con.responseText;

                                          if(firstload)loadDetailPekerjaan(true);
                                  }
                          }
                          else {
                                  busy_off();
                                  error_catch(con.status);
                          }
        }	
     } 	
}

//pekerjaan
function savePekerjaan()
{
    nodok=document.getElementById('nodok').value;
    nomor=document.getElementById('nomor').value;
    rincian=document.getElementById('rincian').value;
    kondisi=document.getElementById('kondisi').value;
    param='nodok='+nodok+'&nomor='+nomor+'&rincian='+rincian+'&kondisi='+kondisi;
    param+="&method=savePekerjaan";
    tujuan='pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    loadDetailPekerjaan();
                    bersihFormPekerjaan();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function bersihFormPekerjaan()
{
    document.getElementById('nomor').value='';
    document.getElementById('jumlahBarang').value='';
    document.getElementById('rincian').value='';
    document.getElementById('kondisi').value='';
}

function deletePekerjaan(nodok,nomor)
{
    param='method=deletePekerjaan'+'&nodok='+nodok+'&nomor='+nomor;
    //alert(param);
    tujuan='pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);	
    //}
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
                    loadDetailPekerjaan();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}



function deleteHead(nodok)
{
    param='method=deleteHead'+'&nodok='+nodok;
    tujuan='pabrik_slave_perbaikan.php';
    if(confirm(' Anda yakin ingin menghapus '+nodok+' ?? '))
    {
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


function loadDetailPekerjaan(firstload)
{
    if(typeof firstload=='undefined')
    {
        firstload=false;
    }
    nodok=document.getElementById('nodok').value;
    param='nodok='+nodok;
    param+='&method=loadDetailPekerjaan';
    tujuan='pabrik_slave_perbaikan.php';
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
                    
                    document.getElementById('containListPekerjaan').innerHTML=con.responseText;
                    if(firstload)loadDetailKaryawan(true);
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
        }	
     } 	
}


//karyawan
function saveKaryawan()
{
    nodok=document.getElementById('nodok').value;
    karyawan=document.getElementById('karyawan').value;
    param='nodok='+nodok+'&karyawan='+karyawan;
    param+="&method=saveKaryawan";
    tujuan='pabrik_slave_perbaikan.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                   bersihFormKaryawan();
                    loadDetailKaryawan();
                    
                  
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

	
function bersihFormKaryawan()
{
    document.getElementById('karyawan').value='';
}


function deleteKaryawan(nodok,karyawan)
{
    param='method=deleteKaryawan'+'&nodok='+nodok+'&karyawan='+karyawan;
    //alert(param);
    tujuan='pabrik_slave_perbaikan.php';
    //if(confirm(' Anda yakin ingin menghapus karyawan ini dari daftar lembur?? '))
    //{
    post_response_text(tujuan, param, respog);	
    //}
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
                    loadDetailKaryawan();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
	
}

function loadDetailKaryawan()
{
    nodok=document.getElementById('nodok').value;
    param='nodok='+nodok;
    param+='&method=loadDetailKaryawan';
    tujuan='pabrik_slave_perbaikan.php';
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
                    
                    document.getElementById('containListKaryawan').innerHTML=con.responseText;
                    
                    }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
        }	
     } 	
}











//JS untuk delete detailnya

function update()
{
	
	
}


function fillField(nodok,tglOrder,jmOrder,mnOrder,namaPemohon,statusPemohon,pabrik,station,mesin,shift,tipePerbaikan,
                    uraianKerusakan,tglMulai,jmMulai,mnMulai,tglSelesai,jmSelesai,mnSelesai,jumlahJamPerbaikan,
                    statusKetuntasan,hasilKerja,namaMesin,komMain,komPros)
{
	var re = /<br *\/?>/gi;
    document.getElementById('listData').style.display='none';
    document.getElementById('headher').style.display='block';
    document.getElementById('detailEntry').style.display='block';
    document.getElementById('nodok').value=nodok;
    document.getElementById('tglOrder').value=tglOrder;
    document.getElementById('jmOrder').value=jmOrder;
    document.getElementById('mnOrder').value=mnOrder;
    document.getElementById('namaPemohon').value=namaPemohon;
    document.getElementById('statusPemohon').value=statusPemohon;
    document.getElementById('pabrik').value=pabrik;
    document.getElementById('station').value=station;
    //document.getElementById('mesin').value=mesin;
    document.getElementById('shift').value=shift;
    document.getElementById('tipePerbaikan').value=tipePerbaikan;
    document.getElementById('uraianKerusakan').value=uraianKerusakan.replace(re, '\n');
    document.getElementById('tglMulai').value=tglMulai;
    document.getElementById('jmMulai').value=jmMulai;
    document.getElementById('mnMulai').value=mnMulai;
    document.getElementById('tglSelesai').value=tglSelesai;
    document.getElementById('jmSelesai').value=jmSelesai;
    document.getElementById('mnSelesai').value=mnSelesai;
    document.getElementById('jumlahJamPerbaikan').value=jumlahJamPerbaikan;
    document.getElementById('statusKetuntasan').value=statusKetuntasan;
    document.getElementById('hasilKerja').value=hasilKerja.replace(re, '\n');
    document.getElementById('komMain').value=komMain.replace(re, '\n');
    document.getElementById('komPros').value=komPros.replace(re, '\n');
    document.getElementById('mesin').innerHTML="<option value='"+ mesin +"'>"+ namaMesin +"</option>";
    document.getElementById('station').disabled=true;
    document.getElementById('mesin').disabled=true;
    document.getElementById('tglOrder').disabled=true;
    document.getElementById('jmOrder').disabled=true;
    document.getElementById('mnOrder').disabled=true;
    document.getElementById('method').value='update';
    loadDetailBarang(true);
    //loadDetailPekerjaan();
    //loadDetailKaryawan();
	//document.getElementById('detailForm').style.display='block';
	//loadDataDetail();
}


function cancelHead()
{
    document.getElementById('nodok').value='';
    document.getElementById('tglOrder').value='';
    document.getElementById('jmOrder').value='00';
    document.getElementById('mnOrder').value='00';
    document.getElementById('namaPemohon').value='';
    document.getElementById('statusPemohon').value='P';
    document.getElementById('station').value='';
    document.getElementById('mesin').value='';
    document.getElementById('shift').value='1';
    document.getElementById('tipePerbaikan').value='prev';
    document.getElementById('uraianKerusakan').value='';
    document.getElementById('tglMulai').value='';
    document.getElementById('jmMulai').value='00';
    document.getElementById('mnMulai').value='00';
    document.getElementById('tglSelesai').value='';
    document.getElementById('jmSelesai').value='00';
    document.getElementById('mnSelesai').value='00';
    document.getElementById('jumlahJamPerbaikan').value='';
    document.getElementById('statusKetuntasan').value='';
    document.getElementById('hasilKerja').value='';
    document.getElementById('komMain').value='';
    document.getElementById('komPros').value='';
    document.getElementById('station').disabled=false;
    document.getElementById('mesin').disabled=false;
    document.getElementById('tglOrder').disabled=false;
    document.getElementById('jmOrder').disabled=false;
    document.getElementById('mnOrder').disabled=false;
	document.getElementById('containListBarang').innerHTML='';
    document.getElementById('nomor').value='';
    document.getElementById('rincian').value='';
	document.getElementById('containListPekerjaan').innerHTML='';
	document.getElementById('containListKaryawan').innerHTML='';
    document.getElementById('detailEntry').style.display='none';
    
}




function getStation()
{
    pabrik=document.getElementById('pabrik').value; 
    param='pabrik='+pabrik;
    param+='&proses=getStation';
	//tujuan='pabrik_slave_2perbaikan.php';
	tujuan='master_getdata.php';

    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('station').innerHTML=con.responseText;
					if(pabrik==''){
						document.getElementById('mesin').innerHTML=con.responseText;
					}
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function getStationv()
{
    pabrik=document.getElementById('pabrikv').value; 
    param='pabrik='+pabrik;
    param+='&proses=getStation';
    //tujuan='pabrik_slave_2perbaikan_v2.php';
    tujuan='master_getdata.php';
 
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('stationv').innerHTML=con.responseText;
					if(pabrik==''){
						document.getElementById('mesinv').innerHTML=con.responseText;
					}
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

function detailBarang(nodok,ev)
{
    content= "<div id=formBarang style=\"height:200px;width:500px;overflow:scroll;\"></div>";
    title='No. Job Order : '+nodok;
    height='200';
    width='500';
    showDialog1(title,content,width,height,ev);	
    getListBarangLaporan(nodok);
}


function getListBarangLaporan(nodok)
{
    param='proses=getListBarangLaporan'+'&nodok='+nodok;
    //alert(param);
    tujuan = 'pabrik_slave_2perbaikan.php';
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

function batalLaporan()
	{
		document.getElementById('pabrik').value='';
                document.getElementById('station').value='';
		document.getElementById('tgl2').value='';	
		document.getElementById('tgl1').value='';
		document.getElementById('printContainer').innerHTML='';	
	}

function getMachine(){
    station=document.getElementById('station').value; 
    param='station='+station;
    param+='&proses=getMesin';
    tujuan='master_getdata.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('mesin').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getMachinev(){
    station=document.getElementById('stationv').value; 
    param='station='+station;
    param+='&proses=getMesin';
    tujuan='master_getdata.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('mesinv').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getJam(num){
    tglMulai=document.getElementById('tglMulai').value;
    jmMulai=document.getElementById('jmMulai').value;
    mnMulai=document.getElementById('mnMulai').value;
    tglSelesai=document.getElementById('tglSelesai').value;
    jmSelesai=document.getElementById('jmSelesai').value;
    mnSelesai=document.getElementById('mnSelesai').value;
    param='proses=getJam'+'&num='+num;
    param+='&tglMulai='+tglMulai+'&jmMulai='+jmMulai+'&mnMulai='+mnMulai;
    param+='&tglSelesai='+tglSelesai+'&jmSelesai='+jmSelesai+'&mnSelesai='+mnSelesai;
	tujuan='master_getdata.php';
	//alert(param);
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    //document.getElementById('jumlahJamPerbaikan').innerHTML=con.responseText;
                    document.getElementById('jumlahJamPerbaikan').value=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getPrev(){
	tipePerbaikan=document.getElementById('tipePerbaikan').value;
	nomor=document.getElementById('nomor').value;
	if (nomor < 0 || nomor > 20){
		return false;
	}
	//alert(tipePerbaikan);
	if(tipePerbaikan=='prev'){
		station=document.getElementById('station').value;
		mesin=document.getElementById('mesin').value;
	    param='proses=getPrev'+'&station='+station+'&mesin='+mesin+'&nomor='+nomor+'&tipePerbaikan='+tipePerbaikan;
		tujuan='master_getdata.php';
		//alert(param);
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						//document.getElementById('rincian').innerHTML=con.responseText;
						document.getElementById('rincian').value=con.responseText;
						if(document.getElementById('rincian').value!=''){
							document.getElementById('rincian').focus();
						}
					}
	            }else{
		            busy_off();
			        error_catch(con.status);
				}
	        }
		}
	}  	
}
