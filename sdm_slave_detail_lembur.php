<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$proses=checkPostGet('proses','');
$id=checkPostGet('absnId','');
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$arrTipeLembur=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya']);
switch($proses)
{
        case'createTable':
        $table .= "<table id='ppDetailTable'>";
        # Header
        $table .= "<thead>";
        $table .= "<tr>";
        $table .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
        $table .= "<td>".$_SESSION['lang']['tipelembur']."</td>";
        $table .= "<td>".$_SESSION['lang']['jamaktual'].' 1'."</td>";
        $table .= "<td>".$_SESSION['lang']['uangkelebihanjam'].' 1'."</td>";
        $table .= "<td>".$_SESSION['lang']['jamaktual'].' 2'."</td>";
        $table .= "<td>".$_SESSION['lang']['uangkelebihanjam'].' 2'."</td>";
        $table .= "<td>".$_SESSION['lang']['beban'].' '.$_SESSION['lang']['jamaktual'].' 2'."</td>";
        $table .= "<td>".$_SESSION['lang']['total'].' '.$_SESSION['lang']['uangkelebihanjam']."</td>";
        $table .= "<td>".$_SESSION['lang']['penggantiantransport']."</td>";
        $table .= "<td>".$_SESSION['lang']['uangmakan']."</td>";
        $table .= "<td>No. BA</td>";
        $table .= "<td>Action</td>";
        $table .= "</tr>";
        $table .= "</thead>";

    # Data
    $table .= "<tbody id='detailBody'>";
        $idAbn=explode("###",$id);

        $sTpLmbr2="select tipelembur from ".$dbname.".sdm_5lembur where kodeorg='".substr($idAbn[0],0,4)."'";//echo"warning:".$sTpLmbr2;
        $qTpLmbr2=mysql_query($sTpLmbr2) or die(mysql_error());
        while($rTpLmbr2=mysql_fetch_assoc($qTpLmbr2))
        {
                $optLmbr2.="<option value=".$rTpLmbr2['tipelembur']." >".$arrTipeLembur[$rTpLmbr2['tipelembur']]."</option>";
        }
        if(strlen($idAbn[0])>4)
        {
                $where=" subbagian='".$idAbn[0]."'";
        }
        else
        {
                $where=" lokasitugas='".$idAbn[0]."'"; //echo"warning:".$where;exit();
        }
        $optKry=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where,0);
        $optAbsen=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan') ;
        for($t=0;$t<24;)
        {
                if(strlen($t)<2)
                {
                        $t="0".$t;
                }
                $jm.="<option value=".$t." ".($t==00?'selected':'').">".$t."</option>";
                $t++;
        }
        for($y=0;$y<60;)
        {
                if(strlen($y)<2)
                {
                        $y="0".$y;
                }
                $mnt.="<option value=".$y." ".($y==00?'selected':'').">".$y."</option>";
                $y++;
        }
		$optbeban="<option value=''></option>";
		//exit('Warning: 1='.$_SESSION['empl']['lokasitugas'].' '.substr($_SESSION['empl']['lokasitugas'],3,1));
		if(substr($_SESSION['empl']['lokasitugas'],3,1)=='M'){
	        $sBeban1="select noakundebet,keterangan from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='PKS' and jurnalid='PKS09'";
		    $qBeban1=mysql_query($sBeban1) or die(mysql_error());
	        while($rBeban1=mysql_fetch_assoc($qBeban1)){
				$optbeban.="<option value='Loading_CPO'>".$rBeban1['keterangan']."</option>";
			}
	        $sBeban2="select a.kode,b.akunak,a.nama from ".$dbname.".project a
						LEFT JOIN ".$dbname.".sdm_5tipeasset b on b.kodetipe=substr(a.kode,4,2)
						where kodeorg='".$_SESSION['empl']['lokasitugas']."' and posting=0";
		    $qBeban2=mysql_query($sBeban2) or die(mysql_error());
	        while($rBeban2=mysql_fetch_assoc($qBeban2)){
				$optbeban.="<option value=".$rBeban2['kode'].">Project - ".$rBeban2['nama']."</option>";
			}
		}
        $table .= "<tr id='detail_tr' class='rowcontent'>";
        $table .= "<td>".makeElement("krywnId",'select','',
        array('style'=>'width:150px'),$optKry)."</td>";
        $table .= "<td><select id=tpLmbr>".$optLmbr2."</select></td>";
        $table .= "<td><select id=jmId name=jmId >".$jm."</select>:<select id=mntId name=mntId >".$mnt."</select></td>";
        $table .= "<td>".makeElement("uang_lbhjm",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_3()"))."</td>";
        $table .= "<td><select id=jmId2 name=jmId2 >".$jm."</select>:<select id=mntId2 name=mntId2 >".$mnt."</select></td>";
        $table .= "<td>".makeElement("uang_lbhjm2",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_4()"))."</td>";
        $table .= "<td><select id=beban>".$optbeban."</select></td>";
        $table .= "<td>".makeElement("uanglembur",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_5()"))."</td>";
        $table .= "<td>".makeElement("uang_trnsprt",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_2()"))."</td>";
        $table .= "<td>".makeElement("uang_mkn",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_1()"))."</td>";
        $table .= "<td>".makeElement("noba",'text','',array('style'=>'width:180px','maxlength'=>'30'))."</td>";

    # Add, Container Delete
    $table .= "<td><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
    $table .= "&nbsp;<img id='detail_delete' /></td>";
    $table .= "</tr>";
    $table .= "</tbody>";
    $table .= "</table>";
    echo $table;
        break;
    
	case'loadDetail':
        $sDt="select * from ".$dbname.".sdm_lemburdt where kodeorg='".$kdOrg."' and tanggal='".$tgl."'";
        $qDt=mysql_query($sDt) or die(mysql_error());
		$totum=$totut=$totle=0;
        while($rDet=mysql_fetch_assoc($qDt))
        {
                $sNm="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."'";
                $qNm=mysql_query($sNm) or die(mysql_error());
                $rNm=mysql_fetch_assoc($qNm);
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rNm['namakaryawan']."</td>
                <td>".$arrTipeLembur[$rDet['tipelembur']]."</td>
                <td align=right>".$rDet['jamaktual']."</td>
                <td align=right>".number_format($rDet['uangkelebihanjam'],2)."</td>
                <td align=right>".$rDet['jamaktual2']."</td>
                <td align=right>".number_format($rDet['uanglembur2'],2)."</td>
                <td>".$rDet['beban']."</td>
                <td align=right>".number_format($rDet['uangkelebihanjam']+$rDet['uanglembur2'],2)."</td>
                <td align=right>".number_format($rDet['uangtransport'],2)."</td>
                <td align=right>".number_format($rDet['uangmakan'],2)."</td>
                <td>".$rDet['noba']."</td>
                <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editDetail('".$rDet['karyawanid']."','".$rDet['tipelembur']."','".$rDet['jamaktual']."','".$rDet['jamaktual2']."','".$rDet['uangmakan']."','".$rDet['uangtransport']."','".$rDet['uangkelebihanjam']."','".$rDet['uanglembur2']."','".$rDet['noba']."','".$rDet['beban']."');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$rDet['kodeorg']."','".tanggalnormal($rDet['tanggal'])."','".$rDet['karyawanid']."');\" ></td>
                </tr>
                ";
				$totum+=$rDet['uangmakan'];
                $totut+=$rDet['uangtransport'];
                $totle1+=$rDet['uangkelebihanjam'];
                $totle2+=$rDet['uanglembur2'];
                $totle+=$rDet['uangkelebihanjam']+$rDet['uanglembur2'];
        }
                echo"
                <tr class=rowcontent>
                <td colspan=4>Total</td>
                <td align=right>".number_format($totle1,2)."</td>
                <td colspan=1></td>
                <td align=right>".number_format($totle2,2)."</td>
                <td colspan=1></td>
                <td align=right>".number_format($totle,2)."</td>
                <td align=right>".number_format($totut,2)."</td>
                <td align=right>".number_format($totum,2)."</td>
                <td></td>
                <td></td>
                </tr>
                ";
        break;
        default:
        break;
}
?>
