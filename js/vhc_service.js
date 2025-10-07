function saveBarang()
{
    trans_no=document.getElementById('trans_no').value;
    kodeBarang=document.getElementById('kodeBarang').value;
    jumlahBarang=document.getElementById('jumlahBarang').value;
    satuanBarang=document.getElementById('satuanBarang').value;
    keteranganBarang=document.getElementById('keteranganBarang').value;
    param='trans_no='+trans_no+'&kodeBarang='+kodeBarang+'&jumlahBarang='+jumlahBarang;
    param+='&satuanBarang='+satuanBarang+'&keteranganBarang='+keteranganBarang;
    param+="&proses=saveBarang";
    tujuan='vhc_slave_service.php';
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

function deleteBarang(trans_no,kodeBarang)
{
    param='proses=deleteBarang'+'&trans_no='+trans_no+'&kodeBarang='+kodeBarang;
    //alert(param);
    tujuan='vhc_slave_service.php';
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
    trans_no=document.getElementById('trans_no').value;
    param='trans_no='+trans_no;
    param+='&proses=loadDetailBarang';
    //alert(param);
    tujuan='vhc_slave_service.php';
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
                                          document.getElementById('containListBarang').innerHTML=con.responseText;
                                           loadDetailKaryawan();
                                        //  if(firstload)loadDetailPekerjaan(true);
                                  }
                          }
                          else {
                                  busy_off();
                                  error_catch(con.status);
                          }
        }	
     } 	
}




function saveHeader()//save header + buka input detail di sini
{
    document.getElementById('containListBarang').innerHTML='';
    document.getElementById('containListKaryawan').innerHTML='';
    codeOrg=document.getElementById('codeOrg').value;
    trans_no=document.getElementById('trans_no').value;
    vhc_code=document.getElementById('vhc_code').value;
    kdTraksi=document.getElementById('kdTraksi').value;
    tgl_ganti=document.getElementById('tgl_ganti').value;
    tgl_keluar=document.getElementById('tgl_keluar').value;
    dwnTime=document.getElementById('dwnTime').value;
    kmmasuk=document.getElementById('kmmasuk').value;
    kmkeluar=document.getElementById('kmkeluar').value;
    descDmg=document.getElementById('descDmg').value;
    terlambat=document.getElementById('terlambat').value;
    nodok=document.getElementById('nodok').value;
    proses=document.getElementById('proses').value;
 

    if(codeOrg=='' || trans_no=='' || vhc_code=='' || kdTraksi=='')
    {
        alert('please compleate the form');return;
    }

    param='codeOrg='+codeOrg+'&trans_no='+trans_no+'&vhc_code='+vhc_code+'&kdTraksi='+kdTraksi;
    param+='&tgl_ganti='+tgl_ganti+'&tgl_keluar='+tgl_keluar+'&dwnTime='+dwnTime+'&nodok='+nodok;
    param+='&kmmasuk='+kmmasuk+'&kmkeluar='+kmkeluar+'&descDmg='+descDmg+'&terlambat='+terlambat;
    param+='&proses='+proses;

    tujuan='vhc_slave_service.php';
    post_response_text(tujuan, param, respon);	
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





function getNotrans(notran,kdJenis)
{
        if((notran!=0)&&(kdJenis!=0))
        {
                kdOrg=document.getElementById('codeOrg').value;
                kdjenis=kdJenis;
                notrans=notran;
                param='proses=generate_no'+'&codeOrg='+kdOrg+'&kdjenis='+kdjenis+'&notrans='+notrans;
        }
        else
        {
                kdOrg=document.getElementById('codeOrg').value;
                param='proses=generate_no'+'&codeOrg='+kdOrg;
        }
        tujuan='vhc_slave_service.php';
        post_response_text(tujuan, param, respon);
        function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                                                //ar=con.responseText.split("###");
                                                document.getElementById('trans_no').value = con.responseText;
                                                //document.getElementById('vhc_code').innerHTML=ar[0];
                     }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}




