<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');

$getrows=20;
//default query
if($_POST['page'])
   $page=$_POST['page'];
else
   $page=1; 
  
$maxdisplay=($page*$getrows-20);

$ptsearch = checkPostGet('ptsearch','');
$regional = checkPostGet('regional','');
$txtsearch = checkPostGet('txtsearch','');
$orgsearch = checkPostGet('orgsearch','');
$subunit = checkPostGet('subunit','');
$kodegolongan = checkPostGet('kodegolongan','');
$tipesearch = checkPostGet('tipesearch','');
$statussearch = checkPostGet('statussearch','');
$schjk = checkPostGet('schjk','');
$levelpendidikan = checkPostGet('levelpendidikan','');
$tanggalmasuk1 = tanggalsystem(checkPostGet('tanggalmasuk1',''));
$tanggalmasuk2 = tanggalsystem(checkPostGet('tanggalmasuk2',''));
$tanggalkeluar1 = tanggalsystem(checkPostGet('tanggalkeluar1',''));
$tanggalkeluar2 = tanggalsystem(checkPostGet('tanggalkeluar2',''));
$umur1 = checkPostGet('umur1','');
$umur2 = checkPostGet('umur2','');

if(isset($_POST['method'])){
	$optpt="";
	$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
	
	if($_POST['ptsearch'] != ''){
		$str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk='".$_POST['ptsearch']."' order by namaorganisasi asc";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
		}
	}else{
		$str="select distinct a.lokasitugas as kodeorganisasi,namaorganisasi from ".$dbname.".datakaryawan a 
              left join ".$dbname.".organisasi b on a.lokasitugas=b.kodeorganisasi order by namaorganisasi asc";
		$res=mysql_query($str);
		while($bar=mysql_fetch_object($res))
		{
			$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
		}
	}
	echo $optpt;
	exit;
}

$where='';
if($txtsearch!='')
	$where= " and a.namakaryawan like '%".$txtsearch."%'";
if($orgsearch!=''){
	$where .=" and (a.lokasitugas='".$orgsearch."' or left(a.subbagian,4)='".$orgsearch."')";    
}else{
	if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
		if($ptsearch!=''){
			//$where.=" and a.kodeorganisasi='".$ptsearch."'";
			$where.=" and (a.kodeorganisasi='".$ptsearch."' or left(a.subbagian,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$ptsearch."'))";
		}
		if($regional!=''){
			if($regional=='JAKARTA'){
				$where.=" and left(a.subbagian,4) in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
			}else{
				$where.=" and a.lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";
			}
		}
    }else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
        $where .=" and a.lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where "
                . " induk='".$_SESSION['empl']['kodeorganisasi']."')";    
    }else{
        $where .=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."'";    
    }
}
if($subunit!=''){
	$where .=" and a.subbagian='".$subunit."'"; 
}
if($kodegolongan!=''){
	$where .=" and a.kodegolongan like '".$kodegolongan."%'"; 
}
if($tipesearch!=''){
	if($tipesearch==100){
		$where.=" and a.tipekaryawan!=4 ";
	}else{
		$where .=" and a.tipekaryawan='".$tipesearch."'"; 
	}
}
if($schjk!=''){
	$where.=" and a.jeniskelamin='".$schjk."'";
}
if($levelpendidikan!=''){
	$where .=" and a.levelpendidikan='".$levelpendidikan."'"; 
}
$hariini = date("Y-m-d");	 
if($tanggalmasuk1!='' and $tanggalmasuk2==''){
	//$tanggalmasuk2=$tanggalmasuk1;
	$hariini = $tanggalmasuk1;
}
if($tanggalmasuk1=='' and $tanggalmasuk2!=''){
	//$tanggalmasuk1=$tanggalmasuk2;
	$hariini = $tanggalmasuk2;
}
if($tanggalmasuk1!='' and $tanggalmasuk2!=''){
	$where.=" and (a.tanggalmasuk>='".$tanggalmasuk1."' and a.tanggalmasuk<='".$tanggalmasuk2."')";
}else{
	//if($statussearch=='0000-00-00'){
	//	$where .=" and a.tanggalmasuk<='".$hariini."'";
	//}
}
if($tanggalkeluar1!='' and $tanggalkeluar2==''){
	//$tanggalkeluar2=$tanggalkeluar1;
	$hariini = $tanggalkeluar1;
}
if($tanggalkeluar1=='' and $tanggalkeluar2!=''){
	//$tanggalkeluar1=$tanggalkeluar2;
	$hariini = $tanggalkeluar2;
}
if($tanggalkeluar1!='' and $tanggalkeluar2!=''){
	$where.=" and (a.tanggalkeluar>='".$tanggalkeluar1."' and a.tanggalkeluar<='".$tanggalkeluar2."')";
}
if($umur1!='' and $umur2==''){
	$umur2=$umur1;
}
if($umur1=='' and $umur2!=''){
	$umur1=$umur2;
}
if($umur1!='' and $umur2!=''){
	$where.=" and ((year(CURDATE())-year(a.tanggallahir))>='".$umur1."' and (year(CURDATE())-year(a.tanggallahir))<='".$umur2."')";
}

