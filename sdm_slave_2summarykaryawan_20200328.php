<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$_POST['proses']==''?$proses=$_GET['proses']:$proses=$_POST['proses']; 
$_POST['tanggal']==''?$tanggal=$_GET['tanggal']:$tanggal=$_POST['tanggal']; 
$_POST['region']==''?$region=$_GET['region']:$region=$_POST['region']; 
$_POST['lokTgs']==''?$lokTgs=$_GET['lokTgs']:$lokTgs=$_POST['lokTgs']; 
$_POST['tipekary']==''?$tipekary=$_GET['tipekary']:$tipekary=$_POST['tipekary']; 
$tglPrd=explode("-",$tanggal);
$periodeGj=$tglPrd[2]."-".$tglPrd[1];
$optTip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
if($tanggal==''){    
    exit("Error: All field required");
}
    
    $str="select * from ".$dbname.".sdm_5tipekaryawan
        where 1";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        $tipekar[$bar->id]=$bar->id;
        $artitkr[$bar->id]=$bar->tipe;
    }

    if($region!=''){
        $str="select * from ".$dbname.".bgt_regional_assignment
            where regional = '".$region."'";        
    }else{
        $str="select * from ".$dbname.".bgt_regional_assignment
            where 1";
        
    }
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        if($region!=''){
            $regional[$bar->kodeunit]=$bar->kodeunit;            
        }else{
            $unitreg[$bar->kodeunit]=$bar->regional;
            $regional[$bar->regional]=$bar->regional;            
        }        
    }
    
if($proses=='preview'||$proses=='excel'){
    
    $str="select * from ".$dbname.".datakaryawan
        where tipekaryawan!=4 and tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') ";
    //echo $str;
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        if($region!=''){
            $qwe=$bar->lokasitugas;
        }else{
            $qwe=$unitreg[$bar->lokasitugas];
        }
        $jumlahkar[$qwe][$bar->tipekaryawan]+=1;
    }
$sdmGaji="select a.*  from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
          tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
          and periodegaji='".$periodeGj."'   and tipekaryawan=4
          and idkomponen=1";
//exit("error:".$sdmGaji) ;
$qsdmGaji=mysql_query($sdmGaji) or die(mysql_error($conn));
while($rsdmGaji=  mysql_fetch_assoc($qsdmGaji)){
       if($param['region']!=''){
            $qwe=$rsdmGaji['lokasitugas'];
        }else{
            $qwe=$unitreg[$rsdmGaji['lokasitugas']];
        }
        $jumlahkar[$qwe][$rsdmGaji['tipekaryawan']]+=1;
}
    if($proses!='excel'){
        $brd=0;
        $bgcolor="";
    }else{
        $tab.= $_SESSION['lang']['summary']." ".$_SESSION['lang']['karyawan']."<br>Tanggal: ".$tanggal." ";
        $brd=1;
        $bgcolor="bgcolor=#DEDEDE";
    }
    if($region==''){
        $region=$_SESSION['lang']['regional'];
    }else{
        if($proses!='excel')
        $tab.="<img onclick=level1excel(event,'sdm_slave_2summarykaryawan.php','".$tanggal."','".$region."') src=images/excel.jpg class=resicon title='MS.Excel'>";
    }
        
    $tab.="
    <table width=100% cellspacing=1 border=".$brd.">
    <thead>
    <tr>
        <td ".$bgcolor.">".$region."</td>";
        if(!empty($regional))foreach($regional as $reg)
            if($region!='')
            $tab.="<td ".$bgcolor." align=center title='Click to details...' onclick=getlevel1('".$tanggal."','".$reg."')>".$reg."</td>";
        $tab.="
        <td ".$bgcolor." align=center>".$_SESSION['lang']['total']."</td>
    </tr>        
    </thead>
    <tbody>";
    if(!empty($tipekar))foreach($tipekar as $tkr){
        $tab.="<tr class=rowcontent>
        <td>".$artitkr[$tkr]."</td>";
        $total[$tkr]=0;
        if(!empty($regional))foreach($regional as $reg){
            $tab.="<td align=right>".number_format($jumlahkar[$reg][$tkr])."</td>";
            $total[$tkr]+=$jumlahkar[$reg][$tkr];
            $totalgrand[$reg]+=$jumlahkar[$reg][$tkr];            
        }
        $tab.="
        <td align=right>".number_format($total[$tkr])."</td>
        </tr>";            
    }
    $tab.="<tr class=rowcontent>
    <td>".$_SESSION['lang']['total']."</td>";
    $totalnya=0;
    if(!empty($regional))foreach($regional as $reg){
        $tab.="<td align=right>".number_format($totalgrand[$reg])."</td>";
        $totalnya+=$totalgrand[$reg];            
    }
    $tab.="
    <td align=right>".number_format($totalnya)."</td>
    </tr>";            
    
    $tab.="</tbody></table>";
    
}elseif($proses=='level1'){
     $str="select * from ".$dbname.".datakaryawan
        where tipekaryawan!=4  and tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') ";
    //echo $str;
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res)){
        if($region!=''){
            $qwe=$bar->lokasitugas;
        }else{
            $qwe=$unitreg[$bar->lokasitugas];
        }
        $jumlahkar[$qwe][$bar->tipekaryawan]+=1;
    }
    $sdmGaji="select a.*  from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
          tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
          and periodegaji='".$periodeGj."'   and tipekaryawan=4
          and idkomponen=1";
