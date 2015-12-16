/**
 * @author repindra.ginting
 */

/////

function getSub(sub)
{
    
    aset=document.getElementById('aset').value;
    param='method=getSub'+'&aset='+aset+'&sub='+sub
    
    //alert(param);
    tujuan='vhc_slave_project.php';
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
                        document.getElementById('sub').innerHTML=con.responseText; 
                        
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                    
                }
      }	
     } 
}


///



function simpan()
{
    unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    aset=document.getElementById('aset').options[document.getElementById('aset').selectedIndex].value;
    jenis=document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].value;
	sub=document.getElementById('sub').options[document.getElementById('sub').selectedIndex].value;
    nama=trim(document.getElementById('nama').value);
//    kelompok=document.getElementById('kelompok').options[document.getElementById('kelompok').selectedIndex].value;
//    nilai=trim(document.getElementById('nilai').value);
    tanggalmulai=trim(document.getElementById('tanggalmulai').value);
    tanggalselesai=trim(document.getElementById('tanggalselesai').value);
    method=document.getElementById('method').value;	
    kode=document.getElementById('kode').value;
    
    if(unit=='')            { alert('Please fill UNIT'); exit(); }
    if(aset=='')            { alert('Please fill ASSET'); exit(); }
    if(nama=='')            { alert('Please fill NAMA'); exit(); }
	if(sub=='')            	{ alert('Please fill SUB ASSET'); exit(); }
    if(tanggalmulai=='')    { alert('Please fill TANGGAL MULAI'); exit(); }
    if(tanggalselesai=='')  { alert('Please fill TANGGAL SELESAI'); exit(); }
    
    param='unit='+unit+'&aset='+aset+'&jenis='+jenis;
    param+='&nama='+nama+'&tanggalmulai='+tanggalmulai+'&tanggalselesai='+tanggalselesai+'&kode='+kode+'&sub='+sub;
