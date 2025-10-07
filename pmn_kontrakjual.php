<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src='js/pmn_kontrakjual.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<?php
OPEN_BOX('',$_SESSION['lang']['kontrakjual']);

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where `kelompokbarang`='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
        $optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$sCust="select kodecustomer,namacustomer  from ".$dbname.".pmn_4customer order by namacustomer";
$qCust=mysql_query($sCust) or die(mysql_error($sCust));
while($rCust=mysql_fetch_assoc($qCust))
{
        $optCust.="<option value=".$rCust['kodecustomer'].">".$rCust['namacustomer']."</option>";
}	
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'"; //echo $sOrg;
$qOrg=mysql_query($sOrg) or die(mysql_error());
while($rOrg=mysql_fetch_assoc($qOrg))
{
        $optPt.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$arrKurs=array("IDR","USD");
foreach($arrKurs as $dt){
        $optKurs.="<option value=".$dt.">".$dt."</option>";
}
#ambil franco
$optByrke=$optTermin=$optFranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sFranco="select distinct id_franco,franco_name from ".$dbname.".pmn_5franco order by franco_name asc";
$qFranco=mysql_query($sFranco) or die(mysql_error($conn));
while($rFranco=mysql_fetch_assoc($qFranco)){
	$optFranco.="<option value='".$rFranco['id_franco']."'>".$rFranco['franco_name']."</option>";
}
#termin pembayaran
$sFranco2="select distinct kode from ".$dbname.".pmn_5terminbayar order by kode asc";
$qFranco2=mysql_query($sFranco2) or die(mysql_error($conn));
while($rFranco2=mysql_fetch_assoc($qFranco2)){
	$optTermin.="<option value='".$rFranco2['kode']."'>".$rFranco2['kode']."</option>";
}
$arrStatPPn=array("0"=>"Exclude","1"=>"Include");
$optSat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrStatPPn as $row=>$lstNm){
	$optSat.="<option value='".$row."'>".$lstNm."</option>";
}
$sByr="select * from ".$dbname.".keu_5akunbank order by namabank";
$qbyr=mysql_query($sByr) or die(mysql_error($conn));
while($rByr=mysql_fetch_assoc($qbyr)){
	$optByrke.="<option value='".$rByr['rekening']."'>".$rByr['pemilik'].":".$rByr['namabank']." ".$rByr['rekening']."</option>";
}

$optNoref=$optTtdjual="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTtd="select * from ".$dbname.".pmn_5ttd";
$nTtd=mysql_query($iTtd) or die(mysql_error($conn));
while($dTtd=mysql_fetch_assoc($nTtd)){
	$optTtdjual.="<option value='".$dTtd['nama']."'>".$dTtd['nama']."</option>";
}