//exit("error:".$sdmGaji) ;
$qsdmGaji=mysql_query($sdmGaji) or die(mysql_error($conn));
while($rsdmGaji=  mysql_fetch_assoc($qsdmGaji)){
       if($region!=''){
            $qwe=$rsdmGaji['lokasitugas'];
        }else{
            $qwe=$unitreg[$rsdmGaji['lokasitugas']];
        }
        $jumlahkar[$qwe][$rsdmGaji['tipekaryawan']]+=1;
}
       $brd=0;
       $bgcolor="";
    if($region==''){
        $region=$_SESSION['lang']['regional'];
    }else{
        if($proses!='excel')
        $tab.="<img onclick=level1excel(event,'sdm_slave_2summarykaryawan.php','".$tanggal."','".$region."') src=images/excel.jpg class=resicon title='MS.Excel'>";
    }
$tab.="
    <table width=100% cellspacing=1 border=".$brd.">
    <thead>
    <tr>
        <td ".$bgcolor.">".$region."</td>";
        if(!empty($regional))foreach($regional as $reg)
            if($region!='')
            $tab.="<td ".$bgcolor." align=center>".$reg."</td>";
        $tab.="
        <td ".$bgcolor." align=center>".$_SESSION['lang']['total']."</td>
    </tr>        
    </thead>
    <tbody>";
    if(!empty($tipekar))foreach($tipekar as $tkr){
        $tab.="<tr class=rowcontent>
        <td>".$artitkr[$tkr]."</td>";
        $total[$tkr]=0;
        if(!empty($regional))foreach($regional as $reg){
            $islnk="";
            if($jumlahkar[$reg][$tkr]!=0){
            $islnk=" style='cursor:pointer;' onclick=getKary('".$reg."','".$tkr."',event)";
            }
            $tab.="<td align=right  ".$islnk.">".number_format($jumlahkar[$reg][$tkr])."</td>";
            $total[$tkr]+=$jumlahkar[$reg][$tkr];
            $totalgrand[$reg]+=$jumlahkar[$reg][$tkr];            
        }
      
        $tab.="
        <td align=right >".number_format($total[$tkr])."</td>
        </tr>";            
    }
    $tab.="<tr class=rowcontent>
    <td>".$_SESSION['lang']['total']."</td>";
    $totalnya=0;
    if(!empty($regional))foreach($regional as $reg){
        $tab.="<td align=right>".number_format($totalgrand[$reg])."</td>";
        $totalnya+=$totalgrand[$reg];            
    }
    $tab.="
    <td align=right>".number_format($totalnya)."</td>
    </tr>";            
    
    $tab.="</tbody></table>";
}	

