<link rel=stylesheet type=text/css href='style/generic.css'>
<script language=JavaScript1.2 src=js/menuscript.js></script>
<?
//exit;
require_once('config/connection.php');
@require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid = $_GET['karyawanid'];
$bahasanya = $_GET['bahasa'];
$jabatan = $_GET['jabatan'];
$lokasitugas = $_GET['lokasitugas'];

$tanggal = date('d-m-Y', time());
$hariini = date('Y-m-d', time());
$bulan = date('m', time());
$tahun = date('Y', time());

$updatetime=date('d M Y H:i:s', time());

//                $hariini = '2014-01-20';
//                $bulan = '01';
//                $tahun = '2014';

$dt = strtotime($hariini);
$kemarin = date('Y-m-d', $dt-172800);
$kemarin2 = date('d-m-Y', $dt-172800);

$jumlahnotif=0;
$modulenotif=array();
$modulenotifextras=array();

// load bahasa
$str="SELECT * FROM ".$dbname.".bahasa
    WHERE 1";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    if($bahasanya=='ID')
    {
        $isiBahasa=$bar->ID;
    }
    else if ($bahasanya=='EN')
    {
         $isiBahasa=$bar->EN;
    }
    else if ($bahasanya=='MY')
    {
        $isiBahasa=$bar->MY;
    }
   // if($bahasanya=='ID')$bahasa[$bar->legend]=$bar->ID;
    $bahasa[$bar->legend]=$isiBahasa;
}

// persetujuan PP
//$str="SELECT * FROM ".$dbname.".log_prapoht
  //  WHERE close < 2 and komentar1 != 'diputihkan'";
$str="SELECT * FROM ".$dbname.".log_prapoht
    WHERE close < 2 and (komentar1 != 'diputihkan' or komentar1 is NULL)";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="pp";
    if(
        (($bar->persetujuan1==$karyawanid)and($bar->hasilpersetujuan1=='0'))or
        (($bar->persetujuan2==$karyawanid)and($bar->hasilpersetujuan2=='0'))or
        (($bar->persetujuan3==$karyawanid)and($bar->hasilpersetujuan3=='0'))or
        (($bar->persetujuan4==$karyawanid)and($bar->hasilpersetujuan4=='0'))or
        (($bar->persetujuan5==$karyawanid)and($bar->hasilpersetujuan5=='0'))
    ){
        $jumlahnotif+=1;
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->nopp."; ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['prmntaanPembelian'];
        $modulenotifextras[$tipetransaksi]['file']='log_persetuuanPp';
    }
}


// Verifikasi PP
if(in_array(518,$_SESSION['priv'])) {
    $str="SELECT a.*, c.namabarang FROM ".$dbname.".log_prapodt a
        LEFT JOIN ".$dbname.".log_prapoht b on a.nopp = b.nopp
        LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang
        WHERE b.close = 2 and a.status = 0 and a.create_po = 0 and
        substr(tanggal,1,4)='".$_SESSION['org']['period']['tahun']."' and a.purchaser = 0 and a.ditolakoleh = 0";
    $res=mysql_query($str);
    while($bar=mysql_fetch_object($res))
    {
        $tipetransaksi="verpp";
        $jumlahnotif+=1;
        setIt($modulenotif[$tipetransaksi],$tipetransaksi);
        setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
        setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->namabarang." ".$bar->keterangan.":".tanggalnormal($bar->tgl_sdt,0,10)."; ";
        $modulenotifextras[$tipetransaksi]['title']='Verifikasi PP';
        $modulenotifextras[$tipetransaksi]['file']='log_verifikasiPp';
    }
}


// PO lokal
$str="SELECT a.*, b.namabarang FROM ".$dbname.".log_prapodt a
    LEFT JOIN ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang
    WHERE a.create_po = 0 and a.lokalpusat = 1 and a.purchaser != 0 and a.ditolakoleh = 0 and a.status < 3";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="polokal";
    if($bar->purchaser==$karyawanid){
        $jumlahnotif+=1;
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->namabarang." ".$bar->keterangan.":".tanggalnormal($bar->tgl_sdt,0,10)."; ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['po'].' Lokal';
        $modulenotifextras[$tipetransaksi]['file']='log_POLokal';
    }
}

