<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_sp.js'></script>
<?
OPEN_BOX('',$_SESSION['lang']['sutarperingatan']);
$opts='';
$str="select * from ".$dbname.".sdm_5jenissp order by kode";
$res=mysql_query($str);
$opts="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($bar=mysql_fetch_object($res))
{
	$opts.="<option value='".$bar->kode."'>".$bar->keterangan."</option>";
}

#kamus tipe karyawan
$str="select id,tipe from ".$dbname.".sdm_5tipekaryawan";
$grr=mysql_query($str);
while($bar=mysql_fetch_object($grr)){
    $tip[$bar->id]=$bar->tipe;
}

//get karyawan
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO')
{
  $str=" select karyawanid,namakaryawan,bagian,subbagian,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan
       where (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') order by namakaryawan";	
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
     $str=" select karyawanid,namakaryawan,bagian,subbagian,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan
       where left(lokasitugas,4) in(select kodeunit from ".$dbname.".bgt_regional_assignment
       where regional='".$_SESSION['empl']['regional']."') and tipekaryawan in(1,2,3,6)";
}
else
{
 $str=" select karyawanid,namakaryawan,bagian,subbagian,lokasitugas,tipekaryawan from ".$dbname.".datakaryawan
       where left(lokasitugas,4)='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
       and tipekaryawan in(1,2,3,6)
       order by namakaryawan";   
}



$optkar="<option value=''></option>";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res))
{
        $optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan." | ".$tip[$bar->tipekaryawan]." | ".$bar->lokasitugas." | ".$bar->subbagian."</option>";
}

$paragraf3=readTextFile('config/sp_format/sp_paragraf2_BAPP.lst');
$paragraf4=readTextFile('config/sp_paragraf4.lst');
//=========================
if(substr($_SESSION['empl']['lokasitugas'],2,2)=='HO')
{
$str=" select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
         where length(kodeorganisasi)=4";    
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
$str=" select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
         where kodeorganisasi in(select kodeunit from ".$dbname.".bgt_regional_assignment
                                                     where regional='".$_SESSION['empl']['regional']."')";
}else{
    $str=" select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
         where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";    
}

$rss=mysql_query($str);
$optLok="<option value='%'>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($rss)){
    $optLok.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

