<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$nokontrak = checkPostGet('notrans','');
$periode=checkPostGet('periode','');
$kdBrg=checkPostGet('kdBrg','');
$kdBrg2=checkPostGet('kdBrg2','');
$thn = checkPostGet('thn','');
$kdBrg3 = checkPostGet('kdBrg3','');
$pt = checkPostGet('pt','');
$pt2 = checkPostGet('pt2','');
$pt3 = checkPostGet('pt3','');
$tgl_dr = checkPostGet('tgl_dr','');
$tgl_samp = checkPostGet('tgl_samp','');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

$total1=$total2=$total3=$total4=0;
$total1e=$total2e=$total3e=$total4e=0;
$total1p=$total2p=$total3p=$total4p=0;

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$indukOrg=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$whrNotran="";
$whrNotranA="";
if($kdBrg!='40000003'){
    #filter untuk data timbangan yang duplikasi dari keuangan>Transaksi>Pengakuan Jual
    #jika TBS maka tidak di tambahkan,kodebarang untuk TBS=40000003
    $whrNotran=" and char_length(notransaksi)<8";
    $whrNotranA=" and char_length(a.notransaksi)<8";
}
switch($proses)
{
        case'preview':
		
		if($periode==''){
			exit("Warning : Periode harus dipilih");
		}
		
        echo"<table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>
        <td>".$_SESSION['lang']['kodept']."</td>
        <td>".$_SESSION['lang']['NoKontrak']."</td>
        <td>".$_SESSION['lang']['komoditi']."</td>
        <td>".$_SESSION['lang']['tglKontrak']."</td>
        <td>".$_SESSION['lang']['Pembeli']."</td>
        <td>".$_SESSION['lang']['estimasiPengiriman']."</td>
        <td>".$_SESSION['lang']['jmlhBrg']."</td>
        <td>".$_SESSION['lang']['pemenuhan']."</td>
        <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        <td>".$_SESSION['lang']['sisa']."</td>
        </tr></thead><tbody>
        ";
        if($kdBrg!='')
        {
                $where=" and kodebarang='".$kdBrg."'";
        }
		if($pt!=''){
			$where.=" and kodept like '%".$pt."%'";
		}
                
                
        $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept 
              from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '%".$periode."%' ".$where."";
        //exit("Error".$sql);
        $query=mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($query)<=0){
			echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($res=mysql_fetch_assoc($query))
			{
					$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
					$qBrg=mysql_query($sBrg) or die(mysql_error());
					$rBrg=mysql_fetch_assoc($qBrg);
					
					$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
					$qCust=mysql_query($sCust) or die(mysql_error());
					$rCust=mysql_fetch_assoc($qCust);
					
					// Get No Kontrak Internal
					$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
											"nokontrak = '".$res['nokontrak']."' or
											nokontrakinternal = '".$res['nokontrak']."'");
					$resKontrak = fetchData($qKontrak);
					if(empty($resKontrak)) {
						$optKontrak = array($res['nokontrak']);
					} else {
						$optKontrak = array();
					}
					$optSipb = array();
					foreach($resKontrak as $row) {
						$optKontrak[] = $row['nokontrak'];
						if(!empty($row['nokontrakinternal'])) {
							$optKontrak[] = $row['nokontrakinternal'];
						}
						$optSipb[] = $row['nodo'];
					}
					
					$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."') ".$whrNotran."";
					if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
					$qTimb=mysql_query($sTimb) or die(mysql_error());
					$rTimb=mysql_fetch_assoc($qTimb);
					$arr="nokontrak"."##".$res['nokontrak'];
					$sisaBarang=$res['kuantitaskontrak']-$rTimb['jumlahTotal'];
					echo"<tr class=rowcontent onclick=\"zDetail(event,'pmn_slave_laporanPemenuhanKontrak.php','".$arr."')\" style=\"cursor:pointer;\">
					<td style=\"cursor:pointer;\">".$res['kodept']."</td>
					<td style=\"cursor:pointer;\">".$res['nokontrak']."</td>
					<td style=\"cursor:pointer;\">".$rBrg['namabarang']."</td>
					<td style=\"cursor:pointer;\">".tanggalnormal($res['tanggalkontrak'])."</td>
					<td style=\"cursor:pointer;\">".$rCust['namacustomer']."</td>
					<td style=\"cursor:pointer;\">".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td align=right style=\"cursor:pointer;\">".number_format($res['kuantitaskontrak'])."</td>
					<td align=right style=\"cursor:pointer;\">".number_format($rTimb['jumlahTotal'])."</td>
						<td align=right style=\"cursor:pointer;\">".number_format($rTimb['jumlahKgpem'])."</td>
						<td align=right style=\"cursor:pointer;\">".number_format($sisaBarang)."</td>
					</tr>
					";
					$total1+=$res['kuantitaskontrak'];
					$total2+=$rTimb['jumlahTotal'];
					$total3+=$rTimb['jumlahKgpem'];
					$total4+=$sisaBarang;                
			}
			echo"<tr class=rowcontent>
			<td colspan=6>".$_SESSION['lang']['total']."</td>
			<td align=right>".number_format($total1)."</td>
			<td align=right>".number_format($total2)."</td>
			<td align=right>".number_format($total3)."</td>
			<td align=right>".number_format($total4)."</td>
			</tr>";
		}
        echo"</tbody></table>";
        break;
		
        case'preview2':
        if($thn==''){
			exit("Warning: Periode harus dipilih.");
		}
        echo"<table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>
        <td>".$_SESSION['lang']['kodept']."</td>
        <td>".$_SESSION['lang']['NoKontrak']."</td>
        <td>".$_SESSION['lang']['komoditi']."</td>
        <td>".$_SESSION['lang']['tglKontrak']."</td>
        <td>".$_SESSION['lang']['Pembeli']."</td>
        <td>".$_SESSION['lang']['estimasiPengiriman']."</td>
        <td>".$_SESSION['lang']['jmlhBrg']."</td>
        <td>".$_SESSION['lang']['pemenuhan']."</td>
        <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        <td>".$_SESSION['lang']['sisa']."</td>
        </tr></thead><tbody>
        ";

        $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept  from ".$dbname.".pmn_kontrakjual
              where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak asc";
        
      
        $query=mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($query)<=0){
			echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($res=mysql_fetch_assoc($query))
			{
				$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
				$qBrg=mysql_query($sBrg) or die(mysql_error());
				$rBrg=mysql_fetch_assoc($qBrg);

				$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
				$qCust=mysql_query($sCust) or die(mysql_error());
				$rCust=mysql_fetch_assoc($qCust);
				
				// Get No Kontrak Internal
				$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
										"nokontrak = '".$res['nokontrak']."' or
										nokontrakinternal = '".$res['nokontrak']."'");
				$resKontrak = fetchData($qKontrak);
				if(empty($resKontrak)) {
					$optKontrak = array($res['nokontrak']);
				} else {
					$optKontrak = array();
				}
				$optSipb = array();
				foreach($resKontrak as $row) {
					$optKontrak[] = $row['nokontrak'];
					if(!empty($row['nokontrakinternal'])) {
						$optKontrak[] = $row['nokontrakinternal'];
					}
					$optSipb[] = $row['nodo'];
				}
				
				$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
				if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
				//$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak='".$res['nokontrak']."'";
				$qTimb=mysql_query($sTimb) or die(mysql_error());
				$rTimb=mysql_fetch_assoc($qTimb);
				$arr="nokontrak"."##".$res['nokontrak'];
				$sisaBarang=$res['kuantitaskontrak']-$rTimb['jumlahTotal'];
				if($rTimb['jumlahTotal']<=$res['kuantitaskontrak'])
				{
					echo"<tr class=rowcontent \">
					<td >".$res['kodept']."</td>
					<td >".$res['nokontrak']."</td>
					<td>".$rBrg['namabarang']."</td>
					<td>".tanggalnormal($res['tanggalkontrak'])."</td>
					<td>".$rCust['namacustomer']."</td>
					<td>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td align=right>".number_format($res['kuantitaskontrak'],2)."</td>
					<td align=right>".number_format($rTimb['jumlahTotal'],2)."</td>
					<td align=right>".number_format($rTimb['jumlahKgpem'],2)."</td>
					<td align=right>".number_format($sisaBarang,2)."</td>
					</tr>
					";
				}
			}
		}
        echo"</tbody></table>";
        break;
        
		case'preview3':
		if($tgl_dr==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
		if($tgl_samp==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
        echo"
        <div id=cetakdHtml >
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>No</td>
        <td>".$_SESSION['lang']['kodept']."</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>".$_SESSION['lang']['kodebarang']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>".$_SESSION['lang']['nosipb']."</td>         
        <td>".$_SESSION['lang']['sopir']."</td>
        <td>".$_SESSION['lang']['kendaraan']."</td>               
        <td>".$_SESSION['lang']['beratBersih']."</td>
        <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/
            $tgl1=explode("-",$_POST['tgl_dr']);
            $tangglAwl=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
            $tgl2=explode("-",$_POST['tgl_samp']);
            $tangglSmp=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];
			$hPT = $namaOrg[$indukOrg[$pt3]];

        $sDet="select a.notransaksi,a.tanggal,a.nodo,a.nosipb,a.beratbersih,a.nokendaraan,a.supir,a.kgpembeli,a.kodebarang, b.kodeorganisasi
              from ".$dbname.".pabrik_timbangan a 
			  left join ".$dbname.".organisasi b
			  on a.millcode = b.kodeorganisasi
			  where substr(a.tanggal,1,10) between '".$tangglAwl."' and '".$tangglSmp."' and a.kodebarang like '%".$kdBrg3."%'
                  and a.nokontrak !='' and b.induk like '%".$pt3."%'  ".$whrNotranA."
              order by tanggal asc";
        $qDet=mysql_query($sDet) or die(mysql_error());
        $rCek=mysql_num_rows($qDet);
        if($rCek>0)
        {
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['kodeorganisasi']."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$optNmBrg[$rDet['kodebarang']]."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>			
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td align=right>".number_format($rDet['beratbersih'],2)."</td>
                        <td align=right>".number_format($rDet['kgpembeli'],2)."</td>
                        </tr>";
						setIt($subtot['total'],0);
						setIt($subtotKga['totalKg'],0);
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                echo"<tr class=rowcontent><td colspan='9'>Total</td><td align=right>".number_format($subtot['total'],2)."</td><td align=right>".number_format($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        echo"</tbody></table></div></fieldset>";
        break;
		
		case'excel':
       
                        $stream="
                        <table>
                        <tr><td colspan=11 align=center>".$_SESSION['lang']['laporanPemenuhanKontrak']."</td></tr>
                        <tr><td colspan=3>".$_SESSION['lang']['perusahaan']."</td><td style='text-align:left'>".$namaOrg[$pt]."</td></tr>
                        <tr><td colspan=3>".$_SESSION['lang']['periode']."</td><td style='text-align:left'>".$periode."</td></tr>
                        <tr><td colspan=3></td><td></td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center>No.</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodept']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['NoKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['komoditi']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tglKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['Pembeli']."</td>	
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['estimasiPengiriman']."</td>	
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jmlhBrg']."</td>	
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['pemenuhan']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sisa']."</td>
                        </tr>";
                        
                        
                        
                        if($periode=='')
                        {
                            exit("Warning : Periode harus dipilih");
                        }
                        if($kdBrg!='')
                        {
                            $where=" and kodebarang='".$kdBrg."'";
                        }
                        if($pt!=''){
                            $where.=" and kodept like '%".$pt."%'";
                        }
                
                
        $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept 
              from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '%".$periode."%' ".$where."";
        
      
        //exit("Error".$sql);
        $query=mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($query)<=0){
			 $stream.="<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			while($res=mysql_fetch_assoc($query))
			{
                            $no+=1;
					$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
					$qBrg=mysql_query($sBrg) or die(mysql_error());
					$rBrg=mysql_fetch_assoc($qBrg);
					
					$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
					$qCust=mysql_query($sCust) or die(mysql_error());
					$rCust=mysql_fetch_assoc($qCust);
					
					// Get No Kontrak Internal
					$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
											"nokontrak = '".$res['nokontrak']."' or
											nokontrakinternal = '".$res['nokontrak']."'");
					$resKontrak = fetchData($qKontrak);
					if(empty($resKontrak)) {
						$optKontrak = array($res['nokontrak']);
					} else {
						$optKontrak = array();
					}
					$optSipb = array();
					foreach($resKontrak as $row) {
						$optKontrak[] = $row['nokontrak'];
						if(!empty($row['nokontrakinternal'])) {
							$optKontrak[] = $row['nokontrakinternal'];
						}
						$optSipb[] = $row['nodo'];
					}
					
					$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."') ".$whrNotran."";
					if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
					$qTimb=mysql_query($sTimb) or die(mysql_error());
					$rTimb=mysql_fetch_assoc($qTimb);
					$arr="nokontrak"."##".$res['nokontrak'];
					$sisaBarang=$res['kuantitaskontrak']-$rTimb['jumlahTotal'];
					$stream.="	<tr class=rowcontent>
                                        <td>".$no."</td>    
					<td>".$res['kodept']."</td>
					<td>".$res['nokontrak']."</td>
					<td>".$rBrg['namabarang']."</td>
					<td>".tanggalnormal($res['tanggalkontrak'])."</td>
					<td>".$rCust['namacustomer']."</td>
					<td>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
					<td>".number_format($res['kuantitaskontrak'])."</td>
					<td>".number_format($rTimb['jumlahTotal'])."</td>
                                        <td>".number_format($rTimb['jumlahKgpem'])."</td>
                                        <td>".number_format($sisaBarang)."</td>
					</tr>
					";
					$total1+=$res['kuantitaskontrak'];
					$total2+=$rTimb['jumlahTotal'];
					$total3+=$rTimb['jumlahKgpem'];
					$total4+=$sisaBarang;                
			}
			$stream.="<tr class=rowheader>
			<td colspan=7>".$_SESSION['lang']['total']."</td>
			<td align=right>".number_format($total1)."</td>
			<td align=right>".number_format($total2)."</td>
			<td align=right>".number_format($total3)."</td>
			<td align=right>".number_format($total4)."</td>
			</tr>";
		}
        $stream.="</tbody></table>";
    
                    
                    //echo "warning:".$strx;
                    //=================================================
                    $stream.="</table>";
                                            $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                    $nop_="PemenuhanKontrak";
                    if(strlen($stream)>0)
                    {
                    if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                    @unlink('tempExcel/'.$file);
                    }
                    }	
                    closedir($handle);
                    }
                    $handle=fopen("tempExcel/".$nop_.".xls",'w');
                    if(!fwrite($handle,$stream))
                    {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                    }
                    else
                    {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
                    }
                    fclose($handle);
                    }
        break;
		
		case'excel2':
        $kdBrg2=$_GET['kdBrg2'];
        if($thn=='')
        {
            exit("Warning: tahun harus dipilih.");
        }
		

                        $stream="
                        <table>
                        <tr><td colspan=11 align=center>Unfulfilled sales contract</td></tr>
                        <tr><td colspan=3>".$_SESSION['lang']['perusahaan']." : ".$namaOrg[$pt2]."</td><td></td></tr>
                        <tr><td colspan=3>".$_SESSION['lang']['periode']." : ".$thn."</td><td></td></tr>
                        <tr><td colspan=3></td><td></td></tr>
                        </table>
                        <table border=1>
                        <tr>
                                <td bgcolor=#DEDEDE align=center>No.</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodept']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['NoKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['komoditi']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tglKontrak']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['Pembeli']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['estimasiPengiriman']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jmlhBrg']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['pemenuhan']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
                                <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sisa']."</td>
                        </tr>";

                        $strx="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual
                              where kodebarang like '%".$kdBrg2."%' and tanggalkontrak like '".$thn."%' and kodept like '%".$pt2."%' order by tanggalkontrak asc";
                        $resx=mysql_query($strx) or die(mysql_error());
                        $row=mysql_fetch_row($resx);
                        if($row<1)
                        {
							$stream.="	<tr class=rowcontent>
							<td colspan=11 align=center>Not Found</td></tr>
							";
                        }
                        else
                        {
                        $no=0;
                        $resx=mysql_query($strx);
                                while($barx=mysql_fetch_assoc($resx))
                                {
                                $no+=1;
                                $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$barx['koderekanan']."'";
                                $qCust=mysql_query($sCust) or die(mysql_error());
                                $rCust=mysql_fetch_assoc($qCust);

                                $sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem  from ".$dbname.".pabrik_timbangan where nokontrak='".$barx['nokontrak']."'  ".$whrNotran."";
                                $qTimb=mysql_query($sTimb) or die(mysql_error());
                                $rTimb=mysql_fetch_assoc($qTimb);
                                $sisaData=$barx['kuantitaskontrak']-$rTimb['jumlahTotal'];
                                    if($rTimb['jumlahTotal']<=$barx['kuantitaskontrak'])
                                    {
                                        $stream.="	<tr class=rowcontent>
                                        <td>".$no."</td>
                                        <td>".$barx['kodept']."</td>
                                        <td>".$barx['nokontrak']."</td>
                                        <td>".$optNmBrg[$barx['kodebarang']]."</td>
                                        <td>".$barx['tanggalkontrak']."</td>
                                        <td>".$rCust['namacustomer']."</td>
                                        <td>".tanggalnormal($barx['tanggalkirim'])." s.d. ".tanggalnormal($barx['sdtanggal'])."</td>
                                        <td>".number_format($barx['kuantitaskontrak'],0)."</td>
                                        <td>".number_format($rTimb['jumlahTotal'],0)."</td>
                                        <td>".number_format($rTimb['jumlahKgpem'],0)."</td>
                                        <td>".number_format($sisaData,0)."</td>
                                        </tr>";
                                    }
                                }
                        }

                        //echo "warning:".$strx;
                        //=================================================
                        $stream.="</table>";
                                                $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];

                        $nop_="KontrakBlmTpenuhi";
                        if(strlen($stream)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$stream))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
		
        case'pdf':
		if($periode==''){
			exit("Warning : Periode harus dipilih");
		}
		$periode=$_GET['periode'];
        $kdBrg=$_GET['kdBrg'];
         class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $periode;
                                global $kdBrg;
                                global $namaOrg;
                                global $pt;


                                $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '%".$periode."%'";
                                $query=mysql_query($sql) or die(mysql_error());
                                $res=mysql_fetch_assoc($query);

                //$tkdOperasi=$res['jlhharitdkoperasi'];
                //                $jmlhHariOperasi=$res['jlhharioperasi'];
                                //$meter=$res['merterperhari'];
                                //$kdOrg=$res['orgdata'];


                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$res['kodept']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 15;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->SetFont('Arial','B',9);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['laporanPemenuhanKontrak'],'',0,'L');
                                $this->Ln();
                                $this->SetFont('Arial','',8);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['perusahaan'],'',0,'L');
                                $this->Cell(5,$height,':','',0,'L');
                                $this->Cell(45/100*$width,$height,$namaOrg[$pt],'',0,'L');
								$this->Ln();
                                $this->SetFont('Arial','',8);
                                $this->Cell((20/100*$width)-5,$height,$_SESSION['lang']['periode'],'',0,'L');
                                $this->Cell(5,$height,':','',0,'L');
                                $this->Cell(45/100*$width,$height,$periode,'',0,'L');



                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','U',7);
                $this->Cell($width,$height, $_SESSION['lang']['laporanPemenuhanKontrak'],0,1,'C');	
                $this->Ln();	

                $this->SetFont('Arial','B',5);
                $this->SetFillColor(220,220,220);


                $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                $this->Cell(14/100*$width,$height,$_SESSION['lang']['NoKontrak'],1,0,'C',1);
                $this->Cell(13/100*$width,$height,$_SESSION['lang']['komoditi'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['tglKontrak'],1,0,'C',1);
                $this->Cell(18/100*$width,$height,$_SESSION['lang']['Pembeli'],1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['estimasiPengiriman'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['jmlhBrg']." (KG)",1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['pemenuhan']." (KG)",1,0,'C',1);
                $this->Cell(10/100*$width,$height,$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli'],1,0,'C',1);
                $this->Cell(8/100*$width,$height,$_SESSION['lang']['sisa']." (KG)",1,1,'C',1);

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',5);
                if($kdBrg!='')
                {
                        $where=" and kodebarang='".$kdBrg."'";
                }
				if($pt!='')
                {
                        $where.=" and kodept like '%".$pt."%'";
                }
                $sDet="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak from ".$dbname.".pmn_kontrakjual where tanggalkontrak like '%".$periode."%' ".$where."";
                $qDet=mysql_query($sDet) or die(mysql_error());
				if(mysql_num_rows($qDet)<=0){
					$pdf->Cell(99/100*$width,$height,$_SESSION['lang']['datanotfound'],1,0,'C',1);
				}else{
					while($rDet=mysql_fetch_assoc($qDet))
					{
							$no+=1;
							$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rDet['kodebarang']."'";
							$qBrg=mysql_query($sBrg) or die(mysql_error());
							$rBrg=mysql_fetch_assoc($qBrg);

							$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rDet['koderekanan']."'";
							$qCust=mysql_query($sCust) or die(mysql_error());
							$rCust=mysql_fetch_assoc($qCust);
							
							// Get No Kontrak Internal
							$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
													"nokontrak = '".$res['nokontrak']."' or
													nokontrakinternal = '".$res['nokontrak']."'");
							$resKontrak = fetchData($qKontrak);
							if(empty($resKontrak)) {
								$optKontrak = array($res['nokontrak']);
							} else {
								$optKontrak = array();
							}
							$optSipb = array();
							foreach($resKontrak as $row) {
								$optKontrak[] = $row['nokontrak'];
								if(!empty($row['nokontrakinternal'])) {
									$optKontrak[] = $row['nokontrakinternal'];
								}
								$optSipb[] = $row['nodo'];
							}
							
							$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem   from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
							if(!empty($optSipb)) {$sTimb .= " AND nosipb in ('".implode("','",$optSipb)."')";}
							
							//$sTimb="select sum(beratbersih) as jumlahTotal,sum(kgpembeli) as jumlahKgpem  from ".$dbname.".pabrik_timbangan where nokontrak='".$rDet['nokontrak']."'";
							//exit("Error".$sTimb);
							$qTimb=mysql_query($sTimb) or die(mysql_error());
							$rTimb=mysql_fetch_assoc($qTimb);
							$sisaData=$rDet['kuantitaskontrak']-$rTimb['jumlahTotal'];
							
							$awalX = $pdf->GetX();
							$awalY = $pdf->GetY();
							
							$pdf->SetX(1000);
							$pdf->MultiCell(14/100*$width,$height,$rDet['nokontrak'],1,'L',1);
							$height2 = $pdf->GetY() - $awalY;
							
							
							$pdf->SetX($awalX);
							$pdf->SetY($awalY);
							
							$pdf->Cell(3/100*$width,$height2,$no,1,0,'C',1);
							
							$awalX2 = $pdf->GetX();
							$awalY2 = $pdf->GetY();
							$pdf->MultiCell(14/100*$width,$height,$rDet['nokontrak'],1,'L',1);
							
							$pdf->SetXY($awalX2+(14/100*$width),$awalY2);
							$pdf->Cell(13/100*$width,$height2,$rBrg['namabarang'],1,0,'L',1);
							$pdf->Cell(8/100*$width,$height2,tanggalnormal($rDet['tanggalkontrak']),1,0,'L',1);
							$pdf->Cell(18/100*$width,$height2,substr($rCust['namacustomer'],0,50),1,0,'L',1);		
							$pdf->Cell(10/100*$width,$height2,tanggalnormal($rDet['tanggalkirim'])."-".tanggalnormal($rDet['sdtanggal']),1,0,'C',1);		
							$pdf->Cell(8/100*$width,$height2,number_format($rDet['kuantitaskontrak']),1,0,'R',1);
							$pdf->Cell(8/100*$width,$height2,number_format($rTimb['jumlahTotal']),1,0,'R',1);
							$pdf->Cell(10/100*$width,$height2,number_format($rTimb['jumlahKgpem']),1,0,'R',1);
							$pdf->Cell(8/100*$width,$height2,number_format($sisaData),1,1,'R',1);
							$total1p+=$rDet['kuantitaskontrak'];
							$total2p+=$rTimb['jumlahTotal'];
							$total3p+=$rTimb['jumlahKgpem'];
							$total4p+=$sisaData;
					}
							$pdf->Cell(66/100*$width,$height,$_SESSION['lang']['total'],1,0,'R',1);
							$pdf->Cell(8/100*$width,$height,number_format($total1p),1,0,'R',1);
							$pdf->Cell(8/100*$width,$height,number_format($total2p),1,0,'R',1);
							$pdf->Cell(10/100*$width,$height,number_format($total3p),1,0,'R',1);
							$pdf->Cell(8/100*$width,$height,number_format($total4p),1,1,'R',1);
				}
        $pdf->Output();
        break;
        
        case'getDetail':
        $drr="##no_kontrak";
        $arr="nokontrak"."##".$res['nokontrak'];
        echo"<script language=javascript src=js/generic.js></script><script language=javascript src=js/zTools.js></script>
        <script language=javascript src=js/pmn_laporanPemenuhanKontrak.js></script>";
        echo"<link rel=stylesheet type=text/css href=style/generic.css>";
        $nokontrak=$_GET['nokontrak'];
        $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
        $qHead=mysql_query($sHed) or die(mysql_error());
        $rHead=mysql_fetch_assoc($qHead);
        $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
        $qBrg=mysql_query($sBrg) or die(mysql_error());
        $rBrg=mysql_fetch_assoc($qBrg);

        $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
        $qCust=mysql_query($sCust) or die(mysql_error());
        $rCust=mysql_fetch_assoc($qCust);
        echo"<fieldset><legend>".$_SESSION['lang']['detailPengiriman']."</legend>
        <table cellspacing=1 border=0 class=myinputtext>
        <tr>
                <td>".$_SESSION['lang']['NoKontrak']."</td><td>:</td><td id='no_kontrak' value='".$nokontrak."'>".$nokontrak."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['tglKontrak']."</td><td>:</td><td>".tanggalnormal($rHead['tanggalkontrak'])."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['komoditi']."</td><td>:</td><td>".$rBrg['namabarang']."</td>
        </tr>
        <tr>
                <td>".$_SESSION['lang']['Pembeli']."</td><td>:</td><td>".$rCust['namacustomer']."</td>
        </tr>
        <tr><td><button onclick=\"zPdfDetail('pmn_slave_laporanPemenuhanKontrak','".$drr."','printPdf')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>
        <button onclick=\"zBack()\" class=\"mybutton\" name=\"preview\" id=\"preview\">HTML</button>
        <button onclick=\"detailExcel('".$nokontrak."','pmn_slave_laporanPemenuhanKontrak.php','printExcel','event')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
        </table><br />";
        echo"<div id=cetakdPdf style=\"display:none;\">
        <fieldset><legend>".$_SESSION['lang']['print']."</legend>
        <div id=\"printPdf\">
        </div>
        </fieldset>
        </div>
        ";
        echo"
        <div id=cetakdHtml >
        <table cellspacing=1 border=0 class=sortable><thead>
        <tr class=data>
        <td>No</td>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>".$_SESSION['lang']['nodo']."</td>
        <td>".$_SESSION['lang']['nosipb']."</td>
        <td>".$_SESSION['lang']['kendaraan']."</td>            
        <td>".$_SESSION['lang']['sopir']."</td>
        <td>".$_SESSION['lang']['beratBersih']."</td>
        <td>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/	
		// Get No Kontrak Internal
		$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
								"nokontrak = '".$nokontrak."' or
								nokontrakinternal = '".$nokontrak."'");
		$resKontrak = fetchData($qKontrak);
		if(empty($resKontrak)) {
			$optKontrak = array($nokontrak);
		} else {
			$optKontrak = array();
		}
		$optSipb = array();
		foreach($resKontrak as $row) {
			$optKontrak[] = $row['nokontrak'];
			if(!empty($row['nokontrakinternal'])) {
				$optKontrak[] = $row['nokontrakinternal'];
			}
			$optSipb[] = $row['nodo'];
		}
		
		$sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
		if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}
        $qDet=mysql_query($sDet) or die(mysql_error());
        $rCek=mysql_num_rows($qDet);
        if($rCek>0)
        {
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        $no+=1;
                        echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".number_format($rDet['beratbersih'],2)."</td>
                        <td align=right>".number_format($rDet['kgpembeli'],2)."</td>
                        </tr>";
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                echo"<tr class=rowcontent><td colspan='7'>Total</td><td align=right>".number_format($subtot['total'],2)."</td><td align=right>".number_format($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                echo"<tr><td colspan=7>Not Found</td></tr>";
        }
        echo"</tbody></table></div></fieldset>";

        break;
        case'getExcel':
            $tab.="
        <table cellspacing=1 border=1 class=sortable><thead>
        <tr class=data>
        <td  bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nodo']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nosipb']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kendaraan']."</td>            
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sopir']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/	
		// Get No Kontrak Internal
		$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
								"nokontrak = '".$nokontrak."' or
								nokontrakinternal = '".$nokontrak."'");
		$resKontrak = fetchData($qKontrak);
		if(empty($resKontrak)) {
			$optKontrak = array($nokontrak);
		} else {
			$optKontrak = array();
		}
		$optSipb = array();
		foreach($resKontrak as $row) {
			$optKontrak[] = $row['nokontrak'];
			if(!empty($row['nokontrakinternal'])) {
				$optKontrak[] = $row['nokontrakinternal'];
			}
			$optSipb[] = $row['nodo'];
		}
        $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
        if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}
        $qDet=mysql_query($sDet) or die(mysql_error());
        $rCek=mysql_num_rows($qDet);
        if($rCek>0)
        {
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        $no+=1;
                        $tab.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".number_format($rDet['beratbersih'],2)."</td>
                        <td align=right>".number_format($rDet['kgpembeli'],2)."</td>
                        </tr>";
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                $tab.="<tr class=rowcontent><td colspan='7'>Total</td><td align=right>".number_format($subtot['total'],2)."</td><td align=right>".number_format($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                $tab.="<tr><td colspan=7>Not Found</td></tr>";
        }
        $tab.="</tbody>";
                        $tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	

                        $nop_="ContractFullfillmentDetail";
                        if(strlen($tab)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$tab))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
        
        case'excel3':
		if($tgl_dr==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
		if($tgl_samp==''){
			exit("Warning : Periode tanggal harus diisi.");
		}
        $tab.="
        <table cellspacing=1 border=1 class=sortable><thead>
        <tr class=data>
        <td bgcolor=#DEDEDE align=center>No</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodept']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodebarang']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nodo']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nosipb']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kendaraan']."</td>            
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['sopir']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli']."</td>
        </tr></thead><tbody>
        ";
/*	$sDet="select a.tanggalkontrak,a.pembeli,a.komoditi,b.* from ".$dbname.".pmn_kontrakjual a inner join ".$dbname.".pabrik_timbangan on a.nokontrak=b.nokontrak where a.nokontrak='".$nokontrak."'";
*/
            $tgl1=explode("-",$_GET['tgl_dr']);
            $tangglAwl=$tgl1[2]."-".$tgl1[1]."-".$tgl1[0];
            $tgl2=explode("-",$_GET['tgl_samp']);
            $tangglSmp=$tgl2[2]."-".$tgl2[1]."-".$tgl2[0];

        $sDet="select a.notransaksi,a.tanggal,a.nodo,a.nosipb,a.beratbersih,a.nokendaraan,a.supir,a.kgpembeli,a.kodebarang, b.kodeorganisasi
              from ".$dbname.".pabrik_timbangan a 
			  left join ".$dbname.".organisasi b
			  on a.millcode = b.kodeorganisasi 
			  where substr(a.tanggal,1,10) between '".$tangglAwl."' and '".$tangglSmp."' and a.kodebarang like '%".$kdBrg3."%'
                  and a.nokontrak !='' and b.induk like '%".$pt3."%'  ".$whrNotranA."
              order by a.tanggal asc";
        $qDet=mysql_query($sDet) or die(mysql_error());
        $rCek=mysql_num_rows($qDet);
        if($rCek>0)
        {
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        $no+=1;
                        $tab.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rDet['kodeorganisasi']."</td>
                        <td>".$rDet['notransaksi']."</td>
                        <td>".tanggalnormal($rDet['tanggal'])."</td>
                        <td>".$optNmBrg[$rDet['kodebarang']]."</td>
                        <td>".$rDet['nodo']."</td>
                        <td>".$rDet['nosipb']."</td>
                        <td>".$rDet['nokendaraan']."</td>
                        <td>".ucfirst($rDet['supir'])."</td>
                        <td align=right>".number_format($rDet['beratbersih'],0)."</td>
                        <td align=right>".number_format($rDet['kgpembeli'],0)."</td>
                        </tr>";
						setIt($subtot['total'],0);
						setIt($subtotKga['totalKg'],0);
                        $subtot['total']+=$rDet['beratbersih'];
                        $subtotKga['totalKg']+=$rDet['kgpembeli'];
                }
                $tab.="<tr class=rowcontent><td colspan='9'>Total</td><td align=right>".number_format($subtot['total'],0)."</td><td align=right>".number_format($subtotKga['totalKg'],2)."</td></tr>";
        }
        else
        {
                $tab.="<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }
        $tab.="</tbody></table>";
        $nop_="rangePengiriman";
                        if(strlen($tab)>0)
                        {
                        if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                        @unlink('tempExcel/'.$file);
                        }
                        }
                        closedir($handle);
                        }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$tab))
                        {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                        }
                        else
                        {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                        }
                        fclose($handle);
                        }
        break;
        case'detailpdf':
        $no_kontrak=$_GET['no_kontrak'];
        class PDF extends FPDF
        { 
            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                                global $no_kontrak;


                                $sql="select nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept from ".$dbname.".pmn_kontrakjual where nokontrak='".$no_kontrak."'";
                                $query=mysql_query($sql) or die(mysql_error());
                                $res=mysql_fetch_assoc($query);

                                $sHed="select  a.tanggalkontrak,a.koderekanan,a.kodebarang from ".$dbname.".pmn_kontrakjual a where a.nokontrak='".$nokontrak."'";
                                $qHead=mysql_query($sHed) or die(mysql_error());
                                $rHead=mysql_fetch_assoc($qHead);
                                $sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$rHead['kodebarang']."'";
                                $qBrg=mysql_query($sBrg) or die(mysql_error());
                                $rBrg=mysql_fetch_assoc($qBrg);

                                $sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$rHead['koderekanan']."'";
                                $qCust=mysql_query($sCust) or die(mysql_error());
                                $rCust=mysql_fetch_assoc($qCust);	

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$res['kodept']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();

                $this->Ln();
                                $this->Ln();
                $this->SetFont('Arial','U',9);
                $this->Cell($width,$height, $_SESSION['lang']['detailPengiriman'],0,1,'C');	
                $this->Ln();	

                $this->SetFont('Arial','B',7);	
                $this->SetFillColor(220,220,220);
                    $this->Cell(3/100*$width,$height,'No',1,0,'C',1);
                    $this->Cell(10/100*$width,$height,$_SESSION['lang']['notransaksi'],1,0,'C',1);
                    $this->Cell(10/100*$width,$height,$_SESSION['lang']['tanggal'],1,0,'C',1);
                    $this->Cell(12/100*$width,$height,$_SESSION['lang']['nodo'],1,0,'C',1);
                    $this->Cell(16/100*$width,$height,$_SESSION['lang']['nosipb'],1,0,'C',1);
                    $this->Cell(11/100*$width,$height,$_SESSION['lang']['kendaraan'],1,0,'C',1);
                    $this->Cell(11/100*$width,$height,$_SESSION['lang']['sopir'],1,0,'C',1);
                    $this->Cell(12/100*$width,$height,$_SESSION['lang']['beratBersih'],1,0,'C',1);
                    $this->Cell(16/100*$width,$height,$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['Pembeli'],1,1,'C',1);

            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
                $pdf->AddPage();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',7);
                // Get No Kontrak Internal
				$qKontrak = selectQuery($dbname,'pmn_suratperintahpengiriman','nodo,nokontrak,nokontrakinternal',
										"nokontrak = '".$no_kontrak."' or
										nokontrakinternal = '".$no_kontrak."'");
				$resKontrak = fetchData($qKontrak);
				if(empty($resKontrak)) {
					$optKontrak = array($no_kontrak);
				} else {
					$optKontrak = array();
				}
				$optSipb = array();
				foreach($resKontrak as $row) {
					$optKontrak[] = $row['nokontrak'];
					if(!empty($row['nokontrakinternal'])) {
						$optKontrak[] = $row['nokontrakinternal'];
					}
					$optSipb[] = $row['nodo'];
				}
				
                $sDet="select notransaksi,tanggal,nodo,nosipb,beratbersih,nokendaraan,supir,kgpembeli from ".$dbname.".pabrik_timbangan where nokontrak in ('".implode("','",$optKontrak)."')  ".$whrNotran."";
                if(!empty($optSipb)) {$sDet .= " AND nosipb in ('".implode("','",$optSipb)."')";}
                $qDet=mysql_query($sDet) or die(mysql_error());
                while($rDet=mysql_fetch_assoc($qDet))
                {
                        $no+=1;

                        $pdf->Cell(3/100*$width,$height,$no,1,0,'C',1);
                        $pdf->Cell(10/100*$width,$height,$rDet['notransaksi'],1,0,'L',1);	
                        $pdf->Cell(10/100*$width,$height,tanggalnormal($rDet['tanggal']),1,0,'L',1);	
                        $pdf->Cell(12/100*$width,$height,$rDet['nodo'],1,0,'L',1);		
                        $pdf->Cell(16/100*$width,$height,$rDet['nosipb'],1,0,'L',1);						
                        $pdf->Cell(11/100*$width,$height,$rDet['nokendaraan'],1,0,'L',1);		
                        $pdf->Cell(11/100*$width,$height,ucfirst($rDet['supir']),1,0,'L',1);		
                        $pdf->Cell(12/100*$width,$height,number_format($rDet['beratbersih'],2),1,0,'R',1);
                        $pdf->Cell(16/100*$width,$height,number_format($rDet['kgpembeli'],2),1,1,'R',1);
                        $subtot+=$rDet['beratbersih'];
                        $subtot2+=$rDet['kgpembeli'];
                }
                $pdf->Cell(73/100*$width,$height,"Total",1,0,'R',1);			
                $pdf->Cell(12/100*$width,$height,number_format($subtot,2),1,0,'R',1);
                $pdf->Cell(16/100*$width,$height,number_format($subtot2,2),1,1,'R',1);
        $pdf->Output();
        break;
        default:
        break;
}

?>