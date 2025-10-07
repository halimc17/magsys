<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$periode=checkPostGet('periode','');
$kodeorg=checkPostGet('kodeorg','');
$rs=checkPostGet('rs','');
$kary=checkPostGet('kary','');
$method=checkPostGet('method','');
$optJabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$hariini = date("Y-m-d");
$tahunini = date("Y");

function getAge($tdate,$dob)
{
        $age = 0;
        while( $tdate > $dob = strtotime('+1 year', $dob))
        {
                ++$age;
        }
        return $age;
}   

if($method==1){   
    $str="select a.*, b.*,g.tipe as tipekaryawan,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan, nama,
        c.jeniskelamin as sex,c.tanggalmasuk as masuk, c.tanggalkeluar as keluar, c.tanggallahir as lahir, c.subbagian as subbag,
        a.jasars as byrs,a.jasadr as bydr,a.jasalab as bylab,a.byobat as byobat,a.bypendaftaran as byadmin
        from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".sdm_5rs b on a.rs=b.id 
        left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
        left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
        left join ".$dbname.".sdm_karyawankeluarga f on a.ygsakit=f.nomor
        left join ".$dbname.".sdm_5tipekaryawan g on c.tipekaryawan=g.id
        where a.periode like '".$periode."%'
        and b.namars like '".$rs."%' and a.karyawanid like '".$kary."%' and c.lokasitugas like '".$kodeorg."%'
        order by a.updatetime desc, a.tanggal desc";

    // and c.lokasitugas like '".$kodeorg."%' and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
//   exit("error: ".$str);
    $res=mysql_query($str);
    $no=0;
   
    while($bar=mysql_fetch_object($res))
    {
        $no+=1;
        
        $masakerja=getAge(strtotime($hariini),strtotime($bar->masuk));
        $usia=getAge(strtotime($tahunini),strtotime($bar->lahir))+1;
        
        $pasien='';
        //get hubungan keluarga
        $stru="select hubungankeluarga from ".$dbname.".sdm_karyawankeluarga 
              where nomor=".$bar->ygsakit;
        $resu=mysql_query($stru);
        while($baru=mysql_fetch_object($resu))
        {
            $pasien=$baru->hubungankeluarga;
        }
        if($pasien=='')$pasien='AsIs';	

        echo"<tr class=rowcontent>
            <td>&nbsp <img src=images/zoom.png title='view' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)></td>
            <td>".$no."</td>
            <td>".$bar->notransaksi."</td>
            <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>";
        if($bar->subbag=='')
            echo"<td>".$bar->loktug."</td>";
        else
            echo"<td>".$bar->subbag."</td>";
        
        echo"<td>".$bar->namakaryawan."</td>
            <td align=center>".$bar->sex."</td>
            <td align=right>".$usia."</td>
            <td>".tanggalnormal($bar->masuk)."</td>";
        if($bar->keluar=='0000-00-00')
            echo"<td></td>";
        else
            echo"<td>".$bar->keluar."</td>";       
        echo"<td align=right>".$masakerja."</td>
            <td>".$optJabatan[$bar->kodejabatan]."</td>
            <td>".$pasien."</td>
            <td>".$bar->nama."</td>
            <td>".$bar->namars."[".$bar->kota."]"."</td>
            <td>".$bar->kodebiaya."</td>
            <td align=right>".number_format($bar->byrs,2,'.',',')."</td>
            <td align=right>".number_format($bar->byadmin,2,'.',',')."</td>
            <td align=right>".number_format($bar->bylab,2,'.',',')."</td>
            <td align=right>".number_format($bar->byobat,2,'.',',')."</td>
            <td align=right>".number_format($bar->bydr,2,'.',',')."</td>    
            <td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>
            <td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
            <td align=right>".number_format($bar->bebanperusahaan,2,'.',',')."</td>
            <td align=right>".number_format($bar->bebankaryawan,2,'.',',')."</td>
            <td align=right>".number_format($bar->bebanjamsostek,2,'.',',')."</td>     
            <td>".$bar->ketdiag."</td>
            <td>".$bar->keterangan."</td>
        </tr>";	  	
    }    
}

if($method==2){
    $str1="select a.diagnosa, count(*) as kali,d.diagnosa as ketdiag from ".$dbname.".sdm_pengobatanht a 
              left join ".$dbname.".sdm_5diagnosa d
              on a.diagnosa=d.id 
        left join ".$dbname.".datakaryawan c
        on a.karyawanid=c.karyawanid
              
              where a.periode like '".$periode."%'
              and c.lokasitugas like '".$kodeorg."%'
            group by a.diagnosa order by kali desc
        ";
    $res1=mysql_query($str1);    
    $no=0;
//    echo $str1;
    while($bar1=mysql_fetch_object($res1))
    {
        $no+=1;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar1->ketdiag."</td>
            <td align=right>".$bar1->kali."</td>
        </tr>";	  	
//            <td>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan1('".$bar->notransaksi."',event)></td>
    }
}

