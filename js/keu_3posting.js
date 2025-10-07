/* listPosting
 * Fungsi untuk men-generate list dari transaksi yang dapat di posting
 */
function listPosting() {
    var listPost = document.getElementById('listPosting');
    var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode')+"&jenisdata="+getValue('jenisData');

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    listPost.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    x=getValue('jenisData');
    if(x=='gudang')
        post_response_text('keu_slave_3posting.php', param, respon);
    else if(x=='gaji')
        post_response_text('keu_slave_3gajikaryawan.php', param, respon);
    else if(x=='depresiasi') 
        post_response_text('keu_slave_3depresiasi.php', param, respon);
    else if(x=='alokasi') 
        post_response_text('keu_slave_3traksi.php', param, respon);   
    else if(x=='gajiharilibur') 
        post_response_text('keu_slave_3gajiharilibur.php', param, respon); 
    else if(x=='potongan')
        post_response_text('keu_slave_3pengakuanPotongan.php', param, respon);
    else if(x=='kurs')
        post_response_text('keu_slave_3selisihKurs.php?proses=list', param, respon);
    else if(x=='mutasi')
        post_response_text('keu_slave_3mutasiKary.php?proses=list', param, respon);
    else if(x=='fixtrans'){
        if(confirm("Ini akan memakan waktu, anda yakin?")){
          post_response_text('keu_slave_3updateTransaksi.php', param, respon);         
        }
    }     
}

