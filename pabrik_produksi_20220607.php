<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_produksi.js'></script>
<?php
include('master_mainMenu.php');


OPEN_BOX('',"<b>".$_SESSION['lang']['produksipabrik'].":</b>");
//get org
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res))
{
	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
echo "<fieldset style='width:750px;'>
        <legend>".$_SESSION['lang']['form'].":</legend>
		<table><tr><td>
		
		<table>
		   <tr>
		     <td>
			    ".$_SESSION['lang']['kodeorganisasi']."
			 </td>
		     <td>
			    <select id=kodeorg>".$optorg."</select>
			 </td>
		   </tr>
		   <tr> 
			 <td>".$_SESSION['lang']['tanggal']."</td>
			 <td><input type=text class=myinputtext onchange=getData() id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\">
			 </td>	
		     <td>		 
		   </tr>
		   <tr>
		     <td>
			    ".$_SESSION['lang']['sisatbskemarin']."
			 </td>
		     <td>
			    <input type=text id=sisatbskemarin value=0 class=myinputtextnumber onblur=hitungSisa() maxlength=10 size=10 onkeypress=\"return angka_doang(event);\">Kg.
			 </td>
		   </tr>
		   <tr> 
		     <td>
			    ".$_SESSION['lang']['tbsmasuk']."
			 </td>
			 <td>
			    <input type=text id=tbsmasuk value=0  class=myinputtextnumber onblur=hitungSisa()  maxlength=10 size=10 onkeypress=\"return angka_doang(event);\">Kg. 
			 </td>	 		 
		   </tr>		
		   <tr>
		     <td>
			    ".$_SESSION['lang']['tbsdiolah']."
			 </td>
		     <td>
			    <input type=text id=tbsdiolah value=0  class=myinputtextnumber onkeyup=hitungSisa()  maxlength=10 size=10 onkeypress=\"return angka_doang(event);\">Kg. 
			 </td>		 
		   </tr>
		   <tr>
		     <td>
			    ".$_SESSION['lang']['sisa']."
			 </td>
		     <td>
			    <input type=text id=sisa  value=0 class=myinputtextnumber  maxlength=10 size=10 readonly>Kg. 
			 </td>		 
		   </tr>";
           echo" <tr>
		     <td>% USB Before Collector
			 </td>
		     <td>
			    <input type=text id=usbbefore  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
			 </td>		 
		   </tr>	  
           <tr>
		     <td>% USB After Collector
			 </td>
		     <td>
			    <input type=text id=usbafter  value=0 class=myinputtextnumber  maxlength=10 size=10 >% 
			 </td>		 
		   </tr>	
           <tr>
		     <td>% Oil Diluted Crude Oil
			 </td>
		     <td>
			    <input type=text id=oildiluted  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
			 </td>		 
		   </tr>	
           <tr>
		     <td>% Oil in underflow (CST)
			 </td>
		     <td>
			    <input type=text id=oilin  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
			 </td>		 
		   </tr>	
           <tr>
		     <td>% Oil in Heavy Phase - S/D
			 </td>
		     <td>
			    <input type=text id=oilinheavy  value=0 class=myinputtextnumber  maxlength=10 size=10 >%
			 </td>		 
		   </tr>	
           <tr>
		     <td>CaCO3
			 </td>
		     <td>
			    <input type=text id=caco  value=0 class=myinputtextnumber  maxlength=10 size=10 >
			 </td>		 
		   </tr>
           <tr>
		     <td>Limbah</td>
		     <td>
			    <input type=text id=limbah value=0 class=myinputtextnumber maxlength=10 size=10 >
			 </td>		 
		   </tr>
           <tr>
		     <td>Jam Pompa</td>
		     <td>
			    <input type=text id=jampompa value=0 class=myinputtextnumber maxlength=10 size=10 >
			 </td>		 
		   </tr>
           <tr>
		     <td>Land Aplikasi</td>
		     <td>
			    <input type=text id=landaplikasi value=0 class=myinputtextnumber maxlength=10 size=10 >
			 </td>		 
		   </tr>";
	  echo"</table>	  
	  </td>
	  <td valign=top>  
  	<table>
		<tr>
		<td> 
		 <fieldset><legend>".$_SESSION['lang']['cpo']."</legend>
		 <table>
		 <tr><td>
			    ".$_SESSION['lang']['cpo']."(Kg)
			 </td>
			 <td>
			    <input type=text id=oercpo  value=0 onblur=periksaOERCPO(this) class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\">Kg. 
			 </td>
		  </tr>
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kotoran']."
			 </td>
		     <td>
			    <input type=text id=dirtcpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>	
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kadarair']."
			 </td>
			 <td>
			    <input type=text id=kadaraircpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>
		 <tr>
		     <td>
			    FFa
			 </td>
		     <td>
			    <input type=text id=ffacpo value=0 onblur=periksaCPO(this)   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>			 
		 </tr>
		 <tr>
		     <td>
			    Dobi
			 </td>
		     <td>
			    <input type=text id=dobicpo value=0 class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
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
                 <tr>
                    <td>USB</td>
                    <td>
                       <input type=text id=usbcpo  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> 
                    </td>
		  </tr>

                    
		 <tr>
                    <td>Fruit In Empty Bunch</td>
                    <td>
                       <input type=text id=fruitineb  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> 
                    </td>
		  </tr>
		 <tr>
		     <td>Empty Bunch Stalk 
			 </td>
		     <td>
			    <input type=text id=ebstalk value=0    class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">  
			 </td>
		 </tr>	
		 <tr>
		     <td> Fibre From Press Cake
			 </td>
			 <td>
			    <input type=text id=fibre value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">  
			 </td>
		 </tr>	
		 <tr>
		     <td>Nut From Press Cake
			 </td>
		     <td>
			    <input type=text id=nut value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">  
			 </td>			 
		 </tr>	
                  <tr>
		     <td>Effluent
			 </td>
		     <td>
			    <input type=text id=effluent value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> 
			 </td>			 
		 </tr>	
                   <tr>
		     <td>Solid Decanter
			 </td>
		     <td>
			    <input type=text id=soliddecanter value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> 
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
			    <input type=text id=oerpk  value=0 onblur=periksaOERPK(this)  class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\">Kg. 
			 </td>
		  </tr>
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kotoran']."
			 </td>
		     <td>
			    <input type=text id=dirtpk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>	
		 <tr>
		     <td>
			    ".$_SESSION['lang']['kadarair']."
			 </td>
			 <td>
			    <input type=text id=kadarairpk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>
		 </tr>	
		 <tr>
		     <td>
			    FFa
			 </td>
		     <td>
			    <input type=text id=ffapk  value=0 onblur=periksaPK(this)  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">%. 
			 </td>			 
		 </tr>
		 <tr>
		     <td>&nbsp</td>
		     <td>&nbsp</td>
		 </tr>	
		 <tr>
		     <td></td>
		 </tr>	
                 
		</table>
		</fieldset>
		
		</td>
		</tr>
                <tr>
		<td> 
		 <fieldset><legend>".$_SESSION['lang']['kernel']." Loses</legend>
		 <table>
                 
                  <tr>
                    <td>USB</td>
                    <td>
                       <input type=text id=usbpk  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\"> 
                    </td>
		  </tr>

		 <tr><td>Fruit In Empty Bunch

			 </td>
			 <td>
			    <input type=text id=fruitinebker  value=0   class=myinputtextnumber maxlength=7 size=10 onkeypress=\"return angka_doang(event);\">
			 </td>
		  </tr>
		 <tr>
		     <td>Fibre Cyclone
			 </td>
		     <td>
			    <input type=text id=cyclone  value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> 
			 </td>
		 </tr>	
		 <tr>
		     <td>LTDS
			 </td>
			 <td>
			    <input type=text id=ltds  value=0   class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\"> 
			 </td>
		 </tr>	
		 <tr>
		     <td>Claybath
			 </td>
		     <td>
			    <input type=text id=claybath  value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">
			 </td>			 
		 </tr>	
                 

                 <tr>
		     <td>Hydrocyclone	
			 </td>
		     <td>
			    <input type=text id=hydrocyclone  value=0  class=myinputtextnumber maxlength=5 size=10 onkeypress=\"return angka_doang(event);\">
			 </td>			 
		 </tr>	
                 
		<tr>
			<td>Kernel Pecah</td>
			<td>
				<input type=text id=kernelpecah value=0 class=myinputtextnumber maxlength=8 size=10 onkeypress=\"return angka_doang(event);\">
			</td>			 
		</tr>	
		<tr>
			<td>Jam Olah</td>
			<td>
				<input type=text id=kerneljamolah value=0 class=myinputtextnumber maxlength=8 size=10 onkeypress=\"return angka_doang(event);\">
			</td>			 
		</tr>	
		<tr>
			<td>Kapasitas Olah</td>
			<td>
				<input type=text id=kernelkapasitasolah value=0 class=myinputtextnumber maxlength=8 size=10 onkeypress=\"return angka_doang(event);\">
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
	
		<center>
			<button class=mybutton onclick=simpanProduksi()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
		</center>
	  </fieldset>
	 ";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
      <table class=sortable cellspacing=1 border=0 width=100%>
	    <thead>
		  <tr class=rowheader>
		   <td rowspan=2 align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['sisatbskemarin']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tbsmasuk']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tbsdiolah']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['sisa']." (Kg.)</td>
		   <td colspan=6 align=center>".$_SESSION['lang']['cpo']."
		   </td>
		   <td colspan=6 align=center>".$_SESSION['lang']['kernel']."
		   </td>
		   <td rowspan=2 align=center>Action</td>	   
		  </tr>  
		  <tr class=rowheader> 
		   <td align=center>".$_SESSION['lang']['cpo']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa)(%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
                       <td align=center>Loses</td>
		   
		   <td align=center>".$_SESSION['lang']['kernel']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa) (%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
                       <td align=center>Loses</td>
		  </tr>
		</thead>
		<tbody id=container>";
