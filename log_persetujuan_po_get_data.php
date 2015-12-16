<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');

$method=$_POST['method'];
$nopo=$_POST['nopo'];
$user_id=$_SESSION['standard']['userid'];

switch($method) {
    case 'get_form_approval':
        $sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
        $query=mysql_query($sql) or die(mysql_error());
        $rest=mysql_fetch_assoc($query);

        for($i=1;$i<4;$i++) {
            if($user_id==$rest['persetujuan'.$i]) {
				if($rest['persetujuan3']!='') {
					echo"<br /><div id=approve>
					<fieldset>
					<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
					<table cellspacing=1 border=0>
					<tr>
					<td colspan=3>
					Submit to Purchasing dept for Release</td></tr>

					<tr><td colspan=3 align=center>
					<button class=mybutton onclick=close_po() >".$_SESSION['lang']['yes']."</button><button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button></td></tr></table><input type=hidden name=kolom id=kolom />
					</fieldset>
					</div>";
				} else {	
					echo"<br />
					<div id=test>
					<fieldset>
					<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
					<table cellspacing=1 border=0>
					<tr>
					<td colspan=3>
					Submit for the next verification :</td>
					</tr>
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td>:</td>
					<td valign=top>";

					$optPur='';
					$klq="select karyawanid,namakaryawan,bagian,lokasitugas from ".$dbname.".`datakaryawan` where tipekaryawan='0' and karyawanid!='".$user_id."' and lokasitugas!='' order by namakaryawan asc"; 
					//echo $klq;
					$qry=mysql_query($klq) or die(mysql_error());
					while($rst=mysql_fetch_object($qry))
					{
							$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
							$qBag=mysql_query($sBag) or die(mysql_error());
							$rBag=mysql_fetch_assoc($qBag);
							$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
					}

                    echo"
						<select id=id_user name=id_user>
								$optPur; 
						</select></td></tr>
						<tr>

						<td colspan=3 align=center>
						<button class=mybutton onclick=forward_po() title=\"Submit for the next verification\" >".$_SESSION['lang']['diajukan']."</button>
						<button class=mybutton onclick=close_form_po() title=\"Submit to Purchasing dept for Release\"  >".$_SESSION['lang']['kePurchaser']."</button>
						<button class=mybutton onclick=cancel_po() title=\"Menutup Form Ini\">".$_SESSION['lang']['close']."</button>
						</td></tr></table><br /> 

						</fieldset></div>
						<div id=approve style=display:none>
						<fieldset>
						<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
						<table cellspacing=1 border=0>
						<tr>
						<td colspan=3>
						Submit to Purchasing dept for Release</td></tr>

						<tr><td colspan=3 align=center>
						<button class=mybutton onclick=close_po() >".$_SESSION['lang']['yes']."</button>
						<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button></td></tr></table>
						</fieldset>
						</div>
						<input type=hidden name=method id=method  /> 
						<input type=hidden name=user_id id=user_id value=".$user_id." />
						<input type=hidden name=nopo id=nopo value=".$nopo."  />
						<input type=hidden name=kolom id=kolom />
						";
                }
            }
        }
        break;
	
    case 'get_form_rejected':
		echo"<div id=rejected_form>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		Are you sure rejecting this PO</td></tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=rejected_po_proses() >".$_SESSION['lang']['yes']."</button>
		<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button>
		</td></tr></table>
		</fieldset>
		</div>
		<input type=hidden name=method id=method  /> 
		<input type=hidden name=user_id id=user_id value=".$user_id." />
		<input type=hidden name=nopo id=nopo value=".$nopo."  />
		<input type=hidden name=kolom id=kolom />
		";
		break;
	
    default:
        break;
}