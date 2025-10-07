<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$method=checkPostGet('method','');


$pabrik=checkPostGet('pabrik','');
$tangki=checkPostGet('tangki','');
$barang=checkPostGet('barang','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$jm=checkPostGet('jm','');
$mn=checkPostGet('mn','');
$jumlah=checkPostGet('jumlah','');
$ket=checkPostGet('ket','');


$brgSch=checkPostGet('brgSch','');
$tglSch=tanggalsystemn(checkPostGet('tglSch',''));


$tanggal=$tgl.' '.$jm.':'.$mn;


//exit("Error:$sInsert");	
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang','kelompokbarang=400');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');

if($tglSch=='--')
{
    $tglSch='';
}

?>

<?php

switch($method)
{//'".$_SESSION['standard']['userid']."'
    
   case'getTangki':
        $optTangki.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $iTangki="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and komoditi in ('CPO','KER') ";
        $nTangki=  mysql_query($iTangki) or die (mysql_error($conn));
        while($dTangki=  mysql_fetch_assoc($nTangki))
        {
            $optTangki.="<option value=".$dTangki['kodetangki'].">".$dTangki['keterangan']."</option>";
        }
        echo $optTangki;
    break;
    
    case'getBarang':
        $arrKdBrg=array('CPO'=>'40000001','KER'=>'40000002');
        $arrNmBrg=array('CPO'=>'CRUDE PALM OIL (CPO)','KER'=>'PALM KERNEL (PK)');
        $iBarang="select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and kodetangki='".$tangki."'";
        $nBarang=  mysql_query($iBarang) or die (mysql_error($conn));
        while($dBarang=  mysql_fetch_assoc($nBarang))
        {
            $optBrg.="<option value=".$arrKdBrg[$dBarang['komoditi']].">".$arrNmBrg[$dBarang['komoditi']]."</option>";
        }
        echo $optBrg;
    break;
    
    case 'insert':
        
        $iSave="INSERT INTO ".$dbname.".`pabrik_pembersihantangki` (`kodeorg`, `kodetangki`, `kodebarang`, `tanggal`, 
                `jumlah`, `keterangan`, `updateby`)
        values ('".$pabrik."','".$tangki."','".$barang."','".$tanggal."','".$jumlah."','".$ket."','".$_SESSION['standard']['userid']."')";
       
        if(mysql_query($iSave))
            
        {}
        else
        {echo " Gagal,".addslashes(mysql_error($conn));}	
    break;

    case 'update':
        $iUpdate="update ".$dbname.".pabrik_pembersihantangki set jumlah='".$jumlah."'"
            . ",updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."' "
            . " where kodeorg='".$pabrik."' and kodetangki='".$tangki."' and kodebarang='".$barang."' "
            . " and tanggal='".$tanggal."'";
        //exit("Error:$iUpdate");
        if(mysql_query($iUpdate))
        {}
        else
        {echo " Gagal,".addslashes(mysql_error($conn));}	
    break;
    
    case 'delete':
        $iDelete="delete from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$pabrik."' and kodetangki='".$tangki."' "
            . " and kodebarang='".$barang."' and tanggal='".$tanggal."'  ";
        if(mysql_query($iDelete))
        {}
        else
        {echo " Gagal,".addslashes(mysql_error($conn));}			
    break;
        

    case'loadData':
            echo"<div id=container>
                    <table class=sortable cellspacing=1 border=0>
                     <thead>
                                 <tr class=rowheader>
                                    <td align=center>No</td>
                                    <td align=center>".$_SESSION['lang']['pabrik']."</td>
                                    <td align=center>".$_SESSION['lang']['tangki']."</td> 
                                    <td align=center>".$_SESSION['lang']['namabarang']."</td>    
                                    <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                    
                                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                                    <td align=center>".$_SESSION['lang']['keterangan']."</td>
                                    <td align=center>*</td></tr>
                                 </tr>
                        </thead>
                        <tbody>";

                        $tmbh="";
            if($brgSch!='')
            {
                $tmbh.=" and kodebarang='".$brgSch."' ";
            }
            if($tglSch!='')
            {
                $tmbh.=" and tanggal like '%".$tglSch."%' ";
            }

            $limit=10;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$tmbh."  ";// echo $ql2;notran
            $query2=mysql_query($ql2) or die(mysql_error());
            while($jsl=mysql_fetch_object($query2)){
            $jlhbrs= $jsl->jmlhrow;
            }
            $iList="select * from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$tmbh." limit ".$offset.",".$limit."";
            //echo $iList;
            //$str="select * from ".$dbname.".pabrik_pembersihantangki ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
            $nList=mysql_query($iList) or die(mysql_error());
            $no=$maxdisplay;
            while($dList=mysql_fetch_assoc($nList))
            {
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
                echo "<td align=left>".$nmOrg[$dList['kodeorg']]."</td>";
                echo "<td align=left>".$nmTangki[$dList['kodetangki']]."</td>";
                
                echo "<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
                echo "<td align=left>".tanggalnormal($dList['tanggal'])." ".substr($dList['tanggal'],11,8)."</td>";
                echo "<td align=right>".number_format($dList['jumlah'])."</td>";
                echo "<td align=left>".$dList['keterangan']."</td>";
                echo "<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$dList['kodeorg']."','".$dList['kodetangki']."',
                        '".$nmTangki[$dList['kodetangki']]."','".$dList['kodebarang']."','".$nmBrg[$dList['kodebarang']]."',
                        '".tanggalnormal(substr($dList['tanggal'],0,10))."','".substr($dList['tanggal'],11,2)."',
                        '".substr($dList['tanggal'],14,2)."','".$dList['jumlah']."','".$dList['keterangan']."');\">
                        <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                        onclick=\"del('".$dList['kodeorg']."','".$dList['kodetangki']."','".$dList['kodebarang']."',
                        '".tanggalnormal(substr($dList['tanggal'],0,10))."','".substr($dList['tanggal'],11,2)."',
                        '".substr($dList['tanggal'],14,2)."','".$dList['kodebarang']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;        

    
	
    case 'getperiodesort':
    //exit("Error:MASUK");
            $optpersort="<option value=''>".$_SESSION['lang']['all']."</option>";
            $aper = "SELECT distinct substr(tanggal,1,7) as tanggal FROM ".$dbname.".pabrik_pembersihantangki where substr(tanggal,1,7) order by tanggal desc";
            //exit ("Error:$asup");
            $bper=mysql_query($aper) or die(mysql_error($conn));
            while($cper=mysql_fetch_assoc($bper))
            {
                    $optpersort.="<option value='".$cper['tanggal']."'>".$cper['tanggal']."</option>";
            }
            echo $optpersort;
    break;

    case 'getsuppsort':
                    //exit("Error:xx");
            $optsupsort="<option value=''>".$_SESSION['lang']['all']."</option>";
            $asup = "SELECT distinct kodesupplier FROM ".$dbname.".pabrik_pembersihantangki ";
            //exit ("Error:$asup");
            $bsup=mysql_query($asup) or die(mysql_error($conn));
            while($csup=mysql_fetch_assoc($bsup))
            {
                    $optsupsort.="<option value='".$csup['kodesupplier']."'>".$namasupp[$csup['kodesupplier']]."</option>";
            }
            echo $optsupsort;//exit();
            //exit ("Error:$optsupsort");
    break;

    case 'getorgsort':
                    //exit("Error:xx");
            $optorgsort="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $aorg = "SELECT distinct kodeorg FROM ".$dbname.".pabrik_pembersihantangki ";
            //exit ("Error:$aorg");
            $borg=mysql_query($aorg) or die(mysql_error($conn));
            while($corg=mysql_fetch_assoc($borg))
            {
                    $optorgsort.="<option value='".$corg['kodeorg']."'>".$namaorg[$corg['kodeorg']]."</option>";
            }
            echo $optorgsort;//exit();
            //exit ("Error:$optsupsort");
    break;
	
	
default:
}
?>