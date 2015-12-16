function getkodeunit(karyawanid){
    param='method=getkodeunit'+'&karyawanid='+karyawanid;
    tujuan='sdm_slave_pesangon.php';
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
                    pisah=con.responseText.split('###');
                    document.getElementById('kodeunit').value=pisah[0];
                    document.getElementById('tglmasuk').value=pisah[1];
                    document.getElementById('gajipokok').value=pisah[2];
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
     }    
}

function getmasakerja(tglberhenti){
    tglmasuk=document.getElementById('tglmasuk').value;
    tglberhenti=document.getElementById('tglberhenti').value;
    param='method=getmasakerja'+'&tglberhenti='+tglberhenti+'&tglmasuk='+tglmasuk;
    tujuan='sdm_slave_pesangon.php';
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
                    pisah=con.responseText.split('###');
                    document.getElementById('masakerjatahun').value=pisah[0];
                    document.getElementById('masakerjabulan').value=pisah[1];
                    document.getElementById('masakerjahari').value=pisah[2];
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
     }    
}

function getdetail(nosurat,tanggal,karyawanid,kodeunit,tglberhenti,masakerjatahun,masakerjabulan,masakerjahari,gajipokok,tunjanganjabatan,jenissks,p1562,jml_pesangon,p1563,tot_penghargaan,p1564a,jmlh_p1564a,p1564b,jmlh_p1564b,p1564c,jmlh_p1564c,tot_sblm_pajak,tot_pesangon) {
    if(jenissks!=''){
            jenissk=jenissks.replace(/%20/g, " ");
    }
    if(nosurat==''){
        karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
        gajipokok=document.getElementById('gajipokok').value;
        gajipokok=remove_comma(document.getElementById('gajipokok'));
        tunjanganjabatan=document.getElementById('tunjanganjabatan').value;  
        tunjanganjabatan=remove_comma(document.getElementById('tunjanganjabatan'));
        masakerjatahun=document.getElementById('masakerjatahun').value;
        jenissk=document.getElementById('jenissk').value;
        if(jenissk.replace(/%20/g, " ")=='Uang Pisah'){
            param='method=insert';
        }
        else{
            param='method=insert2';
        }
//        alert(param);
    }
    else{
        document.getElementById('gajipokok').value=gajipokok;
        document.getElementById('tunjanganjabatan').value=tunjanganjabatan;
        document.getElementById('karyawanid').value=karyawanid;
        document.getElementById('masakerjatahun').value=masakerjatahun;
        document.getElementById('jenissk').value=jenissk;
    }
    
    param='method=createTable'+'&jenissk='+jenissk+'&masakerjatahun='+masakerjatahun;
    param+='&gajipokok='+gajipokok+'&tunjanganjabatan='+tunjanganjabatan+'&karyawanid='+karyawanid;
//    alert(param);
    
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    var detailDiv = document.getElementById('detailTable');
                    detailDiv.innerHTML = con.responseText;
                    if(nosurat!=''){
                        edit(nosurat,tanggal,karyawanid,kodeunit,tglberhenti,masakerjatahun,masakerjabulan,masakerjahari,gajipokok,tunjanganjabatan,jenissks,p1562,jml_pesangon,p1563,tot_penghargaan,p1564a,jmlh_p1564a,p1564b,jmlh_p1564b,p1564c,jmlh_p1564c,tot_sblm_pajak,tot_pesangon);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('sdm_slave_pesangon.php', param, respon);
}

function calculateUangPisah()
{
    karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    gajipokok       =remove_comma(document.getElementById('gajipokok'));
    tunjanganjabatan=remove_comma(document.getElementById('tunjanganjabatan'));
    p1564a          =remove_comma(document.getElementById('p1564a'));
    p1564b          =remove_comma(document.getElementById('p1564b'));
    tot_uangpisah   =remove_comma(document.getElementById('tot_uangpisah'));
    
    gaji     =parseFloat(gajipokok)+parseFloat(tunjanganjabatan);    
    uangcuti=parseFloat(gaji)*parseFloat(p1564a);
    document.getElementById('jmlh_p1564a').value=uangcuti;
    change_number(document.getElementById('jmlh_p1564a'));
    
    ongkospulang=parseFloat(gaji)*parseFloat(p1564b);
    document.getElementById('jmlh_p1564b').value=ongkospulang;
    change_number(document.getElementById('jmlh_p1564b'));
    
    ttl=parseFloat(uangcuti)+parseFloat(ongkospulang)+parseFloat(tot_uangpisah); 
    document.getElementById('tot_sblm_pajak').value=ttl;
    change_number(document.getElementById('tot_sblm_pajak'));
    
    totsblmpajak=ttl;
    param='&karyawanid='+karyawanid+'&totsblmpajak='+totsblmpajak;
//    alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    pisah=con.responseText.split('###');
                    document.getElementById('pajakprogresif1').value=pisah[0];
                    document.getElementById('pajakprogresif2').value=pisah[1];
                    document.getElementById('pajakprogresif3').value=pisah[2];
                    document.getElementById('tot_pajak').value=pisah[3];
                    document.getElementById('tot_pesangon').value=pisah[4];
                }
             } else {
                busy_off();
                error_catch(con.status);
             }
        }
    }
    post_response_text('sdm_slave_pesangon_progresif.php', param, respon);     
}

