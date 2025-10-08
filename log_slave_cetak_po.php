<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    require_once('config/connection.php');
    include_once('lib/zLib.php');
    $method=$_POST['method'];
    switch($method)
    {
            
            
            
            
        case 'list_new_data':
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
        $page=0;
        }
        $offset=$page*$limit;
	$txt_search='';
        $txt_tgl='';
        if(!empty($_POST['txtSearch'])) {
            $txt_search=$_POST['txtSearch'];
            }
            if(!empty($_POST['tglCari'])) {
                    $txt_tgl=tanggalsystem($_POST['tglCari']);
                    $txt_tgl_t=substr($txt_tgl,0,4);
                    $txt_tgl_b=substr($txt_tgl,4,2);
                    $txt_tgl_tg=substr($txt_tgl,6,2);
                    $txt_tglr=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
        }
	$where = "";
        if($txt_search!='') {
                    $where.=" and nopo LIKE  '%".$txt_search."%'";
            }
            if($txt_tgl!='') {
                    $where.=" and tanggal LIKE '%".$txt_tglr."%'";
            }
            
         // print_r($_SESSION['empl']);
        if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
        {
            $where.=" and lokalpusat=1";
            $where.=" and kodeorg='".$_SESSION['empl']['kodeorganisasi']."' ";
        }
      
            
	$strx="SELECT * FROM ".$dbname.".log_poht where nopo!=''  ".$where." order by tanggal desc limit ".$offset.",".$limit."";
	$sql2="SELECT count(*) as jmlhrow FROM ".$dbname.".log_poht where nopo!='' ".$where." order by tanggal desc ";	 
	//echo $strx;		
	
        
        
	$query2=mysql_query($sql2) or die(mysql_error());
	while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
	}
        
        
        
         if($jlhbrs<1)
        {
            echo"data kosong";
        }
        
	
	if($res=mysql_query($strx))
	{
		$no=0;
        while($bar=mysql_fetch_assoc($res)) {
					
					$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$bar['kodeorg']."'"; //echo $spr;
					$rep=mysql_query($spr) or die(mysql_error($conn));
					$bas=mysql_fetch_object($rep);
					$no+=1;
					if($bar['stat_release']==1)
						$st=$_SESSION['lang']['release_po'];
					else
						$st=$_SESSION['lang']['un_release_po'];
					echo"<tr class=rowcontent id='tr_".$no."'>
                                            <td>".$no."</td>
                                            <td id=td_".$no.">".$bar['nopo']."</td>
                                            <td>".tanggalnormal($bar['tanggal'])."</td>
                                            <td>".$bas->namaorganisasi."</td>
                                            <td>".$st."</td>";//<td align=center>".$yrs['namakaryawan']."</td>
                                                  $sql="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['persetujuan1']."'";
                                                  $query=mysql_query($sql) or die(mysql_error());
                                                  $yrs=mysql_fetch_assoc($query);	
                                                  echo"

                                                   <td>
                                                   <button class=mybutton onclick=masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event)>".$_SESSION['lang']['print']."</button>
                                                   
                                                   </td>


                                           ";
                                        echo"</tr>";
				}
                                        echo"
						 <tr><td colspan=9 align=center>
						".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
						<button class=mybutton onclick=cariPage(".($page-1).");>".$_SESSION['lang']['pref']."</button>
						<button class=mybutton onclick=cariPage(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
						</td>
						</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";   
                                 }
                                  else
                                {
                                echo " Gagal,".(mysql_error($conn));
                                 }
                                
                        

		break;
        /*        
        case 'loadData':
		$limit=20;
			$page=0;
			if(isset($_POST['page']))
			{
			$page=$_POST['page'];
			if($page<0)
			$page=0;
			}
			$offset=$page*$limit;
			
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht  ORDER BY nopo DESC";
			$query2=mysql_query($sql2) or die(mysql_error());
			while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
			}
		$str="SELECT * FROM ".$dbname.".log_poht   ORDER BY tanggal DESC limit ".$offset.",".$limit."";

	  if($res=mysql_query($str))
	  {
		$no=0;
		while($bar=mysql_fetch_assoc($res))
		{
			$kodeorg=$bar['kodeorg'];
			$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$kodeorg."' or induk='".$kodeorg."'"; //echo $spr;
			$rep=mysql_query($spr) or die(mysql_error($conn));
			$bas=mysql_fetch_object($rep);
			$no+=1;
			if($bar['stat_release']==1)
				$st=$_SESSION['lang']['release_po'];
			else
				$st=$_SESSION['lang']['un_release_po'];
			echo"<tr class=rowcontent id='tr_".$no."'>
				  <td>".$no."</td>
				  <td id=td_".$no.">".$bar['nopo']."</td>
				  <td>".tanggalnormal($bar['tanggal'])."</td>
				  <td>".$bas->namaorganisasi."</td>
				  <td>".$st."</td>";                            
					$sql="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['persetujuan1']."'";
					$query=mysql_query($sql) or die(mysql_error());
					$yrs=mysql_fetch_assoc($query);	
					echo"<td align=center>".$yrs['namakaryawan']."</td>";
					  ?>
					 <td>			
					 <button class=mybutton onclick="masterPDF('log_poht','<?php  echo $bar['nopo']?>','','log_slave_print_log_po',event);" ><?php echo $_SESSION['lang']['print'] ?>
					 </button>
					 </td>
	
				 <?php
				
				 echo"</tr>";
		}	 	 	echo"
				 <tr><td colspan=8 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";   	
	  }	
	  else
		{
			echo " Gagal,".(mysql_error($conn));
		}	
        break;*/

	default:
	break;
	}
?>