//    param+='&kelompok='+kelompok+'&nilai='+nilai;
    param+='&method='+method;
    if(confirm('Save/Simpan?'))
    {
        tujuan = 'vhc_slave_project.php';
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
                else {
                    //alert(con.responseText);
                    alert('Done.');
                    //document.getElementById('container').innerHTML=con.responseText;
                    loadData();
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

function batal()
{
    /*var d = new Date();
    var curr_date = d.getDate();
    
    
    var curr_month = d.getMonth() + 1; //Months are zero based
    var curr_year = d.getFullYear();
    d1=curr_date + "-" + curr_month + "-" + curr_year;*/
    
    
    var d = new Date();
    var curr_date = d.getDate();
    var curr_month = d.getMonth() + 1; //Months are zero based
    var curr_year = d.getFullYear();
    if(curr_date.length=1)
    {
            curr_date='0'+curr_date;
    }
    
    d1=curr_date + "-" + curr_month + "-" + curr_year;
    
    

    document.getElementById('unit').value='';
    document.getElementById('aset').value='';
    document.getElementById('jenis').value='AK';
    document.getElementById('nama').value='';
    document.getElementById('tanggalmulai').value=d1;
    document.getElementById('tanggalselesai').value=d1;
    document.getElementById('method').value='insert';
    document.getElementById('kode').value='';
    document.getElementById('sub').value='';
    
    document.getElementById('unit').disabled=false;
    document.getElementById('aset').disabled=false;
    document.getElementById('jenis').disabled=false;
    document.getElementById('sub').disabled=false;
}

function fillField(unit,aset,jenis,nama,tanggalmulai,tanggalselesai,method,kode,sub)
{
    document.getElementById('unit').value=unit;
    document.getElementById('aset').value=aset;
    document.getElementById('jenis').value=jenis;
    document.getElementById('nama').value=nama;
    document.getElementById('tanggalmulai').value=tanggalmulai;
    document.getElementById('tanggalselesai').value=tanggalselesai;
    document.getElementById('method').value=method;
    document.getElementById('kode').value=kode;
    
    document.getElementById('unit').disabled=true;
    document.getElementById('aset').disabled=true;
    document.getElementById('jenis').disabled=true;
    
    document.getElementById('sub').disabled=true;
    getSub(sub);
    

}
function detailForm(unit,aset,jenis,nama,tanggalmulai,tanggalselesai,method,kode,sub,namasub)
{
  
    document.getElementById('sub').innerHTML="<option value='"+ sub +"'>"+ namasub +"</option>"
    document.getElementById('unit').value=unit;
    document.getElementById('aset').value=aset;
    document.getElementById('jenis').value=jenis;
    document.getElementById('nama').value=nama;
    document.getElementById('tanggalmulai').value=tanggalmulai;
    document.getElementById('tanggalselesai').value=tanggalselesai;
    document.getElementById('method').value='insertDetail';
    document.getElementById('kode').value=kode;
    document.getElementById('kdProj').value=kode;
    document.getElementById('unit').disabled=true;
    document.getElementById('aset').disabled=true;
    document.getElementById('jenis').disabled=true;
    document.getElementById('tanggalselesai').disabled=true;
    document.getElementById('tanggalmulai').disabled=true;
    document.getElementById('nama').disabled=true;
    document.getElementById('sub').value=sub;
    document.getElementById('sub').disabled=true;
    param='method='+method+'&kode='+kode;
    tujuan='vhc_slave_project.php';
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
                   document.getElementById('detailInput').style.display='block';
                   document.getElementById('dataDisimpan').style.display='none';
                   document.getElementById('printDat').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}
function doneSlsi()
{
    //waktu=date('d-m-Y');
    document.getElementById('unit').value='';
    document.getElementById('aset').value='';
    document.getElementById('jenis').value='';
    document.getElementById('nama').value='';
    document.getElementById('method').value='insert';
    document.getElementById('kode').value='';
    document.getElementById('kdProj').value='';
    document.getElementById('unit').disabled=false;
    document.getElementById('aset').disabled=false;
    document.getElementById('jenis').disabled=false;
    document.getElementById('tanggalselesai').disabled=false;
    document.getElementById('tanggalmulai').disabled=false;
    document.getElementById('nama').disabled=false;
    document.getElementById('detailInput').style.display='none';
    document.getElementById('dataDisimpan').style.display='block';
    document.getElementById('printDat').innerHTML='';
    //document.getElementById('tanggalmulai').value=waktu;
    //document.getElementById('tanggalselesai').value=waktu;
}
function editDet(tanggalmulai,tanggalselesai,method,kode,knci,nmkeg,satKeg,volKeg,bobotKeg)
{
    document.getElementById('kdProj').value=kode;
    document.getElementById('namaKeg').value=nmkeg;
    document.getElementById('tanggalMulai').value=tanggalmulai;
    document.getElementById('tanggalSampai').value=tanggalselesai;
    document.getElementById('kegId').value=knci;
    //document.getElementById('satKeg').value=satKeg;
	setValue('satKeg',satKeg);
    document.getElementById('volKeg').value=volKeg;
    document.getElementById('bobotKeg').value=bobotKeg;
    document.getElementById('method').value=method;
}
function hapus(kode)
{
    document.getElementById('method').value='hapus';
    param='kode='+kode+'&method=delete';
    if(confirm('Delete/Hapus '+kode+'?'))
    {
        tujuan='vhc_slave_project.php';
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
                else {
                    //alert(con.responseText);
                    alert('Done.');
                    //document.getElementById('container').innerHTML=con.responseText;
                    loadData();
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
function loadData()
{
    param='method=loadData';
    tujuan='vhc_slave_project.php';
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
//excel timeframe
function timeFrame(ev,kode)
{
        param='method=timeFrame'+'&kode='+kode;
        //alert(param);
        tujuan='vhc_slave_project.php';
        judul='Time Frame '+kode;		
        printFile(param,tujuan,judul,ev)	
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}
function excelMaterial(ev,kode)
{
        param='method=excelMaterial'+'&kode='+kode;
        //alert(param);
        tujuan='vhc_slave_project.php';
        judul='Material '+kode;		
        printFile(param,tujuan,judul,ev)	
}
function postIni(kd,unit)
{
    param='method=postingData'+'&kode='+kd+'&unit='+unit;
    tujuan='vhc_slave_project.php';
    if(confirm("Anda Yakin Ingin Memposting Kode :"+kd))
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
                else {
                    //alert(con.responseText);
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


function batalKeg()
{
    document.getElementById('satKeg').selectedIndex=0;
    document.getElementById('volKeg').value='';
    document.getElementById('bobotKeg').value='';
}

function addDetail()
{
    kd=document.getElementById('kdProj').value;
    nmKeg=document.getElementById('namaKeg').value;
    tglMul=document.getElementById('tanggalMulai').value;
    tglSmp=document.getElementById('tanggalSampai').value;
    knci=document.getElementById('kegId').value;
    met=document.getElementById('method').value;
    satKeg=document.getElementById('satKeg').value;
    volKeg=document.getElementById('volKeg').value;
    bobotKeg=document.getElementById('bobotKeg').value;
    
    param='&kode='+kd+'&nmKeg='+nmKeg+'&tglMul='+tglMul+'&tglSmp='+tglSmp;
    param+='&index='+knci+'&method='+met;
    param+='&satKeg='+satKeg+'&volKeg='+volKeg+'&bobotKeg='+bobotKeg;
    tujuan='vhc_slave_project.php';
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
                   // document.getElementById('container').innerHTML=con.responseText;
                   batalKeg();
                   loadDetail();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function loadDetail()
{
    kd=document.getElementById('kdProj').value;
    param='method=detail'+'&kode='+kd;
    tujuan='vhc_slave_project.php';
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
                    document.getElementById('printDat').innerHTML=con.responseText;
                    document.getElementById('method').value='insertDetail';
                    document.getElementById('namaKeg').value='';
                    document.getElementById('tanggalMulai').value=date('d-m-Y');
                    document.getElementById('tanggalSampai').value=date('d-m-Y');
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function hapusData(kode)
{
    param='index='+kode+'&method=hpsDetail';
    if(confirm('Delete/Hapus Detail ?'))
    {
        tujuan='vhc_slave_project.php';
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
                else {
                    //alert(con.responseText);
                   loadDetail();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

////////////////////
//BUKA MATERIAL
////////////////////

function saveFormBarang(kegiatan,kodeproject)
{

	//alert('MASUK');
	kodeproject=document.getElementById('kodeproject').value;
	kodekegiatan=document.getElementById('kodekegiatan').value;
	kodeBarangForm=document.getElementById('kodeBarangForm').value;
	jumlahBarangForm=document.getElementById('jumlahBarangForm').value;
	method=document.getElementById('method').value;

	//param='kodeproject='+kodeproject+'&kodekegiatan='+kodekegiatan+'&kodeBarangForm='+kodeBarangForm+'&jumlahBarangForm='+jumlahBarangForm+'&method='+saveFormBarang;
	param='method=saveFormBarang'+'&kodeproject='+kodeproject+'&kodekegiatan='+kodekegiatan+'&kodeBarangForm='+kodeBarangForm+'&jumlahBarangForm='+jumlahBarangForm;
	
	tujuan = 'vhc_slave_project.php';
	
	//alert(tujuan);
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
							//alert(con.responseText
							cancelFormBarang(kegiatan,kodeproject);
							
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
	
}
 

 
function tambahBarang(kegiatan,kodeproject,title,ev)
{
                  content= "<div id=formBarang style=\"height:450px;width:800px;overflow:scroll;\"></div>";
				 
				   //content+="<div id=formCariBarang></div>";
                
                 title='Project : '+kodeproject;
			
                   width='800';
                   height='450';
                   showDialog1(title,content,width,height,ev);	
				   getListBarang(kegiatan,kodeproject);
}






function moveDataBarang(kodebarang,namabarang,satuanbarang)
{
	document.getElementById('kodeBarangForm').value=kodebarang;
	document.getElementById('namaBarangForm').value=namabarang;
	document.getElementById('satuanBarangForm').value=satuanbarang;
	
	//document.getElementById('').innerHTML=con.responseText;
	document.getElementById('listCariBarang').style.display='none';
	
}



function cariListBarang(kegiatan,kodeproject)
{
	//alert('MASUK');
	namaBarangCari=document.getElementById('namaBarangCari').value;
	//alert(kegiatan);
	param='method=getListBarang'+'&namaBarangCari='+namaBarangCari+'&kegiatan='+kegiatan+'&kodeproject='+kodeproject;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
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



function delMaterial(kodeproject,kegiatan,kodebarang)
{
	param='method=deleteMaterial'+'&kodeproject='+kodeproject+'&kegiatan='+kegiatan+'&kodebarang='+kodebarang;
	//alert(param);
	tujuan='vhc_slave_project.php';
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
						cancelFormBarang(kegiatan,kodeproject);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}


function cancelFormBarang(kegiatan,kodeproject)
{
	
	//document.getElementById('kodekegiatan').value=kodek
	//kodeproject
	
	
	document.getElementById('kodeBarangForm').value='';
	document.getElementById('namaBarangForm').value='';
	document.getElementById('jumlahBarangForm').value='';
	getListBarang(kegiatan,kodeproject);
	//document.getElementById('listCariBarang').style.display='none';
}


function getListBarang(kegiatan,kodeproject)
{
	param='method=getListBarang'+'&kegiatan='+kegiatan+'&kodeproject='+kodeproject;
	//alert(param);
	tujuan = 'vhc_slave_project.php';
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


////////////////////
//TUTUP MATERIAL
////////////////////