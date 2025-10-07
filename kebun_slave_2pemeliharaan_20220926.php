<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg=checkPostGet('kdOrg','');
$kdAfd=checkPostGet('kdAfd','');
$tgl1_=checkPostGet('tgl1','');
$tgl2_=checkPostGet('tgl2','');
$kegiatan=checkPostGet('kegiatan','');
$sumber=checkPostGet('sumber','');
$kdbarang=checkPostGet('kdbarang','');
$intiplasma = checkPostGet('intiplasma','');

if(($proses=='excel')or($proses=='pdf')){
	$kdOrg=$_GET['kdOrg'];
	$kdAfd=$_GET['kdAfd'];
	$tgl1_=$_GET['tgl1'];
	$tgl2_=$_GET['tgl2'];     
	$kegiatan=$_GET['kegiatan'];
	$sumber=$_GET['sumber'];
        $kdbarang=$_GET['kdbarang'];
}




if($kdbarang=='')
{
    $brg="";
}
else
{
    $brg=" and e.kodebarang='".$kdbarang."' ";
}


if($kdAfd=='')
    $kdAfd=$kdOrg;

$tgl1_=tanggalsystem($tgl1_); $tgl1=substr($tgl1_,0,4).'-'.substr($tgl1_,4,2).'-'.substr($tgl1_,6,2);
$tgl2_=tanggalsystem($tgl2_); $tgl2=substr($tgl2_,0,4).'-'.substr($tgl2_,4,2).'-'.substr($tgl2_,6,2);

if($_SESSION['language']=='EN'){
    $zz='namakegiatan1 as namakegiatan';
}else{
    $zz='namakegiatan';
}    
    $str="select kodekegiatan, ".$zz.", satuan
        from ".$dbname.".setup_kegiatan
        ";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $kamusKeg[$bar->kodekegiatan]=$bar->namakegiatan;
    }

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kdOrg==''){
        if(substr($lksiTgs,2,2)=='HO'){
            
        }
        else{
           // echo"Error: Estate code and afdeling code required."; exit;
        }
    }

    if(($tgl1_=='')or($tgl2_=='')){
            echo"Error: Date required."; exit;
    }

    if($tgl1>$tgl2){
            echo"Error: First date must lower than the second."; exit;
    }
	
}





