<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kdPT=checkPostGet('kdPT','');
$kdUnit=checkPostGet('kdUnit','');
$kdDivisi=checkPostGet('kdDivisi','');
$Tahun=checkPostGet('Tahun','');
$ThnTanam=checkPostGet('ThnTanam','');
$tipeBudget='ESTATE';
$kdStatus=checkPostGet('kdStatus','');
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

		// Pilihan divisi
		$optDivisi2="";
		$sDivisi2= "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
					where kodeorganisasi<>'MHO' and detail='1' and induk='".$kdUnit."' and tipe not like 'GUDANG%' order by kodeorganisasi";
		$qDivisi2=mysql_query($sDivisi2) or die(mysql_error($conn));
		while($rDivisi2=mysql_fetch_assoc($qDivisi2)){
			$optDivisi2.="<option ".$select." value=".$rDivisi2['kodeorganisasi'].">".$rDivisi2['kodeorganisasi']."</option>";
		}
		// Pilihan TahunTanam
		$optThnTanam2="";
		$sThnTanam2="select distinct tahuntanam from ".$dbname.".setup_blok order by tahuntanam";
		$qThnTanam2=mysql_query($sThnTanam2) or die(mysql_error($conn));
		while($rThnTanam2=mysql_fetch_assoc($qThnTanam2)){
			$optThnTanam2.="<option value=".$rThnTanam2['tahuntanam'].">".$rThnTanam2['tahuntanam']."</option>";
		}
		// Pilihan kodebudget
		$optBudget2="";
		$sBudget2="select kodebudget,nama from ".$dbname.".bgt_kode where kodebudget not like 'EXPL%' and kodebudget not like 'SALES%' order by kodebudget";
		$qBudget2=mysql_query($sBudget2) or die(mysql_error($conn));
		while($rBudget2=mysql_fetch_assoc($qBudget2)){
			//$optBudget2.="<option value=".$rBudget2['kodebudget'].">[".$rBudget2['kodebudget']."]-".$rBudget2['nama']."</option>";
			$optBudget2.="<option value=".$rBudget2['kodebudget'].">".$rBudget2['kodebudget']."</option>";
		}
		// Pilihan kodekegiatan
		$optKegiatan2="";
		$sKegiatan2="select kodekegiatan,namakegiatan from ".$dbname.".setup_kegiatan where status='1' and (kodekegiatan like '126%' or kodekegiatan like '128%' or kodekegiatan like '61%' or kodekegiatan like '62%' or kodekegiatan like '7%') order by kodekegiatan";
		$qKegiatan2=mysql_query($sKegiatan2) or die(mysql_error($conn));
		while($rKegiatan2=mysql_fetch_assoc($qKegiatan2)){
			$optKegiatan2.="<option value=".$rKegiatan2['kodekegiatan'].">".$rKegiatan2['namakegiatan']."</option>";
		}
		// Pilihan kodebarang
		$optBrg2="<option value=''></option>";
		//$sBrg2="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where inactive='0' and (kodebarang like '31%' or kodebarang like '35%' or kodebarang like '36%' or kodebarang like '37%' or kodebarang like '38%' or kodebarang like '905%' or kodebarang like '906%' or kodebarang like '909%') and kodebarang not like '384%' order by kodebarang";
		$sBrg2="select a.kodebarang,b.namabarang,b.satuan from ".$dbname.".bgt_masterbarang a 
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=a.kodebarang
				where b.inactive='0' and a.hargasatuan>0 and a.regional='".$_SESSION['empl']['regional']."' and a.tahunbudget='".$Tahun."' order by kodebarang";
		$qBrg2=mysql_query($sBrg2) or die(mysql_error($conn));
		while($rBrg2=mysql_fetch_assoc($qBrg2)){
			$optBrg2.="<option value=".$rBrg2['kodebarang'].">".$rBrg2['namabarang']."</option>";
		}
		// Pilihan kodevhc
		$optVhc2="<option value=''></option>";
		$sVhc2="select kodevhc from ".$dbname.".vhc_5master where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['induk']."') order by kodevhc";
		$qVhc2=mysql_query($sVhc2) or die(mysql_error($conn));
		while($rVhc2=mysql_fetch_assoc($qVhc2)){
			$optVhc2.="<option value=".$rVhc2['kodevhc'].">".$rVhc2['kodevhc']."</option>";
		}
		//Data Detail
		$str="select a.*,b.namakegiatan,c.namabarang from ".$dbname.".bgt_unit a 
				left join ".$dbname.".setup_kegiatan b on b.kodekegiatan=a.kegiatan
				left join ".$dbname.".log_5masterbarang c on c.kodebarang=a.kodebarang
				where ".$where."
				order by a.posting,a.tahunbudget,a.divisi,a.kodevhc,a.kodebarang,a.kodebudget,a.kegiatan,a.noakun";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			$id=$bar->idbgtunit;
			$optDivisi3=$optDivisi2."<option selected value=".$bar->divisi.">".$bar->divisi."</option>";
			$optThnTanam3=$optThnTanam2."<option selected value=".$bar->tahuntanam.">".$bar->tahuntanam."</option>";
			$optBudget3=$optBudget2."<option selected value=".$bar->kodebudget.">".$bar->kodebudget."</option>";
			$optKegiatan3=$optKegiatan2."<option selected value=".$bar->kegiatan.">".$bar->namakegiatan."</option>";
			$optBrg3=$optBrg2."<option selected value=".$bar->kodebarang.">".$bar->namabarang."</option>";
			$optVhc3=$optVhc2."<option selected value=".$bar->kodevhc.">".$bar->kodevhc."</option>";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->tahunbudget."</td>
					<td ".$drcl." align=center><select id=divisi".$id." name=divisi".$id." onchange=\"SaveCell('".$bar->idbgtunit."','divisi','".$bar->divisi."');\">".$optDivisi3."</select></td>
					<td ".$drcl." align=center><select id=tahuntanam".$id." name=tahuntanam".$id." onchange=\"SaveCell('".$bar->idbgtunit."','tahuntanam','".$bar->tahuntanam."');\">".$optThnTanam3."</select></td>
					<td ".$drcl." align=left><select id=kodebudget".$id." name=kodebudget".$id." onchange=\"SaveCell('".$bar->idbgtunit."','kodebudget','".$bar->kodebudget."');\">".$optBudget3."</select></td>
					<td ".$drcl." align=right>".$bar->noakun."</td>
					<td ".$drcl." align=left><select id=kegiatan".$id." name=kegiatan".$id." style='width:300px' onchange=\"SaveCell('".$bar->idbgtunit."','kegiatan','".$bar->kegiatan."');\">".$optKegiatan3."</select></td>
					<td ".$drcl." align=left>".$bar->satuanv."</td>
					<td ".$drcl." align=right>".$bar->volume."</td>
					<td ".$drcl." align=right>".$bar->rotasi."</td>";
			/*
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->tahunbudget."</td>
					<td ".$drcl." align=center>".$bar->divisi."</td>
					<td ".$drcl." align=center>".$bar->tipebudget."</td>
					<td ".$drcl." align=left>".$bar->kodebudget."</td>
					<td ".$drcl." align=left>".$bar->namakegiatan."</td>
					<td ".$drcl." align=left>".$bar->satuanv."</td>
					<td ".$drcl." align=right>".$bar->volume."</td>
					<td ".$drcl." align=right>".$bar->rotasi."</td>";
			*/
			if($bar->kodebarang==''){
				echo"<td ".$drcl." align=right><input type=text id=jumlah".$id." value=".number_format($bar->jumlah,0)." onchange=\"SaveCell('".$bar->idbgtunit."','jumlah','".$bar->jumlah."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:30px;\"></td>";
			}else{
				echo"<td ".$drcl." align=right></td>";
			}
			echo "	<td ".$drcl." align=left><select id=kodebarang".$id." name=kodebarang".$id." style='width:160px' onchange=\"SaveCell('".$bar->idbgtunit."','kodebarang','".$bar->kodebarang."');\">".$optBrg3."</select></td>";
			if($bar->kodebarang==''){
				echo"<td ".$drcl." align=right></td>";
			}else{
				echo"<td ".$drcl." align=right><input type=text id=jumlah".$id." value=".number_format($bar->jumlah,2)." onchange=\"SaveCell('".$bar->idbgtunit."','jumlah','".$bar->jumlah."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>";
			}
			echo "	<td ".$drcl." align=left>".$bar->satuanj."</td>
					<td ".$drcl." align=left><select id=kodevhc".$id." name=kodevhc".$id." style='width:160px' onchange=\"SaveCell('".$bar->idbgtunit."','kodevhc','".$bar->kodevhc."');\">".$optVhc3."</select></td>
					<td ".$drcl." align=right>".number_format($bar->rupiah,2,'.',',')."</td>
					<td ".$drcl." align=right><input type=text id=sebaran01".$id." value=".number_format($bar->sebaran01,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran01','".$bar->sebaran01."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran02".$id." value=".number_format($bar->sebaran02,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran02','".$bar->sebaran02."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran03".$id." value=".number_format($bar->sebaran03,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran03','".$bar->sebaran03."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran04".$id." value=".number_format($bar->sebaran04,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran04','".$bar->sebaran04."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran05".$id." value=".number_format($bar->sebaran05,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran05','".$bar->sebaran05."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran06".$id." value=".number_format($bar->sebaran06,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran06','".$bar->sebaran06."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran07".$id." value=".number_format($bar->sebaran07,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran07','".$bar->sebaran07."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran08".$id." value=".number_format($bar->sebaran08,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran08','".$bar->sebaran08."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran09".$id." value=".number_format($bar->sebaran09,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran09','".$bar->sebaran09."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran10".$id." value=".number_format($bar->sebaran10,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran10','".$bar->sebaran10."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran11".$id." value=".number_format($bar->sebaran11,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran11','".$bar->sebaran11."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td ".$drcl." align=right><input type=text id=sebaran12".$id." value=".number_format($bar->sebaran12,1)." onchange=\"SaveCell('".$bar->idbgtunit."','sebaran12','".$bar->sebaran12."');\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:20px;\"></td>
					<td align=center>
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->idbgtunit."');\">
						<label style=\"width:200px;\">&nbsp&nbsp</label>";
			if($bar->posting==0){
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
		$luasdivisi=0;
		if(substr($kdKegiatan,0,1)=='1' or substr($kdKegiatan,0,2)=='61' or substr($kdKegiatan,0,2)=='62'){
			if(substr($kdKegiatan,0,1)=='1'){
				$sLookup = "select sum(a.luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok a
							left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
							where a.kodeorg like '".$kdDivisi."%' and a.statusblok like 'TB%' and b.detail='1'";
			}else{
				$sLookup = "select sum(a.luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok a
							left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
							where a.kodeorg like '".$kdDivisi."%' and a.statusblok = 'TM' and b.detail='1'";
			}
			$qLookup=mysql_query($sLookup) or die(mysql_error($conn));
			while($rLookup=mysql_fetch_assoc($qLookup)){
				$luasdivisi=$rLookup['luasdivisi'];
			}
		}
		//exit('Warning: '.$sLookup.' - '.$luasdivisi);
		$optSatKeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
		$optSatBrg=""; 
		if($kdBarang!=''){
			$optSatBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"inactive='0'");
			$satuanj=$optSatBrg[$kdBarang];
		}else{
			$satuanj='HK';
		}
		$optSatKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
		if($addedit=='update'){
			$strs="select * from ".$dbname.".bgt_unit 
					where tahunbudget='".$Tahun."' and divisi='".$kdDivisi."' and kodebudget='".$kdBudget."' and kegiatan='".$kdKegiatan."' and noakun='".substr($kdKegiatan,0,7)."' and kodevhc='".$kdVhc."' and kodebarang='".$kdBarang."' and tahuntanam='".$ThnTanam."'";
			$ress=mysql_query($strs);
			$nums=mysql_num_rows($ress);
			if($rows>0){
				exit('Warning : Data Sudah Ada...!');
			}
			$strx="update ".$dbname.".bgt_unit set tahunbudget='".$Tahun."',divisi='".$kdDivisi."',tahuntanam='".$ThnTanam."',kodebudget='".$kdBudget."'
				,kegiatan='".$kdKegiatan."',noakun='".substr($kdKegiatan,0,7)."',satuanv='".$optSatKeg[$kdKegiatan]."',volume='".$luasdivisi."'
				,kodevhc='".$kdVhc."',kodebarang='".$kdBarang."',satuanj='".$satuanj."',keterangan='".$_SESSION['standard']['username']."'
				,updateby='".$_SESSION['standard']['userid']."',lastupdate=now() 
				where idbgtunit='".$idbgtunit."'";
		}else{
			$strx="insert into ".$dbname.".bgt_unit
				(tahunbudget,divisi,tahuntanam,tipebudget,kodebudget,kegiatan,noakun,satuanv,volume,kodevhc,kodebarang
				,regional,rotasi,jumlah,satuanj,keterangan,updateby,lastupdate) values ('".$Tahun."','".$kdDivisi."','".$ThnTanam."','".$tipeBudget."','".$kdBudget."','".$kdKegiatan."','".substr($kdKegiatan,0,7)."'
				,'".$optSatKeg[$kdKegiatan]."','".$luasdivisi."','".$kdVhc."','".$kdBarang."','".$_SESSION['empl']['regional']."',0,0,'".$satuanj."'
				,'".$_SESSION['standard']['username']."','".$_SESSION['standard']['userid']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'salinData':
		$optSatKeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
		$optSatBrg=""; 
		if($kdBarang!=''){
			$optSatBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"inactive='0'");
			$satuanj=$optSatBrg[$kdBarang];
		}else{
			$satuanj='HK';
		}
		$sUnitx="select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kdUnit."%' and kodeorganisasi like '".$kdDivisi."%' 
				and length(kodeorganisasi)=6 and (substr(kodeorganisasi,5,1)='0' or substr(kodeorganisasi,5,1)='1') order by kodeorganisasi";
		$qUnitx=mysql_query($sUnitx) or die(mysql_error($conn));
		while($rUnitx=mysql_fetch_assoc($qUnitx)){
			$Divisi=$rUnitx['kodeorganisasi'];
			$sBlokx="select DISTINCT tahuntanam,sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok where kodeorg like '".$Divisi."%' 
					group by tahuntanam
					order by tahuntanam";
			$qBlokx=mysql_query($sBlokx) or die(mysql_error($conn));
			while($rBlokx=mysql_fetch_assoc($qBlokx)){
				$ThnTnm=$rBlokx['tahuntanam'];
				$luasdivisi=$rBlokx['luasdivisi'];
				$sKegiatan="select kodekegiatan from ".$dbname.".setup_kegiatan where status='1' and kelompok='".$kdStatus."' 
							and (kodekegiatan like '126%' or (kodekegiatan between 128000000 and 128129999) or kodekegiatan like '61%' or kodekegiatan like '62%') order by kodekegiatan";
				$qKegiatan=mysql_query($sKegiatan) or die(mysql_error($conn));
				while($rKegiatan=mysql_fetch_assoc($qKegiatan)){
					$kodegiat=$rKegiatan['kodekegiatan'];
					$strs="select * from ".$dbname.".bgt_unit 
							where tahunbudget='".$Tahun."' and divisi='".$Divisi."' and kodebudget='".$kdBudget."' and kegiatan='".$kodegiat."' and noakun='".substr($kodegiat,0,7)."' and kodevhc='".$kdVhc."' and kodebarang='".$kdBarang."' and tahuntanam='".$ThnTnm."'";
					$ress=mysql_query($strs);
					$nums=mysql_num_rows($ress);
					if($nums==0){
						//Insert Into
						$strx="insert into ".$dbname.".bgt_unit
								(tahunbudget,divisi,tahuntanam,tipebudget,kodebudget,kegiatan,noakun,satuanv,volume,kodevhc,kodebarang
								,regional,rotasi,jumlah,satuanj,keterangan,updateby,lastupdate) values ('".$Tahun."','".$Divisi."','".$ThnTnm."','".$tipeBudget."','".$kdBudget."','".$kodegiat."','".substr($kodegiat,0,7)."'
								,'".$optSatKeg[$kodegiat]."','".$luasdivisi."','','".$kdBarang."','".$_SESSION['empl']['regional']."',0,0,'".$satuanj."'
								,'".$_SESSION['standard']['username']."','".$_SESSION['standard']['userid']."',now())";
						if(!mysql_query($strx)){
							echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
						}
					}
				}
			}
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
			$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."',updateby='".$_SESSION['standard']['userid']."',lastupdate=now(),rupiah=".$rupiah."
					where idbgtunit='".$idbgtunit."'";
		}else if(substr($cellname,0,7)=='sebaran'){
			$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."'
					,rotasi=sebaran01+sebaran02+sebaran03+sebaran04+sebaran05+sebaran06+sebaran07+sebaran08+sebaran09+sebaran10+sebaran11+sebaran12
					,updateby='".$_SESSION['standard']['userid']."',lastupdate=now() 
					where idbgtunit='".$idbgtunit."'";
		}else{
			if($cellname=='kegiatan'){
				$optSatKeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"status='1'");
				$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."',noakun='".substr($cellvalue,0,7)."',satuanv='".$optSatKeg[$cellvalue]."'
						,updateby='".$_SESSION['standard']['userid']."',lastupdate=now() where idbgtunit='".$idbgtunit."'";
			}else{
				$strx="update ".$dbname.".bgt_unit set ".$cellname."='".$cellvalue."',updateby='".$_SESSION['standard']['userid']."',lastupdate=now() 
						where idbgtunit='".$idbgtunit."'";
			}
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

    case'getKegiatan':
		$optKegiatan="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKegiatan="select kodekegiatan,namakegiatan from ".$dbname.".setup_kegiatan where status='1' and kelompok='".$kdStatus."' 
					and (kodekegiatan like '126%' or (kodekegiatan between 128000000 and 128129999) or kodekegiatan like '61%' or kodekegiatan like '62%') order by kodekegiatan";
		$qKegiatan=mysql_query($sKegiatan) or die(mysql_error($conn));
		while($rKegiatan=mysql_fetch_assoc($qKegiatan)){
			$optKegiatan.="<option value=".$rKegiatan['kodekegiatan'].">[".$rKegiatan['kodekegiatan']."]-".$rKegiatan['namakegiatan']."</option>";
		}
		//exit('Warning: '.$sKegiatan);
		echo $optKegiatan;
    break;

	case'postingData':
		$sBgt="select * from ".$dbname.".bgt_unit where idbgtunit='".$idbgtunit."'";
		$qBgt=mysql_query($sBgt) or die(mysql_error($conn));
		while($rBgt=mysql_fetch_assoc($qBgt)){
			$tahunbudget=$rBgt['tahunbudget'];
			$divisi		=$rBgt['divisi'];
			$tahuntanam	=$rBgt['tahuntanam'];
			$tipebudget	=$rBgt['tipebudget'];
			$kodebudget	=$rBgt['kodebudget'];
			$kegiatan	=$rBgt['kegiatan'];
			$noakun		=$rBgt['noakun'];
			$volume		=$rBgt['volume'];
			$satuanv	=$rBgt['satuanv'];
			$rupiah		=$rBgt['rupiah'];
			$kodevhc	=$rBgt['kodevhc'];
			$kodebarang	=$rBgt['kodebarang'];
			$rotasi		=$rBgt['rotasi'];
			$regional	=$rBgt['regional'];
			$jumlah		=$rBgt['jumlah'];
			$satuanj	=$rBgt['satuanj'];
			$keterangan	=$rBgt['keterangan'];
			$sebaran01	=$rBgt['sebaran01'];
			$sebaran02	=$rBgt['sebaran02'];
			$sebaran03	=$rBgt['sebaran03'];
			$sebaran04	=$rBgt['sebaran04'];
			$sebaran05	=$rBgt['sebaran05'];
			$sebaran06	=$rBgt['sebaran06'];
			$sebaran07	=$rBgt['sebaran07'];
			$sebaran08	=$rBgt['sebaran08'];
			$sebaran09	=$rBgt['sebaran09'];
			$sebaran10	=$rBgt['sebaran10'];
			$sebaran11	=$rBgt['sebaran11'];
			$sebaran12	=$rBgt['sebaran12'];
			$sebaranAll	=$sebaran01+$sebaran02+$sebaran03+$sebaran04+$sebaran05+$sebaran06+$sebaran07+$sebaran08+$sebaran09+$sebaran10+$sebaran11+$sebaran12;
			if($sebaranAll==0){
				$sebaran01	=1;
				$sebaran02	=1;
				$sebaran03	=1;
				$sebaran04	=1;
				$sebaran05	=1;
				$sebaran06	=1;
				$sebaran07	=1;
				$sebaran08	=1;
				$sebaran09	=1;
				$sebaran10	=1;
				$sebaran11	=1;
				$sebaran12	=1;
				$sebaranAll	=12;
			}
			$rotasi=$sebaranAll;
		}
		$kunci=0;
		$sBgt="select kunci from ".$dbname.".bgt_budget order by kunci desc limit 1";
		$qBgt=mysql_query($sBgt) or die(mysql_error($conn));
		while($rBgt=mysql_fetch_assoc($qBgt)){
			$kunci=$rBgt['kunci'];
		}
		$luasdivisi=$jumlah;
		if(substr($kegiatan,0,1)=='1' or substr($kegiatan,0,2)=='61' or substr($kegiatan,0,2)=='62'){
			if(substr($kegiatan,0,1)=='1'){
				$sBlok1="select sum(a.luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok a
						left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
						where a.kodeorg like '".$divisi."%' and a.statusblok like 'TB%' and b.detail='1' and a.tahuntanam='".$tahuntanam."'";
				$sBlok2="select a.kodeorg,a.luasareaproduktif from ".$dbname.".setup_blok a
						left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
						where a.kodeorg like '".$divisi."%' and a.statusblok like 'TB%' and b.detail='1' and a.tahuntanam='".$tahuntanam."'";
			}else{
				$sBlok1="select sum(a.luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok a
						left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
						where a.kodeorg like '".$divisi."%' and a.statusblok = 'TM' and b.detail='1' and a.tahuntanam='".$tahuntanam."'";
				$sBlok2="select a.kodeorg,a.luasareaproduktif from ".$dbname.".setup_blok a
						left join ".$dbname.".organisasi b on b.kodeorganisasi=a.kodeorg
						where a.kodeorg like '".$divisi."%' and a.statusblok = 'TM' and b.detail='1' and a.tahuntanam='".$tahuntanam."'";
			}
			$qBlok=mysql_query($sBlok1) or die(mysql_error($conn));
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$luasdivisi=$rBlok['luasdivisi'];
			}
			$qBlok=mysql_query($sBlok2) or die(mysql_error($conn));
			$nBlok=mysql_num_rows($qBlok);
			if($nBlok==0){
				exit('Warning: Data Tidak ada...!');
			}
			while($rBlok=mysql_fetch_assoc($qBlok)){
				$kunci+=1;
				$rupiahblok=$rBlok['luasareaproduktif']/$luasdivisi*$rupiah;
				$jumlahblok=$rBlok['luasareaproduktif']/$luasdivisi*$jumlah;
				$strx1="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg,tipebudget,kodebudget,kegiatan,noakun
				,volume,satuanv,rupiah,kodevhc,kodebarang,rotasi,kunci,regional
				,updateby,lastupdate,jumlah,satuanj,keterangan,tutup) values
				('".$tahunbudget."','".$rBlok['kodeorg']."','".$tipebudget."','".$kodebudget."','".$kegiatan."','".substr($kegiatan,0,7)."'
				,'".$rBlok['luasareaproduktif']."','".$satuanv."','".$rupiahblok."','".$kodevhc."','".$kodebarang."','".$rotasi."','".$kunci."','".$regional."'
				,'".$_SESSION['standard']['userid']."',now(),'".$jumlahblok."','".$satuanj."','".$_SESSION['standard']['username']."','1')";
				if(!mysql_query($strx1)){
					echo " Gagal, ".$strx1,' '.addslashes(mysql_error($conn));
				}
				$rupiahblok01=$sebaran01/$sebaranAll*$rupiahblok;
				$rupiahblok02=$sebaran02/$sebaranAll*$rupiahblok;
				$rupiahblok03=$sebaran03/$sebaranAll*$rupiahblok;
				$rupiahblok04=$sebaran04/$sebaranAll*$rupiahblok;
				$rupiahblok05=$sebaran05/$sebaranAll*$rupiahblok;
				$rupiahblok06=$sebaran06/$sebaranAll*$rupiahblok;
				$rupiahblok07=$sebaran07/$sebaranAll*$rupiahblok;
				$rupiahblok08=$sebaran08/$sebaranAll*$rupiahblok;
				$rupiahblok09=$sebaran09/$sebaranAll*$rupiahblok;
				$rupiahblok10=$sebaran10/$sebaranAll*$rupiahblok;
				$rupiahblok11=$sebaran11/$sebaranAll*$rupiahblok;
				$rupiahblok12=$sebaran12/$sebaranAll*$rupiahblok;

				$jumlahblok01=$sebaran01/$sebaranAll*$jumlahblok;
				$jumlahblok02=$sebaran02/$sebaranAll*$jumlahblok;
				$jumlahblok03=$sebaran03/$sebaranAll*$jumlahblok;
				$jumlahblok04=$sebaran04/$sebaranAll*$jumlahblok;
				$jumlahblok05=$sebaran05/$sebaranAll*$jumlahblok;
				$jumlahblok06=$sebaran06/$sebaranAll*$jumlahblok;
				$jumlahblok07=$sebaran07/$sebaranAll*$jumlahblok;
				$jumlahblok08=$sebaran08/$sebaranAll*$jumlahblok;
				$jumlahblok09=$sebaran09/$sebaranAll*$jumlahblok;
				$jumlahblok10=$sebaran10/$sebaranAll*$jumlahblok;
				$jumlahblok11=$sebaran11/$sebaranAll*$jumlahblok;
				$jumlahblok12=$sebaran12/$sebaranAll*$jumlahblok;
				$strx2="insert into ".$dbname.".bgt_distribusi (kunci,rp01,fis01,rp02,fis02,rp03,fis03,rp04,fis04,rp05,fis05,rp06,fis06
				,rp07,fis07,rp08,fis08,rp09,fis09,rp10,fis10,rp11,fis11,rp12,fis12	,updateby,lastupdate) values
				('".$kunci."','".$rupiahblok01."','".$jumlahblok01."','".$rupiahblok02."','".$jumlahblok02."','".$rupiahblok03."','".$jumlahblok03."'
				,'".$rupiahblok04."','".$jumlahblok04."','".$rupiahblok05."','".$jumlahblok05."','".$rupiahblok06."','".$jumlahblok06."'
				,'".$rupiahblok07."','".$jumlahblok07."','".$rupiahblok08."','".$jumlahblok08."','".$rupiahblok09."','".$jumlahblok09."'
				,'".$rupiahblok10."','".$jumlahblok10."','".$rupiahblok11."','".$jumlahblok11."','".$rupiahblok12."','".$jumlahblok12."'
				,'".$_SESSION['standard']['userid']."',now())";
				if(!mysql_query($strx2)){
					echo " Gagal, ".$strx2,' '.addslashes(mysql_error($conn));
				}
			}
		}else{
			$kunci+=1;
			$rupiahblok=$rupiah;
			$jumlahblok=$jumlah;
			$strx1="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg,tipebudget,kodebudget,kegiatan,noakun
			,volume,satuanv,rupiah,kodevhc,kodebarang,rotasi,kunci,regional
			,updateby,lastupdate,jumlah,satuanj,keterangan,tutup) values
			('".$tahunbudget."','".$divisi."','".$tipebudget."','".$kodebudget."','".$kegiatan."','".substr($kegiatan,0,7)."'
			,'".$jumlah."','".$satuanj."','".$rupiahblok."','".$kodevhc."','".$kodebarang."','".$rotasi."','".$kunci."','".$regional."'
			,'".$_SESSION['standard']['userid']."',now(),'".$jumlahblok."','".$satuanj."','".$_SESSION['standard']['username']."','1')";
			if(!mysql_query($strx1)){
				echo " Gagal, ".$strx1,' '.addslashes(mysql_error($conn));
			}
			$rupiahblok01=$sebaran01/$sebaranAll*$rupiahblok;
			$rupiahblok02=$sebaran02/$sebaranAll*$rupiahblok;
			$rupiahblok03=$sebaran03/$sebaranAll*$rupiahblok;
			$rupiahblok04=$sebaran04/$sebaranAll*$rupiahblok;
			$rupiahblok05=$sebaran05/$sebaranAll*$rupiahblok;
			$rupiahblok06=$sebaran06/$sebaranAll*$rupiahblok;
			$rupiahblok07=$sebaran07/$sebaranAll*$rupiahblok;
			$rupiahblok08=$sebaran08/$sebaranAll*$rupiahblok;
			$rupiahblok09=$sebaran09/$sebaranAll*$rupiahblok;
			$rupiahblok10=$sebaran10/$sebaranAll*$rupiahblok;
			$rupiahblok11=$sebaran11/$sebaranAll*$rupiahblok;
			$rupiahblok12=$sebaran12/$sebaranAll*$rupiahblok;

			$jumlahblok01=$sebaran01/$sebaranAll*$jumlahblok;
			$jumlahblok02=$sebaran02/$sebaranAll*$jumlahblok;
			$jumlahblok03=$sebaran03/$sebaranAll*$jumlahblok;
			$jumlahblok04=$sebaran04/$sebaranAll*$jumlahblok;
			$jumlahblok05=$sebaran05/$sebaranAll*$jumlahblok;
			$jumlahblok06=$sebaran06/$sebaranAll*$jumlahblok;
			$jumlahblok07=$sebaran07/$sebaranAll*$jumlahblok;
			$jumlahblok08=$sebaran08/$sebaranAll*$jumlahblok;
			$jumlahblok09=$sebaran09/$sebaranAll*$jumlahblok;
			$jumlahblok10=$sebaran10/$sebaranAll*$jumlahblok;
			$jumlahblok11=$sebaran11/$sebaranAll*$jumlahblok;
			$jumlahblok12=$sebaran12/$sebaranAll*$jumlahblok;
			$strx2="insert into ".$dbname.".bgt_distribusi (kunci,rp01,fis01,rp02,fis02,rp03,fis03,rp04,fis04,rp05,fis05,rp06,fis06
			,rp07,fis07,rp08,fis08,rp09,fis09,rp10,fis10,rp11,fis11,rp12,fis12	,updateby,lastupdate) values
			('".$kunci."','".$rupiahblok01."','".$jumlahblok01."','".$rupiahblok02."','".$jumlahblok02."','".$rupiahblok03."','".$jumlahblok03."'
			,'".$rupiahblok04."','".$jumlahblok04."','".$rupiahblok05."','".$jumlahblok05."','".$rupiahblok06."','".$jumlahblok06."'
			,'".$rupiahblok07."','".$jumlahblok07."','".$rupiahblok08."','".$jumlahblok08."','".$rupiahblok09."','".$jumlahblok09."'
			,'".$rupiahblok10."','".$jumlahblok10."','".$rupiahblok11."','".$jumlahblok11."','".$rupiahblok12."','".$jumlahblok12."'
			,'".$_SESSION['standard']['userid']."',now())";
			if(!mysql_query($strx2)){
				echo " Gagal, ".$strx2,' '.addslashes(mysql_error($conn));
			}
		}
		$strx3="update ".$dbname.".bgt_unit set volume='".$luasdivisi."',rotasi='".$rotasi."',posting='1',sebaran01='".$sebaran01."',sebaran02='".$sebaran02."'
				,sebaran03='".$sebaran03."',sebaran04='".$sebaran04."',sebaran05='".$sebaran05."',sebaran06='".$sebaran06."',sebaran07='".$sebaran07."'
				,sebaran08='".$sebaran08."',sebaran09='".$sebaran09."',sebaran10='".$sebaran10."',sebaran11='".$sebaran11."',sebaran12='".$sebaran12."'
				where idbgtunit='".$idbgtunit."'";
		if(!mysql_query($strx3)){
			echo " Gagal, ".$strx3,' '.addslashes(mysql_error($conn));
		}
		//exit('Warning: '.$luasdivisi);
	break;

	default:
	break;
}
?>
