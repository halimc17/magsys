// JavaScript Document

function dataKeExcel(ev,tujuan,nokontrak)
{
        judul='Report Ms.Excel';	
        param='nokontrak='+nokontrak+'&proses=excel';
        printFile(param,tujuan,judul,ev)	
}


function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev); 	
}
function formDetail(nokontrak,ev){
   title="Add "+nokontrak;
   width='780';
   height='320';
   content="<div id=continerform style=width:600;height:320;overflow:auto;> </div>";
   showDialog1(title,content,width,height,ev); 	
}
function addDetail(nokontrak,totKnrtk,komoditi,ev){
	formDetail(nokontrak,ev)
	param='method=getFormDet'+'&nokontrak='+nokontrak;
	param+='&totKontrak='+totKnrtk+'&komoditi='+komoditi;
        //alert(param);
	tujuan='pmn_kontrakjual_slave.php';
	function respog(){
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
                                document.getElementById('continerform').innerHTML=con.responseText;
                                document.getElementById('nokntr_ref2').value="";
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }	
    } 	
		 post_response_text(tujuan, param, respog);
}
function loadNewData(){
        param='method=LoadNew';
        tujuan='pmn_kontrakjual_slave.php';
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
                                document.getElementById('containerlist').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }	
         } 	
		 post_response_text(tujuan, param, respog);
}
function cariBast(num){				
				txtSearch=document.getElementById('txtnokntrk').value;
				ptSch=document.getElementById('ptSch').value;
				param='txtSearch='+txtSearch+'&ptSch='+ptSch+'&method=LoadNew'
                param+='&page='+num;
                tujuan = 'pmn_kontrakjual_slave.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert('ERROR TRANSACTION,\n' + con.responseText);
                                        }
                                        else {
                                                document.getElementById('containerlist').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}
function saveKP(){
        noKntrk=document.getElementById('noKtrk').value;
        custid=document.getElementById('custId').value;
        tglkntr=document.getElementById('tlgKntrk').value;
		/*detail barang*/
        kdbrg=document.getElementById('kdBrg').value;
        satuan=document.getElementById('stn').value;
        HrgStn=remove_comma_var(document.getElementById('HrgStn').value);
        tBlg=document.getElementById('tBlg').innerHTML;
        qty=remove_comma_var(document.getElementById('jmlh').value);
        ppn=document.getElementById('ppnId');
        ppn=ppn.options[ppn.selectedIndex].value;
        param='noKntrk='+noKntrk+'&custId='+custid+'&tlgKntrk='+tglkntr+'&kdBrg='+kdbrg;
        param+='&satuan='+satuan+'&tBlg='+tBlg+'&qty='+qty+'&HrgStn='+HrgStn+'&ppnId='+ppn;
		
		/*tanggal dan jumlah penyerahan */
        tglKrm0=document.getElementById('tglKrm0').value;
        tglKrm1=document.getElementById('tglKrm1').value;
        tglKrm2=document.getElementById('tglKrm2').value;
        tglKrm3=document.getElementById('tglKrm3').value;
        tglSd0=document.getElementById('tglSd0').value;
        tglSd1=document.getElementById('tglSd1').value;
        tglSd2=document.getElementById('tglSd2').value;
        tglSd3=document.getElementById('tglSd3').value;
        jmlh0=remove_comma_var(document.getElementById('jmlh0').value);
        jmlh1=remove_comma_var(document.getElementById('jmlh1').value);
        jmlh2=remove_comma_var(document.getElementById('jmlh2').value);
        jmlh3=remove_comma_var(document.getElementById('jmlh3').value);
        param+='&tglKrm0='+tglKrm0+'&tglKrm1='+tglKrm1+'&tglKrm2='+tglKrm2;
        param+='&tglKrm3='+tglKrm3+'&tglSd0='+tglSd0+'&tglSd1='+tglSd1;
        param+='&tglSd2='+tglSd2+'&tglSd3='+tglSd3+'&jmlh0='+jmlh0;
        param+='&jmlh1='+jmlh1+'&jmlh2='+jmlh2+'&jmlh3='+jmlh3;
		
		/*toleransi,kualitas dan franco*/
        tlransi=document.getElementById('tlransi').value;
        franco=document.getElementById('tmbngn');
        franco=franco.options[franco.selectedIndex].value;
        nmperson=document.getElementById('nmPerson');
        nmperson=nmperson.options[nmperson.selectedIndex].value;
        kualitasffa=document.getElementById('ffa').value;
        kualitasdob=document.getElementById('dobi').value;
        kualitasmdani=document.getElementById('mdani').value;
        moist=document.getElementById('moist').value;
        dirt=document.getElementById('dirt').value;        
        grading=document.getElementById('grading').value;   
        param+='&tlransi='+tlransi+'&franco='+franco+'&kualitasffa='+kualitasffa;
        param+='&kualitasdob='+kualitasdob+'&kualitasmdani='+kualitasmdani+'&nmPerson='+nmperson;
        param+='&moist='+moist+'&dirt='+dirt+'&grading='+grading;
        
		
		/*syart,term pembayaran*/
        syrtByr=document.getElementById('syrtByr');
        syrtByr=syrtByr.options[syrtByr.selectedIndex].value;
        byrKe=document.getElementById('byrKe');
        byrKe=byrKe.options[byrKe.selectedIndex].value;
		tndtng=document.getElementById('tndtng').value;
        tndtngJbtn=document.getElementById('tndtngJbtn').value;
        tndtngPembli=document.getElementById('tndtngPembli').value;
        jtbnPembli=document.getElementById('jtbnPembli').value;
        cttnLain=document.getElementById('cttnLain').value;
        kdPt=document.getElementById('kdPt').value;
        kurs=document.getElementById('kurs').value;
        tglbayar=document.getElementById('tglByr').value;
        met=document.getElementById('method').value;
		kntrk=document.getElementById('kntrkRef');
        kntrk=kntrk.options[kntrk.selectedIndex].value;
        
        param+='&method='+met+'&syrtByr='+syrtByr+'&byrKe='+byrKe+'&tndtng='+tndtng;
        param+='&tndtngJbtn='+tndtngJbtn+'&tndtngPembli='+tndtngPembli+'&kurs='+kurs;
        param+='&jtbnPembli='+jtbnPembli+'&cttnLain='+cttnLain+'&kdPt='+kdPt+'&tglByr='+tglbayar+'&kntrkRef='+kntrk;
		
		if(byrKe == ""){
			alert("Field bayar ke, harus diisi.");
			return false;
		}
		
        tujuan='pmn_kontrakjual_slave.php';
        
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
                                                        //document.getElementById('stn').innerHTML=con.responseText;
                                                        loadNewData();
                                                        clearFrom();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
         if(confirm("Are you sure?"))	
         {
                post_response_text(tujuan, param, respog);
         }

}

