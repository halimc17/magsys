<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$alamatalamat=checkPostGet('alamatalamat',''); 
$alamatkota=checkPostGet('alamatkota',''); 
$alamatkodepos=checkPostGet('alamatkodepos',''); 
$alamattelepon=checkPostGet('alamattelepon',''); 
$alamatemplasement=checkPostGet('alamatemplasement',''); 
$alamatstatus=checkPostGet('alamatstatus',0); 
$alamatprovinsi=checkPostGet('alamatprovinsi',''); 			
$karyawanid=checkPostGet('karyawanid','');
$nourut=checkPostGet('nomor','');

if($alamatalamat!='' or (isset($_POST['del']) and $_POST['del']=='true') or isset($_POST['queryonly']))
{
	if($nourut=='')
	   $nourut=0;
	   
	if(isset($_POST['del']) and $_POST['del']=='true')
	{
		$str="delete from ".$dbname.".sdm_karyawanalamat where nomor=".$nourut;
	}
	else if(isset($_POST['queryonly']))
	{
		$str="select 1=1";
	}
	else
	{
		$str="insert into ".$dbname.".sdm_karyawanalamat
		     (`karyawanid`,
				  `alamat`,
				  `kota`,
				  `kodepos`,
				  `telepon`,
				  `emplasemen`,
				  `aktif`,
				  `provinsi`
			  )
			  values(".$karyawanid.",
			  '".$alamatalamat."',
			  '".$alamatkota."',
			  '".$alamatkodepos."',
			  '".$alamattelepon."',
			  '".$alamatemplasement."',
			  ".$alamatstatus.",
			  '".$alamatprovinsi."'
			  )";
	}
	if(mysql_query($str))
	   {
		 //jika alamat adalah aktif, update table datakaryawan
		 if($alamatstatus==1)
		 {
		 	$strx="update ".$dbname.".datakaryawan set alamataktif='".$alamatalamat."',
			kota='".$alamatkota."', provinsi='".$alamatprovinsi."'
			where karyawanid=".$karyawanid;
			mysql_query($strx);
		 }
		 $str="select *,case aktif when 1 then 'Yes' when 0 then 'No' end as status from ".$dbname.".sdm_karyawanalamat where karyawanid=".$karyawanid." order by nomor desc";
		 $res=mysql_query($str);
		 $no=0;
		 while($bar=mysql_fetch_object($res))
		 {
		 $no+=1;	
		 echo"	  <tr class=rowcontent>
				  <td class=firsttd>".$no."</td>
				  <td>".$bar->alamat."</td>			  
				  <td>".$bar->kota."</td>
				  <td>".$bar->provinsi."</td>			  
				  <td>".$bar->kodepos."</td>			  
				  <td>".$bar->emplasemen."</td>
				  <td>".$bar->status."</td>
				  <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delAlamat('".$karyawanid."','".$bar->nomor."');\"></td>
				</tr>";	 	
		 }
	    }
		else
		{
			echo " Gagal:".addslashes(mysql_error($conn)).$str;
		}
}
else
{
	echo" Error: Incorrect Period";
}
?>
