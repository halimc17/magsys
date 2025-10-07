<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

	$pt=$_POST['pt'];
	$gudang=$_POST['gudang'];
	$intiplasma=$_POST['intiplasma'];
	$tgl1=$_POST['tgl1'];
	$tgl2=$_POST['tgl2'];        
       
	if($gudang=='')
	{
            $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,
                  sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
                  sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
                  sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk
                  ,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi
                  from ".$dbname.".kebun_prestasi_vs_hk a
				  left join ".$dbname.".organisasi d on a.kodeorg = d.kodeorganisasi
                  left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
				  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
                  where c.induk = '".$pt."' and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%'
                  and a.jurnal=1
                  group by a.tanggal,a.kodeorg";
	}
	else
	{
            $where='';
            if($gudang != $_SESSION['empl']['lokasitugas']){                
                $where=" and a.jurnal=1";
            }
            $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,
                  sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
                  sum(a.upahpenalty) as upahpenalty, sum(a.premibasis) as premibasis,
                  sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,count(a.karyawanid) as jumlahhk  
                  ,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi
                  from ".$dbname.".kebun_prestasi_vs_hk a 
				  left join ".$dbname.".organisasi d on a.kodeorg = d.kodeorganisasi
				  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
                  where unit = '".$gudang."'  and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%' 
                  ".$where."
                  group by a.tanggal, a.kodeorg";
	}	
//=================================================
        $res=mysql_query($str) or die(mysql_error($conn));
	$no=0;
	if(mysql_num_rows($res)<1)
	{
		echo"<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
	}
	else
	{
		$totberat=$totUpah=$totUpahpenalty=$totJjg=$totPremi=$totPremibasis=$totHk=$totPenalty=0;
	while($bar=mysql_fetch_object($res))
	{
		$no+=1;
			$periode=date('Y-m-d H:i:s');
			$tanggal=$bar->tanggal; 
			$kodeorg 	=$bar->kodeorg;
			$namaorg 	=$bar->namaorganisasi;
		$arr="tanggal##".$tanggal."##kodeorg##".$kodeorg;	  
		echo"<tr class=rowcontent>
				  <td align=center width=20 style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".$no."</td>
				 
				  <td align=center style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".tanggalnormal($tanggal)."</td>
				  <td align=center style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".substr($kodeorg,0,6)."</td>
				  <td align=center style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".$namaorg."</td>
				  <td align=center style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".$bar->intiplasma."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".$bar->tahuntanam."</td>    
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->jjg,0)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->berat,2)."</td>    
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->jumlahhk,2)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->upah,2)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->upahpenalty,2)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->premi,2)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetailDenda(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->penalty,2)."</td>
                                  <td align=right style='cursor:pointer;' title='Click' onclick=\"zDetail(event,'kebun_slave_2panen.php','".$arr."');\">".number_format($bar->upah-$bar->upahpenalty+$bar->premi-$bar->penalty,2)."</td>
			</tr>";
		$totJjg+=$bar->jjg;
		$totberat+=$bar->berat;
		$totHk+=$bar->hkpanenperhari;
		$totUpah+=$bar->upah;
		$totUpahpenalty+=$bar->upahpenalty;
		$totPremi+=$bar->premi;
		$totPremibasis+=$bar->premibasis;
		$totPenalty+=$bar->penalty;
		$totJumlah+=($bar->upah-$bar->upahpenalty+$bar->premi-$bar->penalty);
	}	
        $arr2="tgl1##".$tgl1."##tgl2##".$tgl2."##gudang##".$gudang."";
        echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"zDetailTotal(event,'kebun_slave_2panen.php','".$arr2."');\">
				  <td align=center colspan=6>".$_SESSION['lang']['total']."</td>		 
				  <td align=right>".number_format($totJjg,0)."</td>
                                  <td align=right>".number_format($totberat,2)."</td>
                                  <td align=right>".number_format($totHk,2)."</td>
                                  <td align=right>".number_format($totUpah,2)."</td>
                                  <td align=right>".number_format($totUpahpenalty,2)."</td>
                                  <td align=right>".number_format($totPremi,2)."</td>
                                  <td align=right>".number_format($totPenalty,2)."</td>
                                  <td align=right>".number_format($totJumlah,2)."</td>
                   </tr>
                ";
  }
?>