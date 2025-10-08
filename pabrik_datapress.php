<?php //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/pabrik_datapress.js'></script>
<?php
include('master_mainMenu.php');


OPEN_BOX('',"<b>".'Data Press Dan Air'."</b>");
//get org
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and tipe='PABRIK'";
$res=mysql_query($str);
$optorg='';
while($bar=mysql_fetch_object($res))
{
	$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}
echo "<fieldset style='width:860px;'>
		<legend>".$_SESSION['lang']['form'].":</legend>
		<table>
			<tr>
				<td valign=top>  
					<table>
						<tr>
							<td>".$_SESSION['lang']['kodeorganisasi']."</td>
							<td><select id=kodeorg>".$optorg."</select></td>
						</tr>
						<tr> 
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td><input type=text class=myinputtext onchange=getData() id=tanggal size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"></td>
						</tr>
					</table>
				</td>

				<td valign=top>  
  					<table>
						<tr>
							<td> 
								<fieldset><legend>Tek. Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td><input type=text id=tekpressp1 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P2</td>
											<td><input type=text id=tekpressp2 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P3</td>
											<td><input type=text id=tekpressp3 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P4</td>
											<td><input type=text id=tekpressp4 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
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
								<fieldset><legend>Suhu Digester</legend>
									<table>
										<tr>
											<td>D1</td>
											<td><input type=text id=suhud1 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>D2</td>
											<td><input type=text id=suhud2 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>D3</td>
											<td><input type=text id=suhud3 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>D4</td>
											<td><input type=text id=suhud4 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
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
								<fieldset><legend>Jam Press</legend>
									<table>
										<tr>
											<td>P1</td>
											<td><input type=text id=jampressp1 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P2</td>
											<td><input type=text id=jampressp2 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P3</td>
											<td><input type=text id=jampressp3 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
										</tr>
										<tr>
											<td>P4</td>
											<td><input type=text id=jampressp4 value=0 class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"></td>
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
								<fieldset><legend>Air</legend>
									<table>
										<tr>
											<td>Air Sisa kemarin (Bak Basin)</td>
											<td><input type=text id=airkemarin value=0 disabled class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>
										<tr>
											<td>Air Clarifier Tank</td>
											<td><input type=text id=airclarifier value=0 class=myinputtextnumber onblur=hitungsisaair() maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>	
										<tr>
											<td>Air Boiler</td>
											<td><input type=text id=airboiler value=0 class=myinputtextnumber onblur=hitungsisaair() maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>	
										<tr>
											<td>Air Produksi</td>
											<td><input type=text id=airproduksi value=0 class=myinputtextnumber onblur=hitungsisaair() maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>	
										<tr>
											<td>Air Pembersihan</td>
											<td><input type=text id=airpembersihan value=0 class=myinputtextnumber onblur=hitungsisaair() maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>	
										<tr>
											<td>Air Domestik Camp</td>
											<td><input type=text id=airdomestik value=0 class=myinputtextnumber onblur=hitungsisaair() maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
										</tr>	
										<tr>
											<td>Air Sisa (Bak Basin)</td>
											<td><input type=text id=airsisa value=0 disabled class=myinputtextnumber maxlength=5 size=8 onkeypress=\"return angka_doang(event);\"> M3</td>
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
			<button class=mybutton onclick=simpanData()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=bersihkanForm()>".$_SESSION['lang']['cancel']."</button>
		</center>
	</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
		<table class=sortable cellspacing=1 border=0 width=100%>
			<thead>
				<tr class=rowheader>
					<td rowspan=2 align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
					<td colspan=4 align=center>".'Tek. Press'."</td>
					<td colspan=4 align=center>".'Suhu Digester'."</td>
					<td colspan=4 align=center>".'Jam. Press'."</td>
					<td rowspan=2 align=center>".'Air Sisa Kemarin'."</td>
					<td rowspan=2 align=center>".'Air Clarifier Tank'."</td>
					<td rowspan=2 align=center>".'Air Boiler'."</td>
					<td rowspan=2 align=center>".'Air Produksi'."</td>
					<td rowspan=2 align=center>".'Air Pembersihan'."</td>
					<td rowspan=2 align=center>".'Air Domestik Camp'."</td>
					<td rowspan=2 align=center>".'Air Sisa'."</td>
					<td rowspan=2 align=center>Action</td>	   
				</tr>
				<tr class=rowheader> 
					<td align=center>P1</td>
					<td align=center>P2</td>
					<td align=center>P3</td>
					<td align=center>P4</td>
					<td align=center>D1</td>
					<td align=center>D2</td>
					<td align=center>D3</td>
					<td align=center>D4</td>
					<td align=center>P1</td>
					<td align=center>P2</td>
					<td align=center>P3</td>
					<td align=center>P4</td>
				</tr>
			</thead>
			<tbody id=container>";
$str="select a.* from ".$dbname.".pabrik_datapress a
      where kodeorg='".$_SESSION['empl']['lokasitugas']."'
      order by a.tanggal desc limit 31";
$res=mysql_query($str);
while($bar=mysql_fetch_object($res)){
    $airkemarin=$bar->airsisa-$bar->airclarifier+$bar->airboiler+$bar->airproduksi+$bar->airpembersihan+$bar->airdomestik;
    $drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
	echo"<tr class=rowcontent >
			<td ".$drcl." align=center>".$bar->kodeorg."</td>
			<td ".$drcl." align=center>".tanggalnormal($bar->tanggal)."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp1,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp2,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp3,0,'.',',.')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->tekpressp4,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->suhud1,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->suhud2,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->suhud3,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->suhud4,0,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp1,2,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp2,2,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp3,2,'.',',')."</td>
			<td ".$drcl." align=right width='4%'>".number_format($bar->jampressp4,2,'.',',')."</td>

			<td ".$drcl." align=right width='6%'>".number_format($airkemarin,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airclarifier,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airboiler,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airproduksi,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airpembersihan,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airdomestik,0,'.',',')."</td>
			<td ".$drcl." align=right width='6%'>".number_format($bar->airsisa,0,'.',',')."</td>
		   
			<td>
				<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kodeorg."','".tanggalnormal($bar->tanggal)."','".$bar->tekpressp1."','".$bar->tekpressp2."','".$bar->tekpressp3."'
				,'".$bar->tekpressp4."','".$bar->suhud1."','".$bar->suhud2."','".$bar->suhud3."','".$bar->suhud4."','".$bar->jampressp1."','".$bar->jampressp2."'
				,'".$bar->jampressp3."','".$bar->jampressp4."','".$airkemarin."','".$bar->airclarifier."','".$bar->airboiler."','".$bar->airproduksi."'
				,'".$bar->airpembersihan."','".$bar->airdomestik."','".$bar->airsisa."',)\">&nbsp
				<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deldata('".$bar->kodeorg."','".$bar->tanggal."','".(isset($bar->kodebarang)? $bar->kodebarang:'')."');\">
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