if($method==3){
    $str2="select a.karyawanid, sum(jlhbayar) as klaim,d.namakaryawan,d.lokasitugas,
             COALESCE(ROUND(DATEDIFF('".date('Y-m-d')."',d.tanggallahir)/365.25,1),0) as umur 
             from ".$dbname.".sdm_pengobatanht a 
             left join ".$dbname.".datakaryawan d
             on a.karyawanid=d.karyawanid 
              where a.periode like '".$periode."%'
              and d.lokasitugas like '".$kodeorg."%'
        group by a.karyawanid order by klaim desc
    ";
    $res2=mysql_query($str2);    
    $no=0;
     $total=0;
    while($bar2=mysql_fetch_object($res2))
    {
        $no+=1;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar2->namakaryawan."</td>
            <td>".$bar2->umur."(Th)</td>    
            <td>".$bar2->lokasitugas."</td>
            <td align=right>".number_format($bar2->klaim)."</td>	  	
             <td>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPerorang('".$bar2->karyawanid."',event)></td>
            </tr>";
        $total+=$bar2->klaim; 
    }
    echo"<tr class=rowcontent>
              <td></td>
               <td>".$_SESSION['lang']['total']."</td>
               <td></td>
               <td></td>
               <td>".  number_format($total)."</td>
                <td></td></tr>";
}

if($method==4){
$str3="select a.diagnosa, sum(jlhbayar) as klaim,d.diagnosa as ketdiag from ".$dbname.".sdm_pengobatanht a 
	  left join ".$dbname.".sdm_5diagnosa d
	  on a.diagnosa=d.id 
        left join ".$dbname.".datakaryawan c
        on a.karyawanid=c.karyawanid
              where a.periode like '".$periode."%'
              and c.lokasitugas like '".$kodeorg."%'
        group by a.diagnosa order by klaim desc
    ";
    $res3=mysql_query($str3);    
    $no=0;
//    echo $str1;
    while($bar3=mysql_fetch_object($res3))
    {
        $no+=1;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar3->ketdiag."</td>
            <td align=right>".number_format($bar3->klaim)."</td>
        </tr>";	  	
//            <td>&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan1('".$bar->notransaksi."',event)></td>
    }
}
if($method==5){
$str3="select  sum(a.jlhbayar) as klaim,a.periode from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".datakaryawan c
        on a.karyawanid=c.karyawanid
              where a.periode like '".$periode."%'
              and c.lokasitugas like '".$kodeorg."%'
        group by periode order by periode
    ";
    $res3=mysql_query($str3);    
    $no=0;
//    echo $str1;
    while($bar3=mysql_fetch_object($res3))
    {
        $no+=1;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar3->periode."</td>
            <td align=right>".number_format($bar3->klaim)."</td>
        </tr>";	  	
    }
}

