// JavaScript Document
function getNosbp(){
	tgl=document.getElementById('tgl').value;
	param='proses=getNosbp'+'&tgl='+tgl;
	tujuan='kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		  if(con.readyState==4)
		  {
			if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
							//alert(con.responseText);
							document.getElementById('spbId').innerHTML=con.responseText;
					}
			}
			else {
					busy_off();
					error_catch(con.status);
			}
		  }	
	 }  
}
function getBersih(){
	brtMsk=document.getElementById('brtMsk').value;
	brtKlr=document.getElementById('brtKlr').value;
	brtBrsh=parseInt(brtMsk)-parseInt(brtKlr);
	if(isNaN(brtBrsh)){
		brtBrsh=0;
	}
	if(brtBrsh<0){
		alert("Nilai tidak boleh minus");
		brtBrsh=0;
	}
	document.getElementById('brtBrsh').value=brtBrsh;
}
function saveData(){
	tgl=document.getElementById('tgl').value;
	kdknd=document.getElementById('kdKend').value;
	nmspr=document.getElementById('nmSupir').value;
	jmjjg=document.getElementById('jmlhJjg').value;
	brtms=document.getElementById('brtMsk').value;
	brtklr=document.getElementById('brtKlr').value;
	brtbrsh=document.getElementById('brtBrsh').value;
	jmmsk=document.getElementById('jmMasuk');
	jmmsk=jmmsk.options[jmmsk.selectedIndex].value;
	mntmsk=document.getElementById('mntMasuk');
	mntmsk=mntmsk.options[mntmsk.selectedIndex].value;
	jmklr=document.getElementById('jmKeluar');
	jmklr=jmklr.options[jmklr.selectedIndex].value;
	mntklr=document.getElementById('mntKeluar');
	mntklr=mntklr.options[mntklr.selectedIndex].value;
	nosbp=document.getElementById('spbId');
	nosbp=nosbp.options[nosbp.selectedIndex].value;
	jjgSortsi=document.getElementById('JjgSortasi').value;
	potDt=document.getElementById('potKg').value;
	noTrans=document.getElementById('notrans').value;
	prs=document.getElementById('proses').value;
	jamMasuk=jmmsk+":"+mntmsk+":00";
	jamKeluar=jmklr+":"+mntklr+":00";
	param='proses='+prs+'&tgl='+tgl+'&kdKend='+kdknd+'&JjgSortasi='+jjgSortsi+'&potKg='+potDt;
	param+='&nmSupir='+nmspr+'&jmlhJjg='+jmjjg+'&brtMsk='+brtms+'&spbId='+nosbp+'&notransaksi='+noTrans;
	param+='&brtKlr='+brtklr+'&brtBrsh='+brtbrsh+'&jamMasuk='+jamMasuk+'&jamKeluar='+jamKeluar;
	tujuan='kebun_slave_timbangke_eksternal.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		  if(con.readyState==4)
		  {
			if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
					}
					else {
							//alert(con.responseText);
							displayList();
					}
			}
			else {
					busy_off();
					error_catch(con.status);
			}
		  }	
	 }  
}
function displayList(){
        document.getElementById('listData').style.display='block';
        document.getElementById('headher').style.display='none';
        document.getElementById('nosbpCr').value='';
        document.getElementById('tgl_cari').value='';
		cancelData();
		loadData(0);
		
}
function cancelData(){
	document.getElementById('tgl').value='';
	document.getElementById('kdKend').value='';
	document.getElementById('nmSupir').value='';
	document.getElementById('jmlhJjg').value='';
	document.getElementById('brtMsk').value='';
	document.getElementById('brtKlr').value='';
	document.getElementById('brtBrsh').value='';
	document.getElementById('jmMasuk').value='00';
	document.getElementById('mntMasuk').value='00';
	document.getElementById('jmKeluar').value='00';
	document.getElementById('mntKeluar').value='00';
	document.getElementById('JjgSortasi').value='';
	document.getElementById('potKg').value='';
	getNosbp();
	document.getElementById('proses').value='insert';
}
function loadData(num){
        nospbcr=document.getElementById('nosbpCr').value;
        tgl=document.getElementById('tgl_cari').value;
        param='proses=loadNewData'+'&nosbpCr='+nospbcr;
        param+='&tgl_cari='+tgl;
        param+='&page='+num;
        tujuan='kebun_slave_timbangke_eksternal.php';
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

function fillField(notransaksi,jammask2,jamklur,nokendaraan,supir,jumlahtandan1,beratmasuk,beratkeluar,beratbersih,jjgsortasi,kgpotsortas,nospb,tgl){
	document.getElementById('headher').style.display="block";
	document.getElementById('listData').style.display="none";
	document.getElementById('proses').value='updatedata';
	document.getElementById('notrans').value=notransaksi;
	jmmsk=jammask2.split(":");
	jammsk=document.getElementById('jmMasuk');
	for(x=0;x<jammsk.length;x++){
		if(jammsk.options[x].value==jmmsk[0]){
				jammsk.options[x].selected=true;
		}
	}
	mntmsk=document.getElementById('mntMasuk');
	for(x=0;x<mntmsk.length;x++){
		if(mntmsk.options[x].value==jmmsk[1]){
				mntmsk.options[x].selected=true;
		}
	}
	jmklr=jamklur.split(":");
	jamklr=document.getElementById('jmKeluar');
	for(x=0;x<jamklr.length;x++){
		if(jamklr.options[x].value==jmklr[0]){
				jamklr.options[x].selected=true;
		}
	}
	mntklr=document.getElementById('mntKeluar');
	for(x=0;x<mntklr.length;x++){
		if(mntklr.options[x].value==jmklr[1]){
				mntklr.options[x].selected=true;
		}
	}
	document.getElementById('kdKend').value=nokendaraan;
	document.getElementById('nmSupir').value=supir;
	document.getElementById('jmlhJjg').value=jumlahtandan1;
	document.getElementById('brtMsk').value=beratmasuk;
	document.getElementById('brtKlr').value=beratkeluar;
	document.getElementById('brtBrsh').value=beratbersih;
	document.getElementById('JjgSortasi').value=jjgsortasi;
	document.getElementById('potKg').value=kgpotsortas;
	document.getElementById('tgl').value=tgl;
	document.getElementById('proses').value='update';
	param='proses=getNosbp'+'&nospb='+nospb+'&tgl='+tgl;
	tujuan='kebun_slave_timbangke_eksternal.php';
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
										
                                                document.getElementById('spbId').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
              }	
         } 	
}
function deleteData(notrans){
	param='proses=deleteData'+'&notransaksi='+notrans;
	tujuan='kebun_slave_timbangke_eksternal.php';
	if(confirm("Anda Yakin Ingin Menghapus?")){
		post_response_text(tujuan, param, respog);
		function respog(){
			  if(con.readyState==4)
			  {
				if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
								alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else {
								//alert(con.responseText);
								displayList();
						}
				}
				else {
						busy_off();
						error_catch(con.status);
				}
			  }	
		 }
	}
	  
}
/* 
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}
function excel(ev,kodeorg,periodegaji,tipepotongan)
{
        param='method=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
        //alert(param);
        tujuan='kebun_slave_timbangke_eksternalExcel.php';
        judul='Print Excel';		
        printFile(param,tujuan,judul,ev)	
}

function getPrd(){
        kdOrg=document.getElementById('kdOrg');
        kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
        prd=document.getElementById('tglAbsen');
        prd=prd.options[prd.selectedIndex].value;
        param='periode='+prd+'&proses=getPrd'+'&kdOrg='+kdOrg;
        tujuan='kebun_slave_timbangke_eksternal.php';
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
                                                        document.getElementById('tglAbsen').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  	
}

function cariOrg(title,content,ev){
        width='500';
        height='400';
        showDialog1(title,content,width,height,ev);
        //alert('asdasd');
}
function findOrg(){
        txt=trim(document.getElementById('fnOrg').value);
        if(txt=='')
        {
                alert('Text is obligatory');
        }
        else if(txt.length<3)
        {
                alert('Text too short');
        }
        else
        {
                param='txtfind='+txt+'&proses=cariOrg';
                tujuan='kebun_slave_timbangke_eksternal.php';
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
function setOrg(kdOrg,nmOrg){
        document.getElementById('kdOrg').value=kdOrg;
        document.getElementById('nmOrg').value=nmOrg;
        closeDialog();
}
function findOrg2()
{
        txt=trim(document.getElementById('crOrg').value);
        if(txt=='')
        {
                alert('Text is obligatory');
        }
        else if(txt.length<3)
        {
                alert('Text too short');
        }
        else
        {
                param='txtfind='+txt+'&proses=cariOrg2';
                tujuan='kebun_slave_timbangke_eksternal.php';
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
function setOrg2(kdOrg,nmOrg)
{
        document.getElementById('kdOrg').value=kdOrg;
        document.getElementById('txtsearch').value=nmOrg;
        closeDialog();
}
function add_detail(){
        kdOrg=document.getElementById('kdOrg');
        kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
        prd=document.getElementById('tglAbsen');
        prd=prd.options[prd.selectedIndex].value;
        tpPot=document.getElementById('tpPotongan');
        tpPot=tpPot.options[tpPot.selectedIndex].value;
        param='kdOrg='+kdOrg+'&proses=createTable';
        param+='&periode='+prd+'&tipePot='+tpPot; 
        tujuan='kebun_slave_timbangke_eksternal.php';
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
                                                            document.getElementById('detailEntry').style.display='block';
                                                            document.getElementById('detailIsi').innerHTML=con.responseText;
                                                            document.getElementById('tmbLheader').innerHTML='';
                                                            lockForm();
                                                    }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  

}

function lockForm(){
        document.getElementById('kdOrg').disabled=true;
        document.getElementById('tglAbsen').disabled=true;
        document.getElementById('tpPotongan').disabled=true;
        document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
        document.getElementById('kdOrg').disabled=false;
        document.getElementById('tglAbsen').disabled=false;
        document.getElementById('tpPotongan').disabled=false;
        document.getElementById('kdOrg').value='';
        document.getElementById('tglAbsen').value='';
        document.getElementById('tpPotongan').value='';
        document.getElementById('tombolHeader').style.display="block";
}
status_inputan=0;
function addDetail() {
        if(status_inputan==0){
                if(confirm('Are you sure..?')){
                        saveData();	
                }
        }
        else if(status_inputan!=0){
                saveData();	
        }

}
function saveData(){
        kdOrg=document.getElementById('kdOrg');
        kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
        prd=document.getElementById('tglAbsen');
        prd=prd.options[prd.selectedIndex].value;
        tpPot=document.getElementById('tpPotongan');
        tpPot=tpPot.options[tpPot.selectedIndex].value;
        karyId=document.getElementById('krywnId');
        karyId=karyId.options[karyId.selectedIndex].value;
        rpPot=document.getElementById('rpPot').value;
        ketpot=document.getElementById('ketPot').value;
        
        
        pros=document.getElementById('proses').value;
        if(pros!="updateDetail"){
                param = "proses=saveData";
        }
        else{
                param = "proses=updateDetail";
        }
        param+='&kdOrg='+kdOrg;
        param+='&periode='+prd+'&tipePot='+tpPot+'&krywnId='+karyId; 
        param+='&rupPot='+rpPot+'&ketPot='+ketpot
        tujuan='kebun_slave_timbangke_eksternal.php';
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
                                                        status_inputan=1;
                                                        lockForm();
                                                        showTmbl();
                                                        bersihFormDet();
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
function editDetail(karyawn,rppot,ketrng) {
    document.getElementById('krywnId').value=karyawn;
    document.getElementById('krywnId').disabled=true;
    document.getElementById('rpPot').value=rppot;
    document.getElementById('ketPot').value=ketrng;
    document.getElementById('proses').value="updateDetail";
}
statFrm=0;
function showTmbl()
{
        if(statFrm==0)
        {
                document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
        }
        else if(statFrm==1)
        {
                document.getElementById('tombol').innerHTML="<button class=mybutton onclick=frm_aju()>"+nmTmblDone+"</button>";
        }
}

function bersihFormDet(){
                document.getElementById('krywnId').disabled=false;
                document.getElementById('ketPot').value='';
                document.getElementById('krywnId').value='';
                document.getElementById('proses').value="saveData";
}

function delDetail(kdorg,period,krywn,tppot){
        param+='&kdOrg='+kdorg;
        param+='&periode='+period+'&tipePot='+tppot+'&krywnId='+krywn; 
        param+='&proses=delDetail';
        tujuan='kebun_slave_timbangke_eksternal.php';
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                        loadDetail();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
        if(confirm("Deleting, are you sure..?"))
        post_response_text(tujuan, param, respog);	
}

function loadData(num){
        kdorg=document.getElementById('kdOrgCr');
        kdorg=kdorg.options[kdorg.selectedIndex].value;
        tgl=document.getElementById('tgl_cari').value;
        tppot=document.getElementById('tpPotCr');
        tppot=tppot.options[tppot.selectedIndex].value;
        param='proses=loadNewData'+'&kdOrgCr='+kdorg;
        param+='&periodecr='+tgl+'&tipePotCr='+tppot;
        param+='&page='+num;
        tujuan='kebun_slave_timbangke_eksternal.php';
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
function loadDetail(){
        kdOrg=document.getElementById('kdOrg');
        kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
        prd=document.getElementById('tglAbsen');
        prd=prd.options[prd.selectedIndex].value;
        tpPot=document.getElementById('tpPotongan');
        tpPot=tpPot.options[tpPot.selectedIndex].value;
        param='kdOrg='+kdOrg+'&periode='+prd+'&tipePot='+tpPot;
        param+='&proses=loadDetail';
        //alert(param);
        tujuan='kebun_slave_timbangke_eksternal.php';
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
                                                        document.getElementById('contentDetail').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 	

}
function fillField(kdorg,prder,potong){
   
       kdOrg=document.getElementById('kdOrg');
       for(x=0;x<kdOrg.length;x++){
                if(kdOrg.options[x].value==kdorg)
                {
                        kdOrg.options[x].selected=true;
                }
       }
       prd=document.getElementById('tglAbsen');
       for(x=0;x<prd.length;x++){
                if(prd.options[x].value==prder)
                {
                        prd.options[x].selected=true;
                }
       }
       tppot=document.getElementById('tpPotongan');
       for(x=0;x<tppot.length;x++){
                if(tppot.options[x].value==potong)
                {
                        tppot.options[x].selected=true;
                }
       }
        param='kdOrg='+kdorg+'&periode='+prder+'&tipePotongan='+potong+'&statUpdate=1';
        param+="&proses=createTable";
        //alert(param);
        tujuan='kebun_slave_timbangke_eksternal.php';
        post_response_text(tujuan, param, respon);
                function respon(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert('ERROR TRANSACTION,\n' + con.responseText);
                                        } else {
                                                // Success Response
                                lockForm();
                                document.getElementById('listData').style.display='none';
                                document.getElementById('headher').style.display='block';
                                document.getElementById('detailEntry').style.display='block';
                                var detailDiv = document.getElementById('detailIsi');
                                detailDiv.innerHTML = con.responseText;
                                status_inputan=1;
                                statFrm=1;
                                showTmbl();
                                loadDetail();
                                        }
                                } else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }


}

function delData(kdorg,prder,potong){
        param+='&kdOrg='+kdorg;
        param+='&periode='+prder+'&tipePot='+potong;
        param+='&proses=delData';
        tujuan='kebun_slave_timbangke_eksternal.php';
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                }
                                else {
                                        displayList();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
        if(confirm("Deleteing, are you sure..?"))
        post_response_text(tujuan, param, respog);	
}
function frm_aju()
{

        if(statFrm==0)
        {
                if(confirm("Done, are you sure..?"))
                {
                        displayList();
                }
        }
        else if(statFrm==1)
        {		
                if(confirm("Done, are you sure..?"))
                {
                        displayList();
                }
        }
}
function reset_data()
{
        if(statFrm==0)
        {
                if(confirm("Canceling, are you sure..?"))
                {
                        kdorg=document.getElementById('kdOrg').value;
                        tgl=document.getElementById('tglAbsen').value;
                        delDataAll(kdorg,tgl);
                }
        }

}

 
function getKary(title,pil,ev){
        utkUnit=document.getElementById('kdOrg');
        utkUnit=utkUnit.options[utkUnit.selectedIndex].value;
        prd=document.getElementById('tglAbsen').value;
        tpPot=document.getElementById('tpPotongan');
        tpPot=tpPot.options[tpPot.selectedIndex].value;
        
         if(pil==1){
                content= "<div style='width:100%;'>";
                content+="<fieldset>"+title+"<input type=hidden id=unit value="+utkUnit+" /><input type=hidden id=tppot value="+tpPot+" /><input type=hidden id=periode value="+prd+" /><input type=text id=txtnamabarang class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariKary("+pil+")>Go</button> </fieldset>";
                content+="<div id=containercari style='overflow:scroll;height:300px;width:520px'></div></div>";                 
         }

     //display window
	   width='550';
	   height='350';
	   showDialog1(title,content,width,height,ev);		
}
function goCariKary(pil){
    //keu_slave_2globalfungsi
        lokTgs=document.getElementById('unit').value;
        tppotongan=document.getElementById('tppot').value;
        prd=document.getElementById('periode').value;
        nmkary=document.getElementById('txtnamabarang').value;
        param='unit='+lokTgs+'&tppot='+tppotongan+'&periode='+prd+'&nmkary='+nmkary;
       
        if(pil==1){
            param+='&proses=getKary';
        }
    tujuan = 'kebun_slave_timbangke_eksternal.php';
    post_response_text(tujuan, param, respog);				
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
function setKary(karyid){
      kar=document.getElementById('krywnId');
      for(x=0;x<kar.length;x++){
        if(kar.options[x].value==karyid){
                kar.options[x].selected=true;
        }
      }
      closeDialog();
} */