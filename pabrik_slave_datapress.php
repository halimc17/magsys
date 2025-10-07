<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');


$method=$_POST['method'];


########cara hitung tanggal kemarin###############
        $tgl =  tanggalsystem($_POST['tanggal']);//merubah dari 10-10-2014 menjadi 20141010
        $newdate = strtotime('-1 day',strtotime($tgl));
        $newdate = date('Y-m-d', $newdate);
        
$kodeorg=$_POST['kodeorg'];  
$tgl=tanggalsystemn($_POST['tanggal']);

#tgl kmrn
$tglKmrn = strtotime('-1 day',strtotime($tgl));
$tglKmrn = date('Y-m-d', $tglKmrn);

//exit("Error:$tgl._.$tglKmrn");


switch($method)
{
    case'getData':
        ##bentuk tanggal kemarin
        $tgl =  tanggalsystem($_POST['tanggal']);
        $tglKmrn = strtotime('-1 day',strtotime($tgl));
        $tglKmrn = date('Y-m-d', $tglKmrn);
        
        #ambil sisa kemarin
        $iSisa="select airsisa from ".$dbname.".pabrik_datapress where kodeorg='".$_POST['kodeorg']."' and "
                . " tanggal='".$tglKmrn."'";
		//exit('Warning: '.$iSisa);
        $nSisa=mysql_query($iSisa) or die (mysql_errno($conn));
        $dSisa=mysql_fetch_assoc($nSisa);
            $airKmrn=$dSisa['airsisa'];
            
        $tbsHr=0;
        /*
		#ambil produksi hari ini
        $iTbs="select sum(beratbersih) as beratbersih  from ".$dbname.".pabrik_timbangan where millcode='".$_POST['kodeorg']."' and "
                . " tanggal like '%".tanggalsystemn($_POST['tanggal'])."%' and kodebarang='40000003'"; 
        $nTbs=  mysql_query($iTbs) or die (mysql_errno($conn));
        $dTbs=  mysql_fetch_assoc($nTbs);
            $tbsHr=$dTbs['beratbersih'];
        */  
        if($airKmrn=='')
            $airKmrn=0;
        
        echo $airKmrn."###".$tbsHr;
    break;
    
    
    
    case'getDetailPA':
        $str="select * from ".$dbname.".pabrik_datapress
			where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$_POST['tgl']."'";

		$res=mysql_query($str) or die(mysql_error($conn));
        $rdata=mysql_fetch_assoc($res);
       
		$airkemarin=$rdata['airsisa']-$rdata['airclarifier']+$rdata['airboiler']+$rdata['airproduksi']+$rdata['airpembersihan']+$rdata['airdomestik'];
		
echo "<fieldset style='width:860px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td>".$rdata['kodeorg']."</td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>".tanggalnormal($rdata['tanggal'])."</td>
						</tr>
					</table>
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Tek. Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td>".$rdata['tekpressp1']."</td>
										</tr>
										<tr>
											<td>P2</td>
											<td>".$rdata['tekpressp2']."</td>
										</tr>
										<tr>
											<td>P3</td>
											<td>".$rdata['tekpressp3']."</td>
										</tr>
										<tr>
											<td>P4</td>
											<td>".$rdata['tekpressp4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Suhu Digester</legend>
									<table>
										<tr>
											<td>D1</td>
											<td>".$rdata['suhud1']."</td>
										</tr>
										<tr>
											<td>D2</td>
											<td>".$rdata['suhud2']."</td>
										</tr>
										<tr>
											<td>D3</td>
											<td>".$rdata['suhud3']."</td>
										</tr>
										<tr>
											<td>D4</td>
											<td>".$rdata['suhud4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Jam Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td>".$rdata['jampressp1']."</td>
										</tr>
										<tr>
											<td>P2</td>
											<td>".$rdata['jampressp2']."</td>
										</tr>
										<tr>
											<td>P3</td>
											<td>".$rdata['jampressp3']."</td>
										</tr>
										<tr>
											<td>P4</td>
											<td>".$rdata['jampressp4']."</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>

				<td valign=top>
  					<table>
						<tr>
							<td>
								<fieldset><legend>Air</legend>
									<table>
										<tr>
											<td>Air Sisa kemarin (Bak Basin)</td>
											<td>".$airkemarin." M3</td>
										</tr>
										<tr>
											<td>Air Clarifier Tank</td>
											<td>".$rdata['airclarifier']." M3</td>
										</tr>	
										<tr>
											<td>Air Boiler</td>
											<td>".$rdata['airboiler']." M3</td>
										</tr>	
										<tr>
											<td>Air Produksi</td>
											<td>".$rdata['airproduksi']." M3</td>
										</tr>	
										<tr>
											<td>Air Pembersihan</td>
											<td>".$rdata['airpembersihan']." M3</td>
										</tr>	
										<tr>
											<td>Air Domestik Camp</td>
											<td>".$rdata['airdomestik']." M3</td>
										</tr>	
										<tr>
											<td>Air Sisa (Bak Basin)</td>
											<td>".$rdata['airsisa']." M3</td>
										</tr>
									</table>
								</fieldset>
							</td>
						</tr>
					</table>	
				</td>
			</tr>	  
		</table>
	</fieldset>";
    break;
}
?>
