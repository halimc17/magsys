<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##thnProd##kdBlok##jmBjr##proses";
$proses=checkPostGet('proses','');
$kdBlok=checkPostGet('kdBlok','');
$kdKebun=checkPostGet('kdKebun','');
$kelaspohon=checkPostGet('kelaspohon','');
$thnProd=checkPostGet('thnProd','');
$jmBjr=checkPostGet('jmBjr','');

$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

	switch($proses)
	{
		case'insert':
		if($jmBjr==''||$thnProd==''||$kdBlok=='')
		{
			echo"warning:Field tidak boleh kosong";
			exit();
		}
		else
		{
			$sDel="delete from ".$dbname.".kebun_5bjr where tahunproduksi='".$thnProd."' and kodeorg='".$kdBlok."'";
                        if(mysql_query($sDel))
                        {
                            $sIns="insert into ".$dbname.".kebun_5bjr (tahunproduksi,kodeorg,kelaspohon,bjr) values ('".$thnProd."','".$kdBlok."','".$kelaspohon."','".$jmBjr."')";
                            if(mysql_query($sIns))
                            echo"";
                            else
                                echo"Gagal:Db Error".$sIns."__".mysql_error();
                        }
                        else
                        {
                            echo"Gagal:Db Error".$sDel."__".mysql_error();
                        }
		}
		break;
		case'loadData':
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
//                 $sql2="select count(b.kodeorg) as jmlhrow from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
//                      where b.kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and a.luasareaproduktif!=0 order by b.kodeorg asc";
                 $sql2="select count(b.kodeorg) as jmlhrow from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
                      where b.kodeorg like '".$kdKebun."%' and b.tahunproduksi='".$thnProd."' and a.luasareaproduktif!=0 order by b.kodeorg asc";
                    //exit("Error".$sql2);
                    $query2=mysql_query($sql2) or die(mysql_error());
                    while($jsl=mysql_fetch_object($query2)){
                    $jlhbrs= $jsl->jmlhrow;
                    }
//		$str="select a.tahuntanam,a.jenisbibit,b.* from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
//                      where b.kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and tahunproduksi='".$thnProd."' and a.luasareaproduktif!=0 order by b.kodeorg asc  limit ".$offset.",".$limit."";
		$str="select a.tahuntanam,a.jenisbibit,b.*,c.nama from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
			left join ".$dbname.".kebun_5kelaspohon c on b.kelaspohon=c.kelas
                      where b.kodeorg like '".$kdKebun."%' and b.tahunproduksi='".$thnProd."' and a.luasareaproduktif!=0 order by b.kodeorg asc  limit ".$offset.",".$limit."";
		$res=mysql_query($str);
                $row=mysql_num_rows($res);
                if($row>0)
                {
		while($bar=mysql_fetch_assoc($res))
		{
                    
		$no+=1;	
		echo"<tr class=rowcontent>
		<td>".$no."</td>
		<td>".$optNmOrg[$bar['kodeorg']]."</td>
		<td style='display:none;'>".$bar['kelaspohon']." - ".$bar['nama']."</td>
		<td>".$bar['tahunproduksi']."</td>
		<td>".$bar['tahuntanam']."</td>
		<td>".$bar['jenisbibit']."</td>
		<td align=right>".number_format($bar['bjr'],2)."</td>
		<td>";
			  if($_SESSION['empl']['tipelokasitugas']=='HOLDING')echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['kelaspohon']."','".$bar['bjr']."');\"> 
			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$bar['tahunproduksi']."','".$bar['kodeorg']."');\">";
		  echo"</td>
		
		</tr>";	
		}     
                echo" <tr><td colspan=10 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";   
                }
                else
                {
                    echo "<tr class=rowcontent><td colspan=10>".$_SESSION['lang']['dataempty']."</td></tr>";
                echo" <tr><td colspan=10 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";   
                }
		break;
                case'loadBlok':
                    $optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $sBlok="select distinct kodeorg from ".$dbname.".setup_blok where kodeorg like '".$kdKebun."%' ";
                    $qBlok=mysql_query($sBlok) or die(mysql_error());
                    while($rBlok=mysql_fetch_assoc($qBlok))
                    {
                        $optBlok.="<option value='".$rBlok['kodeorg']."'>".$optNmOrg[$rBlok['kodeorg']]."</option>";
                    }          
                    echo $optBlok;
                break;
		case'update':
		if(($thnProd=='')||($jmBjr=='')||($kelaspohon==''))
		{
			echo"warning:Field tidak boleh kosong";
			exit();
		}
		else
		{
			$sDel="delete from ".$dbname.".kebun_5bjr where tahunproduksi='".$thnProd."' and kodeorg='".$kdBlok."'";
                        if(mysql_query($sDel))
                        {
                            $sIns="insert into ".$dbname.".kebun_5bjr (tahunproduksi,kodeorg,kelaspohon,bjr) values ('".$thnProd."','".$kdBlok."','".$kelaspohon."','".$jmBjr."')";
                            if(mysql_query($sIns))
                            echo"";
                            else
                                echo"Gagal:Db Error".$sIns."__".mysql_error();
                        }
                        else
                        {
                            echo"Gagal:Db Error".$sDel."__".mysql_error();
                        }
		}
		break;
		case'delData':
		$sDel="delete from ".$dbname.".kebun_5bjr where tahunproduksi='".$thnProd."' and kodeorg='".$kdBlok."'";
                 //   exit("error".$sDel);
                if(!mysql_query($sDel))
                {
			echo"Gagal".mysql_error($conn);
		}
		break;
		case'getData':
		$sDt="select * from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
		$qDt=mysql_query($sDt) or die(mysql_error($conn));
		$rDet=mysql_fetch_assoc($qDt);
		echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['contact']."###".$rDet['handphone']."###".$rDet['status'];
		break;
		default:
		break;
	}
?>