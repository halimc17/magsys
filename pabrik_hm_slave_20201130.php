<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$stasiun=checkPostGet('stasiun','');
$mesin=checkPostGet('mesin','');
########cara hitung tanggal kemarin###############
$tanggal=tanggalsystem(checkPostGet('tanggal',''));//merubah dari 10-10-2014 menjadi 20141010
$tglKmrn=strtotime('-1 day',strtotime($tanggal));
$tglKmrn=date('Y-m-d', $tglKmrn);
$hmawal=checkPostGet('hmawal',0);
$hmakhir=checkPostGet('hmakhir',0);
$jam=checkPostGet('jam',0);
$jam=checkPostGet('jam',0);
$tipeservice=checkPostGet('tipeservice','');
$keterangan=checkPostGet('keterangan','');
$addedit=checkPostGet('addedit','');
switch($proses){
	case'loadData':
		$strtb="select left(a.tanggal,7) as periode from ".$dbname.".pabrik_hm a where a.kodemesin like '".$mesin."%'
				order by a.kodemesin,a.tanggal desc limit 1";
		//$strtb="select periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".$kodeorg."' order by periode limit 1";
		$restb=mysql_query($strtb);
		$periodetb=date('Y-m');
		while($bartb=mysql_fetch_object($restb)){
			$periodetb=$bartb->periode;
		}
		$str="select a.*,b.namaorganisasi from ".$dbname.".pabrik_hm a 
				left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodemesin
				where a.kodemesin like '".$mesin."%' and tanggal like '".$periodetb."%'
				order by a.kodemesin,a.tanggal";
		//exit('Warning: '.$str);
		//echo $str;
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			if($awalmesin!=$bar->kodemesin){
				$awalmesin=$bar->kodemesin;
				$strjam="select a.jamganti1 from ".$dbname.".pabrik_hm_setup a where a.kodemesin like '".$bar->kodemesin."%'";
				$resjam=mysql_query($strjam);
				$jamganti=10000;
				$jamjalan1=0;
				$jamjalan2=0;
				$jamjalan3=0;
				while($barjam=mysql_fetch_object($resjam)){
					$jamganti=$barjam->jamganti1;
				}
			}
			$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$jamjalan1+=$bar->jam;
			$jamjalan2+=$bar->jam;
			$jamjalan3+=$bar->jam;
			if($bar->tipeservice=='3'){
				$jenisservice='Major/General Overhaul/Sparepart3';
				$jamjalan1=0;
				$jamjalan2=0;
				$jamjalan3=0;
				$jamganti=$bar->jamganti;
			}elseif($bar->tipeservice=='2'){
				$jenisservice='Intermediate/Top Overhaul/Sparepart2';
				$jamjalan1=0;
				$jamjalan2=0;
				$jamganti=$bar->jamganti;
			}elseif($bar->tipeservice=='1'){
				$jenisservice='Pergantian Sparepart1';
				$jamjalan1=0;
				$jamganti=$bar->jamganti;
			}else{
				$jenisservice='Tidak Service';
			}
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center width='3%'>".$bar->kodeorg."</td>
					<td ".$drcl." align=center width='5%'>".substr($bar->kodemesin,0,6)."</td>
					<td ".$drcl." align=center width='7%'>".$bar->kodemesin."</td>
					<td ".$drcl." align=left>".$bar->namaorganisasi."</td>
					<td ".$drcl." align=center width='6%'>".tanggalnormal($bar->tanggal)."</td>
					<td ".$drcl." align=left width='13%'>".$jenisservice."</td>
					<td ".$drcl." align=right width='5%'>".number_format($bar->hmawal,2,'.',',')."</td>
					<td ".$drcl." align=right width='5%'>".number_format($bar->hmakhir,2,'.',',')."</td>
					<td ".$drcl." align=right width='5%'>".number_format($bar->jam,2,'.',',')."</td>
					<td ".$drcl." align=right width='5%'>".number_format($jamjalan1,2,'.',',')."</td>
					<td ".$drcl." align=right width='5%'>".number_format($jamjalan2,2,'.',',')."</td>
					<td ".$drcl." align=right width='5%'>".number_format($jamjalan3,2,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->keterangan."</td>
					<td align=center width='5%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->tipeservice."','".$bar->hmawal."','".$bar->hmakhir."','".$bar->jam."','".$bar->keterangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodemesin."','".tanggalnormal($bar->tanggal)."','".$bar->tipeservice."');\">&nbsp
						<img src=images/zoom.png class=resicon title='Detail' onclick=\"showpopup('".$bar->kodemesin."','".$bar->tanggal."','".$bar->kodeorg."','".substr($bar->kodemesin,0,6)."',event);\">
					</td>
				</tr>";	
		}
		break;

	case'delData':
		$strx="delete from ".$dbname.".pabrik_hm 
				where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and tipeservice='".$tipeservice."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		$jamganti=0;
		$stra="select * from ".$dbname.".pabrik_hm_setup where kodemesin='".$mesin."'";
		$resa=mysql_query($stra);
		$rowa=mysql_num_rows($resa);
		while($bara=mysql_fetch_object($resa)){
			if($tipeservice=='2'){
				$jamganti=$bara->jamganti2;
			}elseif($tipeservice=='1'){
				$jamganti=$bara->jamganti1;
			}else{
				$jamganti=0;
			}
		}
		if($addedit=='update'){
			$strs="select * from ".$dbname.".pabrik_hm
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal>='".$tanggal."'";
			$ress=mysql_query($strs);
			$rows=mysql_num_rows($ress);
			if($rows>1){
				exit('Warning : Ada tanggal lain setelah tanggal ini...!');
			}
			$strx="update ".$dbname.".pabrik_hm set kodeorg='".$kodeorg."',kodemesin='".$mesin."',tanggal='".$tanggal."'
					,hmawal=".$hmawal.",hmakhir=".$hmakhir.",jam=".$jam.",tipeservice='".$tipeservice."'
					,jamganti=".$jamganti.",keterangan='".$keterangan."',lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodemesin='".$mesin."' and tanggal='".$tanggal."' and tipeservice='".$tipeservice."'";
		}else{
			$strx="insert into ".$dbname.".pabrik_hm (kodeorg,kodemesin,tanggal,hmawal,hmakhir,jam,tipeservice,jamganti,keterangan,lastuser,lastdate)
					values('".$kodeorg."','".$mesin."','".$tanggal."',".$hmawal.",".$hmakhir.",".$jam.",'".$tipeservice."'
					,'".$jamganti."','".$keterangan."','".$_SESSION['standard']['username']."',now())";
		}
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}else{
			if($rowa>0){
				$strz="update ".$dbname.".pabrik_hm_setup set hmakhir=".$hmakhir." where kodemesin='".$mesin."'";
			}else{
				$strz="insert into ".$dbname.".pabrik_hm_setup (kodemesin,jamganti1,jamganti2,jamganti3,hmakhir,lastuser,lastdate)
						values('".$mesin."',800,800,800,'".$hmakhir."','".$_SESSION['standard']['username']."',now())";
			}
			if(!mysql_query($strz)){
				echo " Gagal, ".$strz,' '.addslashes(mysql_error($conn));
			}
		}
	break;

