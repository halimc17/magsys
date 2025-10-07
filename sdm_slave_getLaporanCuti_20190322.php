<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
$karyawan=checkPostGet('karyawan','');
$method=checkPostGet('method','');

switch($method){
	case 'preview':
		$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	       from ".$dbname.".sdm_cutiht a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where a.kodeorg='".$kodeorg."' 
		   and a.periodecuti='".$periode."' 
		   and b.nik like '%".$karyawan."%'"; 
		   
		$res1=mysql_query($str1);
		
		if(mysql_num_rows($res1) <= 0){
			echo $_SESSION['lang']['datanotfound'];
		}else{
			echo"<table class=sortable cellspacing=1 border=0>
				 <thead>
				 <tr class=rowheader>
					<td>No</td>
					<td>".$_SESSION['lang']['kodeorganisasi']."</td>		 
					<td>".$_SESSION['lang']['nik']."</td>
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td>".$_SESSION['lang']['tanggalmasuk']."</td>			
					<td>".$_SESSION['lang']['periode']."</td>			
					<td>".$_SESSION['lang']['dari']."</td>
					<td>".$_SESSION['lang']['tanggalsampai']."</td>
					<td>".$_SESSION['lang']['hakcuti']."</td>
					<td>".$_SESSION['lang']['diambil']."</td>
					<td>".$_SESSION['lang']['sisa']."</td>
					</tr>
				 </thead>
				 <tbody id=container>"; 
			$no=0;	 
			while($bar1=mysql_fetch_object($res1))
			{
				$no+=1;
				
				echo"<tr class=rowcontent id=baris".$no.">
						   <td>".$no."</td>
						   <td>".substr($bar1->kodeorg,0,4)."</td>
						   <td>".$bar1->nik."</td>
						   <td>".$bar1->namakaryawan."</td>
						   <td>".tanggalnormal($bar1->tanggalmasuk)."</td>
						   <td>".$periode."</td>				   
						   <td>".tanggalnormal($bar1->dari)."</td>
						   <td>".tanggalnormal($bar1->sampai)."</td>
						   <td>".$bar1->hakcuti."</td>
						   <td>".$bar1->diambil."</td>
						   <td>".$bar1->sisa."</td>
					</tr>	   
						   ";
			}	 
			echo"	 
				 </tbody>
				 <tfoot>
				 </tfoot>
				 </table>";
		}
	break;
	
	case 'loadkaryawan':
		$hariini = date("Y-m-d");
		$optkaryawan="";
		$str="select nik,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in(0,1,7,8) and lokasitugas='".$kodeorg."' and (tanggalkeluar='0000-00-00' or tanggalkeluar>'".$hariini."') order by namakaryawan";
		$res=mysql_query($str);
		$optkaryawan.="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($bar=mysql_fetch_object($res))
		{
			$optkaryawan.="<option value='".$bar->nik."'>".$bar->namakaryawan."</option>";
		}
		echo $optkaryawan;
	break;
	
	default:
	break;
}
?>