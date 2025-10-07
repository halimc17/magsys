<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');

    // ambil yang dilempar javascript
    $pt=$_POST['pt'];
    $unit=$_POST['unit'];
    $intiplasma=$_POST['intiplasma'];
    $tgl1=$_POST['tgl1'];
    $tgl2=$_POST['tgl2'];
    
    // kamus luas
        $str2="select kodeorg, 	luasareaproduktif from ".$dbname.".setup_blok
        where kodeorg like '".$unit."%'";
    $qKmrn=mysql_query($str2) or die(mysql_error($conn));
    while($rKmr=mysql_fetch_object($qKmrn))
    {
            $luasblok[$rKmr->kodeorg]=$rKmr->luasareaproduktif;
    }    
    
    // olah tanggal
    $tanggal1=explode('-',$tgl1);
    $tanggal2=explode('-',$tgl2);
    $date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
    $tanggalterakhir=date('t', strtotime($date1));
    
    // urutin tanggal
    $tanggal=Array();
    if($tanggal2[1]>$tanggal1[1]){ // beda bulan
        for ($i = $tanggal1[0]; $i <= $tanggalterakhir; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
        for ($i = 1; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal2[2].'-'.$tanggal2[1].'-'.$ii]=$tanggal2[2].'-'.$tanggal2[1].'-'.$ii;
        }
    }else{ // sama bulan
        for ($i = $tanggal1[0]; $i <= $tanggal2[0]; $i++) {
            if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
            $tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
        }
    }
        
    if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
              sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
              count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi 
              from ".$dbname.".kebun_prestasi_vs_hk a
              left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi 
              left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where c.induk = '".$pt."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%'  
              and a.jurnal=1
              group by a.tanggal,a.kodeorg";
       
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
        $str="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
			  sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
			  count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi  
              from ".$dbname.".kebun_prestasi_vs_hk a
              left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi 
			  left join ".$dbname.".setup_blok b on a.kodeorg = b.kodeorg 
              where unit = '".$unit."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%' 
              ".$where."
              group by a.tanggal, a.kodeorg";
    }
    
  
    
    
$dzArr=array();
        // ni kemarin buat apa ya?
