<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    case 'add':
		$tglnow=date('Y-m-d');
        $tglinput=tanggalsystemn($data['tanggal']);
        
        //exit("Error:$tglnow._.$tglinput");
        
        if($tglinput>$tglnow)
        {
            exit("Error:Tanggal Tidak Boleh melewati tanggal sekarang ".tanggalnormal($tglnow)." ");
        }
        
		// Validasi Kurs
		if($data['matauang']!='IDR') {
			$qKurs = selectQuery($dbname,'setup_matauangrate','kurs',
								 "kode='".$data['matauang']."' and daritanggal='".
								 tanggalsystem($data['tanggal'])."'");
			$resKurs = fetchData($qKurs);
			if(empty($resKurs)) exit("Warning: Kurs ".$data['matauang']." di tanggal ".
									 $data['tanggal']." belum ada");
		}
		
        #=============== Get Nomor Jurnal
        $whereNo = "kodekelompok='".$data['kodejurnal']."' and kodeorg='".
            $_SESSION['org']['kodeorganisasi']."'";
        $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            $whereNo);
        $noKon = fetchData($query);
        $tmpC = $noKon[0]['nokounter'];
        $tmpC++;
        $counter = addZero($tmpC,3);
        $data['nojurnal'] = tanggalsystem($data['tanggal'])."/".
            $_SESSION['empl']['lokasitugas']."/".$data['kodejurnal']."/".
            $counter;
        $nojur = $data['nojurnal'];

        #mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
           $sekarang=  tanggalsystem($data['tanggal']);
           if($sekarang<$_SESSION['org']['period']['start']){
           echo "Validation Error : Date out or range";
           break;                        
           }
         #======================================================

        #=============== Insert Process
        # Column
        $column = array('kodejurnal','tanggal','noreferensi','matauang',
            'nojurnal','tanggalentry','posting','totaldebet','totalkredit',
            'amountkoreksi','autojurnal','kurs');

        # Add Default Data
        $data['tanggal'] = tanggalsystem($data['tanggal']);
        $data['tanggalentry'] = date('Ymd');
        $data['posting'] = 0;
        $data['totaldebet'] = 0;
        $data['totalkredit'] = 0;
        $data['amountkoreksi'] = 0;
        $data['autojurnal'] = 0;
        $data['kurs'] = 0;

        # Query
        $query = insertQuery($dbname,'keu_jurnalht',$data,$column);
        if(!mysql_query($query)) {
            echo "DB Error : ".mysql_error();
        } else {
            $updData = array('nokounter'=>$tmpC);
            $query2 = updateQuery($dbname,'keu_5kelompokjurnal',$updData,$whereNo);
            if(!mysql_query($query2)) {
                echo "DB Error : ".mysql_error();
            } else {
                echo $nojur;
            }
        }
        break;
    case 'edit':
        $data = $_POST;
        unset($data['nojurnal']);
        $data['tanggal'] = tanggalsystem($data['tanggal']);
		
		// Validasi Kurs
		if($data['matauang']!='IDR') {
			$qKurs = selectQuery($dbname,'setup_matauangrate','kurs',
								 "kode='".$data['matauang']."' and daritanggal='".
								 tanggalsystem($data['tanggal'])."'");
			$resKurs = fetchData($qKurs);
			if(empty($resKurs)) exit("Warning: Kurs ".$data['matauang']." di tanggal ".
									 $data['tanggal']." belum ada");
		}
		
        $query = updateQuery($dbname,'keu_jurnalht',$data,"nojurnal='".$_POST['nojurnal']."'");
        if(!mysql_query($query)) {
            echo "DB Error : ".mysql_error();
        } else {
			// tambahan dhyaz, kalo update tanggal header, update detail juga
			$query="UPDATE `".$dbname."`.`keu_jurnaldt` SET `tanggal` = '".$data['tanggal']."' 
					WHERE `nojurnal` = '".$_POST['nojurnal']."'";
			if(!mysql_query($query)) {
				echo "DB Error : Please print screen and report to IT. ".mysql_error();
			}
			// end of tambahan dhyaz            
            
            $data['tanggal'] = tanggalnormal($data['tanggal']);
            echo json_encode($data);
        }
        break;
    case 'delete':
        $query = selectQuery($dbname,'keu_jurnaldt','nojurnal',"nojurnal='".$data['nojurnal']."'");
        $res = fetchData($query);
        if(empty($res)) {
            $qDel = "delete from `".$dbname."`.`keu_jurnalht` where nojurnal='".$data['nojurnal']."'";
            echo $qDel;
            if(!mysql_query($qDel)) {
                echo "DB Error : ".mysql_error();
                exit;
            }
        } else {
            echo "Warning : Please delete detail transaction in the first place";
            exit;
        }
	case 'loadHeader':
		#== Get Journal Header
		$period = $_SESSION['org']['period'];
		$where = " tanggal>=".$period['start'].
			" and substr(nojurnal,10,4)='".$_SESSION['empl']['lokasitugas'].
			"' and kodejurnal='M'".
			" and revisi=0";
		$query = selectQuery($dbname,'keu_jurnalht',"kodejurnal,nojurnal,tanggal,noreferensi,matauang,totaldebet,totalkredit",
							 $where,"nojurnal desc");
		$resTab = fetchData($query);
		
		$table = "";
		foreach($resTab as $key=>$row) {
			$table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
			$table .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
			$table .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
			$table .= "<td id='delHead_".$key."'>";
			$table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
			$table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
			
			foreach($row as $col=>$dat) {
				if($col=='tanggal') {
					$dat = tanggalnormal($dat);
				}
				$dtplus=0;
				$dtmin=0;
				$krngan=0;
				$sData="select distinct sum(jumlah) as plus from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah>0";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				$rData= mysql_fetch_assoc($qData);
				$dtplus=$rData['plus'];
				
				$sData="select distinct sum(jumlah) as min from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah<0";
				$qData=mysql_query($sData) or die(mysql_error($conn));
				$rData= mysql_fetch_assoc($qData);
				$dtmin=$rData['min']*(-1);
				
				$sCekData="select sum(jumlah) as selisih from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."'";
				$qCekData=mysql_query($sCekData) or die(mysql_error($conn));
				$rCekData=mysql_fetch_assoc($qCekData);
			  
				$dbgr="";
				if(intval($rCekData['selisih'])!=0)
				{
				 $dbgr="bgcolor='red'";
				}
				if($col=='totaldebet')
				{
					$table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtplus,0)."</td>";
				}
				elseif($col=='totalkredit')
				{
					$table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtmin,0)."</td>";
				}
				else
				{
					$table .= "<td id='".$col."_".$key."' onclick='passEditHeader(".$key.")' ".$dbgr.">".$dat."</td>";
				}
				
			}
			$table .= "</tr>";
		}
		echo $table;
		break;
    default:
        break;
}
?>