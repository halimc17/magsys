<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/sdm_5harilibur.js'></script>

<?
$arr="##kebun##tanggal##keterangan##catatan";

$optKebun="";
$optKebun.="<option value='GLOBAL'>GLOBAL</option>";
$strKebun="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$resKebun=mysql_query($strKebun) or die(mysql_error());
while($barKebun=mysql_fetch_object($resKebun))
{
	$optKebun.="<option value='".$barKebun->kodeorganisasi."'>".$barKebun->kodeorganisasi." - ".$barKebun->namaorganisasi."</option>";
}

$arrketerangan=getEnum($dbname,'sdm_5harilibur','keterangan');
$optketerangan="";
foreach($arrketerangan as $kei=>$fal)
{
    $optketerangan.="<option value='".$kei."'>".$fal."</option>";
}

include('master_mainMenu.php');
OPEN_BOX();

echo"<fieldset>
     <legend>".$_SESSION['lang']['harilibur']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['kebun']."</td>
	   <td><select id=\"kebun\" name=\"kebun\" style=\"width:150px\">".$optKebun."</select></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td><input id=\"tanggal\" name=\"tanggal\" class=\"myinputtext\" onkeypress=\"return tanpa_kutip(event)\" style=\"width:150px\" readonly=\"readonly\" onmousemove=\"setCalendar(this.id)\" type=\"text\"></td>
	 </tr>
        <tr>
            <td><label>".$_SESSION['lang']['keterangan']."</label></td>
            <td><select id=\"keterangan\" name=\"keterangan\" style=\"width:150px\">".$optketerangan."</select></td>
            <td>&nbsp;</td>
        </tr>         
	 <tr>
	   <td>".$_SESSION['lang']['catatan']."</td>
	   <td><input type=text class=myinputtext id=catatan name=catatan onkeypress=\"return tanpa_kutip(event);\" style=\"width:150px;\" /></td>
	 </tr>
	 </table>
         <input type=hidden value=insert id=method>
         <button class=mybutton onclick=savehk('sdm_slave_5harilibur','".$arr."')>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
     </fieldset><input type='hidden' id=oldtanggal name=oldtanggal />";
CLOSE_BOX();

OPEN_BOX();
$str="select * from ".$dbname.".bgt_hk order by tahunbudget desc";
$res=mysql_query($str);
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['kebun']."</td>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td>".$_SESSION['lang']['keterangan']."</td>
	   <td>".$_SESSION['lang']['catatan']."</td>
	   <td>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>