<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$notransaksi_head=	isset($_POST['notrans'])?	$_POST['notrans']: '';
$notransaksi=		isset($_POST['noOptrans'])?	$_POST['noOptrans']: '';
$proses=			isset($_POST['proses'])?	$_POST['proses']: '';
$lokasi=			$_SESSION['empl']['lokasitugas'];
$jnsPekerjaan=		isset($_POST['jnsPekerjaan'])?	$_POST['jnsPekerjaan']: '';
$lokKerja=			isset($_POST['locationKerja'])?	$_POST['locationKerja']: '';
$muatan=			isset($_POST['muatan'])?	$_POST['muatan']: '';
$brtMuatan=			isset($_POST['brtmuatan'])?	$_POST['brtmuatan']: '';
$jmlhRit=			isset($_POST['jmlhRit'])?	$_POST['jmlhRit']: '';
$ket=				isset($_POST['ket'])?		$_POST['ket']: '';
$posisi=			isset($_POST['posisi'])?	$_POST['posisi']: '';
$kdKry=				isset($_POST['kdKry'])?		$_POST['kdKry']: '';
$oldjnsPekerjaan=	isset($_POST['oldjnsPekerjaan'])?	$_POST['oldjnsPekerjaan']: '';
$uphOprt=			isset($_POST['uphOprt'])?	$_POST['uphOprt']: '';
$prmiOprt=			isset($_POST['prmiOprt'])?	$_POST['prmiOprt']: '';
$pnltyOprt=			isset($_POST['pnltyOprt'])?	$_POST['pnltyOprt']: '';
$tglTrans=			isset($_POST['tglTrans'])?	tanggalsystem($_POST['tglTrans']): '';
$thnKntrk=			isset($_POST['thnKntrk'])?	$_POST['thnKntrk']: '';
$noKntrak=			isset($_POST['noKntrak'])?	$_POST['noKntrak']: '';
$biaya=				isset($_POST['biaya'])?		$_POST['biaya']: '';
$Blok=				isset($_POST['Blok'])?		$_POST['Blok']: '';
$segment=			isset($_POST['kodesegment'])?	$_POST['kodesegment']: '';
$oldSegment=		isset($_POST['oldSegment'])?	$_POST['oldSegment']: '';
$oldBlok=			isset($_POST['oldBlok'])?		$_POST['oldBlok']: '';
$old_lokKerja=		isset($_POST['old_lokKerja'])?	$_POST['old_lokKerja']: '';