switch($proses)
{
    case'preview':
        echo $tab;
    break;
    case'level1':
        echo $tab;
    break;
    case'getNmKar':
        $tab.="<script language=javascript src=js/generic.js></script><script language=javascript src=js/zTools.js></script>
               <script language=javascript src=js/sdm_2summarykaryawan.js></script>";
        $tab.="<link rel=stylesheet type=text/css href=style/generic.css>";
        $tab.="<img onclick=parent.detexcel(event,'".$lokTgs."','".$tipekary."','".$tanggal."') src=images/excel.jpg class=resicon title='MS.Excel'>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr><td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['namakaryawan']."</td></tr></thead><tbody>";
        if($tipekary!=4){
        $sdatakar="select namakaryawan,tipekaryawan,lokasitugas from ".$dbname.".datakaryawan 
                   where tanggalmasuk <= '".tanggalsystem($tanggal)."' and 
                   (tanggalkeluar >= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                   and lokasitugas='".$lokTgs."' and tipekaryawan='".$tipekary."' order by namakaryawan asc";
        }else{
            $sdatakar="select a.*  from ".$dbname.".datakaryawan a 
          left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
          tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
          and periodegaji='".$periodeGj."'   and tipekaryawan=4 and lokasitugas='".$lokTgs."'
          and idkomponen=1";
        }
        //echo $sdatakar;
        $qdatakar=mysql_query($sdatakar) or die(mysql_error($conn));
        while($rdatakar=  mysql_fetch_assoc($qdatakar)){
            $nor+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$nor."</td>";
            $tab.="<td>".$rdatakar['namakaryawan']."</td>";
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
    case'excelDt':
        $bgcolor="bgcolor=#DEDEDE";
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable><thead>";
        $tab.="<tr ".$bgcolor."><td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['namakaryawan']."</td></tr></thead><tbody>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$optTip[$tipekary]."</td>";
        if($tipekary!=4){
            $sdatakar="select namakaryawan,tipekaryawan,lokasitugas from ".$dbname.".datakaryawan 
                       where tanggalmasuk <= '".tanggalsystem($tanggal)."' and 
                       (tanggalkeluar >= '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                       and lokasitugas='".$lokTgs."' and tipekaryawan='".$tipekary."' order by namakaryawan asc";
        }else{
            $sdatakar="select a.*  from ".$dbname.".datakaryawan a 
              left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
              tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
              and periodegaji='".$periodeGj."'   and tipekaryawan=4 and lokasitugas='".$lokTgs."'
              and idkomponen=1";
        }
        //exit("error:".$sdatakar);
        $qdatakar=mysql_query($sdatakar) or die(mysql_error($conn));
        while($rdatakar=  mysql_fetch_assoc($qdatakar)){
            $nor+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td>".$nor."</td>";
            $tab.="<td>".$rdatakar['namakaryawan']."</td>";
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";  
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="dtDet_".$tanggal."_".$lokTgs;
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    case'excel2':
        $bgcolor="bgcolor=#DEDEDE";
                     $str="select * from ".$dbname.".datakaryawan
                    where tipekaryawan!=4  and tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') ";
                //echo $str;
                $res=mysql_query($str);
                while($bar=mysql_fetch_object($res)){
                    if($region!=''){
                        $qwe=$bar->lokasitugas;
                    }else{
                        $qwe=$unitreg[$bar->lokasitugas];
                    }
                    $jumlahkar[$qwe][$bar->tipekaryawan]+=1;
                }
                $sdmGaji="select a.*  from ".$dbname.".datakaryawan a 
                      left join ".$dbname.".sdm_gaji b on a.karyawanid=b.karyawanid where 
                      tanggalmasuk <= ".tanggalsystem($tanggal)." and (tanggalkeluar > '".substr(tanggalsystem($tanggal),0,6)."01' or tanggalkeluar = '0000-00-00') 
                      and periodegaji='".$periodeGj."'   and tipekaryawan=4
                      and idkomponen=1";
            //exit("error:".$sdmGaji) ;
            $qsdmGaji=mysql_query($sdmGaji) or die(mysql_error($conn));
            while($rsdmGaji=  mysql_fetch_assoc($qsdmGaji)){
                   if($region!=''){
                        $qwe=$rsdmGaji['lokasitugas'];
                    }else{
                        $qwe=$unitreg[$rsdmGaji['lokasitugas']];
                    }
                    $jumlahkar[$qwe][$rsdmGaji['tipekaryawan']]+=1;
            }
                   $brd=0;
                   $bgcolor="";
                if($region==''){
                    $region=$_SESSION['lang']['regional'];
                }else{
                    if($proses!='excel')
                    $tab.="<img onclick=level1excel(event,'sdm_slave_2summarykaryawan.php','".$tanggal."','".$region."') src=images/excel.jpg class=resicon title='MS.Excel'>";
                }
            $tab.="
                <table width=100% cellspacing=1 border=1>
                <thead>
                <tr>
                    <td ".$bgcolor.">".$region."</td>";
                    if(!empty($regional))foreach($regional as $reg)
                        if($region!='')
                        $tab.="<td ".$bgcolor." align=center>".$reg."</td>";
                    $tab.="
                    <td ".$bgcolor." align=center>".$_SESSION['lang']['total']."</td>
                </tr>        
                </thead>
                <tbody>";
                if(!empty($tipekar))foreach($tipekar as $tkr){
                    $tab.="<tr class=rowcontent>
                    <td>".$artitkr[$tkr]."</td>";
                    $total[$tkr]=0;
                    if(!empty($regional))foreach($regional as $reg){
                        $islnk="";
                        if($jumlahkar[$reg][$tkr]!=0){
                        $islnk=" style='cursor:pointer;' onclick=getKary('".$reg."','".$tkr."',event)";
                        }
                        $tab.="<td align=right  ".$islnk.">".number_format($jumlahkar[$reg][$tkr])."</td>";
                        $total[$tkr]+=$jumlahkar[$reg][$tkr];
                        $totalgrand[$reg]+=$jumlahkar[$reg][$tkr];            
                    }

                    $tab.="
                    <td align=right >".number_format($total[$tkr])."</td>
                    </tr>";            
                }
                $tab.="<tr class=rowcontent>
                <td>".$_SESSION['lang']['total']."</td>";
                $totalnya=0;
                if(!empty($regional))foreach($regional as $reg){
                    $tab.="<td align=right>".number_format($totalgrand[$reg])."</td>";
                    $totalnya+=$totalgrand[$reg];            
                }
                $tab.="
                <td align=right>".number_format($totalnya)."</td>
                </tr>";            

                $tab.="</tbody></table>";
                $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="summary_karyawan_".$tanggal."_".$region;
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="summary_karyawan_".$tanggal."_".$region;
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    
    default:
    break;
}
?>