<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
		cekHK();
		
		# Kegiatan harus ada
		$qKeg = selectQuery($dbname,'kebun_prestasi','*',"notransaksi='".$param['notransaksi']."'");
		$resKeg = fetchData($qKeg);
		if(empty($resKeg)) {
			echo 'Warning : Kegiatan harus diisi lebih dahulu';
			exit;
		}
		
		# Search No urut
		$selQuery = selectQuery($dbname,'kebun_kehadiran','nourut',"notransaksi='".$param['notransaksi']."'");
		$nourut = fetchData($selQuery);
		$maxNoUrut = 1;
		if(!empty($nourut)) {
			foreach($nourut as $row) {
			$row['nourut']>=$maxNoUrut ? $maxNoUrut=$row['nourut'] : false;
			}
			$maxNoUrut++;
		}
		#==============periksa apakah sudah ada kehadiran pada hari yang sama
        $tanggal=substr($param['notransaksi'],0,8);
        $str="select sum(jhk) as jum from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
              and karyawanid='".$param['nik']."' group by karyawanid";
        $res=mysql_query($str);
        $datr=mysql_fetch_assoc($res);
        #=        
        $str1="select a.* from ".$dbname.".sdm_absensidt a
			LEFT JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
			where a.tanggal=".$tanggal." and b.tipekaryawan = 4 and fingerprint<>1
            and a.karyawanid=".$param['nik'];
        $res1=mysql_query($str1);
		
        if(($datr['jum']+$param['jhk'])>1)
        {
            $not='';
            $str="select * from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
                  and karyawanid='".$param['nik']."'";
            $res=mysql_query($str);
            while($bar=mysql_fetch_object($res))
            {
                $not.="\n".$bar->notransaksi;
            }
            exit("Error: Karyawan tersebut sudah memiliki absen lebih dari satu HK (".$not.")__".$datr['jum']);
        }
        if(mysql_num_rows($res1)>0)#cek dari sdm_absensi
        {
            exit("Error: Karyawan tersebut sudah memiliki absen pada daftar absen untuk hari yang sama");
        }
        else
        {#jika belum ada maka aman
            $cols = array('nourut','nik','absensi','jhk','umr','insentif','notransaksi');
            $data = $param;
            $data['nourut'] = $maxNoUrut;
            unset($data['numRow']);
            $query = insertQuery($dbname,'kebun_kehadiran',$data,$cols);
            if(!mysql_query($query)) {
                echo "DB Error : ".mysql_error();
                exit;
            }

            unset($data['notransaksi']);
            $res = "";
            foreach($data as $cont) {
                $res .= "##".$cont;
            }

            $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
            echo $result;
        }
		break;
    case 'edit':
		cekHK();
		
		#==============periksa apakah sudah ada kehadiran pada hari yang sama
        $tanggal=substr($param['notransaksi'],0,8);
        $str="select sum(jhk) as jum from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
              and karyawanid='".$param['nik']."'and notransaksi<>'".$param['notransaksi']."' group by karyawanid";
        $res=mysql_query($str);
        $datr=mysql_fetch_assoc($res);
        #=        
        $str1="select a.* from ".$dbname.".sdm_absensidt a
			LEFT JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
			where a.tanggal=".$tanggal." and b.tipekaryawan = 4 and fingerprint<>1
            and a.karyawanid=".$param['nik'];
        $res1=mysql_query($str1);
		
        if(($datr['jum']+$param['jhk'])>1)
        {
            $not='';
            $str="select * from ".$dbname.".kebun_kehadiran_vw where tanggal=".$tanggal."
                  and karyawanid='".$param['nik']."'";
            $res=mysql_query($str);
            while($bar=mysql_fetch_object($res))
            {
                $not.="\n".$bar->notransaksi;
            }
            exit("Error: Karyawan tersebut sudah memiliki absen lebih dari satu HK (".$not.")__".$datr['jum']);
        }
        if(mysql_num_rows($res1)>0)#cek dari sdm_absensi
        {
            exit("Error: Karyawan tersebut sudah memiliki absen pada daftar absen untuk hari yang sama");
        }
        else
        {#jika belum ada maka aman
			$data = $param;
			unset($data['notransaksi']);
			unset($data['nourut']);
			foreach($data as $key=>$cont) {
				if(substr($key,0,5)=='cond_') {
					unset($data[$key]);
				}
			}
			$where = "notransaksi='".$param['notransaksi']."' and nourut='".$param['cond_nourut']."'";
			$query = updateQuery($dbname,'kebun_kehadiran',$data,$where);
			if(!mysql_query($query)) {
				echo "DB Error : ".mysql_error();
				exit;
			}
			echo json_encode($param);
		}
		break;
    case 'delete':
		$where = "notransaksi='".$param['notransaksi']."' and nourut='".$param['nourut']."'";
		$query = "delete from `".$dbname."`.`kebun_kehadiran` where ".$where;
		if(!mysql_query($query)) {
			echo "DB Error : ".mysql_error();
			exit;
		}
		break;
    default:
		break;
}


function cekHK() {
	global $dbname;
	global $param;
	global $proses;
	
	// Cek Upah harus ada jika HK lebih dari 0
	if($param['jhk']>0 and $param['umr']==0) {
		exit("Warning: Untuk pekerjaan dengan HK, maka upah tidak boleh 0");
	}
	
	// Get HK Prestasi
	$qHK = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".$param['notransaksi']."'");
	$resHK = fetchData($qHK);
	
	// Get HK Absensi
	$optAbs = makeOption($dbname,'kebun_kehadiran',"nourut,jhk",
						 "notransaksi='".$param['notransaksi']."'");
	$hkAbs = 0;
	foreach($optAbs as $val) {
		$hkAbs += $val;
	}
	
	if($proses=='edit') {
		$hkAbs -= $optAbs[$param['nourut']];
	}
	$hkAbs += $param['jhk'];
	
	if(empty($resHK)) {
		exit("Warning: Prestasi harus diisi terlebih dahulu");
	} else {
		$hkPres = $resHK[0]['jumlahhk'];
		
		if($hkAbs > $hkPres) exit("Warning: HK Absensi tidak boleh lebih besar dari HK Prestasi");
	}
}