$kmrn=strtotime ('-1 day',strtotime ($date1));
$kmrn=date ('Y-m-d', $kmrn );
if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
        $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
               sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
               count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi
			   from ".$dbname.".kebun_prestasi_vs_hk a 
               left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi 
               left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi
			   left join ".$dbname.".setup_blok b
			   on a.kodeorg = b.kodeorg
               where c.induk = '".$pt."'  and a.tanggal between '".$kmrn."' and '".tanggalsystem($tgl2)."' 
               and a.jurnal=1 and b.intiplasma like '%".$intiplasma."%'
               group by a.tanggal,a.kodeorg";
    }
    else
    {
        $where='';
        if($unit != $_SESSION['empl']['lokasitugas']){                
            $where=" and a.jurnal=1";
        }
        $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
               sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
               count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma,d.namaorganisasi
               from ".$dbname.".kebun_prestasi_vs_hk a
			   left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi 
			   left join ".$dbname.".setup_blok b
			   on a.kodeorg = b.kodeorg
			   where unit = '".$unit."'  and a.tanggal between '".$kmrn."' and '".tanggalsystem($tgl2)."'  and b.intiplasma like '%".$intiplasma."%' 
               ".$where." 
               group by a.tanggal, a.kodeorg";
    }
    // echo $str2;
    $qKmrn=mysql_query($str2) or die(mysql_error($conn));
    while($rKmr=mysql_fetch_object($qKmrn))
    {
            $dzArrk[$rKmr->kodeorg][$rKmr->tanggal.'j']=$rKmr->jjg;
    }

    // isi array
    $jumlahhari=count($tanggal);
    $res=mysql_query($str);
    
    if(mysql_num_rows($res)<1){
        $jukol=6+($jumlahhari*4)+5;
        echo"<tr class=rowcontent><td colspan=".$jukol.">".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }else{
        while($bar=mysql_fetch_object($res)){
            $dzArr[$bar->kodeorg][$bar->tanggal]=$bar->tanggal;
            $dzArr[$bar->kodeorg]['kodeorg']=$bar->kodeorg;
            $dzArr[$bar->kodeorg]['intiplasma']=$bar->intiplasma;
            $dzArr[$bar->kodeorg]['tahuntanam']=$bar->tahuntanam;
            $dzArr[$bar->kodeorg][$bar->tanggal.'j']=$bar->jjg;
            $dzArr[$bar->kodeorg][$bar->tanggal.'k']=$bar->berat;
            $dzArr[$bar->kodeorg][$bar->tanggal.'h']=$bar->hkpanenperhari;
            $dzArr[$bar->kodeorg][$bar->tanggal.'l']=$bar->luas;
            $dzArr[$bar->kodeorg]['namaorg']=$bar->namaorganisasi;
        }	
    } 
    if(!empty($dzArr)) { // list isi data on kodeorg
        foreach($dzArr as $c=>$key) { // list tanggal
            $sort_kodeorg[] = $key['kodeorg'];
            $sort_tahuntanam[] = $key['tahuntanam'];
        }
        array_multisort($sort_kodeorg, SORT_ASC, $sort_tahuntanam, SORT_ASC, $dzArr); // urut kodeorg, terus tahun tanam
    }
        
    // header
    echo"<thead>
        <tr>
            <td rowspan=2 align=center>No.</td>
            <td rowspan=2 align=center>".$_SESSION['lang']['afdeling']."</td>
            <td rowspan=2 align=center>".$_SESSION['lang']['kodeblok']."</td>
            <td rowspan=2 align=center>".$_SESSION['lang']['intiplasma']."</td>
            <td rowspan=2 align=center>".$_SESSION['lang']['luas']."</td>
            <td rowspan=2 align=center>".$_SESSION['lang']['tahuntanam']."</td>";    
    foreach($tanggal as $tang){
        $ting=explode('-',$tang);
        $qwe=date('D', strtotime($tang));
        echo"<td colspan=4 align=center>";
        if($qwe=='Sun')echo"<font color=red>".$ting[2]."</font>"; else echo $ting[2]; 
        echo"</td>";
    }
    echo"<td colspan=4 align=center>Total</td><td rowspan=2 align=center>BJR</td></tr><tr>";  
    foreach($tanggal as $tang){
        echo"<td align=center>".$_SESSION['lang']['jjg']."</td>
            <td align=center>".$_SESSION['lang']['kg']."</td>
            <td align=center>".$_SESSION['lang']['luas']."</td>
            <td align=center>".$_SESSION['lang']['jumlahhk']."</td>";    
    }
    echo"<td align=center>".$_SESSION['lang']['jjg']."</td>
        <td align=center>".$_SESSION['lang']['kg']."</td>
            <td align=center>".$_SESSION['lang']['luas']."</td>
        <td align=center>".$_SESSION['lang']['jumlahhk']."</td></tr>  
        </thead>
	<tbody>";

    // content
    $no=0;
    foreach($dzArr as $arey){ // list isi data on kodeorg
        $no+=1;
        echo"<tr class='rowcontent'>
            <td align=center>".$no."</td>
            <td align=center>".substr($arey['kodeorg'],0,6)."</td>
            <td align=center>".$arey['namaorg']."</td>
            <td align=center>".$arey['intiplasma']."</td>
            <td align=center>".number_format($luasblok[$arey['kodeorg']],2)."</td>
            <td align=center>".$arey['tahuntanam']."</td>";    
        $totalj=0;
        $totalk=0;
        $totalh=0;
        $totall=0;
        $totaltanpanol=0;
        $jumlahtanpanol=0;
        foreach($tanggal as $tang){ // list tanggal
            $dbg="";
            $tglkmrn=strtotime ('-1 day',strtotime ($tang));
            $tglkmrn2=date ('Y-m-d', $tglkmrn );
			setIt($dzArrk[$arey['kodeorg']][$tglkmrn2.'j'],0);
			setIt($arey[$tang.'j'],0);
			setIt($arey[$tang.'k'],0);
			setIt($arey[$tang.'l'],0);
			setIt($arey[$tang.'h'],0);
            if(($dzArrk[$arey['kodeorg']][$tglkmrn2.'j']!=0)&&($arey[$tang.'j']!=0))
            {
                $dbg="bgcolor=red";
                $tittle='title="panen di blok yang sama lebih dari satu hari" ';
            }
            $qwe=date('D', strtotime($tang));
            if($qwe=='Sun'){
                echo"<td align=right ".$dbg." ".$tittle."><font color=red>".number_format($arey[$tang.'j'])."</font></td>";
                echo"<td align=right ><font color=red>".number_format($arey[$tang.'k'],2)."</font></td>";
                echo"<td align=right ><font color=red>".number_format($arey[$tang.'l'],2)."</font></td>";
                echo"<td align=right ><font color=red>".number_format($arey[$tang.'h'])."</font></td>";
            }else{
                echo"<td align=right ".$dbg." ".$tittle.">".number_format($arey[$tang.'j'])."</td>";
                echo"<td align=right >".number_format($arey[$tang.'k'],2)."</td>";
                echo"<td align=right >".number_format($arey[$tang.'l'],2)."</td>";
                echo"<td align=right >".number_format($arey[$tang.'h'])."</td>";
            }
			setIt($total[$tang.'j'],0);
			setIt($total[$tang.'k'],0);
			setIt($total[$tang.'l'],0);
			setIt($total[$tang.'h'],0);
			setIt($totalj,0);
			setIt($totalk,0);
			setIt($totall,0);
			setIt($totalh,0);
			
            $total[$tang.'j']+=$arey[$tang.'j']; // tambahin total bawah
            $total[$tang.'k']+=$arey[$tang.'k']; // tambahin total bawah
            $total[$tang.'h']+=$arey[$tang.'h']; // tambahin total bawah
            $total[$tang.'l']+=$arey[$tang.'l']; // tambahin total bawah
            
            $totalj+=$arey[$tang.'j']; // tambahin total kanan
            $totalk+=$arey[$tang.'k']; // tambahin total kanan
            $totalh+=$arey[$tang.'h']; // tambahin total kanan
            $totall+=$arey[$tang.'l']; // tambahin total kanan
            
            if($arey[$tang.'j']>0){
                $totaltanpanol+=$arey[$tang.'j'];
                $jumlahtanpanol+=1;
            }
        }
        @$rataj=$totaltanpanol/$jumlahtanpanol;
        
        @$bjr=$totalk/$totalj;
        
        echo"<td align=right>".number_format($totalj)."</td>
            <td align=right>".number_format($totalk,2)."</td>
            <td align=right>".number_format($totall,2)."</td>
            <td align=right>".number_format($totalh)."</td><td align=right>".number_format($bjr,2)."</td></tr>";
    }
    
    // tampilin total
    echo"<tr class='rowcontent'>
        <td colspan=6 align=center>Total</td>";
    $totalj=0;
    $totalk=0;
    $totalh=0;
    $totall=0;
    foreach($tanggal as $tang){ // list tanggal
        echo"<td align=right>".number_format($total[$tang.'j'])."</td>";   
        echo"<td align=right>".number_format($total[$tang.'k'])."</td>";    
        echo"<td align=right>".number_format($total[$tang.'l'],2)."</td>";    
        echo"<td align=right>".number_format($total[$tang.'h'])."</td>";    
        $totalj+=$total[$tang.'j']; // tambahin total kanan
        $totalk+=$total[$tang.'k']; // tambahin total kanan
        $totalh+=$total[$tang.'h']; // tambahin total kanan
        $totall+=$total[$tang.'l']; // tambahin total kanan
    }
    echo"<td align=right>".number_format($totalj)."</td>
        <td align=right>".number_format($totalk,2)."</td>
        <td align=right>".number_format($totall,2)."</td>
        <td align=right>".number_format($totalh)."</td><td></td></tr>";
    echo"</tbody>
        <tfoot>
        </tfoot>";		 

?>