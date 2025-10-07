/**
 * @author repindra.ginting
 */

function displayFormInput()
{
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
	cancelAsset();
}

function displayList()
{
	tabAction(document.getElementById('tabFRM1'),1,'FRM',0);
}

function getSub(sub)
{
    
    tipe=document.getElementById('tipe').value;
    param='method=getSub'+'&tipe='+tipe+'&sub='+sub
    
    //alert(param);
    tujuan='sdm_slave_save_daftarAsset2.php';
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
                        
                        
                        if(tipe=='BG')//bangunan
                        {
                            document.getElementById('kodebarang').value='';
                            document.getElementById('kodebarang').style.display='none';
                        }
                        else
                        {
                            document.getElementById('kodebarang').style.display='';
                        }
                        
                        
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



function cariNoGudang(title,ev)
{
                
    content= "<div>";
    content+="<fieldset>";
      content+="Document : <select id=tipedoc><option value='po'>PO</option>";
    content+="<option value='spk'>SPK</option></select>";
    content+="No PO:<input type=text id=noGudang class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariGudang()>Go</button> </fieldset>";
    content+="<div id=containercari style=\"height:250px;width:470px;overflow:scroll;\"></div></div>";
    title=title+' PO:';
     width='500';
     height='300';
     showDialog1(title,content,width,height,ev);	
}

function goCariGudang()
{
    tipedoc=document.getElementById('tipedoc').options[document.getElementById('tipedoc').selectedIndex].value;
    
    
    
       // tipe=document.getElementById('tipe').value;
        noGudang=trim(document.getElementById('noGudang').value);
        //tipedocalert(tipedoc);
        if(noGudang.length<4)
           alert('Text too short');
        else
        {   
           
            param='method=goCariGudang'+'&noGudang='+noGudang+'&tipedoc='+tipedoc;
           // alert(param);
        tujuan = 'sdm_slave_save_daftarAsset2.php';
        post_response_text(tujuan, param, respog);			
        }
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                        document.getElementById('containercari').innerHTML=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
}


function goPickGudang(noGudang)
{
    document.getElementById('nodokpengadaan').value=noGudang;
    closeDialog();
}



//////////////////////// cari nomor induk
function cariNoInduk(title,ev)
{
                
    content= "<div>";
    content+="<fieldset>";
  
    content+="No :<input type=text id=noInduk class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=goCariInduk()>Go</button>"
    content+="</fieldset>";
    content+="<div id=containercari style=\"height:250px;width:470px;overflow:scroll;\"></div></div>";
    title=title+' Induk Number:';
    width='500';
    height='300';
    showDialog1(title,content,width,height,ev);	
}

function goCariInduk()
{
    
        noInduk=trim(document.getElementById('noInduk').value);
        if(noInduk.length<4)
           alert('Text too short');
        else
        {   
            param='method=goCariInduk'+'&noInduk='+noInduk;
        tujuan = 'sdm_slave_save_daftarAsset2.php';
        post_response_text(tujuan, param, respog);			
        }
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                        document.getElementById('containercari').innerHTML=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
}


function goPickInduk(noInduk)
{
    document.getElementById('induk').value=noInduk;
    closeDialog();
}


function cek()
{
    kdAset=document.getElementById('tipe').value;
    sub=document.getElementById('sub').value;
    param='method=getKodeAkhir'+'&kdAset='+kdAset+'&sub='+sub;
    //alert(param);
    tujuan = 'sdm_slave_save_daftarAsset2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
                    if (con.readyState == 4) {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alert('ERROR TRANSACTION,\n' + con.responseText);
                                    }
                                    else {
                                        
                                        
                                        isi=con.responseText.split("#####");
                                        document.getElementById('kodeaset').value=isi[0];
                                        document.getElementById('jumlahbulan').value="";
                                        document.getElementById('persendecline').value="";
                                        if(isi[1]=='double'){
                                            document.getElementById('jumlahbulan').disabled=false;
                                            document.getElementById('persendecline').disabled=false;
                                        }else{
                                            document.getElementById('jumlahbulan').disabled=false;
                                            document.getElementById('persendecline').disabled=true;
                                        }
                                        
                                       getSusut();
                                        
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
                    }
            }
	
}