function prosesGudang(row)
{
    document.getElementById('btnproses').disabled=true;
    tipetransaksi   =document.getElementById('tipetransaksi'+row).innerHTML;
    notransaksi     =document.getElementById('notransaksi'+row).innerHTML;
    kodebarang      =document.getElementById('kodebarang'+row).innerHTML;
    jumlah          =document.getElementById('jumlah'+row).innerHTML;
    satuan          =document.getElementById('satuan'+row).innerHTML;
    idsupplier      =document.getElementById('idsupplier'+row).innerHTML;
    gudangx         =document.getElementById('gudangx'+row).innerHTML;
    untukunit       =document.getElementById('untukunit'+row).innerHTML;
    kodeblok        =document.getElementById('kodeblok'+row).innerHTML;
    kodemesin       =document.getElementById('kodemesin'+row).innerHTML;
    kodekegiatan    =document.getElementById('kodekegiatan'+row).innerHTML;
    hartot          =document.getElementById('hartot'+row).innerHTML;
    nopo            =document.getElementById('nopo'+row).innerHTML;
    kodegudang      =document.getElementById('kodegudang'+row).innerHTML;
    tanggal         =document.getElementById('tanggal'+row).innerHTML;
    keterangan      =document.getElementById('keterangan'+row).innerHTML;

    param='tipetransaksi='+tipetransaksi+'&notransaksi='+notransaksi+
          '&kodebarang='+kodebarang+
          '&jumlah='+jumlah+'&satuan='+satuan+'&idsupplier='+idsupplier+
          '&gudangx='+gudangx+'&untukunit='+untukunit+'&kodeblok='+kodeblok+
          '&kodemesin='+kodemesin+'&kodekegiatan='+kodekegiatan+
          '&hartot='+hartot+'&nopo='+nopo+'&kodegudang='+kodegudang+'&tanggal='+tanggal+
          '&keterangan='+keterangan;
    tujuan='keu_slave_prosesGudangAkhirbulan.php';
    post_response_text(tujuan, param, respon);
    document.getElementById('row'+row).style.backgroundColor='orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+row).style.backgroundColor='red';
                } else {
                    document.getElementById('row'+row).style.display='none';
                    try{
                        x=row+1;
                        if(document.getElementById('row'+x))
                         {   
                             row=x;
                             prosesGudang(row);
                         }
                                                 else
                                                 {
                                                    alert('Done');
                                                 }
                    }
                    catch(e)
                    {
                        alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

function prosesGaji(row)
{
    document.getElementById('btnproses').disabled=true;
    namakaryawan   =document.getElementById('namakaryawan'+row).innerHTML;
    karyawanid     =document.getElementById('karyawanid'+row).innerHTML;
    komponen       =document.getElementById('komponen'+row).innerHTML;
    namakomponen   =document.getElementById('namakomponen'+row).innerHTML;
    subbagian      =document.getElementById('subbagian'+row).innerHTML;
    mesin          =document.getElementById('mesin'+row).innerHTML;
    jumlah         =document.getElementById('jumlah'+row).innerHTML;
    tipeorganisasi =document.getElementById('tipeorganisasi'+row).innerHTML;
    periode        =document.getElementById('periode'+row).innerHTML;

    param='namakaryawan='+namakaryawan+'&karyawanid='+karyawanid+
          '&komponen='+komponen+'&namakomponen='+namakomponen+
          '&subbagian='+subbagian+'&mesin='+mesin+'&jumlah='+jumlah+
          '&tipeorganisasi='+tipeorganisasi+'&periode='+periode+'&row='+row;    
    tujuan='keu_slave_prosesAlokasiGajiAkhirbulan.php';
 if(row==1 && confirm('Anda yakin melakukan proses pengalokasian gaji?'))
        post_response_text(tujuan, param, respon);
 else
        post_response_text(tujuan, param, respon);

    document.getElementById('row'+row).style.backgroundColor='orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+row).style.backgroundColor='red';
                } else {
                    document.getElementById('row'+row).style.display='none';
                    try{
                        x=row+1;
                        if(document.getElementById('row'+x))
                         {   
                             row=x;
                             prosesGaji(row);
                         }
                         else
                         {
                            alert('Done');
                         }
                    }
                    catch(e)
                    {
                        alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}

function prosesAlokasi(row)
{
    periode  =document.getElementById('periode'+row).innerHTML;
    param='periode='+periode;
    tujuan='vhc_slave_updateFlag.php';
     if(confirm('Anda yakin melakukan proses pengalokasian biaya Kendaraan?'))
     post_response_text(tujuan, param, respon);

        function respon() {
              if (con.readyState == 4) {
                  if (con.status == 200) {
                      busy_off();
                      if (!isSaveResponse(con.responseText)) {
                          alert(' Error:,\n' + con.responseText);
                      } else {
                          doProsesAlokasi(row);
                      }
                  } else {
                      busy_off();
                      error_catch(con.status);
                  }
              }
          }     
}

function doProsesAlokasi(row)
{
    document.getElementById('btnproses').disabled=true;
    periode  =document.getElementById('periode'+row).innerHTML;
    kodevhc  =document.getElementById('kodevhc'+row).innerHTML;
    jumlah   =document.getElementById('jumlah'+row).innerHTML;
    jenis    =document.getElementById('jenis'+row).innerHTML;

    param='periode='+periode+'&kodevhc='+kodevhc+'&jumlah='+jumlah+'&jenis='+jenis;   
    tujuan='keu_slave_prosesAlokasiTraksi.php';
 if(jumlah!='0')
   {   
            post_response_text(tujuan, param, respon);
   }
  else
   {//next
         row++;
         doProsesAlokasi(row);      
   }   
    document.getElementById('row'+row).style.backgroundColor='orange';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+row).style.backgroundColor='red';
                } else {
                    document.getElementById('row'+row).style.display='none';
                    try{
                        x=row+1;
                        if(document.getElementById('row'+x))
                         {   
                             row=x;
                             doProsesAlokasi(row);
                         }
                         else
                         {
                            alert('Done');//jangan buang ini
                         }
                    }
                    catch(e)
                    {
                        alert('Done');//jangan buang ini
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
}



function prosesPenyusutan(row)
{
    kodejurnal  =document.getElementById('kodejurnal'+row).innerHTML;
    periode     =document.getElementById('periode'+row).innerHTML;
    keterangan  =document.getElementById('keterangan'+row).innerHTML;
    jumlah      =document.getElementById('jumlah'+row).innerHTML;


    param='kodejurnal='+kodejurnal+'&periode='+periode+
          '&keterangan='+keterangan+'&jumlah='+jumlah+'&row='+row;    
    tujuan='keu_slave_prosesDepresiasiAkhirbulan.php';
    if(row==1 && confirm('Anda yakin melakukan proses penyusutan?')) {
        document.getElementById('btnproses').disabled=true;
        post_response_text(tujuan, param, respon);
        document.getElementById('row'+row).style.backgroundColor='orange';
    } else if (row>1) {
        document.getElementById('btnproses').disabled=true;
        post_response_text(tujuan, param, respon);
        document.getElementById('row'+row).style.backgroundColor='orange';
    }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+row).style.backgroundColor='red';
                } else {
                    document.getElementById('row'+row).style.display='none';
                    try{
                        x=row+1;
                        if(document.getElementById('row'+x))
                         {   
                             row=x;
                             prosesPenyusutan(row);
                         }
                         else
                         {
                            alert('Done');
                         }
                    }
                    catch(e)
                    {
                        alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }     
}


function prosesGajiLangsung(row)
{
    document.getElementById('btnproses').disabled=true;
    karyawanid     =document.getElementById('karyawanid').value;
    jumlah         =document.getElementById('jumlah').value;
    dari        =document.getElementById('dari').value;
    sampai        =document.getElementById('sampai').value;
    param='karyawanid='+karyawanid+'&jumlah='+jumlah+
          '&dari='+dari+'&sampai='+sampai+'&row='+row;     
    tujuan='keu_slave_prosesAlokasiGajiKetinggalan.php';
 if(confirm('Anda yakin melakukan proses pengalokasian gaji?'))
        post_response_text(tujuan, param, respon);


    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    try{
                        x=row+1;
                        //if(document.getElementById('row'+x))
                        // {   
                        //     row=x;
                        //     prosesGajiLangsung(row);
                        // }
                        // else
                        // {
                            alert('Done');
                         //}
                    }
                    catch(e)
                    {
                        alert('Done');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
}

function prosesPotongan(periode){
     param='periode='+periode+'&method=post';
    tujuan='keu_slave_3pengakuanPotongan.php';
     if(confirm('Anda yakin melakukan proses ini?'))
          post_response_text(tujuan, param, respon);

        function respon() {
              if (con.readyState == 4) {
                  if (con.status == 200) {
                      busy_off();
                      if (!isSaveResponse(con.responseText)) {
                          alert(' Error:,\n' + con.responseText);
                      } else {
                          alert('Done');
                      }
                  } else {
                      busy_off();
                      error_catch(con.status);
                  }
              }
          }  
}


function postKurs() {
	var param = "kodeorg="+getValue('kodeorgKurs')
		+"&periode="+getValue('periodeKurs'),
		tujuan = "keu_slave_3selisihKurs.php?proses=post";
	
	if(confirm('Anda yakin akan melakukan proses selisih kurs?'))
        post_response_text(tujuan, param, respon);
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert('Proses Selisih Kurs berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postMutasi() {
	var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode'),
		tujuan = "keu_slave_3mutasiKary.php?proses=post";
	
	if(confirm("Anda yakin akan melakukan proses update Mutasi / Promosi / "+
			   "Demosi Karyawan ?"))
        post_response_text(tujuan, param, respon);
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert('Proses Mutasi / Promosi / Demosi Karyawan berhasil');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}