function calculatePesangon()
{
    karyawanid       =document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    gajipokok        =remove_comma(document.getElementById('gajipokok'));
    tunjanganjabatan =remove_comma(document.getElementById('tunjanganjabatan'));
	p1562           =remove_comma(document.getElementById('p1562'));
	p1563           =remove_comma(document.getElementById('p1563'));
    p1564a           =remove_comma(document.getElementById('p1564a'));
    p1564b           =remove_comma(document.getElementById('p1564b'));
    p1564c           =remove_comma(document.getElementById('p1564c'));
    
    jml_pesangon     =remove_comma(document.getElementById('jml_pesangon'));
    tot_penghargaan  =remove_comma(document.getElementById('tot_penghargaan'));
    gaji             =parseFloat(gajipokok)+parseFloat(tunjanganjabatan);
//    alert(tot_penghargaan);
    jmlh_p1564a =parseFloat(gaji)*parseFloat(p1564a);
    document.getElementById('jmlh_p1564a').value=jmlh_p1564a;
    change_number(document.getElementById('jmlh_p1564a'));
    
    jmlh_p1564b=parseFloat(gaji)*parseFloat(p1564b);
    document.getElementById('jmlh_p1564b').value=jmlh_p1564b;
    change_number(document.getElementById('jmlh_p1564b'));
    
    jmlh_p1564c=(parseFloat(jmlh_p1564a)+parseFloat(jmlh_p1564b))*parseFloat(p1564c);
    document.getElementById('jmlh_p1564c').value=jmlh_p1564c;
    change_number(document.getElementById('jmlh_p1564c'));
    
    ttl=parseFloat(jml_pesangon)+parseFloat(tot_penghargaan)+parseFloat(jmlh_p1564a)+parseFloat(jmlh_p1564b)+parseFloat(jmlh_p1564c); 
    document.getElementById('tot_sblm_pajak').value=ttl;
    change_number(document.getElementById('tot_sblm_pajak'));
    
    totsblmpajak=ttl;
    param='&karyawanid='+karyawanid+'&totsblmpajak='+totsblmpajak;
//    alert(jmlh_p1564a);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    pisah=con.responseText.split('###');
                    document.getElementById('pajakprogresif1_').value=pisah[0];
                    document.getElementById('pajakprogresif2_').value=pisah[1];
                    document.getElementById('pajakprogresif3_').value=pisah[2];
                    document.getElementById('tot_pajak_').value=pisah[3];
                    document.getElementById('tot_pesangon').value=pisah[4];
//                    alert(pisah[1]);
                }
             } else {
                busy_off();
                error_catch(con.status);
             }
        }
    }
    post_response_text('sdm_slave_pesangon_progresif.php', param, respon);     
}

