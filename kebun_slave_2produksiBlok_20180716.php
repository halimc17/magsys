<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=$_GET['proses'];
switch ($proses){
	case 'preview':
		$param=$_POST; 
		break;
	case 'excel':
		$param=$_GET;    
		break;
}

    $bulanini=$param['periode'];
    $qwe=explode('-',$bulanini);
    $tahunlalu=$qwe[0];
    $bulanlalu=$qwe[1];
    if($bulanlalu=='01'){
        $tahunlalu-=1;
        $bulanlalu='12';
    }else{
        $bulanlalu-=1;
    }
    
    $bulanlalu=str_pad($bulanlalu, 2, "0", STR_PAD_LEFT);

    // bjr bulan kemarin =  taken from kebun_laporanPanen_orang.php
    $bulankemarin=$tahunlalu."-".$bulanlalu;
    
    $sbjrlalu="select blok, sum(jjg) as jjg, sum(kgwb) as kgwb from ".$dbname.".kebun_spb_vw
        where notiket IS NOT NULL and tanggal like '".$bulankemarin."%'
        group by blok";
    $qbjrlalu=mysql_query($sbjrlalu) or die(mysql_error($conn));
    while($rbjrlalu=  mysql_fetch_assoc($qbjrlalu))
    {
        @$beje=$rbjrlalu['kgwb']/$rbjrlalu['jjg'];
        $bjrlalu[$rbjrlalu['blok']]=$beje;
    }    
    
	if($param['intiplasma']!='')
	{
		$whrip=" and  intiplasma='".$param['intiplasma']."' ";
	}
	

        //ambil  tahun tanam
        $str="select kodeorg,tahuntanam,kodeorg from ".$dbname.".setup_blok where kodeorg like '".$param['idKebun']."%' ".$whrip." ";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $tt[$bar->kodeorg]=$bar->tahuntanam;
            $blok[]=$bar->kodeorg;
        }
        
        //ambil  jjg panen
        $str="select sum(hasilkerja) as jjgpanen,kodeorg,tanggal from ".$dbname.".kebun_prestasi_vw where tanggal like '".$param['periode']."%'
                  and kodeorg like '".$param['idKebun']."%' group by tanggal,kodeorg";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $jjgpanen[$bar->tanggal][$bar->kodeorg]=$bar->jjgpanen;
        }
        //ambil janjang spb
        $str="select sum(jjg) as jjgangkut,blok,sum(totalkg) as kgwb, tanggal,sum(brondolan) as brd from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."' group by tanggal,blok";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res))
        {
            $jjgangkut[$bar->tanggal][$bar->blok]=$bar->jjgangkut;
            $brdkbn[$bar->tanggal][$bar->blok]=$bar->brd;
            $berat[$bar->tanggal][$bar->blok]=$bar->kgwb;
        }        
        //======================================
        //ambil spb per tiket
        $str="select blok,jjg,tanggal,notiket from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."'";
        $res=mysql_query($str);
        while($bar=mysql_fetch_object($res)){
            $spbk[$bar->notiket][$bar->tanggal][$bar->blok]=$bar->jjg;
            $spbktg[$bar->notiket]=$bar->tanggal;
        }
        //ambil brondolan per no tiket dari timbangan
        $str="select notransaksi,brondolan as bb from ".$dbname.".pabrik_timbangan
                  where notransaksi in(select notiket from ".$dbname.".kebun_spb_vw where tanggal like '".$param['periode']."%'
                  and kodeorg = '".$param['idKebun']."')";
        $res=mysql_query($str);
		$tiket = array();
        while($bar=mysql_fetch_object($res))
        {
            $tiket[$bar->notransaksi]=$bar->bb;
        }        
        //kalkulasi brondolan per blok spb;
        foreach($tiket as $tik =>$nx)
        {
            foreach($spbk[$tik] as $tg){
                    $tjg=array_sum($tg);
                    foreach($tg as $bl=>$jg)
                    {
                            setIt($brd[$spbktg[$tik]][$bl],0);
                            $brd[$spbktg[$tik]][$bl]+=$jg/$tjg*$tiket[$tik];
                    }
            }    
            
        }
        
        
        
                ##tambahan ind
        #ambil tiket
        $iTim="select sum(kgsortasi) as kgsortasi,blok,tanggal from ".$dbname.".sortasi_pabrik_spb 
                  where tanggal like '".$param['periode']."%'
                  and blok like '%".$param['idKebun']."%' group by tanggal,blok ";
        $nTim=  mysql_query($iTim) or die (mysql_error($conn));
        while($dTim=  mysql_fetch_assoc($nTim))
        {
            //$berat[$bar->tanggal][$bar->blok]=$bar->kgwb;
            $sortasi[$dTim['tanggal']][$dTim['blok']]=$dTim['kgsortasi'];
        }
     
        echo"<pre>";
        //print_r($sortasi);
        echo"</pre>";
        
        
 $stream="Produksi_Per_Blok ".$param['idKebun']." Periode:".$param['periode']."
         <table class=sortable border=0 cellspacing=1>
          <thead>
          <tr class=rowheader>
             <td>No</td>
             <td>".$_SESSION['lang']['tanggal']."</td>
             <td>".$_SESSION['lang']['blok']."</td>
             <td>".$_SESSION['lang']['thntnm']."</td>
             <td>".$_SESSION['lang']['tbs']." ".$_SESSION['lang']['panen']."(JJG)</td>
             <td>".$_SESSION['lang']['pengiriman']."(JJG)</td>
             <td>Netto(Kg)</td>        
             <td>Sortasi (Kg)</td> 
             <td>Berat Normal (netto-sortasi) (Kg)</td> 
             <td>".$_SESSION['lang']['bjr']." Actual</td>           
             <td>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['blnlalu']."</td>           
          </tr></thead><tbody>
          ";
      //jumlah hari
      
      ##defaultnya ini
     //$mk=mktime(0,0,0,substr($param['periode'],5,2),15,substr($param['periode'],0,4));
      
      #kenapa jadi hanya 15 hari.. di move jadi 31 hari
 
    $str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$param['periode']."' limit 1 ";
    $res=  mysql_query($str) or die (mysql_error($conn));
    $bar=  mysql_fetch_assoc($res);
        $tglakhir=  explode('-', $bar['tanggalsampai']);
    
 
      $mk=mktime(0,0,0,substr($param['periode'],5,2),$tglakhir[2],substr($param['periode'],0,4));
      $jhari=date('j',$mk);
      $a=$tjp=$tja=$tbk=$tb=$tberat=0;
      
      
      for($x=1;$x<=$jhari;$x++){
          
      
          
          foreach($blok as $ki=>$bl){
              
              
              
              
            $tttt=str_pad($x, 2, "0", STR_PAD_LEFT);
            
           
            
			setIt($jjgpanen[$param['periode']."-".$tttt][$bl],0);
			setIt($jjgangkut[$param['periode']."-".$tttt][$bl],0);
			setIt($brdkbn[$param['periode']."-".$tttt][$bl],0);
			setIt($brd[$param['periode']."-".$tttt][$bl],0);
			setIt($berat[$param['periode']."-".$tttt][$bl],0);
            if($jjgpanen[$param['periode']."-".$tttt][$bl]>0 or $jjgangkut[$param['periode']."-".$tttt][$bl]>0 or $brdkbn[$param['periode']."-".$tttt][$bl]>0 or $brd[$param['periode']."-".$tttt][$bl]>0)
            {
                $a++;
                @$bjraktual=$berat[$param['periode']."-".$tttt][$bl]/$jjgangkut[$param['periode']."-".$tttt][$bl];
                if($bjraktual<$bjrlalu[$bl]){
                    $merah=' bgcolor=red';
                }else{
                    $merah='';
                }
                $stream.="<tr class=rowcontent>
                           <td>".$a."</td>
                           <td>".$param['periode']."-".$tttt."</td>
                           <td>".$bl."</td>
                           <td>".$tt[$bl]."</td>
                            <td align=right>".number_format($jjgpanen[$param['periode']."-".$tttt][$bl])."</td>
                            <td align=right>".number_format($jjgangkut[$param['periode']."-".$tttt][$bl])."</td>    
                           <td align=right>".number_format($berat[$param['periode']."-".$tttt][$bl],2)."</td> 
                            
                            <td align=right>".number_format($sortasi[$param['periode']."-".$tttt][$bl],2)."</td> 
                            <td align=right>".number_format($berat[$param['periode']."-".$tttt][$bl]-$sortasi[$param['periode']."-".$tttt][$bl],2)."</td>     
                           
                           <td align=right ".$merah.">".number_format($bjraktual,2)."</td> 
                           <td align=right>".number_format($bjrlalu[$bl],2)."</td> 
                     </tr>";
				$tjp+=$jjgpanen[$param['periode']."-".$tttt][$bl];
                $tja+=$jjgangkut[$param['periode']."-".$tttt][$bl];
                $tbk+=$brdkbn[$param['periode']."-".$tttt][$bl];
                $tb+=$brd[$param['periode']."-".$tttt][$bl];
                $tberat+=$berat[$param['periode']."-".$tttt][$bl];
                
                $tsortasi+=$sortasi[$param['periode']."-".$tttt][$bl];
                $tnormal+=$berat[$param['periode']."-".$tttt][$bl]-$sortasi[$param['periode']."-".$tttt][$bl];
            }
          }
      }
      $stream.="</tbody><tfoot>
                    <tr class=rowcontent>
                       <td colspan=4>TOTAL</td>
                       <td align=right>".number_format($tjp,2)."</td>
                       <td align=right>".number_format($tja,2)."</td>
                       <td align=right>".number_format($tberat,2)."</td>
                            <td align=right>".number_format($tsortasi,2)."</td>
                                 <td align=right>".number_format($tnormal,2)."</td>
                           
                       <td></td><td></td>    
                       </tr align=right>
                 </tfoot></table>Pastikan SPB sudah diinput dengan Benar/Make sure all FFB Transport document has been confirmed";
        //========================================
switch ($proses){
        case 'preview':
                echo $stream;
            break;
         case 'excel':
            $nop_="produksiperblok_".$param['idKebun']."_".$param['periode'];
            if(strlen($stream)>0)
            {
                 $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                 gzwrite($gztralala, $stream);
                 gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
            }
             break;
}

?>