function getSusut()
{
    tipe=document.getElementById('tipe').value;
    sub=document.getElementById('sub').value;
    param='method=getSusut'+'&tipe='+tipe+'&sub='+sub;
    //alert(param);
    tujuan = 'sdm_slave_save_daftarAsset2.php';
    post_response_text(tujuan, param, respog);
    function respog(){
                    if (con.readyState == 4) {
                            if (con.status == 200) {
                                    busy_off();
                                    if (!isSaveResponse(con.responseText)) {
                                            alert('ERROR TRANSACTION,\n' + con.responseText);
                                    }
                                    else {
                                        document.getElementById('jumlahbulan').value=con.responseText;
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
                    }
            }
}




function showWindowBarang(title,ev)
{
	  
	  content= "<div style='width:100%;'>";
	  content+="<fieldset>"+title+"<input type=text id=txtnamabarang class=myinputtext size=25 onkeypress=\"return enterEuy(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> </fieldset>";
	  content+="<div id=containercari style='overflow:scroll;height:300px;width:520px'></div></div>";
     //display window
	   width='550';
	   height='350';
	   showDialog1(title,content,width,height,ev);		
}
function enterEuy(evt)
{
	key=getKey(evt);
	if(key==13)
	{
		goCariBarang();
	}
	else
	{
		return tanpa_kutip(evt);
	}
	
}

function goCariBarang()
{
		txtcari = trim(document.getElementById('txtnamabarang').value);

				if (txtcari.length < 3) {
					alert('material name min. 3 char');
				}
				else {
					param = 'txtcari=' + txtcari;
					tujuan = 'log_slave_cariBarangUmum.php';
					post_response_text(tujuan, param, respog);
				}
	function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						document.getElementById('containercari').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}		
}


function throwThisRow(kode,nama,satuan)
{
  document.getElementById('kodebarang').value=kode;
  document.getElementById('namaaset').value=nama;
  closeDialog();
}

function simpanAssetBaru()
{
	kodeorg			=trim(document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value);
	tipe			=trim(document.getElementById('tipe').options[document.getElementById('tipe').selectedIndex].value);
	kodeasset		=trim(document.getElementById('kodeaset').value);
	kodebarang		=trim(document.getElementById('kodebarang').value);
	namaaset		=trim(document.getElementById('namaaset').value);
	tahunperolehan	=trim(document.getElementById('tahunperolehan').value);
	statu			=trim(document.getElementById('status').options[document.getElementById('status').selectedIndex].value);
	nilaiperolehan	=trim(document.getElementById('nilaiperolehan').value);
	jumlahbulan		=trim(document.getElementById('jumlahbulan').value);
	bulanawal		=trim(document.getElementById('bulanawal').options[document.getElementById('bulanawal').selectedIndex].value);
	keterangan		=trim(document.getElementById('keterangan').value);
	penambah		=trim(document.getElementById('penambah').value);
	pengurang		=trim(document.getElementById('pengurang').value);
	leasing			=trim(document.getElementById('leasing').options[document.getElementById('leasing').selectedIndex].value);
        psisasset	        =trim(document.getElementById('posisiasset').options[document.getElementById('posisiasset').selectedIndex].value);
	refbayar		=trim(document.getElementById('refbayar').value);
	nodokpengadaan	=trim(document.getElementById('nodokpengadaan').value);
	persendecline	=trim(document.getElementById('persendecline').value);
        sub=trim(document.getElementById('sub').value);
        induk=trim(document.getElementById('induk').value);
        
	met				=document.getElementById('method').value;
	if(kodeorg=='' || tipe=='' || kodeasset==''  || namaaset=='' || tahunperolehan=='' || statu=='')
	{
		alert('Data inconsistent');
	}
	else
	{
		param='kodeorg='+kodeorg+'&tipe='+tipe+'&kodeasset='+kodeasset;
		param+='&kodebarang='+kodebarang+'&namaaset='+namaaset+'&tahunperolehan='+tahunperolehan+'&status='+statu;
		param+='&nilaiperolehan='+nilaiperolehan+'&jumlahbulan='+jumlahbulan+'&bulanawal='+bulanawal;
		param+='&keterangan='+keterangan+'&method='+met+'&leasing='+leasing;
		param+='&penambah='+penambah+'&pengurang='+pengurang+'&posisiasset='+psisasset;
		param+='&refbayar='+refbayar+'&nodokpengadaan='+nodokpengadaan+'&persendecline='+persendecline;
                param+='&sub='+sub+'&induk='+induk;
                
              //  alert(param);
                
		tujuan='sdm_slave_save_daftarAsset.php';
        if(confirm('Are you sure..?'))
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
							document.getElementById('containeraset').innerHTML=con.responseText;
						    alert('Saved');
							cancelAsset();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
		
}

function cariAsset(page)
{
     tex=trim(document.getElementById('txtsearch').value);
	 param='';
	 if (tex == '') 
	 	param = '&page=' + page;
	 else {
	    param+="&txtcari="+tex;
	 }  
		tujuan = 'sdm_slave_save_daftarAsset.php';
		post_response_text(tujuan, param, respog);			

		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						document.getElementById('containeraset').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}		 
}

function editAsset(kodeorg,tipeasset,kodeasset,namasset,kodebarang,tahunperolehan,stat,hargaperolehan,jlhblnpenyusutan,awalpenyusutan,keterangan,leasing,pena,peng,refbayar,nodok,persen,pssasset,induk,sub){
	
	a=document.getElementById('kodeorg');
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==kodeorg)
		{
			a.options[x].selected=true;
		}
	}
	a=document.getElementById('tipe');
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==tipeasset)
		{
			a.options[x].selected=true;
		}
	}
	a=document.getElementById('status');
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==stat)
		{
			a.options[x].selected=true;
		}
	}
	a=document.getElementById('leasing');
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==leasing)
		{
			a.options[x].selected=true;
		}
	}
	a=document.getElementById('bulanawal');
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==awalpenyusutan)
		{
			a.options[x].selected=true;
		}
	}
        
        
  
	
	document.getElementById('kodeaset').value=kodeasset;
	document.getElementById('kodeaset').disabled=true;	
	document.getElementById('kodeorg').disabled=true;
	document.getElementById('namaaset').value=namasset;
	document.getElementById('kodebarang').value=kodebarang;
	document.getElementById('tahunperolehan').value=tahunperolehan;
	document.getElementById('nilaiperolehan').value=hargaperolehan;
    document.getElementById('jumlahbulan').value=jlhblnpenyusutan;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('penambah').value=pena;
    document.getElementById('pengurang').value=peng;
	document.getElementById('method').value='update';
	document.getElementById('refbayar').value=refbayar;
	document.getElementById('nodokpengadaan').value=nodok;
	document.getElementById('persendecline').value=persen;
        document.getElementById('induk').value=induk;
          
        document.getElementById('sub').value=sub;
        a=document.getElementById('posisiasset');
        
        
        document.getElementById('sub').disabled=true;
        document.getElementById('tipe').disabled=true;
        
	for(x=0;x<a.length;x++)
	{
		if(a.options[x].value==pssasset)
		{
			a.options[x].selected=true;
		}
	}
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
        getSub(sub);
        
        
}
function delAsset(kodeorg,kodeasset)
{
	    param="&kodeorg="+kodeorg+'&kodeasset='+kodeasset+'&method=delete';//deleting row
		tujuan = 'sdm_slave_save_daftarAsset.php';
		if(confirm('Deleting '+kodeasset+', Are you sure..?'))
		   post_response_text(tujuan, param, respog);			

		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
						document.getElementById('containeraset').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}	
}


