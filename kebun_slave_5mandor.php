<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$mandor=checkPostGet('mandor','');
$karyawan=checkPostGet('karyawan','');
$urut=checkPostGet('urut','');
$aktif=checkPostGet('aktif','');
$status=checkPostGet('status','');

switch($method)
{
    case'tampilmandor': // nampilin data mandor yang punya karyawan
    $str="select distinct(a.mandorid), b.namakaryawan from ".$dbname.".kebun_5mandor a
        left join ".$dbname.".datakaryawan b on a.mandorid = b.karyawanid
        ";
    $res=mysql_query($str) or die(mysql_error($conn));
    while($bar=mysql_fetch_assoc($res))
    {
        $no+=1;	
        echo"<tr class=rowcontent>
        <td>".$no."</td>
        <td align=left>".$bar['namakaryawan']."</td>
		<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"pilihmandor('".$bar['mandorid']."');\"></td>
        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapusmandor('".$bar['mandorid']."');\"></td>
        </tr>";	
    }     
    break;
    
    case'tampilkaryawan': // nampilin pilihan karyawan setelah pilih mandor
        $optkaryawan='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
        $str="select t1.karyawanid, t1.namakaryawan from ".$dbname.".datakaryawan t1
            where t1.lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0
                and t1.karyawanid != '".$mandor."' and not exists (select t2.karyawanid from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.karyawanid) and not exists (select t2.mandorid  from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.mandorid)
            order by t1.namakaryawan";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $optkaryawan.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
        }
        echo $optkaryawan;
    break;
	
	case'syntampilmandor': 
        $optmandor='<option value=\'\'>'.$_SESSION['lang']['pilihdata'].'</option>';
        $str="select t1.karyawanid, t1.namakaryawan from ".$dbname.".datakaryawan t1
			where t1.lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and (t1.tanggalkeluar = '0000-00-00' or t1.tanggalkeluar > ".$_SESSION['org']['period']['start'].") and t1.alokasi = 0 and not exists (select t2.karyawanid from ".$dbname.".kebun_5mandor t2 where t1.karyawanid=t2.karyawanid)
			order by t1.namakaryawan";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
			if($bar->karyawanid==$mandor){
				$optmandor.="<option value='".$bar->karyawanid."' selected>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
			}else{
				$optmandor.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." [".$bar->karyawanid."]</option>";
			}
        }
        echo $optmandor;
    break;
	
	case'pilihmandor': // nampilin data karyawan yang dimandori
    $no=0;
    $str="select a.karyawanid, b.namakaryawan, a.statusaktif, a.mandorid, a.nourut from ".$dbname.".kebun_5mandor a
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.mandorid='".$mandor."'
        order by a.nourut ASC";
    $res=mysql_query($str) or die(mysql_error($conn));
	$numrows=mysql_num_rows($res);
    if($numrows<=0){
		echo 'Tidak ada daftar karyawan';
	}else{
		echo"<p /><table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['karyawan']."</td>
				<td>".$_SESSION['lang']['urutan']."</td>
				<td>".$_SESSION['lang']['status']."</td>
				<td colspan=2>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>";
		$statusaktif['0']='Tidak Aktif';
		$statusaktif['1']='Aktif';
		while($bar=mysql_fetch_assoc($res))
		{
			$no+=1;	
			echo"<tr class=rowcontent>
			<td align=right>".$no."</td>
			<td align=left>".$bar['namakaryawan']."</td>
			<td align=center>".$bar['nourut']."</td>
			<td align=center title='Set Aktif'>".$statusaktif[$bar['statusaktif']]."</td>
			<td align=center>
			<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editkaryawan('".$bar['karyawanid']."','".$bar['namakaryawan']."','".$bar['statusaktif']."','".$bar['nourut']."');\">
			</td>
			<td align=center>
			<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"hapuskaryawan('".$bar['karyawanid']."');\">
			</td>
			</tr>";	
		}     
		echo"</table>";
	}
    break;
    
    case'tambahkaryawan': // tambah karyawan mandor
	$strUr="select * from ".$dbname.".kebun_5mandor
        where karyawanid='".$mandor."'";
    $queryUr=mysql_query($strUr) or die(mysql_error($conn));
	$numrowsUr=mysql_num_rows($queryUr);
	
	$str="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and nourut='".$urut."'";
    $res=mysql_query($str) or die(mysql_error($conn));
	$numrows=mysql_num_rows($res);
	if($numrowsUr>0){
		echo"Gagal : Periksa kembali Nama Mandor. Sudah pernah terdaftar didatabase";
	}else if($numrows>0){
		echo"Gagal : Periksa kembali no urut. Sudah pernah terdaftar didatabase";
	}else{
		$sIns="insert into ".$dbname.".kebun_5mandor (`mandorid`,`karyawanid`,`statusaktif`,`nourut`,`updateby`) 
			values ('".$mandor."','".$karyawan."','1','".$urut."','".$_SESSION['standard']['userid']."')";
		if(!mysql_query($sIns))
		{
			echo"Gagal : ".mysql_error($conn);
		}
	}
    break;
	
	case'editkaryawan': 
	$strUr="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and karyawanid='".$karyawan."'";
    $queryUr=mysql_query($strUr) or die(mysql_error($conn));
	$restUr=mysql_fetch_assoc($queryUr);
	
	$str="select * from ".$dbname.".kebun_5mandor
        where mandorid='".$mandor."' and nourut='".$urut."'";
    $res=mysql_query($str) or die(mysql_error($conn));
	$numrows=mysql_num_rows($res);
	if($numrows>0 && $urut != $restUr['nourut']){
		echo"Gagal : Periksa kembali no urut. Sudah pernah terdaftar didatabase";
	}else{
		$sIns="update ".$dbname.".kebun_5mandor set statusaktif ='".$status."', nourut='".$urut."' where mandorid='".$mandor."' and karyawanid = '".$karyawan."'";
		if(!mysql_query($sIns))
		{
			echo"Gagal : ".mysql_error($conn);
		}
	}
    break;

    case'hapuskaryawan': // hapus karyawan mandor        
    $sIns="delete from ".$dbname.".kebun_5mandor where mandorid='".$mandor."' and karyawanid='".$karyawan."'";
    if(!mysql_query($sIns))
    {
        echo"Gagal : ".mysql_error($conn);
    }
    break;

    case'hapusmandor': // hapus mandor beserta karyawannya
    $sIns="delete from ".$dbname.".kebun_5mandor where mandorid='".$mandor."'";
    if(!mysql_query($sIns))
    {
        echo"Gagal : ".mysql_error($conn);
    }
    break;
    
    case'aktifkaryawan': // update status aktif karyawan 
    if($aktif=='1')$aktif='0'; else $aktif='1';
    // UPDATE `owlv2`.`kebun_5mandor` SET `statusaktif` = '0' WHERE `kebun_5mandor`.`mandorid` =0000012456 AND `kebun_5mandor`.`karyawanid` =0000013591;    
    $sIns="update ".$dbname.".kebun_5mandor set statusaktif ='".$aktif."' where mandorid='".$mandor."' and karyawanid = '".$karyawan."'";
    if(!mysql_query($sIns))
    {
        echo"Gagal : ".mysql_error($conn);
    }
    break;
    
	default:
    break;
}
?>