//=========================
$frm[0].="
<input type=hidden id=method name=method value='insert' />     <fieldset>
          <legend>".$_SESSION['lang']['form']."</legend>
          <fieldset>
                <legend>".$_SESSION['lang']['header']."</legend>
                <table cellspacing=1 border=0>
                <tr><td>".$_SESSION['lang']['NoKontrak']."</td><td>
                 <input type=text class=myinputtext id=noKtrk name=noKtrk maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:150px;\" disabled /></td>
            <td>".$_SESSION['lang']['pt']."</td>
            <td><select id=kdPt name=kdPt onchange='getRek()'><option value=''></option>".$optPt."</select></td>
                        
                                        <td>&nbsp;</td> <td>".$_SESSION['lang']['tglKontrak']."</td><td align=right><input type=text id=tlgKntrk size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)  /></td>
                        </tr>
                </table>
          </fieldset>
          <br />
          <fieldset>
                <legend>".$_SESSION['lang']['custInformation']."</legend>
                <table>
                        <tr> 	 
                                <td>".$_SESSION['lang']['nmcust']."</td>
                                <td>
                                <select id=custId name=custId style=\"width:150px;\" onchange=\"getDataCust(0)\"><option value=></option>".$optCust."</select></td>
                                <td style='display:none;'>Contact Person :</td>
	            <td style='display:none'><select  id=nmPerson style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
				<td>".$_SESSION['lang']['nokontrakinduk']."</td><td><select style=\"width:150px;\" id='kntrkRef'>".$optNoref."</select></td>
                        </tr>
                </table>
          </fieldset><br />
          <fieldset>
                        <legend>".$_SESSION['lang']['orderInfor']."</legend>

                        <table cellspacing=1 border=0>
                        <thead>
                        <tr>
                        <td colspan=7>".$_SESSION['lang']['goodsDesc']."</td>
                        </tr>
                        <tr class=rowheader>
                                <td>".$_SESSION['lang']['namabarang']."</td>
                                <td>".$_SESSION['lang']['satuan']."</td>
                                <td>".$_SESSION['lang']['hargasatuan']."</td>
                                <td>".$_SESSION['lang']['matauang']."</td>
                                <td>".$_SESSION['lang']['jmlhBrg']."</td>
								<td>".$_SESSION['lang']['ppn']."</td>
                                <td>".$_SESSION['lang']['terbilang']."</td>
                        </tr>
                        </thead>
                        <tbody>
                                <td><select id=kdBrg name=kdBrg onchange=\"getSatuan(0,0,0)\" style=\"width:150px;\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select></td>
                                <td><select id=stn name=stn style=\"width:50px;\"><option value=''></option></select></td>
                                <td><input type=text class=myinputtextnumber  name=HrgStn id=HrgStn onkeypress=\"return angka_doang(event);\"  onkeyup=\"z.numberFormat('HrgStn',3);hitungHarga();rupiahkan(getById('tmpHarga'),'tBlg',true)\" onblur=\"rupiahkan(this,'tBlg',true)\" style=\"width:100px;\" /></td>
                                <td><select id=kurs name=kurs style=\"width:50px;\">".$optKurs."</select></td>
                                <td><input type=text class=myinputtextnumber name=jmlh id=jmlh onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" onkeyup=\"z.numberFormat('jmlh',2);hitungHarga();getBerat();\" />
								<input id=tmpHarga type=hidden value=0>
								</td>
								<td><select id=ppnId name=ppnId style=\"width:50px;\">".$optSat."</select></td>
                                <td width:350><span id=tBlg></span></td>
                        </tbody>
                        </table><br />
                        <table cellspacing=1 border=0>
                        <thead>
                        <tr>
                        <td colspan=2>".$_SESSION['lang']['penyerahan']."</td>
                        </tr>
                        <tr class=rowheader>
                                <td>".$_SESSION['lang']['tgl_kirim']."</td>
                                <td>".$_SESSION['lang']['jumlah']."</td>
                        </tr>
                        </thead>
                        <tbody>
                                <tr><td> <input type=text id=tglKrm0 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)> s.d.<input type=text id=tglSd0 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
                                <td><input type=text class=myinputtextnumber name=jmlh0 id=jmlh0 style=\"width:150px;\" onkeypress=\"return angka_doang(event);\"  /></td></tr>
								<tr><td> <input type=text id=tglKrm1 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)> s.d.<input type=text id=tglSd1 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
                                <td><input type=text class=myinputtextnumber name=jmlh1 id=jmlh1 style=\"width:150px;\" onkeypress=\"return angka_doang(event);\"   /></td></tr>
								<tr><td> <input type=text id=tglKrm2 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)> s.d.<input type=text id=tglSd2 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
                                <td><input type=text class=myinputtextnumber name=jmlh2 id=jmlh2 style=\"width:150px;\" onkeypress=\"return angka_doang(event);\"    /></td></tr>
								<tr><td> <input type=text id=tglKrm3 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)> s.d.<input type=text id=tglSd3 size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
                                <td><input type=text class=myinputtextnumber name=jmlh3 id=jmlh3 style=\"width:150px;\" onkeypress=\"return angka_doang(event);\"  /></td></tr>

                        </tbody>
                        </table><br />
                        <table border=0 cellspacing=1>
                        <tr>
                        <td valign=top>
                        <table cellspacing=1 border=0>
                        <thead>
                        <tr>
                        <td colspan=3 style=\"width:200px;\">".$_SESSION['lang']['kualitas']."</td>
                        </tr></thead>
                        <tbody>
                        <tr>
                        <td>".$_SESSION['lang']['tempatpenyerahan']."</td><td>:</td><td><select name=tmbngn id=tmbngn style=\"width: 150px;\" >".$optFranco."</select></td></tr>
                        <tr><td>FFA</td><td>:</td><td><input class=myinputtextnumber id=ffa style=\"width: 150px;\" onkeypress='return angka_doang(event)' />%</td></tr>
                        <tr><td>DOBI</td><td>:</td><td><input class=myinputtextnumber id=dobi style=\"width: 150px;\" onkeypress='return angka_doang(event)' /></td></tr>
                        <tr><td>M & I</td><td>:</td><td><input class=myinputtextnumber id=mdani style=\"width: 150px;\" onkeypress='return angka_doang(event)' />%</td></tr>
                        <tr><td>Moisture</td><td>:</td><td><input class=myinputtextnumber id=moist style=\"width: 150px;\" onkeypress='return angka_doang(event)' />%</td></tr>
                        <tr><td>Impurities</td><td>:</td><td><input class=myinputtextnumber id=dirt style=\"width: 150px;\" onkeypress='return angka_doang(event)' />%</td></tr>
                        <tr><td>Grading</td><td>:</td><td><input class=myinputtextnumber id=grading style=\"width: 150px;\" onkeypress='return angka_doang(event)' />%</td></tr>
                        <tr><td>".$_SESSION['lang']['toleransi']."</td><td>:</td><td><textarea name=tlransi id=tlransi style=\"width:150px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td></tr>
                        </tbody>
                        </table>
                        </td><td valign=top>
                        <table cellspacing=1 border=0>
                        <thead>
                        <tr>
                        <td colspan=3 style=\"width:200px;\">".$_SESSION['lang']['syaratPem']."</td>
                        </tr></thead>
                        <tbody>
                        <tr>
                        <td>".$_SESSION['lang']['payment']."</td><td>:</td><td>
                        <select style=\"width: 170px;\" name=syrtByr id=syrtByr >".$optTermin."</select></td></tr>
                        <tr>
                        <td>".$_SESSION['lang']['tanggalbayar']."</td>
                        <td>:</td>
                        <td><input type=text id=tglByr style=\"width: 170px;\" size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
                        </tr>
                        <tr>
                        <td>".$_SESSION['lang']['bayarke']."</td><td>:</td><td>
                        <select style=\"width: 170px;\" name=byrKe id=byrKe >".$optByrke."</select></td></tr>
                        <tr>
                        <td>".$_SESSION['lang']['tndaTangan']."</td><td>:</td><td><select style=\"width: 170px;\" name=tndtng id=tndtng >".$optTtdjual."</select></td></tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['penjual']."</td><td hidden>:</td><td hidden><input type=text name=tndtngJbtn id=tndtngJbtn class=myinputtext style=\"width: 170px;\" /></td></tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['tandatangan']." ".$_SESSION['lang']['Pembeli']."</td><td hidden>:</td><td hidden><input type=text name=tndtngPembli id=tndtngPembli class=myinputtext style=\"width: 170px;\" /></td></tr>
						<tr>
                        <td hidden>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['Pembeli']."</td><td hidden>:</td><td hidden><input type=text name=jtbnPembli id=jtbnPembli class=myinputtext style=\"width: 170px;\" /></td></tr>

                        </tbody>
                        </table>
                        </td>
                        </tr>
                        </table>
          </fieldset>
          <br />
        <fieldset>
        <legend>".$_SESSION['lang']['lainlain']."</legend>
     <table>
            <tr> 	 
                 <td style='valign:top'>".$_SESSION['lang']['lainlain']."</td><td>
				 <textarea onkeypress=\"return tanpa_kutip(event);\" id=cttnLain style=\"width:830px;height:150px\" rows='5' cols='50' >".
				 //"Kualitas mutu FFA berasarkan hasil analisa Sucofindo yang sudah ditentukan oleh kedua belah pihak, dimana hasilnya akan dipakai sebagai acuan penetapan mutu barang. Tenggang waktu penyerahan barang maksimal 4 (empat) hari. Penjual dapat melakukan pembatalan penyerahan sepihak bila pembeli tidak melakukan pengangkutan dari tempat yang disepakati dalam batas tenggang waktu. 
				 //Bila kualitas  diluar standar, maka klaim akan ditentukan sbb:
				 //- FFA 5.00%-5.50% harga akan dipotong sebesar Rp 100,-/kg.
				 //- FFA 5.51%-6.00% harga akan dipotong sebesar Rp 200,-/kg.
				 //- FFA 6.01%-6.50% harga akan dipotong sebesar Rp 300,-/kg.
				 //- FFA 6.51%-7.00% harga akan dipotong sebesar Rp 400,-/kg.
				 //- FFA > 7.00% maka pembeli berhak menolak barang.
				 //- Klaim DOBI: (2-DOBI Pemuatan Hasil Analisa Sucofindo)/100 x harga x kuantitas".
				 "</textarea></td>
          </tr>
         
     </table>
        </fieldset>
         <center>
           <button class=mybutton onclick=saveKP()>".$_SESSION['lang']['save']."</button>
           <!--<button class=mybutton onclick=copyFromLast()>".$_SESSION['lang']['copy']."</button>-->
           <button class=mybutton onclick=clearFrom()>".$_SESSION['lang']['new']."</button>

         </center>
         </fieldset>";

