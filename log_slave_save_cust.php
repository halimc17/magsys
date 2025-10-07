<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');

	$kodecustomer=$_POST['kodecustomer'];
	$namacustomer=$_POST['namacustomer'];
  	$alamat=$_POST['alamat'];
	$kota=$_POST['kota'];
	$telepon=$_POST['telepon'];
	$kontakperson=$_POST['kontakperson'];
	$akun=$_POST['akun'];
	$plafon=$_POST['plafon'];
	$nilaihutang=$_POST['nilaihutang'];
	$npwp=$_POST['npwp'];
	$npwpalamat=$_POST['npwpalamat'];
	$penandatangan=$_POST['penandatangan'];
	$jabatan=$_POST['jabatan'];
	$noseri=$_POST['noseri'];
	$klcustomer=$_POST['klcustomer'];
	$method=$_POST['method'];
	$komoditi=$_POST['komoditi'];
	$berikat=$_POST['berikat'];
	$ketBerikat=$_POST['ketBerikat'];
	$toleransipenyusutan=$_POST['toleransipenyusutan'];
	$statusinteks=$_POST['statusinteks'];

	//print_r($_POST);
	switch($method){
		case 'delete':
			$strx="delete from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
			$sKp="delete from ".$dbname.".pmn_4customercontact where kodecustomer='".$kodecustomer."'";
			$sKo="delete from ".$dbname.".pmn_4komoditi where kodecustomer='".$kodecustomer."'";
			mysql_query($sKp);
			mysql_query($sKo);
		break;
		case 'update':
		//print_r($_POST); exit();
		$sKo="delete from ".$dbname.".pmn_4komoditi where kodecustomer='".$kodecustomer."'";
		mysql_query($sKo);
		$expKomoditi = explode(",", $komoditi);
		foreach($expKomoditi as $key) {
			$sUKo="insert into ".$dbname.".pmn_4komoditi (kodecustomer,kodebarang) values ('".$kodecustomer."','".$key."')";
			mysql_query($sUKo);
		}
		$strx="update ".$dbname.". pmn_4customer set namacustomer='".$namacustomer."',alamat='".$alamat."',kota='".$kota."',
		telepon='".$telepon."',
		akun='".$akun."',plafon='".$plafon."',
		nilaihutang='".$nilaihutang."',npwp='".$npwp."',alamatnpwp='".$npwpalamat."'
		,penandatangan='".$penandatangan."',jabatan='".$jabatan."'
		,noseri='".$noseri."',klcustomer='".$klcustomer."', statusinteks='".$statusinteks."' 
		,toleransipenyusutan='".$toleransipenyusutan."',statusberikat='".$berikat."',keteranganberikat='".$ketBerikat."' 
		where kodecustomer='".$kodecustomer."'";
		break;	
		
		case 'insert':
		
		$expKomoditi = explode(",", $komoditi);
		foreach($expKomoditi as $key) {
			$sKo="insert into ".$dbname.".pmn_4komoditi (kodecustomer,kodebarang) values ('".$kodecustomer."','".$key."')";
			mysql_query($sKo);
		}
		$strx="insert into ".$dbname.".pmn_4customer
		(`kodecustomer`, `namacustomer`, `alamat`, `kota`, `telepon`, `akun`, `plafon`, `nilaihutang`, `npwp`, `alamatnpwp`, `penandatangan`, `jabatan`, `noseri`, `klcustomer`, `toleransipenyusutan`, `statusberikat`, `keteranganberikat`,`statusinteks`)
		values
		('".$kodecustomer."','".$namacustomer."','".$alamat."','".$kota."','".$telepon."','".$akun."','".$plafon."','".$nilaihutang."','".$npwp."','".$npwpalamat."','".$penandatangan."','".$jabatan."','".$noseri."','".$klcustomer."','".$toleransipenyusutan."','".$berikat."','".$ketBerikat."','".$statusinteks."')"; //echo $strx; exit();
		break;
		default:
        break;	
	}
	if(mysql_query($strx))
  {}	
  else
	{
		echo " Gagal,".addslashes(mysql_error($conn));
	}	
	

	 	//ambil data dari tabel kelompok customer
	 		
		$srt="select * from ".$dbname.".pmn_4customer order by kodecustomer desc";  //echo $srt;
		if($rep=mysql_query($srt))
		  {
			$no=0;
			while($bar=mysql_fetch_object($rep))
			{
			//get kelompok cust
			$sql="select * from ".$dbname.".pmn_4klcustomer where `kode`='".$bar->klcustomer."'";
			$query=mysql_query($sql) or die(mysql_error($conn));
			$res=mysql_fetch_object($query);
			
			//get Komoditi
			$sKo="select t1.*,t2.namabarang from ".$dbname.".pmn_4komoditi t1
				left join ".$dbname.".log_5masterbarang t2
				on t1.kodebarang = t2.kodebarang
				where `kodecustomer`='".$bar->kodecustomer."'";
			$qKo=mysql_query($sKo) or die(mysql_error($conn));
			$hasilKomoditi="";
			$hasilKomoditi2="";
			while($rKo=mysql_fetch_object($qKo)){
				$hasilKomoditi.=",".$rKo->kodebarang;
				$hasilKomoditi2.=",<br>".$rKo->namabarang;
			}
			
			//get Kontak Person
			$sPer="select * from ".$dbname.".pmn_4customercontact
				where `kodecustomer`='".$bar->kodecustomer."'";
			$qPer=mysql_query($sPer) or die(mysql_error($conn));
			$hasilPerson="";
			while($rPer=mysql_fetch_object($qPer)){
				$hasilPerson.=",<br>".$rPer->nama." (".$rPer->email.")";
			}
			
			//get akun
			$spr="select * from  ".$dbname.".keu_5akun where `noakun`='".$bar->akun."'";
			$rej=mysql_query($spr) or die(mysql_error($conn));
			$bas=mysql_fetch_object($rej);
			$no+=1;
			$bar->alamat = clearInvalidChar($bar->alamat);
			$bar->telepon = clearInvalidChar($bar->telepon);
			$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
			echo"<tr class=rowcontent>
				<td style='vertical-align:top;'>".$no."</td>
				<td style='vertical-align:top;'>".substr($hasilKomoditi2,5)."</td>
				<td style='vertical-align:top;'>".$bar->kodecustomer."</td>
				<td style='vertical-align:top;'>".$bar->namacustomer."</td>
				<td style='vertical-align:top;'>".$bar->alamat."</td>
				<td style='vertical-align:top;'>".$bar->kota."</td>
				<td style='vertical-align:top;'>".$bar->telepon."</td>
				<td style='vertical-align:top;'>".$bar->npwp."</td>
				<td style='vertical-align:top;'>".$bar->alamatnpwp."</td>
				<td style='vertical-align:top;'>".$bar->penandatangan."</td>
				<td style='vertical-align:top;'>".$bar->jabatan."</td>
				<td style='vertical-align:top;'>".substr($hasilPerson,5)."</td>
				<td style='vertical-align:top;'>".$bar->statusinteks."</td>
				<td style='vertical-align:top; text-align:right;'>".$bar->plafon."</td>
				<td style='vertical-align:top; text-align:right;'>".$bar->nilaihutang."</td>
				<td style='vertical-align:top; text-align:right;'>".$bar->toleransipenyusutan."</td>
				<td style='vertical-align:top; text-align:center;'>".(($bar->statusberikat=='1') ? 'Y' : '')."</td>
				<td style='vertical-align:top;'>".$bar->keteranganberikat."</td>
				<td style='vertical-align:top;'><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodecustomer."','".$bar->namacustomer."','".$bar->alamat."','".$bar->kota."','".$bar->telepon."','','".$bar->akun."','".$bar->plafon."','".$bar->nilaihutang."','".$bar->npwp."','".$bar->alamatnpwp."','".$bar->penandatangan."','".$bar->jabatan."','".$bar->noseri."','".$bar->klcustomer."','".(isset($bas->namaakun)? $bas->namaakun:'')."','','".$bar->toleransipenyusutan."','".$bar->statusberikat."','".$bar->keteranganberikat."','".substr($hasilKomoditi,1)."','".$bar->statusinteks."');\"></td>
				<td style='vertical-align:top;'><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPlgn('".$bar->kodecustomer."');\"></td>
				</tr>";
			}
		  }
		  else
		 {
			echo " Gagal,".(mysql_error($conn));
		 }
	 ?>