// PO pusat
$str="SELECT a.*, b.namabarang FROM ".$dbname.".log_prapodt a
    LEFT JOIN ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang
    WHERE a.create_po = 0 and a.lokalpusat = 0 and a.purchaser != 0 and a.ditolakoleh = 0 and a.status < 3";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="popusat";
    if($bar->purchaser==$karyawanid){
		setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
		setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $jumlahnotif+=1;
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->namabarang." ".$bar->keterangan.":".tanggalnormal($bar->tgl_sdt,0,10)."; ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['po'].' Pusat';
        $modulenotifextras[$tipetransaksi]['file']='log_po';
    }
}



##demosi
$str="SELECT a.*, b.namakaryawan FROM ".$dbname.".sdm_riwayatjabatan a
    LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
    WHERE a.posting = 0 ";

$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="promosidemosi";
    if($bar->postingby==$karyawanid)
    {        
        $jumlahnotif+=1;
		setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
		setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->nomorsk." ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['promosidemosi'];
        $modulenotifextras[$tipetransaksi]['file']='sdm_promosi';
    }
}










// persetujuan PJD
$str="SELECT a.*, b.namakaryawan FROM ".$dbname.".sdm_pjdinasht a
    LEFT JOIN ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
    WHERE (a.statuspersetujuan = 0 or a.statushrd = 0)";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="pjd";
    if(
        (($bar->persetujuan==$karyawanid)or($bar->hrd==$karyawanid))
    ){        
        $jumlahnotif+=1;
		setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
		setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->namakaryawan." ".$bar->tujuan1.":".tanggalnormal($bar->tanggalperjalanan,0,10)."sd".tanggalnormal($bar->tanggalkembali,0,10)."; ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['persetujuanpjdinas'];
        $modulenotifextras[$tipetransaksi]['file']='sdm_3persetujuanPJD';
    }
}

// pertanggungjawaban PJD
$str="SELECT * FROM ".$dbname.".sdm_pjdinasht
    WHERE (statuspertanggungjawaban = 0) and tanggalkembali <= '".$hariini."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="pertanggungpjd";
    if(
        (($bar->karyawanid==$karyawanid))
    ){        
        $jumlahnotif+=1;
		setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
		setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$bar->tujuan1.":".tanggalnormal($bar->tanggalperjalanan,0,10)."sd".tanggalnormal($bar->tanggalkembali,0,10)."; ";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['pertanggungjawabandinas'];
        $modulenotifextras[$tipetransaksi]['file']='sdm_pertanggungjawabanPJD';
    }
}    
    
// persetujuan ijin/cuti

$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whkar);

$str="SELECT * FROM ".$dbname.".sdm_ijin
    WHERE (stpersetujuan1 = 0 or stpersetujuanhrd = 0)";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="ijincuti";
    if(
        (($bar->persetujuan1==$karyawanid)and($bar->stpersetujuan1 =='0'))or
        (($bar->hrd==$karyawanid)and($bar->stpersetujuanhrd =='0'))
    )
    {
        $whkar=" karyawanid='".$bar->karyawanid."'";
        
        
		setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
		setIt($modulenotifextras[$tipetransaksi]['note'],'');
        $jumlahnotif+=1;
        $modulenotif[$tipetransaksi]=$tipetransaksi;
        $modulenotifextras[$tipetransaksi]['jumlah']+=1;
        $modulenotifextras[$tipetransaksi]['note'].=$nmKar[$bar->karyawanid]." - ".$bar->jenisijin." : ".tanggalnormal(substr($bar->darijam,0,10))." sd ".tanggalnormal(substr($bar->sampaijam,0,10))."\n";
        $modulenotifextras[$tipetransaksi]['title']=$bahasa['persetujuan']." ".$bahasa['cuti']."/".$bahasa['izinkntor'];
        $modulenotifextras[$tipetransaksi]['file']='sdm_laporan_ijin_keluar_kantor';
        
        //$modulenotifextras[$tipetransaksi]['kar'][$bar->karyawanid]=$bar->karyawanid;
        
    }
}  



