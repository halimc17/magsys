<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
//$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";
$proses=$_POST['proses'];
$kdBarang=$_POST['kdBarang'];
$satuan=$_POST['satuan'];
$idPasar=$_POST['idPasar'];
$idMatauang=$_POST['idMatauang'];
$hrgPasar=$_POST['hrgPasar'];
$ffa=$_POST['ffa'];
$status=$_POST['status'];
$mni=$_POST['mni'];
$kdBrgCari=$_POST['kdBrgCari'];
$tglHarga=tanggalsystem($_POST['tglHarga']);

$optNmBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$where="tanggal='".$tglHarga."' and kodeproduk='".$kdBarang."' and pasar='".$idPasar."'";
        switch($proses)
        {

            case'getSatuan':
            $sSatuan="select distinct satuan from ".$dbname.".log_5masterbarang where kodebarang='".$kdBarang."'";
            $qSatuan=mysql_query($sSatuan) or die(mysql_error($conn));
            $rSatuan=mysql_fetch_assoc($qSatuan);
            echo $rSatuan['satuan'];
            break;
            case'insert':
                $sCek="select distinct * from ".$dbname.".pmn_hargapasar where ".$where."";
                $qCek=mysql_query($sCek) or die(mysql_error($conn));
                $rCek=mysql_num_rows($qCek);
                if($rCek<1)
                {
            $sIns="insert into ".$dbname.".pmn_hargapasar (tanggal, kodeproduk, pasar, satuan, harga, matauang,statusharga,ffa,mni) 
                   values ('".$tglHarga."','".$kdBarang."','".$idPasar."','".$satuan."','".$hrgPasar."',"
                    . "'".$idMatauang."','".$status."','".$ffa."','".$mni."')";
            //exit("Error:$sIns");
                if(!mysql_query($sIns))
                { echo "Gagal,".addslashes(mysql_error($conn));}
                }
                else
                {
                    exit("Error: Already exist");
                }
            break;
            case'update':
            $sIns="update ".$dbname.".pmn_hargapasar set statusharga='".$status."',harga='".$hrgPasar."',"
                    . "matauang='".$idMatauang."',statusharga='".$status."',ffa='".$ffa."',mni='".$mni."' where ".$where."";
            //exit("Error:$sIns");
            if(!mysql_query($sIns))
                { echo "Gagal,".addslashes(mysql_error($conn));}
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

                $ql2="select count(*) as jmlhrow from ".$dbname.".pmn_hargapasar order by `tanggal` desc";// echo $ql2;
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }


                $str="select * from ".$dbname.".pmn_hargapasar order by `tanggal` desc  limit ".$offset.",".$limit."";
				if(($page*$limit)==0){
					$no = 0;
				}else{
					$no = ($page*$limit);
				}
                if($res=mysql_query($str))
                {
                    $barisData=mysql_num_rows($res);
                    if($barisData>0)
                    {
                        while($bar=mysql_fetch_object($res))
                        {

                        $no+=1;


                        echo"<tr class=rowcontent id='tr_".$no."'>
                        <td>".$no."</td>

                        <td>".tanggalnormal($bar->tanggal)."</td>
                        <td>".$optNmBarang[$bar->kodeproduk]."</td>
                        <td>".$bar->satuan."</td>
                        <td>".$bar->pasar."</td>
                        <td>".$bar->matauang."</td>
                        <td align=right>".number_format($bar->harga,2)."</td>
                            <td>".$bar->statusharga."</td>
                                <td>".$bar->ffa."</td>
                                    <td>".$bar->mni."</td>
                        <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".tanggalnormal($bar->tanggal)."','".$bar->kodeproduk."','".$bar->satuan."','".$bar->pasar."','".$bar->matauang."','".$bar->harga."','".$bar->statusharga."','".$bar->ffa."','".$bar->mni."');\"> 
                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".tanggalnormal($bar->tanggal)."','".$bar->kodeproduk."','".$bar->pasar."');\"></td>
                        </tr>";
                        }	 	  
                    }
                    else
                    {
                        echo"<tr class=rowcontent><td colspan=12 style='text-align:center;'>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
					echo"
                        <tr><td colspan=11 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";
                }	
                else
                {
                echo " Gagal,".(mysql_error($conn));
                }	
                echo"</tbody></table>";
                break;

                case'cariData':
				// exit("Error : ".$kdBrgCari);
                if($kdBrgCari!='')
                {
                    $wre.=" and kodeproduk='".$kdBrgCari."'";
                }
                if($tglHarga!='')
                {
                     $wre.=" and tanggal='".$tglHarga."'";
                }
                if($idPasar!='')
                {
                    $wre.=" and pasar='".$idPasar."'";
                }
                $limit=20;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".pmn_hargapasar where tanggal!='' ".$wre." order by `tanggal` desc";// echo $ql2;
                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                $jlhbrs= $jsl->jmlhrow;
                }


                $str="select * from ".$dbname.".pmn_hargapasar where tanggal!='' ".$wre." order by `tanggal` desc  limit ".$offset.",".$limit."";
                //echo "warning:".$str;exit();
                if($res=mysql_query($str))
                {
                    $barisData=mysql_num_rows($res);
                    if($barisData>0)
                    {
                        while($bar=mysql_fetch_object($res))
                        {

                        $no+=1;


                        echo"<tr class=rowcontent id='tr_".$no."'>
                        <td>".$no."</td>

                        <td>".tanggalnormal($bar->tanggal)."</td>
                        <td>".$optNmBarang[$bar->kodeproduk]."</td>
                        <td>".$bar->satuan."</td>
                        <td>".$bar->pasar."</td>
                        <td>".$bar->matauang."</td>
                        <td align=right>".number_format($bar->harga,2)."</td>
                        <td>".$bar->statusharga."</td>
                        <td>".$bar->ffa."</td>    
                            <td>".$bar->mni."</td>   
                        <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".tanggalnormal($bar->tanggal)."','".$bar->kodeproduk."','".$bar->satuan."','".$bar->pasar."','".$bar->matauang."','".$bar->harga."','".$bar->statusharga."','".$bar->ffa."','".$bar->mni."');\">
                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".tanggalnormal($bar->tanggal)."','".$bar->kodeproduk."','".$bar->pasar."');\"></td>
                        </tr>";
                        }	 	 
                    }
                    else
                    {
                        echo"<tr class=rowcontent><td colspan=12 style='text-align:center;'>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
					echo"
                        <tr><td colspan=11 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariTrans(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariTrans(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>"; 
                }	
                else
                {
                echo " Gagal,".(mysql_error($conn));
                }		
                echo"</tbody></table>";
                break;
                
                
                case'delData':
                $sDel="delete from ".$dbname.".pmn_hargapasar where ".$where." ";
                    if(!mysql_query($sDel))
                    {
                        echo " Gagal,".(mysql_error($conn));
                    }
                break;

                default:
                break;
        }

?>