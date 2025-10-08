<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();

/* Get Value Enum Status Internal/Eksternal */ 
$optStatusIntEks='';
$arrStatusIntEks=getEnum($dbname,'pmn_4customer','statusinteks');
foreach($arrStatusIntEks as $kei=>$fal)
{
	$optStatusIntEks.="<option value='".$kei."'>".ucfirst(strtolower($fal))."</option>";
}
?>
<script language="javascript" src="js/pmn_5customer.js"></script>
<fieldset>
<legend><b><?php echo $_SESSION['lang']['customerlist']?></b></legend>
<table cellpadding="2" cellspacing="2" border="0">
        <tr>
                <td width="120px"><?php echo $_SESSION['lang']['kodecustomer']?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="kode_cus" onkeypress="return tanpa_kutip(event);" /></td>
        </tr>
		<tr>
                <td style='vertical-align:top;'><?php echo $_SESSION['lang']['komoditi']?></td>
                <td style='vertical-align:top;'>:</td>
                <td>
				<table width="40%"><tr><td>
				<?php
					$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kelompokbarang=400";
					$qry=mysql_query($str) or die(mysql_error());
					while($res=mysql_fetch_assoc($qry)){
						echo "<li style='float:left;width:50%;list-style-type:none'><input type='checkbox' id='chkKomoditi' name='chkKomoditi[]' value='".$res['kodebarang']."' />".$res['namabarang']."</li>";
					}
				?>
				</td></tr></table>
				</td>
        </tr>
        <tr style='display:none;'>
                <td><?php echo $_SESSION['lang']['klmpkcust']?></td>
                <td>:</td>
                <td><input type="hidden" id="klcustomer_code"  />
                <input type="text" id="nama_group" class="myinputtext" disabled="disabled"/> 
                <img src=images/search.png class=dellicon title=<?php echo $_SESSION['lang']['find']?> onclick="searchGruop('<?php echo $_SESSION['lang']['findgroup']?>','<fieldset><legend><?php echo $_SESSION['lang']['findgroup']?></legend>Find<input type=text class=myinputtext id=group_name><button class=mybutton onclick=findGroup()>Find</button></fieldset><div id=container_cari></div>',event)";></td>
        </tr>
        <tr style='display:none;'>
                <td><?php echo $_SESSION['lang']['akun']?></td>
                <td>:</td>
                <td>
                <input type="hidden" id="akun_cust"  /><input type="text" id="nama_akun" class="myinputtext" disabled="disabled"/> <img src=images/search.png class=dellicon title=<?php echo $_SESSION['lang']['find']?> onclick="searchAkun('<?php echo $_SESSION['lang']['findnoakun']?>','<fieldset><legend><?php echo $_SESSION['lang']['findnoakun']?></legend>Find<input type=text class=myinputtext id=no_akun><button class=mybutton onclick=findAkun()>Find</button></fieldset><div id=container_cari_akun></div>',event)";>
                <!--<input type="text" class="myinputtext" id="no_akun" onkeypress="return tanpa_kutip(event);"  />-->
                </td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['nmcust']?></td>
                <td>:</td>
                <td><input type="text" class="myinputtext" id="cust_nm" onkeypress="return tanpa_kutip(event);"  /></td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['alamat']?></td>
                <td style='vertical-align:top'>:</td>
                <td style='vertical-align:top'>
				<textarea id='almt' onkeypress='return tanpa_kutip(event);'></textarea>
                </td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['kota']?></td>
                <td>:</td>
                <td style='vertical-align:top'>
                <input type="text" class="myinputtext" id="kta" onkeypress="return tanpa_kutip(event);"  />
                </td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['telepon']?></td>
                <td>:</td>
                <td style='vertical-align:top'>
				<textarea id='tlp_cust' onkeypress='return tanpa_kutip(event);' ></textarea>
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['npwp']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtext" id="npwp_no" onkeypress="return tanpa_kutip(event);"  />
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['alamat']." ".$_SESSION['lang']['npwp']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <textarea id='npwp_alamat' onkeypress='return tanpa_kutip(event);' ></textarea>
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['penandatangan']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtext" id="penandatangan" onkeypress="return tanpa_kutip(event);"  />
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['jabatan']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtext" id="jabatan" onkeypress="return tanpa_kutip(event);"  />
                </td>
        </tr>
        <tr>
                <td style="vertical-align:top;"><?php echo $_SESSION['lang']['kntprson']?></td>
                <td style="vertical-align:top;">:</td>
                <td>
				<script>loadKontakPerson()</script>
				<div id="listKontakPerson"></div>
				<input type="hidden" class="myinputtext" id="kntk_person" onkeypress="return tanpa_kutip(event);"  />
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'>Status <?php echo $_SESSION['lang']['eksternal']."/".$_SESSION['lang']['internal']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <select id=statusinteks><?php echo $optStatusIntEks ?></select>
                </td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['plafon']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
					<input type="text" class="myinputtextnumber" id="plafon_cus" onkeypress="return angka_doang(event);" value="0" />
                </td>
        </tr>
        <tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['nilaihutang']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtextnumber" id="n_hutang" onkeypress="return angka_doang(event);" value="0" />
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['toleransipenyusutan']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtext" id="toleransipenyusutan" onkeypress="return tanpa_kutip(event);" />
                </td>
        </tr>
        <tr style="display:none">
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['noseripajak']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="text" class="myinputtext" id="seri_no" onkeypress="return tanpa_kutip(event);"  />
        <input type="hidden" value="insert" id="method" />
                </td>
        </tr>
		<tr>
                <td style='vertical-align:top'><?php echo $_SESSION['lang']['berikat']?></td>
                <td style='vertical-align:top'>:</td>
                <td>
                <input type="checkbox" id="chkBerikat" onclick="checkChkBerikat()" />
                </td>
        </tr>
		<tr>
                <td style="vertical-align:top;"><?php echo $_SESSION['lang']['statusberikat']?></td>
                <td style="vertical-align:top;">:</td>
                <td>
                <textarea id='ketBerikat' onkeypress='return tanpa_kutip(event);' disabled="true"></textarea>
                </td>
        </tr>
        <tr>
				<td colspan="2"></td>
                <td align="left">
                <button class=mybutton onclick=simpanPlgn()><?php echo $_SESSION['lang']['save']?></button>
         <button class=mybutton onclick=batalPlgn()><?php echo $_SESSION['lang']['cancel']?></button>
                </td>
        </tr>
