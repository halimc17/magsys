<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

        $pt=$_GET['pt'];
        $gudang=$_GET['gudang'];
        $tanggalpivot=$_GET['tanggalpivot'];
        $tanggalv=  tanggalsystemn($_GET['tanggalpivot']);

        $statuspo=$_GET['statuspo'];
	
       
        $supkontran=$_GET['supkontran'];
        
        //exit("Error:$pt._.$gudang._.$tanggalpivot._.$tanggalv._.$statuspo._.$supkontran");
        $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";



$namapt='Seluruhnya';
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
        $namapt=strtoupper($bar->namaorganisasi);
}
    $stream='';

/*if($gudang!='')
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal <= '".$tanggalv."' and kodeorg = '".$gudang."'and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}else
if($pt!='')
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal  <= '".$tanggalv."' and kodeorg = '".$pt."'and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}else
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal <= '".$tanggalv."' and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}*/
    
    
     
if($gudang!=''){
	$whereGudang = " and a.kodeorg = '".$gudang."'";
}else{
	$whereGudang = "";
}

if($pt!=''){
    $wherePt = " and a.kodeorg = '".$pt."'"; 
}else{
	$wherePt = "";
}

if($statuspo!='')
{
	if($statuspo==1)
	{
		$wherePo = " and b.lokalpusat = '1'";	
	}
	else
	{
		$wherePo = " and (b.lokalpusat = '0' or b.lokalpusat is null)";
	}
}
else
{
	$wherePo = "";
}

if($supkontran!=''){
	$wheresupkontran = " and left(a.kodesupplier,1) = '".$supkontran."'";
}else{
	$wheresupkontran = "";
}




$str = "select a.* from ".$dbname.".aging_sch_vw a 
		left join ".$dbname.".log_poht b 
		on a.nopo = b.nopo
                where a.posting=1 and a.tanggal <= '".$tanggalv."' and (a.nilaiinvoice > dibayar or a.dibayar is NULL) "
        . " ".$whereGudang." ".$wherePt." ".$wherePo." ".$wheresupkontran." ";
	//where a.tanggal > '2011-12-31' and (a.nilaiinvoice > dibayar or a.dibayar is NULL) ".$whereGudang." ".$wherePt." ".$wherePo."";

    

function tanggalbiasa($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,4,4)."-".substr($_q,2,2)."-".substr($_q,0,2);
 return($_retval);
}

