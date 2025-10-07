<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
//require_once('lib/zLib.php');

$method		=checkPostGet('method','');
$kodeorg	=checkPostGet('kodeorg','');
$kodeblok	=checkPostGet('kodeblok','');
$tanggal	=tanggalsystem(checkPostGet('tanggal',''));
$type		=checkPostGet('type','');
############ cara hitung tanggal kemarin ##############
$tgllalu	=strtotime('-1 day',strtotime($tgl));
$tgllalu	=date('Y-m-d', $tglKmrn);

//exit("Warning: $kodeorg._.$kodeblok._.$tanggal");

//UnRipe
$berattbs_ur2		=checkPostGet('berattbs_ur2',0);
$berattbs_ur4		=checkPostGet('berattbs_ur4',0);
$berattbs_ur5		=checkPostGet('berattbs_ur5',0);
$tbsrebus_ur2		=checkPostGet('tbsrebus_ur2',0);
$tbsrebus_ur4		=checkPostGet('tbsrebus_ur4',0);
$tbsrebus_ur5		=checkPostGet('tbsrebus_ur5',0);

$condensate_ur2		=checkPostGet('condensate_ur2',0);
$condensate_ur4		=checkPostGet('condensate_ur4',0);
$condensate_ur5		=checkPostGet('condensate_ur5',0);
$brondolluar_ur2	=checkPostGet('brondolluar_ur2',0);
$brondolluar_ur4	=checkPostGet('brondolluar_ur4',0);
$brondolluar_ur5	=checkPostGet('brondolluar_ur5',0);
$brondoldalam_ur2	=checkPostGet('brondoldalam_ur2',0);
$brondoldalam_ur4	=checkPostGet('brondoldalam_ur4',0);
$brondoldalam_ur5	=checkPostGet('brondoldalam_ur5',0);
$abn_ur2			=checkPostGet('abn_ur2',0);
$abn_ur4			=checkPostGet('abn_ur4',0);
$abn_ur5			=checkPostGet('abn_ur5',0);
$calix_ur2			=checkPostGet('calix_ur2',0);
$calix_ur4			=checkPostGet('calix_ur4',0);
$calix_ur5			=checkPostGet('calix_ur5',0);
$jangkos_ur2		=checkPostGet('jangkos_ur2',0);
$jangkos_ur4		=checkPostGet('jangkos_ur4',0);
$jangkos_ur5		=checkPostGet('jangkos_ur5',0);
$totaltbs_ur2		=checkPostGet('totaltbs_ur2',0);
$totaltbs_ur4		=checkPostGet('totaltbs_ur4',0);
$totaltbs_ur5		=checkPostGet('totaltbs_ur5',0);
$sampel_ur2			=checkPostGet('sampel_ur2',0);
$sampel_ur4			=checkPostGet('sampel_ur4',0);
$sampel_ur5			=checkPostGet('sampel_ur5',0);

$brondolan_ur2		=checkPostGet('brondolan_ur2',0);
$brondolan_ur4		=checkPostGet('brondolan_ur4',0);
$brondolan_ur5		=checkPostGet('brondolan_ur5',0);
$evaporation_ur2	=checkPostGet('evaporation_ur2',0);
$evaporation_ur4	=checkPostGet('evaporation_ur4',0);
$evaporation_ur5	=checkPostGet('evaporation_ur5',0);
$brondoldry_ur2		=checkPostGet('brondoldry_ur2',0);
$brondoldry_ur4		=checkPostGet('brondoldry_ur4',0);
$brondoldry_ur5		=checkPostGet('brondoldry_ur5',0);
$fiber_ur2			=checkPostGet('fiber_ur2',0);
$fiber_ur4			=checkPostGet('fiber_ur4',0);
$fiber_ur5			=checkPostGet('fiber_ur5',0);
$nut_ur2			=checkPostGet('nut_ur2',0);
$nut_ur4			=checkPostGet('nut_ur4',0);
$nut_ur5			=checkPostGet('nut_ur5',0);
$shell_ur2			=checkPostGet('shell_ur2',0);
$shell_ur4			=checkPostGet('shell_ur4',0);
$shell_ur5			=checkPostGet('shell_ur5',0);
$kernel_ur2			=checkPostGet('kernel_ur2',0);
$kernel_ur4			=checkPostGet('kernel_ur4',0);
$kernel_ur5			=checkPostGet('kernel_ur5',0);
$kerneldry_ur2		=checkPostGet('kerneldry_ur2',0);
$kerneldry_ur4		=checkPostGet('kerneldry_ur4',0);
$kerneldry_ur5		=checkPostGet('kerneldry_ur5',0);
$lossestbs_ur2		=checkPostGet('lossestbs_ur2',0);
$lossestbs_ur4		=checkPostGet('lossestbs_ur4',0);
$lossestbs_ur5		=checkPostGet('lossestbs_ur5',0);
$sttotal_ur2		=checkPostGet('sttotal_ur2',0);
$sttotal_ur4		=checkPostGet('sttotal_ur4',0);
$sttotal_ur5		=checkPostGet('sttotal_ur5',0);