//=========================
$str=" select id,tipe from ".$dbname.".sdm_5tipekaryawan";
$rss=mysql_query($str);
$optT="<option value='%'>".$_SESSION['lang']['all']."</option>";
while($bar=mysql_fetch_object($rss)){
    $optT.="<option value='".$bar->id."'>".$bar->tipe."</option>";
}
#=========================
$frm[0]="
<fieldset>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table>
		<tr> 	 
			<td>
				<input type=hidden value='insert' id=method>
				<input type=hidden value='' id=nosp>
				".$_SESSION['lang']['memotype']."
			</td>
			<td><select id=jenissp onchange=\"memotypeChange()\">".$opts."</select></td>
		</tr>
		<tr> 	 
			<td>".$_SESSION['lang']['lokasitugas']."</td>
			<td><select id=lokasitugas onchange=filterK()>".$optLok."</select></td>
		</tr>
		<tr> 	 
			<td>".$_SESSION['lang']['tipekaryawan']."</td>
			<td><select id=tipekaryawan onchange=filterK()>".$optT."</select></td>
		</tr>          
		<tr> 	 
			<td>".$_SESSION['lang']['karyawan']."</td>
			<td><select id=karyawanid>".$optkar."</select></td>
		</tr>
		<tr> 	 
			<td>".$_SESSION['lang']['tanggalsurat']."</td>
			<td><input type=text id=tanggalsp size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
		</tr>
		<tr style='display:none'> 	 
			<td>".$_SESSION['lang']['masaberlaku']."</td>
			<td><select id=masaberlaku>
				<option value='1'>1</option>
				<option value='2'>2</option>
				<option value='3'>3</option>
				<option value='6'>6</option>
				<option value='9'>9</option>
				<option value='12'>12</option>
			   </select> ".$_SESSION['lang']['bulan']."
			</td>
		</tr>
		<tr>	
			<td style='vertical-align:top'><label id='txt1'>".$_SESSION['lang']['pelanggaran']."</label></td></td><td><textarea id=pelanggaran onkeypress=\"return tanpa_kutip(event);\" cols=80 rows=3></textarea></td>
		</tr>	
		<tr>
			<td style='vertical-align:top'><label id='txt2'>Paragraf 2</label></td><td><textarea id=paragraf1 onkeypress=\"return tanpa_kutip(event);\" cols=80 rows=3></textarea></td>
		</tr>	
		<tr>
			<td style='vertical-align:top'><label id='txt3'>Paragraf 3</label></td><td><textarea id=paragraf3 onkeypress=\"return tanpa_kutip(event);\" cols=80 rows=3></textarea></td>
		</tr>	
		<tr>
			<td style='vertical-align:top'><label id='txt4'>Paragraf 4</label></td><td><textarea id=paragraf4 onkeypress=\"return tanpa_kutip(event);\" cols=80 rows=3></textarea></td>
		</tr>
	</table>
	<table>
		<tr>
			<td id='tdDistujui'><label id='labelPersetujuan1'>".$_SESSION['lang']['disetujui']."</label></td>
			<td id='tdDistujui2'><input type=text class=myinputtext id=penandatangan size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>
			<td id='tdDiketahui'><label id='labelPersetujuan2'>".$_SESSION['lang']['diketahuioleh']."</label></td>
			<td id='tdDiketahui2'><input type=text class=myinputtext id=verifikasi size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>       
			<td id='tdDibuat'><label id='labelPersetujuan3'>".$_SESSION['lang']['dibuat']."</label></td>
			<td id='tdDibuat2'><input type=text class=myinputtext id=dibuat size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>           
		</tr>
		<tr>
			<td id='tdDistujui3'>".$_SESSION['lang']['functionname']."</td>
			<td id='tdDistujui4'><input type=text class=myinputtext id=jabatan size=25 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td> 
			<td id='tdDiketahui3'>".$_SESSION['lang']['functionname']."</td>
			<td id='tdDiketahui4'><input type=text class=myinputtext id=jabatan1 size=25 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td>
			<td id='tdDibuat3'>".$_SESSION['lang']['functionname']."</td>
			<td id='tdDibuat4'><input type=text class=myinputtext id=jabatan2 size=25 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td>        
		</tr>
	</table>
	<br>
	<table>
		<tr>
			<td>".$_SESSION['lang']['tembusan']."(i)</td>
			<td><input type=text class=myinputtext id=tembusan1 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tembusan']."(ii)</td>
			<td><input type=text class=myinputtext id=tembusan2 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tembusan']."(iii)</td>
			<td><input type=text class=myinputtext id=tembusan3 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tembusan']."(iiii)</td>
			<td><input type=text class=myinputtext id=tembusan4 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>	 	 	 	 
	</table>
	 
	<center>
		<button class=mybutton onclick=saveSP()>".$_SESSION['lang']['save']."</button>
		<button class=mybutton onclick=batal()>".$_SESSION['lang']['new']."</button>
	</center>
 </fieldset>";

$frm[1]="<fieldset>
           <legend>".$_SESSION['lang']['list']."</legend>
          <fieldset><legend></legend>
          ".$_SESSION['lang']['caripadanama']."
          <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=9>
          <button class=mybutton onclick=cariSP(0)>".$_SESSION['lang']['find']."</button>
          </fieldset>
          <table class=sortable cellspacing=1 border=0>
      <thead>
          <tr class=rowheader>
          <td>No.</td>
          <td>".$_SESSION['lang']['nomorsk']."</td>
          <td>".$_SESSION['lang']['karyawan']."</td>
          <td>".$_SESSION['lang']['tanggalsurat']."</td>
          <td>".$_SESSION['lang']['tanggalsampai']."</td>
          <td>".$_SESSION['lang']['tipetransaksi']."</td>
          <td>".$_SESSION['lang']['dbuat_oleh']."</td>
          <td></td>
          </tr>
          </head>
           <tbody id=containerlist>
           </tbody>
           <tfoot>
           </tfoot>
           </table>
         </fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];

drawTab('FRM',$hfrm,$frm,100,900);
CLOSE_BOX();
echo close_body('');
?>