//=================================================
        $res=mysql_query($str);
        $no=0;
        if(@mysql_num_rows($res)<1)
        {
                echo"<tr class=rowcontent><td colspan=13>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
        }
        else
        {
                $stream.=$_SESSION['lang']['usiahutang'].": ".$namapt."<br>
                tanggal: ".$tanggalpivot."
                <table border=1>
                    <tr>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['nourut']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['noinvoice']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['namasupplier']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['jatuhtempo']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['nopokontrak']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['nilaipokontrak']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['nilaiinvoice']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['belumjatuhtempo']."</td>
                          <td nowrap align=center colspan=4>".$_SESSION['lang']['sudahjatuhtempo']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['dibayar']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['jmlh_hari_outstanding']."</td>
                        </tr>  
                    <tr>
                          <td nowrap align=center>1-30 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>31-60 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>61-90 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>over 90 ".$_SESSION['lang']['hari']."</td>
                        </tr>";  
             $total0=$total30=$total60=$total90=$total100=$totaldibayar=0;
            $totalinvoice=0;
                while($bar=mysql_fetch_object($res))
                {
                        $namasupplier	=$bar->namasupplier;
                        $noinvoice	=$bar->noinvoice; 
                        $tanggal	=$bar->tanggal; 
                        $jatuhtempo 	=$bar->jatuhtempo;
                        $nopokontrak    =$bar->nopo;
                        $nilaipo        =$bar->kurs*$bar->nilaipo;
                        $nilaikontrak   =$bar->kurs*$bar->nilaikontrak;
                        $nilaiinvoice 	=$bar->kurs*$bar->nilaiinvoice;
                        $totalinvoice+=$nilaiinvoice;
                        $dibayar 	=$bar->kurs*$bar->dibayar;
                        $sisainvoice    =$nilaiinvoice-$dibayar;
                        $nilaipokontrak =$nilaipo;
                        if($nilaikontrak>0)$nilaipokontrak=$nilaikontrak;
//			$date1=date('Y-m-d');
                        $date1=$tanggalpivot;
                        $diff =(strtotime($jatuhtempo)-strtotime($date1));
                        $outstd =floor(($diff)/(60*60*24));
//			if($outstd<1)$outstd=0;
                        /*$flag0=$flag15=$flag30=$flag45=$flag100=0;
                        if($outstd!=0)$outstd*=-1;
                        if($outstd<=0)$flag0=1; 
                        if(($outstd>=1)and($outstd<=15))$flag15=1;
                        if(($outstd>=16)and($outstd<=30))$flag30=1;
                        if(($outstd>=31)and($outstd<=45))$flag45=1;
                        if($outstd>45)$flag100=1;
                        if($flag0==1)$total0+=$sisainvoice;
                        if($flag15==1)$total15+=$sisainvoice;
                        if($flag30==1)$total30+=$sisainvoice;
                        if($flag45==1)$total45+=$sisainvoice;
                        if($flag100==1)$total100+=$sisainvoice;*/
                        
                        
                             $flag0=$flag30=$flag60=$flag90=$flag100=0;
                            if($outstd!=0)$outstd*=-1;
                            if($outstd<=0)$flag0=1; 
                            if(($outstd>=1)and($outstd<=30))$flag30=1;
                            if(($outstd>=31)and($outstd<=60))$flag60=1;
                            if(($outstd>=61)and($outstd<=90))$flag90=1;
                            if($outstd>90)$flag100=1;
                            if($flag0==1){$total0+=$sisainvoice;}
                            if($flag30==1){$total30+=$sisainvoice;}
                            if($flag60==1){$total60+=$sisainvoice;}
                            if($flag90==1){$total90+=$sisainvoice;}
                            if($flag100==1){$total100+=$sisainvoice;}
                        
                        
                        $totaldibayar+=$dibayar;
                        if($jatuhtempo=='0000-00-00'){ $outstd=''; $jatuhtempo=''; }
//			if($dibayar>=$nilaiinvoice)continue;
                        $no+=1;

                        $stream.="<tr>
                                  <td nowrap align=center>".$no."</td>
                                  <td nowrap align=center>".$tanggal."</td>
                                  <td nowrap align=left nowrap>&nbsp;".$noinvoice."</td> 
                                  <td nowrap align=left nowrap>".$namasupplier."</td> 
                                  <td nowrap align=center>".$jatuhtempo."</td>
                                  <td nowrap align=center>".$nopokontrak."</td>
                                  <td nowrap align=right>".number_format($nilaipokontrak,2)."</td>
                                  <td nowrap align=right>".number_format($nilaiinvoice,2)."</td>
                                  <td nowrap align=right>";
                                  if($flag0==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag30==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag60==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag90==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag100==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>".number_format($dibayar,2)."</td>
                                  <td nowrap align=right>".$outstd."</td>
                                </tr>";
                }
                $stream.="<tr>
                                  <td colspan=7 align=center>TOTAL</td>
                                  <td align=right>";
                                  $stream.= number_format($totalinvoice,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total0,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total30,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total60,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total90,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total100,2); $stream.="</td>
                                  <td align=right>".number_format($totaldibayar,2)."</td>
                                  <td align=right>&nbsp;</td>
                        </tr>";                 
          $stream.="</table>";	
        }
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];

$nop_="DaftarUsiaHutang";
if(strlen($stream)>0)
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
 if(!fwrite($handle,$stream))
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
?>