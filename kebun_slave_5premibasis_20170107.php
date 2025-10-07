<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$afd=checkPostGet('afd','');
$jenispremi=checkPostGet('jenispremi','');
$kelaspohon=checkPostGet('kelaspohon','');
$basis=checkPostGet('basis',0);
$premilebih=checkPostGet('premilebih',0);

$premilibur=checkPostGet('premilibur',0);
$premiliburcapaibasis=checkPostGet('premiliburcapaibasis',0);
$topografi=checkPostGet('topografi','');
$premitopografi=checkPostGet('premitopografi',0);
$premibrondolan=checkPostGet('premibrondolan',0);

$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
$optJenis = array(
	'KERJA' => 'Hari Kerja',
	'LIBUR' => 'Hari Libur'
);
$optKelas = makeOption($dbname,'kebun_5kelaspohon','kelas,nama');

switch($method)
{
	case'getBasis':
		$str="select * from ".$dbname.".kebun_5kelaspohon where kelas='".$kelaspohon."'";
		$qry=mysql_query($str) or die(mysql_error($conn));
		$res=mysql_fetch_assoc($qry);
		if($kelaspohon==''){
			echo '0';
		}else{
			echo $res['basishari'];
		}
	break;
	
    case'insert':
		if($afd=='') exit("Warning: Silakan pilih Perusahaan");
		if($kelaspohon=='') exit("warning: Silakan pilih Kelas Pohon");
		if($basis=='') exit("warning: Silakan isi Basis (JJG)");
		if($premilebih=='') exit("warning: Silakan isi Premi Lebih Basis (/JJG)");
		
		$scek="select * from ".$dbname.".kebun_5basispanen2 
			   where afdeling='".$afd."' and jenispremi='".$jenispremi.
			   "' and kelaspohon = '".$kelaspohon."' and topografi = '".$topografi."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rcek=mysql_num_rows($qcek);
		if($rcek!=0){
			exit("error: Data sudah pernah diinput.");
		}
		
		$sIns="insert into ".$dbname.".kebun_5basispanen2 (afdeling,jenispremi,
			kelaspohon,basis,premilebihbasis,premilibur,premiliburcapaibasis,
			topografi,premitopografi,premibrondolan) 
			values ('".$afd."','".$jenispremi."','".$kelaspohon."','".$basis."','".
				$premilebih."','".$premilibur."','".$premiliburcapaibasis."','".
				$topografi."','".$premitopografi."','".$premibrondolan."')";
		if(!mysql_query($sIns))
		{
			echo"Gagal".mysql_error($conn);
		}
		break;
    
    case'loadData':
		echo"<div id=container>
            <table class=sortable cellspacing=1 border=0>
                <thead>
					<tr class=rowheader>
						<td>No</td>
						<td align=center>".$_SESSION['lang']['pt']."</td>
						<td align=center>".$_SESSION['lang']['jenispremi']."</td>
						<td align=center>".$_SESSION['lang']['kelaspohon']."</td>
						<td align=center>".$_SESSION['lang']['basisjjg']."</td>
						<td align=center>".$_SESSION['lang']['premilebihbasis']."(/JJG)</td>
						<td align=center>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['harilibur']."</td>
						<td align=center>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['harilibur']." Capai Basis</td>
						<td align=center>".$_SESSION['lang']['topografi']."</td>
						<td align=center>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['absensi']."</td>
						<td align=center>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['brondolan']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>
				<tbody>";
        $limit=15;
		$page=0;
		if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5basispanen2 ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		
		$str="select * from ".$dbname.".kebun_5basispanen2 order by afdeling   limit ".$offset.",".$limit."";
		$res=mysql_query($str)or die(mysql_error());
		$no=$maxdisplay;
		while($bar=mysql_fetch_assoc($res)) {
			$no+=1;	
			echo"<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$bar['afdeling']."</td>
			<td>".$bar['jenispremi']."</td>
			<td>".$bar['kelaspohon']."</td>
			<td style='text-align:center'>".number_format($bar['basis'],0)."</td>
			<td style='text-align:center'>".number_format($bar['premilebihbasis'],0)."</td>
			<td style='text-align:center'>".number_format($bar['premilibur'],0)."</td>
			<td style='text-align:center'>".number_format($bar['premiliburcapaibasis'],0)."</td>
			<td>".$optTopografi[$bar['topografi']]."</td>
			<td style='text-align:center'>".number_format($bar['premitopografi'],0)."</td>
			<td style='text-align:center'>".number_format($bar['premibrondolan'],0)."</td>
			<td>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".
					$bar['afdeling']."','".$bar['jenispremi']."','".$bar['kelaspohon']."','".$bar['basis']."','".$bar['premilebihbasis']."'
					,'".$bar['premilibur']."','".$bar['premiliburcapaibasis']."','".$bar['topografi']."','".$bar['premitopografi']."','".$bar['premibrondolan']."');\">
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['afdeling']."','".$bar['jenispremi']."','".$bar['kelaspohon']."','".$bar['topografi']."');\">             
			</td>
			</tr>";	
		}
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
                
                
            break;   
    
    
    
    
    
    
    
    case'update':
    if($basis=='')
    {
        echo"warning: Silakan isi Basis (JJG)"; exit();
    }
    if($premilebih=='')
    {
        echo"warning: Silakan isi Premi Lebih Basis (/JJG)"; exit();
    }
    
    $sUpd="update ".$dbname.".kebun_5basispanen2 set basis='".$basis."', premilebihbasis='".$premilebih."',
            premilibur='".$premilibur."',premiliburcapaibasis='".$premiliburcapaibasis."',
			premitopografi='".$premitopografi."',premibrondolan='".$premibrondolan."' 
			where afdeling='".$afd."' and jenispremi='".$jenispremi."' and kelaspohon = '".$kelaspohon."' and topografi='".$topografi."'";
    
    if(!mysql_query($sUpd))
    {
        echo"Gagal".mysql_error($conn);
    }
    break;
    case 'deletedata':
    $sDel="delete from ".$dbname.".kebun_5basispanen2 
           where afdeling='".$afd."' and jenispremi='".$jenispremi."' and kelaspohon = '".$kelaspohon."' and topografi='".$topografi."'";
    // print_r($sDel);
	if(mysql_query($sDel))
        echo"";
    else
        echo "DB Error : ".mysql_error($conn);                        
    break;
    
    default:
    break;
}
?>