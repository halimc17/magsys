<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$gudang=$_POST['gudang'];
$periode=$_POST['periode'];
$periode1=$_POST['periode1'];
$revisi=$_POST['revisi'];
$regional=$_POST['regional'];
        
//cek periode dan periode1
if($periode1<$periode)
{  #ditukar
    $z=$periode;
    $periode=$periode1;
    $periode1=$z;
}
	
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namapt=strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'";
$namagudang='';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil akun laba rugi tahun berjalan:
$CLM='';
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'";
$res=mysql_query($str);
while($bar=  mysql_fetch_object($res))
{
    $CLM=$bar->noakundebet;
}

//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);
if($_SESSION['language']=='ID'){
$str="select distinct noakun,namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."' order by noakun";
}
else{
    $str="select distinct noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."' order by noakun";
}
$res=mysql_query($str);
$TAB=Array();

while($bar=mysql_fetch_object($res))
{
    $TAB[$bar->noakun]['noakun']=$bar->noakun;
    $TAB[$bar->noakun]['namaakun']=$bar->namaakun;
    $TAB[$bar->noakun]['sawal']=0;
    $TAB[$bar->noakun]['salak']=0;
}

//ambil saldo awal
/*if($gudang=='' and $pt!='')
{
    $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
else if($gudang!='')
{
    $where =" and kodeorg ='".$gudang."'";
}
else
{
    $where='';  
}*/


#jika regional all, unit all
#jika regional isi, unit all
#jika regional isi, unit isi
if($regional=='' && $gudang=='')
{
   $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    $where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}
else
{
    $where =" and kodeorg ='".$gudang."'";
}



/*$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun from ".$dbname.".keu_saldobulanan 
      where periode ='".str_replace("-","",$periode)."' ".$where." 
      and noakun!='3110400' group by noakun order by noakun";*/
$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun from ".$dbname.".keu_saldobulanan 
      where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where." group by noakun order by noakun";
//print_r($str);
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $TAB[$bar->noakun]['sawal']=$bar->sawal;
    $TAB[$bar->noakun]['salak']=$bar->sawal;
}

$str="select sum(debet) as debet,sum(kredit) as kredit, noakun from ".$dbname.".keu_jurnaldt_vw
    where periode>='".$periode."' and periode<='".$periode1."' ".$where." 
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan




//ambil mutasi-----------------------
/*if($gudang=='' and $pt=='') // pt ga mungkin kosong
{
//$str="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnalsum_vw
//    where periode>='".$periode."' and periode<='".$periode1."' 
//    and noakun!='".$CLM."' group by noakun"; #tidak sama dengan laba/rugi berjalan
$str="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnaldt_vw
    where periode>='".$periode."' and periode<='".$periode1."' 
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan
}
else if($gudang=='' and $pt!='')
{
//$str="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnalsum_vw
//    where periode>='".$periode."' and periode<='".$periode1."' and kodeorg in(select kodeorganisasi 
//    from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)
//    and noakun!='".$CLM."' group by noakun"; #tidak sama dengan laba/rugi berjalan
$str="select sum(debet) as debet,sum(kredit) as kredit, noakun from ".$dbname.".keu_jurnaldt_vw
    where periode>='".$periode."' and periode<='".$periode1."' and kodeorg in(select kodeorganisasi 
    from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan
}
else
{
$str="select sum(debet) as debet,sum(kredit) as kredit, noakun from ".$dbname.".keu_jurnaldt_vw
    where periode>='".$periode."' and periode<='".$periode1."' and kodeorg ='".$gudang."'
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan 
//$str="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnalsum_vw
//    where periode>='".$periode."' and periode<='".$periode1."' and kodeorg ='".$gudang."'
//    and noakun!='".$CLM."' group by noakun"; #tidak sama dengan laba/rugi berjalan 
}   
//echo $str;*/

//=================================================
$res=mysql_query($str);
while($bar= mysql_fetch_object($res))
{
    $TAB[$bar->noakun]['debet']=$bar->debet;
    $TAB[$bar->noakun]['kredit']=$bar->kredit;
    $TAB[$bar->noakun]['salak']=$TAB[$bar->noakun]['sawal']+$bar->debet-$bar->kredit;
} 
$no=0;
$sal_awal=0;
$sal_debet=0;
$sal_kredit=0;
$sal_salak=0;     
foreach($TAB as $baris => $data)
{
   // if($data['sawal']==0 && $data['debet']==0 && $data['kredit']==0 && $data['salak']==0)
   // {
        
   // }
   // else
    //{    
    $no+=1;
    echo"<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudang."','".$revisi."',event);\">
        <td style='width:50px;'>".$no."</td>
        <td style='width:80px;'>".$data['noakun']."</td>    
        <td style='width:430px;'>".$data['namaakun']."</td>
        <td align=right style='width:130px;'>".number_format($data['sawal'],2)."</td>
        <td align=right style='width:130px;'>".number_format(isset($data['debet'])? $data['debet']: 0,2)."</td>
        <td align=right style='width:130px;'>".number_format(isset($data['kredit'])? $data['kredit']: 0,2)."</td>   
        <td align=right style='width:130px;'>".number_format($data['salak'],2)."</td>    
    </tr>";
   // } 
    $sal_awal+=$data['sawal'];
    $sal_debet+=isset($data['debet'])? $data['debet']: 0;
    $sal_kredit+=isset($data['kredit'])? $data['kredit']: 0;
    $sal_salak+=$data['salak']; 
}   
echo"<tr class=rowcontent>
        <td colspan=3 align=center>TOTAL</td>
        <td align=right>".number_format($sal_awal,2)."</td>
        <td align=right>".number_format($sal_debet,2)."</td>
        <td align=right>".number_format($sal_kredit,2)."</td>   
        <td align=right>".number_format($sal_salak,2)."</td> 
    </tr>"    
?>