<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

	$kodeorg	  =checkPostGet('kodeorg','');
        $tanggal	  =tanggalsystem(checkPostGet('tanggal',''));
	$sisatbskemarin=checkPostGet('sisatbskemarin','');
	$tbsmasuk     =checkPostGet('tbsmasuk','');
	$tbsdiolah    =checkPostGet('tbsdiolah','');
	$sisahariini  =checkPostGet('sisahariini','');
	
	$oer     	  =checkPostGet('oer','');
	$kadarair     =checkPostGet('kadarair','');
	$ffa     	  =checkPostGet('ffa','');
	$dirt     	  =checkPostGet('dirt','');

	$oerpk     	  =checkPostGet('oerpk','');
	$kadarairpk   =checkPostGet('kadarairpk','');
	$ffapk     	  =checkPostGet('ffapk','');
	$dirtpk       =checkPostGet('dirtpk','');

        $usbbefore     	  =checkPostGet('usbbefore','');
        $usbafter     	  =checkPostGet('usbafter','');
        $oildiluted       =checkPostGet('oildiluted','');
        $oilin    	  =checkPostGet('oilin','');
        $oilinheavy    	  =checkPostGet('oilinheavy','');
        $caco     	  =checkPostGet('caco','');
   
        //cpo loses
        $fruitineb     	  =checkPostGet('fruitineb','');
        $ebstalk     	  =checkPostGet('ebstalk','');
        $fibre            =checkPostGet('fibre','');
        $nut    	  =checkPostGet('nut','');
        $effluent    	  =checkPostGet('effluent','');
        $soliddecanter    =checkPostGet('soliddecanter','');
    

        //kernel loses
        $fruitinebker     =checkPostGet('fruitinebker','');
        $cyclone    	  =checkPostGet('cyclone','');
        $claybath   	  =checkPostGet('claybath','');
        $ltds             =checkPostGet('ltds','');
        $usbcpo           =checkPostGet('usbcpo','');
        $usbpk            =checkPostGet('usbpk','');
        $hydrocyclone         =checkPostGet('hydrocyclone','');
        
     
	
	if(isset($_POST['del']))
	  {
			$strx="delete from ".$dbname.".pabrik_produksi 
			       where kodeorg='".$kodeorg."' 
				   and tanggal='".$_POST['tanggal']."'";   
	  }
	  else
	  {

			$strx="insert into ".$dbname.".pabrik_produksi
                   (kodeorg,tanggal,sisatbskemarin,
				    tbsmasuk,tbsdiolah,sisahariini,
				    oer,ffa,kadarair,kadarkotoran,
					oerpk,ffapk,kadarairpk,kadarkotoranpk,
					karyawanid,fruitineb, ebstalk, fibre, nut, 
                                        effluent, soliddecanter, fruitinebker, cyclone, 
                                        ltds, claybath, usbbefore, usbafter, oildiluted, oilin, 
                                        oilinheavy, caco,usbcpo,usbpk,hydrocyclone)
					values('".$kodeorg."',".$tanggal.",".$sisatbskemarin.",
					".$tbsmasuk.",".$tbsdiolah.",".$sisahariini.",
					".$oer.",".$ffa.",".$kadarair.",".$dirt.",
					".$oerpk.",".$ffapk.",".$kadarairpk.",".$dirtpk.",
					".$_SESSION['standard']['userid'].",".$fruitineb.",".$ebstalk.",
                                        ".$fibre.",".$nut.",".$effluent.",".$soliddecanter.",".$fruitinebker.",".$cyclone.",
                                        ".$ltds.",".$claybath.",".$usbbefore.",".$usbafter.",
                                        ".$oildiluted.",".$oilin.",".$oilinheavy.",".$caco.",".$usbcpo.",".$usbpk.",".$hydrocyclone.")";
	  }

  if(mysql_query($strx))
  {
	
			$str="select a.* from ".$dbname.".pabrik_produksi a where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
			      order by a.tanggal desc limit 20";
			$res=mysql_query($str);
			while($bar=mysql_fetch_object($res))
			{
                            $tCpoLoses=$bar->usbcpo+$bar->fruitineb+$bar->ebstalk+$bar->fibre+$bar->nut+$bar->effluent+$bar->soliddecanter;
                            $tKernelLoses=$bar->usbpk+$bar->fruitinebker+$bar->cyclone+$bar->ltds+$bar->claybath+$bar->hydrocyclone;
                                $drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
                                echo"<tr class=rowcontent >
                                <td ".$drcl.">".$bar->kodeorg."</td>
                                <td ".$drcl.">".tanggalnormal($bar->tanggal)."</td>
                                <td ".$drcl." align=right>".number_format($bar->sisatbskemarin,0,'.',',')."</td>
                                <td ".$drcl." align=right>".number_format($bar->tbsmasuk,0,'.',',')."</td>
                                <td ".$drcl." align=right>".number_format($bar->tbsdiolah,0,'.',',.')."</td>
                                <td ".$drcl." align=right>".number_format($bar->sisahariini,0,'.',',')."</td>

                                <td ".$drcl." align=right>".number_format($bar->oer,2,'.',',')."</td>
                                <td ".$drcl." align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
                                <td ".$drcl." align=right>".$bar->ffa."</td>
                                <td ".$drcl." align=right>".$bar->kadarkotoran."</td>
                                <td ".$drcl." align=right>".$bar->kadarair."</td>
                                    <td ".$drcl." align=right>".$tCpoLoses."</td>
                                    


                                <td ".$drcl." align=right>".number_format($bar->oerpk,2,'.',',')."</td>
                                <td ".$drcl." align=right>".(@number_format(@$bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
                                <td ".$drcl." align=right>".$bar->ffapk."</td>
                                <td ".$drcl." align=right>".$bar->kadarkotoranpk."</td>
                                <td ".$drcl." align=right>".$bar->kadarairpk."</td>
                                    <td ".$drcl." align=right>".$tKernelLoses."</td>
				   <td>
				     <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delProduksi('".$bar->kodeorg."','".$bar->tanggal."','".(isset($bar->kodebarang)? $bar->kodebarang: '')."');\">
				   </td>
				  </tr>";	
			}
}	
  else
	{
		echo " Gagal,".addslashes(mysql_error($conn));
	}	
	
?>