$kmhmAwal=	isset($_POST['kmhmAwal'])?	$_POST['kmhmAwal']: '';
$kmhmAkhir=	isset($_POST['kmhmAkhir'])?	$_POST['kmhmAkhir']: '';
$satuan=	isset($_POST['satuan'])?	$_POST['satuan']: '';
if($notransaksi_head!='')
{
        $sKode="select kodeorg from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
        $qKode=mysql_query($sKode) or die(mysql_error());
        $rKode=mysql_fetch_assoc($qKode);
}
$optKdVhc=makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc',"notransaksi = '".$notransaksi_head."'");
switch($proses)
{
        case 'load_data_kerjaan':
        //echo "warning:masuk";	

        $sql="select a.*,b.namasegment from ".$dbname.".vhc_rundt a
			left join ".$dbname.".keu_5segment b on a.kodesegment=b.kodesegment
			where substring(notransaksi,1,4)='".$rKode['kodeorg']."' and notransaksi='".$notransaksi_head."' order by notransaksi desc";// echo $sql;
        $query=mysql_query($sql) or die(mysql_error());
		$no=0;
        while($res=mysql_fetch_assoc($query))
        {
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$res['notransaksi']."</td>
                <td>".$res['jenispekerjaan']."</td>
                <td>".$res['alokasibiaya']."</td>
				<td>".$res['namasegment']."</td>
                <td>".number_format($res['jumlahrit'],2)."</td>
                <td>".number_format($res['beratmuatan'],2)."</td>
                <td>".number_format($res['kmhmawal'],2)."</td>
                <td>".number_format($res['kmhmakhir'],2)."</td>
                 <td>".$res['satuan']."</td>
                <td>".number_format($res['biaya'],2)."</td>
                <td>".$res['keterangan']."</td>
                <td><img src=images/application/application_edit.png class=resicon  title='Edit' 
                onclick=\"fillFieldKrj('".$res['jenispekerjaan']."','".$res['alokasibiaya']."','". $res['beratmuatan']."','". $res['jumlahrit']."','". $res['keterangan']."','". $res['biaya']."','". $res['kmhmawal']."','". $res['kmhmakhir']."','". $res['satuan']."','".$res['kodesegment']."','".$res['namasegment']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDataKrj('". $res['notransaksi']."','". $res['jenispekerjaan']."','".$res['alokasibiaya']."','".$res['kodesegment']."');\" >	
                </td>
                </tr>
                ";
        }
        break;

        case'insert_pekerjaan':
			// Get Header
			$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',
								 "notransaksi = '".$notransaksi_head."'");
			$resHead = fetchData($qHead);
			if(empty($resHead)) exit("Warning: Data Header tidak ada");
			$resHead = $resHead[0];
			
			// Cek apakah kodevhc sudah ada di tanggal > tanggal input
			$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',
								"kodevhc = '".$resHead['kodevhc']."' and tanggal > '".$resHead['tanggal']."'");
			$resCek = fetchData($qCek);
			if(!empty($resCek[0]['tgl'])) {
				exit("Warning: Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
					 "\nTanggal transaksi terakhir ".tanggalnormal($resCek[0]['tgl']));
			}
			
			//Cek Jenis kegiatan
			$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a 
						left join ".$dbname.".setup_kegiatan b 
						on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') 
						where a.kodekegiatan='".$jnsPekerjaan."'";
			$qAlokasi = mysql_query($sAlokasi) or die(mysql_error($conn));
			$rAlokasi = mysql_fetch_assoc($qAlokasi);
			
			if($notransaksi_head=='')
			{
				echo"warning: please confirm heade first";
				exit();
			}
			if($jnsPekerjaan=='')
			{
				echo"warning: Activity required";
				exit();
	
			}
			if($lokKerja=='')
			{
				echo"warning: Cost allocation (block) required";
				exit();
	
			}
			if($rAlokasi['countkelompok'] != 0){
				if($Blok == ''){
					echo "warning : Blok harus dipilih.";
					exit();
				}
			}
			if($kmhmAwal>=$kmhmAkhir)
			{
					echo"warning:".$_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']."";
					exit();
			}
			$jumlah=$kmhmAkhir-$kmhmAwal;
			$sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
	
			$qCekHt=mysql_query($sCekHt) or die(mysql_error());
			$rCekHt=mysql_num_rows($qCekHt);
			if($rCekHt<1)
			{
					echo"warning: Header required";
					exit();
			}
	
			if($Blok!='')
			{
				if(strlen($Blok)<10)
				{
					exit("Error: Block required");
				}
					$lokKerja=$Blok;
			}
	
			if($biaya=='')
				$biaya=0;
			$sins="insert into ".$dbname.".vhc_rundt (`notransaksi`,`jenispekerjaan`,`alokasibiaya`,`beratmuatan`,`jumlahrit`,`keterangan`,`biaya`,`kmhmawal`,
					`kmhmakhir`,`jumlah`,`satuan`,`kodesegment`) 
					values ('".$notransaksi_head."','".$jnsPekerjaan."','".$lokKerja."','".$brtMuatan."','".$jmlhRit."','".$ket."'
					,'".$biaya."','".$kmhmAwal."','".$kmhmAkhir."','".$jumlah."','".$satuan."','".$segment."')";
	
			if(mysql_query($sins))
			{
				$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$notransaksi_head]."'";
				$qKm=mysql_query($sKm) or die(mysql_error($conn));
				$rKm=mysql_fetch_assoc($qKm);
				
				updateKmHm($optKdVhc[$notransaksi_head]);
				echo intval($rKm['kmhmakhir']);
			}
			else
			{	echo "DB Error : ".mysql_error($conn);	 }
            break;

        case'update_kerja':
			$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a 
						left join ".$dbname.".setup_kegiatan b 
						on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') 
						where a.kodekegiatan='".$jnsPekerjaan."'";
			$qAlokasi = mysql_query($sAlokasi) or die(mysql_error($conn));
			$rAlokasi = mysql_fetch_assoc($qAlokasi);
			
			if(($brtMuatan=='')||($jmlhRit==''))
			{
					echo"warning:Please Complete The Form";
					exit();
			}
			// exit("error : ".$rAlokasi['countkelompok']);
			if($rAlokasi['countkelompok'] != 0){
				if($Blok == ''){
					echo "warning : Blok harus dipilih.";
					exit();
				}
			}
			
			if($Blok!='')
			{
				$lokKerja=$Blok;
				if(!empty($oldBlok) and $lokKerja!=$oldBlok)
				{
						$where.=" and alokasibiaya='".$oldBlok."'";
				}
				else
				{
					if($old_lokKerja!=$lokKerja)
					{
						$where.=" and alokasibiaya='".$old_lokKerja."'";
					} else {
						$where.=" and alokasibiaya='".$lokKerja."'";
					}
				}
			}
			else
			{
					if($old_lokKerja!=$lokKerja)
					{
							$where.=" and alokasibiaya='".$old_lokKerja."'";
					}
					else
					{
							$where.=" and alokasibiaya='".$lokKerja."'";
					}
			}
			if($oldjnsPekerjaan!='')
			{
					if($jnsPekerjaan!=$oldjnsPekerjaan)
					{
							$where.="  and jenispekerjaan='".$oldjnsPekerjaan."'";
					}
					else
					{
							$where.="  and jenispekerjaan='".$jnsPekerjaan."'";
					}
			}
			if(!empty($segment)) {
				$where.="  and kodesegment='".$oldSegment."'";
			}
			if($kmhmAwal>=$kmhmAkhir)
			{
					echo"warning:".$_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']."";
					exit();
			}
			
			// Get Prev Data
			$qData = selectQuery($dbname,'vhc_rundt','*',
								 "notransaksi='".$notransaksi_head."' ".$where);
			$resData = fetchData($qData);
			
			// All Detail in Transaksi
			$qKm = selectQuery($dbname,'vhc_rundt','max(kmhmakhir) as kmakhir',
								 "notransaksi='".$notransaksi_head."'");
			$resKm = fetchData($qKm);
			if($resKm[0]['kmakhir']>$resData[0]['kmhmakhir'] and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
				exit("Warning: Transaksi yang bukan terakhir tidak boleh diubah KM / HM Akhir");
			}
			
			// Get Header
			$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',
								 "notransaksi = '".$notransaksi_head."'");
			$resHead = fetchData($qHead);
			if(empty($resHead)) exit("Warning: Data Header tidak ada");
			$resHead = $resHead[0];
			
			// Cek apakah kodevhc sudah ada di tanggal > tanggal input
			$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',
								"kodevhc = '".$resHead['kodevhc']."' and tanggal > '".$resHead['tanggal']."'");
			$resCek = fetchData($qCek);
			if(!empty($resCek[0]['tgl']) and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
				exit("Warning: Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
					 "\nPerubahan KM / HM Akhir tidak bisa dilakukan");
			}
			//print_r($resKm);exit('error');
			$jumlah=$kmhmAkhir-$kmhmAwal;
			$sup="update ".$dbname.".vhc_rundt set jenispekerjaan='".$jnsPekerjaan."',alokasibiaya='".$lokKerja."',beratmuatan='".$brtMuatan."'
			,jumlahrit='".$jmlhRit."',keterangan='".$ket."',biaya='".$biaya."',kmhmawal='".$kmhmAwal."',kmhmakhir='".$kmhmAkhir."',jumlah='".$jumlah."'
			,satuan='".$satuan."',kodesegment='".$segment."' where notransaksi='".$notransaksi_head."' ".$where."";
			//exit("Error:".$sup);
			if(mysql_query($sup))
			{
	
				$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$notransaksi_head]."'";
				//exit("Error:".$sKm);
				$qKm=mysql_query($sKm) or die(mysql_error($conn));
				$rKm=mysql_fetch_assoc($qKm);
				
				updateKmHm($optKdVhc[$notransaksi_head]);
				echo intval($rKm['kmhmakhir']);
			}
			else
			{echo "DB Error : ".mysql_error($conn);	 }
			break;

        case'deleteKrj':
        $delKrj="delete from ".$dbname.".vhc_rundt
			where notransaksi='".$notransaksi_head."' and
			jenispekerjaan='".$jnsPekerjaan."' and
			alokasibiaya='".$Blok."' and
			kodesegment='".$segment."'";
        if(mysql_query($delKrj))
        updateKmHm($optKdVhc[$notransaksi_head]);
        else
        echo "DB Error : ".mysql_error($conn);	 

        break;
        case'insert_operator':
        $sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notransaksi_head."'";