$oilinfiber_ur2		=checkPostGet('oilinfiber_ur2',0);
$oilinfiber_ur4		=checkPostGet('oilinfiber_ur4',0);
$oilinfiber_ur5		=checkPostGet('oilinfiber_ur5',0);
$oilinshell_ur2		=checkPostGet('oilinshell_ur2',0);
$oilinshell_ur4		=checkPostGet('oilinshell_ur4',0);
$oilinshell_ur5		=checkPostGet('oilinshell_ur5',0);
$totaloil_ur2		=checkPostGet('totaloil_ur2',0);
$totaloil_ur4		=checkPostGet('totaloil_ur4',0);
$totaloil_ur5		=checkPostGet('totaloil_ur5',0);
$lossesoil_ur2		=checkPostGet('lossesoil_ur2',0);
$lossesoil_ur4		=checkPostGet('lossesoil_ur4',0);
$lossesoil_ur5		=checkPostGet('lossesoil_ur5',0);
$gttotal_ur2		=checkPostGet('gttotal_ur2',0);
$gttotal_ur4		=checkPostGet('gttotal_ur4',0);
$gttotal_ur5		=checkPostGet('gttotal_ur5',0);
$hasil_ur2			=checkPostGet('hasil_ur2',0);
$hasil_ur4			=checkPostGet('hasil_ur4',0);
$hasil_ur5			=checkPostGet('hasil_ur5',0);

//Normal Ripe
$berattbs_nr2		=checkPostGet('berattbs_nr2',0);
$berattbs_nr4		=checkPostGet('berattbs_nr4',0);
$berattbs_nr5		=checkPostGet('berattbs_nr5',0);
$tbsrebus_nr2		=checkPostGet('tbsrebus_nr2',0);
$tbsrebus_nr4		=checkPostGet('tbsrebus_nr4',0);
$tbsrebus_nr5		=checkPostGet('tbsrebus_nr5',0);

$condensate_nr2		=checkPostGet('condensate_nr2',0);
$condensate_nr4		=checkPostGet('condensate_nr4',0);
$condensate_nr5		=checkPostGet('condensate_nr5',0);
$brondolluar_nr2	=checkPostGet('brondolluar_nr2',0);
$brondolluar_nr4	=checkPostGet('brondolluar_nr4',0);
$brondolluar_nr5	=checkPostGet('brondolluar_nr5',0);
$brondoldalam_nr2	=checkPostGet('brondoldalam_nr2',0);
$brondoldalam_nr4	=checkPostGet('brondoldalam_nr4',0);
$brondoldalam_nr5	=checkPostGet('brondoldalam_nr5',0);
$abn_nr2			=checkPostGet('abn_nr2',0);
$abn_nr4			=checkPostGet('abn_nr4',0);
$abn_nr5			=checkPostGet('abn_nr5',0);
$calix_nr2			=checkPostGet('calix_nr2',0);
$calix_nr4			=checkPostGet('calix_nr4',0);
$calix_nr5			=checkPostGet('calix_nr5',0);
$jangkos_nr2		=checkPostGet('jangkos_nr2',0);
$jangkos_nr4		=checkPostGet('jangkos_nr4',0);
$jangkos_nr5		=checkPostGet('jangkos_nr5',0);
$totaltbs_nr2		=checkPostGet('totaltbs_nr2',0);
$totaltbs_nr4		=checkPostGet('totaltbs_nr4',0);
$totaltbs_nr5		=checkPostGet('totaltbs_nr5',0);
$sampel_nr2			=checkPostGet('sampel_nr2',0);
$sampel_nr4			=checkPostGet('sampel_nr4',0);
$sampel_nr5			=checkPostGet('sampel_nr5',0);

