//JS 



function simpan()
{
    pt=trim(document.getElementById('pt').value);
    noakun=trim(document.getElementById('noakun').value);
    bank=trim(document.getElementById('bank').value);
    rek=trim(document.getElementById('rek').value);
    method=document.getElementById('method').value;

    if(pt=='' || noakun=='' || bank=='')
    {
        alert('Please complete the form');return;
    }

    param='pt='+pt+'&noakun='+noakun+'&bank='+bank+'&rek='+rek+'&method='+method;
    tujuan='keu_slave_5akunbankv2.php';
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
                    hapus();							
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

function hapus()
{
	document.getElementById('pt').value='';
	document.getElementById('noakun').value='';
	document.getElementById('bank').value='';
        document.getElementById('rek').value='';
	document.getElementById('method').value='insert';
	document.getElementById('pt').disabled=false;
	//method=document.getElementById('method').value;
}

function loadData () 
{
    param='method=loadData';
    tujuan='keu_slave_5akunbankv2.php';
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
                   // alert(con.responseText);
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

function fillField(pt,noakun,bank,rek)
{
    document.getElementById('pt').value=pt;
    document.getElementById('noakun').value=noakun;
    document.getElementById('bank').value=bank;
    document.getElementById('rek').value=rek;
    document.getElementById('pt').disabled=true;
    document.getElementById('method').value='update';
}

function del(pt,noakun,bank,rek)
{
    param='method=delete'+'&pt='+pt+'&noakun='+noakun+'&bank='+bank+'&rek='+rek;
    //alert(param);
    tujuan='keu_slave_5akunbankv2.php';
    //if(confirm("Delete data for "+kdorg+" period "+ptsupplier+" ?"))
    if(confirm("Delete data?"))

    {
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
                    else 
                    {
                        document.getElementById('container').innerHTML=con.responseText;
                        loadData();	
                    }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
    }
	//alert("Data telah terhapus !!!");	
}