$optSch.="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPt="select * from ".$dbname.".organisasi where tipe='PT' ";
$nPt=  mysql_query($iPt) or die (mysql_error($conn));
while($dPt=  mysql_fetch_assoc($nPt))
{
    $optSch.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}

$optKomoditi="<option value=''>".$_SESSION['lang']['all']."</option>";
$sKomoditi="select distinct(a.kodebarang),b.namabarang from ".$dbname.".pmn_kontrakjual a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang";
$qKomoditi= mysql_query($sKomoditi) or die (mysql_error($conn));
while($dKomoditi=  mysql_fetch_assoc($qKomoditi))
{
    $optKomoditi.="<option value='".$dKomoditi['kodebarang']."'>".$dKomoditi['namabarang']."</option>";
}

$optCust="<option value=''>".$_SESSION['lang']['all']."</option>";
$sCust="select distinct(a.koderekanan),b.namacustomer from ".$dbname.".pmn_kontrakjual a left join ".$dbname.".pmn_4customer b on a.koderekanan=b.kodecustomer";
$qCust= mysql_query($sCust) or die (mysql_error($conn));
while($dCust=  mysql_fetch_assoc($qCust))
{
    $optCust.="<option value='".$dCust['koderekanan']."'>".$dCust['namacustomer']."</option>";
}

