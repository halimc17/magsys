<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX(); //1 O
?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_persetujuan.js"></script>
<div id="action_list">
<?php
$optListUsr="<option value=''>".$_SESSION['lang']['all']."</option>";
$sListUser="select  distinct a.dibuat,b.namakaryawan,lokasitugas from ".$dbname.".log_prapoht a left join 
            ".$dbname.".datakaryawan b on a.dibuat=b.karyawanid order by namakaryawan asc";
$qListUser=mysql_query($sListUser) or die(mysql_error($conn));
while($rListUser=mysql_fetch_assoc($qListUser))
{
    if($rListUser['namakaryawan']!='')
    {
        $optListUsr.="<option value='".$rListUser['dibuat']."'>".$rListUser['namakaryawan']." [".$rListUser['lokasitugas']."]</option>";
    }
}

$optStatus.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optStatus.="<option value='0'>Belum Disetujui</option>";
$optStatus.="<option value='1'>Sudah Disetujui</option>";

echo"<table>
     <tr valign=moiddle>
         <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/orgicon.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
         <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>
             <table>
                <tr>
                    <td>".$_SESSION['lang']['carinopp']."</td>
                    <td>:</td>
                    <td><input type=text style=width:200px; id=txtsearch size=25 maxlength=30 class=myinputtext></td>
                    
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td><input type=text style=width:200px; class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td>                
                </tr>    
                <tr>
                    <td>".$_SESSION['lang']['namabarang']."</td>
                    <td>:</td>
                    <td><input type=text style=width:200px; id=txtnmbrg size=25 maxlength=30 class=myinputtext></td>
                    <td>".$_SESSION['lang']['dbuat_oleh']."</td>
                    <td>:</td>
                    <td><select id=pembuatPP style=width:200px;>".$optListUsr."</select></td>
                </tr> 
                
                <tr>
                    <td>".$_SESSION['lang']['status']." Persetujuan</td>
                    <td>:</td>
                    <td><select id=statusSch style=width:200px;>".$optStatus."</select></td>
                </tr> 
                <tr>
                    <td><button class=mybutton onclick=cariNopp()>".$_SESSION['lang']['find']."</button></td>
                </tr> 
             </table>

            ";
                    /*echo $_SESSION['lang']['carinopp'].":<input type=text id=txtsearch size=25 maxlength=30 class=myinputtext><br>";
                    echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /><br>";
                    echo $_SESSION['lang']['namabarang'].":<input type=text id=txtnmbrg size=25 maxlength=30 class=myinputtext><br>";
                    echo $_SESSION['lang']['dbuat_oleh'].":<select id=pembuatPP>".$optListUsr."</select><br>";
                    echo $_SESSION['lang']['status'].":<select id=statusSch>".$optStatus."</select><br>";
                    
                    echo"<button class=mybutton onclick=cariNopp()>".$_SESSION['lang']['find']."</button>";*/
echo"</fieldset></td>
     </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX(); //1 C //2 O
?>
<div id=list_pp_verication>
<?php OPEN_BOX();?>
<fieldset>
<legend><?php echo $_SESSION['lang']['list_pp'];?></legend>
<div style="overflow:scroll; height:420px;">
         <table class="sortable" cellspacing="1" border="0">
         <thead>
         <tr class=rowheader>
         <td>No.</td>
         <td><?php echo $_SESSION['lang']['nopp']?></td>
         <td><?php echo $_SESSION['lang']['tanggal'];?></td> 
         <td><?php echo $_SESSION['lang']['namaorganisasi'];?></td>
          <td>PR Detail</td>
           <td colspan="4" align="center">Verification</td>
          <?php		
                                for($i=1;$i<6;$i++)
                                 {
                                        echo"<td>".$_SESSION['lang']['persetujuan'].$i."</td>";
                                 }
           ?>

         </tr>
         </thead>
         <tbody id="contain">

     <script>refresh_data()</script>
          </tbody>
         <tfoot>
         </tfoot>
         </table></div>
</fieldset
><?php
CLOSE_BOX();
?>
</div>
<input type="hidden" name="method" id="method"  /> 
<input type="hidden" id="no_pp" name="no_pp" />
<input type="hidden" name="user_login" id="user_login" value="<?php echo $_SESSION['standard']['userid']?>" />
<?php close_body();?>