/*
    case'getData':
        ##bentuk tanggal kemarin
        $tgl =  tanggalsystem($_POST['tanggal']);
        $tglKmrn = strtotime('-1 day',strtotime($tgl));
        $tglKmrn = date('Y-m-d', $tglKmrn);
        
        #ambil sisa kemarin
        $iSisa="select airsisa from ".$dbname.".pabrik_datapress where kodeorg='".$_POST['kodeorg']."' and "
                . " tanggal='".$tglKmrn."'";
		//exit('Warning: '.$iSisa);
        $nSisa=mysql_query($iSisa) or die (mysql_errno($conn));
        $dSisa=mysql_fetch_assoc($nSisa);
            $airKmrn=$dSisa['airsisa'];
            
        $tbsHr=0;
		#ambil produksi hari ini
        //$iTbs="select sum(beratbersih) as beratbersih  from ".$dbname.".pabrik_timbangan where millcode='".$_POST['kodeorg']."' and "
        //        . " tanggal like '%".tanggalsystemn($_POST['tanggal'])."%' and kodebarang='40000003'"; 
        //$nTbs=  mysql_query($iTbs) or die (mysql_errno($conn));
        //$dTbs=  mysql_fetch_assoc($nTbs);
        //   $tbsHr=$dTbs['beratbersih'];
        if($airKmrn=='')
            $airKmrn=0;
        
        echo $airKmrn."###".$tbsHr;
    break;
*/
	case'getStasiun':
		$optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($kodeorg!=''){
			$iStation="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where detail=1 and tipe='STATION' and induk='".$kodeorg."'";     
			$nStation=mysql_query($iStation) or die(mysql_error($conn));
			while($dStation=mysql_fetch_assoc($nStation)){
				$optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
			}  
		}
		echo $optStation;
    break;

    case'getMesin':
        $optMesin.="";
		if($stasiun==''){
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk like '".$kodeorg."%' and length(kodeorganisasi)=10";
		}else{
			$iMesin="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$stasiun."'";
		}
		$nMesin=mysql_query($iMesin) or die (mysql_error($conn));
		while($dMesin=mysql_fetch_assoc($nMesin)){
			if($mesin==$dMesin['kodeorganisasi'])
				{$select="selected=selected";}
			else
				{$select="";}
			$optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">[".$dMesin['kodeorganisasi']."] ".$dMesin['namaorganisasi']."</option>";
		}
        echo $optMesin;
    break;

    case'getHM':
        $optHM=0;
		$iMesin="select kodemesin,hmakhir from ".$dbname.".pabrik_hm_setup where kodemesin='".$mesin."'";
		$nMesin=mysql_query($iMesin) or die (mysql_error($conn));
		while($dMesin=mysql_fetch_assoc($nMesin)){
			$optHM=$dMesin['hmakhir'];
		}
		//exit('Warning : '.$optHM.' '.$iMesin);
        echo $optHM;
    break;
