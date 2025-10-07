<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$periode= checkPostGet('periode','');
$tampil= checkPostGet('tampil','');
$pabrik= checkPostGet('pabrik','');

if($pabrik==$_SESSION['lang']['all']){
    $str="select tanggal,sum(tbsdiolah) as tbsdiolah,sum(sisahariini) as sisahariini,sum(oer) as oer,sum(oerpk) as oerpk,
          sum(tbsmasuk) as tbsmasuk,sum(sisatbskemarin) as sisatbskemarin, kodeorg
          from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%' and kodeorg like '%%'
          group by tanggal order by tanggal asc";
}
else{    
$str="select * from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%' and kodeorg='".$pabrik."'
      order by tanggal asc";
}


//echo $str;
    $res2=mysql_query($str);
    $res=mysql_query($str);
    while($datArr=  mysql_fetch_assoc($res2))
    {
        
        $tbs[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['tbsdiolah'];
        $jmOer[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oer'];
        $jmOerPk[$datArr['kodeorg']][$datArr['tanggal']]=$datArr['oerpk'];
        
    }
    


    #jam start
     #jam end
    /*$sStart="select distinct tanggal,jammulai,jamselesai from ".$dbname.".pabrik_pengolahan 
             where kodeorg='".$pabrik."' and tanggal like '".$periode."%' and shift=1 order by tanggal asc";
    $qStart=mysql_query($sStart) or die(mysql_error($conn));
    while($rStart=  mysql_fetch_assoc($qStart)){
        $jmStart[$rStart['tanggal']]=$rStart['jammulai'];
        $jmEnd[$rStart['tanggal']]=$rStart['jamselesai'];
    }*/
    
    
   
   
    
    
    if(!isset($_GET['method']) or $_GET['method']!='excel')
    {
        $bg="";
        $brdr="0";
         echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
          if($pabrik==$_SESSION['lang']['all']){              
          }
          else{
              echo "Graph OER : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Graphics'  onclick=grafikProduksi('".$periode."','".$tampil."','".$pabrik."',event)>";
              echo "<br>Graph FFA : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Graphics'  onclick=grafikProduksiFfa('".$periode."','".$tampil."','".$pabrik."',event)>";
              echo "<br>Graph Tbs diolah dan Sisa Tbs : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Graphics'  onclick=grafikTbs('".$periode."','".$tampil."','".$pabrik."',event)>";

              
          }
	 echo "<br>Print PDF : <img src='images/skyblue/pdf.jpg' class=resicon title='PDF' onclick=laporanPDF('".$periode."','".$tampil."','".$pabrik."',event)>
	       <br>Print Excel :<img src='images/skyblue/excel.jpg' class=resicon title='Spreadsheet' onclick=laporanEXCEL('".$periode."','".$tampil."','".$pabrik."',event)>      
            ";
         $komanya=3;
    }
    else
    {
        $bg=" bgcolor=#DEDEDE";
        $brdr="1";
        $komanya=3;
    }
    if($pabrik==$_SESSION['lang']['all']){
        $tab.="
      <table class=sortable cellspacing=1 border=".$brdr." style='width:1500px;'>
	    <thead>
		  <tr class=rowheader>
		   <td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['kodeorganisasi']."</td>
		   <td rowspan=2 align=center ".$bg." width=100px>".$_SESSION['lang']['tanggal']."</td>
		   <td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['tersedia']." (Kg.)</td>
		   <td align=center colspan=2  ".$bg.">".$_SESSION['lang']['tbsdiolah']." (Kg.)</td>
                   <td rowspan=2 align=center  ".$bg.">".$_SESSION['lang']['sisa']." (Kg.)</td>
		   <td colspan=2 align=center  ".$bg.">".$_SESSION['lang']['cpo']."</td>
		   <td colspan=2 align=center  ".$bg.">".$_SESSION['lang']['kernel']."</td>
		  </tr>  
		  <tr class=rowheader> 
                   <td align=center  ".$bg.">HI</td><td align=center  ".$bg.">SHI</td> 
                   <td align=center  ".$bg.">".$_SESSION['lang']['cpo']." (Kg) HI</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['cpo']." (Kg) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kernel']." (Kg) HI</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['kernel']." (Kg) SHI</td>
		  </tr>
		</thead>
		<tbody>";
    }
    else{
        $tab.="
      <table class=sortable cellspacing=1 cellpadding=5 border=".$brdr." style='width:1800px;'>
	    <thead>
		  <tr class=rowheader>
		   <td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['kodeorganisasi']."</td>
		   <td rowspan=2 align=center ".$bg." width=100px>".$_SESSION['lang']['tanggal']."</td>
		   <td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['tersedia']." (Kg.)</td>
		   <td align=center colspan=2  ".$bg.">".$_SESSION['lang']['tbsdiolah']." (Kg.)</td>
                   <td rowspan=2 align=center  ".$bg.">".$_SESSION['lang']['sisa']." (Kg.)</td>
                   <td colspan=4 align=center  ".$bg.">".$_SESSION['lang']['jampengolahan']."</td>
                   <td colspan=4 align=center  ".$bg.">".$_SESSION['lang']['jamstagnasi']."</td>
                   <td colspan=2 align=center ".$bg.">".$_SESSION['lang']['kapasitasOlah']." (Ton/Jam)</td>
		   <td colspan=10 align=center  ".$bg.">".$_SESSION['lang']['cpo']."</td>
		   <td colspan=10 align=center  ".$bg.">".$_SESSION['lang']['kernel']."</td>
		  </tr>  
		  <tr class=rowheader> 
                   <td align=center  ".$bg.">HI</td><td align=center  ".$bg.">SHI</td> 
                   <td align=center  ".$bg.">START</td><td align=center  ".$bg.">STOP</td>                   
                   <td align=center  ".$bg.">HI</td><td align=center  ".$bg.">SHI</td> 
                   <td align=center  ".$bg.">HI</td><td align=center  ".$bg.">SHI</td> 
                   <td align=center  ".$bg.">%HI</td><td align=center  ".$bg.">%SHI</td> 
                   <td align=center  ".$bg.">HI</td><td align=center  ".$bg.">SHI</td> 
		   <td align=center  ".$bg.">".$_SESSION['lang']['cpo']." (Kg) HI</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['cpo']." (Kg) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['oer']." (%)</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['oer']." (%) SHI</td>
		   <td align=center  ".$bg.">(FFa)(%)</td>
		   <td align=center  ".$bg.">(FFa)(%) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kotoran']." (%) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kadarair']." (%)</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kadarair']." (%) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kernel']." (Kg) HI</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['kernel']." (Kg) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['oerpk']." (%)</td>
                   <td align=center  ".$bg.">".$_SESSION['lang']['oerpk']." (%) SHI</td>
		   <td align=center  ".$bg.">(FFa) (%)</td>
		   <td align=center  ".$bg.">(FFa) (%) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kotoran']." (%) SHI</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kadarair']." (%)</td>
		   <td align=center  ".$bg.">".$_SESSION['lang']['kadarair']." (%) SHI</td>
		  </tr>
		</thead>
		<tbody>";
    }
    
    $tgl=1;
    $cposdkem=0;
    $ffasdkem=0;
    $kotsdkem=0;
    $airsdkem=0;
    
    $kersdkem=0;
    $ffksdkem=0;
    $koksdkem=0;
    $aiksdkem=0;
	$ared=0;
       while($bar=mysql_fetch_object($res))
        {
			$ared+=1;
			/*$sPengolahan="select jamdinasbruto as jampengolahan, jamstagnasi as jamstagnasi from ".$dbname.".pabrik_pengolahan 
				where kodeorg='".$bar->kodeorg."' and tanggal='".$bar->tanggal."'";
        
			//echo $sPengolahan."__\n";
			$qPengolahan=mysql_query($sPengolahan) or die(mysql_error($conn));
			#$rPengolahan=mysql_fetch_assoc($qPengolahan);
			unset($jamP);
			unset($menitP);
			unset($jamS);
			unset($menitS);      
			while($res2=mysql_fetch_object($qPengolahan)){
				$dd=explode(".",$res2->jampengolahan);
				$ee=explode(".",$res2->jamstagnasi);
				$jamP[]=$dd[0];
				$menitP[]=count($dd)>1? $dd[1] * 0.6: '';
				$jamS[]=$ee[0];
				$menitS[]=count($ee)>1? $ee[1] * 0.6: '';
			}*/
                        
            
            ##jam baru
		    $aOlah="select sum(jamdinasbruto) as jampengolahan, sum(jamstagnasi) as jamstagnasi from ".$dbname.".pabrik_pengolahan 
               where kodeorg='".$bar->kodeorg."' and tanggal='".$bar->tanggal."'";
		    $bOlah=mysql_query($aOlah) or die (mysql_error($conn));
			$cOlah=mysql_fetch_assoc($bOlah);            
                        
                        
           /*             
           @$rPengolahan['jampengolahan']=  array_sum($jamP)+(array_sum($menitP)/60);
		   setIt($sJamPeng,0);
		   setIt($sJamStag,0);
           $sJamPeng+=$rPengolahan['jampengolahan'];
           //
           @$rPengolahan['jamstagnasi']=array_sum($jamS)+(array_sum($menitS)/60); 
           $sJamStag+=$rPengolahan['jamstagnasi'];
           */
                        
              @$rPengolahan['jampengolahan']=$cOlah['jampengolahan']; 
           $sJamPeng+=$rPengolahan['jampengolahan'];
		   
		   
           //
          /* @$rPengolahan['jamstagnasi']=array_sum($jamS)+(array_sum($menitS)/60); 
           $sJamStag+=$rPengolahan['jamstagnasi'];*/
		   
		   @$rPengolahan['jamstagnasi']=$cOlah['jamstagnasi']; 
           $sJamStag+=$rPengolahan['jamstagnasi'];            
                        
           
           if(strlen($tgl)==1)
           {
               $agl="0".$tgl;
           }
           $tglServ=substr($bar->tanggal,0,8);
           $tab.="<tr class=rowcontent>";
           if($pabrik==$_SESSION['lang']['all']){
               $tab.="<td>Seluruhnya</td>";
           }
           else{
               $tab.="<td>".$bar->kodeorg."</td>";
           }
           if(isset($_GET['method']) and $_GET['method']=='excel'){
               $tab.="<td>".$bar->tanggal."</td>";
           }else{
               $tab.="<td>".tanggalnormal($bar->tanggal)."</td>";
           }
            $tab.="<td align=right>".number_format($bar->tbsmasuk+$bar->sisatbskemarin,0,'.',',')."</td>";
			setIt($tbs[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($tbs[$bar->kodeorg][$bar->tanggal],0);
			setIt($des,0);
			setIt($oerTotal,0);
			setIt($oerpkTotal,0);
			setIt($siKps,0);
			$tbsSd=$tbs[$bar->kodeorg][$tglServ.$agl+1];
            $tbsSd2=$tbs[$bar->kodeorg][$bar->tanggal];
            $tbsTot=$tbsSd2+$tbsSd;
            $des+=$tbsTot;
            
            //get cpo
			setIt($jmOer[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($jmOer[$bar->kodeorg][$bar->tanggal],0);
            $oerSd=$jmOer[$bar->kodeorg][$tglServ.$agl+1];
            $oerSd2=$jmOer[$bar->kodeorg][$bar->tanggal];
            $oerTot=$oerSd2+$oerSd;
            $oerTotal+=$oerTot;
            
            //get pk
			setIt($jmOerPk[$bar->kodeorg][$tglServ.$agl+1],0);
			setIt($jmOerPk[$bar->kodeorg][$bar->tanggal],0);
            $oerpkSd=$jmOerPk[$bar->kodeorg][$tglServ.$agl+1];
            $oerpkSd2=$jmOerPk[$bar->kodeorg][$bar->tanggal];
            $oerpkTot=$oerpkSd+$oerpkSd2;
            $oerpkTotal+=$oerpkTot;
            
           
            @$kpsitas=($bar->tbsdiolah/$rPengolahan['jampengolahan'])/1000;     
            $siKps+=$rPengolahan['jampengolahan'];
            // exit("Error:".substr($bar->tanggal,-2));
            if($ared==1)
            {
                $olhShi=$kpsitas;
                @$oershi=$oerTotal/$bar->tbsdiolah*100;
                @$oerpkshi=$oerpkTotal/$bar->tbsdiolah*100;
            }
            else
            {
                @$olhShi=$des/$siKps/1000;
                @$oershi=$oerTotal/$des*100;
                @$oerpkshi=$oerpkTotal/$des*100;
            }
            
            @$ffasdhi=(($bar->ffa*$bar->oer)+($cposdkem*$ffasdkem))/$oerTotal;
            @$kotsdhi=(($bar->kadarkotoran*$bar->oer)+($cposdkem*$kotsdkem))/$oerTotal;
            @$airsdhi=(($bar->kadarair*$bar->oer)+($cposdkem*$airsdkem))/$oerTotal;
            
            @$ffksdhi=(($bar->ffapk*$bar->oerpk)+($kersdkem*$ffksdkem))/$oerpkTotal;
            @$koksdhi=(($bar->kadarkotoranpk*$bar->oerpk)+($kersdkem*$koksdkem))/$oerpkTotal;
            @$aiksdhi=(($bar->kadarairpk*$bar->oerpk)+($kersdkem*$aiksdkem))/$oerpkTotal;

            $cposdkem=$oerTotal;
            $ffasdkem=$ffasdhi;
            $kotsdkem=$kotsdhi;
            $airsdkem=$airsdhi;
            
            $kersdkem=$oerpkTotal;
            $ffksdkem=$ffksdhi;
            $koksdkem=$koksdhi;
            $aiksdkem=$aiksdhi;
            if($pabrik==$_SESSION['lang']['all']){
                $tab.= "<td align=right>".number_format($bar->tbsdiolah,0,'.',',')."</td>
                        <td align=right>".number_format($des,0,'.',',')."</td> 
                        <td align=right>".number_format($bar->sisahariini,0,'.',',')."</td>";
                $tab.=" <td align=right>".number_format($bar->oer,0,'.',',')."</td> 
                        <td align=right>".number_format($oerTotal,0,'.',',')."</td>"; 
                $tab.=" <td align=right>".number_format($bar->oerpk,0,'.',',')."</td>
                        <td align=right>".number_format($oerpkTotal,0,'.',',')."</td>";
            }
            else{
                $tab.= "<td align=right>".number_format($bar->tbsdiolah,0,'.',',')."</td>
                        <td align=right>".number_format($des,0,'.',',')."</td>
                        <td align=right>".number_format($bar->sisahariini,0,'.',',')."</td>";
                
                /*$tab.="
                        <td align=right>".substr($jmStart[$bar->tanggal],0,5)."</td>
                        <td align=right>".substr($jmEnd[$bar->tanggal],0,5)."</td>";*/
                
                $ijm="select jammulai from ".$dbname.".pabrik_pengolahan where tanggal='".$bar->tanggal."'"
                        . " and kodeorg='".$pabrik."' order by shift asc limit 1  ";
                $njm=  mysql_query($ijm) or die (mysql_error($conn));
                $djm=  mysql_fetch_assoc($njm);
                
                $ijs="select jamselesai from ".$dbname.".pabrik_pengolahan where tanggal='".$bar->tanggal."'"
                        . " and kodeorg='".$pabrik."' order by shift desc limit 1  ";
                $njs=  mysql_query($ijs) or die (mysql_error($conn));
                $djs=  mysql_fetch_assoc($njs);
                
                
                $tab.= "
                        <td align=right>".substr($djm['jammulai'],0,5)."</td>
                        <td align=right>".substr($djs['jamselesai'],0,5)."</td>";
                //$tab.= "<td align=right>".number_format($rPengolahan['jamstagnasi'],2,'.',',')."</td>
               
        
                
                $tab.= "<td align=right>".number_format($rPengolahan['jampengolahan'],2,'.',',')."</td>";
                $tab.= "<td align=right>".number_format($sJamPeng,2,'.',',')."</td>";
                
                
                
                $tab.="<td align=right>".number_format($rPengolahan['jamstagnasi'],2,'.',',')."</td>
                  <td align=right>".number_format($sJamStag,2,'.',',')."</td>";
                
                
                $tab.= "<td align=right>".@number_format(($rPengolahan['jamstagnasi']/$rPengolahan['jampengolahan']*100),2,'.',',')."</td>
                        <td align=right>".@number_format(($sJamStag/$sJamPeng*100),2,'.',',')."</td>";
                
                
                
                
                $tab.= "<td align=right>".number_format($kpsitas,2,'.',',')."</td>
                        <td align=right>".number_format($olhShi,2,'.',',')."</td>";
                
                
                
                
                $tab.=" <td align=right>".number_format($bar->oer,0,'.',',')."</td>
                        <td align=right>".number_format($oerTotal,0,'.',',')."</td>";
                $tab.=" <td align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,$komanya,'.',','))."</td>";
                $tab.=" <td align=right>".number_format($oershi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->ffa,$komanya,'.',',')."</td>
                        <td align=right>".number_format($ffasdhi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->kadarkotoran,$komanya,'.',',')."</td>
                        <td align=right>".number_format($kotsdhi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->kadarair,$komanya,'.',',')."</td>
                        <td align=right>".number_format($airsdhi,$komanya,'.',',')."</td>";
                $tab.=" <td align=right>".number_format($bar->oerpk,0,'.',',')."</td>
                        <td align=right>".number_format($oerpkTotal,0,'.',',')."</td>";
                $tab.=" <td align=right>".(@number_format($bar->oerpk/$bar->tbsdiolah*100,$komanya,'.',','))."</td>
                        <td align=right>".number_format($oerpkshi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->ffapk,$komanya,'.',',')."</td>
                        <td align=right>".number_format($ffksdhi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->kadarkotoranpk,$komanya,'.',',')."</td>
                        <td align=right>".number_format($koksdhi,$komanya,'.',',')."</td>
                        <td align=right>".number_format($bar->kadarairpk,$komanya,'.',',')."</td>
                        <td align=right>".number_format($aiksdhi,$komanya,'.',',')."</td>";
            }
          
           $tab.="</tr>";
           $tgl++;
         }	  
		
       $tab.="	</tbody>
		<tfoot>
		</tfoot>
	  </table>
	  </fieldset>";
      if(isset($_GET['method']) and $_GET['method']=='excel')
      {
                      //exit("Error:masuk".$method);
          $dte=date("YmdHis");
                      $nop_="laporan_produksi_".$dte;
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

      }
      else
      {
          echo $tab;
      }

?>