function getKdVhc(kdtrak,kdvhc){
if((kdvhc==0)||(kdtrak==0)){
	kdtraks=document.getElementById('kdTraksi');
	kdtraks=kdtraks.options[kdtraks.selectedIndex].value;
	param='kdTraksi='+kdtraks;
}else{
	param='kdTraksi='+kdtrak+'&kdVhc='+kdvhc;
}
param+='&proses=getVhc';
tujuan='vhc_slave_service.php';
 post_response_text(tujuan, param, respon);
function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                                        
                        document.getElementById('vhc_code').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



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
    
    param='proses=getListBarang';
    tujuan = 'vhc_slave_service.php';
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
    param='proses=getListBarang'+'&namaBarangCari='+namaBarangCari;
  
    tujuan = 'vhc_slave_service.php';
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



function moveDataBarang(kodebarang,namabarang,satuanbarang)
{
    document.getElementById('kodeBarang').value=kodebarang;
    document.getElementById('namaBarang').value=namabarang;
    document.getElementById('satuanBarang').value=satuanbarang;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
	
}





function cariBast(num)
{
    param='proses=loadData';
    param+='&page='+num;
    tujuan = 'vhc_slave_service.php';
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
    schTran=trim(document.getElementById('schTran').value);
    schTgl=trim(document.getElementById('schTgl').value);
    schRef=trim(document.getElementById('schRef').value);
    param='schTran='+schTran+'&schTgl='+schTgl+'&schRef='+schRef;
    param+='&proses=loadData';//loadSch
    //alert(param);
    tujuan = 'vhc_slave_service.php';
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
    param='proses=loadData';
    tujuan = 'vhc_slave_service.php';
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



function add_new_data()
{
	//alert('MASUK COI');
	//alert(con.responseText);
    document.getElementById('containListBarang').innerHTML='';
    document.getElementById('containListKaryawan').innerHTML='';
	document.getElementById('headher').style.display='block';
	document.getElementById('listData').style.display='none';
	document.getElementById('detailEntry').style.display='none';
        cancelHead();
	//document.getElementById('contentDetail').innerHTML='';
	//bukaform();
}


function displayList()
{
	document.getElementById('listData').style.display='block';
	document.getElementById('headher').style.display='none';
	document.getElementById('detailEntry').style.display='none';
	document.getElementById('schTgl').value='';
        document.getElementById('schTran').value='';
        document.getElementById('schRef').value='';
	loadData();
}








function deleteHead(trans_no)
{
    param='proses=delete'+'&trans_no='+trans_no;
    tujuan='vhc_slave_service.php';
    if(confirm(' Anda yakin ingin menghapus '+trans_no+' ?? '))
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



//karyawan
function saveKaryawan()
{
    trans_no=document.getElementById('trans_no').value;
    karyawan=document.getElementById('karyawan').value;
    param='trans_no='+trans_no+'&karyawan='+karyawan;
    param+="&proses=saveKaryawan";
   
    tujuan='vhc_slave_service.php';
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


function deleteKaryawan(trans_no,karyawan)
{
    param='proses=deleteKaryawan'+'&trans_no='+trans_no+'&karyawan='+karyawan;
    tujuan='vhc_slave_service.php';
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
    trans_no=document.getElementById('trans_no').value;
    param='trans_no='+trans_no;
    param+='&proses=loadDetailKaryawan';
    tujuan='vhc_slave_service.php';
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


function fillField(codeOrg,trans_no,kdTraksi,vhc_code,nodok,tgl_ganti,tgl_keluar,dwnTime,kmmasuk,kmkeluar,descDmg,terlambat)
{
    document.getElementById('listData').style.display='none';
    document.getElementById('headher').style.display='block';
    document.getElementById('detailEntry').style.display='block';
    document.getElementById('codeOrg').value=codeOrg;
    document.getElementById('codeOrg').disabled=true;
    document.getElementById('trans_no').value=trans_no;
    document.getElementById('trans_no').disabled=true;
    document.getElementById('kdTraksi').value=kdTraksi;
    document.getElementById('kdTraksi').disabled=true;
    document.getElementById('vhc_code').value=vhc_code;
    document.getElementById('vhc_code').disabled=true;
    document.getElementById('nodok').value=nodok;
    document.getElementById('tgl_ganti').value=tgl_ganti;
    document.getElementById('tgl_keluar').value=tgl_keluar;
    document.getElementById('dwnTime').value=dwnTime;
    //document.getElementById('mesin').value=mesin;
    document.getElementById('kmmasuk').value=kmmasuk;
    document.getElementById('kmkeluar').value=kmkeluar;
    document.getElementById('descDmg').value=descDmg;
    document.getElementById('terlambat').value=terlambat;
    //document.getElementById('mesin').innerHTML="<option value='"+ mesin +"'>"+ namaMesin +"</option>";
    document.getElementById('proses').value='update';
    loadDetailBarang(true);

}


function cancelHead()
{
    
    document.getElementById('codeOrg').disabled=false;
    //document.getElementById('trans_no').disabled=false;
    document.getElementById('kdTraksi').disabled=false;
    document.getElementById('vhc_code').disabled=false;
    document.getElementById('codeOrg').value='';
    document.getElementById('trans_no').value='';
    document.getElementById('kdTraksi').value='';
    document.getElementById('vhc_code').value='';
    document.getElementById('nodok').value='';
    document.getElementById('tgl_ganti').value='';
    document.getElementById('tgl_keluar').value='';
    document.getElementById('dwnTime').value='';
    //document.getElementById('mesin').value=mesin;
    document.getElementById('kmmasuk').value='0';
    document.getElementById('kmmasuk').disabled=true;
    document.getElementById('kmkeluar').value='';
    document.getElementById('descDmg').value='';
    document.getElementById('terlambat').value='';
    document.getElementById('detailEntry').style.display='none';
    document.getElementById('proses').value='insert';
    
}


//getKm

function getKm()
{
    vhc_code=document.getElementById('vhc_code').value; 
    param='vhc_code='+vhc_code;
    param+='&proses=getKm';
    tujuan='vhc_slave_service.php';
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
                   
                    ar=con.responseText.split("###");
                    
                    
                    if(ar[1]==1)
                    {
                        document.getElementById('kmmasuk').value=ar[0];   
                        document.getElementById('kmmasuk').disabled=true; 
						document.getElementById('kmkeluar').value=ar[0];   
                        document.getElementById('kmkeluar').disabled=false; 
                    }
                    else
                    {
                        document.getElementById('kmmasuk').disabled=false;
                        document.getElementById('kmmasuk').value=0;
						document.getElementById('kmkeluar').disabled=false;
                        document.getElementById('kmkeluar').value=0; 						
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