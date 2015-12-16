<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$proses = $_GET['proses'];
$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$maxDay = cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);

/**
 * Proses Get Posted Mutasi
 */
// Get Data
$qTrans = "SELECT a.*,b.* FROM ".
	$dbname.".sdm_riwayatjabatan a LEFT JOIN ".$dbname.".datakaryawan b
	ON a.karyawanid = b.karyawanid
	WHERE a.mulaiberlaku like '".periodeberikut($param['periode'])."%' and
	b.lokasitugas='".$param['kodeorg']."' and 
	a.posting=1";
$data = fetchData($qTrans);

switch($proses) {
	case 'list':
		// Tampilan
		if(empty($data)) {
			$attr = array('disabled'=>'disabled');
		} else {
			$attr = array('onclick'=>"postMutasi()");
		}
		$tab = makeElement($dbname,'button',$_SESSION['lang']['posting'], $attr);
		$tab .= "<table class=data><thead><tr class=rowheader>";
		$tab .= "<td>".$_SESSION['lang']['nomorsk']."</td>";
		$tab .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
		$tab .= "<td>".$_SESSION['lang']['tanggalberlaku']."</td>";
		$tab .= "</tr></thead><tbody>";
		if(empty($data)) {
			$tab .= "<tr class=rowcontent><td colspan=3>".
				$_SESSION['lang']['tidakditemukan']."</td></tr>";
		} else {
			foreach($data as $row) {
				$tab .= "<tr class=rowcontent>";
				$tab .= "<td>".$row['nomorsk']."</td>";
				$tab .= "<td>".$row['namakaryawan']."</td>";
				$tab .= "<td>".tanggalnormal($row['mulaiberlaku'])."</td>";
				$tab .= "</tr>";
			}
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;
	case 'post':
		$nosk = array();
		$idKary = array();
		foreach($data as $row) {
			$nosk[] = $row['nomorsk'];
			$idKary[] = $row['karyawanid'];
		}
		
		// Get Kode PT
		$optPT = makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
		// Get Old Gaji =
		$qOldGaji = selectQuery($dbname,'sdm_5gajipokok',"karyawanid,idkomponen,jumlah",
								"karyawanid in ('".implode("','".$idKary)."') and tahun=".$tmpPeriod[1]);
		$resOldGaji = fetchData($qOldGaji);
		$optOldGaji = array();
		foreach($resOldGaji as $row) {
			$optOldGaji[$row['karyawanid']][$row['idkomponen']] = $row['rupiah'];
		}
		
		// Get Gaji
		$qGaji = selectQuery($dbname,'sdm_riwayatjabatan_gaji',"karyawanid,idkomponen,rupiah",
							 "nomorsk in ('".implode("','".$nosk)."')");
		$resGaji = fetchData($qGaji);
		$optGaji = array();
		foreach($resGaji as $row) {
			$optGaji[$row['karyawanid']][$row['idkomponen']] = $row['rupiah'];
		}
		
		// Iterasi
		foreach($data as $row) {
			// Update Data Karyawan
			$dataUpd = array(
				'kodeorganisasi' => $optPT[$row['kekodeorg']],
				'lokasitugas' => $row['kekodeorg'],
				'kodejabatan' => $row['kekodejabatan'],
				'tipekaryawan' => $row['ketipekaryawan'],
				'kodegolongan' => $row['kekodegolongan'],
				'bagian' => $row['kebagian']
			);
			$qUpdData = updateQuery($dbname,'datakaryawan',$dataUpd,
									"karyawanid='".$row['karyawanid']."'");
			
			// Init History Gaji
			$histGaji = array();
			
			// Update Karyawan
			if(!mysql_query($qUpdData)) {
				exit("DB Error: ".mysql_error());
			} else {
				// Update Perubahan Gaji
				$dbError = "";
				foreach($optGaji[$row['karyawanid']] as $komp=>$nilai) {
					// Query Insert
					$dataInsGaji = array(
						'tahun' => $tmpPeriod[1],
						'karyawanid' => $row['karyawanid'],
						'idkomponen' => $komp,
						'jumlah' => $nilai
					);
					$qInsGaji = insertQuery($dbname,'sdm_5gajipokok',$dataInsGaji);
					
					// Query Update
					$dataUpdGaji = array('jumlah' => $nilai);
					$qUpdGaji = updateQuery($dbname,'sdm_5gajipokok',$dataUpdGaji,
											"karyawanid='".$row['karyawanid']."' 
											and idkomponen='".$komp."' 
											and tahun ='".$tmpPeriod[1]."'");
					
					// Insert / Update Gaji
					if(!mysql_query($qInsGaji)) {
						if(!mysql_query($qUpdGaji)) {
							exit("Update Gaji ".$row['karyawanid'].
								"[".$komp."] Error: ".mysql_error());
						}
					}
					
					// History Data Gaji
					$histGaji[] = array(
						'updatetime' => date('Y-m-d H:i:s'),
						'updateby' => $_SESSION['standard']['userid'],
						'karyawanid' => $row['karyawanid'],
						'tahun' => $tmpPeriod[1],
						'idkomponen' => $komp,
						'jumlahlalu' => $optOldGaji[$row['karyawanid']][$komp],
						'jumlah' => $nilai
					);
					
					// Update Status Posting
					$dataUpdPost = array('posting' => 2);
					$updPost = updateQuery($dbname,'sdm_riwayatjabatan',$dataUpdPost,
										   "karyawanid='".$row['karyawanid']."'");
					if(!mysql_query($updPost)) {
						exit("Update Posting Error: ".mysql_error());
					}
				}
			}
			
			// Log Data Karyawan
			// Field yang berubah di data karyawan
			$dataChange = array();
			if($row['kekodeorg'] != $row['lokasitugas']) // Lokasi Tugas
				$dataChange[] = array('lokasitugas'=>array('old'=>$row['lokasitugas'],'new'=>$row['kekodeorg']));
			if($row['kekodejabatan'] != $row['kodejabatan']) // Jabatan
				$dataChange[] = array('kodejabatan'=>array('old'=>$row['kodejabatan'],'new'=>$row['kekodejabatan']));
			if($row['ketipekaryawan'] != $row['tipekaryawan']) // Tipe Karyawan
				$dataChange[] = array('tipekaryawan'=>array('old'=>$row['tipekaryawan'],'new'=>$row['ketipekaryawan']));
			if($row['kekodegolongan'] != $row['kodegolongan']) // Golongan
				$dataChange[] = array('kodegolongan'=>array('old'=>$row['kodegolongan'],'new'=>$row['kekodegolongan']));
			if($row['kebagian'] != $row['bagian']) // Bagian / Departemen
				$dataChange[] = array('bagian'=>array('old'=>$row['bagian'],'new'=>$row['kebagian']));
			
			// History Data Karyawan
			$histData = array(
				'updatetime' => date('Y-m-d H:i:s'),
				'updateby' => $_SESSION['standard']['userid'],
				'karyawanid' => $row['karyawanid'],
				'data' => json_encode($dataChange)
			);
			
			// Silent Insert Log - Data Karyawan
			$insHistData = insertQuery($dbname,'hist_datakaryawan',$histData);
			mysql_query($insHistData);
			
			// Silent Insert Log - Gaji Karyawan
			if(!empty($histGaji)) {
				$insHistGaji = insertQuery($dbname,'hist_gajikaryawan',$histGaji);
				mysql_query($insHistGaji);
			}
		}
		break;
	
	default:
		echo 'Process Undefined\nProcess Undefined\nProcess Undefined';
		break;
}