//	echo"warning:".$sCekHt;
        $qCekHt=mysql_query($sCekHt) or die(mysql_error());
        $rCekHt=mysql_num_rows($qCekHt);
        if($rCekHt<1)
        {
                echo"warning: Header required";
                exit();
        }

        $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($rKode['kodeorg'],0,4)."' and periode='".substr($tglTrans,0,4)."-".substr($tglTrans,4,2)."'";# tanggalmulai<".$tglTrans." and tanggalsampai>=".$tglTrans;
        //echo $sPeriode;
        //exit("Error:");
        $qPeriode=mysql_query($sPeriode) or die(mysql_error($conn));
        $rPeriode=mysql_fetch_assoc($qPeriode);
        //echo"warning".$rPeriode['periode'];exit();
        if($rPeriode['periode']=='')
        {
        echo"warning: Transaction date out of range";
        exit();
        }
                $sKd="select lokasitugas,subbagian from ".$dbname.".datakaryawan where karyawanid='".$kdKry."'";
                $qKd=mysql_query($sKd) or die(mysql_error());
                $rKd=mysql_fetch_assoc($qKd);
                $lokasiTugas=$rKd['lokasitugas'];
                if(!is_null($rKd['subbagian'])||$rKd['subbagian']!=0||$rKd['subbagian']!='')
                {
                   $lokasiTugas=$rKd['subbagian'];
                }



        if($posisi==1)
        {
                $sCek="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                //echo "warning:".$sCek;
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_assoc($qCek);

                        if($rCek['jmlh']!=4)
                        {
                                $sqlIns="insert into ".$dbname.".vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`) values ('".$notransaksi_head."','".$kdKry."','".$posisi."','".$tglTrans."','".$rkry['tipe']."','".$uphOprt."','".$prmiOprt."','".$pnltyOprt."')";
                                //echo"warning:".$sqlIns;
                                if(mysql_query($sqlIns))
                                {									
                                        //cek tanggal dan periode sudah ada di header atau blm
                                        $sInsAbsnC="select tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tglTrans."' and periode='".$rPeriode['periode']."' and kodeorg='".$lokasiTugas."'";

                                        $qInsAbsnC=mysql_query($sInsAbsnC) or die(mysql_error($conn));
                                        $rInsAbsnC=mysql_num_rows($qInsAbsnC);
                                        if($rInsAbsnC>0)
                                        {

                                                $sCek="select karyawanid from ".$dbname.".sdm_absensidt where kodeorg='".$lokasiTugas."' and tanggal='".$tglTrans."' and karyawanid='".$kdKry."'";

                                                $qCek=mysql_query($sCek) or die(mysql_error($conn));
                                                $rCek=mysql_num_rows($qCek);
                                                if($rCek!=1)
                                                {
                                                $sUpdAbns="insert into ".$dbname.".sdm_absensidt (`kodeorg`, `tanggal`, `karyawanid`, `absensi`, `jam`, `jamPlg`) values ('".$lokasiTugas."','".$tglTrans."','".$kdKry."','H','07:00:00','15:00:00')";
                                                //echo"warning".$sUpdAbns;
                                                        if(!mysql_query($sUpdAbns))
                                                        {
                                                        echo "DB Error : ".mysql_error($conn);
                                                        }
                                                }
                                        }
                                        elseif($rInsAbsnC<1)
                                        {
                                                //echo"warning:Masuk aja B";
                                                $sInshead="insert into ".$dbname.".sdm_absensiht (`tanggal`, `kodeorg`, `periode`, `posting`) values('".$tglTrans."','".$lokasiTugas."','".$rPeriode['periode']."','0')";
                                                //echo"warning".$sInshead;
                                                if(mysql_query($sInshead))
                                                {
                                                $sUpdAbns="insert into ".$dbname.".sdm_absensidt (`kodeorg`, `tanggal`, `karyawanid`, `absensi`, `jam`, `jamPlg`) values ('".$lokasiTugas."','".$tglTrans."','".$kdKry."','H','07:00:00','15:00:00')";
                                                        if(!mysql_query($sUpdAbns))
                                                        {
                                                                echo "DB Error : ".mysql_error($conn);
                                                        }	
                                                }
                                                else
                                                {
                                                echo "DB Error : ".mysql_error($conn);
                                                }					
                                        }
                                }
                                else
                                {
                                        echo "DB Error : ".mysql_error($conn);	
                                }
                        }
                        else
                        {
                                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                                exit();
                        }
        }
        elseif($posisi==0)
        {
                $sCekSop="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                //echo "warning:".$sCekSop;
                $qCekSop=mysql_query($sCekSop) or die(mysql_error());
                $rCekSop=mysql_fetch_assoc($qCekSop);
                if($rCekSop['jmlh']==1)
                {
                        echo"warning: Operator can only one";
                        break;
                        exit();
                }
                elseif($rCekSop['jmlh']==0)
                {

                                $sqlIns="insert into ".$dbname.".vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`) values ('".$notransaksi_head."','".$kdKry."','".$posisi."','".$tglTrans."','".$rkry['tipe']."','".$uphOprt."','".$prmiOprt."','".$pnltyOprt."')";
                                        //echo"warning:".$sqlIns;
                                if(mysql_query($sqlIns))
                                {
                                        //cek tanggal dan periode sudah ada di header atau blm
                                        $sInsAbsnC="select tanggal from ".$dbname.".sdm_absensiht where tanggal='".$tglTrans."' and kodeorg='".$lokasiTugas."'";
                                        //exit("Error:".$sInsAbsnC);
                                        $qInsAbsnC=mysql_query($sInsAbsnC) or die(mysql_error($conn));
                                        $rInsAbsnC=mysql_num_rows($qInsAbsnC);
                                        if($rInsAbsnC>0)
                                        {
                                        //echo"warning:Masuk aja A";
                                                $sCek="select karyawanid from ".$dbname.".sdm_absensidt where kodeorg='".$lokasiTugas."' and tanggal='".$tglTrans."' and karyawanid='".$kdKry."'";
                                                $qCek=mysql_query($sCek) or die(mysql_error($conn));
                                                $rCek=mysql_num_rows($qCek);
                                                if($rCek<1)
                                                {
                                                $sUpdAbns="insert into ".$dbname.".sdm_absensidt (`kodeorg`, `tanggal`, `karyawanid`, `absensi`, `jam`, `jamPlg`) values ('".$lokasiTugas."','".$tglTrans."','".$kdKry."','H','07:00:00','15:00:00')";
                                                //echo"warning".$sUpdAbns;
                                                        if(!mysql_query($sUpdAbns))
                                                        {
                                                        echo "DB Error : ".mysql_error($conn);
                                                        }
                                                }
                                        }
                                        elseif($rInsAbsnC<1)
                                        {
                                                //echo"warning:Masuk aja B";
                                                $sInshead="insert into ".$dbname.".sdm_absensiht (`tanggal`, `kodeorg`, `periode`, `posting`) values('".$tglTrans."','".$lokasiTugas."','".$rPeriode['periode']."','0')";
                                                //echo"warning".$sInshead;
                                                if(mysql_query($sInshead))
                                                {
                                                $sUpdAbns="insert into ".$dbname.".sdm_absensidt (`kodeorg`, `tanggal`, `karyawanid`, `absensi`, `jam`, `jamPlg`) values ('".$lokasiTugas."','".$tglTrans."','".$kdKry."','H','07:00:00','15:00:00')";
                                                        if(!mysql_query($sUpdAbns))
                                                        {
                                                                echo "DB Error : ".mysql_error($conn);
                                                        }	
                                                }
                                                else
                                                {
                                                echo "DB Error : ".mysql_error($conn);
                                                }					
                                        }
                                }
                                else
                                {
                                        echo "DB Error : ".mysql_error($conn);
                                }
                        }
        }
        break;
        case 'update_operator':
        if($posisi==1)
        {
                $sCek="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                //echo "warning:".$sCek;
                $qCek=mysql_query($sCek) or die(mysql_error());
                $rCek=mysql_fetch_assoc($qCek);
        }
        elseif($posisi==0)
        {
                $sCekSop="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                //echo "warning:".$sCekSop;
                $qCekSop=mysql_query($sCekSop) or die(mysql_error());
                $rCekSop=mysql_fetch_assoc($qCekSop);
        }
        if($rCek['jmlh']>4)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        if($rCekSop['jmlh']>1)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        $skry="select a.`alokasi`,b.tipe from ".$dbname.".datakaryawan a inner join ".$dbname.".sdm_5tipekaryawan b on 
        a.tipekaryawan=b.id where karyawanid='".$kdKry."'"; 
        //echo "warning:".$skry;
        $qkry=mysql_query($skry) or die(mysql_error());
        $rkry=mysql_fetch_assoc($qkry);


        $sup_op="update ".$dbname.".vhc_runhk set posisi='".$posisi."',tanggal='".$tglTrans."',statuskaryawan='".$rkry['tipe']."',upah='".$uphOprt."',premi='".$prmiOprt."',penalty='".$pnltyOprt."' where notransaksi='".$notransaksi_head."' and idkaryawan='".$kdKry."'";
        if(mysql_query($sup_op))
        echo"";
        else
                echo "DB Error : ".mysql_error($conn);
        break;
        case'getUmr':
            if($_POST['tahun']!='')
                    $tahun=$_POST['tahun'];
            else {
                    $tahun=date('Y');
            }
        $sUmr="select sum(jumlah) as jumlah from ".$dbname.".sdm_5gajipokok 
            where karyawanid='".$kdKry."' and tahun=".$tahun."  and idkomponen in (1,31)";
        $qUmr=mysql_query($sUmr) or die(mysql_error());
        $rUmr=mysql_fetch_assoc($qUmr);
        $umr=$rUmr['jumlah']/25;
        echo intval($umr);
        break;

        case'load_data_opt':
        $arrPos=array("Operator","Helper");
        $sql="select * from ".$dbname.".vhc_runhk where substring(notransaksi,1,4)='".$rKode['kodeorg']."' and notransaksi='".$notransaksi_head."' order by notransaksi desc"; //echo "warning:".$sql;
        $query=mysql_query($sql) or die(mysql_error());
        while($res=mysql_fetch_assoc($query))
        {
                $skry="select `namakaryawan` from ".$dbname.".datakaryawan where karyawanid='".$res['idkaryawan']."'";
                $qkry=mysql_query($skry) or die(mysql_error());
                $rkry=mysql_fetch_assoc($qkry);
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$res['notransaksi']."</td>
                <td>".$rkry['namakaryawan']."</td>
                <td>".$arrPos[$res['posisi']]."</td>
                <td>".number_format($res['upah'],2)."</td>
                <td>".number_format($res['premi'],2)."</td>
                <td>".number_format($res['penalty'],2)."</td>
                <td align=center>
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('". $res['notransaksi']."','". $res['idkaryawan']."');\" >	
                </td>
                </tr>
                ";
        }
        break;
        case'getKntrk':
        $optKntrk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sSpk="select notransaksi from ".$dbname.".log_spkht where kodeorg='".$lokasi."' and posting<>'0' and tanggal like '%".$thnKntrk."%'";
        //echo "warning:".$sSpk;
        $qSpk=mysql_query($sSpk) or die(mysql_error());
        $rSpk=mysql_num_rows($qSpk);
        if($rSpk>0)
        {
                while($rSpk=mysql_fetch_assoc($qSpk))
                {
                        $optKntrk.="<option value=".$rSpk['notransaksi']." ".($rSpk['notransaksi']==$noKntrak?'selected':'').">".$rSpk['notransaksi']."</option>";
                }

        }
        else
        {
                $optKntrk="<option value=''></option>";
                //echo $optKntrk;
        }
        echo $optKntrk;
        break;

        case'delete_opt':
            $sTanggal="select distinct tanggal from ".$dbname.".vhc_runht where notransaksi='".$notransaksi."'";
            $qTanggal=mysql_query($sTanggal) or die(mysql_error($conn));
            $rTanggal=mysql_fetch_assoc($qTanggal);
            $delAbsen="delete from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$rTanggal['tanggal']."'";
            if(mysql_query($delAbsen))
            {
                $sdel="delete from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi."' and idkaryawan='".$kdKry."'";
                //echo "warning:".$sdel;
                if(mysql_query($sdel))
                echo"";
                else
                echo "DB Error : ".mysql_error($conn);
            }
            else
            {
                 echo "DB Error : ".$delAbsen."___".mysql_error($conn);
            }
        break;
		case'getSatuan':
			$strSat="select satuan from ".$dbname.".`vhc_kegiatan` where  kodekegiatan='".$jnsPekerjaan."'";
			$qrySat=mysql_query($strSat) or die(mysql_error());
			$resSat=mysql_fetch_assoc($qrySat);
			echo $resSat['satuan'];
		break;
        case'getBlok':
		
		$sAlokasi = "select distinct(b.kelompok) as kelompok from ".$dbname.".vhc_kegiatan a 
					left join ".$dbname.".setup_kegiatan b 
					on a.noakun = b.noakun 
					where a.kodekegiatan='".$jnsPekerjaan."'";
		$qAlokasi = mysql_query($sAlokasi) or die(mysql_error($conn));
		$rAlokasi = mysql_fetch_assoc($qAlokasi);
		
		// if($rAlokasi['kelompok'] != ''){
			if($rAlokasi['kelompok']=='PNN'){
				$statusblok = " and statusblok = 'TM' and luasareaproduktif>0";
			}else if($rAlokasi['kelompok']=='TB'){
				$statusblok = " and statusblok IN ('LC','TB','TBM','TBM-01','TBM-02','TBM-03')";
			}else if($rAlokasi['kelompok']=='TBM'){
				$statusblok = " and statusblok IN ('TB','TBM','TBM-01','TBM-02','TBM-03')";
			}else{
				$statusblok = " and luasareaproduktif>0";
				//$statusblok = " and statusblok = '".$rAlokasi['kelompok']."' and luasareaproduktif>0";
				//$statusblok = " and statusblok = '".$rAlokasi['kelompok']."'";
			}
		
		$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		/*
        $sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where induk like '%".$lokKerja."%' and (tipe='BLOK' OR tipe='BIBITAN')
                and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$lokKerja."' and luasareaproduktif>0 ".$statusblok.")";
		*/
        $sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where detail=1 and induk like '%".$lokKerja."%' and (tipe='BLOK' OR tipe='BIBITAN')
                and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$lokKerja."' ".$statusblok.")";
		// exit('error : '.$sBlok);
        $qBlok=mysql_query($sBlok) or die(mysql_error());
        while($rBlok=mysql_fetch_assoc($qBlok))
        {
                if($Blok!="")
                {
                        $optBlok.="<option value=".$rBlok['kodeorganisasi']." ".($rBlok['kodeorganisasi']==$Blok?"selected":"").">".$rBlok['namaorganisasi']."</option>";
                }
                else
                {
                        $optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
                }
        }
            #khusus Project:
              $str="select kode,nama from  ".$dbname.".project where kodeorg='".$lokKerja."' and posting=0";
              $res=mysql_query($str);
              while($bar=mysql_fetch_object($res))
              {
                  $optBlok.="<option value=".$bar->kode.">Project-".$bar->nama."</option>";
              }
        echo $optBlok;
        break;
        default:
        break;
}

function updateKmHm($kodevhc) {
	global $dbname;
	
	// Get KM/HM Akhir
	$qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'");
	$resKm = fetchData($qKm);
	$kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];
	
	$dataIns = array($kodevhc,$kmhmAkhir);
	$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
	if(!mysql_query($qIns)) {
		$dataUpd = array('kmhmakhir'=>$kmhmAkhir);
		$qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,
							"kodevhc='".$kodevhc."'");
		if(!mysql_query($qUpd)) {
			exit("Update KM/HM Error: ".mysql_error());
		}
	}
}