// JavaScript Document

function savehk(fileTarget,passParam) 
{
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    param+="&method=insert";
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } 
                else 
                {
                    loadData();
                    cancelIsi();
                    alert('Done.');
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);
}

function deletehk(tanggal)
{
    fileTarget='sdm_slave_5harilibur';
    param='tanggal='+tanggal+'&method=delete';
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } 
                else 
                {
                    loadData();
                    cancelIsi();
                }
            } 
            else 
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    if(confirm('Hapus data tanggal '+tanggal+'?'))post_response_text(fileTarget+'.php', param, respon);
}

function loadData()
{
    param='method=loadData';
    tujuan='sdm_slave_5harilibur';
    post_response_text(tujuan+'.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function cancelIsi()
{
    document.getElementById('tanggal').value='';
    document.getElementById('catatan').value='';
}