if($method==6){
    if($_POST['karyawanid']==''){
    $str3="select  sum(jasars) as rs, 
               sum(jasadr) as dr, sum(jasalab) as lab, 
               sum(byobat) as obat, 
               sum(bypendaftaran) administrasi, 
               a.periode, sum(a.totalklaim) as klaim,sum(a.jlhbayar) as bayar from ".$dbname.".sdm_pengobatanht a 
               left join ".$dbname.".datakaryawan c
               on a.karyawanid=c.karyawanid
              where a.periode like '".$periode."%'
             group by periode order by periode";
}
else
        {
    $str3="select  sum(jasars) as rs, 
               sum(jasadr) as dr, sum(jasalab) as lab, 
               sum(byobat) as obat, 
               sum(bypendaftaran) administrasi, 
               a.periode, sum(a.totalklaim) as klaim,sum(a.jlhbayar) as bayar from ".$dbname.".sdm_pengobatanht a 
               left join ".$dbname.".datakaryawan c
               on a.karyawanid=c.karyawanid
              where a.periode like '".$periode."%'
               and c.karyawanid=".$_POST['karyawanid']."
             group by periode order by periode";    
        }
        
    $res3=mysql_query($str3);    
    $no=0;
   $trs=0;
   $tdr=0;
   $tlb=0;
   $tob=0;
   $tad=0;
   $ttl=$byr=0;
    while($bar3=mysql_fetch_object($res3))
    {
        $no+=1;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar3->periode."</td>
            <td align=right>".number_format($bar3->rs)."</td>
            <td align=right>".number_format($bar3->dr)."</td>
            <td align=right>".number_format($bar3->lab)."</td>
            <td align=right>".number_format($bar3->obat)."</td>
            <td align=right>".number_format($bar3->administrasi)."</td>
            <td align=right>".number_format($bar3->klaim)."</td>
            <td align=right>".number_format($bar3->bayar)."</td>    
        </tr>";	  
         $trs+=$bar3->rs;
         $tdr+=$bar3->dr;
         $tlb+=$bar3->lab;
         $tob+=$bar3->obat;
         $tad+=$bar3->administrasi;
         $ttl+=$bar3->klaim; 
          $byr+=$bar3->bayar; 
    }
        echo"<tr class=rowcontent>
            <td></td>
            <td>".$_SESSION['lang']['total']."</td>
            <td align=right>".number_format($trs)."</td>
            <td align=right>".number_format($tdr)."</td>
            <td align=right>".number_format($tlb)."</td>
            <td align=right>".number_format($tob)."</td>
            <td align=right>".number_format($tad)."</td>
            <td align=right>".number_format($ttl)."</td>
             <td align=right>".number_format($byr)."</td>   
        </tr>";    
}
if($method==8){   
    $str="select a.*, b.*,g.tipe as tipekaryawan,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan, nama,
        c.jeniskelamin as sex,c.tanggalmasuk as masuk, c.tanggalkeluar as keluar, c.tanggallahir as lahir, c.subbagian as subbag,
        a.jasars as byrs,a.jasadr as bydr,a.jasalab as bylab,a.byobat as byobat,a.bypendaftaran as byadmin,a.tanggalbayar as tglbayar
        from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".sdm_5rs b on a.rs=b.id 
        left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
        left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
        left join ".$dbname.".sdm_karyawankeluarga f on a.ygsakit=f.nomor
        left join ".$dbname.".sdm_5tipekaryawan g on c.tipekaryawan=g.id
        where a.periode like '".$periode."%' and a.karyawanid like '".$kary."%'
        order by a.updatetime desc, a.tanggal desc";
	//if(empty($kary)) $str .= " limit 0,100";
    // and c.lokasitugas like '".$kodeorg."%' and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
//   exit("error: ".$str);
    $res=mysql_query($str);
    $no=0;
   
    while($bar=mysql_fetch_object($res))
    {
        $no+=1;
        
        $masakerja=getAge(strtotime($hariini),strtotime($bar->masuk));
        $usia=getAge(strtotime($tahunini),strtotime($bar->lahir))+1;
        
        $pasien='';
        //get hubungan keluarga
        $stru="select hubungankeluarga from ".$dbname.".sdm_karyawankeluarga 
              where nomor=".$bar->ygsakit;
        $resu=mysql_query($stru);
        while($baru=mysql_fetch_object($resu))
        {
            $pasien=$baru->hubungankeluarga;
        }
        if($pasien=='')$pasien='AsIs';	

        echo"<tr class=rowcontent>
            <td align=center>".$no."</td>
            <td>".$bar->notransaksi."</td>
            <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>";
//        <td>&nbsp <img src=images/zoom.png title='view' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)></td>
        if($bar->subbag=='')
            echo"<td>".$bar->loktug."</td>";
        else
            echo"<td>".$bar->subbag."</td>";
        
        echo"<td align=center>".$bar->tipekaryawan."</td>
             <td>".$bar->namakaryawan."</td>";
            
        echo"<td>".$pasien."</td>
            <td>".$bar->nama."</td>
            <td>".$bar->namars."[".$bar->kota."]"."</td>
            <td>".$bar->kodebiaya."</td>
            <td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>
            <td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
            <td>".$bar->tglbayar."</td>
        </tr>";
    } 
        $s_tot="select sum(a.totalklaim) as totklaim,sum(a.jlhbayar) as totdibayar,b.kode as kodebiaya
                from ".$dbname.".sdm_pengobatanht a 
                left join ".$dbname.".sdm_5jenisbiayapengobatan b on a.kodebiaya=b.kode
                where a.periode like '".$periode."%' and a.karyawanid like '".$kary."%'
                group by a.kodebiaya";
        $r_tot=mysql_query($s_tot);
		$grantot_klaim=$grantot_dibayar=0;
        while($bar=mysql_fetch_object($r_tot))
        {
           echo"<tr class=rowcontent>
            <td align=right colspan=11>".$bar->kodebiaya."</td>
            <td align=right>".number_format($bar->totklaim,2,'.',',')."</td>
            <td align=right>".number_format($bar->totdibayar,2,'.',',')."</td>
            <td></td></tr>";
           
           $grantot_klaim +=$bar->totklaim;
           $grantot_dibayar +=$bar->totdibayar;
        }
        
        echo"<tr class=rowcontent>
            <td align=right colspan=11>TOTAL KLAIM</td>
            <td align=right>".number_format($grantot_klaim,2,'.',',')."</td>
            <td align=right>".number_format($grantot_dibayar,2,'.',',')."</td>
            <td></td></tr>";
}
if($method==7){
$str3="select  sum(a.jlhbayar) as klaim,a.periode,a.kodebiaya,c.nama from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".sdm_5jenisbiayapengobatan c
        on a.kodebiaya=c.kode
        left join ".$dbname.".datakaryawan b 
        on a.karyawanid=b.karyawanid
              where a.periode like '".$periode."%'
              and b.lokasitugas like '".$kodeorg."%'
        group by kodebiaya,periode order by periode
    ";
    $res3=mysql_query($str3);    
    $no=0;
    while($bar3=mysql_fetch_object($res3))
    {
        $kode[$bar3->kodebiaya][$bar3->periode]=$bar3->klaim;
        $kodex[$bar3->kodebiaya]['nama']=$bar3->nama;
    }
	setIt($kodex,array());
	$t01=$t02=$t03=
	$t04=$t05=$t06=
	$t07=$t08=$t09=
	$t10=$t11=$t12=$gt=0;
    if(count($kodex)>0){
    foreach($kodex as $key=>$val){
        $no+=1;
		setIt($kode[$key][$periode."-12"],0);
		setIt($kode[$key][$periode."-11"],0);
		setIt($kode[$key][$periode."-10"],0);
		setIt($kode[$key][$periode."-09"],0);
		setIt($kode[$key][$periode."-08"],0);
		setIt($kode[$key][$periode."-07"],0);
		setIt($kode[$key][$periode."-06"],0);
		setIt($kode[$key][$periode."-05"],0);
		setIt($kode[$key][$periode."-04"],0);
		setIt($kode[$key][$periode."-03"],0);
		setIt($kode[$key][$periode."-02"],0);
		setIt($kode[$key][$periode."-01"],0);
        $total=$kode[$key][$periode."-12"]+$kode[$key][$periode."-11"]+$kode[$key][$periode."-10"]+$kode[$key][$periode."-09"]+$kode[$key][$periode."-08"]+$kode[$key][$periode."-07"]+$kode[$key][$periode."-06"]+$kode[$key][$periode."-05"]+$kode[$key][$periode."-04"]+$kode[$key][$periode."-03"]+$kode[$key][$periode."-02"]+$kode[$key][$periode."-01"];
        $gt+=$total;
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$kodeorg."</td>
            <td>".$periode."</td>    
            <td>".$kodex[$key]['nama']."</td>                
            <td align=right>".number_format($kode[$key][$periode."-01"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-02"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-03"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-04"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-05"])."</td> 
            <td align=right>".number_format($kode[$key][$periode."-06"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-07"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-08"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-09"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-10"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-11"])."</td>
            <td align=right>".number_format($kode[$key][$periode."-12"])."</td>
            <td align=right>".number_format($total)."</td>    
        </tr>";
	    $t01+=$kode[$key][$periode."-01"];
        $t02+=$kode[$key][$periode."-02"];
        $t03+=$kode[$key][$periode."-03"];
        $t04+=$kode[$key][$periode."-04"];
        $t05+=$kode[$key][$periode."-05"];
        $t06+=$kode[$key][$periode."-06"];
        $t07+=$kode[$key][$periode."-07"];
        $t08+=$kode[$key][$periode."-08"];
        $t09+=$kode[$key][$periode."-09"];
        $t10+=$kode[$key][$periode."-10"];
        $t11+=$kode[$key][$periode."-11"];
        $t12+=$kode[$key][$periode."-12"];
    }
    }
        echo"<tr class=rowcontent>
            <td colspan=4>Total</td>                
            <td align=right>".number_format($t01)."</td>
            <td align=right>".number_format($t02)."</td>
            <td align=right>".number_format($t03)."</td>
             <td align=right>".number_format($t04)."</td>
             <td align=right>".number_format($t05)."</td>
             <td align=right>".number_format($t06)."</td>
             <td align=right>".number_format($t07)."</td>
             <td align=right>".number_format($t08)."</td>
             <td align=right>".number_format($t09)."</td>
             <td align=right>".number_format($t10)."</td>
             <td align=right>".number_format($t11)."</td>
             <td align=right>".number_format($t12)."</td>     
            <td align=right>".number_format($gt)."</td>    
        </tr>";   
}
?>
