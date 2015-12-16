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
    case'getCpo':
        
        //Kg CPO : X + AZ + Z –Y
        
        #stok CPO kemarin
        $iCpoKmrn="select sum(kuantitas) as cpo from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tglKmrn."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='CPO')";
        $nCpoKmrn=  mysql_query($iCpoKmrn) or die (mysql_error($conn));
        $dCpoKmrn=  mysql_fetch_assoc($nCpoKmrn);
            $xCpo=$dCpoKmrn['cpo'];
            
        #cpo sekarang
        $iCpoSkrg="select sum(kuantitas) as cpo from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tgl."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='CPO')";
        $nCpoSkrg=  mysql_query($iCpoSkrg) or die (mysql_error($conn));
        $dCpoSkrg=  mysql_fetch_assoc($nCpoSkrg);
            $yCpo=$dCpoSkrg['cpo'];    
          
        $tglKmrnJamKirim=$tglKmrn.' 06:59:59';
        $tglSkrgJamKirim=$tgl.' 07:00:00';
        
        #cpo kirim
        $iCpoKirim="select sum(beratbersih) as beratbersih from ".$dbname.".pabrik_timbangan where"
                . " kodebarang='40000001' and millcode='".$kodeorg."' "
                . " and tanggal > '".$tglKmrnJamKirim."' and tanggal < '".$tglSkrgJamKirim."'  "; 
        $nCpoKirim=  mysql_query($iCpoKirim) or die (mysql_error($conn));
        $dCpoKirim=  mysql_fetch_assoc($nCpoKirim);
            $zCpo=$dCpoKirim['beratbersih'];
        //exit("Error:$iCpoKirim");
            //and a.tanggal>='".$tanggal1."' and a.tanggal<='".$tanggal2."'  
        
        #pembersihan tangki
        $iBa="select sum(jumlah) as jumlah from ".$dbname.".pabrik_pembersihantangki where "
             . " kodebarang='40000001' and kodeorg='".$kodeorg."' "
             . " and tanggal > '".$tglKmrnJamKirim."' and tanggal < '".$tglSkrgJamKirim."'  ";  
        $nBa=  mysql_query($iBa) or die (mysql_error($conn));
        $dBa=  mysql_fetch_assoc($nBa);
            $azCpo=$dBa['jumlah'];
            
        $kgCpo=$xCpo+$zCpo+$azCpo-$yCpo;
        echo $kgCpo;
        
        //echo 3;
    break;


    case'getKernel':
        //Kg CPO : X + AZ + Z –Y
        
        #stok kernel kemarin
        $iKerKmrn="select sum(kernelquantity) as kernel from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tglKmrn."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='KER')";
        $nKerKmrn=  mysql_query($iKerKmrn) or die (mysql_error($conn));
        $dKerKmrn=  mysql_fetch_assoc($nKerKmrn);
            $xKer=$dKerKmrn['kernel'];
            
        #kernel sekarang
        $iKerSkrg="select sum(kernelquantity) as ker from ".$dbname.".pabrik_masukkeluartangki "
            . " where tanggal='".$tgl."' "
            . " and kodetangki in (select kodetangki from ".$dbname.".pabrik_5tangki "
            . " where kodeorg='".$kodeorg."' and komoditi='KER')";
        $nKerSkrg=  mysql_query($iKerSkrg) or die (mysql_error($conn));
        $dKerSkrg=  mysql_fetch_assoc($nKerSkrg);
            $yKer=$dKerSkrg['ker'];   
          
        $tglKmrnJamKirim=$tglKmrn.' 06:59:59';
        $tglSkrgJamKirim=$tgl.' 07:00:00';
        
        #kernel kirim
        $iKerKirim="select sum(beratbersih) as beratbersih from ".$dbname.".pabrik_timbangan where"
                . " kodebarang='40000002' and millcode='".$kodeorg."' "
                . " and tanggal > '".$tglKmrnJamKirim."' and tanggal < '".$tglSkrgJamKirim."'  "; 
        $nKerKirim=  mysql_query($iKerKirim) or die (mysql_error($conn));
        $dKerKirim=  mysql_fetch_assoc($nKerKirim);
            $zKer=$dKerKirim['beratbersih'];
            
        #pembersihan tangki
        $iBa="select sum(jumlah) as jumlah from ".$dbname.".pabrik_pembersihantangki where "
             . " kodebarang='40000002' and kodeorg='".$kodeorg."' "
             . " and tanggal > '".$tglKmrnJamKirim."' and tanggal < '".$tglSkrgJamKirim."'  ";  
        $nBa=  mysql_query($iBa) or die (mysql_error($conn));
        $dBa=  mysql_fetch_assoc($nBa);
            $azKer=$dBa['jumlah'];
            
        $kgKer=$xKer+$zKer+$azKer-$yKer;
        echo $kgKer;
    break;


    case'getData':
        
        ##bentuk tanggal kemarin
        $tgl =  tanggalsystem($_POST['tanggal']);
        $tglKmrn = strtotime('-1 day',strtotime($tgl));
        $tglKmrn = date('Y-m-d', $tglKmrn);
        
        #ambil sisa tbs kemarin
        $iSisa="select sisahariini from ".$dbname.".pabrik_produksi where kodeorg='".$_POST['kodeorg']."' and "
                . " tanggal='".$tglKmrn."' ";
        $nSisa=mysql_query($iSisa) or die (mysql_errno($conn));
        $dSisa=mysql_fetch_assoc($nSisa);
            $tbsKmrn=$dSisa['sisahariini'];
            
        #ambil produksi hari ini
        $iTbs="select sum(beratbersih) as beratbersih  from ".$dbname.".pabrik_timbangan where millcode='".$_POST['kodeorg']."' and "
                . " tanggal like '%".tanggalsystemn($_POST['tanggal'])."%' and kodebarang='40000003'"; 
        $nTbs=  mysql_query($iTbs) or die (mysql_errno($conn));
        $dTbs=  mysql_fetch_assoc($nTbs);
            $tbsHr=$dTbs['beratbersih'];
            
        if($tbsKmrn!='')
            $tbsKmrn=$tbsKmrn;
        else
            $tbsKmrn=0;
        
        echo $tbsKmrn."###".$tbsHr;
            
        
    break;
    
    
    
    case'getDetailPP':
        $str="select * from ".$dbname.".pabrik_produksi
      where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$_POST['tgl']."'";