$brondolan_nr2		=checkPostGet('brondolan_nr2',0);
$brondolan_nr4		=checkPostGet('brondolan_nr4',0);
$brondolan_nr5		=checkPostGet('brondolan_nr5',0);
$evaporation_nr2	=checkPostGet('evaporation_nr2',0);
$evaporation_nr4	=checkPostGet('evaporation_nr4',0);
$evaporation_nr5	=checkPostGet('evaporation_nr5',0);
$brondoldry_nr2		=checkPostGet('brondoldry_nr2',0);
$brondoldry_nr4		=checkPostGet('brondoldry_nr4',0);
$brondoldry_nr5		=checkPostGet('brondoldry_nr5',0);
$fiber_nr2			=checkPostGet('fiber_nr2',0);
$fiber_nr4			=checkPostGet('fiber_nr4',0);
$fiber_nr5			=checkPostGet('fiber_nr5',0);
$nut_nr2			=checkPostGet('nut_nr2',0);
$nut_nr4			=checkPostGet('nut_nr4',0);
$nut_nr5			=checkPostGet('nut_nr5',0);
$shell_nr2			=checkPostGet('shell_nr2',0);
$shell_nr4			=checkPostGet('shell_nr4',0);
$shell_nr5			=checkPostGet('shell_nr5',0);
$kernel_nr2			=checkPostGet('kernel_nr2',0);
$kernel_nr4			=checkPostGet('kernel_nr4',0);
$kernel_nr5			=checkPostGet('kernel_nr5',0);
$kerneldry_nr2		=checkPostGet('kerneldry_nr2',0);
$kerneldry_nr4		=checkPostGet('kerneldry_nr4',0);
$kerneldry_nr5		=checkPostGet('kerneldry_nr5',0);
$lossestbs_nr2		=checkPostGet('lossestbs_nr2',0);
$lossestbs_nr4		=checkPostGet('lossestbs_nr4',0);
$lossestbs_nr5		=checkPostGet('lossestbs_nr5',0);
$sttotal_nr2		=checkPostGet('sttotal_nr2',0);
$sttotal_nr4		=checkPostGet('sttotal_nr4',0);
$sttotal_nr5		=checkPostGet('sttotal_nr5',0);

$oilinfiber_nr2		=checkPostGet('oilinfiber_nr2',0);
$oilinfiber_nr4		=checkPostGet('oilinfiber_nr4',0);
$oilinfiber_nr5		=checkPostGet('oilinfiber_nr5',0);
$oilinshell_nr2		=checkPostGet('oilinshell_nr2',0);
$oilinshell_nr4		=checkPostGet('oilinshell_nr4',0);
$oilinshell_nr5		=checkPostGet('oilinshell_nr5',0);
$totaloil_nr2		=checkPostGet('totaloil_nr2',0);
$totaloil_nr4		=checkPostGet('totaloil_nr4',0);
$totaloil_nr5		=checkPostGet('totaloil_nr5',0);
$lossesoil_nr2		=checkPostGet('lossesoil_nr2',0);
$lossesoil_nr4		=checkPostGet('lossesoil_nr4',0);
$lossesoil_nr5		=checkPostGet('lossesoil_nr5',0);
$gttotal_nr2		=checkPostGet('gttotal_nr2',0);
$gttotal_nr4		=checkPostGet('gttotal_nr4',0);
$gttotal_nr5		=checkPostGet('gttotal_nr5',0);
$hasil_nr2			=checkPostGet('hasil_nr2',0);
$hasil_nr4			=checkPostGet('hasil_nr4',0);
$hasil_nr5			=checkPostGet('hasil_nr5',0);

//UnRipe
$berattbs_or2		=checkPostGet('berattbs_or2',0);
$berattbs_or4		=checkPostGet('berattbs_or4',0);
$berattbs_or5		=checkPostGet('berattbs_or5',0);
$tbsrebus_or2		=checkPostGet('tbsrebus_or2',0);
$tbsrebus_or4		=checkPostGet('tbsrebus_or4',0);
$tbsrebus_or5		=checkPostGet('tbsrebus_or5',0);

