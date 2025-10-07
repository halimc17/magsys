

function loadData(hal){
    
    if(hal=='1.1'){
       hal='1.1';
    }
    
    param='proses=loadData'+'&page='+hal;
     if(hal=='1.1'){
       hal=document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value;
       param+='&page2='+hal;
    }
    
    tujuan='kebun_slave_taksasi.php';
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
                        document.getElementById('dataList').style.display='block';
                        document.getElementById('formData').style.display='none';
                        document.getElementById('container').innerHTML=con.responseText;
                        cancelIsi();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
     
    
}
function cariData(hal){
    if(hal=='1.1'){
       hal=document.getElementById('pages').options[document.getElementById('pages').selectedIndex].value;
        
    }
    valtxt=document.getElementById('sNoTrans').value;
    param='proses=cariData'+'&page='+hal;
    if(valtxt!=''){
        param+='&sNoTrans='+valtxt;
    }
    tujuan='kebun_slave_taksasi.php';
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
                        document.getElementById('dataList').style.display='block';
                        document.getElementById('formData').style.display='none';
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
function saveData(fileTarget,passParam) {
    var elor = '';
//    var jumlahpokok=document.getElementById('jmlhpokok').value;
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            if(getValue(passP[i])==''){
                alert('tanggal tidak boleh kosong.');
                elor='eror';
            }
            param += passP[i]+"="+getValue(passP[i]);
        } else if(i==3) {
            if(getValue(passP[i])==''){
                alert('blok tidak boleh kosong.');
                elor='eror';
            }
            param += "&"+passP[i]+"="+getValue(passP[i]);
        } else if(i==8) {
            if(getValue(passP[i])==''){
                alert('jumlah pokok tidak boleh kosong.\n silakan mengisi luas dan melengkapi data di SETUP - BLOK.');
                elor='eror';
            }
            if(getValue(passP[i])=='0'){
                alert('jumlah pokok tidak boleh kosong.\n silakan mengisi luas dan melengkapi data di SETUP - BLOK.');
                elor='eror';
            }
            param += "&"+passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
//	alert(param);
 
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
//                        loadData();
//                        cancelIsi();
                        selesaiIsi();
                        alert('Done.');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
  if(elor=='')
    post_response_text(fileTarget+'.php', param, respon);

}

function cancelIsi(){
    //$arr="##tanggal##afdeling##blok##seksi##proses##hasisa##haesok##jmlhpokok##persenbuahmatang##jjgmasak##jjgoutput##hkdigunakan##bjr";
            document.getElementById('afdeling').value='';
            document.getElementById('tanggal').diabled=false;
            document.getElementById('sNoTrans').value='';
            document.getElementById('blok').value='';
            document.getElementById('seksi').value='';
            document.getElementById('hasisa').value='';
            document.getElementById('haesok').value='';
            document.getElementById('jmlhpokok').value='';
            document.getElementById('persenbuahmatang').value='';
            document.getElementById('jjgmasak').value='';
            document.getElementById('jjgoutput').value='';
            document.getElementById('hkdigunakan').value='';
            document.getElementById('bisapanen').value='';
            document.getElementById('bjr').value='';
            document.getElementById('sph').value='';
            document.getElementById('tanggal').disabled=false;
            document.getElementById('afdeling').disabled=false;
            document.getElementById('proses').value='insert';
            document.getElementById('kebundt').disabled=false;
//            document.getElementById('kebundt').value='';
//            document.getElementById('mandor').disabled=false;
//            document.getElementById('afdeling').innerHTML="";
//            document.getElementById('afdeling').innerHTML="<option value=''></option>";
//            document.getElementById('blok').innerHTML="";
//            document.getElementById('blok').innerHTML="<option value=''></option>";
//            document.getElementById('mandor').innerHTML="";
//            document.getElementById('mandor').innerHTML="<option value=''></option>";
            
}

function selesaiIsi(){
    //$arr="##tanggal##afdeling##blok##seksi##proses##hasisa##haesok##jmlhpokok##persenbuahmatang##jjgmasak##jjgoutput##hkdigunakan##bjr";
            document.getElementById('tanggal').disabled=true;
            document.getElementById('kebundt').disabled=true;
            document.getElementById('afdeling').disabled=true;
            document.getElementById('sNoTrans').value='';
            document.getElementById('blok').value='';
            document.getElementById('seksi').value='';
            document.getElementById('hasisa').value='';
            document.getElementById('haesok').value='';
            document.getElementById('jmlhpokok').value='';
            document.getElementById('persenbuahmatang').value='';
            document.getElementById('jjgmasak').value='';
            document.getElementById('jjgoutput').value='';
            document.getElementById('hkdigunakan').value='';
            document.getElementById('bisapanen').value='';
            document.getElementById('bjr').value='';            
            document.getElementById('sph').value='';            
}


function showAdd(){
    document.getElementById('dataList').style.display='none';
    document.getElementById('formData').style.display='block';
    cancelIsi();
}

function showEdit(notrans,tgl,blok){
               
        param='proses=getData'+'&afdeling='+notrans+'&tanggal='+tgl;
        param+='&blok='+blok
        fileTarget='kebun_slave_taksasi';
        function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    document.getElementById('dataList').style.display='none';
                    document.getElementById('formData').style.display='block';
                    cancelIsi();
//echo $rts['afdeling']."###".tanggalnormal($rts['tanggal'])."###".$rts['blok']."###".$rts['seksi']."###".$rts['hasisa']."###".$rts['haesok']."###".$rts['jmlhpokok']."###".$rts['persenbuahmatang']."###".$rts['jjgmasak']."###".$rts['jjgoutput']."###".$rts['hkdigunakan']."###".$rts['bjr'];                    
                    isiDt=con.responseText.split("###");
                    //document.getElementById('afdeling').value=isiDt[0];
                    document.getElementById('tanggal').value=isiDt[1];
                    document.getElementById('blok').value=isiDt[2];
                    document.getElementById('seksi').value=isiDt[3];
                    document.getElementById('hasisa').value=isiDt[4];
                    document.getElementById('haesok').value=isiDt[5];
                    document.getElementById('jmlhpokok').value=isiDt[6];
                    document.getElementById('persenbuahmatang').value=isiDt[7];
                    document.getElementById('jjgmasak').value=isiDt[8];
                    document.getElementById('jjgoutput').value=isiDt[9];
                    document.getElementById('hkdigunakan').value=isiDt[10];
                    document.getElementById('bjr').value=isiDt[11];                   
                    //document.getElementById('proses').value='update';
                    document.getElementById('tanggal').disabled=true;
                    document.getElementById('afdeling').disabled=true;
                    document.getElementById('kebundt').disabled=true;
//                     document.getElementById('mandor').disabled=true;
                    kbn=isiDt[0].substring(0,4);
                    document.getElementById('kebundt').value=kbn;
                   
                    getAfdeling(kbn,isiDt[0],isiDt[2]);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php', param, respon);
                    
}
function deleteData(notrans,tgl,blok){
    param='proses=delete'+'&afdeling='+notrans+'&tanggal='+tgl;
    param+='&blok='+blok
    fileTarget='kebun_slave_taksasi.php';
    if(confirm("Anda Yakin Ingin Menghapus Data Ini?")){
        post_response_text(fileTarget, param, respon);
    }
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    loadData(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getAfdeling(kbn,afd,kary){
    if(kbn==0||afd==0||kary==0){
        dr=document.getElementById('kebundt').options[document.getElementById('kebundt').selectedIndex].value;
        kbn=dr;
        param='proses=getAfd'+'&kebun='+kbn;
    }
    else{
        param='proses=getAfd'+'&kebun='+kbn+'&afdeling='+afd;
        param+='&mandor='+kary;
    }
    
    fileTarget='kebun_slave_taksasi';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                     dr=con.responseText.split("###");
                     document.getElementById('afdeling').innerHTML=dr[0];
                     document.getElementById('blok').innerHTML=dr[1];
                     if(kary!=0){
                         document.getElementById('blok').value=kary;
                     }
                     getSPH();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
     post_response_text(fileTarget+'.php', param, respon);
}

function getSPH(){
    var disablekah = document.getElementById('hkdigunakan').disabled;
        blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
        tanggal=document.getElementById('tanggal').value;
        param='proses=getSPH'+'&blok='+blok+'&tanggal='+tanggal;
    
    fileTarget='kebun_slave_taksasi';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                     dr=con.responseText.split("###");
                     document.getElementById('sph').value=dr[0];
                     document.getElementById('jjgoutput').value=dr[1];
                     document.getElementById('bjr').value=dr[2];
                     getPokok();
                } 
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if((blok!='')&&(tanggal!='')){
     if(disablekah==true){
     post_response_text(fileTarget+'.php', param, respon);
     }   
    }
}

function getPokok(){
        hasisa=document.getElementById('hasisa').value;
        haesok=document.getElementById('haesok').value;
        sph=document.getElementById('sph').value;
        jmlhpokok=sph*(+hasisa + +haesok);
        jmlhpokok=jmlhpokok.toFixed(0);
                     document.getElementById('jmlhpokok').value=jmlhpokok;
                     getMasak();
}

function getMasak(){
        persenbuahmatang=document.getElementById('persenbuahmatang').value;
        jmlhpokok=document.getElementById('jmlhpokok').value;
        jjgmasak= +persenbuahmatang / 100 * +jmlhpokok;
        jjgmasak=jjgmasak.toFixed(0);
                     document.getElementById('jjgmasak').value=jjgmasak;
                     getHK();
}

function getHK(){
        jjgmasak=document.getElementById('jjgmasak').value;
        jjgoutput=document.getElementById('jjgoutput').value;
        hasisa=document.getElementById('hasisa').value;
        haesok=document.getElementById('haesok').value;
        luas=(+hasisa + +haesok);
        
        hkdigunakan=Math.ceil(jjgmasak/jjgoutput);
//        hkdigunakan=hkdigunakan.toFixed(0);
                     document.getElementById('hkdigunakan').value=hkdigunakan;
                     
        if(luas/hkdigunakan <= 6){
            bisapanen=hkdigunakan;
        }else{
            bisapanen=Math.ceil(luas/6);
        }
        if(isFinite(bisapanen)==false)bisapanen=0;
                     document.getElementById('bisapanen').value=bisapanen;
}