$str="select a.* from ".$dbname.".pabrik_produksi a
      where kodeorg='".$_SESSION['empl']['lokasitugas']."'
      order by a.tanggal desc limit 31";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
    $tCpoLoses=$bar->usbcpo+$bar->fruitineb+$bar->ebstalk+$bar->fibre+$bar->nut+$bar->effluent+$bar->soliddecanter;
                            $tKernelLoses=$bar->usbpk+$bar->fruitinebker+$bar->cyclone+$bar->ltds+$bar->claybath+$bar->hydrocyclone;
                             
    
    $drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
		 echo"<tr class=rowcontent >
		   <td ".$drcl.">".$bar->kodeorg."</td>
		   <td ".$drcl.">".tanggalnormal($bar->tanggal)."</td>
		   <td ".$drcl." align=right>".number_format($bar->sisatbskemarin,0,'.',',')."</td>
		   <td ".$drcl." align=right>".number_format($bar->tbsmasuk,0,'.',',')."</td>
		   <td ".$drcl." align=right>".number_format($bar->tbsdiolah,0,'.',',.')."</td>
		   <td ".$drcl." align=right>".number_format($bar->sisahariini,0,'.',',')."</td>
		   
		   <td ".$drcl." align=right>".number_format($bar->oer,2,'.',',')."</td>
		   <td ".$drcl." align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td ".$drcl." align=right>".$bar->ffa."</td>
		   <td ".$drcl." align=right>".$bar->kadarkotoran."</td>
		   <td ".$drcl." align=right>".$bar->kadarair."</td>
                        <td ".$drcl." align=right>".$tCpoLoses."</td>
		   
		   <td ".$drcl." align=right>".number_format($bar->oerpk,2,'.',',')."</td>
		   <td ".$drcl." align=right>".(@number_format(@$bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td ".$drcl." align=right>".$bar->ffapk."</td>
		   <td ".$drcl." align=right>".$bar->kadarkotoranpk."</td>
		   <td ".$drcl." align=right>".$bar->kadarairpk."</td>
		    <td ".$drcl." align=right>".$tKernelLoses."</td>	   
		   <td>
			 <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->sisatbskemarin."','".$bar->tbsmasuk."','".$bar->tbsdiolah."'
				,'".$bar->sisahariini."','".$bar->oer."','".$bar->kadarkotoran."','".$bar->kadarair."','".$bar->ffa."','".$bar->oerpk."','".$bar->kadarkotoranpk."'
				,'".$bar->kadarairpk."','".$bar->ffapk."','".$bar->usbbefore."','".$bar->usbafter."','".$bar->oildiluted."','".$bar->oilin."','".$bar->oilinheavy."'
				,'".$bar->caco."','".$bar->fruitineb."','".$bar->ebstalk."','".$bar->fibre."','".$bar->nut."','".$bar->effluent."','".$bar->soliddecanter."'
				,'".$bar->fruitinebker."','".$bar->cyclone."','".$bar->ltds."','".$bar->claybath."','".$bar->usbcpo."','".$bar->usbpk."','".$bar->hydrocyclone."'
				,'".$bar->dobicpo."','".$bar->kernelpecah."','".$bar->kerneljamolah."','".$bar->kernelkapasitasolah."','".$bar->limbah."','".$bar->jampompa."'
				,'".$bar->landaplikasi."')\">&nbsp

		     <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delProduksi('".$bar->kodeorg."','".$bar->tanggal."','".(isset($bar->kodebarang)? $bar->kodebarang:'')."');\">
		   </td>
		  </tr>";	
}	  
		
echo"	
		</tbody>
		<tfoot>
		</tfoot>
	  </table>
	  </fieldset>";
CLOSE_BOX();

close_body();
?>