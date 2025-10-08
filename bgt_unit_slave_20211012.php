<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kdPT=checkPostGet('kdPT','');
$kdUnit=checkPostGet('kdUnit','');
$kdDivisi=checkPostGet('kdDivisi','');
$Tahun=checkPostGet('Tahun','');
$tipeBudget='ESTATE';
$kdBudget=checkPostGet('kdBudget','');
$kdKegiatan=checkPostGet('kdKegiatan','');
$kdBarang=checkPostGet('kdBarang','');
$kdVhc=checkPostGet('kdVhc','');
$idbgtunit=checkPostGet('idbgtunit','');
switch($proses){
	case'loadData':
		$where="";
		if($Tahun!=''){
			$where.="tahunbudget='".$Tahun."'";
		}else{
			exit;
		}
		if($kdUnit!=''){
			$where.=" and left(divisi,4)='".$kdUnit."'";
		}
		if($kdDivisi!=''){
			$where.=" and left(divisi,6)='".$kdDivisi."'";
		}
		$str="select a.*,b.namakegiatan,c.namabarang from ".$dbname.".bgt_unit a 
				left join ".$dbname.".setup_kegiatan b on b.kodekegiatan=a.kegiatan
				left join ".$dbname.".log_5masterbarang c on c.kodebarang=a.kodebarang
				where ".$where."
				order by a.tahunbudget,a.divisi,a.kodebudget,a.kegiatan,a.noakun,a.kodevhc,a.kodebarang";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			$id=$bar->idbgtunit;
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->tahunbudget."</td>
					<td ".$drcl." align=center>".$bar->divisi."</td>
					<td ".$drcl." align=center>".$bar->tipebudget."</td>
					<td ".$drcl." align=left>".$bar->kodebudget."</td>
					<td ".$drcl." align=left>".$bar->namakegiatan."</td>
					<td ".$drcl." align=left>".$bar->satuanv."</td>
					<td ".$drcl." align=right><input type=text id=volume".$id." value=".number_format($bar->volume,2)." onchange=\"SaveCell('".$bar->idbgtunit."','volume','".$bar->volume."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:40px;\"></td>
					<td ".$drcl." align=right><input type=text id=rotasi".$id." value=".number_format($bar->rotasi,0)." onchange=\"SaveCell('".$bar->idbgtunit."','rotasi','".$bar->rotasi."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:30px;\"></td>";
			if($bar->kodebarang==''){
				echo"<td ".$drcl." align=right><input type=text id=jumlah".$id." value=".number_format($bar->jumlah,0)." onchange=\"SaveCell('".$bar->idbgtunit."','jumlah','".$bar->jumlah."','".$bar->kodebarang."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:30px;\"></td>";
			}else{
				echo"<td ".$drcl." align=right></td>";
			}
			echo "	<td ".$drcl." align=left>".$bar->namabarang."</td>";
			if($bar->kodebarang==''){
				echo"<td ".$drcl." align=right></td>";
			}else{
				echo"<td ".$drcl." align=right><input type=text id=jumlah".$id." value=".number_format($bar->jumlah,2)." onchange=\"SaveCell('".$bar->idbgtunit."','jumlah','".$bar->jumlah."','".$bar->kodebarang."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";
			}
			echo "	<td ".$drcl." align=left>".$bar->satuanj."</td>
					<td ".$drcl." align=left>".$bar->kodevhc."</td>
					<td ".$drcl." align=right>".number_format($bar->rupiah,2,'.',',')."</td>
					<td ".$drcl." align=right><input type=text id=sebaran01".$id." value=".number_format($bar->sebaran01,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran01','".$bar->sebaran01."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran02".$id." value=".number_format($bar->sebaran02,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran02','".$bar->sebaran02."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran03".$id." value=".number_format($bar->sebaran03,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran03','".$bar->sebaran03."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran04".$id." value=".number_format($bar->sebaran04,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran04','".$bar->sebaran04."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran05".$id." value=".number_format($bar->sebaran05,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran05','".$bar->sebaran05."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran06".$id." value=".number_format($bar->sebaran06,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran06','".$bar->sebaran06."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran07".$id." value=".number_format($bar->sebaran07,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran07','".$bar->sebaran07."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran08".$id." value=".number_format($bar->sebaran08,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran08','".$bar->sebaran08."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran09".$id." value=".number_format($bar->sebaran09,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran09','".$bar->sebaran09."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran10".$id." value=".number_format($bar->sebaran10,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran10','".$bar->sebaran10."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran11".$id." value=".number_format($bar->sebaran11,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran11','".$bar->sebaran11."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran12".$id." value=".number_format($bar->sebaran12,0)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran12','".$bar->sebaran12."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td align=center>
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->idbgtunit."');\">";
			if($bar->kodevhc==0){
				echo "	<img src='images/".$_SESSION['theme']."/posting.png' class=resicon title='Posting' onclick=\"postingdata('".$bar->idbgtunit."');\">";
			}else{
				echo "	<img src='images/".$_SESSION['theme']."/posted.png' class=resicon title='Posted'>";
			}
			echo"	</td>
				</tr>";	
		}
	break;

	case'delData':
		$strx="delete from ".$dbname.".bgt_unit where idbgtunit='".$idbgtunit."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		$optSatKeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
		$optSatBrg=""; 
		if($kdBarang!=''){
			$optSatBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"inactive='0'");
		}
		$optSatKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
		if($addedit=='update'){
			$strs="select * from ".$dbname.".bgt_unit 
					where tahunbudget='".$Tahun."' and divisi='".$kdDivisi."' and kodebudget='".$kdBudget."' and kegiatan='".$kdKegiatan."' and noakun='".substr($kdKegiatan,0,7)."' and kodevhc='".$kdVhc."' and kodebarang='".$kdBarang."'";
			$ress=mysql_query($strs);
			$nums=mysql_num_rows($ress);
			if($rows>0){
				exit('Warning : Data Sudah Ada...!');
			}
			$strx="update ".$dbname.".bgt_unit set tahunbudget='".$Tahun."',divisi='".$kdDivisi."',kodebudget='".$kdBudget."',kegiatan='".$kdKegiatan."'
					,noakun='".substr($kdKegiatan,0,7)."',kodevhc='".$kdVhc."',kodebarang='".$kdBarang."',keterangan='".$_SESSION['standard']['username']."'
					,updateby='".$_SESSION['standard']['userid']."',lastupdate=now() where idbgtunit='".$idbgtunit."'";
		}else{
			$strx="insert into ".$dbname.".bgt_unit
				(tahunbudget,divisi,tipebudget,kodebudget,kegiatan,noakun,satuanv,kodevhc,kodebarang,satuanj,keterangan,updateby,lastupdate)
				values('".$Tahun."','".$kdDivisi."','".$tipeBudget."','".$kdBudget."','".$kdKegiatan."','".substr($kdKegiatan,0,7)."','".$optSatKeg[$kdKegiatan]."'
				,'".$kdVhc."','".$kdBarang."','".$optSatBrg[$kdBarang]."','".$_SESSION['standard']['username']."','".$_SESSION['standard']['userid']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'SimpanCell':
		$cellname=checkPostGet('cellname','');
		$cellvalue=checkPostGet('cellvalue','');
		$reffvalue=checkPostGet('reffvalue','');
		if($cellname=='jumlah'){
			$sData="select * from ".$dbname.".bgt_unit where idbgtunit='".$idbgtunit."'";
			$qData=mysql_query($sData) or die(mysql_error($conn));
			while($rData=mysql_fetch_assoc($qData)){
				$rupiah=0;
				if($rData['kodebarang']==''){
					$sLookup="select * from ".$dbname.".bgt_upah where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".substr($rData['divisi'],0,4)."' and  golongan='".$rData['kodebudget']."'";
					$qLookup=mysql_query($sLookup) or die(mysql_error($conn));
					while($rLookup=mysql_fetch_assoc($qLookup)){
						$rupiah=$rLookup['jumlah']*$cellvalue;
					}
				}else{
					$sLookup="select * from ".$dbname.".bgt_masterbarang where tahunbudget='".$rData['tahunbudget']."' and regional='".$_SESSION['empl']['regional']."' and  kodebarang='".$rData['kodebarang']."'";
					$qLookup=mysql_query($sLookup) or die(mysql_error($conn));
					while($rLookup=mysql_fetch_assoc($qLookup)){
						$rupiah=$rLookup['hargasatuan']*$cellvalue;
					}
				}

			}
			$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."',updateby='".$_SESSION['standard']['userid']."',lastupdate=now(),rupiah=".$rupiah." where idbgtunit='".$idbgtunit."'";
		}else{
			$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."',updateby='".$_SESSION['standard']['userid']."',lastupdate=now() where idbgtunit='".$idbgtunit."'";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getUnit':
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
		}else{
			$optUnit="";
		}
		$sUnit="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and induk='".$kdPT."' order by namaorganisasi";
		//exit('Warning: '.$sUnit);
		$qUnit=mysql_query($sUnit) or die(mysql_error($conn));
		while($rUnit=mysql_fetch_assoc($qUnit)){
			if($kdUnit==$rUnit['kodeorganisasi'])
				{$select="selected=selected";}
			else
				{$select="";}
			$optUnit.="<option ".$select." value=".$rUnit['kodeorganisasi'].">".$rUnit['namaorganisasi']."</option>";
		}
		echo $optUnit;
    break;

    case'getDivisi':
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sDivisi="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi<>'MHO' and detail='1' and induk='".$kdUnit."' and tipe not like 'GUDANG%' order by namaorganisasi";
		//exit('Warning: '.$sDivisi);
		$qDivisi=mysql_query($sDivisi) or die(mysql_error($conn));
		while($rDivisi=mysql_fetch_assoc($qDivisi)){
			if($kdDivisi==$rDivisi['kodeorganisasi'])
				{$select="selected=selected";}
			else
				{$select="";}
			$optDivisi.="<option ".$select." value=".$rDivisi['kodeorganisasi'].">".$rDivisi['namaorganisasi']."</option>";
		}
		echo $optDivisi;
    break;

	default:
	break;
}
?>
