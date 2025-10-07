<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$proses=checkPostGet('proses','');
$kdOrg=checkPostGet('kdDiv','');
$noSpb=checkPostGet('noSpb','');
$periode=checkPostGet('periode','');
$tgl=explode('-',checkPostGet('tgl',''));
$tglThn= count($tgl)>2? $tgl[2]: '';
$tglBln= count($tgl)>1? $tgl[1]: '';
$periodeB=$tglThn."-".$tglBln;

switch($proses)
{
	case 'createTable':
            $cekPost="select distinct posting from ".$dbname.".kebun_spbht where nospb='".$noSpb."'";
            $qcekPost=mysql_query($cekPost) or die(mysql_error($conn));
            $rCek=mysql_fetch_assoc($qcekPost);
                    if($rCek['posting']!=0)
                    {
                        exit("Error:Nospb Sudah Posting");
                    }
	//$kodeOrg=substr($id,8,6);
	//echo"warning:".$periode."___".$periodeB;exit();
	if($periode!=$periodeB)
	{
		echo"warning:Tanggal dan Periode tidak sama";
		exit();
	}
	
	if(!isset($_POST['statusCek']) or $_POST['statusCek']==0){
            $where=" tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$kdOrg."' and luasareaproduktif!=0)"; //echo"warning:".$where;exit();
        }else{
            $where=" tipe='BLOK' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".substr($kdOrg,0,4)."' and luasareaproduktif!=0)"; //echo"warning:".$where;exit();
        }
	$optBlok=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$where,'0',true);
	//$table .= "<table id='ppDetailTable'>";
	//echo"warning:".$table;
    # Header
	$table = "<thead>";
        $table .= "<tr class='rowheader'>";
        $table .= "<td>".$_SESSION['lang']['blok']."</td>";
        $table .= "<td>".$_SESSION['lang']['bjr'].'(Kg)'."</td>";
        $table .= "<td>".$_SESSION['lang']['janjang']."</td>";
        $table .= "<td>".$_SESSION['lang']['brondolan']."</td>";
        $table .= "<td hidden><font size=0.5>Kg WB (Khusus ke Pabrik Luar)</font></td>";
        $table .= "<td>".$_SESSION['lang']['mentah']."</td>";
        $table .= "<td>".$_SESSION['lang']['busuk']."</td>";
        $table .= "<td>".$_SESSION['lang']['matang']."</td>";
        $table .= "<td>".$_SESSION['lang']['lewatmatang']."</td>";
        $table .= "<td colspan=3>Action</td>";
        $table .= "</tr>";
        $table .= "</thead>";
	$table .= "<tbody id='detailBody'>";
        
	$table .= "<tr id='detail_tr' class='rowcontent'>";
	$table .= "<td>".makeElement("blok",'select','',
        array('style'=>'width:150px','onchange'=>"getBjr()"),$optBlok)."<img src=images/search.png class=dellicon onclick=\"searchBrg('".$_SESSION['lang']['find']." ".$_SESSION['lang']['blok']."','<fieldset><legend>".$_SESSION['lang']['find']."</legend>".$_SESSION['lang']['blok']."<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>".$_SESSION['lang']['find']."</button></fieldset><div id=container></div><input type=hidden id=kdafd value=".$kdOrg." />',event)\"; /><input type=hidden id=oldBlok name=oldBlok value='' /></td>";
	//array('style'=>'width:150px','onchange'=>""),$optBlok)."<input type=hidden id=oldBlok name=oldBlok value='' /></td>";
        $table .= "<td>".makeElement("bjr",'textnum','0',
	array('style'=>'width:80px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
	$table .= "<td>".makeElement("jjng",'textnum','0',
	array('style'=>'width:80px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
	$table .= "<td>".makeElement("brondln",'textnum','0',
	array('style'=>'width:80px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
        
        $table .= "<td hidden>".makeElement("kgwb",'textnum','0',
	array('style'=>'width:135px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5'))."</td>";
	
        $table .= "<td>".makeElement("mnth",'textnum','0',
	array('style'=>'width:30px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
	$table .= "<td>".makeElement("bsk",'textnum','0',
	array('style'=>'width:30px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
	$table .= "<td>".makeElement("mtng",'textnum','0',
	array('style'=>'width:30px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
	$table .= "<td>".makeElement("lwtmtng",'textnum','0',
	array('style'=>'width:30px','onkeypress'=>'return angka_doang(event)','maxlength'=>'5','disabled'=>'true'))."</td>";
	
    # Add, Container Delete
    $table .= "<td><img id='detail_add' title=".$_SESSION['lang']['save']." class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
    $table .= "&nbsp;<img id='detail_delete' /></td>";
    $table .= "</tr>";
    $table .="<tr><td colspan=10><font color=red>KG WB di isi untuk Kebun yang belum memiliki Mill (Pabrik)</font></td></tr>";
    $table .= "</tbody>";
  //  $table .= "</table>";
    echo $table;
	break;
	case 'detail_add' :
			$lokasi=$_SESSION['empl']['lokasitugas'];
			$lokasi=substr($lokasi,0,4);
			$entry_by=$_SESSION['standard']['userid'];
			#Check Header
			
			if(($data['jjng']=='') or ($data['brondolan']=='') or ($data['bjr']=='')) {
                echo "Error : Tolong lengkap data detail, data tidak boleh kosong";
                exit();
            }
			/*if(($data['jjng']==0) or ($data['bjr']==0) ) {
                echo "Error : ".$_SESSION['lang']['bjr'].",".$_SESSION['lang']['jjg']." tidak boleh kosong atau nol";
                exit();
            }*/
			$sql="select nospb from ".$dbname.".kebun_spbht where nospb='".$_POST['noSpb']."'";
			$query=mysql_query($sql) or die(mysql_error());
			$res=mysql_fetch_row($query);
			//echo "warning:".$res;exit();
			if($res<1)
			{
				$sins="insert into ".$dbname.".kebun_spbht (`nospb`, `kodeorg`, `tanggal`,`updateby`,`tujuan`,`penerimatbs`) values 
				('".$_POST['noSpb']."','".$_POST['kodeOrg']."','".tanggalsystem($_POST['tgl'])."','".$entry_by."','".$_POST['intex']."','".$_POST['pks']."')";
                               
				if(mysql_query($sins))
				{
					$kgBjr=intval($_POST['jjng'])*intval($_POST['bjr']);
					$dins="insert into ".$dbname.".kebun_spbdt (nospb, blok, jjg, bjr, brondolan,  mentah, busuk, matang, lewatmatang,kgbjr,kgwb) 
					values ('".$_POST['noSpb']."','".$_POST['blok']."','".$_POST['jjng']."','".$_POST['bjr']."',
					'".$_POST['brondolan']."','".$_POST['mentah']."','".$_POST['busuk']."','".$_POST['matang']."','".$_POST['lwtmatang']."','".$kgBjr."','".$_POST['kgwb']."')";
					//echo "warning:test".$dins;
					if(mysql_query($dins))
					{
						echo"";
					}
					else
					{
					//echo "warning:masuk";
					echo "DB Error : ".mysql_error($conn);
					}
				}
				else
				{
					echo "DB Error : ".mysql_error($conn);
				}
			}
			else
			{
				$kgBjr=intval($_POST['jjng'])*intval($_POST['bjr']);
				$dins="insert into ".$dbname.".kebun_spbdt (nospb, blok, jjg, bjr, brondolan, mentah, busuk, matang, lewatmatang,kgbjr,kgwb) 
				values ('".$_POST['noSpb']."','".$_POST['blok']."','".$_POST['jjng']."','".$_POST['bjr']."',
					'".$_POST['brondolan']."','".$_POST['mentah']."','".$_POST['busuk']."','".$_POST['matang']."','".$_POST['lwtmatang']."','".$kgBjr."','".$_POST['kgwb']."')";
				//echo "warning:test".$dins;
				if(mysql_query($dins))
				{
					echo"";
				}
				else
				{
					echo "DB Error : ".mysql_error($conn);
				}
			}
            break;
	case'loadDetail':
	$sDet="select a.*,b.namaorganisasi from ".$dbname.".kebun_spbdt a left join ".$dbname.".organisasi b on b.kodeorganisasi=a.blok where nospb='".$noSpb."' order by blok desc";
	//echo $sDet;
	$qDet=mysql_query($sDet) or die(mysql_error());
	$no=0;
	while($rDet=mysql_fetch_assoc($qDet))
	{
		$no+=1;
		echo"<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$rDet['namaorganisasi']."</td>
		<td>".$rDet['bjr']."</td>
		<td>".$rDet['jjg']."</td>
		<td>".$rDet['brondolan']."</td>
		<td>".$rDet['kgwb']."</td>
		<td>".$rDet['mentah']."</td>
		<td>".$rDet['busuk']."</td>
		<td>".$rDet['matang']."</td>
		<td>".$rDet['lewatmatang']."</td>
		<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDetail('".$rDet['nospb']."','".$rDet['blok']."','".$rDet['jjg']."','".$rDet['bjr']."','".$rDet['brondolan']."','".$rDet['mentah']."','".$rDet['busuk']."','".$rDet['matang']."','".$rDet['lewatmatang']."','".$rDet['kgwb']."');\">
			<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$rDet['nospb']."','".$rDet['blok']."');\" ></td>
		</tr>
		";
	}
	break;
        case'getBlokSma':
        $optKdBlok="<option value=''></option>";    
        $sdt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where
              induk like '".substr($_POST['kdAfd'],0,4)."%'  and tipe='BLOK' order by namaorganisasi asc";
        //exit("error:".$sdt);
        $qdt=mysql_query($sdt) or die(mysql_error($conn));
        while($rdt=  mysql_fetch_assoc($qdt)){
            $optKdBlok.="<option value='".$rdt['kodeorganisasi']."'>".$rdt['namaorganisasi']."</option>";
        }
        echo $optKdBlok;
        break;
        case'getBlokNor':
        $optKdBlok="<option value=''></option>";    
        $sdt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where
              induk='".$_POST['kdAfd']."' and tipe='BLOK' order by namaorganisasi asc";
        $qdt=mysql_query($sdt) or die(mysql_error($conn));
        while($rdt=  mysql_fetch_assoc($qdt)){
            $optKdBlok.="<option value='".$rdt['kodeorganisasi']."'>".$rdt['namaorganisasi']."</option>";
        }
        echo $optKdBlok;
        break;
	case'cariBlok':
        $tab.="<fieldset>
               <legend>Result</legend>
               <div style=\"overflow:auto; height:300px;\" >
               <table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab.="<thead><tr><td>No.</td>";
        $tab.="<td>".$_SESSION['lang']['blok']."</td>";
        $tab.="<td>".$_SESSION['lang']['namaorganisasi']."</td></tr></thead><tbody>";
        if($_POST['idCer']==1){
            $dhr=" induk like '".substr($_POST['kdAfd'],0,4)."%' 
                    and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".substr($_POST['kdAfd'],0,4)."' and luasareaproduktif!=0)";
        }else{
            $dhr=" induk='".$_POST['kdAfd']."' and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$_POST['kdAfd']."' and luasareaproduktif!=0)";
        }
        $sdt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where
              ".$dhr." and tipe='BLOK' and namaorganisasi like '%".$_POST['txtfind']."%' order by namaorganisasi asc";
        $qdt=mysql_query($sdt) or die(mysql_error($conn));
        while($rdt=  mysql_fetch_assoc($qdt)){
            $ert+=1;
            $tab.="<tr class=rowcontent onclick=\"setBlok('".$rdt['kodeorganisasi']."')\" style='cursor:pointer;'><td>".$ert."</td>";
            $tab.="<td>".$rdt['kodeorganisasi']."</td>";
            $tab.="<td>".$rdt['namaorganisasi']."</td></tr>";
        }
        $tab.="</tbody></table></div></fieldset>";
        echo $tab;
        break;
}

?>