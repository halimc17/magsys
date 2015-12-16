<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2>
    function simpanJabatan()
    {
        kode=document.getElementById('kode').value;
        potongan=document.getElementById('potongan').value;
        if(kode=='' || potongan=='')
            alert('Fields are oblogatory');
        else
           {
               param='kode='+kode+'&potongan='+potongan;
                tujuan = 'pabrik_slave_save_pot_sortasi.php';
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

function cancelJabatan()
{
         document.getElementById('kode').value='';
         document.getElementById('potongan').value='';
         document.getElementById('kode').disabled=false;
}

function fillField(x,y)
{
         document.getElementById('kode').value=x;
         document.getElementById('potongan').value=y;   
         document.getElementById('kode').disabled=true;
}
</script>
<?
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['kodefraksi']);

echo"<fieldset style='width:500px;'><table>
     <tr><td>".$_SESSION['lang']['kodeabs']."</td><td><input type=text id=kode size=4 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
	 <tr><td>".$_SESSION['lang']['potongan']."</td><td><input type=text id=potongan size=4 onkeypress=\"return angka_doang(event);\" class=myinputtext></td></tr>
 	 </table>
	 <button class=mybutton onclick=simpanJabatan()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJabatan()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
echo open_theme($_SESSION['lang']['availfunct']);
echo "<div>";
	$str1="select a.*,b.keterangan, b.keterangan1 from ".$dbname.".pabrik_5pot_fraksi a LEFT JOIN
		".$dbname.".pabrik_5fraksi2 b ON a.kodefraksi = b.kode
		order by a.kodefraksi";
	$res1=mysql_query($str1);
       // echo mysql_error($conn);
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
			<td style='width:150px;'>".$_SESSION['lang']['kodeabs']."</td>
			<td>".$_SESSION['lang']['nama']."</td>
			<td>".$_SESSION['lang']['nama']." (EN)</td>
			<td>".$_SESSION['lang']['potongan']."</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>";
	while($bar1=mysql_fetch_object($res1))
	{
		echo"<tr class=rowcontent><td align=center>".$bar1->kodefraksi."</td>
			<td>".$bar1->keterangan."</td>
			<td>".$bar1->keterangan1."</td>
			<td align=right>".$bar1->potongan."</td>
			<td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodefraksi."','".$bar1->potongan."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>