/*
	case'getDetailPA':
        $str="select * from ".$dbname.".pabrik_datapress
			where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$_POST['tgl']."'";

		$res=mysql_query($str) or die(mysql_error($conn));
        $rdata=mysql_fetch_assoc($res);
       
		$airkemarin=$rdata['airsisa']-$rdata['airclarifier']+$rdata['airboiler']+$rdata['airproduksi']+$rdata['airpembersihan']+$rdata['airdomestik'];
		
echo "<fieldset style='width:860px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td>".$rdata['kodeorg']."</td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>".tanggalnormal($rdata['tanggal'])."</td>
						</tr>
					</table>
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Tek. Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td>".$rdata['tekpressp1']."</td>
										</tr>
										<tr>
											<td>P2</td>
											<td>".$rdata['tekpressp2']."</td>
										</tr>
										<tr>
											<td>P3</td>
											<td>".$rdata['tekpressp3']."</td>
										</tr>
										<tr>
											<td>P4</td>
											<td>".$rdata['tekpressp4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Suhu Digester</legend>
									<table>
										<tr>
											<td>D1</td>
											<td>".$rdata['suhud1']."</td>
										</tr>
										<tr>
											<td>D2</td>
											<td>".$rdata['suhud2']."</td>
										</tr>
										<tr>
											<td>D3</td>
											<td>".$rdata['suhud3']."</td>
										</tr>
										<tr>
											<td>D4</td>
											<td>".$rdata['suhud4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Jam Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td>".$rdata['jampressp1']."</td>
										</tr>
										<tr>
											<td>P2</td>
											<td>".$rdata['jampressp2']."</td>
										</tr>
										<tr>
											<td>P3</td>
											<td>".$rdata['jampressp3']."</td>
										</tr>
										<tr>
											<td>P4</td>
											<td>".$rdata['jampressp4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>
  					<table>
						<tr>
							<td>
								<fieldset><legend>Air</legend>
									<table>
										<tr>
											<td>Air Sisa kemarin (Bak Basin)</td>
											<td>".$airkemarin." M3</td>
										</tr>
										<tr>
											<td>Air Clarifier Tank</td>
											<td>".$rdata['airclarifier']." M3</td>
										</tr>	
										<tr>
											<td>Air Boiler</td>
											<td>".$rdata['airboiler']." M3</td>
										</tr>	
										<tr>
											<td>Air Produksi</td>
											<td>".$rdata['airproduksi']." M3</td>
										</tr>	
										<tr>
											<td>Air Pembersihan</td>
											<td>".$rdata['airpembersihan']." M3</td>
										</tr>	
										<tr>
											<td>Air Domestik Camp</td>
											<td>".$rdata['airdomestik']." M3</td>
										</tr>	
										<tr>
											<td>Air Sisa (Bak Basin)</td>
											<td>".$rdata['airsisa']." M3</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>
			</tr>	  
		</table>
	</fieldset>";
    break;
*/
	default:
	break;
}
?>
