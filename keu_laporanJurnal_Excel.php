<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$periode=$_GET['periode'];
$periode1=$_GET['periode1'];
$revisi=$_GET['revisi'];
$regional=$_GET['regional'];
$kdKel=$_GET['kdKel'];
$ref=$_GET['ref'];
$ket=$_GET['ket'];
$nojurnal=$_GET['nojurnal'];
            
/*if($periode=='' and $gudang=='' and $pt=='')
{               
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
                and a.nojurnal NOT LIKE '%CLSM%'
		order by a.nojurnal 
		";
}
else if($periode=='' and $gudang=='' and $pt!='')
{               
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
		and a.kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
                and length(kodeorganisasi)=4)
                and a.nojurnal NOT LIKE '%CLSM%'
                order by a.nojurnal 
		";
}
else if($periode=='' and $gudang!='')
{
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal>=".$_SESSION['org']['period']['start']." and  a.tanggal<=".$_SESSION['org']['period']['end']."
		and a.kodeorg='".$gudang."'
                and a.nojurnal NOT LIKE '%CLSM%'
                order by a.nojurnal 
		";
}
else if($periode!='' and $gudang=='' and $pt=='')
{
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal like '".$periode."%'
                and a.nojurnal NOT LIKE '%CLSM%'
		order by a.nojurnal 
		";
}
else if($periode!='' and $gudang=='' and $pt!='')
{
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal like '".$periode."%'
                and a.kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' 
                and length(kodeorganisasi)=4)        
                and a.nojurnal NOT LIKE '%CLSM%'
		order by a.nojurnal 
		";
}
else if($periode!='' and $gudang!='')
{
               $str="select a.*,b.namaakun from ".$dbname.".keu_jurnaldt_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		where a.tanggal like '".$periode."%'
		and a.kodeorg='".$gudang."'
                and a.nojurnal NOT LIKE '%CLSM%'
                order by a.nojurnal 
		";
}*/

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


if($kdKel!='')
{
   $kdKelSch=" and a.nojurnal like '%/".$kdKel."/%'  "; 
}

if($regional=='' && $gudang=='')
{
    $kdOrgSch=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
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
    
// kamus tahun tanam
$aresta="SELECT kodeorg, tahuntanam FROM ".$dbname.".setup_blok
    ";
$query=mysql_query($aresta) or die(mysql_error($conn));
while($res=mysql_fetch_assoc($query))
{
    $tahuntanam[$res['kodeorg']]=$res['tahuntanam'];
}   

//=================================================
$stream="<table border=1>
	     <thead>
		    <tr>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['nourut']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['nojurnal']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['tanggal']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['organisasi']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['noakun']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['namaakun']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['keterangan']."</td>
			  <td bgcolor='#dedede'>Arus Kas</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['debet']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['kredit']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['sumber']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['kodevhc']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['kodeblok']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['tahuntanam']."</td>
			  <td bgcolor='#dedede'>".$_SESSION['lang']['afdeling']."</td>    
			  <td bgcolor='#dedede'>".$_SESSION['lang']['revisi']."</td>    
			</tr>  
		 </thead>
		 <tbody id=container>";
$res=mysql_query($str) or die(mysql_error($conn)." ".$str);
$no=0;
if(!$res)
{
       $stream.="<tr class=rowcontent><td colspan=12>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}
else
{
while($bar=mysql_fetch_object($res))
{
        $no+=1;
        $debet=0;
        $kredit=0;
        if($bar->jumlah>0)
            $debet=$bar->jumlah;
        else
            $kredit=$bar->jumlah*-1;
        
       $stream.="<tr class=rowcontent>
              <td align=center width=20>".$no."</td>
              <td>".$bar->nojurnal."</td>
              <td>".tanggalnormal($bar->tanggal)."</td>
              <td align=center>".$bar->kodeorg."</td>
              <td>".$bar->noakun."</td>
              <td>".$bar->namaakun."</td>
              <td>".$bar->keterangan."</td>
              <td align=right width=100>".$bar->noaruskas."</td>
              <td align=right width=100>".number_format($debet,2)."</td>
              <td align=right width=100>".number_format($kredit,2)."</td>
              <td>".$bar->noreferensi." </td>
              <td>".$bar->kodevhc." </td>
              <td>".$bar->kodeblok." </td>
              <td>".$tahuntanam[$bar->kodeblok]." </td>
              <td>".substr($bar->kodeblok,0,6)."</td>    
              <td>".$bar->revisi." </td>
             </tr>"; 		
        $tdebet+=$debet;
        $tkredit+=$kredit;
}	
    $stream.="<tr bgcolor='#dedede'>
        <td align=center colspan=8>Total</td>
        <td align=right width=100>".number_format($tdebet,2)."</td>
        <td align=right width=100>".number_format($tkredit,2)."</td>
        <td align=center colspan=6></td>
        </tr>"; 		
} 
$stream.="</tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>";
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$qwe=date("YmdHms");
$nop_="LP_JRNL_".$gudang.$periode."rev".$revisi."___".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}
?>