if ($proses=='excel' or $proses=='preview')
{
    
    if($kdOrg=='')
    {
        exit("Warning:Kebun masih kosong");
    }
    
    
//ambil material
    $str="select a.notransaksi,a.kwantitas,a.kodebarang, b.namabarang,b.satuan from
          ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
          on a.kodebarang=b.kodebarang    
          where  kodeorg like '".$kdAfd."%'  and tanggal between '".$tgl1_."' and '".$tgl2_."' and a.kodekegiatan like '%".$kegiatan."%'";
//    echo $str;
//    exit("error: ".$str);
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $barang[$bar->notransaksi]['kodebarang'][]=$bar->kodebarang;
        $barang[$bar->notransaksi]['namabarang'][]=$bar->namabarang;
        $barang[$bar->notransaksi]['satuan'][]=$bar->satuan;
        $barang[$bar->notransaksi]['jumlah'][]=$bar->kwantitas;
    }
    $border=0;
    if($proses=='excel')$border=1;

        if($sumber=='BKM'){
            if($kdOrg=='') 
            {
                $str1="select a.notransaksi as notransaksi,a.tanggal as tanggal,b.kodeorg as kodeorg,
                  b.kodekegiatan as kodekegiatan,b.hasilkerja as hasilkerja, b.jumlahhk as jumlahhk,
                  IF((select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi) <= 1, sum(c.umr),sum(c.umr)/(select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi)) as upah,
				  IF((select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi) <= 1, sum(c.insentif),sum(c.insentif)/(select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi)) as premi,
				  d.namakegiatan as namakegiatan,
                  d.satuan as satuan,d.kelompok as kelompok,e.kodebarang,g.namaorganisasi
                  from ".$dbname.".kebun_aktifitas a 
                  left join ".$dbname.".kebun_prestasi b on a.notransaksi = b.notransaksi
                  left join ".$dbname.".kebun_kehadiran c on a.notransaksi = c.notransaksi
                  left join ".$dbname.".setup_kegiatan d on b.kodekegiatan = d.kodekegiatan
                  left join ".$dbname.".kebun_pakai_material_vw e  on a.notransaksi = e.notransaksi
				  left join ".$dbname.".setup_blok f on b.kodeorg = f.kodeorg
				    left join ".$dbname.".organisasi g on b.kodeorg = g.kodeorganisasi
                  where b.kodeorg like '".$kdAfd."%' and a.tanggal between '".$tgl1_."' and '".$tgl2_."'
                  and b.kodekegiatan like '%".$kegiatan."%'  and b.kodekegiatan != '0' and a.jurnal=1
                  ".$brg." and f.intiplasma like '%".$intiplasma."%'
                  group by a.notransaksi,d.kodekegiatan,b.kodeorg,a.tanggal
                  order by d.kodekegiatan,b.kodeorg asc";
            }
            else{
                $where='';
                if($kdOrg != $_SESSION['empl']['lokasitugas']){                
                    $where=" and a.jurnal=1";
                }
                
                
                $str1="select a.notransaksi as notransaksi,a.tanggal as tanggal,b.kodeorg as kodeorg,
					b.kodekegiatan as kodekegiatan,b.hasilkerja as hasilkerja, b.jumlahhk as jumlahhk,
					IF((select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi) <= 1, sum(c.umr),sum(c.umr)/(select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi)) as upah,
					IF((select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi) <= 1, sum(c.insentif),sum(c.insentif)/(select count(t1.notransaksi) from ".$dbname.".kebun_pakai_material_vw t1 where t1.notransaksi = a.notransaksi)) as premi,
					d.namakegiatan as namakegiatan,
					d.satuan as satuan,d.kelompok as kelompok,e.kodebarang,g.namaorganisasi
					from ".$dbname.".kebun_aktifitas a 
					left join ".$dbname.".kebun_prestasi b on a.notransaksi = b.notransaksi
					left join ".$dbname.".kebun_kehadiran c on a.notransaksi = c.notransaksi
					left join ".$dbname.".setup_kegiatan d on b.kodekegiatan = d.kodekegiatan
                    left join ".$dbname.".kebun_pakai_material_vw e  on a.notransaksi = e.notransaksi
				    left join ".$dbname.".setup_blok f on b.kodeorg = f.kodeorg
				    left join ".$dbname.".organisasi g on b.kodeorg = g.kodeorganisasi
					where b.kodeorg like '".$kdAfd."%' and a.tanggal between '".$tgl1_."' and '".$tgl2_."'
					and b.kodekegiatan like '%".$kegiatan."%'  and b.kodekegiatan != '0' ".$where."
					  ".$brg." and f.intiplasma like '%".$intiplasma."%'
					group by a.notransaksi,d.kodekegiatan,b.kodeorg,a.tanggal
                                         order by d.kodekegiatan,b.kodeorg asc";
            }
        }
        else if($sumber=='SPK'){
            if($kdOrg=='') 
            {
                $str1="select a.notransaksi as notransaksi,a.tanggal as tanggal,a.kodeblok as kodeorg,
                  a.kodekegiatan as kodekegiatan,a.hasilkerjarealisasi as hasilkerja,a.hkrealisasi as jumlahhk,
                  a.jumlahrealisasi as upah,b.namakegiatan as namakegiatan,
                  b.satuan as satuan,b.kelompok as kelompok,g.namaorganisasi
                  from ".$dbname.".log_baspk a 
                  left join ".$dbname.".setup_kegiatan b on a.kodekegiatan = b.kodekegiatan
		    left join ".$dbname.".setup_blok c on a.kodeblok = c.kodeorg
		    left join ".$dbname.".organisasi g on a.kodeblok = g.kodeorganisasi
                  where a.kodeblok like '".$kdAfd."%' and a.tanggal between '".$tgl1_."' and '".$tgl2_."'
                  and a.kodekegiatan like '%".$kegiatan."%' and a.statusjurnal=1 and c.intiplasma like '%".$intiplasma."%'
                  group by a.notransaksi,a.kodekegiatan,a.kodeblok,a.tanggal
                  order by a.kodekegiatan,a.kodeblok asc";
            }
            else{
                $where='';
                if($kdOrg != $_SESSION['empl']['lokasitugas']){                
                    $where=" and a.statusjurnal=1";
                }
            $str1="select a.notransaksi as notransaksi,a.tanggal as tanggal,a.kodeblok as kodeorg,
                  a.kodekegiatan as kodekegiatan,a.hasilkerjarealisasi as hasilkerja,a.hkrealisasi as jumlahhk,
                  a.jumlahrealisasi as upah,b.namakegiatan as namakegiatan,
                  b.satuan as satuan,b.kelompok as kelompok,g.namaorganisasi
                  from ".$dbname.".log_baspk a 
                  left join ".$dbname.".setup_kegiatan b on a.kodekegiatan = b.kodekegiatan
		    left join ".$dbname.".setup_blok c on a.kodeblok = c.kodeorg
		    left join ".$dbname.".organisasi g on a.kodeblok = g.kodeorganisasi
                  where a.kodeblok like '".$kdAfd."%' and a.tanggal between '".$tgl1_."' and '".$tgl2_."'
                  and a.kodekegiatan like '%".$kegiatan."%' ".$where." and c.intiplasma like '%".$intiplasma."%'
                   and b.kelompok in ('BBT','TM','TB','TBM','PNN')    
                  group by a.notransaksi,a.kodekegiatan,a.kodeblok,a.tanggal
                  order by a.kodekegiatan,a.kodeblok asc";
           
                }
        }
        else{}
