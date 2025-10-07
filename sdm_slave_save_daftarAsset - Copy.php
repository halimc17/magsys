<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kodeorg		=checkPostGet('kodeorg','');
$tipe			=checkPostGet('tipe','');
$kodeasset		=checkPostGet('kodeasset','');
$kodebarang		=checkPostGet('kodebarang','');
$namaaset		=checkPostGet('namaaset','');
$tahunperolehan	=checkPostGet('tahunperolehan','');
$nilaiperolehan	=checkPostGet('nilaiperolehan','');
$jumlahbulan	=checkPostGet('jumlahbulan','');
$bulanawal		=checkPostGet('bulanawal','');
$keterangan		=checkPostGet('keterangan','');
$status			=checkPostGet('status','');
$method			=checkPostGet('method','');
$leasing		=checkPostGet('leasing','');
$penambah		=checkPostGet('penambah','');
$pengurang		=checkPostGet('pengurang','');
$refbayar		=checkPostGet('refbayar','');
$nodokpengadaan	=checkPostGet('nodokpengadaan','');
$persendecline	=checkPostGet('persendecline','');
$posisiasset	=checkPostGet('posisiasset','');
$induk			=checkPostGet('induk','');
$sub			=checkPostGet('sub','');

$arrstatus=array('0'=>$_SESSION['lang']['pensiun'],'1'=>$_SESSION['lang']['aktif'],'2'=>$_SESSION['lang']['rusak'],'3'=>$_SESSION['lang']['hilang']);


$optTpasset=makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,metodepenyusutan');
$kamusleasing[0]='Not Leasing';
$kamusleasing[1]='Leasing';
if($penambah==''){
$penambah=0;
}
if($pengurang==''){
$pengurang=0;
}
if($jumlahbulan!=='' and $jumlahbulan!='' and $jumlahbulan>0)
   $bulanan=$nilaiperolehan/$jumlahbulan;
else
  $bulanan=0;  
$tex='';
if(isset($_POST['txtcari']))
{
	$tex=" and (kodeasset like '%".$_POST['txtcari']."%' or namasset like '%".$_POST['txtcari']."%')";
}
$dmn="char_length(kodeorganisasi)='4'";
$orgOption=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $dmn,'2');
//==================
//limit/page
$limit=20;
$page=0;
  if(isset($_POST['page']))
     {
	 	$page=$_POST['page'];
	    if($page<0)
		  $page=0;
	 }
  $offset=$page*$limit;
//===========================

	$str="select a.*		  
		  from ".$dbname.".sdm_daftarasset a
		  where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
		  ".$tex;
	$res=mysql_query($str);	  
	$jlhbrs=mysql_num_rows($res);
	//===================================================
// Set menjadi 0, jika persentase blank string
if($persendecline=='') $persendecline=0;

switch($method)
{
case 'update':	
    if(($jumlahbulan=='')||($jumlahbulan=='0')){
            exit("error: ".$_SESSION['lang']['jumlahbulanpenyusutan']." can't empty or zero");
    }
    if($optTpasset[$tipe]=='double'){
        if(($persendecline=='')||($persendecline=='0')){
            exit("error: percentage can't empty or zero");
        }
    }
	$str="update ".$dbname.".sdm_daftarasset set 
	       tipeasset='".$tipe."',
		   kodebarang='".$kodebarang."',
		   namasset='".$namaaset."',
		   tahunperolehan=".$tahunperolehan.",
		   status=".$status.",
		   leasing=".$leasing.",
		   hargaperolehan=".$nilaiperolehan.",
		   jlhblnpenyusutan=".$jumlahbulan.",
		   awalpenyusutan='".$bulanawal."',
		   keterangan='".$keterangan."',
		   user=".$_SESSION['standard']['userid'].",
		   bulanan=".$bulanan.",
		   penambah=".$penambah.",
		   pengurang=".$pengurang.",
			refbayar='".$refbayar."',
			dokpengadaan='".$nodokpengadaan."',
			persendecline=".$persendecline.",
                        posisiasset='".$posisiasset."',
                            induk='".$induk."',
                                subtipe='".$sub."'
	       where kodeasset='".$kodeasset."'
		   and kodeorg='".$kodeorg."'";
		   
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));
		 exit(0);
	}
	break;
