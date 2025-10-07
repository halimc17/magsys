/* tutupBuku
 * Fungsi untuk melakukan proses tutup buku bulanan
 */
 function tutupBuku2(){
    var param = "kodeorg="+getValue('kodeorg')+"&periode="+getValue('periode2');
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    // alert('Proses Tutup Buku berhasil');
                    // logout();
                    isiDt=con.responseText.split("####");
                    totalRow=parseInt(isiDt[0])+1;
                    tutupBuku(totalRow,isiDt[1],1,isiDt[2]);//maxRow,kdorg,current,periode
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if(confirm('Close this period for '+getValue('kodeorg')+
        '\n are you sure?')) {
        post_response_text('keu_slave_3tutupbulanAudit.php?proses=getPeriode', param, respon);
    }
 }
 
function tutupBuku(maxRow,kdorg,currentRw,periode) {
    dtprd=periode;
    if(currentRw!=1){
        var dtprd="";
        nourutdt+=1;
        isiPrd=periode.split("-");
        if(currentRw<10){
            dtprd=(parseInt(isiPrd[0])+1)+"-"+"0"+nourutdt;
        }else{
            dtprd=(parseInt(isiPrd[0])+1)+"-"+nourutdt;
        }
    }else{
        nourutdt=0;
    }
    var param = "kodeorg="+kdorg+"&periode="+dtprd;
    post_response_text('keu_slave_3tutupbulanAudit.php?proses=tutupBuku', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    currentRw+=1;
                    if(maxRow>currentRw){
                       tutupBuku(maxRow,kdorg,currentRw,periode);
                    }else{
                       alert('Proses Tutup Buku berhasil');
                       logout();
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    
}