function cancelAsset()
{
        //document.getElementById('kodeaset').disabled=false;
	document.getElementById('kodeorg').disabled=false;
        document.getElementById('tipe').disabled=false;
        document.getElementById('sub').disabled=false;
	document.getElementById('kodeorg').options[0].selected=true;
	document.getElementById('tipe').options[0].selected=true;
	document.getElementById('kodeaset').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('namaaset').value='';
	document.getElementById('tahunperolehan').value='';
	document.getElementById('status').options[0].selected=true;
	document.getElementById('nilaiperolehan').value='0';
	document.getElementById('jumlahbulan').value='0';
	document.getElementById('penambah').value='0';
	document.getElementById('pengurang').value='0';
	document.getElementById('bulanawal').options[0].selected=true;
	document.getElementById('keterangan').value='';
	document.getElementById('method').value='insert';
	document.getElementById('refbayar').value='';
	document.getElementById('nodokpengadaan').value='';
	document.getElementById('persendecline').value='0';
        document.getElementById('induk').value='';
        document.getElementById('sub').value='';
        
        
        
}

/**
 * cekJenisDecline
 * Cek Bulan dan Persen Penyusutan, untuk single atau double decline
 */
function cekJenisDecline()
{
	var bulan = document.getElementById('jumlahbulan'),
		persen = document.getElementById('persendecline');
	
	if(bulan.value > 0) {
		persen.disabled = true;
		persen.value = 0;
	} else {
		persen.disabled = false;
	}
	
	if(persen.value > 0) {
		bulan.disabled = true;
		bulan.value = 0;
	} else {
		bulan.disabled = false;
	}
}