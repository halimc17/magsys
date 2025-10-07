//JS 

function cariBast(num)
{
	cariPt=document.getElementById('cariPt').value;
	cariStatus=document.getElementById('cariStatus').value;
	
    param='method=loadData&cariPt='+cariPt+'&cariStatus='+cariStatus;
    param+='&page='+num;
    tujuan = 'keu_slave_faktur.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert('ERROR TRANSACTION,\n' + con.responseText);
                            }
                            else {
                                    //displayList();

                                    document.getElementById('container').innerHTML=con.responseText;
                                    //loadData();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }	
}

function simpan()
{
   id=document.getElementById('id').value;
    pt=document.getElementById('pt').value;
    faktur=document.getElementById('faktur').value;
    method=document.getElementById('method').value;

    if(pt=='' || faktur=='')
    {
            alert('Field Was Empty');
            return;
    }

    param='id='+id+'&pt='+pt+'&faktur='+faktur+'&method='+method;
    tujuan='keu_slave_faktur.php';
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
							alert("Data berhasil disimpan.");
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
					


function cancel()
{
   
    document.getElementById('id').value='';
    document.getElementById('pt').selectedIndex='0';
    document.getElementById('faktur').value='';
    document.getElementById('method').value='insert';
}




function loadData () 
{
	param='method=loadData';
	tujuan='keu_slave_faktur.php';
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

function edit(id,pt,faktur)
{
    document.getElementById('id').value=id;
    document.getElementById('pt').value=pt;
     document.getElementById('faktur').value=faktur;
    document.getElementById('method').value='update';
}



function del(id)
{
	param='method=delete'+'&id='+id;
	tujuan='keu_slave_faktur.php';
	if(confirm("Anda yakin menghapus item ini?"))
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
					else 
					{
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




