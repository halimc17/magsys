// JavaScript Document
function addData(tGl,noTkt)
{
        if((tGl=='0')&&(noTkt=='0'))
        {
                tgl=document.getElementById('tgl').value;
                kdorg=document.getElementById('unitdt');
                kdorg=kdorg.options[kdorg.selectedIndex].value;
                param='proses=createTable'+'&tgl='+tgl;
                param+='&kdOrg='+kdorg;
        }
        else
        {
                noTiket=noTkt;
                tgl=tGl;
                param='proses=createTable'+'&noTiket='+noTkt+'&tgl='+tgl;
        }
        //alert(param);
        tujuan='pabrik_slave_sortasi2.php';
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
                                                        //document.getElementById('noTiket').innerHTML=con.responseText;
                                document.getElementById('tgl').disabled=true;
                                document.getElementById('tanggalForm').innerHTML=tgl;
                                document.getElementById('tmblPilih').innerHTML='<button class="mybutton" id="cancelAbn" onclick="cancelForm()" >'+canForm+'</button>';
                                //document.getElementById('formInput').style.display='block';
                                document.getElementById('listData').style.display='none';
                                document.getElementById('formDetail').innerHTML=con.responseText;
                                //document.getElementById('noTiket').disabled=true;
                                //document.getElementById('cancelAbn').disabled=false;
                                document.getElementById('showFormBwh').style.display="block";
                                if(a==0)
                                    {
                                        loadDataDetail()
                                    }
                                                       // getForm(noTkt);
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
        document.getElementById('headher').style.display='block';
        document.getElementById('listData').style.display='none';
        document.getElementById('formInput').style.display='none';
        document.getElementById('showFormBwh').style.display="none";
        document.getElementById('formDetail').innerHTML='';
        bersih();
}
function displayList()
{
        document.getElementById('headher').style.display='none';
        document.getElementById('listData').style.display='block';
        document.getElementById('noTiketcr').value='';
        //document.getElementById('noTiketcr').value='';
        loadData();
}
function bersih()
{
        document.getElementById('tgl').value='';
        document.getElementById('tgl').disabled=false;
        document.getElementById('tmblPilih').innerHTML="<button class=mybutton id=dtlAbn onclick=addData('0','0')>"+tmblPilih+"</button>";
        document.getElementById('proses').value='insert';
}
function cancelSave()
{
        bersih();
        displayList();
}
function loadData()
{
        param='proses=LoadData';
        tujuan='pabrik_slave_sortasi2.php';
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
function cariBast(num)
{
                param='proses=LoadData';
                param+='&page='+num;
                tujuan = 'pabrik_slave_sortasi2.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert('ERROR TRANSACTION,\n' + con.responseText);
                                        }
                                        else {
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
function saveData()
{
        noTiket=document.getElementById('noTiket').options[document.getElementById('noTiket').selectedIndex].value;
        kdFraksi=document.getElementById('kdFraksi').options[document.getElementById('kdFraksi').selectedIndex].value;
        jmlh=document.getElementById('jmlh').value;
        pros=document.getElementById('proses').value;
        param='noTiket='+noTiket+'&kdFraksi='+kdFraksi+'&jmlh='+jmlh+'&proses='+pros
        //alert(param);
        tujuan='pabrik_slave_sortasi2.php';
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
                                                        if(confirm("Next input ?"))
                                                        {
                                                                document.getElementById('noTiket').disabled=false;
                                                                document.getElementById('kdFraksi').disabled=false
                                                                //document.getElementById('noTiket').value='';
                                                                document.getElementById('kdFraksi').value='';
                                                                document.getElementById('jmlh').value='';
                                                                document.getElementById('proses').value="insert";								
                                                                //addData('0','0');
                                                        }
                                                        else
                                                        {
                                                                displayList();
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
function fillField(id)
{
        ar=id.split("##");
        noTiket=ar[1];
        kdFraksi=ar[0];
        param='noTiket='+noTiket+'&kdFraksi='+kdFraksi+'&proses=getData';
        tujuan='pabrik_slave_sortasi2.php';
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

                                                        ar=con.responseText.split("###");
                                                        document.getElementById('tmblPilih').innerHTML='';
                                                        document.getElementById('formInput').style.display='block';
                                                        document.getElementById('headher').style.display='block';
                                                        document.getElementById('listData').style.display='none';
                                                        document.getElementById('tgl').value=ar[3];
                                                        document.getElementById('tgl').disabled=true;
                                                        document.getElementById('kdFraksi').value=ar[1];
                                                        document.getElementById('noTiket').disabled=true;
                                                        document.getElementById('kdFraksi').disabled=true;
                                                        document.getElementById('jmlh').value=ar[2];
                                                        document.getElementById('proses').value='update';
                                                        addData(ar[3],ar[0]);

                                                        }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  


        }
function deldata(notiket,kdfraksi)
{
        noTiket=notiket;
        kdFraksi=kdfraksi;
        param='noTiket='+noTiket+'&kdFraksi='+kdFraksi+'&proses=delData';
        //alert(param);
        tujuan='pabrik_slave_sortasi2.php';
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
                                                //	alert(con.responseText);
                                                        displayList();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  
         if(confirm(" Clear grading for : "+noTiket+", are you sure ?"))
                post_response_text(tujuan, param, respog);
}
function delDet(notiket,kdfraksi)
{
        noTiket=notiket;
        kdFraksi=kdfraksi;
        param='noTiket='+noTiket+'&kdFraksi='+kdFraksi+'&proses=delData';
        //alert(param);
        tujuan='pabrik_slave_sortasi2.php';
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
                                                //	alert(con.responseText);
                                                        loadDataDetail();

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  
         if(confirm(" Clear grading for : "+noTiket+", are you sure ?"))
                post_response_text(tujuan, param, respog);
}
function printPDF(kdorg,tgl,ev) {
    // Prep Param
        kdORg=kdorg;
        daTtgl=tgl;
        param='kdOrg='+kdORg+'&daTtgl='+daTtgl;
    param += "&proses=pdf";

    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='kebun_curahHujanPdf.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}
function cariTiket()
{
        document.getElementById('headher').style.display='none';
        document.getElementById('listData').style.display='block';
        noTiket=document.getElementById('noTiketcr').value;
        param='noTiket='+noTiket+'&proses=cariData';
        //alert(param);
        tujuan='pabrik_slave_sortasi2.php';
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
                                                //	alert(con.responseText);

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
function cariData(num)
{
                noTiket=document.getElementById('noTiketcr').value;
                param='noTiket='+noTiket+'&proses=cariData';
                param+='&page='+num;
                tujuan = 'pabrik_slave_sortasi2.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert('ERROR TRANSACTION,\n' + con.responseText);
                                        }
                                        else {
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

a=0;
function getForm()
{
    //notkt=document.getElementById('noTiket').options[document.getElementById('noTiket').selectedIndex].value;
    tngl=document.getElementById('tgl').value;
    document.getElementById('tanggalForm').innerHTML=tngl;
    param='proses=createTable'+'&noTiket='+notkt;
    tujuan = 'pabrik_slave_sortasi2.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                               // alert(con.responseText);

                                document.getElementById('formDetail').innerHTML=con.responseText;
                                //document.getElementById('noTiket').disabled=true;
                                //document.getElementById('cancelAbn').disabled=false;
                                document.getElementById('showFormBwh').style.display="block";
                                if(a==0)
                                    {
                                        loadDataDetail()
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
function cancelForm()
{
    document.getElementById('formDetail').innerHTML='';
    //document.getElementById('noTiket').value='';
    //document.getElementById('noTiket').disabled=false;
    //document.getElementById('cancelAbn').disabled=true;
    document.getElementById('showFormBwh').style.display="none";
    displayList();

}
function loadDataDetail()
{
    //a=1;
    kdorg=document.getElementById('unitdt');
    kdorg=kdorg.options[kdorg.selectedIndex].value;
    tngl=document.getElementById('tgl').value;
    param='proses=loadDataDetail'+'&tgl='+tngl+'&kdOrg='+kdorg;
    tujuan = 'pabrik_slave_sortasi2.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                               // alert(con.responseText);

                                document.getElementById('isiDetail').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }
}
function addDetail(brs)
{
    baris=brs;
    row=baris;
    strUrl = '';
    for(i=1;i<=row;i++)
    {
    try{
        if(strUrl != '')
        {
            Fraksi=document.getElementById('fraksi_'+i).getAttribute('value');
            strUrl += '&isiData['+Fraksi+']='+encodeURIComponent(trim(document.getElementById('inputan_'+i).value))
                   +'&kdFraksi[]='+Fraksi;
        }
        else
        {
            Fraksi=document.getElementById('fraksi_'+i).getAttribute('value');
            strUrl += '&isiData['+Fraksi+']='+encodeURIComponent(trim(document.getElementById('inputan_'+i).value))
                   +'&kdFraksi[]='+Fraksi;
        }
    }
    catch(e){}
    }
    noTkt=document.getElementById('noTkt').value;
    jmlh=document.getElementById('jmlhJJg').value;
    //prsn=document.getElementById('persenBrnd').value
    kgPtngan=document.getElementById('kgPtngan').value
    pros=document.getElementById('proses').value;
    param="proses="+pros+"&noTiket="+noTkt+"&jmlhJJg="+jmlh+'&kgPtngan='+kgPtngan;
    param+=strUrl;
    fileTarget='pabrik_slave_sortasi2.php';
   // alert(param);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    loadDataDetail();
                    bersihForm();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);

    post_response_text(fileTarget, param, respon);
}
function bersihForm()
{
    row=document.getElementById('jmlhBaris').value;
//    for(i=1;i<=row;i++)
//    {
//        document.getElementById('inputan_'+i).value='';
//    }
    for(d=1;d<=row;d++)
    {
        document.getElementById('inputan_'+d).value='0';
    }

    document.getElementById('jmlhJJg').value=0;
    //document.getElementById('persenBrnd').value=0;
    document.getElementById('kgPtngan').value=0;
    document.getElementById('noTkt').disabled=false;
    document.getElementById('noTkt').value='';
    document.getElementById('nettox').innerHTML='';
    document.getElementById('jmlJJg').value=0;
    document.getElementById('bjrx').innerHTML='';
    
    document.getElementById('proses').value='insert';
}
function editDet(nTk,tanggal)
{
    notkt=nTk;
    tngl=tanggal;
    param='noTiket='+notkt+'&proses=EditData'+'&tgl='+tngl;
    fileTarget='pabrik_slave_sortasi2.php';
    document.getElementById('formDetail').innerHTML='';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                   // alert(con.responseText);
                   document.getElementById('formDetail').innerHTML=con.responseText;
                   document.getElementById('proses').value='update';
                   document.getElementById('noTkt').disabled=true;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);

    post_response_text(fileTarget, param, respon);
}
function editDetHead(nTk,tanggal)
{
    notkt=nTk;
    tngl=tanggal;
    param='noTiket='+notkt+'&proses=EditData'+'&tgl='+tngl;
    fileTarget='pabrik_slave_sortasi2.php';
    document.getElementById('formDetail').innerHTML='';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                   // alert(con.responseText);
                   document.getElementById('formDetail').innerHTML=con.responseText;
                   document.getElementById('proses').value='update';
                   document.getElementById('tgl').value=tanggal;
                   document.getElementById('noTkt').disabled=true;
                   document.getElementById('tgl').disabled=true;
                   document.getElementById('tanggalForm').innerHTML=tanggal;
                   document.getElementById('tmblPilih').innerHTML='<button class="mybutton" id="cancelAbn" onclick="cancelForm()" >'+canForm+'</button>';
                   document.getElementById('formInput').style.display='block';
                   document.getElementById('listData').style.display='none';
                   document.getElementById('formDetail').innerHTML=con.responseText;
                   //document.getElementById('isiDetail').innerHTML='';
                    //document.getElementById('noTiket').disabled=true;
                    //document.getElementById('cancelAbn').disabled=false;
                    document.getElementById('headher').style.display='block';
                   document.getElementById('showFormBwh').style.display="block";
                   loadDataDetail();

                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);

    post_response_text(fileTarget, param, respon);
}


function getNetto(noticket)
{
    param='noticket='+noticket+'&proses=getNetto';
    fileTarget='pabrik_slave_sortasi2.php';
    post_response_text(fileTarget, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
					arr=con.responseText.split("###");
					document.getElementById('nettox').value=arr[0];
					document.getElementById('nettox').innerHTML=arr[0];
					document.getElementById('jmlJJg').value=arr[1];
					if(arr[1]==0){
						document.getElementById('bjrx').value=0;
						document.getElementById('bjrx').innerHTML=0;
					}else{
						document.getElementById('bjrx').value=(arr[0]/arr[1]).toFixed(2);
						document.getElementById('bjrx').innerHTML=(arr[0]/arr[1]).toFixed(2);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }   
}

function hitungPotongan(val,kodefraksi,field)
{
    if(document.getElementById('bjrx').innerHTML=='' || document.getElementById('bjrx').innerHTML==0)
        alert(' Please insert bunch');
    else{
		fraksi = JSON.parse(document.getElementById('fraksi').value);
		potFraksi = JSON.parse(document.getElementById('potFraksi').value),
        pt = getValue('kodePt'),
        //koef = 7;
        koef = 12.5;
        if (pt=='SMA') {
            koef = 12.5;
        }
        
        jjg = document.getElementById('jmlhJJg').value;
		if(jjg==0){
			//alert('Janjang Sortasi Tidak boleh 0');
		}
        totpot=0;
        totprsn=0;
		for(x=1;x<=field;x++){
            tm1 = document.getElementById('inputan_'+x).value;
			fCode = document.getElementById('fraksi_'+x).value;
			var p;
			if(fCode=='A') tm1 = tm1 - 5;
			if(tm1<0) tm1=0;
			if(fraksi[fCode]=='JJG') {
				if(jjg==0){
					p = 0;
				}else{
					p = parseFloat(tm1) * potFraksi[fCode] * 100/jjg;
				}
			} else {
				if(parseFloat(tm1) > koef) {
					p = 0;
				} else {
					p = (koef - parseFloat(tm1)) * 0.3;
				}
			}
			totprsn = totprsn+p;
        }
        nettox=document.getElementById('nettox').innerHTML;
        nettox=parseFloat(nettox);
		if(jjg==0){
	        totpot=0;
		}else{
	        totpot=nettox*(totprsn/100);
		}
	    document.getElementById('kgPtngan').value=totpot.toFixed(2);
    }
}

function hitungBJR(d,val)
{
  nettox=document.getElementById('nettox').innerHTML;
  nettox=parseFloat(nettox);
  document.getElementById('bjrx').innerHTML=(nettox/d).toFixed(2).toString();
  hitungPotongan(0,'BRD',val);
}