// daerah khusus kasie begins
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
if($jabatan=='119'){
$str="select tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where 
      tutupbuku = 0 and kodeorg='".$lokasitugas."' order by periode limit 1";
$res=mysql_query($str);
$tanggal1='';
$tanggal2='';
while($bar=mysql_fetch_object($res))
{
    $tanggal1=$bar->tanggalmulai;
    $tanggal2=$bar->tanggalsampai;
}

// kasbank yang belum diposting
$str="SELECT * FROM ".$dbname.".keu_kasbankht
    WHERE kodeorg like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$tipetransaksi="kasbank";
    $jumlahnotif+=1;
	setIt($modulenotifextras[$tipetransaksi],array());
	setIt($modulenotifextras[$tipetransaksi]['note'],'');
	setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
    $modulenotif[$tipetransaksi]=$tipetransaksi;
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->notransaksi ."; ";
    $modulenotifextras[$tipetransaksi]['title']=$bahasa['posting']." ".$bahasa['kasbank'];
    $modulenotifextras[$tipetransaksi]['file']='keu_kasbank';
}

// transaksi BKM yang belum diposting
$str="SELECT * FROM ".$dbname.".kebun_aktifitas
    WHERE kodeorg like '".$lokasitugas."%' and jurnal = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="bkm".$bar->tipetransaksi;
    $jumlahnotif+=1;
    $modulenotif[$tipetransaksi]=$tipetransaksi;
	setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
	setIt($modulenotifextras[$tipetransaksi]['note'],'');
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->notransaksi."; ";
    if(
        ($bar->tipetransaksi=='TB')
    ){        
        $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." BKM] ".$bahasa['bukalahan'];
        $modulenotifextras[$tipetransaksi]['file']='kebun_bukalahan';
    }
    if(
        ($bar->tipetransaksi=='BBT')
    ){        
        $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." BKM] ".$bahasa['pembibitan'];
        $modulenotifextras[$tipetransaksi]['file']='kebun_pembibitan';
    }
    if(
        ($bar->tipetransaksi=='TBM')
    ){        
        $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." BKM] ".$bahasa['tbm'];
        $modulenotifextras[$tipetransaksi]['file']='kebun_pemeliharaantbm';
    }
    if(
        ($bar->tipetransaksi=='TM')
    ){        
        $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." BKM] ".$bahasa['tm'];
        $modulenotifextras[$tipetransaksi]['file']='kebun_pemeliharaantm';
    }
    if(
        ($bar->tipetransaksi=='PNN')
    ){        
        $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." BKM] ".$bahasa['panen'];
        $modulenotifextras[$tipetransaksi]['file']='kebun_panen';
    }
}      
// SPB yang belum diposting
$str="SELECT * FROM ".$dbname.".kebun_spbht
    WHERE kodeorg like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
	$tipetransaksi="spb";
    $jumlahnotif+=1;
	setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
	setIt($modulenotifextras[$tipetransaksi]['note'],'');
    $modulenotif[$tipetransaksi]=$tipetransaksi;
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->nospb ."; ";
    $modulenotifextras[$tipetransaksi]['title']=$bahasa['posting']." ".$bahasa['suratPengantarBuah'];
    $modulenotifextras[$tipetransaksi]['file']='kebun_3AmbilKgTimbangan';
}
// transaksi Taksasi yang belum diposting
//update ind taksasi gk ada posting
/*$str="SELECT * FROM ".$dbname.".kebun_taksasi
    WHERE afdeling like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
exit("Error:$str");
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="taksasi";
    $jumlahnotif+=1;
    $modulenotif[$tipetransaksi]=$tipetransaksi;
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->blok .":".tanggalnormal($bar->tanggal)."; ";
    $modulenotifextras[$tipetransaksi]['title']=$bahasa['posting']." ".$bahasa['taksasi'];
    $modulenotifextras[$tipetransaksi]['file']='kebun_taksasi';
}*/


