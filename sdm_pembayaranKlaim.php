<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src='js/sdm_pengobatan.js'></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?php
OPEN_BOX('',$_SESSION['lang']['pembayaranclaim']);
//option periode akuntansi
//ambil daftar pengobatan dengan tahun sekarang

$namaBiaya = makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama');

echo" Periode :<select id='periode'>";

for($x=0;$x<=24;$x++)
{
    $t=mktime(0,0,0,date('m')-$x,15,date('Y'));
    echo"<option value='".date('Y-m',$t)."'>".date('m-Y',$t)."</option>";
}
echo"</select>
          <button onclick=getDaftar() class=mybutton>".$_SESSION['lang']['proses']."</button>";

echo "<div id=cont>";
if(isset($_GET['periode']))
{
    if($_SESSION['empl']['lokasitugas']=='MJHO'){
        $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.karyawanid as karyawanid,
              a.totalklaim as totalklaim,a.tahunplafon as tahunplafon 
              from ".$dbname.".sdm_pengobatanht a 
              left join ".$dbname.".sdm_5rs b on a.rs=b.id 
              left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
              left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
              where periode='".$_GET['periode']."' and (c.tipekaryawan in ('0','7','8') or c.alokasi=1)
              order by a.updatetime desc, a.tanggal desc";
    }
    else{
        $str="select a.*, b.*,c.namakaryawan,d.diagnosa as ketdiag,c.karyawanid as karyawanid,
              a.totalklaim as totalklaim,a.tahunplafon as tahunplafon 
              from ".$dbname.".sdm_pengobatanht a 
              left join ".$dbname.".sdm_5rs b on a.rs=b.id 
              left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid
              left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id
              where periode='".$_GET['periode']."' and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
              order by a.updatetime desc, a.tanggal desc";
    }
//     exit("error: ".$str); 
    
$res=mysql_query($str);
echo"<fieldset>
	  <legend>".$_SESSION['lang']['belumbayar']."</legend>
	  <table class=sortable cellspacing=1 border=0>
	  <thead>
	    <tr class=rowheader>
		<td width=50></td>
		  <td align=center>No</td>
		  <td align=center width=100>".$_SESSION['lang']['notransaksi']."</td>
		  <td align=center width=50>".$_SESSION['lang']['periode']."</td>
		  <td align=center width=30>".$_SESSION['lang']['tanggal']."</td>
		  <td align=center width=200>".$_SESSION['lang']['namakaryawan']."</td>
		  <td align=center width=150>".$_SESSION['lang']['rumahsakit']."</td>
		  <td align=center width=50>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
		  <td align=center>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['perusahaan']."</td>    
          <td align=center>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['karyawan']."</td>
          <td align=center>".$_SESSION['lang']['beban']." ".$_SESSION['lang']['jms']."</td>    
		  <td align=center width=90>".$_SESSION['lang']['total']."</td>
		  <td align=center>".$_SESSION['lang']['dibayar']."</td>
		  <td align=center>".$_SESSION['lang']['tanggalbayar']."</td>
		  <td></td>
		</tr>
	  </thead>
	  <tbody id='container'>";
	  $no=0;
	  while($bar=mysql_fetch_object($res))
	  {
	   $no+=1;
	   echo"<tr class=rowcontent>
	   <td>";
	     echo"&nbsp <img src=images/zoom.png  title='view' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)>";
	   
	   echo"</td><td>".$no."</td>
		  <td>".$bar->notransaksi."</td>
		  <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
		  <td>".tanggalnormal($bar->tanggal)."</td>
		  <td>".$bar->namakaryawan."</td>
		  <td>".$bar->namars."[".$bar->kota."]"."</td>
		  <td>".$bar->kodebiaya."</td>
		  <td align=right>".number_format($bar->bebanperusahaan,2,'.',',')."</td>     
          <td align=right>".number_format($bar->bebankaryawan,2,'.',',')."</td>
          <td align=right>".number_format($bar->bebanjamsostek,2,'.',',')."</td>
          <td align=right>".number_format($bar->totalklaim,2,'.',',')."</td>           
         
           ";
           if($bar->tanggalbayar != '0000-00-00'){
               echo"<td align=right>".number_format($bar->jlhbayar,2,'.',',')."</td>
		    <td align=right>".tanggalnormal($bar->tanggalbayar)."</td>
		    <td></td>";
               
           }
           else{
                
                        
               echo"<td align=right><img src='images/puzz.png' style='cursor:pointer;' title='click to get value' onclick=\"document.getElementById('bayar".$no."').value=".$bar->bebanperusahaan."\">
		      <input type=text id=bayar".$no." class=myinputtextnumber onkeypress=\"return angka_doang(event);\" maxlength=12 onblur=change_number(this) size=12 value=".$bar->jlhbayar." ></td>
		  <td align=right><input type=text id=tglbayar".$no." class=myinputtext onkeypress=\"return false;\" maxlength=10  size=10 onmouseover=setCalendar(this) value='".date('d-m-Y')."'></td>
		  <td><img src='images/save.png' title='Save' class=resicon onclick=savePClaim('".$no."','".$bar->notransaksi."','".$bar->bebanperusahaan."')></td>";
           }
	   echo "</tr>";	  
	  }
echo"</tbody>
	 <tfoot>
	 </tfoot>
	 </table>
	 </fieldset> 	 
	 ";	 
}
echo "</div>";
CLOSE_BOX();
echo close_body();
?>