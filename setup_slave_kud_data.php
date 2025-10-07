<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once ('lib/zLib.php');

# Get POST Data
$kodeblok=checkPostGet('kodeblok','');
$supplierid=checkPostGet('supplierid','');
$nosertifikat=checkPostGet('nosertifikat','');
$proses=checkPostGet('proses','');
$afdeling=checkPostGet('afdeling','');
$hiddensupplierid=checkPostGet('hiddensupplierid','');

switch($proses)
{
	case 'simpan':
		$sCount = "select * from ".$dbname.".kebun_5kud where supplierid = '".$supplierid."' and kodeblok = '".$kodeblok."'";
		if(mysql_num_rows(mysql_query($sCount)) <= 0){
			$str="insert into ".$dbname.".kebun_5kud values ('".$supplierid."','".$kodeblok."','".$nosertifikat."')";
			mysql_query($str);
		}else{
			exit("warning : Item sudah ada didatabase.");
		}
	break;
	
	case 'update':
		$str="update ".$dbname.".kebun_5kud set supplierid='".$supplierid."', nosertifikat='".$nosertifikat."' where kodeblok='".$kodeblok."' and supplierid='".$hiddensupplierid."'";
        mysql_query($str);
		// echo"wew";
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".kebun_5kud where kodeblok='".$kodeblok."' and supplierid='".$supplierid."'";
        mysql_query($str);
	break;
	
	default:
	break;
}

$str="select * from ".$dbname.".kebun_5kud t1, ".$dbname.".organisasi t2, ".$dbname.".log_5supplier t3
	 where t1.kodeblok=t2.kodeorganisasi and t1.supplierid=t3.supplierid and t2.induk='".$afdeling."' 
	 order by t1.kodeblok ASC";
$res=mysql_query($str);
$no=0;

echo"<fieldset id='search' style='margin-bottom:10px;float:left;clear:both'>
	<legend><b>List Data : KUD</b></legend>
	 <table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead>
			<tr class=rowheader>
				<td>".$_SESSION['lang']['nomor']."</td>
				<td>".$_SESSION['lang']['kodeblok']."</td>
				<td>".$_SESSION['lang']['kodesupplier']."</td>
				<td>".$_SESSION['lang']['namasupplier']."</td>
				<td>".$_SESSION['lang']['nosertifikat']."</td>
				<td colspan=2 style=text-align:center>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody>";
		
		while($bar=mysql_fetch_object($res))
		{
			$no++;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$bar->kodeblok."</td>
					<td>".$bar->supplierid."</td>
					<td>".$bar->namasupplier."</td>
					<td>".$bar->nosertifikat."</td>
					<td><img class='zImgBtn' src='images/001_45.png' onclick=editRow('".$bar->kodeblok."','".$bar->supplierid."','".$bar->nosertifikat."')></td>
					<td><img class='zImgBtn' src='images/delete_32.png' onclick=deleteitem('".$bar->kodeblok."','".$bar->supplierid."')></td>
				</tr>";
		}
			
		
		"</tbody>
		<thead>
			<tr class=rowheader>
				<td colspan=6 height=10px></td>
			</tr>
		</thead>	
	</table>
	</fieldset>";



// #========Get Blok Ids============
// # Create Condition
// $where1 = "(tipe='BLOK' or tipe='BIBITAN') and induk='".$afdeling."'";

// # Get Org Data
// $query = selectQuery($dbname,'organisasi',"kodeorganisasi",$where1);
// $data = fetchData($query);
// # Create Condition for Table
// $where2 = array();
// foreach($data as $key=>$row) {
    // $where2[] = array('kodeorg'=>$row['kodeorganisasi']);
// }
// if(count($where2)<1)
// {
    // exit("Error:Tidak ada data");
// }
// $where2['sep'] = 'OR';

// #========Start Make Table
// # Prep
// $fieldStr = '##kodeorg##tahuntanam##luasareaproduktif';
// $fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

// # Get Data
// #$query = selectQuery($dbname,'setup_blok',"*",$where2);
// #$data = fetchData($query);

// # Set Header Name
// $head = array();
// $head[0]['name'] = $_SESSION['lang']['kodeorg'];
// $head[1]['name'] = $_SESSION['lang']['namasupplier'];
// $head[2]['name'] = $_SESSION['lang']['nosertifikat'];

// # Display Table
// $master = masterTableBlok($dbname,'setup_blok',1,$fieldArr,$head,$conSetting,$where2,
    // array(),'setup_slave_blok_pdf');
// try {
    // echo $master;
// } catch(Exception $e) {
    // echo "Create Table Error";
// }
?>