<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/alokasiIDC.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('',$_SESSION['lang']['input'].' '.$_SESSION['lang']['alokasiidc']);
echo"<fieldset><legend>".$_SESSION['lang']['form']."</legend>
           <table>
             <tr>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td><input class='myinputtext' id='tanggal' size='26' onmousemove='setCalendar(this.id)' maxlength='10' onkeypress='return false;' type='text' onblur=ambilBuktiKas(this.value)></td>
            </tr>
             <tr>
                <td>".$_SESSION['lang']['nojurnal']."</td><td><select id=nokas onchange=ambilAlokasi()></select></td>
             </tr>
             <tr>
                <td>".$_SESSION['lang']['alokasibiaya']."</td>
                <td><select id=alokasi onchange=getAfd()></select>
                <select id=afdeling onchange=ambilBlok(this.options[this.selectedIndex].value)></select></td>
                
             </tr>   
           </table>
          </fieldset>";   
CLOSE_BOX();
OPEN_BOX('','');

#ambil daftar IDC
$str="select distinct nojurnal,tanggal,totaldebet as jumlah,substr(nojurnal,10,4) as kodeorg from ".$dbname.".keu_jurnalht where nojurnal like '%/IDC/%' and substr(nojurnal,10,4) in( select kodeorganisasi from ".$dbname.".organisasi
          where induk='".$_SESSION['empl']['kodeorganisasi']."') order by tanggal desc";
		  
$res=mysql_query($str);
$tab="<table>
             <thead>
              <tr class=rowheader>
             <td>".$_SESSION['lang']['nomor']."</td>
             <td>".$_SESSION['lang']['nojurnal']."</td>
              <td>".$_SESSION['lang']['tanggal']."</td>
			  <td>".$_SESSION['lang']['jumlah']."</td>
              <td>".$_SESSION['lang']['action']."</td>
             </tr>
             </thead>
             <tbody>";
             
$no=0;
while($bar=mysql_fetch_object($res))
{  $no+=1;
    $tab.="<tr class=rowcontent><td>".$no."</td><td>".$bar->nojurnal."</td>
	<td>".tanggalnormal($bar->tanggal)."</td>
	<td align=right>".number_format($bar->jumlah)."</td>
	<td><button onclick=hapusIni('".$bar->nojurnal."','".$bar->tanggal."','".$bar->kodeorg."')>".$_SESSION['lang']['delete']."</button></tr>";
}
$tab.="</tbody><tfoot></tfoot></table>";
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend>
          <div id=space></div>".$tab."
          </fieldset>";  
CLOSE_BOX();
echo close_body();
?>