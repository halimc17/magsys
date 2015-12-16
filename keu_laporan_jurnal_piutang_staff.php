<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/keu_laporan.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX("","<b>".$_SESSION['lang']['daftarHutang']."/".$_SESSION['lang']['usiapiutang']."</b>");

//list akun
$str="select b.noakun, b.namaakun from  ".$dbname.".keu_5akun b 
      where detail=1 and (noakun like '113%' or noakun like '114%' or noakun like '211%' or noakun like '118%') order by b.noakun"; 
$res=mysql_query($str);
$optnoakun="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
        $optnoakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}
//list org
$str="select kodeorganisasi, namaorganisasi from  ".$dbname.".organisasi 
      where length(kodeorganisasi)=3 order by kodeorganisasi
"; 

$res=mysql_query($str);
$optorg="";
while($bar=mysql_fetch_object($res))
{
    $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

//list karyawan
$str="select a.nik, b.namakaryawan from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".datakaryawan b on a.nik = b.karyawanid
      where a.kodeorg ='".$_SESSION['empl']['lokasitugas']."' and a.nik!='0'
      and a.nik != '' and a.noakun != '' group by a.nik order by b.namakaryawan
"; // hanya menampilkan nama yang ada di jurnal 
$res=mysql_query($str);
$optnamakaryawan="<option value=''></option>";
while($bar=mysql_fetch_object($res))
{
        $optnamakaryawan.="<option value='".$bar->nik."'>".$bar->namakaryawan."</option>";
}



echo"<fieldset>
     <legend>".$_SESSION['lang']['laporanjurnal']."</legend>
         ".$_SESSION['lang']['tanggalmulai']." : <input class=\"myinputtext\" id=\"tanggalmulai\" size=\"12\" onmousemove=\"setCalendar(this.id)\" maxlength=\"10\" onkeypress=\"return false;\" type=\"text\">
         s/d <input class=\"myinputtext\" id=\"tanggalsampai\" size=\"12\" onmousemove=\"setCalendar(this.id)\" maxlength=\"10\" onkeypress=\"return false;\" type=\"text\">
         ".$_SESSION['lang']['noakun']." <select id=noakun >".$optnoakun."</select>
         ".$_SESSION['lang']['kodeorg']." <select id=kodeorg >".$optorg."</select>    
         <button class=mybutton onclick=getLaporanJurnalPiutangKaryawan()>".$_SESSION['lang']['proses']."</button>
         </fieldset>";
CLOSE_BOX();
OPEN_BOX('','Result:');
echo"<span id=printPanel style='display:none;'>
     <img onclick=piutangKaryawanKeExcel(event,'keu_laporanJurnalPiutangKaryawan_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>
         </span>    
         <div style='width:100%;height:359px;overflow:scroll;'>
       <table class=sortable cellspacing=1 border=0 width=100%>
             <thead>
                    <tr>
                          <td align=center width=50>".$_SESSION['lang']['nourut']."</td>
                          <td align=center>".$_SESSION['lang']['organisasi']."</td>
                          <td align=center>".$_SESSION['lang']['noakun']."</td>
                          <td align=center>".$_SESSION['lang']['namaakun']."</td>
                          <td align=center>".$_SESSION['lang']['karyawan']."/".$_SESSION['lang']['supplier']."</td>
                          <td align=center>".$_SESSION['lang']['saldoawal']."</td>                             
                          <td align=center>".$_SESSION['lang']['debet']."</td>
                          <td align=center>".$_SESSION['lang']['kredit']."</td>
                          <td align=center>".$_SESSION['lang']['saldoakhir']."</td>                               
                        </tr>  
                 </thead>
                 <tbody id=container>
                 </tbody>
                 <tfoot>
                 </tfoot>		 
           </table>
     </div>";
CLOSE_BOX();
close_body();
?>