$frm[1]="<fieldset>
           <legend>".$_SESSION['lang']['list']."</legend>
          <fieldset><legend></legend>
          ".$_SESSION['lang']['NoKontrak']."
          <input type=text id=txtnokntrk size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" >
          
          ".$_SESSION['lang']['pt']."
          <select style=\"width: 210px;\" name=ptSch id=ptSch >".$optSch."</select>    

          ".$_SESSION['lang']['komoditi']."
          <select style=\"width: 155px;\" name=ptKomoditi id=ptKomoditi >".$optKomoditi."</select><BR>    

          ".$_SESSION['lang']['nmcust']."
          <select style=\"width: 270px;\" name=ptCust id=ptCust >".$optCust."</select>    

          <button class=mybutton onclick=cariNoKntrk()>".$_SESSION['lang']['find']."</button>
          </fieldset>
          <table class=sortable cellspacing=1 border=0>
      <thead>
          <tr class=rowheader>
          <td>No.</td>
          <td>".$_SESSION['lang']['NoKontrak']."</td>
          <td>".$_SESSION['lang']['nm_perusahaan']."</td>
          <td>".$_SESSION['lang']['nmcust']."</td>
          <td>".$_SESSION['lang']['tglKontrak']."</td>
          <td>".$_SESSION['lang']['produk']."</td>
          <td>".$_SESSION['lang']['hargasatuan']."</td>
          <td>".$_SESSION['lang']['ppn'].' Incl/Excl'."</td>
          <td>".$_SESSION['lang']['tgl_kirim']."</td>
          <td width='8%'>Action</td>
          </tr>
          </head>
           <tbody id=containerlist>
           <script>
           loadNewData();
           </script>
           </tbody>
           <tfoot>
           </tfoot>
           </table>
         </fieldset>";

$hfrm[0]=$_SESSION['lang']['form'];
$hfrm[1]=$_SESSION['lang']['list'];

drawTab('FRM',$hfrm,$frm,100,1100);
?>

<?
CLOSE_BOX();
echo close_body();
?>