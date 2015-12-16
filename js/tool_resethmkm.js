function resetDt()
{
    var param = "kodevhc="+getValue('kodevhc')+"&kmhmakhir="+getValue('kmhmakhir');
	post_response_text('tool_slave_reset.php?proses=reset', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    alert("Proses Reset KM/HM untuk "+getValue('kodevhc')+" berhasil");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getKmHmAkhir() {
	var param = "kodevhc="+getValue('kodevhc');
	post_response_text('tool_slave_reset.php?proses=getKm', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    document.getElementById('kmhmakhir').value=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}