case 'insert':
    if(strlen($tipe)==4)
        $kodeasset=str_pad($kodeasset, 6, "0", STR_PAD_LEFT);
    else if(strlen($tipe)==3)
        $kodeasset=str_pad($kodeasset, 7, "0", STR_PAD_LEFT);
    else if(strlen($tipe)==2)
         $kodeasset=str_pad($kodeasset, 8, "0", STR_PAD_LEFT);
     else 
         $kodeasset=str_pad($kodeasset, 8, "0", STR_PAD_LEFT);    
   if(($jumlahbulan=='')||($jumlahbulan=='0')){
            exit("error: ".$_SESSION['lang']['jumlahbulanpenyusutan']." can't empty or zero");
        }
	if($optTpasset[$tipe]=='double'){   
        if(($persendecline=='')||($persendecline=='0')){
            exit("error: percentage can't empty or zero");
        }
    }
    //$kodeasset=$_SESSION['org']['kodeorganisasi']."-".$tipe.$kodeasset;
	$str="insert into ".$dbname.".sdm_daftarasset (
	       tipeasset,kodeorg,kodebarang,
		   namasset,tahunperolehan,status,
		   hargaperolehan,jlhblnpenyusutan,
		   awalpenyusutan,keterangan,kodeasset,user,bulanan,leasing,penambah,pengurang,
		   refbayar,dokpengadaan,persendecline,posisiasset,induk,subtipe
		   )
	      values(
		    '".$tipe."',
			'".$kodeorg."',
			'".$kodebarang."',
			'".$namaaset."',
			".$tahunperolehan.",
			".$status.",
			".$nilaiperolehan.",
			".$jumlahbulan.",
			'".$bulanawal."',
			'".$keterangan."',
			'".$kodeasset."',
			".$_SESSION['standard']['userid'].",
			".$bulanan.",
			".$leasing.",
			".$penambah.",
			".$pengurang.",
			'".$refbayar."',
			'".$nodokpengadaan."',
			".$persendecline.",'".$posisiasset."',
                            '".$induk."','".$sub."'
			)";
        //exit("error:".$str);
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));
		 exit(0);
	}	
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_daftarasset 
	where kodeasset='".$kodeasset."'";
	if(mysql_query($str))
	{}
	else
	{echo " Gagal,".addslashes(mysql_error($conn));
	 exit(0);
	}
	break;
default:
   break;					
}
         if($_SESSION['language']=='EN'){
             $ads="b.namatipe1 as namatipe";
         }
         else{
            $ads="b.namatipe as namatipe"; 
         }
         
	$str="select a.*,".$ads."
	      
		  from ".$dbname.".sdm_daftarasset a
	      left join  ".$dbname.".sdm_5tipeasset b
	      on a.tipeasset=.b.kodetipe
		  where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$tex." 
		  order by tahunperolehan desc,awalpenyusutan desc,namatipe asc
		   limit ".$offset.",".$limit;

	/*$str="select a.*,".$ads.", 
	      CASE a.status
		  when 0 then '".$_SESSION['lang']['pensiun']."'
		  when 1 then '".$_SESSION['lang']['aktif']."' 
		  when 2 then '".$_SESSION['lang']['rusak']."' 
		  when 3 then '".$_SESSION['lang']['hilang']."' 
		  else 'Unknown'
          END as stat		  
		  from ".$dbname.".sdm_daftarasset a
	      left join  ".$dbname.".sdm_5tipeasset b
	      on a.tipeasset=.b.kodetipe
		  where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$tex." 
		  order by tahunperolehan desc,awalpenyusutan desc,namatipe asc
		   limit ".$offset.",".$limit;*/	  
		  
		  
	$res=mysql_query($str);

	$no=$offset;
	while($bar=mysql_fetch_object($res))
	{
	  $no+=1;
	  echo"<tr class=rowcontent>
	          <td>".$no."</td>
		      <td>".$orgOption[$bar->kodeorg]."</td>
                          <td>".$orgOption[$bar->posisiasset]."</td>
			  <td>".$bar->namatipe."</td>
			  <td>".$bar->kodeasset."</td>
			  <td>".$bar->namasset."</td>
			  <td align=right>".$bar->tahunperolehan."</td>
			  <td>".$arrstatus[$bar->status]."</td>
			  <td align=right>".number_format($bar->hargaperolehan,2,'.',',')."</td>
			  <td align=right>".$bar->jlhblnpenyusutan."</td>
			  <td align=right>".$bar->persendecline."</td>
			  <td align=center>".substr($bar->awalpenyusutan,5,2)."-".substr($bar->awalpenyusutan,0,4)."</td>
			  <td>".$bar->keterangan."</td>
			  <td>".$kamusleasing[$bar->leasing]."</td>
			  <td>".$bar->kodeproject."</td>
			  <td>
			   <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAsset('".$bar->kodeorg."','".$bar->tipeasset."','".$bar->kodeasset."','".$bar->namasset."','".$bar->kodebarang."','".$bar->tahunperolehan."','".$bar->status."','".$bar->hargaperolehan."','".$bar->jlhblnpenyusutan."','".$bar->awalpenyusutan."','".$bar->keterangan."','".$bar->leasing."','".$bar->penambah."','".$bar->pengurang."','".$bar->refbayar."','".$bar->dokpengadaan."','".$bar->persendecline."','".$bar->posisiasset."','".$bar->induk."','".$bar->subtipe."');\">
		      &nbsp <!--<img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delAsset('".$bar->kodeorg."','".$bar->kodeasset."');\">-->
			  </td>
		   </tr>
		   </tr>";		
	}
  echo"<tr><td colspan=12 align=center>
       ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
	   <br>
       <button class=mybutton onclick=cariAsset(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	   <button class=mybutton onclick=cariAsset(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	   </td>
	   </tr>";	
?>