function savePesangon()
{
    nosurat             =document.getElementById('nosurat').value;
    tanggal             =document.getElementById('tanggal').value;
    karyawanid          =document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    kodeunit            =document.getElementById('kodeunit').value;
    tglberhenti         =document.getElementById('tglberhenti').value;
    masakerjatahun      =document.getElementById('masakerjatahun').value;
    masakerjabulan      =document.getElementById('masakerjabulan').value;
    masakerjahari       =document.getElementById('masakerjahari').value;
    gajipokok           =document.getElementById('gajipokok').value;
    gajipokok           =remove_comma_var(gajipokok);
    tunjanganjabatan    =document.getElementById('tunjanganjabatan').value;
    tunjanganjabatan    =remove_comma_var(tunjanganjabatan);
    jenissk             =document.getElementById('jenissk').options[document.getElementById('jenissk').selectedIndex].value;
    
    tot_uangpisah       =document.getElementById('tot_uangpisah').value;
    tot_uangpisah       =remove_comma_var(tot_uangpisah);
    p1564a              =document.getElementById('p1564a').value;
    p1564a              =remove_comma_var(p1564a);
    jmlh_p1564a         =document.getElementById('jmlh_p1564a').value;
    jmlh_p1564a         =remove_comma_var(jmlh_p1564a);
    p1564b              =document.getElementById('p1564b').value;
    p1564b              =remove_comma_var(p1564b);
    jmlh_p1564b         =document.getElementById('jmlh_p1564b').value; 
    jmlh_p1564b         =remove_comma_var(jmlh_p1564b);
    tot_sblm_pajak      =document.getElementById('tot_sblm_pajak').value;
    tot_sblm_pajak      =remove_comma_var(tot_sblm_pajak);
    pajakprogresif1     =document.getElementById('pajakprogresif1').value;
    pajakprogresif2     =document.getElementById('pajakprogresif2').value;
    pajakprogresif3     =document.getElementById('pajakprogresif3').value;
    tot_pajak           =document.getElementById('tot_pajak').value;
    tot_pesangon        =document.getElementById('tot_pesangon').value;
    tot_pesangon        =remove_comma_var(tot_pesangon);
    
    param='nosurat='+nosurat+'&tanggal='+tanggal+'&karyawanid='+karyawanid+'&kodeunit='+kodeunit+'&tglberhenti='+tglberhenti; 
    param+='&masakerjatahun='+masakerjatahun+'&masakerjabulan='+masakerjabulan+'&masakerjahari='+masakerjahari+'&gajipokok='+gajipokok+'&tunjanganjabatan='+tunjanganjabatan; 
    param+='&jenissk='+jenissk+'&tot_uangpisah='+tot_uangpisah+'&p1564a='+p1564a+'&jmlh_p1564a='+jmlh_p1564a+'&p1564b='+p1564b+'&jmlh_p1564b='+jmlh_p1564b; 
    param+='&tot_sblm_pajak='+tot_sblm_pajak+'&pajakprogresif1='+pajakprogresif1+'&pajakprogresif2='+pajakprogresif2+'&pajakprogresif3='+pajakprogresif3+'&tot_pajak='+tot_pajak+'&tot_pesangon='+tot_pesangon+'&method=insert'; 
  
    tujuan='sdm_slave_pesangon.php';
    
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
                    alert('Done.');
                    loadData();
                    document.getElementById('nosurat').value='';
                    document.getElementById('nosurat').disabled=false
                    document.getElementById('tanggal').value='';
                    document.getElementById('karyawanid').value='';
                    document.getElementById('kodeunit').value='';
                    document.getElementById('tglberhenti').value='';
                    document.getElementById('masakerjatahun').value='';
                    document.getElementById('masakerjabulan').value='';
                    document.getElementById('masakerjahari').value='';
                    document.getElementById('gajipokok').value='';
                    document.getElementById('tunjanganjabatan').value='';
                    document.getElementById('jenissk').value='';
                    document.getElementById('tot_uangpisah').value='';
                    document.getElementById('p1564a').value='';
                    document.getElementById('jmlh_p1564a').value='';
                    document.getElementById('p1564b').value='';
                    document.getElementById('jmlh_p1564b').value='';
                    document.getElementById('tot_sblm_pajak').value='';
                    document.getElementById('pajakprogresif1').value='';
                    document.getElementById('pajakprogresif2').value='';
                    document.getElementById('pajakprogresif3').value='';
                    document.getElementById('tot_pajak').value='';
                    document.getElementById('tot_pesangon').value='';
                    document.getElementById('banyaknya').value='';
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }     	
}

