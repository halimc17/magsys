<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$str="delete from ".$dbname.".pabrik_5pot_fraksi where kodefraksi='".$_POST['kode']."'";
mysql_query($str);
$str="insert into ".$dbname.".pabrik_5pot_fraksi (kodefraksi,potongan)
      values('".$_POST['kode']."',".$_POST['potongan'].");";
if(mysql_query($str))
{
	$str1="select a.*,b.keterangan, b.keterangan1 from ".$dbname.".pabrik_5pot_fraksi a LEFT JOIN
		".$dbname.".pabrik_5fraksi2 b ON a.kodefraksi = b.kode
		order by a.kodefraksi";
	$res1=mysql_query($str1);
	while($bar1=mysql_fetch_object($res1)) {
        echo"<tr class=rowcontent><td align=center>".$bar1->kodefraksi."</td>
			<td>".$bar1->keterangan."</td>
			<td>".$bar1->keterangan1."</td>
			<td align=right>".$bar1->potongan."</td>
			<td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodefraksi."','".$bar1->potongan."');\"></td></tr>";
	}
}
 else {
    exit("Error:".mysql_error($conn));
}

?>