</table>
</fieldset>
<?php CLOSE_BOX();
OPEN_BOX();
?>
<fieldset>
         <table class="sortable" cellspacing="1" border="0">
         <thead>
         <tr class=rowheader>
         <td>No.</td>
         <td><?php echo $_SESSION['lang']['komoditi']?></td>
         <td><?php echo $_SESSION['lang']['kodecustomer'];?></td>
         <td><?php echo $_SESSION['lang']['nmcust'];?></td>
         <td><?php echo $_SESSION['lang']['alamat'];?></td>
         <td><?php echo $_SESSION['lang']['kota'];?></td>
         <td><?php echo $_SESSION['lang']['telepon'];?></td>
         <td><?php echo $_SESSION['lang']['npwp'];?></td>
         <td><?php echo $_SESSION['lang']['alamat']." ".$_SESSION['lang']['npwp'];?></td>
         <td><?php echo $_SESSION['lang']['penandatangan'];?></td>
         <td><?php echo $_SESSION['lang']['jabatan'];?></td>
         <td><?php echo $_SESSION['lang']['kntprson']. "(".$_SESSION['lang']['email'].")"; ?></td>
         <td>Status <?php echo $_SESSION['lang']['eksternal']."/".$_SESSION['lang']['internal'];?></td>
         <td><?php echo $_SESSION['lang']['plafon'];?></td>
         <td><?php echo $_SESSION['lang']['nilaihutang'];?></td>
         <td><?php echo $_SESSION['lang']['toleransipenyusutan'];?></td>
         <td><?php echo $_SESSION['lang']['berikat'];?></td>
         <td><?php echo $_SESSION['lang']['statusberikat'];?></td> 
         <td colspan="2">Action</td>
         </tr>
         </thead>
         <tbody id="container">
         <?php 
                //ambil data dari tabel kelompok customer

                $srt="select * from ".$dbname.".pmn_4customer order by kodecustomer desc";  //echo $srt;
                if($rep=mysql_query($srt))
                  {
                        $no=0;
                        while($bar=mysql_fetch_object($rep))
                        {
                        //get kelompok cust
                        $sql="select * from ".$dbname.".pmn_4klcustomer where `kode`='".$bar->klcustomer."'";
                        $query=mysql_query($sql) or die(mysql_error($conn));
                        $res=mysql_fetch_object($query);
						
						//get Komoditi
						$sKo="select t1.*,t2.namabarang from ".$dbname.".pmn_4komoditi t1
							left join ".$dbname.".log_5masterbarang t2
							on t1.kodebarang = t2.kodebarang
							where `kodecustomer`='".$bar->kodecustomer."'";
						$qKo=mysql_query($sKo) or die(mysql_error($conn));
						$hasilKomoditi="";
						$hasilKomoditi2="";
						while($rKo=mysql_fetch_object($qKo)){
							$hasilKomoditi.=",".$rKo->kodebarang;
							$hasilKomoditi2.=",<br>".$rKo->namabarang;
						}
						
						//get Kontak Person
						$sPer="select * from ".$dbname.".pmn_4customercontact
							where `kodecustomer`='".$bar->kodecustomer."'";
						$qPer=mysql_query($sPer) or die(mysql_error($conn));
						$hasilPerson="";
						while($rPer=mysql_fetch_object($qPer)){
							$hasilPerson.=",<br>".$rPer->nama." (".$rPer->email.")";
						}
						
						//get akun
                        $spr="select * from  ".$dbname.".keu_5akun where `noakun`='".$bar->akun."'";
                        $rej=mysql_query($spr) or die(mysql_error($conn));
                        $bas=mysql_fetch_object($rej);
                        $no++;
						$bar->alamat = clearInvalidChar($bar->alamat);
						$bar->telepon = clearInvalidChar($bar->telepon);
						$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
                        echo"<tr class=rowcontent>
                                  <td style='vertical-align:top;'>".$no."</td>
                                  <td style='vertical-align:top;'>".substr($hasilKomoditi2,5)."</td>
                                  <td style='vertical-align:top;'>".$bar->kodecustomer."</td>
                                  <td style='vertical-align:top;'>".$bar->namacustomer."</td>
                                  <td style='vertical-align:top;'>".$bar->alamat."</td>
                                  <td style='vertical-align:top;'>".$bar->kota."</td>
                                  <td style='vertical-align:top;'>".$bar->telepon."</td>
                                  <td style='vertical-align:top;'>".$bar->npwp."</td>
                                  <td style='vertical-align:top;'>".$bar->alamatnpwp."</td>
                                  <td style='vertical-align:top;'>".$bar->penandatangan."</td>
                                  <td style='vertical-align:top;'>".$bar->jabatan."</td>
                                  <td style='vertical-align:top;'>".substr($hasilPerson,5)."</td>
                                  <td style='vertical-align:top;'>".$bar->statusinteks."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->plafon."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->nilaihutang."</td>
                                  <td style='vertical-align:top; text-align:right;'>".$bar->toleransipenyusutan."</td>
                                  <td style='vertical-align:top; text-align:center;'>".(($bar->statusberikat=='1') ? 'Y' : '')."</td>
                                  <td style='vertical-align:top;'>".$bar->keteranganberikat."</td>
                                  <td style='vertical-align:top;'><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kodecustomer."','".$bar->namacustomer."','".$bar->alamat."','".$bar->kota."','".$bar->telepon."','','".$bar->akun."','".$bar->plafon."','".$bar->nilaihutang."','".$bar->npwp."','".$bar->alamatnpwp."','".$bar->penandatangan."','".$bar->jabatan."','".$bar->noseri."','".$bar->klcustomer."','".(isset($bas->namaakun)? $bas->namaakun:'')."','','".$bar->toleransipenyusutan."','".$bar->statusberikat."','".$bar->keteranganberikat."','".substr($hasilKomoditi,1)."','".$bar->statusinteks."');\"></td>
                                  <td style='vertical-align:top;'><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPlgn('".$bar->kodecustomer."');\"></td>
                                 </tr>";
                        }
                  }
                  else
                 {
                        echo " Gagal,".(mysql_error($conn));
                 }
         ?>
          </tbody>
         <tfoot>
         </tfoot>
         </table>
</fieldset>
<?php
CLOSE_BOX();
echo close_body();
?>