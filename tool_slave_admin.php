<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=$_POST['method'];
if(isset($_POST['method3']))
{
$method=$_POST['method3'];
}
$listTransaksi=		isset($_POST['listTransaksi'])? explode(",",$_POST['listTransaksi']): array();
$listTransaksi2=	isset($_POST['listTransaksi2'])? explode(",",$_POST['listTransaksi2']): array();
$pilUn_1=	isset($_POST['pilUn_1'])? $_POST['pilUn_1']: '';
$pilUn_5=	isset($_POST['pilUn_5'])? $_POST['pilUn_5']: '';
$unitId=	isset($_POST['unitId'])? $_POST['unitId']: '';
$periodeId=	isset($_POST['periodeId'])? $_POST['periodeId']: '';
$bloklama=	isset($_POST['bloklama'])? $_POST['bloklama']: '';
$blokbaru=	isset($_POST['blokbaru'])? $_POST['blokbaru']: '';
$no=0;

foreach($listTransaksi as $dtr=>$lst)
{
    $no++;
    if($no==1)
    {
        $notrans="'".$lst."'";
    }
    else
    {
        $notrans.=",'".$lst."'";
    }
}
$no=0;
foreach($listTransaksi2 as $dtr=>$lst)
{
    $no++;
    if($no==1)
    {
        $notrans2="'".$lst."'";
    }
    else
    {
        $notrans2.=",'".$lst."'";
    }
}
switch($method)
{
    case'blokganti':
        $bloklama=$_POST['bloklama'];
        $blokbaru=  strtoupper($_POST['blokbaru']);

        if(substr($bloklama,0,4)!=substr($blokbaru,0,4))
        {
            exit("Error: Tidak boleh ganti kebun");
        }
        $udahada="";        
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$blokbaru."' ";
        $res=mysql_query($str);
        $optKebun='';
        while($bar=mysql_fetch_object($res))
        {
            $udahada="Blok ".$bar->kodeorganisasi." (".$bar->namaorganisasi.") sudah ada.";
        }

        if($udahada==""){
            $apdet="UPDATE ".$dbname.".`organisasi` SET `induk` = '".substr($blokbaru,0,6)."' WHERE `kodeorganisasi` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`organisasi` SET `namaorganisasi` = '".$blokbaru."' WHERE `kodeorganisasi` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`organisasi` SET `kodeorganisasi` = '".$blokbaru."' WHERE `kodeorganisasi` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`setup_blok` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            // 20140506, dz
            $apdet="UPDATE ".$dbname.".`setup_blok_tahunan` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));

            // 20140507, dz
            $apdet="UPDATE ".$dbname.".`bgt_blok` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`bgt_budget` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            
            $apdet="UPDATE ".$dbname.".`bibitan_mutasi` SET `afdeling` = '".$blokbaru."' WHERE `afdeling` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_5bjr` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_crossblock_ht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_pakaimaterial` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            //$apdet="UPDATE ".$dbname.".`kebun_peta` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            //if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_prestasi` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
//            $apdet="UPDATE ".$dbname.".`kebun_qc_ancakht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
//            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
//            $apdet="UPDATE ".$dbname.".`kebun_qc_kondisitbdt` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
//            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
//            $apdet="UPDATE ".$dbname.".`kebun_qc_kondisitbmdt` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
//            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
//            $apdet="UPDATE ".$dbname.".`kebun_qc_panenht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
//            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_rekomendasipupuk` SET `blok` = '".$blokbaru."' WHERE `blok` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_rencanapanen` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_restan` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_sisip` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`kebun_spbdt` SET `blok` = '".$blokbaru."' WHERE `blok` = '".$bloklama."'"; // ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            // 20140506, dz
            $apdet="UPDATE ".$dbname.".`kebun_taksasi` SET `blok` = '".$blokbaru."' WHERE `blok` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`keu_jurnaldt` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'"; // ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`keu_kasbankdt` SET `orgalokasi` = '".$blokbaru."' WHERE `orgalokasi` = '".$bloklama."'"; // ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`keu_penagihanht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'";
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`log_baspk` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'"; // ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`log_baspk` SET `blokspkdt` = '".$blokbaru."' WHERE `blokspkdt` = '".$bloklama."'"; //  ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`log_spkdt` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'"; //  ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`log_transaksidt` SET `kodeblok` = '".$blokbaru."' WHERE `kodeblok` = '".$bloklama."'"; //  ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`qc_ht` SET `kodeorg` = '".$blokbaru."' WHERE `kodeorg` = '".$bloklama."'"; //  ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
            $apdet="UPDATE ".$dbname.".`vhc_rundt` SET `alokasibiaya` = '".$blokbaru."' WHERE `alokasibiaya` = '".$bloklama."'"; // ga keupdate?
            if(mysql_query($apdet))        echo"";        else        echo " Gagal,".addslashes(mysql_error($conn));
        } else {
            echo "error: ".$udahada;
        }
		break;
	
    case'getData':
        if($pilUn_1==1 or $pilUn_1==4)//Kas dan Kebun
        {
            if($_POST['listTransaksi']!='')
            {
				$whr=" noreferensi in (".$notrans.") ";
				if($unitId!='') {
					$whr.="and nojurnal like '%".$unitId."%'";
				}
				if($periodeId!='')
				{
					$whr.="and tanggal like '%".$periodeId."%'";
				}
            }
            else
            {
				if($unitId!='')  
				{
					$whr.="nojurnal like '%".$unitId."%'";
				}
				if($periodeId!='')
				{
					$whr.="and tanggal like '%".$periodeId."%'";
				}
            }
            
			$sKasBank="select distinct noreferensi as notransaksi,nojurnal,tanggal from ".$dbname.".keu_jurnalht
					  where ".$whr." ";
			$qKasBank=mysql_query($sKasBank) or die(mysql_error($conn));
        }
        else if($pilUn_1==5)// Traksi
        {
            if($_POST['listTransaksi']!='')
            {
                $whr="notransaksi in (".$notrans.") ";
                if($unitId!='')  
                {
					$whr.="and kodeorg='".$unitId."'";
                }
				if($periodeId!='')
				{
					$whr.="and tanggal like '%".$periodeId."%'";
				}
            }
            else
            {
                if($unitId!='')  
                {
                  $whr.="kodeorg='".$unitId."'";
                }
				if($periodeId!='')
				{
					$whr.="and tanggal like '%".$periodeId."%'";
				}
            }
            $sTraksi="select distinct notransaksi,tanggal from ".$dbname.".vhc_runht 
                      where ".$whr." and posting=1";
            //echo $sTraksi;
            $qKasBank=mysql_query($sTraksi) or die(mysql_error($conn));
        }
        else if($pilUn_1==3){// SPK
            $whr="notransaksi in (".$notrans.") ";
			if($unitId!='')  
			{
			  $whr.=" and blokspkdt like '".$unitId."%'";
			}
			$sTraksi="select distinct notransaksi,blokspkdt,tanggal,kodekegiatan from ".$dbname.".log_baspk
				where ".$whr." and statusjurnal=1";
            //echo $sTraksi;
            $qKasBank=mysql_query($sTraksi) or die(mysql_error($conn));               
        }
		if($pilUn_1!=3){ 
			$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
			$tab.="<td>".$_SESSION['lang']['nojurnal']."</td>";
			$tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
			$tab.="<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
	
			$nor=0;
                                                            if($qKasBank){
                                                                while($rData=mysql_fetch_assoc($qKasBank))
                                                                {
                                                                        $nor++;

                                                                        $tab.="<tr class=rowcontent>";
                                                                        $tab.="<td id=notransaks_".$nor.">".$rData['notransaksi']."</td>";
                                                                        $tab.="<td id=nojurnal_".$nor.">".$rData['nojurnal']."</td>";
                                                                        $tab.="<td id=tgl_".$nor.">".$rData['tanggal']."</td>";
                                                                        $tab.="<td><input type=checkbox id=act_".$nor." /></td>";
                                                                        $tab.="</tr>";
                                                                }
                                                            }   
		}
		else{//SPK============
			$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
			$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
			$tab.="<td>".$_SESSION['lang']['kodeblok']."</td>";
			$tab.="<td>".$_SESSION['lang']['kodekegiatan']."</td>";
			$tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
			$tab.="<td><input type=checkbox id=allCheck onclick=checkAll() /></td></tr></thead><tbody id=dataIsi>";
                                                             if($qKasBank){
                                                                while($rData=mysql_fetch_assoc($qKasBank))
                                                                {
                                                                        $nor++;

                                                                        $tab.="<tr class=rowcontent>";
                                                                        $tab.="<td id=notransaks_".$nor.">".$rData['notransaksi']."</td>";
                                                                        $tab.="<td id=blokspkdt_".$nor.">".$rData['blokspkdt']."</td>";
                                                                        $tab.="<td id=kodekegiatan_".$nor.">".$rData['kodekegiatan']."</td>";            
                                                                        $tab.="<td id=tgl_".$nor.">".$rData['tanggal']."</td>";
                                                                        $tab.="<td><input type=checkbox id=act_".$nor." /></td>";
                                                                        $tab.="</tr>";
                                                                }
                                                             }
		}
        $tab.="<tr><td colspan=4 align=center><button class=mybutton onclick=unPosting()>unposting</button><button class=mybutton onclick=unlockForm()>batal</button></td></tr>";
        $tab.="</tbody></table>";       
        echo $tab;
        break;
	
    case'unposting':
		switch($pilUn_1)
		{
			case'1':
			foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
			{
				#periksa apakah unit RK lain sudah tutup buku
				$comment='';
				$str="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";                      
				$res=mysql_query($str);
				while($bar=mysql_fetch_object($res)){
					$unit=split("/",$bar->nojurnal);
					#periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unit[1]."' and periode='".substr($bar->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}
				}
				if($comment!=''){
					exit(" Error :".$comment."\n process canceled");
				}
				
				$sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
				if(mysql_query($sDel))
				{
				$sUp="update ".$dbname.".keu_kasbankht set posting=0 where notransaksi='".$bsdlis."' ";
				mysql_query($sUp) or die(mysql_error($conn));
				}
				else
				{
				echo"Gagal".mysql_error($conn);
				}
			}
			break;
		
        case'2':
			foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
			{
			 
				#periksa apakah unit RK lain sudah tutup buku
				$comment='';
				$str="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
				$res=mysql_query($str);
				while($bar=mysql_fetch_object($res)){
					$unit=split("/",$bar->nojurnal);
					#periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unit[1]."' and periode='".substr($bar->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}
				}
				if($comment!=''){
					exit(" Error :".$comment."\n process canceled");
				}

                $sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
				if(mysql_query($sDel))
				{
				$sUp="update ".$dbname.".kebun_aktifitas set jurnal=0 where notransaksi='".$bsdlis."'";
				mysql_query($sUp) or die(mysql_error($conn));
				}
				else
				{
				echo"Gagal".mysql_error($conn);
				}
			}
			break;
		
		case'3':
			foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
			{
				#periksa apakah unit RK lain sudah tutup buku
				$comment='';
				$str="select a.nojurnal,a.tanggal from ".$dbname.".keu_jurnaldt a 
					left join ".$dbname.".log_baspk b on a.noreferensi = b.notransaksi and a.tanggal = b.tanggal
					where a.noreferensi like '%".$bsdlis."%' and " 
					. "b.kodeblok='".$_POST['blokspkdt'][$dtList]."' and 
					a.kodekegiatan='".$_POST['kodekegiatan'][$dtList]."' and 
					b.tanggal = '".$_POST['tanggal'][$dtList]."'";
				$res=mysql_query($str);
				while($bar=mysql_fetch_object($res)){
					$unit=split("/",$bar->nojurnal);
					#periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unit[1]."' and periode='".substr($bar->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}
				}
				if($comment!=''){
					exit(" Error :".$comment."\n process canceled");
				}
				$str="select a.nojurnal,a.tanggal from ".$dbname.".keu_jurnaldt a 
					left join ".$dbname.".log_baspk b on a.noreferensi = b.notransaksi and a.tanggal = b.tanggal
					where a.noreferensi like '%".$bsdlis."%' and "
					. "b.kodeblok='".$_POST['blokspkdt'][$dtList]."' and 
					a.kodekegiatan='".$_POST['kodekegiatan'][$dtList]."' and 
					b.tanggal = '".$_POST['tanggal'][$dtList]."'";
				$res=mysql_query($str);
				while($bar=mysql_fetch_object($res)){
						$sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$bar->nojurnal."'";
							if(mysql_query($sDel))
							{
							}
				}
				$sUp="update ".$dbname.".log_baspk set statusjurnal=0,posting=0 where notransaksi='".$bsdlis."' and "
				. "blokspkdt='".$_POST['blokspkdt'][$dtList]."' and kodekegiatan='".$_POST['kodekegiatan'][$dtList]."' and tanggal='".$_POST['tanggal'][$dtList]."'";
				mysql_query($sUp) or die(mysql_error($conn));
			}  
			break;
		
        /***********************************************************************
         ** 4: Unposting BKM ***************************************************
         ***********************************************************************/
		case'4':
        foreach($_POST['notransaksi'] as $dtList=>$bsdlis){
            $drcek=explode("/",$_POST['nojurnal'][$dtList]);
                if($drcek[2]=='INVK1'){
                #================================
                  //unposting gudang dilarang
                  echo "Gagal: Maaf, unposting BKM yang memiliki Material tidak dapat digunakan, menjaga konsistensi harga rata2";
                  exit();
                }
        }

			foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
			{
				# [VALIDASI] periksa apakah unit RK lain sudah tutup buku
				$comment='';
				$str="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
				$res=mysql_query($str);
				while($bar=mysql_fetch_object($res)){
					$unit=explode("/",$bar->nojurnal);
					#periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unit[1]."' and periode='".substr($bar->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}
				}
				if($comment!=''){
					exit(" Error :".$comment."\n process canceled");
				}
                
                $comment1='';
                # [VALIDASI] periksa gudang apakah sudah tutup buku
                $strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
                $ret=mysql_query($strt);
                while($bat=mysql_fetch_object($ret))
                {
                   #periksa apakah sudah tutup buku
                   $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
                   $resu=mysql_query($stu);
                   while($baru=mysql_fetch_object($resu)){
                       $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                   }                             
                }
                if($comment1!=''){
                    exit(" Error :".$comment1."\n process canceled");
                }
                
				// $drcek=explode("/",$_POST['nojurnal'][$dtList]);
				// if($drcek[2]=='INVK1'){
				// #================================
				//   //unposting gudang dilarang
				//   echo "Gagal: Maaf, unposting BKM yang memiliki Material tidak dapat digunakan, menjaga konsistensi harga rata2";
				//   exit();
				// #================================
				// 	//ambil array barang dan saldonya di bkm... add di log_5saldobulanan saldo akhir ditambahin, pengeluaran di kurangin..dan apdtae
				// 	//nilai saldo akhir dan qtykeluar harga
				// 	//update log_5masterbarangdt sesuai dengan saldo akhir
				// 	$sbarang="select kodebarang,kwantitas,kodegudang,left(tanggal,7) as periode from ".$dbname.".kebun_pakai_material_vw
				// 			 where notransaksi='".$bsdlis."'";
				// 	//exit("error:".$sbarang);
				// 	$qbarang=mysql_query($sbarang);
				// 	$rowde=mysql_num_rows($qbarang);
				// 	if($rowde==0)
				// 	{
				// 		continue;
				// 	}
				// 	else
				// 	{
    //                     while($rbarang=  mysql_fetch_assoc($qbarang))
				// 		{
				// 			$slogblnan="select distinct saldoakhirqty,hargarata,qtykeluar from ".$dbname.".log_5saldobulanan
				// 					   where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
				// 					and kodegudang='".$rbarang['kodegudang']."'";
				// 			//exit("Error:".$slogblnan);
				// 			$rett=mysql_query($slogblnan) or die(mysql_error($conn));
				// 			$hsldr=mysql_fetch_assoc($rett);
				// 			$saldoAkhir=$hsldr['saldoakhirqty'];
				// 			$hrgrata=$hsldr['hargarata'];
				// 			$qtykelr=$hsldr['qtykeluar'];
							
				// 			$supdate="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(".$saldoAkhir."+".$rbarang['kwantitas']."),
				// 					qtykeluar=(".$qtykelr."-".$rbarang['kwantitas']."),nilaisaldoakhir=(".$saldoAkhir."+".$rbarang['kwantitas'].")*".$hrgrata.",
				// 					qtykeluarxharga=(".$qtykelr."-".$rbarang['kwantitas'].")*".$hrgrata."
				// 					where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
				// 					and kodegudang='".$rbarang['kodegudang']."'";
				// 			//exit("Error:".$supdate);
				// 			if(mysql_query($supdate)){
				// 				$slogblnan="select distinct saldoakhirqty from ".$dbname.".log_5saldobulanan
				// 						   where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
				// 						and kodegudang='".$rbarang['kodegudang']."'";
				// 				//exit("Error:".$slogblnan);
				// 				$rett=mysql_query($slogblnan);
				// 				$rowd=mysql_num_rows($rett);
				// 				if($rowd!=0)
				// 				{
				// 					$rdta=mysql_fetch_assoc($rett);
				// 					$supdatedata="update ".$dbname.".log_5masterbarangdt set saldoqty='".$rdta['saldoakhirqty']."' where
				// 								kodebarang='".$rbarang['kodebarang']."' and kodegudang='".$rbarang['kodegudang']."'";
				// 					if(mysql_query($supdatedata)){
				// 						//bersihkan jurnal sesuai dengan notransaksi
				// 						$sdel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
				// 						if(!mysql_query($sdel)){
				// 							echo"Gagal".mysql_error($conn);
				// 						}
                                        
				// 						//hapus log transaksiht
				// 						$sdel2="delete from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
				// 						if(!mysql_query($sdel2)){
				// 							echo"Gagal".mysql_error($conn);
				// 						}

				// 						//update kebun_aktivitas
				// 						$supak="update ".$dbname.".kebun_aktifitas set jurnal=0 where notransaksi='".$bsdlis."' ";
				// 						if(!mysql_query($supak)){
				// 							echo"Gagal".mysql_error($conn);
				// 						}
				// 					}
				// 				}
				// 			} else {
				// 				exit("warning : db error".mysql_error($conn).$supdate);
				// 			}
				// 		}
				// 	}
				// } else {
					//bersihkan jurnal sesuai dengan notransaksi
					$sdel="delete from ".$dbname.".keu_jurnalht where noreferensi='".$bsdlis."'";
					if(!mysql_query($sdel)){
						echo"Gagal".mysql_error($conn);
					}
					$comment1='';
					#periksa gudang apakah sudah tutup buku
					$strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
					$ret=mysql_query($strt);
					while($bat=mysql_fetch_object($ret))
					{
						#periksa apakah sudah tutup buku
						$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
						$resu=mysql_query($stu);
						while($baru=mysql_fetch_object($resu)){
							$comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
						}                             
					}
					if($comment1!=''){
						exit(" Error :".$comment1."\n process canceled");
					}
					
					//hapus log transaksiht
					$sdel2="delete from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
					if(!mysql_query($sdel2)){
						echo"Gagal".mysql_error($conn);
					}

					//update kebun_aktivitas
					$supak="update ".$dbname.".kebun_aktifitas set jurnal=0 where notransaksi='".$bsdlis."'";
					if(!mysql_query($supak)){
						echo"Gagal".mysql_error($conn);
					}
					
					// Get Kebun Prestasi
					$wherePres = "notransaksi = '".$bsdlis."'";
					$qPres = selectQuery($dbname,'kebun_prestasi',"*",$wherePres);
					$resPres = fetchData($qPres);
					
					// Cek transaksi sisip
					$where = "nilai = '".$resPres[0]['kodekegiatan']."' and kodeaplikasi = 'TN'"; // kalo kodeparameter SISIP
					$query = selectQuery($dbname,'setup_parameterappl','*',$where);
					$resSisip = fetchData($query);
					
					if(!empty($resSisip)) { // Jika transaksi sisip, maka update jumlah pokok
						// Ambil Blok Lama
						$qBlok = selectQuery($dbname,'setup_blok','jumlahpokok',
											 "kodeorg='".$resPres[0]['kodeorg']."'");
						$resBlok = fetchData($qBlok);
						
						// Update Blok Baru
						$dataUpd = array(
							'jumlahpokok' => $resBlok[0]['jumlahpokok'] - $resPres[0]['hasilkerja']
						);
						$updBlok = updateQuery($dbname,'setup_blok',$dataUpd,
											   "kodeorg='".$resPres[0]['kodeorg']."'");
						if(!mysql_query($updBlok)) {
							exit("Update Blok Error: ".mysql_error());
						}
					}
				//}						
/*                        
				$drcek=explode("/",$_POST['nojurnal'][$dtList]);
				//exit("error:".$drcek[2]);
				//bersihkan jurnal sesuai dengan notransaksi
				 $sdel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
				 if(!mysql_query($sdel)){
					 echo"Gagal".mysql_error($conn);
				 }
				$comment1='';
				 #periksa gudang apakah sudah tutup buku
				 $strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
				 $ret=mysql_query($strt);
				 while($bat=mysql_fetch_object($ret))
				 {
					#periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}                             
				 }
			   if($comment1!=''){
					exit(" Error :".$comment1."\n process canceled");
				}
				//hapus log transaksiht
				 $sdel2="delete from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
				 if(!mysql_query($sdel2)){
					 echo"Gagal".mysql_error($conn);
				 }
				 
				//update kebun_aktivitas
				$supak="update ".$dbname.".kebun_aktifitas set jurnal=0 where notransaksi='".$bsdlis."' ";
				 if(!mysql_query($supak)){
					 echo"Gagal".mysql_error($conn);
				 }
				 
				 if($drcek[2]=='INVK1'){
					//ambil array barang dan saldonya di bkm... add di log_5saldobulanan saldo akhir ditambahin, pengeluaran di kurangin..dan apdtae
					//nilai saldo akhir dan qtykeluar harga
					//update log_5masterbarangdt sesuai dengan saldo akhir
					 $sbarang="select kodebarang,kwantitas,kodegudang,substr(tanggal,0,7) as periode from ".$dbname.".kebun_pakai_material_vw
							  where notransaksi='".$bsdlis."'";

					 $qbarang=mysql_query($sbarang);
					 $rowde=mysql_num_rows($qbarang);
					 if($rowde==0)
					 {
						 continue;
					 }
					 else
					 {
						 while($rbarang=  mysql_fetch_assoc($qbarang))
						 {
							 $slogblnan="select distinct saldoakhirqty,hargarata,qtykeluar from ".$dbname.".log_5saldobulanan
										where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
									 and kodegudang='".$rbarang['kodegudang']."'";
							 //exit("Error:".$slogblnan);
							 $rett=mysql_query($slogblnan) or die(mysql_error($conn));
							 $hsldr=mysql_fetch_assoc($rett);
							 $saldoAkhir=$hsldr['saldoakhirqty'];
							 $hrgrata=$hsldr['hargarata'];
							 $qtykelr=$hsldr['qtykeluar'];
							 
							 $supdate="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(".$saldoAkhir."+".$rbarang['kwantitas']."),
									 qtykeluar=(".$qtykelr."-".$rbarang['kwantitas']."),nilaisaldoakhir=(".$saldoAkhir."+".$rbarang['kwantitas'].")*".$hrgrata.",
									 qtykeluarxharga=(".$qtykelr."-".$rbarang['kwantitas'].")*".$hrgrata."
									 where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
									 and kodegudang='".$rbarang['kodegudang']."'";
							 //exit("Error:".$supdate);
							 mysql_query($supdate);
							 $slogblnan="select distinct saldoakhirqty from ".$dbname.".log_5saldobulanan
										where kodebarang='".$rbarang['kodebarang']."' and periode='".$rbarang['periode']."'
									 and kodegudang='".$rbarang['kodegudang']."'";
							 //exit("Error:".$slogblnan);
							 $rett=mysql_query($slogblnan);
							 $rowd=mysql_num_rows($rett);
							 if($rowd!=0)
							 {
								 $rdta=mysql_fetch_assoc($rett);
								 $supdatedata="update ".$dbname.".log_5masterbarangdt set saldoqty='".$rdta['saldoakhirqty']."' where
											 kodebarang='".$rbarang['kodebarang']."' and kodegudang='".$rbarang['kodegudang']."'";
								 mysql_query($supdatedata);
							 }
						 }
					 }
				 }
*/                        
			}
			break;
		
		case'5':#traksi
			foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
			{
				$comment2='';
				$stry="select kodeorg,tanggal from ".$dbname.".vhc_runht where notransaksi='".$bsdlis."'";
				$rey=mysql_query($stry);
				while($bat=mysql_fetch_object($rey)){
					 #periksa apakah sudah tutup buku
					$stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$bat->kodeorg."' and periode='".substr($bat->tanggal,0,7)."'";
					$resu=mysql_query($stu);
					while($baru=mysql_fetch_object($resu)){
						$comment2.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
					}    
				 }    
			   if($comment2!=''){
					exit(" Error :".$comment2."\n process canceled");
				}                         
				$sUp="update ".$dbname.".vhc_runht set posting=0  where notransaksi='".$bsdlis."'";
				if(!mysql_query($sUp))
				{
					echo"Gagal".mysql_error($conn);
				}
			}  
			break;
			default:
			break;
		}
        break;
	
    case'delData':
        $sDel="delete from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
        if(!mysql_query($sDel))
        {
                echo"Gagal".mysql_error($conn);
        }
        break;
        case'getData':
        $sDt="select * from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
        $qDt=mysql_query($sDt) or die(mysql_error($conn));
        $rDet=mysql_fetch_assoc($qDt);
        echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['contact']."###".$rDet['handphone']."###".$rDet['status'];
        break;
       case 'getBlok':
//           $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unitId."%' and length(kodeorganisasi)=10 order by kodeorganisasi";
           $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unitId."%' and tipe = 'BLOK' order by kodeorganisasi";
           $res=mysql_query($str);
           while($bar=mysql_fetch_object($res)){
               $opt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." ".$bar->namaorganisasi."</option>";
           }
           echo $opt;
        break;
 case 'getPeriodeOClose':
     $str="select periode from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['unit']."' order by periode desc";
     $res=mysql_query($str);
     $z=0;
     $last='';
     while($bar=mysql_fetch_object($res))
     {
         if($z==0){
          #penambah periode             
             $last=$bar->periode;
            for($u=10;$u>=1;$u--)
            {
                 $st=mktime(0,0,0,intval(substr($last,5,2))+$u,15,intval(substr($last,0,4)));
                 $stream.="<option value='".date('Y-m',$st)."'>".date('Y-m',$st)."</option>";
            }             
         }
         $stream.="<option value='".$bar->periode."'>".$bar->periode."</option>";
         $z++;
     }
     
     echo "<select id=dariperiode>".$stream."</select> to <select id=sampaiperiode>".$stream."</select>";
      break;
case 'openCloseMethod':
        if($_POST['tipe']=='OPEN')
        {
            #periksa apakah ada periode terkecil
            $str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$_POST['dariperiode']."'
                      and kodeorg='".$_POST['unitopenclose']."'";

            $res=mysql_query($str);
            if(mysql_num_rows($res)<1)
            {
                echo "Error : Periode terkecil tersebut belum terdaftar pada periode akuntansi";
            }
            else{
                #1 task hapus jurnal CLS
                $str="delete from ".$dbname.".keu_jurnalht where nojurnal like '%/".$_POST['unitopenclose']."/CLS%'
                          and left(tanggal,7)>='".$_POST['dariperiode']."' and  left(tanggal,7)<='".$_POST['sampaiperiode']."'"; 
               // exit(" error:".$str);
                if(mysql_query($str)){
                        #buka periode akuntansi
                        $str=" update ".$dbname.".setup_periodeakuntansi set tutupbuku=0 where kodeorg='".$_POST['unitopenclose']."'
                                   and periode='".$_POST['dariperiode']."'";
                         if(mysql_query($str)){
                                $str=" delete from  ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['unitopenclose']."'
                                           and periode>'".$_POST['dariperiode']."'";   
                              if(mysql_query($str)){}
                              else{
                                      echo " Error deleting period:".addcslashes(mysql_error($conn));
                              }                                           
                         }
                         else
                         {
                                 echo " Error Updating smalest period:".addcslashes(mysql_error($conn));
                         }    
                }
                else
                {
                    echo " Error deleting CLM:".addcslashes(mysql_error($conn));
                }
            }
        }  
        if($_POST['tipe']=='CLOSE')
        {
            #periksa periode terakhir dari unit ybs
            $curperiode='';
            $str="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 
                     and kodeorg='".$_POST['unitopenclose']."' order by periode desc limit 1";
            $res=mysql_query($str);
            while($bar=  mysql_fetch_object($res))
            {
                $curperiode=$bar->periode;
            }
            if($curperiode==$_POST['dariperiode']){
                    #mengubah session, kemudian pada response terakhir akan dibawa logout
                    $_SESSION['empl']['lokasitugas']=$_POST['unitopenclose'];
                   #================================================== 
                    $zz=$_POST['dariperiode'];
                    $list=$_POST['dariperiode'];
                   while($zz<$_POST['sampaiperiode'])
                   {
                       $st=mktime(0,0,0,intval(substr($zz,5,2))+1,15,intval(substr($_POST['dariperiode'],0,4)));
                       $zz=date('Y-m',$st);
                       $list.="#".$zz;              
                   }
                   echo $list;
           }
           else
           {
               echo" Error: Periode terakhir tidak sama dengan periode awal yang dipilih, mohon diperiksa kembali";
           }
        }        
      break;
      case'getData2':
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
        $tab.="<td>".$_SESSION['lang']['nojurnal']."</td>";
        $tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $tab.="<td><input type=checkbox id=allCheck2 onclick=checkAll2() /></td></tr></thead><tbody id=dataIsi2>";
          $sData="select distinct * from ".$dbname.".keu_jurnalht where noreferensi in (".$notrans2.")";
           //exit("Error:".$sData);
          $qData=mysql_query($sData) or die(mysql_error($conn));
          while($rData=mysql_fetch_assoc($qData)){
              $nor++;
        $tab.="<tr class=rowcontent>";
        $tab.="<td id=notransaks_".$nor.">".$rData['noreferensi']."</td>";
        $tab.="<td id=nojurnal_".$nor.">".$rData['nojurnal']."</td>";
        $tab.="<td id=tgl_".$nor.">".$rData['tanggal']."</td>";
        $tab.="<td><input type=checkbox id=trans_".$nor." /></td>";
        $tab.="</tr>";
          }
          $tab.="</tbody></table>";
          $tab.="<button class=mybutton id=tmblDt onclick=unpostingGudang()>Unposting</button>";
          echo $tab;
      break;
      case'unpostingGudang':
			#================================
			  //--- unposting gudang dilarang
			  //echo "Gagal: Maaf, unposting gudang tidak dapat digunakan, menjaga konsistensi harga rata2";
			  //exit();
			#================================
          switch($pilUn_5){
          case'1':
              foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
              {
                     
                    $tgl=substr($_POST['tanggal'][$dtList],0,7);
                    $unit=substr($bsdlis,-6,6);
                    //exit("error:".$unit);
                    $scek="select distinct tutupbuku from ".$dbname.".setup_periodeakuntansi 
                           where periode='".$tgl."' and kodeorg='".$unit."'";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    $rcek=mysql_fetch_assoc($qcek);
                    if($rcek['tutupbuku']==1)
                    {
                        exit("error:periode akuntansi sudah di tutup");
                    }
                    
                        $sBrg="select distinct kodebarang,notransaksi,jumlah from ".$dbname.".log_transaksidt where notransaksi='".$bsdlis."' and statussaldo=1";
                        $qBrg=mysql_query($sBrg) or die(mysql_error($conn));
                        while($rBrg=  mysql_fetch_assoc($qBrg)){
                            
                            $supd="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(saldoakhirqty-".$rBrg['jumlah']."),
                                   nilaisaldoakhir=(saldoakhirqty-".$rBrg['jumlah'].")*hargarata,qtymasuk=(qtymasuk-".$rBrg['jumlah']."),
                                   qtymasukxharga=(qtymasuk-".$rBrg['jumlah'].")*hargarata where periode='".$tgl."' and kodegudang='".$unit."'
                                   and kodebarang='".$rBrg['kodebarang']."'";
                            if(mysql_query($supd))
                            {
                                $supd="update ".$dbname.".log_5masterbarangdt set saldoqty=saldoqty-".$rBrg['jumlah']."
                                       where kodegudang='".$unit."' and kodebarang='".$rBrg['kodebarang']."'";
                                if(!mysql_query($supd))
                                {
                                    echo"Gagal".mysql_error($conn)."____".$supd;
                                }
                            }
                            else
                            {
                                
                                 echo"Gagal".mysql_error($conn)."____".$supd;
                            }
                            
                        }
                        $supd="update ".$dbname.".log_transaksidt set statussaldo=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd;
                        }
                        $supd2="update ".$dbname.".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd2))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd2;
                        }
                     
                    $sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
                    if(!mysql_query($sDel))
                    {
                    
                    echo"Gagal".mysql_error($conn);
                    }
              }
          break;
          case'5':
           foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
              {
                        $comment1='';
                         #periksa gudang apakah sudah tutup buku
                         $strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksireferensi='".$bsdlis."'";
                         $ret=mysql_query($strt);
                         while($bat=mysql_fetch_object($ret))
                         {
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }   
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".substr($bat->kodegudang,0,4)."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }                            
                         }
                        $str="select nojurnal,tanggal from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."'";
                        $res=mysql_query($str);
                        while($bar=mysql_fetch_object($res)){
                            $unit=split("/",$bar->nojurnal);
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg='".$unit[1]."' and periode='".substr($bar->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }
                        }                         
                       if($comment1!=''){
                            exit(" Error :".$comment1."\n process canceled");
                        }
                        
                    $tgl=substr($_POST['tanggal'][$dtList],0,7);
                    $unit=substr($bsdlis,-6,6);
                    //exit("error:".$unit);
                    $scek="select distinct tutupbuku from ".$dbname.".setup_periodeakuntansi 
                           where periode='".$tgl."' and kodeorg='".$unit."'";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    $rcek=mysql_fetch_assoc($qcek);
                    if($rcek['tutupbuku']==1)
                    {
                        exit("error:periode akuntansi sudah di tutup");
                    }
                    
                        $sBrg="select distinct kodebarang,notransaksi,jumlah from ".$dbname.".log_transaksidt where notransaksi='".$bsdlis."' and statussaldo=1";
                        $qBrg=mysql_query($sBrg) or die(mysql_error($conn));
                        while($rBrg=  mysql_fetch_assoc($qBrg)){
                            
                            $supd="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(saldoakhirqty+".$rBrg['jumlah']."),
                                   nilaisaldoakhir=(saldoakhirqty+".$rBrg['jumlah'].")*hargarata,qtykeluar=(qtykeluar-".$rBrg['jumlah']."),
                                   qtykeluarxharga=(qtykeluar-".$rBrg['jumlah'].")*hargarata where periode='".$tgl."' and kodegudang='".$unit."'
                                   and kodebarang='".$rBrg['kodebarang']."'";
                            if(mysql_query($supd))
                            {
                                $supd="update ".$dbname.".log_5masterbarangdt set saldoqty=saldoqty+".$rBrg['jumlah']."
                                       where kodegudang='".$unit."' and kodebarang='".$rBrg['kodebarang']."'";
                                if(!mysql_query($supd))
                                {
                                    echo"Gagal".mysql_error($conn)."____".$supd;
                                }
                            }
                            else
                            {
                                
                                 echo"Gagal".mysql_error($conn)."____".$supd;
                            }
                            
                        }
                        $supd="update ".$dbname.".log_transaksidt set statussaldo=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd;
                        }
                        $supd2="update ".$dbname.".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd2))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd2;
                        }
                     
                    $sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
                    if(!mysql_query($sDel))
                    {
                        echo"Gagal".mysql_error($conn);
                    }
              }
          break;
          case'3':
             foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
              {
                        $comment1='';
                         #periksa gudang apakah sudah tutup buku
                         $strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksi='".$bsdlis."'";
                         $ret=mysql_query($strt);
                         while($bat=mysql_fetch_object($ret))
                         {
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }   
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".substr($bat->kodegudang,0,4)."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }                            
                         }
                       if($comment1!=''){
                            exit(" Error :".$comment1."\n process canceled");
                        }
                                    
                    $tgl=substr($_POST['tanggal'][$dtList],0,7);
                    $unit=substr($bsdlis,-6,6);
                    //exit("error:".$unit);
                    $scek="select distinct tutupbuku from ".$dbname.".setup_periodeakuntansi 
                           where periode='".$tgl."' and kodeorg='".$unit."'";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    $rcek=mysql_fetch_assoc($qcek);
                    if($rcek['tutupbuku']==1)
                    {
                        exit("error:periode akuntansi sudah di tutup");
                    }
                    
                        $sBrg="select distinct kodebarang,notransaksi,jumlah from ".$dbname.".log_transaksidt where notransaksi='".$bsdlis."' and statussaldo=1";
                        $qBrg=mysql_query($sBrg) or die(mysql_error($conn));
                        while($rBrg=  mysql_fetch_assoc($qBrg)){
                            
                            $supd="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(saldoakhirqty-".$rBrg['jumlah']."),
                                   nilaisaldoakhir=(saldoakhirqty-".$rBrg['jumlah'].")*hargarata,qtymasuk=(qtymasuk-".$rBrg['jumlah']."),
                                   qtymasukxharga=(qtymasuk-".$rBrg['jumlah'].")*hargarata where periode='".$tgl."' and kodegudang='".$unit."'
                                   and kodebarang='".$rBrg['kodebarang']."'";
                            if(mysql_query($supd))
                            {
                                $supd="update ".$dbname.".log_5masterbarangdt set saldoqty=saldoqty-".$rBrg['jumlah']."
                                       where kodegudang='".$unit."' and kodebarang='".$rBrg['kodebarang']."'";
                                if(!mysql_query($supd))
                                {
                                    echo"Gagal".mysql_error($conn)."____".$supd;
                                }
                            }
                            else
                            {
                                
                                 echo"Gagal".mysql_error($conn)."____".$supd;
                            }
                            
                        }
                        $supd="update ".$dbname.".log_transaksidt set statussaldo=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd;
                        }
                        $supd2="update ".$dbname.".log_transaksiht set post=0,statusjurnal=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd2))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd2;
                        }
                     
                    $sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
                    if(!mysql_query($sDel))
                    {
                        echo"Gagal".mysql_error($conn);
                    }
              }
          
          break;
          case'7':
              foreach($_POST['notransaksi'] as $dtList=>$bsdlis)
              {
                        $comment1='';
                         #periksa gudang apakah sudah tutup buku
                         $strt="select kodegudang,tanggal from ".$dbname.".log_transaksiht where notransaksi='".$bsdlis."'";
                         $ret=mysql_query($strt);
                         while($bat=mysql_fetch_object($ret))
                         {
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".$bat->kodegudang."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }   
                            #periksa apakah sudah tutup buku
                            $stu="select * from ".$dbname.".setup_periodeakuntansi where tutupbuku=1 and kodeorg ='".substr($bat->kodegudang,0,4)."' and periode='".substr($bat->tanggal,0,7)."'";
                            $resu=mysql_query($stu);
                            while($baru=mysql_fetch_object($resu)){
                                $comment1.="-".$baru->kodeorg." periode ".$baru->periode." has been closed\n";
                            }                            
                         }
                       if($comment1!=''){
                            exit(" Error :".$comment1."\n process canceled");
                        }
                               
                    $tgl=substr($_POST['tanggal'][$dtList],0,7);
                    $unit=substr($bsdlis,-6,6);
                    //exit("error:".$unit);
                    $scek="select distinct tutupbuku from ".$dbname.".setup_periodeakuntansi 
                           where periode='".$tgl."' and kodeorg='".$unit."'";
                    $qcek=mysql_query($scek) or die(mysql_error($conn));
                    $rcek=mysql_fetch_assoc($qcek);
                    if($rcek['tutupbuku']==1)
                    {
                        exit("error:periode akuntansi sudah di tutup");
                    }
                    $scekdua="select distinct notransaksireferensi from ".$dbname.".log_transaksi_vw where notransaksi='".$bsdlis."'";
                    $qcekdua=mysql_query($scekdua) or die(mysql_error($conn));
                    $rcekdua=mysql_fetch_assoc($qcekdua);
                    
                    $sdt="select distinct post,statusjurnal from ".$dbname.".log_transaksiht where notransaksi='".$rcekdua['notransaksireferensi']."'";
                    $qdt=mysql_query($sdt) or die(mysql_error($conn));
                    $rdt=mysql_fetch_assoc($qdt);
                    if($rdt['post']==1&&$rdt['statusjurnal']==1){
                        exit("error:Penerimaan notransaksi : ".$rdt['notransaksireferensi'].", sudah terposting, silakan lakukan unposting penerimaan terlebih dahulu");
                    }
                        $sBrg="select distinct kodebarang,notransaksi,jumlah from ".$dbname.".log_transaksidt where notransaksi='".$bsdlis."' and statussaldo=1";
                        $qBrg=mysql_query($sBrg) or die(mysql_error($conn));
                        while($rBrg=  mysql_fetch_assoc($qBrg)){
                            
                            $supd="update ".$dbname.".log_5saldobulanan set saldoakhirqty=(saldoakhirqty+".$rBrg['jumlah']."),
                                   nilaisaldoakhir=(saldoakhirqty+".$rBrg['jumlah'].")*hargarata,qtykeluar=(qtykeluar-".$rBrg['jumlah']."),
                                   qtykeluarxharga=(qtykeluar-".$rBrg['jumlah'].")*hargarata where periode='".$tgl."' and kodegudang='".$unit."'
                                   and kodebarang='".$rBrg['kodebarang']."'";
                            if(mysql_query($supd))
                            {
                                $supd="update ".$dbname.".log_5masterbarangdt set saldoqty=saldoqty+".$rBrg['jumlah']."
                                       where kodegudang='".$unit."' and kodebarang='".$rBrg['kodebarang']."'";
                                if(!mysql_query($supd))
                                {
                                    echo"Gagal".mysql_error($conn)."____".$supd;
                                }
                            }
                            else
                            {
                                
                                 echo"Gagal".mysql_error($conn)."____".$supd;
                            }
                            
                        }
                        $supd="update ".$dbname.".log_transaksidt set statussaldo=0
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd;
                        }
                        $supd2="update ".$dbname.".log_transaksiht set post=0,statusjurnal=0,notransaksireferensi=NULL
                               where notransaksi='".$bsdlis."'";
                        if(!mysql_query($supd2))
                        {
                            echo"Gagal".mysql_error($conn)."____".$supd2;
                        }
                     
                    $sDel="delete from ".$dbname.".keu_jurnalht where noreferensi like '%".$bsdlis."%'";
                    if(!mysql_query($sDel))
                    {
                        echo"Gagal".mysql_error($conn);
                    }
              }
          break;
          }
          
      break;
}
?>