$condensate_or2		=checkPostGet('condensate_or2',0);
$condensate_or4		=checkPostGet('condensate_or4',0);
$condensate_or5		=checkPostGet('condensate_or5',0);
$brondolluar_or2	=checkPostGet('brondolluar_or2',0);
$brondolluar_or4	=checkPostGet('brondolluar_or4',0);
$brondolluar_or5	=checkPostGet('brondolluar_or5',0);
$brondoldalam_or2	=checkPostGet('brondoldalam_or2',0);
$brondoldalam_or4	=checkPostGet('brondoldalam_or4',0);
$brondoldalam_or5	=checkPostGet('brondoldalam_or5',0);
$abn_or2			=checkPostGet('abn_or2',0);
$abn_or4			=checkPostGet('abn_or4',0);
$abn_or5			=checkPostGet('abn_or5',0);
$calix_or2			=checkPostGet('calix_or2',0);
$calix_or4			=checkPostGet('calix_or4',0);
$calix_or5			=checkPostGet('calix_or5',0);
$jangkos_or2		=checkPostGet('jangkos_or2',0);
$jangkos_or4		=checkPostGet('jangkos_or4',0);
$jangkos_or5		=checkPostGet('jangkos_or5',0);
$totaltbs_or2		=checkPostGet('totaltbs_or2',0);
$totaltbs_or4		=checkPostGet('totaltbs_or4',0);
$totaltbs_or5		=checkPostGet('totaltbs_or5',0);
$sampel_or2			=checkPostGet('sampel_or2',0);
$sampel_or4			=checkPostGet('sampel_or4',0);
$sampel_or5			=checkPostGet('sampel_or5',0);

$brondolan_or2		=checkPostGet('brondolan_or2',0);
$brondolan_or4		=checkPostGet('brondolan_or4',0);
$brondolan_or5		=checkPostGet('brondolan_or5',0);
$evaporation_or2	=checkPostGet('evaporation_or2',0);
$evaporation_or4	=checkPostGet('evaporation_or4',0);
$evaporation_or5	=checkPostGet('evaporation_or5',0);
$brondoldry_or2		=checkPostGet('brondoldry_or2',0);
$brondoldry_or4		=checkPostGet('brondoldry_or4',0);
$brondoldry_or5		=checkPostGet('brondoldry_or5',0);
$fiber_or2			=checkPostGet('fiber_or2',0);
$fiber_or4			=checkPostGet('fiber_or4',0);
$fiber_or5			=checkPostGet('fiber_or5',0);
$nut_or2			=checkPostGet('nut_or2',0);
$nut_or4			=checkPostGet('nut_or4',0);
$nut_or5			=checkPostGet('nut_or5',0);
$shell_or2			=checkPostGet('shell_or2',0);
$shell_or4			=checkPostGet('shell_or4',0);
$shell_or5			=checkPostGet('shell_or5',0);
$kernel_or2			=checkPostGet('kernel_or2',0);
$kernel_or4			=checkPostGet('kernel_or4',0);
$kernel_or5			=checkPostGet('kernel_or5',0);
$kerneldry_or2		=checkPostGet('kerneldry_or2',0);
$kerneldry_or4		=checkPostGet('kerneldry_or4',0);
$kerneldry_or5		=checkPostGet('kerneldry_or5',0);
$lossestbs_or2		=checkPostGet('lossestbs_or2',0);
$lossestbs_or4		=checkPostGet('lossestbs_or4',0);
$lossestbs_or5		=checkPostGet('lossestbs_or5',0);
$sttotal_or2		=checkPostGet('sttotal_or2',0);
$sttotal_or4		=checkPostGet('sttotal_or4',0);
$sttotal_or5		=checkPostGet('sttotal_or5',0);

