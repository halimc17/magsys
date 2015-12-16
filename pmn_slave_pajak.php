<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$method=$_POST['method'];

$invoice=$_POST['invoice'];
$kurs=$_POST['kurs'];
$faktur=$_POST['faktur'];
$jenis=$_POST['jenis'];
$cariPt=$_POST['cariPt'];


$pt=$_POST['pt'];

//exit("Error:$sInsert");	
$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$namaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


//$pt=  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept');
$nokontrak=makeOption($dbname, 'keu_penagihanht', 'noinvoice,nokontrak');

?>

<?php

switch($method)
{
    
    case'getFaktur':
       $iFaktur="select * from ".$dbname.".keu_fakturpajak where pt='".$pt."' "
            . " and status = '0' order by nofaktur limit 1";
       
      
       $nFaktur=  mysql_query($iFaktur) or die (mysql_error($conn));
       while($dFaktur=  mysql_fetch_assoc($nFaktur))
       {
           $optFaktur.="<option value=".$dFaktur['nofaktur'].">".$dFaktur['nofaktur']."</option>";
       }
       echo $optFaktur;
    break;
    
    
    
	case 'insert':
		$ha="insert into ".$dbname.".pmn_faktur (nofaktur,kodept,atasbiaya,kurs,noinvoice,nokontrak,jenis)
		values ('".$faktur."','".$pt."','Harga Jual','".$kurs."',"
                . "'".$invoice."','".$nokontrak[$invoice]."','".$jenis."')";
               
            if(mysql_query($ha))
		{
			$updFaktur = "update ".$dbname.".keu_fakturpajak set status='1' where nofaktur='".$faktur."'";
			mysql_query($updFaktur);
		}
		else
		{
			echo " Gagal,".addslashes(mysql_error($conn));
		}	
	break;
        
       
        

case'loadData':
		echo"<div id=container>
                        <table class=sortable cellspacing=1 border=0>
                         <thead>
                                     <tr class=rowheader>
                                        <td align=center>No</td>
                                        <td align=center>No. Faktur</td>
                                         <td align=center>".$_SESSION['lang']['noinvoice']."</td>    
                                             <td align=center>".$_SESSION['lang']['kontrak']."</td> 
                                                  
                                                 <td align=center style='display:none'>".$_SESSION['lang']['jenis']."</td>  
                                                 <td align=center>".$_SESSION['lang']['kurs']."</td>          
                                        <td align=center>*</td></tr>
                                     </tr>
                            </thead>
                            <tbody>";
		
		
                
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_faktur where kodept like '%".$cariPt."%'";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$iList="select a.*, b.nofaktur from ".$dbname.".pmn_faktur a
				left join ".$dbname.".keu_fakturpajak b
				on a.nofaktur = b.nofaktur 
				where a.kodept like '%".$cariPt."%' 
				limit ".$offset.",".$limit."";
		//$str="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
		$nList=mysql_query($iList) or die(mysql_error());
		$no=$maxdisplay;
		while($dList=mysql_fetch_assoc($nList))
		{
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$dList['nofaktur']."</td>";
                    echo "<td align=left>".$dList['noinvoice']."</td>";
                    echo "<td align=left>".$nokontrak[$dList['noinvoice']]."</td>";
					echo "<td align=right style='display:none'>".$dList['jenis']."</td>";
                    echo "<td align=right>".$dList['kurs']."</td>";
                    echo "<td align=center>
                            <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_faktur','".$dList['nofaktur']."','','pmn_slave_pajak_pdf',event);\">
                           
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['nofaktur']."');\">

                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
		}
                echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
                
		

		//$str="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
		

	case 'delete':
		
		$tab="delete from ".$dbname.".pmn_faktur where nofaktur='".$faktur."'";
		//exit("Error:$tab");
		if(mysql_query($tab))
		{
			$updFaktur = "update ".$dbname.".keu_fakturpajak set status='0' where nofaktur='".$faktur."'";
			mysql_query($updFaktur);
		}
		else
		{
			echo " Gagal,".addslashes(mysql_error($conn));
		}			
	break;
	
	case 'getFaktur':
		// $optFaktur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sFaktur="select * from ".$dbname.".keu_fakturpajak where status = '0' order by nofaktur limit 1";
		$qFaktur = mysql_query($sFaktur) or die ("SQL ERR : ".mysql_error());
		while ($dFaktur=mysql_fetch_assoc($qFaktur))
		{
			$optFaktur.="<option value=".$dFaktur['nofaktur'].">".$dFaktur['nofaktur']."</option>";
		}
		echo $optFaktur;
	break;
	
	
	
	
default:
}
?>