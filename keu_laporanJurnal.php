<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$regional=$_POST['regional'];
$gudang=$_POST['gudang'];
$periode=$_POST['periode'];
$periode1=$_POST['periode1'];
$revisi=$_POST['revisi'];
$kdKel=$_POST['kdKel'];
$nojurnal=$_POST['nojurnal'];

$ref=$_POST['ref'];
$ket=$_POST['ket'];

//if($periode=='' and $gudang=='' and $pt=='')
//{               
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
//                and a.nojurnal NOT LIKE '%CLSM%'
//		order by a.nojurnal 
//		";
//}
//else if($periode=='' and $gudang=='' and $pt!='')
//{               
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
//		and a.kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
//                and length(kodeorganisasi)=4)
//                and a.nojurnal NOT LIKE '%CLSM%'
//                order by a.nojurnal 
//		";
//}
//else if($periode=='' and $gudang!='')
//{
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
//		and a.kodeorg='".$gudang."'
//                order by a.nojurnal 
//		";
//}
//else if($periode!='' and $gudang=='' and $pt=='')
//{
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal like '".$periode."%'
//                and a.nojurnal NOT LIKE '%CLSM%'
//		order by a.nojurnal 
//		";
//}
//else if($periode!='' and $gudang=='' and $pt!='')
//{
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal like '".$periode."%'
//                and a.kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
//                and a.nojurnal NOT LIKE '%CLSM%'
//                and length(kodeorganisasi)=4)                    
//		order by a.nojurnal 
//		";
//}
//else if($periode!='' and $gudang!='')
//{
//               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
//		left join ".$dbname.".keu_5akun b
//		on a.noakun=b.noakun
//		where a.tanggal like '".$periode."%'
//		and a.kodeorg='".$gudang."'
//                and a.nojurnal NOT LIKE '%CLSM%'
//                order by a.nojurnal 
//		";
//}

if(intval(str_replace('-','',$periode1))-intval(str_replace('-','',$periode))>4){
    exit('error: periode terlalu panjang');
}



#jika regional all, unit all
#jika regional isi, unit all
#jika regional isi, unit isi

$kdKelSch="";
if($kdKel!='')
{
   $kdKelSch=" and a.nojurnal like '%/".$kdKel."/%'  "; 
}

if($regional=='' && $gudang=='')
{
    $kdOrgSch=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
}
else if($regional!='' && $gudang=='')
{
    //$kdOrgSch=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."')";

    
    $kdOrgSch=" and a.kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
    
}
else
{
    $kdOrgSch=" and a.kodeorg='".$gudang."'";
}

if($ref!='')
{
    $refKet.=" and a.noreferensi like '%".$ref."%'";
}

if($ket!='')
{
    $refKet.=" and a.keterangan like '%".$ket."%' ";
}

if($nojurnal!='')
{
    $nojurnalsch.=" and a.nojurnal like '%".$nojurnal."%' ";
}

$str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
left join ".$dbname.".keu_5akun b
on a.noakun=b.noakun
where a.tanggal between '".$periode."-01' and LAST_DAY('".$periode1."-15')
".$kdOrgSch."
and a.nojurnal NOT LIKE '%CLSM%' ".$kdKelSch." ".$nojurnalsch."
and a.revisi<='".$revisi."' ".$refKet."  
order by a.nojurnal 
";   
/*if($gudang!=''){
    $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".keu_5akun b
        on a.noakun=b.noakun
        where a.tanggal between '".$periode."-01' and LAST_DAY('".$periode1."-15')
        and a.kodeorg='".$gudang."'
        and a.nojurnal NOT LIKE '%CLSM%'
        and a.revisi<='".$revisi."'
        order by a.nojurnal 
        ";
}else{
    $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
        left join ".$dbname.".keu_5akun b
        on a.noakun=b.noakun
        where a.tanggal between '".$periode."-01' and LAST_DAY('".$periode1."-15')
        and a.kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
        and a.nojurnal NOT LIKE '%CLSM%'
        and a.revisi<='".$revisi."'
        and length(kodeorganisasi)=4)                    
        order by a.nojurnal 
        ";   
}*/


// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok
    ";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   

//exit("Error:".$str);
//=================================================
$res=mysql_query($str);
$no=0;
if(!$res)
{
    echo"<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
	$tdebet = $tkredit = 0;
    while($bar=mysql_fetch_object($res))
    {
        $no+=1;
        $debet=0;
        $kredit=0;
        if($bar->jumlah>0)
            $debet=$bar->jumlah;
        else
            $kredit=$bar->jumlah*-1;

        echo"<tr class=rowcontent>
            <td align=center  style='width:50px;'>".$no."</td>
            <td style='width:278px;'>".$bar->nojurnal."</td>
            <td style='width:80px;'>".tanggalnormal($bar->tanggal)."</td>
            <td align=center style='width:84px;'>".$bar->kodeorg."</td>
            <td style='width:60px;'>".$bar->noakun."</td>
            <td style='width:200px;'>".$bar->namaakun."</td>
            <td style='width:240px;'>".$bar->keterangan."</td>
            <td align=right style='width:60px;'>".$bar->noaruskas."</td>
            <td align=right style='width:60px;'>".number_format($debet,2)."</td>
            <td align=right style='width:60px;'>".number_format($kredit,2)."</td>
            <td align=center style='width:200px;'>".$bar->noreferensi."</td>    
            <td align=center style='width:80px;'>".$bar->kodeblok."</td>
            <td align=center style='width:60px;'>".
			(isset($tahuntanam[$bar->kodeblok])? $tahuntanam[$bar->kodeblok]: '')."</td>
            <td align=center style='width:30px;'>".$bar->revisi."</td>
            </tr>"; 	
        $tdebet+=$debet;
        $tkredit+=$kredit;
    }	
    echo"<tr class=rowtitle>
        <td align=center colspan=8>Total</td>
        <td align=right width=100>".number_format($tdebet,2)."</td>
        <td align=right width=100>".number_format($tkredit,2)."</td>
        <td align=center colspan=4></td>
        </tr>"; 		
} 	
?>