//$hariini = date("Y-m-d");
if($statussearch=='*'){
	//$where .=" and (a.tanggalkeluar!='0000-00-00')";
	$where .=" and (a.tanggalkeluar!='0000-00-00' and a.tanggalkeluar<'".$hariini."')"; // tidak aktif
}else if($statussearch=='0000-00-00'){
	//$where .=" and (a.tanggalkeluar='0000-00-00')";
	$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
}else{
	if($tanggalmasuk1!='' and $tanggalmasuk2==''){
		$where .=" and a.tanggalmasuk<='".$hariini."'";
	}
	if($tanggalmasuk1=='' and $tanggalmasuk2!=''){
		$where .=" and a.tanggalmasuk<='".$hariini."'";
	}
	if($tanggalkeluar1!='' and $tanggalkeluar2==''){
		$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
	}
	if($tanggalkeluar1=='' and $tanggalkeluar2!=''){
		$where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
	}
}

//make sure user can only access allowed data   
$listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
$list=str_replace("|","','",$listOrg);
$list="'".$list."'";

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan ".$where."
		order by a.namakaryawan
		limit ".$maxdisplay.",".$getrows;
	$strx="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan ".$where."
		order by a.namakaryawan";
}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') ".$where."
		order by a.namakaryawan
		limit ".$maxdisplay.",".$getrows;
	$strx="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') ".$where."
		order by a.namakaryawan";
}else{
	$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') and lokasitugas in(".$list.") ".$where." 
		order by a.namakaryawan
		limit ".$maxdisplay.",".$getrows;
	$strx="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
		".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e 
		where a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan 
		and a.tipekaryawan not in ('0','7','8') and lokasitugas in(".$list.") ".$where." 
		order by a.namakaryawan";
}

//exit('Warning: '.$strx);

//==================jlh karyawan
$jlhkar=0;
$resx=mysql_query($strx);
// echo mysql_error($conn);
// while($barx=mysql_fetch_object($resx))
// {
	$jlhkar=mysql_num_rows($resx);
// }


//=====================

$res=mysql_query($str);
$numrows=mysql_num_rows($res);
/*if($numrows<1)
{
	echo "<tr><td>NOT FOUND</td></tr>";
}
else
{*/


	

	
	$no=$maxdisplay;
	if($jlhkar==0)
	{
		echo"<tr><td colspan=2>DATA NOT FOUND</td></tr>";	
	}
	if($jlhkar!==0)
	{
		echo"<tr><td colspan=2>Total: ".$jlhkar." Person</td></tr>";	
	}
	while($bar=mysql_fetch_object($res))
	{
		//get pendidikan terakhir
		$str1="select a.kelompok from ".$dbname.".sdm_5pendidikan a
		       where a.levelpendidikan=".$bar->levelpendidikan." "; 
		$res1=mysql_query($str1);	
		$pendidikan="";
		while($barpendidikan=mysql_fetch_object($res1))
		{
			$pendidikan=$barpendidikan->kelompok;
		}
		   
		$no+=1;
		echo "<tr class=rowcontent>
		     <td>".$no."</td>
			 <td width=85>".$bar->nik."</td>
			 <td>".$bar->namakaryawan."</td>
			 <td>".$bar->namajabatan."</td>
			 <td>".$bar->namagolongan."</td>
			 <td>".$bar->lokasitugas."</td>
			 <td>".$bar->kodeorganisasi."</td>
			 <td>".$bar->subbagian."</td>
			 <td>".$pendidikan."</td>
			 <td>".$bar->statuspajak."</td>
			 <td>".$bar->statusperkawinan."</td>
			 <td align=right >".$bar->jumlahanak."</td>
			 <td>".tanggalnormal($bar->tanggalmasuk)."</td>
			 <td>".tanggalnormal($bar->tanggalkeluar)."</td>
			 <td>".$bar->tipe."</td>
			 <td>
				    <img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewKaryawan('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">
					<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewKaryawanPDF('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">		 
			 </td>
			  </tr>";			 		  
	}
//}
?>