$oilinfiber_or2		=checkPostGet('oilinfiber_or2',0);
$oilinfiber_or4		=checkPostGet('oilinfiber_or4',0);
$oilinfiber_or5		=checkPostGet('oilinfiber_or5',0);
$oilinshell_or2		=checkPostGet('oilinshell_or2',0);
$oilinshell_or4		=checkPostGet('oilinshell_or4',0);
$oilinshell_or5		=checkPostGet('oilinshell_or5',0);
$totaloil_or2		=checkPostGet('totaloil_or2',0);
$totaloil_or4		=checkPostGet('totaloil_or4',0);
$totaloil_or5		=checkPostGet('totaloil_or5',0);
$lossesoil_or2		=checkPostGet('lossesoil_or2',0);
$lossesoil_or4		=checkPostGet('lossesoil_or4',0);
$lossesoil_or5		=checkPostGet('lossesoil_or5',0);
$gttotal_or2		=checkPostGet('gttotal_or2',0);
$gttotal_or4		=checkPostGet('gttotal_or4',0);
$gttotal_or5		=checkPostGet('gttotal_or5',0);
$hasil_or2			=checkPostGet('hasil_or2',0);
$hasil_or4			=checkPostGet('hasil_or4',0);
$hasil_or5			=checkPostGet('hasil_or5',0);

	if($method=='deldata'){
		$strx="delete from ".$dbname.".pabrik_materialballance where kodeorg='".$kodeorg."' and kodeblok='".$kodeblok."' and tanggal='".$tanggal."'";
	}else if($method=='simpanData'){
		$strs="select kodeorg,kodeblok,tanggal from ".$dbname.".pabrik_materialballance 
		       where kodeorg='".$kodeorg."' and kodeblok='".$kodeblok."' and tanggal='".$tanggal."'";
		$ress=mysql_query($strs);
		$rows=mysql_num_rows($ress);
		if($rows>0){
			$strx="update ".$dbname.".pabrik_materialballance set kodeorg='".$kodeorg."',kodeblok='".$kodeblok."',tanggal='".$tanggal."'
					,berattbs_ur=".$berattbs_ur2.",tbsrebus_ur=".$tbsrebus_ur2.",brondolluar_ur=".$brondolluar_ur2.",brondoldalam_ur=".$brondoldalam_ur2."
					,abn_ur=".$abn_ur2.",calix_ur=".$calix_ur2.",jangkos_ur=".$jangkos_ur2.",brondolan_ur=".$brondolan_ur2.",brondoldry_ur=".$brondoldry_ur2."
					,nut_ur=".$nut_ur2.",shell_ur=".$shell_ur2.",kernel_ur=".$kernel_ur2.",kerneldry_ur=".$kerneldry_ur2.",lossestbs_ur=".$lossestbs_ur5."
					,oilinfiber_ur=".$oilinfiber_ur2.",oilinshell_ur=".$oilinshell_ur2.",lossesoil_ur=".$lossesoil_ur5."
					,berattbs_nr=".$berattbs_nr2.",tbsrebus_nr=".$tbsrebus_nr2.",brondolluar_nr=".$brondolluar_nr2.",brondoldalam_nr=".$brondoldalam_nr2."
					,abn_nr=".$abn_nr2.",calix_nr=".$calix_nr2.",jangkos_nr=".$jangkos_nr2.",brondolan_nr=".$brondolan_nr2.",brondoldry_nr=".$brondoldry_nr2."
					,nut_nr=".$nut_nr2.",shell_nr=".$shell_nr2.",kernel_nr=".$kernel_nr2.",kerneldry_nr=".$kerneldry_nr2.",lossestbs_nr=".$lossestbs_nr5."
					,oilinfiber_nr=".$oilinfiber_nr2.",oilinshell_nr=".$oilinshell_nr2.",lossesoil_nr=".$lossesoil_nr5."
					,berattbs_or=".$berattbs_or2.",tbsrebus_or=".$tbsrebus_or2.",brondolluar_or=".$brondolluar_or2.",brondoldalam_or=".$brondoldalam_or2."
					,abn_or=".$abn_or2.",calix_or=".$calix_or2.",jangkos_or=".$jangkos_or2.",brondolan_or=".$brondolan_or2.",brondoldry_or=".$brondoldry_or2."
					,nut_or=".$nut_or2.",shell_or=".$shell_or2.",kernel_or=".$kernel_or2.",kerneldry_or=".$kerneldry_or2.",lossestbs_or=".$lossestbs_or5."
					,oilinfiber_or=".$oilinfiber_or2.",oilinshell_or=".$oilinshell_or2.",lossesoil_or=".$lossesoil_or5."
					,lastdate=now(),lastuser='".$_SESSION['standard']['username']."'
					where kodeorg='".$kodeorg."' and kodeblok='".$kodeblok."' and tanggal='".$tanggal."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_materialballance (kodeorg,kodeblok,tanggal
					,berattbs_ur,tbsrebus_ur,brondolluar_ur,brondoldalam_ur,abn_ur,calix_ur,jangkos_ur
					,brondolan_ur,brondoldry_ur,nut_ur,shell_ur,kernel_ur,kerneldry_ur,lossestbs_ur,oilinfiber_ur,oilinshell_ur,lossesoil_ur
					,berattbs_nr,tbsrebus_nr,brondolluar_nr,brondoldalam_nr,abn_nr,calix_nr,jangkos_nr
					,brondolan_nr,brondoldry_nr,nut_nr,shell_nr,kernel_nr,kerneldry_nr,lossestbs_nr,oilinfiber_nr,oilinshell_nr,lossesoil_nr
					,berattbs_or,tbsrebus_or,brondolluar_or,brondoldalam_or,abn_or,calix_or,jangkos_or
					,brondolan_or,brondoldry_or,nut_or,shell_or,kernel_or,kerneldry_or,lossestbs_or,oilinfiber_or,oilinshell_or,lossesoil_or
					,lastdate,lastuser) values('".$kodeorg."','".$kodeblok."','".$tanggal."'
					,".$berattbs_ur2.",".$tbsrebus_ur2.",".$brondolluar_ur2.",".$brondoldalam_ur2.",".$abn_ur2.",".$calix_ur2.",".$jangkos_ur2."
					,".$brondolan_ur2.",".$brondoldry_ur2.",".$nut_ur2.",".$shell_ur2.",".$kernel_ur2.",".$kerneldry_ur2.",".$lossestbs_ur5."
					,".$oilinfiber_ur2.",".$oilinshell_ur2.",".$lossesoil_ur5."
					,".$berattbs_nr2.",".$tbsrebus_nr2.",".$brondolluar_nr2.",".$brondoldalam_nr2.",".$abn_nr2.",".$calix_nr2.",".$jangkos_nr2."
					,".$brondolan_nr2.",".$brondoldry_nr2.",".$nut_nr2.",".$shell_nr2.",".$kernel_nr2.",".$kerneldry_nr2.",".$lossestbs_nr5."
					,".$oilinfiber_nr2.",".$oilinshell_nr2.",".$lossesoil_nr5."
					,".$berattbs_or2.",".$tbsrebus_or2.",".$brondolluar_or2.",".$brondoldalam_or2.",".$abn_or2.",".$calix_or2.",".$jangkos_or2."
					,".$brondolan_or2.",".$brondoldry_or2.",".$nut_or2.",".$shell_or2.",".$kernel_or2.",".$kerneldry_or2.",".$lossestbs_or5."
					,".$oilinfiber_or2.",".$oilinshell_or2.",".$lossesoil_or5."
					,now(),'".$_SESSION['standard']['username']."')";
		}
	}else{
		$strx="select count(a.kodeorg) as jmlrec from ".$dbname.".pabrik_materialballance a where a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	//exit('Warning: '.$_POST['page']);
	if(mysql_query($strx)){
		$strb="select count(a.kodeorg) as jmlrec from ".$dbname.".pabrik_materialballance a where a.kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$resb=mysql_query($strb);
		$rdatab=mysql_fetch_assoc($resb);
		$jlhbrs=$rdatab['jmlrec'];
		//$jlhbrs=mysql_num_rows($resb);
		//exit('Warning: '.$strb.' '.$jlhbrs);
		$limit=25;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',0);
			if((($page*$limit)+1)>$jlhbrs)
				$page=$page-1;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$str="select a.*,b.namaorganisasi as namablok from ".$dbname.".pabrik_materialballance a
			  left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeblok
		      where a.kodeorg='".$_SESSION['empl']['lokasitugas']."'
			  order by a.kodeorg,a.tanggal desc,a.kodeblok limit ".$offset.",".$limit."";
		//exit('Warning: '.$str.' page='.$page.' hal='.(($page*$limit)+1));
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			$totaltbs_ur=$bar->berattbs_ur-$bar->tbsrebus_ur+$bar->brondolluar_ur+$bar->brondoldalam_ur+$bar->abn_ur+$bar->calix_ur+$bar->jangkos_ur;
			$totaltbs_nr=$bar->berattbs_nr-$bar->tbsrebus_nr+$bar->brondolluar_nr+$bar->brondoldalam_nr+$bar->abn_nr+$bar->calix_nr+$bar->jangkos_nr;
			$totaltbs_or=$bar->berattbs_or-$bar->tbsrebus_or+$bar->brondolluar_or+$bar->brondoldalam_or+$bar->abn_or+$bar->calix_or+$bar->jangkos_or;
			echo"<tr class=rowcontent>
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=center>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=center>".substr($bar->kodeblok,0,6)."</td>
					<td ".$drcl." align=center>".(substr($bar->kodeblok,0,6)=='TBSEXT' ? 'TBS Luar' : $bar->namablok)."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->tbsrebus_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_ur-$bar->tbsrebus_ur,0,'.',',.')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondolluar_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondoldalam_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->abn_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->calix_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->jangkos_ur,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->tbsrebus_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_nr-$bar->tbsrebus_nr,0,'.',',.')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondolluar_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondoldalam_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->abn_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->calix_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->jangkos_nr,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->tbsrebus_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->berattbs_or-$bar->tbsrebus_or,0,'.',',.')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondolluar_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->brondoldalam_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->abn_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->calix_or,0,'.',',')."</td>
					<td ".$drcl." align=right width='2%'>".number_format($bar->jangkos_or,0,'.',',')."</td>
					<td align=center>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodeblok."','".tanggalnormal($bar->tanggal)."'
						,'".$bar->berattbs_ur."','".$bar->tbsrebus_ur."','".$bar->brondolluar_ur."','".$bar->brondoldalam_ur."','".$bar->abn_ur."'
						,'".$bar->calix_ur."','".$bar->jangkos_ur."','".$bar->brondolan_ur."','".$bar->brondoldry_ur."','".$bar->nut_ur."','".$bar->shell_ur."'
						,'".$bar->kernel_ur."','".$bar->kerneldry_ur."','".$bar->lossestbs_ur."','".$bar->oilinfiber_ur."','".$bar->oilinshell_ur."'
						,'".$bar->lossesoil_ur."'
						,'".$bar->berattbs_nr."','".$bar->tbsrebus_nr."','".$bar->brondolluar_nr."','".$bar->brondoldalam_nr."','".$bar->abn_nr."'
						,'".$bar->calix_nr."','".$bar->jangkos_nr."','".$bar->brondolan_nr."','".$bar->brondoldry_nr."','".$bar->nut_nr."','".$bar->shell_nr."'
						,'".$bar->kernel_nr."','".$bar->kerneldry_nr."','".$bar->lossestbs_nr."','".$bar->oilinfiber_nr."','".$bar->oilinshell_nr."'
						,'".$bar->lossesoil_nr."'
						,'".$bar->berattbs_or."','".$bar->tbsrebus_or."','".$bar->brondolluar_or."','".$bar->brondoldalam_or."','".$bar->abn_or."'
						,'".$bar->calix_or."','".$bar->jangkos_or."','".$bar->brondolan_or."','".$bar->brondoldry_or."','".$bar->nut_or."','".$bar->shell_or."'
						,'".$bar->kernel_or."','".$bar->kerneldry_or."','".$bar->lossestbs_or."','".$bar->oilinfiber_or."','".$bar->oilinshell_or."'
						,'".$bar->lossesoil_or."'
						)\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodeblok."','".tanggalnormal($bar->tanggal)."');\">&nbsp
						<img src=images/excel.jpg class=resicon title='Excel' onclick=\"showpopup('".$bar->kodeorg."','".$bar->kodeblok."','".tanggalnormal($bar->tanggal)."','excel',event);\">
					</td>
				</tr>";	
		}
		echo"
		<tr class=rowheader>
			<td colspan=29 align=center>
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>&nbsp
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."&nbsp
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
	}else{
		echo " Gagal,".addslashes(mysql_error($conn));
	}
?>
