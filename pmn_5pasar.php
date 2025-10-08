<?php //@Copy nangkoelframework
//ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>




<script language=javascript src=js/zTools.js></script>
<script>

function simpan()
{
    pasar=document.getElementById('pasar').value;
    if(pasar=='')
    {
            alert('Field Empty');
            return;
    }

	param='method=insert'+'&pasar='+pasar;
    tujuan='pmn_slave_5pasar.php';
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
							 //document.location.reload();
                                                         document.getElementById('pasar').value='';
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

function loadData () 
{
	param='method=loadData';
	tujuan='pmn_slave_5pasar.php';
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

   

function del(pasar)
{
	param='method=delete'+'&pasar='+pasar;
	tujuan='pmn_slave_5pasar.php';
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

</script>


<?php



OPEN_BOX();
//print_r($_SESSION['empl']['regional']);
echo"<fieldset style='float:left;'>";
    echo"<legend>Daftar Pasar</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                 
                <tr>
                    <td>".$_SESSION['lang']['pasar']."</td> 
                    <td>:</td>
                    <td><input type=text  id=pasar nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:100px;\"></td>
                </tr>
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>Simpan</button>
                              
                        </td>
                </tr>

        </table></fieldset>";
       

CLOSE_BOX();//                        <input type=hidden id=method value='insert'>
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>