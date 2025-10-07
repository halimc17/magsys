//javascript document

function getAfd()
{

    // var select = document.getElementById("divisi0");   
    // var options = ["satu", "dua", "tiga", "empat", "lima"];

    // for(var i = 0; i < options.length; i++) {
    //     var opt = options[i];
    //     var el = document.createElement("option");
    //     el.textContent = opt;
    //     el.value = opt;
    //     select.appendChild(el);
    // }

    kdOrg=document.getElementById('unit0').options[document.getElementById('unit0').selectedIndex].value;
	param='&kdOrg='+kdOrg;
	tujuan='agro_slave_2costelement0';
    
    
    
    //alert(tujuan+'.php?proses=getAfdAll'+param);
    post_response_text(tujuan+'.php?proses=getAfdAll&kdOrg='+kdOrg,param, respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    // Success Response
                    if(kdOrg=='')
                    {
                        document.getElementById('divisi0').innerHTML='';
                    }
                    else
                    {
                        document.getElementById('divisi0').innerHTML=con.responseText;
                    }
                    
                                                
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
}