function savePesangon2()
{
    nosurat             =document.getElementById('nosurat').value;
    tanggal             =document.getElementById('tanggal').value;
    karyawanid          =document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    kodeunit            =document.getElementById('kodeunit').value;
    tglberhenti         =document.getElementById('tglberhenti').value;
    masakerjatahun      =document.getElementById('masakerjatahun').value;
    masakerjabulan      =document.getElementById('masakerjabulan').value;
    masakerjahari       =document.getElementById('masakerjahari').value;
    gajipokok           =document.getElementById('gajipokok').value;
	gajipokok    		=remove_comma_var(gajipokok);
    tunjanganjabatan    =document.getElementById('tunjanganjabatan').value;
    tunjanganjabatan    =remove_comma_var(tunjanganjabatan);
    jenissk             =document.getElementById('jenissk').options[document.getElementById('jenissk').selectedIndex].value;
        
    p1562               =document.getElementById('p1562').value;
    jml_pesangon        =document.getElementById('jml_pesangon').value;
    jml_pesangon        =remove_comma_var(jml_pesangon);
    p1563               =document.getElementById('p1563').value;
    tot_penghargaan     =document.getElementById('tot_penghargaan').value; 
    tot_penghargaan     =remove_comma_var(tot_penghargaan);
    p1564a             =document.getElementById('p1564a').value;
    jmlh_p1564a       =document.getElementById('jmlh_p1564a').value;
    jmlh_p1564a       =remove_comma_var(jmlh_p1564a);
    p1564b             =document.getElementById('p1564b').value;
    jmlh_p1564b      =document.getElementById('jmlh_p1564b').value;
    jmlh_p1564b     =remove_comma_var(jmlh_p1564b);
    p1564c             =document.getElementById('p1564c').value;
    jmlh_p1564c      =document.getElementById('jmlh_p1564c').value;
    jmlh_p1564c      =remove_comma_var(jmlh_p1564c);
    tot_sblm_pajak     =document.getElementById('tot_sblm_pajak').value;
    tot_sblm_pajak     =remove_comma_var(tot_sblm_pajak);
    pajakprogresif1    =document.getElementById('pajakprogresif1_').value;
    pajakprogresif2    =document.getElementById('pajakprogresif2_').value;
    pajakprogresif3    =document.getElementById('pajakprogresif3_').value;
    tot_pajak_          =document.getElementById('tot_pajak_').value;
    tot_pajak_          =remove_comma_var(tot_pajak_);
    tot_pesangon       =document.getElementById('tot_pesangon').value;
    tot_pesangon       =remove_comma_var(tot_pesangon);
    
    param='nosurat='+nosurat+'&tanggal='+tanggal+'&karyawanid='+karyawanid+'&kodeunit='+kodeunit+'&tglberhenti='+tglberhenti; 
    param+='&masakerjatahun='+masakerjatahun+'&masakerjabulan='+masakerjabulan+'&masakerjahari='+masakerjahari+'&gajipokok='+gajipokok+'&tunjanganjabatan='+tunjanganjabatan; 
    param+='&jenissk='+jenissk+'&p1562='+p1562+'&jml_pesangon='+jml_pesangon+'&p1563='+p1563+'&tot_penghargaan='+tot_penghargaan+'&p1564a='+p1564a; 
    param+='&jmlh_p1564a='+jmlh_p1564a+'&p1564b='+p1564b+'&jmlh_p1564b='+jmlh_p1564b+'&p1564c='+p1564c+'&jmlh_p1564c='+jmlh_p1564c+'&tot_sblm_pajak='+tot_sblm_pajak; 
    param+='&pajakprogresif1_='+pajakprogresif1_+'&pajakprogresif2_='+pajakprogresif2_+'&pajakprogresif3_='+pajakprogresif3_+'&tot_pajak_='+tot_pajak_+'&tot_pesangon='+tot_pesangon+'&method=insert2'; 
//    alert(param);
    tujuan='sdm_slave_pesangon.php';
    
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
                    alert('Done.');
                    loadData();
                    document.getElementById('nosurat').value='';
                    document.getElementById('nosurat').disabled=false
                    document.getElementById('tanggal').value='';
                    document.getElementById('karyawanid').value='';
                    document.getElementById('kodeunit').value='';
                    document.getElementById('tglberhenti').value='';
                    document.getElementById('masakerjatahun').value='';
                    document.getElementById('masakerjabulan').value='';
                    document.getElementById('masakerjahari').value='';
                    document.getElementById('gajipokok').value='';
                    document.getElementById('tunjanganjabatan').value='';
                    document.getElementById('jenissk').value='';
                    document.getElementById('p1562').value='';
                    document.getElementById('jml_pesangon').value='';
                    document.getElementById('p1563').value='';
                    document.getElementById('tot_penghargaan').value='';
                    document.getElementById('p1564a').value='';
                    document.getElementById('jmlh_p1564a').value='';
                    document.getElementById('p1564b').value='';
                    document.getElementById('jmlh_p1564b').value='';
                    document.getElementById('p1564c').value='';
                    document.getElementById('jmlh_p1564c').value='';
                    document.getElementById('tot_sblm_pajak').value='';
                    document.getElementById('pajakprogresif1_').value='';
                    document.getElementById('pajakprogresif2_').value='';
                    document.getElementById('pajakprogresif3_').value='';
                    document.getElementById('tot_pajak_').value='';
                    document.getElementById('tot_pesangon').value='';
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }     	
}

