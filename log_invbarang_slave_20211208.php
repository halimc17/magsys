<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

$proses=$_POST['proses'];
$kodeorg=checkPostGet('kodeorg','');
$kodebarang=checkPostGet('kodebarang','');
$kodebrglama=checkPostGet('kodebrglama','');
$kodeinv=checkPostGet('kodeinv','');
$namainv=checkPostGet('namainv','');
$merkinv=checkPostGet('merkinv','');
$tipeinv=checkPostGet('tipeinv','');
$ketinv=checkPostGet('ketinv','');
$ukuraninv=checkPostGet('ukuraninv','');
$warnainv=checkPostGet('warnainv','');
$bahaninv=checkPostGet('bahaninv','');
$tglbeli=tanggalsystem(checkPostGet('tglbeli',''));//merubah dari 10-10-2014 menjadi 20141010
$hrgbeli=checkPostGet('hrgbeli',0);
$nopo=checkPostGet('nopo','');
$kodesupplier=checkPostGet('kodesupplier','');
$nik=checkPostGet('nik','');
$tgldiuser=tanggalsystem(checkPostGet('tgldiuser',''));//merubah dari 10-10-2014 menjadi 20141010
$kondisi=checkPostGet('kondisi','');
$divisi=checkPostGet('divisi','');
$lokasi=checkPostGet('lokasi','');
$ruangan=checkPostGet('ruangan','');
$addedit=checkPostGet('addedit','');
$carikodeorg=checkPostGet('carikodeorg','');
$carinamainv=checkPostGet('carinamainv','');
$carikaryawan=checkPostGet('carikaryawan','');
$cariruangan=checkPostGet('cariruangan','');
switch($proses){
	case'loadData':
		$where="True";
		if($carikodeorg!=''){
			$where.=" and a.kodeorg='".$carikodeorg."'";
		}
		if($carinamainv!=''){
			$where.=" and a.namainventaris like '%".$carinamainv."%'";
		}
		if($carikaryawan!=''){
			$where.=" and a.nik='".$carikaryawan."'";
		}
		if($cariruangan!=''){
			$where.=" and a.ruangan like '%".$cariruangan."%'";
		}
		$strb="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
				left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
				left join ".$dbname.".datakaryawan d on d.nik=a.nik
				where ".$where." 
				order by a.kodeorg,a.kodebarang,a.kodeinventaris";
		//exit('Warning: '.$strb);
		$resb=mysql_query($strb);
		$jlhbrs=mysql_num_rows($resb);
		$limit=15;
		$page=0;
		if(isset($_POST['page'])){
			$page=checkPostGet('page',0);
			if((($page*$limit)+1)>$jlhbrs)
				$page=$page-1;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;
		$str="select a.*,c.namasupplier as supplier,d.namakaryawan as karyawan from ".$dbname.".log_invbarang a 
				left join ".$dbname.".log_5supplier c on c.supplierid=a.kodesupplier
				left join ".$dbname.".datakaryawan d on d.nik=a.nik
				where ".$where." 
				order by a.kodeorg,a.kodebarang,a.kodeinventaris limit ".$offset.",".$limit."";
		//exit('Warning: '.$str);
		$res=mysql_query($str);
		$awalmesin='';
		while($bar=mysql_fetch_object($res)){
			//$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
			$drcl="";
			echo"<tr class=rowcontent >
					<td ".$drcl." align=center>".$bar->kodeorg."</td>
					<td ".$drcl." align=center>".$bar->kodebarang."</td>
					<td ".$drcl." align=center>".$bar->kodeinventaris."</td>
					<td ".$drcl." align=left>".$bar->namainventaris."</td>
					<td ".$drcl." align=left>".$bar->merkinventaris."</td>
					<td ".$drcl." align=left>".$bar->tipeinventaris."</td>
					<td ".$drcl." align=left>".$bar->ketinventaris."</td>
					<td ".$drcl." align=left>".$bar->ukuran."</td>
					<td ".$drcl." align=left>".$bar->warna."</td>
					<td ".$drcl." align=left>".$bar->bahan."</td>
					<td ".$drcl." align=center>".tanggalnormal($bar->tglperolehan)."</td>
					<td ".$drcl." align=right>".number_format($bar->hargaperolehan,0,'.',',')."</td>
					<td ".$drcl." align=left>".$bar->nopo."</td>
					<td ".$drcl." align=left>".$bar->supplier."</td>
					<td ".$drcl." align=left>".$bar->karyawan."</td>
					<td ".$drcl." align=center>".tanggalnormal($bar->tgluserterima)."</td>
					<td ".$drcl." align=left>".$bar->kondisi."</td>
					<td ".$drcl." align=left>".$bar->divisi."</td>
					<td ".$drcl." align=left>".$bar->lokasi."</td>
					<td ".$drcl." align=left>".$bar->ruangan."</td>
					<td align=center width='6%'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".$bar->kodebarang."','".$bar->kodeinventaris."','".$bar->namainventaris."','".$bar->merkinventaris."','".$bar->tipeinventaris."','".$bar->ketinventaris."','".$bar->ukuran."','".$bar->warna."','".$bar->bahan."','".tanggalnormal($bar->tglperolehan)."','".$bar->hargaperolehan."','".$bar->nopo."','".$bar->kodesupplier."','".$bar->nik."','".tanggalnormal($bar->tgluserterima)."','".$bar->kondisi."','".$bar->divisi."','".$bar->lokasi."','".$bar->ruangan."')\">&nbsp
						<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->kodebarang."','".$bar->kodeinventaris."');\">&nbsp
						<img src=images/pdf.jpg class=resicon title='Print QR Code' onclick=\"previewQRCode('".$bar->kodeorg."','".$bar->kodebarang."','".$bar->kodeinventaris."','".$bar->namainventaris."','".$bar->nik."','".$bar->ruangan."','1',event);\">
					</td>
				</tr>";	
		}
		echo"
		<tr class=rowheader>
			<td colspan=20 align=center>
				<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>&nbsp
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."&nbsp
				<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
		</tr>";
	break;

	case'delData':
		$strx="delete from ".$dbname.".log_invbarang 
				where kodeorg='".$kodeorg."' and kodebarang='".$kodebarang."' and kodeinventaris='".$kodeinv."'";
		if(!mysql_query($strx)){
			echo " Gagal, ".addslashes(mysql_error($conn));
		}
	break;

	case'saveData':
		$namakaryawan='';
		if($nik!=''){
			$sKar="select namakaryawan from ".$dbname.".datakaryawan where nik='".$nik."'";
			$qKar=mysql_query($sKar);
			while($rKar=mysql_fetch_object($qKar)){
				$namakaryawan=$rKar->namakaryawan;
			}
		}
		$namasupplier='';
		if($kodesupplier!=''){
			$sKar="select namasupplier from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
			$qKar=mysql_query($sKar);
			while($rKar=mysql_fetch_object($qKar)){
				$namasupplier=$rKar->namasupplier;
			}
		}
		//exit('Warning sup='.$namasupplier.' kary='.$namakaryawan);
		if($addedit=='update'){
			//exit('Warning: kodebrglama='.$kodebrglama.' baru='.$kodebarang);
			if($kodebrglama!=$kodebarang){
				$strs="select * from ".$dbname.".log_invbarang
						where (kodeorg='".$kodeorg."' and kodebarang='".$kodebarang."' and kodeinventaris='".$kodeinv."')";
				$ress=mysql_query($strs);
				$rows=mysql_num_rows($ress);
				if($rows>0){
					exit('Warning : Data Sudah Ada...!');
				}
			}
			$strx="update ".$dbname.".log_invbarang set kodeorg='".$kodeorg."',kodebarang='".$kodebarang."',kodeinventaris='".$kodeinv."',namainventaris='".$namainv."'
					,merkinventaris='".$merkinv."',tipeinventaris='".$tipeinv."',ketinventaris='".$ketinv."',ukuran='".$ukuraninv."',warna='".$warnainv."',bahan='".$bahaninv."',tglperolehan='".$tglbeli."',hargaperolehan=".round($hrgbeli,2).",nopo='".$nopo."',kodesupplier='".$kodesupplier."',namasupplier='".$namasupplier."',nik='".$nik."',namakaryawan='".$namakaryawan."',tgluserterima='".$tgldiuser."',kondisi='".$kondisi."',divisi='".$divisi."',lokasi='".$lokasi."',ruangan='".$ruangan."'
					,lastuser='".$_SESSION['standard']['username']."',lastdate=now()
					where kodeorg='".$kodeorg."' and kodebarang='".$kodebrglama."' and kodeinventaris='".$kodeinv."'";
		}else{
			$strx="insert into ".$dbname.".log_invbarang
					(kodeorg,kodebarang,kodeinventaris,namainventaris,merkinventaris,tipeinventaris,ketinventaris,ukuran,warna,bahan,tglperolehan,hargaperolehan
					,nopo,kodesupplier,namasupplier,nik,namakaryawan,tgluserterima,kondisi,divisi,lokasi,ruangan,aktif,lastuser,lastdate)
					values('".$kodeorg."','".$kodebarang."','".$kodeinv."','".$namainv."','".$merkinv."','".$tipeinv."','".$ketinv."','".$ukuraninv."'
					,'".$warnainv."','".$bahaninv."','".$tglbeli."','".round($hrgbeli,2)."','".$nopo."','".$kodesupplier."','".$namasupplier."'
					,'".$nik."','".$namakaryawan."','".$tgldiuser."','".$kondisi."','".$divisi."','".$lokasi."','".$ruangan."'
					,'1','".$_SESSION['standard']['username']."',now())";
		}
		//exit('Warning: '.$strx);
		if(!mysql_query($strx)){
			echo " Gagal, ".$strx,' '.addslashes(mysql_error($conn));
		}
	break;

	case'getBarang':
		$optBarang="";
		if($kodebarang!=''){
			$sBarang="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";     
			$qBarang=mysql_query($sBarang) or die(mysql_error($conn));
			while($rBarang=mysql_fetch_assoc($qBarang)){
				$optBarang=$rBarang['namabarang'];
			}
		}
		echo $optBarang;
    break;

	default:
	break;
}
?>
