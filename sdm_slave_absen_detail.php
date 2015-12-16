<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$proses=checkPostGet('proses','');
$absnId=checkPostGet('absnId','');
$kdOrg=checkPostGet('kdOrg','');
$tgAbsn=tanggalsystem(checkPostGet('tgAbsn',''));

        switch($proses)
        {
                case'createTable':
                //$thisDate=date("Y-m-d");
                $table = "<table id='ppDetailTable'>";
                //echo"warning:".$table;
                # Header
                $table .= "<thead>";
                $table .= "<tr class=rowheader>";
                $table .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
                $table .= "<td>".$_SESSION['lang']['shift']."</td>";
                $table .= "<td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['premi']."</td>"; 
                $table .= "<td>".$_SESSION['lang']['absensi']."</td>";
                $table .= "<td>".$_SESSION['lang']['jamMsk']."</td>";
                $table .= "<td>".$_SESSION['lang']['jamPlg']."</td>";
                $table .= "<td style='display:none'>".$_SESSION['lang']['pembagiancatu']."</td>"; 
                
                $table .= "<td title='kehadiran kurang dari 7 jam/Presence under 7 hours'>".$_SESSION['lang']['penaltykehadiran']."</td>"; 
                $table .= "<td>".$_SESSION['lang']['premi']."</td>";
                $table .= "<td>".$_SESSION['lang']['keterangan']."</td>";
                $table .= "<td>Action</td>";
                $table .= "</tr>";
                $table .= "</thead>";

                # Data
                $table .= "<tbody id='detailBody'>";
                $idAbn=explode("###",$absnId);
                $tgl=tanggalsystem($idAbn[1]);
                if(strlen($idAbn[0])>4)
                {
                        $where=" a.subbagian='".$idAbn[0]."'  and (a.tanggalkeluar>".$tgl." or a.tanggalkeluar='0000-00-00')";
                }
                else
                {
                        $where=" a.lokasitugas='".$idAbn[0]."' and (a.subbagian IS NULL or a.subbagian='0' or a.subbagian='') and (a.tanggalkeluar>".$tgl." or a.tanggalkeluar='0000-00-00')";
                }

                ##opt karyawan lama    
                //$optKry=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan,nik',$where,5);
                ##opt baru
				$optKry='';
                $iKar="select a.karyawanid,a.namakaryawan,a.nik,a.subbagian,a.tipekaryawan,b.tipe "
                        . "from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b "
                        . "on a.tipekaryawan=b.id where ".$where." and a.tipekaryawan not in ('0','7','8') ";
                $nKar=  mysql_query($iKar) or die (mysql_error($conn));
                while($dKar=  mysql_fetch_assoc($nKar))
                {
                    $optKry.="<option value='".$dKar['karyawanid']."'>".$dKar['namakaryawan']." [".$dKar['nik']."] ".$dKar['tipe']."</option>";
                }

                $whre=" kodeorg='".$idAbn[0]."'";
                $optShift=makeOption($dbname,'pabrik_5shift','shift,shift',$whre) ;
                $optAbsen=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan') ;
				$jm=$mnt="";
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

                $table .= "<tr id='detail_tr' class='rowcontent'>";
                
                //$table .= "<td>".makeElement("krywnId",'select','',
               // array('style'=>'width:150px','onchange'=>'bersihFormDetail()'),$optKry)."</td>";
                $table .= "<td><select id=krywnId name=krywnId onchange=bersihFormDetail() >".$optKry."</select></td>";
                $table .= "<td>".makeElement("shiftId",'text','',
                array('style'=>'width:120px','onkeypress'=>'return tanpa_kutip(event)'))."</td>";
                $table .= "<td><select id=premiPil name=premiPil ><option value=1>Yes</option><option value=0>No</option></select></td>";
                $table .= "<td>".makeElement("absniId",'select','',
                array('style'=>'width:100px'),$optAbsen)."</td>";
                $table .= "<td><select id=jmId name=jmId  >".$jm."</select>:<select id=mntId name=mntId onchange=getPremiTetap()>".$mnt."</select></td>";
                $table .= "<td><select id=jmId2 name=jmId2 >".$jm."</select>:<select id=mntId2 name=mntId2 onchange=getPremiTetap()>".$mnt."</select></td>";
                $table .= "<td style='display:none'><select id=catu name=catu><option value=1>Yes</option><option value=0>No</option></select></td>";
                
                $table .= "<td><input type=text id=dendakehadiran class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" value=0></td>";
                $table .= "<td><input type=text id=premiInsentif class=myinputtextnumber size=12 onkeypress=\"return angka_doang(event)\" /></td>";
                $table .= "<td>".makeElement("ktrng",'text','',
                array('style'=>'width:150px','onkeypress'=>'return tanpa_kutip(event)'))."</td>";
                # Add, Container Delete
                $table .= "<td><input type=hidden id=insentif /><input type=hidden id=premi /><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
                $table .= "&nbsp;<img id='detail_delete' /></td>";
                $table .= "</tr>";
                $table .= "</tbody>";
                $table .= "</table>";
                echo $table;
                break;
                case'loadDetail':
                $sDt="select * from ".$dbname.".sdm_absensidt where kodeorg='".$kdOrg."' and tanggal='".$tgAbsn."'";
                $qDt=mysql_query($sDt) or die(mysql_error());
                while($rDet=mysql_fetch_assoc($qDt))
                {
                    
                    $optTipeKar=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
                    
                    
                        $sNm="select namakaryawan,nik,tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."'";
                        $qNm=mysql_query($sNm) or die(mysql_error());
                        $rNm=mysql_fetch_assoc($qNm);
                        
                        

                        $sAbsn="select keterangan from ".$dbname.".sdm_5absensi where kodeabsen='".$rDet['absensi']."'";
                        $qAbsn=mysql_query($sAbsn) or die(mysql_error());
                        $rAbsn=mysql_fetch_assoc($qAbsn);
                        $no+=1;
                        $strot=0;
                        $drpermi=$rDet['premi']+$rDet['insentif'];
                        if($drpermi!=0){
                            $strot=1;
                        }
                        echo"
                        <tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$rNm['namakaryawan']." - ".$rNm['nik']." - ".$optTipeKar[$rNm['tipekaryawan']]."</td>
                        <td>".$rDet['shift']."</td>
                        <td>".$rAbsn['keterangan']."</td>
                        <td>".$rDet['jam']."</td>
                        <td>".$rDet['jamPlg']."</td>
                        <td style='display:none'>".($rDet['catu']=='1'?'Yes':'No')."</td>
                        <td>".number_format($drpermi)."</td>
                        <td>".number_format($rDet['penaltykehadiran'])."</td>
                        <td>".$rDet['penjelasan']."</td>
                        <td><img src=images/application/application_edit.png class=resicon  title='Edit' 
                        onclick=\"editDetail('".$rDet['karyawanid']."','".$rDet['shift']."','".$rDet['absensi']."','".$rDet['jam']."','".$rDet['jamPlg']."','".$rDet['penjelasan']."','".$rDet['catu']."','".$rDet['penaltykehadiran']."','".$rDet['premi']."','".$rDet['insentif']."','".($rDet['premi']+$rDet['insentif'])."','".$strot."');\">
                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$rDet['kodeorg']."','".tanggalnormal($rDet['tanggal'])."','".$rDet['karyawanid']."');\" ></td>
                        </tr>
                        ";
                }

                break;
                default:
                break;
        }

?>