// transaksi VHC pekerjaan yang belum diposting
$str="SELECT * FROM ".$dbname.".vhc_runht
    WHERE kodeorg like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="vhcrun";
	setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
	setIt($modulenotifextras[$tipetransaksi]['note'],'');
    $jumlahnotif+=1;
    $modulenotif[$tipetransaksi]=$tipetransaksi;
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->notransaksi ."; ";
    $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." ".$bahasa['traksi']."] ".$bahasa['pekerjaan'];
    $modulenotifextras[$tipetransaksi]['file']='vhc_postingPekerjaan';
}
// transaksi VHC service yang belum diposting
$str="SELECT * FROM ".$dbname.".vhc_penggantianht
    WHERE kodeorg like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
//echo $str;
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tipetransaksi="vhcserv";
    $jumlahnotif+=1;
	setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
	setIt($modulenotifextras[$tipetransaksi]['note'],'');
    $modulenotif[$tipetransaksi]=$tipetransaksi;
    $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    $modulenotifextras[$tipetransaksi]['note'].=$bar->notransaksi ."; ";
    $modulenotifextras[$tipetransaksi]['title']="[".$bahasa['posting']." ".$bahasa['traksi']."] ".$bahasa['service'];
    $modulenotifextras[$tipetransaksi]['file']='vhc_postingPenggunaanKomponen';
}
// lembur yang belum diposting
// $str="SELECT * FROM ".$dbname.".sdm_lemburht
    // WHERE kodeorg like '".$lokasitugas."%' and posting = 0 and tanggal between '".$tanggal1."' and '".$tanggal2."' ";
// //echo $str;
// $res=mysql_query($str);
// while($bar=mysql_fetch_object($res))
// {
    // $tipetransaksi="lembur";
    // $jumlahnotif+=1;
	// setIt($modulenotifextras[$tipetransaksi]['jumlah'],0);
	// setIt($modulenotifextras[$tipetransaksi]['note'],'');
    // $modulenotif[$tipetransaksi]=$tipetransaksi;
    // $modulenotifextras[$tipetransaksi]['jumlah']+=1;
    // $modulenotifextras[$tipetransaksi]['note'].=$bar->kodeorg .":".tanggalnormal($bar->tanggal)."; ";
    // $modulenotifextras[$tipetransaksi]['title']=$bahasa['posting']." ".$bahasa['lembur'];
    // $modulenotifextras[$tipetransaksi]['file']='sdm_lembur';
// }
    
}
// daerah khusus kasie ends

$qwe="You've got <font color=red><b>".number_format($jumlahnotif)."</b></font> notifications";

echo"<table class=sortable cellspacing=1 border=0 width=230px>
    <tr class=rowcontent>
    <td align=right width=1% nowrap><!--".$karyawanid." -->".$qwe."</td>
    </tr>
    </table>";

echo"<table class=sortable cellspacing=1 border=0 width=230px>
    <thead>
    <tr class=rowtitle>
        <td align=center style='width:180px;'>Module</td>
        <td align=center style='width:50px;'>#</td>
    </tr>  
    </thead>
    <tbody></tbody></table>";

echo"<!--marquee height=150 onmouseout=\"this.setAttribute('scrollamount', 1, 0);\" onmouseover=\"this.setAttribute('scrollamount', 0, 0);\" scrolldelay=20 scrollamount=1 behavior=scroll direction=up-->
    <table class=sortable cellspacing=1 border=0 width=230px>
    <tbody>";

//echo"<pre>";
//print_r($modulenotif);
//echo"<pre>";
if(!empty($modulenotif))foreach($modulenotif as $mod){
echo"<tr class=rowcontent>
        <td  align=left style='width:180px;'><a href=\"javascript:parent.do_load('".$modulenotifextras[$mod]['file']."')\" title='".$modulenotifextras[$mod]['note']."'>".$modulenotifextras[$mod]['title']."</a></td>
        <td align=center style='width:50px;'>".$modulenotifextras[$mod]['jumlah']."</td>
    </tr>";      
}

echo"</tbody>
    </table>
    <!--* sumber data: OWL-->
    </marquee>";
?>