//echo $str;
        $res=mysql_query($str) or die(mysql_error($conn));
        $rdata=mysql_fetch_assoc($res);
        
        
        $tCpoLoses=$rdata['usbcpo']+$rdata['fruitineb']+$rdata['ebstalk']+$rdata['fibre']+$rdata['nut']+$rdata['effluent']+$rdata['soliddecanter'];
        $tKernelLoses=$rdata['usbpk']+$rdata['fruitinebker']+$rdata['cyclone']+$rdata['ltds']+$rdata['claybath']+$rdata['hydrocyclone'];
                             
        
        echo "<fieldset style='width:700px;'>
                <legend>".$_SESSION['lang']['data'].":</legend>
                        <table><tr><td>

                        <table>
                           <tr>
                             <td>
                                    ".$_SESSION['lang']['kodeorganisasi']."
                                 </td>
                             <td>".$rdata['kodeorg']."
                                 </td>
                           </tr>
                           <tr> 
                                 <td>".$_SESSION['lang']['tanggal']."</td>
                                 <td>".tanggalnormal($rdata['tanggal'])."
                                 </td>	
                             <td>		 
                         </tr>
                           <tr>
                             <td>
                                    ".$_SESSION['lang']['sisatbskemarin']."
                                 </td>
                             <td>".number_format($rdata['sisatbskemarin'],0)."
                                 </td>
                           </tr>
                           <tr> 
                             <td>
                                    ".$_SESSION['lang']['tbsmasuk']."
                                 </td>
                                 <td>
                                    ".number_format($rdata['tbsmasuk'],0)."
                                 </td>	 		 
                         </tr>		
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['tbsdiolah']."
                                 </td>
                             <td>
                                    ".number_format($rdata['tbsdiolah'],0)."
                                 </td>		 
                         </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['sisa']."
                                 </td>
                                 <td>   ".number_format($rdata['sisahariini'],0)."
                                 </td>		 
                         </tr>	";
                       echo" <tr>
                             <td>% USB Before Collector
                                 </td>
                             <td>".$rdata['usbbefore']." %
                                 </td>		 
                         </tr>	  
                          <tr>
                             <td>% USB After Collector
                                 </td>
                             <td>".$rdata['usbafter']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil Diluted Crude Oil
                                 </td>
                             <td>".$rdata['oildiluted']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil in underflow (CST)
                                 </td>
                             <td>".$rdata['oilin']." %
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>% Oil in Heavy Phase - S/D
                                 </td>
                             <td>".$rdata['oilinheavy']." % 
                                 </td>		 
                         </tr>	
                          <tr>
                             <td>CaCO3
                                 </td>
                             <td>".$rdata['caco']." KG
                                 </td>		 
                         </tr>	";
                  echo"</table>	  
                  </td>
                  <td valign=top>  
                <table>
                        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['cpo']."</legend>
                         <table>
                         <tr><td>".$_SESSION['lang']['cpo']."(Kg)
                                 </td>
                                 <td>
                                   ".$rdata['oer']."
                                 </td>
                          </tr>
                          <tr><td>".$_SESSION['lang']['oer']."
                                 </td>
                                 <td>
                                   ".(@number_format($rdata['oer']/$rdata['tbsdiolah']*100,2,'.',','))."
                                 </td>
                          </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kotoran']."
                                 </td>
                             <td>
                                  ".$rdata['kadarkotoran']."%
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kadarair']."
                                 </td>
                                 <td>
                                   ".$rdata['kadarair']."%.
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    FFa
                                 </td>
                             <td>
                                  ".$rdata['ffa']." %. 
                                 </td>			 
                         </tr>		   	   
                        </table>
                        </fieldset>

                        </td>
                        </tr>

        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['cpo']." Loses</legend>
                         <table>
                         <tr><td>USB

                                 </td>
                                 <td>".$rdata['usbcpo']." 
                                 </td>
                          </tr>
                         <tr><td>Fruit In Empty Bunch
                                 </td>
                                 <td>
                                    ".$rdata['fruitineb']." KG
                                 </td>
                          </tr>
                         <tr>
                             <td>Empty Bunch Stalk 
                                 </td>
                             <td>".$rdata['ebstalk']."
                                 </td>
                         </tr>	
                         <tr>
                             <td>Fibre From Press Cake
                                 </td>
                                 <td>".$rdata['fibre']."
                                 </td>
                         </tr>	
                         <tr>
                             <td>Nut From Press Cake
                                 </td>
                             <td>".$rdata['nut']."
                                 </td>			 
                         </tr>	
                          <tr>
                             <td>Effluent
                                 </td>
                             <td>".$rdata['effluent']."
                                 </td>			 
                         </tr>	
                           <tr>
                             <td>Solid Decanter
                                 </td>
                             <td>".$rdata['soliddecanter']."
                                 </td>			 
                         </tr>	
                          <tr>
                             <td><b>Total</b>
                                 </td>
                             <td><b>".$tCpoLoses."</b>
                                 </td>			 
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
                         <fieldset><legend>".$_SESSION['lang']['kernel']."</legend>
                         <table>
                         <tr><td>
                                    ".$_SESSION['lang']['kernel']."(Kg)
                                 </td>
                                 <td>
                                    ".$rdata['oerpk']." Kg.
                                 </td>
                          </tr>
                          <tr><td>
                                    ".$_SESSION['lang']['oerpk']."
                                 </td>
                                 <td>
                                    ".(@number_format($bar->oerpk/$bar->tbsdiolah*100,2,'.',','))." 
                                 </td>
                          </tr>
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kotoran']."
                                 </td>
                             <td>".$rdata['kadarkotoranpk']." %
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    ".$_SESSION['lang']['kadarair']."
                                 </td>
                                 <td>".$rdata['kadarairpk']." %. 
                                 </td>
                         </tr>	
                         <tr>
                             <td>
                                    FFa
                                 </td>
                             <td>".$rdata['ffapk']." %.
                                 </td>			 
                         </tr>	

                        </table>
                        </fieldset>

                        </td>
                        </tr>
                        <tr>
                        <td> 
                         <fieldset><legend>".$_SESSION['lang']['kernel']." Loses</legend>
                         <table>
                         <tr><td>USB

                                 </td>
                                 <td>".$rdata['usbpk']." 
                                 </td>
                          </tr>
                         <tr><td>Fruit In Empty Bunch

                                 </td>
                                 <td>".$rdata['fruitinebker']." KG
                                 </td>
                          </tr>
                         <tr>
                             <td>Fibre Cyclone
                                 </td>
                             <td>".$rdata['cyclone']."
                                 </td>
                         </tr>	
                         <tr>
                             <td>LTDS
                                 </td>
                                 <td>".$rdata['ltds']."
                                 </td>
                         </tr>	
                         <tr>
                             <td>Claybath
                                 </td>
                             <td>".$rdata['claybath']."
                                 </td>			 
                         </tr>
                         
                            <tr>
                             <td><b>Total</b>
                                 </td>
                             <td><b>".$tKernelLoses."</b>
                                 </td>			 
                         </tr>

                        </table>
                        </fieldset>

                        </td>
                        </tr>
                        </table>	


                </td>
                </tr>	  

                </table>	
                  </fieldset>
                 ";
        
    break;
}




?>