function cancelIsi(passParam){
    document.getElementById('nosurat').value='';
    document.getElementById('nosurat').disabled=false
    document.getElementById('tanggal').value='';
    document.getElementById('karyawanid').value='';
    document.getElementById('kodeunit').value='';
    document.getElementById('tglberhenti').value='';
    document.getElementById('masakerjatahun').value='';
    document.getElementById('masakerjabulan').value='';
    document.getElementById('masakerjahari').value='';
    document.getElementById('gajipokok').value='';
    document.getElementById('tunjanganjabatan').value='';
    document.getElementById('jenissk').value='';
    document.getElementById('tot_uangpisah').value='';
    document.getElementById('p1564a').value='';
    document.getElementById('jmlh_p1564a').value='';
    document.getElementById('p1564b').value='';
    document.getElementById('jmlh_p1564b').value='';
    document.getElementById('tot_sblm_pajak').value='';
    document.getElementById('pajakprogresif1').value='';
    document.getElementById('pajakprogresif2').value='';
    document.getElementById('pajakprogresif3').value='';
    document.getElementById('tot_pajak').value='';
    document.getElementById('tot_pesangon').value='';
    document.getElementById('banyaknya').value='';
}
function cancelIsi2(passParam){
    document.getElementById('nosurat').value='';
    document.getElementById('nosurat').disabled=false
    document.getElementById('tanggal').value='';
    document.getElementById('karyawanid').value='';
    document.getElementById('kodeunit').value='';
    document.getElementById('tglberhenti').value='';
    document.getElementById('masakerjatahun').value='';
    document.getElementById('masakerjabulan').value='';
    document.getElementById('masakerjahari').value='';
    document.getElementById('gajipokok').value='';
    document.getElementById('tunjanganjabatan').value='';
    document.getElementById('jenissk').value='';
    document.getElementById('p1562').value='';
    document.getElementById('jml_pesangon').value='';
    document.getElementById('p1563').value='';
    document.getElementById('tot_penghargaan').value='';
    document.getElementById('p1564a').value='';
    document.getElementById('jmlh_p1564a').value='';
    document.getElementById('p1564b').value='';
    document.getElementById('jmlh_p1564b').value='';
    document.getElementById('p1564c').value='';
    document.getElementById('jmlh_p1564c').value='';
    document.getElementById('tot_sblm_pajak').value='';
    document.getElementById('pajakprogresif1_').value='';
    document.getElementById('pajakprogresif2_').value='';
    document.getElementById('pajakprogresif3_').value='';
    document.getElementById('tot_pajak_').value='';
    document.getElementById('tot_pesangon').value='';
}

function loadData(){
   
    param='method=loadData'; 
    tujuan='sdm_slave_pesangon.php';
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('isi').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
   post_response_text(tujuan, param, respon);
}

