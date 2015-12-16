/**
 * @author repindra.ginting
 */
function ambilBuktiKas(tanggal)
{
        document.getElementById('space').innerHTML='';
        param='tanggal='+tanggal+'&aksi=ambilnokas';        
        tujuan = 'keu_slave_alokasiIDC.php';
        if(tanggal!='')	
        post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    document.getElementById('nokas').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }             
}



function getAfd()
{
    
    alokasi=document.getElementById('alokasi').value;
    param='alokasi='+alokasi+'&aksi=getAfd';    
    
    //alert(param);
    tujuan='keu_slave_alokasiIDC.php';
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
                        document.getElementById('afdeling').innerHTML=con.responseText; 
                        ambilBlok();
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                    
                }
      }	
     } 
}




function ambilAlokasi()
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    document.getElementById('space').innerHTML='';
    tujuan = 'keu_slave_alokasiIDC.php';    
    val=val.split("#");
    kodeorg=val[2];
    jumlah=val[1];
    nokas=val[0];
    if(val!='')
    param='kodeorg='+kodeorg+'&aksi=ambilAlokasi';
    post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    document.getElementById('alokasi').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }       
}
function ambilBlok(kebun)
{
    val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
    alokasi=document.getElementById('alokasi').options[document.getElementById('alokasi').selectedIndex].value;
    tanggal=document.getElementById('tanggal').value;
    afdeling=document.getElementById('afdeling');
   
//    if(document.getElementById('tm').checked==true)tm='1'; else tm='0';
    tujuan = 'keu_slave_alokasiIDC.php';    
    val=val.split("#");
    jumlah=val[1];    
//    param='kodeorg='+alokasi+'&jumlah='+jumlah+'&tanggal='+tanggal+'&aksi=ambilBlok'+'&tm='+tm;
    param='kodeorg='+alokasi+'&jumlah='+jumlah+'&tanggal='+tanggal+'&aksi=ambilBlok';
    
    if(afdeling) 
    {
        param+='&afdeling='+afdeling.value;
    }
    
    
    post_response_text(tujuan, param, respog);
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    document.getElementById('space').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }    
}

function saveDistribusi(kebun)
{
val=document.getElementById('nokas').options[document.getElementById('nokas').selectedIndex].value;
debet=document.getElementById('debet').options[document.getElementById('debet').selectedIndex].value;
kredit=document.getElementById('kredit').options[document.getElementById('kredit').selectedIndex].value;
tanggal=document.getElementById('tanggal').value;
afdeling=document.getElementById('afdeling');
tujuan = 'keu_slave_alokasiIDC.php';
    val=val.split("#");
    jumlah=val[1];    
    nokas=val[0];
    
    
    
    param='kodeorg='+kebun+'&jumlah='+jumlah+'&debet='+debet+'&kredit='+kredit+'&nokas='+nokas+'&tanggal='+tanggal+'&aksi=simpanIDC';
    if(afdeling) 
    {
        param+='&afdeling='+afdeling.value;
    }
    //alert(param);
    if(confirm('Anda yakin sudah benar..?')){
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
                                    document.getElementById('space').innerHTML='';
                                    alert('Done');
                                    window.location.reload();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }        
}
function hapusIni(nojurnal,tanggal,kodeorg)
{  
    param='nojurnal='+nojurnal+'&tanggal='+tanggal+'&kodeorg='+kodeorg+'&aksi=hapusJurnal';
    tujuan = 'keu_slave_alokasiIDC.php';
    if(confirm('Anda yakin mau menghapus: '+nojurnal+' ?'))
        post_response_text(tujuan, param, respog);
    
            function respog(){
                if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                   alert('Done');
                                    window.location.reload();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                }
            }     
}