function clearFrom(){
        location.reload();
}
function getSatuan(kdbrg,cust,sat)
{
        if((kdbrg==0)||(cust==0)||(sat==0))
        {
                kdBrg=document.getElementById('kdBrg').value;
                param='kdBrg='+kdBrg+'&method=getSatuan';
        }
        else
        {
                kdBrg=kdbrg;
                satuan=sat;
                param='kdBrg='+kdBrg+'&method=getSatuan'+'&satuan='+satuan;
        }

        //alert(param);
        tujuan='pmn_kontrakjual_slave.php';

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
                                                        document.getElementById('stn').innerHTML=con.responseText;
                                                            if(cust!=0){
                                                                    getDataCust(cust);
                                                            }

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function copyFromLast()
{
        param='method=getLastData';
        tujuan='pmn_kontrakjual_slave.php';
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
                                                        document.getElementById('noKtrk').disabled=false;
                                                        ar=con.responseText.split("###");
                                                        document.getElementById('noKtrk').value=ar[0];
                                                        document.getElementById('custId').value=ar[1];
                                                        document.getElementById('tlgKntrk').value=ar[2];
                                                        document.getElementById('kdBrg').value=ar[3];
                                                        document.getElementById('HrgStn').value=ar[4];
                                                        document.getElementById('tBlg').value=ar[5];
                                                        document.getElementById('jmlh').value=ar[6];
                                                        document.getElementById('tglKrm').value=ar[7];
                                                        document.getElementById('tglSd').value=ar[8];
                                                        document.getElementById('tlransi').value=ar[9];
                                                        document.getElementById('noDo').value=ar[10];
                                                        document.getElementById('kualitas').value=ar[11];
                                                        document.getElementById('syrtByr').value=ar[12];
                                                        document.getElementById('tndtng').value=ar[13];
                                                        document.getElementById('tmbngn').value=ar[14];
                                                        document.getElementById('cttn1').value=ar[15];
                                                        document.getElementById('cttn2').value=ar[16];
                                                        document.getElementById('cttn3').value=ar[17];
                                                        document.getElementById('cttn4').value=ar[18];
                                                        document.getElementById('cttn5').value=ar[19];
                                                        document.getElementById('othCttn').value=ar[20];
                                                        getSatuan(ar[3],ar[1],ar[21]);
                                                        document.getElementById('kdPt').value=ar[22];

                                                        //document.getElementById('stn').value;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function getDataCust(dt)
{
        if(dt==0)
        {
                custId=document.getElementById('custId').value;
        }
        else
        {
                custId=dt;
        }
        param='method=getCust'+'&custId='+custId;
        tujuan='pmn_kontrakjual_slave.php';
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
                                                        document.getElementById('nmPerson').innerHTML=ar[0];
                                                        document.getElementById('kdBrg').innerHTML=ar[1];
														document.getElementById('tlransi').value=ar[2];
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}
function fillField(nokntrk)
{
        noKntrk=nokntrk;
        param='method=getEditData'+'&noKntrk='+noKntrk;
        tujuan='pmn_kontrakjual_slave.php';
        tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
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
                                                    document.getElementById('method').value='update';
                                                   // alert(con.responseText);
                                                    ar=con.responseText.split("###");
                                                    document.getElementById('noKtrk').value=ar[0];
                                                    document.getElementById('custId').value=ar[1];
                                                    document.getElementById('tlgKntrk').value=ar[2];
                                                    /*detail barang*/
                                                    document.getElementById('kdBrg').innerHTML=ar[3];
                                                    document.getElementById('stn').innerHTML=ar[4];
                                                    document.getElementById('HrgStn').value=ar[5];
                                                    document.getElementById('kurs').value=ar[6];
                                                    document.getElementById('tBlg').innerHTML=ar[7];
                                                    document.getElementById('jmlh').value=ar[8];

                                                    /*tanggal dan jumlah penyerahan */
                                                    document.getElementById('tglKrm0').value=ar[9];
                                                    document.getElementById('tglSd0').value=ar[10];
                                                    document.getElementById('tglKrm1').value=ar[11];
                                                    document.getElementById('tglSd1').value=ar[12];
                                                    document.getElementById('tglKrm2').value=ar[13];
                                                    document.getElementById('tglSd2').value=ar[14];
                                                    document.getElementById('tglKrm3').value=ar[15];
                                                    document.getElementById('tglSd3').value=ar[16];
                                                    document.getElementById('jmlh0').value=ar[17];
                                                    document.getElementById('jmlh1').value=ar[18];
                                                    document.getElementById('jmlh2').value=ar[19];
                                                    document.getElementById('jmlh3').value=ar[20];

                                                    /*toleransi,kualitas dan franco*/
                                                    document.getElementById('tmbngn').value=ar[21];
                                                    document.getElementById('ffa').value=ar[22];
                                                    document.getElementById('dobi').value=ar[23];
                                                    document.getElementById('mdani').value=ar[24];
                                                    document.getElementById('tlransi').value=ar[25];

                                                    /*syart,term pembayaran*/
                                                    document.getElementById('syrtByr').value=ar[26];
                                                    document.getElementById('byrKe').innerHTML=ar[27];
                                                    document.getElementById('tndtng').value=ar[28];
                                                    document.getElementById('tndtngJbtn').value=ar[29];
                                                    document.getElementById('tndtngPembli').value=ar[30];
                                                    document.getElementById('jtbnPembli').value=ar[31];
                                                    document.getElementById('cttnLain').value=ar[32];
                                                    document.getElementById('nmPerson').innerHTML=ar[33];
                                                    jk=document.getElementById('kdPt');
                                                    for(x=0;x<jk.length;x++){
                                                                    if(jk.options[x].value==ar[34])
                                                                    {
                                                                                    jk.options[x].selected=true;
                                                                    }
                                                    }
                                                    jk.disabled=true;
                                                    jk2=document.getElementById('ppnId');
                                                    for(x=0;x<jk2.length;x++){
                                                                    if(jk2.options[x].value==ar[35])
                                                                    {
                                                                                    jk2.options[x].selected=true;
                                                                    }
                                                    }
                                                    document.getElementById('tglByr').value=ar[36];
                                                    //alert(ar[3]);
                                                    document.getElementById('moist').value=ar[37];
                                                    document.getElementById('dirt').value=ar[38];
                                                    document.getElementById('grading').value=ar[39];
													document.getElementById('kntrkRef').innerHTML=ar[40];
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	
        post_response_text(tujuan, param, respog);
}

function delData(nokontrk)
{
        noKntrk=nokontrk;
        param='method=dataDel'+'&noKntrk='+noKntrk;
        // alert(param);
        tujuan='pmn_kontrakjual_slave.php';
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
                                                        //document.getElementById('stn').innerHTML=con.responseText;
                                                        //clearFrom();
                                                        //tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
                                                        document.getElementById('method').value='insert';

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
         if(confirm("Are you sure?"))	
         {
                post_response_text(tujuan, param, respog);
         }

}
function cariNoKntrk()
{
        txtSearch=document.getElementById('txtnokntrk').value;
        ptSch=document.getElementById('ptSch').value;
        //param='txtSearch='+txtSearch+'&method=cariNokntrk';
        param='txtSearch='+txtSearch+'&ptSch='+ptSch+'&method=LoadNew';
        
    //
        tujuan='pmn_kontrakjual_slave.php';
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
                                                        //document.getElementById('stn').innerHTML=con.responseText;
                                                        //clearFrom();
                                                        //tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
                                                        //tabAction(document.getElementById('tabFRM1'),0,'FRM',1);	
                                                        document.getElementById('containerlist').innerHTML=con.responseText;

                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
}
function getRek(){
		pt=document.getElementById('kdPt');
		pt=pt.options[pt.selectedIndex].value;
		param='kdpt='+pt+'&method=getRek';
		tujuan='pmn_kontrakjual_slave.php';
        post_response_text(tujuan, param, respog);
        function respog(){
			if(con.readyState==4){
				if (con.status == 200){
								busy_off();
								if (!isSaveResponse(con.responseText)) {
										alert('ERROR TRANSACTION,\n' + con.responseText);
								}
								else {
										dert=con.responseText.split("####");
										document.getElementById('byrKe').innerHTML=dert[0];
										document.getElementById('kntrkRef').innerHTML=dert[1];

								}
						}
						else {
								busy_off();
								error_catch(con.status);
				}
			}	
         } 
}

function getBerat(){
	var isi;
	isi=document.getElementById('jmlh').value;
	document.getElementById('jmlh0').value=isi;
}

function hitungHarga() {
    var hargasatuan = remove_comma_var(getValue('HrgStn')),
        kuantitas = remove_comma_var(getValue('jmlh')),
        container = getById('tmpHarga');
    if (hargasatuan=='') hargasatuan = 0;
    if (kuantitas=='') kuantitas = 0;
    container.value = parseFloat(hargasatuan) * parseFloat(kuantitas);
}
function saveDet(){
    nokontr=document.getElementById('nokontrak').value;
    jmlhnokontr=document.getElementById('jmlHnokontrak').value;
    nokntrkRef=document.getElementById('nokntr_ref');
    nokntrkRef=nokntrkRef.options[nokntrkRef.selectedIndex].value;
    kuota=document.getElementById('jmlhRef').value;
    nokRef=document.getElementById('nokntr_ref2').value;
    param='method=saveDet'+'&nokontrak='+nokontr+'&jmlHnokontrak='+jmlhnokontr;
    param+='&nokntr_ref='+nokntrkRef+'&jmlhRef='+kuota+'&nokntr_ref2='+nokRef;
    tujuan='pmn_kontrakjual_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    loadDetail(nokontr);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
    
}
function loadDetail(nokontrak){
    param='method=loadDet'+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                   document.getElementById('isidetail').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}
function delData2(nokontrak,nokntr_ref){
    param='method=delDet';
    param+='&nokntr_ref='+nokntr_ref+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave.php';
    if(confirm("Anda Yakin Menghapus No.Kontrak induk "+nokntr_ref+"?")){
        post_response_text(tujuan, param, respog);
    }
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    loadDetail(nokontrak);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}
function fillField2(nokontrak,nokntr_ref){
    param='method=editDet';
    param+='&nokntr_ref='+nokntr_ref+'&nokontrak='+nokontrak;
    tujuan='pmn_kontrakjual_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    isied=con.responseText.split("####");
                    document.getElementById('nokntr_ref').innerHTML=isied[1];
                    document.getElementById('jmlhRef').value=isied[2];
                    document.getElementById('nokntr_ref2').value=isied[3];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     } 
}