function gedit(nosurat,tanggal,karyawanid,kodeunit,tglberhenti,masakerjatahun,masakerjabulan,masakerjahari,gajipokok,tunjanganjabatan,jenissk,p1562,jml_pesangon,p1563,tot_penghargaan,p1564a,jmlh_p1564a,p1564b,jmlh_p1564b,p1564c,jmlh_p1564c,tot_sblm_pajak,tot_pesangon)
{
    getdetail(nosurat,tanggal,karyawanid,kodeunit,tglberhenti,masakerjatahun,masakerjabulan,masakerjahari,gajipokok,tunjanganjabatan,jenissk,p1562,jml_pesangon,p1563,tot_penghargaan,p1564a,jmlh_p1564a,p1564b,jmlh_p1564b,p1564c,jmlh_p1564c,tot_sblm_pajak,tot_pesangon);
}

function edit(nosurat,tanggal,karyawanid,kodeunit,tglberhenti,masakerjatahun,masakerjabulan,masakerjahari,gajipokok,tunjanganjabatan,jenissk,p1562,jml_pesangon,p1563,tot_penghargaan,p1564a,jmlh_p1564a,p1564b,jmlh_p1564b,p1564c,jmlh_p1564c,tot_sblm_pajak,tot_pesangon)
{
    document.getElementById('nosurat').value=nosurat;
    document.getElementById('nosurat').disabled=true
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('karyawanid').value=karyawanid;
    document.getElementById('kodeunit').value=kodeunit;
    document.getElementById('tglberhenti').value=tglberhenti;
    document.getElementById('masakerjatahun').value=masakerjatahun;
    document.getElementById('masakerjabulan').value=masakerjabulan;
    document.getElementById('masakerjahari').value=masakerjahari;
    document.getElementById('gajipokok').value=gajipokok;
    document.getElementById('tunjanganjabatan').value=tunjanganjabatan;
    document.getElementById('jenissk').value=jenissk.replace(/%20/g, " ");
    
    document.getElementById('p1564a').value=p1564a;
    document.getElementById('jmlh_p1564a').value=jmlh_p1564a;
    document.getElementById('p1564b').value=p1564b;
    document.getElementById('jmlh_p1564b').value=jmlh_p1564b;
    if(jenissk.replace(/%20/g, " ")!='Uang Pisah'){
        document.getElementById('p1562').value=p1562;
        document.getElementById('jml_pesangon').value=jml_pesangon;
        document.getElementById('p1563').value=p1563;
        document.getElementById('tot_penghargaan').value=tot_penghargaan;
        document.getElementById('p1564c').value=p1564c;
        document.getElementById('jmlh_p1564c').value=jmlh_p1564c;
    }
    
    document.getElementById('tot_sblm_pajak').value=tot_sblm_pajak;
    document.getElementById('tot_pesangon').value=tot_pesangon;
    document.getElementById('method').value="update";
    
    
    param='nosurat='+nosurat+'&tanggal='+tanggal+'&karyawanid='+karyawanid+'&kodeunit='+kodeunit+'&tglberhenti='+tglberhenti; 
    param+='&masakerjatahun='+masakerjatahun+'&masakerjabulan='+masakerjabulan+'&masakerjahari='+masakerjahari+'&gajipokok='+gajipokok+'&tunjanganjabatan='+tunjanganjabatan+'&jenissk='+jenissk.replace(/%20/g, " "); 
    param+='&p1562='+p1562+'&jml_pesangon='+jml_pesangon+'&p1563='+p1563+'&tot_penghargaan='+tot_penghargaan+'&p1564a='+p1564a+'&jmlh_p1564a='+jmlh_p1564a+'&p1564b='+p1564b+'&jmlh_p1564b='+jmlh_p1564b+'&p1564c='+p1564c+'&jmlh_p1564c='+jmlh_p1564c; 
    param+='&tot_sblm_pajak='+tot_sblm_pajak+'&tot_pesangon='+tot_pesangon+'&method=update'; 
//    alert(param);
    tujuan='sdm_slave_pesangon.php';
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
                        if(jenissk.replace(/%20/g, " ")=='Uang Pisah'){
                            calculateUangPisah();
                        }
                        else{
                            calculatePesangon();
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

function del(nosurat,karyawanid,jenispesangon)
{
    param='nosurat='+nosurat+'&karyawanid='+karyawanid+'&jenispesangon='+jenispesangon+'&method=deletedata';
    tujuan='sdm_slave_pesangon.php';
    if(confirm("Are You Sure Want Delete Data?"))
        post_response_text(tujuan, param, respog);
				
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
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