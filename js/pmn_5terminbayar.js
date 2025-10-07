//JS 



function simpan()
{
    kode=trim(document.getElementById('kode').value);
    satu=trim(document.getElementById('satu').value);
    dua=trim(document.getElementById('dua').value);
    method=document.getElementById('method').value;

    if(kode=='' || satu=='' || dua=='')
    {
        alert('Please complete the form');return;
    }

    param='kode='+kode+'&satu='+satu+'&dua='+dua+'&method='+method;
    tujuan='pmn_slave_5terminbayar.php';
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
	document.getElementById('kode').value='';
	document.getElementById('satu').value='';
	document.getElementById('dua').value='';	
	document.getElementById('method').value='insert';
	document.getElementById('kode').disabled=false;
	//method=document.getElementById('method').value;
}

function loadData () 
{
    param='method=loadData';
    tujuan='pmn_slave_5terminbayar.php';
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

function fillField(kode,satu,dua)
{
    document.getElementById('kode').value=kode;
    document.getElementById('satu').value=satu;
    document.getElementById('dua').value=dua;
    document.getElementById('kode').disabled=true;
    document.getElementById('method').value='update';
}

function del(kode)
{
    param='method=delete'+'&kode='+kode;
    //alert(param);
    tujuan='pmn_slave_5terminbayar.php';
    //if(confirm("Delete data for "+kdorg+" period "+kodesupplier+" ?"))
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
