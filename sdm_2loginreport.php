<?//@Copy nangkoelframework 
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/sdm_2loginreport.js"></script>
<?
include('master_mainMenu.php'); 
OPEN_BOX();

//=================ambil user active;  
$str="select a.namauser, a.karyawanid, a.status, b.namakaryawan, b.lokasitugas from ".$dbname.".user a
    left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
    where a.status='1' and b.tanggalkeluar = '0000-00-00' and b.tipekaryawan in ('0','7','8')
    order by a.namauser";
$res=mysql_query($str);
$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=mysql_fetch_object($res))
{
    $optuser.="<option value='".$bar->namauser."'>".$bar->namauser." [".$bar->namakaryawan."] ".$bar->lokasitugas."</option>";
}

?>
<fieldset style="float: left;"> 
<legend><b><?php echo "LOGIN REPORT" ?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['user']?></label></td>
    <td><select id=namauser style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optuser; ?></select></td>
</tr>
<tr height="20"><td colspan="2"><button class=mybutton onclick=getUser()><?php echo $_SESSION['lang']['preview'] ?></button></td></tr>
</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','Result:');
echo"<div id=container style='width:100%;height:359px;overflow:scroll;'>
</div>";
CLOSE_BOX();
close_body();

?>
