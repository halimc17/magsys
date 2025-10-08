<?php
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

		$dobicpo			=checkPostGet('dobicpo','');
		$kernelpecah		=checkPostGet('kernelpecah','');
		$kerneljamolah		=checkPostGet('kerneljamolah','');
		$kernelkapasitasolah=checkPostGet('kernelkapasitasolah','');
		$limbah				=checkPostGet('limbah','');
		$jampompa			=checkPostGet('jampompa','');
		$landaplikasi		=checkPostGet('landaplikasi','');
        
     
	
	if(isset($_POST['del']))
	  {
			$strx="delete from ".$dbname.".pabrik_produksi 
			       where kodeorg='".$kodeorg."' 
				   and tanggal='".$_POST['tanggal']."'";   
	  }
	  else
	  {
		$strs="select * from ".$dbname.".pabrik_produksi 
		       where kodeorg='".$kodeorg."' 
			   and tanggal='".tanggalsystem($_POST['tanggal'])."'";
		$ress=mysql_query($strs);
		$rows=mysql_num_rows($ress);
		if($rows>0){
			$strx="update ".$dbname.".pabrik_produksi set kodeorg='".$kodeorg."',tanggal=".$tanggal.",sisatbskemarin=".$sisatbskemarin.",tbsmasuk=".$tbsmasuk.",
					tbsdiolah=".$tbsdiolah.",sisahariini=".$sisahariini.",oer=".$oer.",ffa=".$ffa.",kadarair=".$kadarair.",kadarkotoran=".$dirt.",oerpk=".$oerpk.",
					ffapk=".$ffapk.",kadarairpk=".$kadarairpk.",kadarkotoranpk=".$dirtpk.",karyawanid=".$_SESSION['standard']['userid'].",fruitineb=".$fruitineb.",
					ebstalk=".$ebstalk.",fibre=".$fibre.",nut=".$nut.",effluent=".$effluent.",soliddecanter=".$soliddecanter.",fruitinebker=".$fruitinebker.",
					cyclone=".$cyclone.",ltds=".$ltds.",claybath=".$claybath.",usbbefore=".$usbbefore.",usbafter=".$usbafter.",oildiluted=".$oildiluted.",
					oilin=".$oilin.",oilinheavy=".$oilinheavy.",caco=".$caco.",usbcpo=".$usbcpo.",usbpk=".$usbpk.",hydrocyclone=".$hydrocyclone.",
					dobicpo=".$dobicpo.",kernelpecah=".$kernelpecah.",kerneljamolah=".$kerneljamolah.",kernelkapasitasolah=".$kernelkapasitasolah.",
					limbah=".$limbah.",jampompa=".$jampompa.",
					landaplikasi=".$landaplikasi." 
					where kodeorg='".$kodeorg."' 
					and tanggal='".tanggalsystem($_POST['tanggal'])."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_produksi
                   (kodeorg,tanggal,sisatbskemarin,
				    tbsmasuk,tbsdiolah,sisahariini,
				    oer,ffa,kadarair,kadarkotoran,
					oerpk,ffapk,kadarairpk,kadarkotoranpk,
					karyawanid,fruitineb, ebstalk, fibre, nut, 
                                        effluent, soliddecanter, fruitinebker, cyclone, 
                                        ltds, claybath, usbbefore, usbafter, oildiluted, oilin, 
                                        oilinheavy, caco,usbcpo,usbpk,hydrocyclone,
										dobicpo,kernelpecah,kerneljamolah,kernelkapasitasolah,limbah,jampompa,landaplikasi)
					values('".$kodeorg."',".$tanggal.",".$sisatbskemarin.",
					".$tbsmasuk.",".$tbsdiolah.",".$sisahariini.",
					".$oer.",".$ffa.",".$kadarair.",".$dirt.",
					".$oerpk.",".$ffapk.",".$kadarairpk.",".$dirtpk.",
					".$_SESSION['standard']['userid'].",".$fruitineb.",".$ebstalk.",
                                        ".$fibre.",".$nut.",".$effluent.",".$soliddecanter.",".$fruitinebker.",".$cyclone.",
                                        ".$ltds.",".$claybath.",".$usbbefore.",".$usbafter.",
                                        ".$oildiluted.",".$oilin.",".$oilinheavy.",".$caco.",".$usbcpo.",".$usbpk.",".$hydrocyclone.",
                                        ".$dobicpo.",".$kernelpecah.",".$kerneljamolah.",".$kernelkapasitasolah.",".$limbah.",".$jampompa.",".$landaplikasi.")";
		}
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
					<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->sisatbskemarin."','".$bar->tbsmasuk."','".$bar->tbsdiolah."'
					,'".$bar->sisahariini."','".$bar->oer."','".$bar->kadarkotoran."','".$bar->kadarair."','".$bar->ffa."','".$bar->oerpk."','".$bar->kadarkotoranpk."'
					,'".$bar->kadarairpk."','".$bar->ffapk."','".$bar->usbbefore."','".$bar->usbafter."','".$bar->oildiluted."','".$bar->oilin."','".$bar->oilinheavy."'
					,'".$bar->caco."','".$bar->fruitineb."','".$bar->ebstalk."','".$bar->fibre."','".$bar->nut."','".$bar->effluent."','".$bar->soliddecanter."'
					,'".$bar->fruitinebker."','".$bar->cyclone."','".$bar->ltds."','".$bar->claybath."','".$bar->usbcpo."','".$bar->usbpk."','".$bar->hydrocyclone."'
					,'".$bar->dobicpo."','".$bar->kernelpecah."','".$bar->kerneljamolah."','".$bar->kernelkapasitasolah."','".$bar->limbah."','".$bar->jampompa."'
					,'".$bar->landaplikasi."')\">&nbsp

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
