<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$jenistraining=checkPostGet('jenistraining','');
$judultraining=checkPostGet('judultraining','');
$penyelenggara=checkPostGet('penyelenggara','');
$tanggalmulai=checkPostGet('tanggalmulai','');
$tanggalselesai=checkPostGet('tanggalselesai','');
// $trainingblnmulai=checkPostGet('trainingblnmulai','');
// $trainingthnmulai=checkPostGet('trainingthnmulai','');
// $trainingblnselesai=checkPostGet('trainingblnselesai','');
// $trainingthnselesai=checkPostGet('trainingthnselesai','');
$sertifikat=checkPostGet('sertifikat','');
$biaya=checkPostGet('biaya','');
$berlakudari=checkPostGet('berlakudari','');
$berlakusampai=checkPostGet('berlakusampai','');
$karyawanid=checkPostGet('karyawanid','');
$nomor=checkPostGet('nomor','');

if(isset($nilai) and $nilai=='')
   $nilai=0;
if(isset($_POST['del']) or ($tanggalmulai!='' and $tanggalselesai!='') or isset($_POST['queryonly']))
{
if(isset($_POST['del']) and $_POST['del']=='true')
{
	$str="delete from ".$dbname.".sdm_karyawantraining where nomor=".$nomor;
}
else if(isset($_POST['queryonly']))
{
	$str="select 1=1";
}
else
{
// $trainingblnmulai=$trainingblnmulai."-".$trainingthnmulai;
// $trainingblnselesai=$trainingblnselesai."-".$trainingthnselesai;
	$skarytr="select nomor from ".$dbname.".sdm_karyawantraining where karyawanid='".$karyawanid."' and penyelenggara='".$penyelenggara."' and tanggalmulai='".tanggalsystem($tanggalmulai)."'";
	$qkarytr=mysql_query($skarytr);
	$rkarytr=mysql_num_rows($qkarytr);
if($rkarytr>0){
	$str="update ".$dbname.".sdm_karyawantraining set jenistraining='".$jenistraining."', tanggalselesai='".tanggalsystem($tanggalselesai)."', 
	judultraining='".$judultraining."', sertifikat=".$sertifikat." , biaya=".$biaya." 
	,berlakudari='".tanggalsystem($berlakudari)."', berlakusampai='".tanggalsystem($berlakusampai)."'
	where karyawanid='".$karyawanid."' and penyelenggara='".$penyelenggara."' and tanggalmulai='".tanggalsystem($tanggalmulai)."'";
}else{
	$str="insert into ".$dbname.".sdm_karyawantraining
	     (	`karyawanid`,
			`jenistraining`,
			`tanggalmulai`,
			`tanggalselesai`,
			`judultraining`,
			`penyelenggara`,
			`sertifikat`,
			`berlakudari`,
			`berlakusampai`,
			`biaya`
		  )
		  values(".$karyawanid.",
		  '".$jenistraining."',
		  '".tanggalsystem($tanggalmulai)."',
		  '".tanggalsystem($tanggalselesai)."',
		  '".$judultraining."',
		  '".$penyelenggara."',
		  ".$sertifikat.",
		  ".tanggalsystem($berlakudari).",
		  ".tanggalsystem($berlakusampai).",
		  ".$biaya."
		  )";
}
}
if(mysql_query($str))
   {
	 $str="select a.*,case a.sertifikat when 0 then 'N' else 'Y' end as bersertifikat, b.jenistraining as jnstraining 
	       from ".$dbname.".sdm_karyawantraining a
		   left join ".$dbname.".sdm_5jenistraining b
		   on a.jenistraining = b.kodetraining
	 		where a.karyawanid=".$karyawanid." 
			order by a.tanggalmulai desc";	
	 $res=mysql_query($str);
	 $no=0;
	 while($bar=mysql_fetch_object($res))
	 {
	 $no+=1;
	if($bar->sertifikat=='1'){
		$ketsertifikat=($_SESSION['language']=='EN'?'Attendance Certificate':'Sertifikat Kehadiran');
	}elseif($bar->sertifikat=='2'){
		$ketsertifikat=($_SESSION['language']=='EN'?'Competency Certificate':'Sertifikat Kompetensi');
	}else{
		$ketsertifikat=($_SESSION['language']=='EN'?'No Certificate':'Tidak Ada Sertifikat');
	}
 
	 echo "<tr class=rowcontent>
			  <td align=center class=firsttd>".$no."</td>
			  <td>".$bar->jnstraining."</td>			  
			  <td>".$bar->judultraining."</td>
			  <td>".$bar->penyelenggara."</td>			  
			  <td align=center>".tanggalnormal($bar->tanggalmulai)."</td>			  
			  <td align=center>".tanggalnormal($bar->tanggalselesai)."</td>
			  <td>".$ketsertifikat."</td>
              <td align=right>".$bar->biaya."</td>
			  <td style='text-align:center;'><img src=images/skyblue/edit.png class=resicon  title='Edit' onclick=\"EditFormTraining('".$bar->jenistraining."','".$bar->judultraining."','".$bar->biaya."','".$bar->penyelenggara."','".tanggalnormal($bar->tanggalmulai)."','".tanggalnormal($bar->tanggalselesai)."','".$bar->sertifikat."','".tanggalnormal($bar->berlakudari)."','".tanggalnormal($bar->berlakusampai)."');\">&nbsp;
			  <img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"delTraining('".$karyawanid."','".$bar->nomor."');\"></td>";
	echo "<td>";
		if($bar->sertifikat==2){
			echo	"<img class=resicon src='images/skyblue/plus.png' title='Edit Scan' onclick=\"editscan('".$bar->nomor."',event);\">&nbsp";
		}
		if($bar->scansertifikat!=''){
			echo	"<img class=resicon src='images/skyblue/zoom.png' title='View Scan' onclick=\"viewscan('".$bar->nomor."','".$bar->scansertifikat."',event);\">";
		}
	echo"	</td>
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
	echo " Error; Data incomplete";
}
?>
