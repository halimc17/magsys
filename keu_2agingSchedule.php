<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_2agingSchedule.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['usiahutang']).'</b>');

	
//=================ambil PT;  


if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT'  order by namaorganisasi";

$optStatus="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Pusat</option>";
$optStatus.="<option value='1'>Lokal</option>";

}//and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'
else
{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT' and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'  order by namaorganisasi";
  
$optStatus.="<option value='1'>Lokal</option>";
}

$res=mysql_query($str);

while($bar=mysql_fetch_object($res))
{
	$optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}


$optsupkontran="<option value=''>".$_SESSION['lang']['all']."</option>";
$optsupkontran.="<option value='S'>Supllier</option>";
$optsupkontran.="<option value='K'>".$_SESSION['lang']['kontraktor']."</option>";
$optsupkontran.="<option value='T'>Transportir</option>";
//=================ambil gudang;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
		where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
		or tipe='HOLDING')  and induk!=''";
//$str="select distinct a.kodeorg,b.namaorganisasi from ".$dbname.".setup_periodeakuntansi a
//      left join ".$dbname.".organisasi b
//	  on a.kodeorg=b.kodeorganisasi
//     where b.tipe='KEBUN'
//	  order by namaorganisasi";
$res=mysql_query($str);
$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
$optper="<option value=''>".$_SESSION['lang']['all']."</option>";

while($bar=mysql_fetch_object($res))
{
#	$optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}

echo"<fieldset>
     <legend>".$_SESSION['lang']['usiahutang']."</legend>
         
     <table>
        <tr>
            <td>".$_SESSION['lang']['pt']."</td>
            <td>:</td>
            <td><select id=pt style='width:200px;' >".$optpt."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['po']."</td>
            <td>:</td>
            <td><select id=statuspo>".$optStatus."</select></td>
        </tr>
        <tr>
            <td>".$_SESSION['lang']['supplier']." / ".$_SESSION['lang']['kontraktor']." / Transportir</td>
            <td>:</td>
            <td><select id=supkontran>".$optsupkontran."</select></td> 
        </tr>
        <tr>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type=\"text\" value=\"".$tanggalpivot=date('d-m-Y')."\" class=\"myinputtext\" id=\"tanggalpivot\" name=\"tanggalpivot\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:100px;\" /></td>
        </tr>
        <tr>
            <td><button class=mybutton onclick=getUsiaHutang()>".$_SESSION['lang']['proses']."</button></td>
            <td><select id=gudang hidden style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select></td>
        </tr>
     </table>

	
	 </fieldset>";
CLOSE_BOX();
/* ".$_SESSION['lang']['pt']." : "."<select id=pt style='width:200px;' >".$optpt."</select>
	 PO Lokal/Pusat : "."<select id=statuspo>".$optStatus."</select><select id=gudang hidden style='width:150px;' onchange=hideById('printPanel')>".$optgudang."</select>
<input type=\"text\" value=\"".$tanggalpivot=date('d-m-Y')."\" class=\"myinputtext\" id=\"tanggalpivot\" name=\"tanggalpivot\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:100px;\" />
	 <button class=mybutton onclick=getUsiaHutang()>".$_SESSION['lang']['proses']."</button>*/
//			  <td rowspan=2 align=center width=60>".$_SESSION['lang']['nilaiinvoice']."</td>
OPEN_BOX('','Result:');
echo"<span id=printPanel style='display:none;'>
     <img onclick=fisikKeExcel(event,'keu_laporanUsiaHutang_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDF(event,'keu_laporanUsiaHutang_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>    
	 <div style='width:100%;height:359px;overflow:scroll;'>
       <table class=sortable cellspacing=1 border=0>
	     <thead>
		    <tr>
			  <td rowspan=2 align=center width=50>".$_SESSION['lang']['nourut']."</td>
			  <td rowspan=2 align=center width=50>".$_SESSION['lang']['tanggal']."</td>
			  <td rowspan=2 align=center width=200>".$_SESSION['lang']['noinvoice']."<br>".$_SESSION['lang']['namasupplier']."</td>
			  <td rowspan=2 align=center width=75>JatuhTempo</td>
			  <td rowspan=2 align=center width=75>".$_SESSION['lang']['nopokontrak']."</td>
			  <td rowspan=2 align=center width=75>".$_SESSION['lang']['nilaipokontrak']."</td>
			  <td rowspan=2 align=center width=75>".$_SESSION['lang']['nilaiinvoice']."</td>
			  <td rowspan=2 align=center width=100>".$_SESSION['lang']['belumjatuhtempo']."</td>
			  <td align=center colspan=4 width=400>".$_SESSION['lang']['sudahjatuhtempo']."</td>
			  <td rowspan=2 align=center width=100>".$_SESSION['lang']['dibayar']."</td>
			  <td rowspan=2 align=center width=50>".$_SESSION['lang']['jmlh_hari_outstanding']."</td>
			</tr>  
		    <tr>
			  <td align=center width=50>1-30 ".$_SESSION['lang']['hari']."</td>
			  <td align=center width=50>31-60 ".$_SESSION['lang']['hari']."</td>
			  <td align=center width=50>61-90 ".$_SESSION['lang']['hari']."</td>
			  <td align=center width=50>over 90 ".$_SESSION['lang']['hari']."</td>
			</tr>  
		 </thead>
		 <tbody id=container>
			<script>getUsiaHutang()</script>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div>";
	
CLOSE_BOX();

close_body();
?>