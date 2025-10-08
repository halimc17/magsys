<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/sdm_2rekapabsen.js"></script>
<link rel=stylesheet type='text/css' href='style/generic.css'>
<?php
	$kdUnit=$_POST['kdUnit'];
	$periode=$_POST['periode'];
	$tipe=$_POST['tipe'];
	$tipekaryawan=$_POST['tipekaryawan'];
	$idkomp=$_POST['idkomp'];
	$dtsub=$_POST['dtsub'];

	if($kdUnit=='')$kdUnit=$_GET['kdUnit'];
	if($periode=='')$periode=$_GET['periode'];
	if($tipe=='')$tipe=$_GET['tipe'];
	if($tipekaryawan=='')$tipekaryawan=$_GET['tipekaryawan'];
	if($idkomp=='')$idkomp=$_GET['idkomp'];
	if($dtsub=='')$dtsub=$_GET['dtsub'];

	$whr='';
	//if($kdUnit!='')$whr.=" and a.kodeorg='".$kdUnit."'  and b.lokasitugas='".$kdUnit."'";
	//if($periode!='')$whr.=" and periodegaji='".$periode."'";
	if($tipe!=''){
		$whr.=" and b.tipekaryawan='".$tipe."'";
	}
	if($idkomp!=''){
		$whr.=" and a.idkomponen in (".$idkomp.")";
	}
	if($dtsub!=''){
		if($dtsub=='Kantor'){
			$whr.=" and b.subbagian=''";
		}else{
			$whr.=" and b.subbagian='".$dtsub."'";
		}
	}

	$strz= "select b.lokasitugas,a.*,b.nik,b.namakaryawan,d.namajabatan,c.tipe as tipekaryawan
			from ".$dbname.".sdm_gajidetail_vw a
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
			left join ".$dbname.".sdm_5tipekaryawan c on b.tipekaryawan=c.id
			left join ".$dbname.".sdm_5jabatan d on b.kodejabatan=d.kodejabatan
			where (a.plus=1 or a.idkomponen='30' or a.idkomponen='31' or a.idkomponen='37' ) 
				and a.kodeorg='".$kdUnit."'  and b.lokasitugas='".$kdUnit."'
				and periodegaji='".$periode."' ".$whr."
			group by a.karyawanid,b.tipekaryawan,a.idkomponen
			ORDER BY a.karyawanid,b.tipekaryawan,a.plus desc,a.idkomponen
			";
	//exit('Warning : '.$strz);
	$resz=mysql_query($strz);
	$row =mysql_num_rows($resz);
	if($_GET['type']!='excel'){
		$stream="<table class=sortable border=0 cellspacing=1>";
	}else{
		$stream="<table class=sortable border=1 cellspacing=1>";
	}
	$stream.="
      <thead>
        <tr class=rowcontent>
          <td align=center>No</td>
          <td align=center>Unit</td>
          <td align=center>Periode</td>
          <td align=center>NIK</td>
          <td>Nama Karyawan</td>
          <td>Jabatan</td>
          <td>Level</td>
          <td>komponen</td>
          <td align=right>Jumlah</td>
        </tr>
      </thead>
      <tbody>";
        if($row==0){
            $stream.="<tr class=rowcontent>";
            $stream.="<td colspan=4>Data not found...</td>";
            $stream.="</tr>";
        }else{
			$jumlah=0;
			while($barz=mysql_fetch_object($resz)){
				$no+=1;
                $stream.="<tr class=rowcontent>";
                $stream.="<td align=center>".$no."</td>";
                $stream.="<td align=center>".$barz->kodeorg."</td>";
                $stream.="<td align=center>".$barz->periodegaji."</td>";
                $stream.="<td align=center>".$barz->nik."</td>";
                $stream.="<td align=left>".$barz->namakaryawan."</td>";
                $stream.="<td align=left>".$barz->namajabatan."</td>";
                $stream.="<td align=left>".$barz->tipekaryawan."</td>";
                $stream.="<td align=left>".$barz->name."</td>";
				if($barz->plus!='1'){
		            $stream.="<td align=right>".number_format($barz->jumlah*-1,2)."</td>";
					$jumlah-=$barz->jumlah;
				}else{
	                $stream.="<td align=right>".number_format($barz->jumlah,2)."</td>";
					$jumlah+=$barz->jumlah;
				}
                $stream.="</tr>";
			}
			$stream.="</tbody><thead><tr>";
			$stream.="<td align=center colspan=8>Total</td>";
			$stream.="<td align=right>".number_format($jumlah,2)."</td>";
			$stream.="</tr></thead></tbody>";
		}
   $stream.="</tbody></table>";
   echo $stream;
?>