//        $str="select * from ".$dbname.".kebun_perawatan_dan_spk_vw where kodeorg like '".$kdAfd."%' 
//             and tanggal between '".$tgl1_."' and '".$tgl2_."' and kodekegiatan like '%".$kegiatan."%'";
//        exit("error: ".$str1);
        $res1=mysql_query($str1);
		// print_r($str1);
	if($proses=='excel'){
		$stream="<table>
					<tr>
						<td colspan=16 style='text-align:left; font-weight:bold'>".$_SESSION['lang']['laporanPemeliharaan']."</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>";
	}else{
		$stream="";
	}	
	$stream.="<table cellspacing='1' border='".$border."' class='sortable'>
	<thead>
	<tr class=rowheader>
        <td align=center>".$_SESSION['lang']['nomor']."</td>
        <td  align=center>".$_SESSION['lang']['notransaksi']."</td>    
	<td  align=center>".$_SESSION['lang']['sumber']."</td>
	<td  align=center>".$_SESSION['lang']['tanggal']."</td>
	<td  align=center>".$_SESSION['lang']['kodeblok']."</td>
	<td  align=center>".$_SESSION['lang']['kodekegiatan']."</td>            
	<td  align=center>".$_SESSION['lang']['kegiatan']."</td>
	<td  align=center>".$_SESSION['lang']['hasilkerjarealisasi']."</td>
	<td  align=center>".$_SESSION['lang']['satuan']."</td>
        <td align=center>".$_SESSION['lang']['jumlahhk']."</td>
	<td  align=center>".$_SESSION['lang']['upahkerja']."</td>
	<td align=center>".$_SESSION['lang']['insentif']."</td>
        <td align=center>".$_SESSION['lang']['kodebarang']."</td> 
        <td align=center>".$_SESSION['lang']['namabarang']."</td>
        <td align=center>".$_SESSION['lang']['jumlah']."</td>  
        <td align=center>".$_SESSION['lang']['satuan']."</td>     
        </tr></thead>
	<tbody>";
        $no=$thk=$tupah=$tpremi=0;
        $oldnotrans='';
        while($bar1=mysql_fetch_object($res1))
        {
            $no+=1;
            $notran=$bar1->notransaksi;
            if($notran!=$oldnotrans and $no!=1)
            {
                if(isset($barang[$oldnotrans]['kodebarang']) and is_array($barang[$oldnotrans]['kodebarang'])){
                foreach($barang[$oldnotrans]['kodebarang'] as $key =>$val){
                $stream.="<tr class=rowcontent>
                <td></td>
                <td>".$oldnotrans."</td>    
                <td>".$sumber."</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>         
                <td align=right></td>                 
                <td></td>
                <td align=right></td>
                <td align=right></td>
                <td align=right></td>
                <td>".$barang[$oldnotrans]['kodebarang'][$key]."</td> 
                <td>".$barang[$oldnotrans]['namabarang'][$key]."</td>
                <td>".$barang[$oldnotrans]['jumlah'][$key]."</td>  
                <td>".$barang[$oldnotrans]['satuan'][$key]."</td>  
                </tr>";  
                }
                }
            }
            if($proses=='excel')$tampiltanggal=$bar1->tanggal; else $tampiltanggal=tanggalnormal($bar1->tanggal);
            $stream.="<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar1->notransaksi."</td>    
            <td>".$sumber."</td>
            <td>".$tampiltanggal."</td>
            <td>".$bar1->namaorganisasi."</td>
            <td>".$bar1->kodekegiatan."</td>
            <td>". $kamusKeg[$bar1->kodekegiatan]."</td>         
            <td align=right>".number_format($bar1->hasilkerja,2)."</td>                 
            <td>".$bar1->satuan."</td>
            <td align=right>".number_format($bar1->jumlahhk,2)."</td>
            <td align=right>".number_format($bar1->upah)."</td>
            <td align=right>".number_format($bar1->premi)."</td>
            <td>-</td> 
            <td>-</td>
            <td>-</td>  
            <td>-</td>                  
            </tr>";
            
            $oldnotrans=$notran;
            $thk+=$bar1->jumlahhk;
            $tupah+=$bar1->upah;
            $tpremi+=$bar1->premi;
        }
                if(isset($barang[$oldnotrans]['kodebarang']) and is_array($barang[$oldnotrans]['kodebarang'])){
                foreach($barang[$oldnotrans]['kodebarang'] as $key =>$val){
                $stream.="<tr class=rowcontent>
                <td></td>
                <td>".$oldnotrans."</td>    
                <td>".$sumber."</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>         
                <td align=right></td>                 
                <td></td>
                <td align=right></td>
                <td align=right></td>
                <td align=right></td>
                <td>".$barang[$oldnotrans]['kodebarang'][$key]."</td> 
                <td>".$barang[$oldnotrans]['namabarang'][$key]."</td>
                <td>".$barang[$oldnotrans]['jumlah'][$key]."</td>  
                <td>".$barang[$oldnotrans]['satuan'][$key]."</td>  
                </tr>";  
                }
                }
   
       $stream.="
	<tr class=rowcontent>
	<td colspan=9>Total</td>
	<td align=right>".number_format($thk)."</td>
	<td align=right>".number_format($tupah)."</td>
	<td align=right>".number_format($tpremi)."</td>
        <td>-</td> 
        <td>-</td>
        <td>-</td>  
        <td>-</td>  
        </tbody></table>";
 
}  
switch($proses)
{
      case 'getAfdAll':
          $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where kodeorganisasi like '".$kdAfd."%' and length(kodeorganisasi)=6 and tipe in ('AFDELING','BIBITAN') order by namaorganisasi
                ";
         
          $op="<option value=''>".$_SESSION['lang']['all']."</option>";
          $res=mysql_query($str);
          while($bar=mysql_fetch_object($res)) 
          {
              $op.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
          }
          echo $op;
          exit();
      break; 
       case'preview':
            echo $stream;    
	break;
        case 'excel':
            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_="Laporan_Perawatan".$kdAfd.$tgl1_."-".$tgl2_."_".date('YmdHis');
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        break;    
}

?>