<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');	
$thnbudget=checkPostGet('thnbudget','');
$method=checkPostGet('method','');
$kdblok=checkPostGet('kdblok','');


$jjg=checkPostGet('jjg','');
$pokprod=checkPostGet('pokprod','');
$bjr=checkPostGet('bjr','');
$total=checkPostGet('total','');
$totbrtthn=checkPostGet('totbrtthn','');
$totCol=checkPostGet('totCol','');
$totRow=checkPostGet('totRow','');
$kgsetahun=checkPostGet('kgsetahun','');
$thnclose=checkPostGet('thnclose','');
$lkstgs=checkPostGet('lkstgs','');

$thnttp=checkPostGet('thnttp','');
$thnbudgetHeader=checkPostGet('thnbudgetHeader','');
$kodeblokHeader=checkPostGet('kodeblokHeader','');
$thnsave=checkPostGet('thnsave','');



$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$where="tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."'";

switch($method)
{
	//gabung
	case 'pokok':
			//$pokok="select pokokproduksi from ".$dbname.".bgt_blok WHERE kodeblok='".$kdblok."'";
			$pokok="select pokokproduksi,thntnm,hathnini from ".$dbname.".bgt_blok WHERE kodeblok='".$kdblok."' and tahunbudget='".$thnbudget."'";		
			$qOpt=mysql_query($pokok) or die(mysql_error());
			$rOpt=mysql_fetch_assoc($qOpt);

			$pokok2="select bjr,thntanam from ".$dbname.".bgt_bjr WHERE 
					kodeorg='".substr($kdblok,0,4)."' and thntanam='".$rOpt['thntnm']."' and tahunbudget='".$thnbudget."'";
			$qOpt2=mysql_query($pokok2) or die(mysql_error());
			$rOp2t=mysql_fetch_assoc($qOpt2);
			echo $rOpt['pokokproduksi']."###".$rOp2t['bjr']."###".$rOpt['thntnm']."###".$rOpt['hathnini'];
	break;


	case 'getthn':
			$optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sql = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 order by tahunbudget desc";
			$qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
			while ($data=mysql_fetch_assoc($qry))
					{
					$optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
					}
			echo $optthnttp;
	break;

	case'saveData':
		$total = str_replace(',','',$total);
		if(($bjr=='')||($bjr==0))
		{
			exit("Error:FFB avg(BJR) required");
		}
		$totalSum=0;
		for($a=1;$a<=$totRow;$a++)
		{
			if($_POST['arrBrt'][$a]=='')
			{
				$_POST['arrBrt'][$a]=0;
			}
			$totalSum+=$_POST['arrBrt'][$a];
		}
		
		if($totalSum>$total)
		{
			exit("Error : Monthly total (".$totalSum.") greater than total a year (".$total.") ");
		}
		$sCek="select distinct * from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."'";
		$qCek=mysql_query($sCek) or die(mysql_error());
		$rCek=mysql_num_rows($qCek);
		if($rCek<1)
		{
			$sInsert="insert into ".$dbname.".bgt_produksi_kebun (tahunbudget, kodeunit, kodeblok, jjgperpkk, updateby, jjg01, jjg02, jjg03, jjg04, jjg05, jjg06, jjg07, jjg08, jjg09, jjg10, jjg11, jjg12)";
			$sInsert.=" values ('".$thnbudget."','".$_SESSION['empl']['lokasitugas']."','".$kdblok."','".$jjg."','".$_SESSION['standard']['userid']."'";
			for($arb=1;$arb<=$totRow;$arb++)
			{
				$sInsert.=",'".$_POST['arrBrt'][$arb]."'";
			}
			$sInsert.=")";
			// exit("Error:$sInsert");	
			if(!mysql_query($sInsert))
			{
				echo " Gagal,________".$sInsert."__".(mysql_error($conn));
			}   
		}
		else
		{
			exit("Error: Data already exist");
		}
    break;


    case'loadData':
		$tmbh=$tmbhA=$tmbhsimpan='';
		if($thnbudgetHeader!='')
		{
			$tmbh=" and tahunbudget='".$thnbudgetHeader."' ";
			$tmbhA=" and a.tahunbudget='".$thnbudgetHeader."' ";
		}

		$tmbh2=$tmbh2A='';
		if($kodeblokHeader!='')
		{
			$tmbh2=" and kodeblok='".$kodeblokHeader."' ";
			$tmbh2A=" and a.kodeblok='".$kodeblokHeader."' ";
		}
		//exit ("Error:$tmbhsimpan");

		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;

		$ql2="select count(*) as jmlhrow from ".$dbname.".bgt_produksi_kbn_vw a
			where kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' ".$tmbh." ".$tmbh2." order by kodeblok asc "; //tahunbudget='".$thnbudget."'
		//exit("Error:$q12");
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
			$jlhbrs= $jsl->jmlhrow;
		}
		$totRowDlm=count($arrBln);
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead><tr class=rowheader><td width=15 align=center>No</td>";
		$tab.="<td align=center width=90>".$_SESSION['lang']['kodeblok']."</td>"; 
						$tab.="<td align=center width=90>".$_SESSION['lang']['budgetyear']."</td>"; 
						$tab.="<td align=center width=75>".$_SESSION['lang']['thntnm']."</td>";
		$tab.="<td align=center width=100>".$_SESSION['lang']['pkkproduktif']."</td>";
		$tab.="<td align=center width=50>".$_SESSION['lang']['bjr']."</td>";
		$tab.="<td align=center width=150>".$_SESSION['lang']['jenjangpokoktahun']."</td>";	
		$tab.="<td align=center  width=50>".$_SESSION['lang']['jjgThn']."</td>";	 

		foreach($arrBln as $brs7=>$dtBln7)
		{
				$tab.="<td  align=center>".$dtBln7."(kg)</td>";
		}
		$tab.="<td align=center  width=50>".$_SESSION['lang']['total']." (KG)</td>";
		$tab.="<td align=center>Aksi</td></tr></thead><tbody>";
		
		$totSemua=$totbjr=$totpkkprod=$totjpt=0;
		$sList="select *, ((jjgperpkk * hathnini)*1000 / (jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12)) as bjrBgt,
			(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjgperthn
			from ".$dbname.".bgt_produksi_kbn_vw
			where kodeunit='".$_SESSION['empl']['lokasitugas']."' ".$tmbh." ".$tmbh2." order by kodeblok asc limit ".$offset.",".$limit."";
		//echo $sList;
		$qList=mysql_query($sList) or die(mysql_error());
		while($rList=mysql_fetch_assoc($qList))
		{
								$pokok="select jjgperpkk,tutup from ".$dbname.".bgt_produksi_kebun WHERE kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
								$qOpt=mysql_query($pokok) or die(mysql_error());
								$rOpt=mysql_fetch_assoc($qOpt);


				$a1=$rOpt['jjgperpkk'];
				$a3=$rList['pokokproduksi'];
				$totala=round($rList['jjgperthn']);
				$rList['bjrBgt'] = round($rList['bjrBgt'],2);
				 if($rOpt['tutup']==0)
					{
				   $rtp="onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodeblok']."','".$rList['pokokproduksi']."','".$rList['bjrBgt']."','".$rOpt['jjgperpkk']."','".$totala."','".$rList['thntnm']."','".$rList['hathnini']."');\" title=\"Edit Data ".$rList['kodeblok']."\" style='cursor:pointer;'";
					}
					else
					{
						$rtp="";
					}
			$no+=1;
			$tab.="<tr class=rowcontent >";
			$tab.="<td align=center ".$rtp.">".$no."</td>";
			$tab.="<td align=left ".$rtp.">".$rList['kodeblok']."</td>";
			$tab.="<td align=right ".$rtp.">".$rList['tahunbudget']."</td>";
			$tab.="<td align=right ".$rtp.">".$rList['thntnm']."</td>";
			$tab.="<td align=right ".$rtp.">".$rList['pokokproduksi']."</td>";
			if(!empty($rList['bjrBgt'])) $rList['bjr'] = $rList['bjrBgt'];
			$tab.="<td align=right ".$rtp.">".number_format($rList['bjr'],2)."</td>";
			$tab.="<td align=right ".$rtp.">".$rOpt['jjgperpkk']."</td>";	

			$tab.="<td align=right ".$rtp.">".number_format($totala,0)."</td>";


			for($a=1;$a<=$totRowDlm;$a++)
			{
				if(strlen($a)=='1')
				{
					$b="0".$a;
				}
				else
				{
					$b=$a;
				}
				if($rList['jjg'.$b]=='')
				{
					$rList['jjg'.$b]=0;
				}
				$tab.="<td align='right' ".$rtp.">".number_format($rList['jjg'.$b] * $rList['bjr'],0)."</td>";
				setIt($rTotal[$rList['kodeblok']],0);
				$rTotal[$rList['kodeblok']]+=$rList['jjg'.$b] * $rList['bjr'];
			}
				 $tab.="<td align=right ".$rtp.">".number_format($rTotal[$rList['kodeblok']],0)."</td>";		
		  if($rOpt['tutup']==0)
			{
			 $tab.="<td align='center'>
														<!--<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodeblok']."','".$rList['pokokproduksi']."','".$rList['bjr']."','".$rOpt['jjgperpkk']."','".$totala."','".$rList['thntnm']."');\">-->
														<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$rList['tahunbudget']."','".$rList['kodeblok']."');\">

										   </td>";
								}
			else
			{
				$tab.="<td>".$_SESSION['lang']['tutup']."</td>";
			}
			$tab.="</tr>";
			//total sebaran perbulan (harus dalam while)	
			$a=array("1"=>"jjg01","2"=>"jjg02","3"=>"jjg03","4"=>"jjg04","5"=>"jjg05","6"=>"jjg06","7"=>"jjg07","8"=>"jjg08","9"=>"jjg09","10"=>"jjg10","11"=>"jjg11","12"=>"jjg12");
			for($i=1;$i<=12;$i++)
			{
				if(strlen($i)=='1')
				{
					$b="0".$i;
				}
				else
				{
					$b=$i;
				}
				$totseb1="select jjg".$b." from ".$dbname.".bgt_produksi_kbn_vw where kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
				$totseb2=mysql_query($totseb1) or die (mysql_error());
				$totseb3=mysql_fetch_array($totseb2) or die (mysql_error());
				setIt($hasil['jjg'.$b],0);
				$hasil['jjg'.$b]+=$totseb3['jjg'.$b] * $rList['bjr'];
			}
			$totSemua+=$totala;
			$totbjr+=$rList['bjr'];
			$totpkkprod+=$rList['pokokproduksi'];
			$totjpt+=$rOpt['jjgperpkk'];
		}//tutup while

//================================================== TOTAL DATA ======================================================================

						//-----------------------------------------------------------------------------------------------

						$tab.="<thead><tr class=rowheader><td align=center colspan=4>".$_SESSION['lang']['total']."</td>";
						$tab.="<td align=right>".number_format($totpkkprod,0)."</td>";
						$tab.="<td align=right>&nbsp</td>";
						$tab.="<td align=right>".number_format($totjpt,0)."</td>";
						$tab.="<td align=right>".number_format($totSemua,0)."</td>";
						for($i=1;$i<=12;$i++)
						{
								$tab.="<td align=right>".number_format($hasil[$a[$i]],2)."</td>";
						}
		$tab.="<td colspan=2>&nbsp;</td>";
						$tab.="</tr></thead>";

		$spnCol=$totRowDlm+21;
		$tab.="
				<tr><td colspan='".$spnCol."' align=center><br />
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>"; 
		$tab.="</tbody></table>";

		echo $tab;
        break;


        //case 'delete=======================================================================================================
        case 'delete':

                $tab="delete from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok ='".$kdblok."' ";
                if(mysql_query($tab))
                {
                }
                else
                {	
                        echo " Gagal,".addslashes(mysql_error($conn));
                        }			
        break;

         case'getBlok':
            $optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sVhc="select distinct kodeblok,thntnm from ".$dbname.".bgt_blok where tahunbudget='".$thnbudget."' and kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' and closed=1";
            //exit ("Error".$sVhc);

            $qVhc=mysql_query($sVhc) or die(mysql_error($conn));
            $brs=mysql_num_rows($qVhc);
            if($brs>0)
            {
            while($rVhc=mysql_fetch_assoc($qVhc))
            {
                if($kdblok!='')
                {
                    $optBlok.="<option value='".$rVhc['kodeblok']."' ".($kdblok==$rVhc['kodeblok']?'selected':'').">".$rVhc['kodeblok']." [".$rVhc['thntnm']."]</option>";
                }
                else
                {

                        $optBlok.="<option value='".$rVhc['kodeblok']."'>".$rVhc['kodeblok']." [".$rVhc['thntnm']."]</option>";
                }
            }
            echo $optBlok;
            }
            else
            {
                exit("Error: Block for budget not set(close) yet");
            }
        break;
        //case edit==========================================================================================================
        case 'update':
            for($a=1;$a<=$totRow;$a++)
        {
                        if($_POST['arrBrt'][$a]=='')
            {
                $_POST['arrBrt'][$a]=0;
            }
        $totalSum+=$_POST['arrBrt'][$a];
        }
        if($totalSum>$total)
        {
            exit("Error : Monthly total (".$totalSum.") greater than total a year(".$total.") ");
                }
                $sUpdate="update ".$dbname.".bgt_produksi_kebun set updateby='".$_SESSION['standard']['userid']."',jjgperpkk='".$jjg."'";
                // exit("Error".$sUpdate);
                for($a=1;$a<=$totRow;$a++)
                {
                        if(strlen($a)=='1')
                        {
                                $c="0".$a;
                        }
                        else
                        {
                                $c=$a;
                        }

                         $sUpdate.=" ,jjg".$c."='".$_POST['arrBrt'][$a]."'";

                }
                $sUpdate.=" where  ".$where."";
                 //exit("Error".$sUpdate);
                if(!mysql_query($sUpdate))
                {
                echo " Gagal,_".$sUpdate."__".(mysql_error($conn));
                }   
    break;

        //case get data==========================================================================================================
        case'getData':
        //exit("Error:".$total);
                $totBrs=count($arrBln);
                        $pokok="select * from ".$dbname.".bgt_produksi_kebun WHERE kodeblok='".$kdblok."' and tahunbudget='".$thnbudget."'";
                        $qOpt=mysql_query($pokok) or die(mysql_error());
                        $rRow=mysql_num_rows($qOpt);
						$isi='';
                        if($rRow>0)
                        {
                            if(isset($_POST['statInputan']) and $_POST['statInputan']!=1)
                            {
                            $sTot="select distinct pokokproduksi,jjgperpkk from ".$dbname.".bgt_produksi_kbn_vw where kodeblok='".$kdblok."' and tahunbudget='".$thnbudget."'";
                           // echo $sTot;
                            $qTot=mysql_query($sTot) or die(mysql_error($sTot));
                            $rRes=mysql_fetch_assoc($qTot);

                            $a3=$rRes['pokokproduksi'];
                            $a1=$rRes['jjgperpkk'];
                            $total=$a1*$a3;
                            $isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']."/".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
                            $isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
                            $isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right id='hasilPerkalian'>".number_format($total)."</td></tr></thead><tbody>";

                            $rOpt=mysql_fetch_assoc($qOpt);


                                    for($bre=1;$bre<=$totBrs;$bre++)
                                    {
                                            if(strlen($bre)<2)
                                            {
                                                    $abe="0".$bre;	
                                            }
                                            else
                                            {
                                                    $abe=$bre;
                                            }
                                            @$hslDr=($rOpt['jjg'.$abe]/$total)*100;
                                            $isi.="<tr class=rowcontent><td>".$arrBln[$bre]."</td>
                                            <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$bre." onblur=ubahNilai(this.value,'".$total."','brt_x') value='".number_format($hslDr,0)."' /></td>";
                                            $isi.="<td><input type='text' id=brt_x".$bre." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$rOpt['jjg'.$abe]." /></td>
                                            </tr>";
                                    }
                            }
                            else
                            {
                                $isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']."/".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
                                $isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
                                $isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right>".number_format($total)."</td></tr></thead><tbody>";
                                    @$bagi=$total/12;
                                    foreach($arrBln as $brs2=>$dtBln2)
                                    {
                                    @$bagi2=$bagi/$total;
                                    $isi.="<tr class=rowcontent><td>".$dtBln2."</td>
                                    <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$brs2." onblur=ubahNilai(this.value,'".$total."','brt_x') value=".number_format((($bagi2)*100),0,'.','')."></td>";
                                    $isi.="<td><input type='text' id=brt_x".$brs2." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$bagi." /></td>
                                    </tr>";
                                }
                            }

                        }
                        else
                        {
                            $isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']."/".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
                            $isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
                            $isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right>".number_format($total)."</td></tr></thead><tbody>";
                                @$bagi=$total/12;
                            foreach($arrBln as $brs2=>$dtBln2)
                            {
                                @$bagi2=$bagi/$total;
                                $isi.="<tr class=rowcontent><td>".$dtBln2."</td>
                                <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$brs2." onblur=ubahNilai(this.value,'".$total."','brt_x') value=".number_format((($bagi2)*100),0,'.','')."></td>";
                                $isi.="<td><input type='text' id=brt_x".$brs2." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$bagi." /></td>
                                </tr>";
                            }
                                    //$isi.="<td><input type='text' class='myinputtextnumber'  id=brt_x".$brs2." value='".$bagi."' style='width:50px' onkeypress=\"return angka_doang(event);\" /></td>";
                   }
                $isi.="<tr class=rowcontent><td  colspan=3 align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveBrt(".$totBrs.")\" src='images/save.png'/>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearForm()\" src='images/clear.png'/></td>";
                $isi.="</tr></tbody></table></fieldset>";
                //$isi.="<td align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveBrt(".$totBrs.")\" src='images/save.png'/></td></tr>";
                echo $isi;
        break;

        //case close==========================================================================================================
        case'closeBudget':
                $sQl="select distinct tutup from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnttp."' and kodeunit='".$lkstgs."' and tutup=1 ";
           //exit("error".$sQl);
                $qQl=mysql_query($sQl) or die(mysql_error($conn));
                $row=mysql_num_rows($qQl);
                if($row!=1)
                {
                        $sUpdate="update ".$dbname.".bgt_produksi_kebun set tutup=1 where tahunbudget='".$thnttp."' and kodeunit='".$lkstgs."'  ";
                   // exit("error".$sUpdate);
                        if(mysql_query($sUpdate))
                                echo "";

                        else
                                 echo " Gagal,_".$sUpdate."__".(mysql_error($conn));
                }
                else
                {
                        exit("Error: Budget for this period has been closed");
                }
		break;
        case 'cek':

                ##UNTUK VALIDASI DATA YANG UDAH DI TUTUP GK BISA INSERT LAGI
                $aCek="select distinct tutup from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' ";
                $bCek=mysql_query($aCek) or die(mysql_error());
                while ($cCek=mysql_fetch_assoc($bCek))
                {
                        //exit("error:$aCek");
                        if($cCek['tutup']==1)
                        {
                                echo "warning : Budget for this period has been closed, coud not proceed";
                                exit();	
                        }
                }

                ##UNTUK VALIDASI DATA DI BGT BLOK ADA APA TIDAK UNTUK THN TANAM DAN KODE BLOKNY
                $xCek="select tahunbudget,kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."' ";
                //exit("Error:$xCek");
                $ada=false;

                $yCek=mysql_query($xCek)or die(mysql_error());
                while($zCek=mysql_fetch_assoc($yCek))
                {
                        $ada=true;
                }
                if ($ada==false)
                {
                        echo "warning : Budget year ".$thnbudget." or block code ".$kdblok." not listed on block budget (Anggaran->Transaksi->Kebun->Blok Anggaran) ";
                        exit();	
                }

                ##UNTUK VALIDASI DATA DI BGT BLOK SUDAH ADA APA BELON
                $xCek="select tahunbudget,kodeblok from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."' ";
                //exit("Error:$xCek");
                $ada=false;

                $yCek=mysql_query($xCek)or die(mysql_error());
                while($zCek=mysql_fetch_assoc($yCek))
                {
                        $ada=true;
                }
                if ($ada==true)
                {
                        echo "warning : data already exist ";
                        exit();	
                }
				
				// Input Budget BJR
				$optThnTnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',
										"kodeorg='".$kdblok."'");
				$dataIns = array(
					'tahunbudget' => $thnbudget,
					'kodeorg' => $kdblok,
					'thntanam' => $optThnTnm[$kdblok],
					'bjr' => $bjr,
					'updateby' => $_SESSION['standard']['userid'],
					'lastupdate' => date('Y-m-d H:i:s'),
					'close' => 0
				);
				$dataUpd = array(
					'bjr' => $bjr,
					'updateby' => $_SESSION['standard']['userid'],
					'lastupdate' => date('Y-m-d H:i:s')
				);
				$whereUpd = "tahunbudget='".$thnbudget."' and kodeorg='".$kdblok.
					"' and thntanam='".$optThnTnm[$kdblok]."'";
				$insBjr = insertQuery($dbname,'bgt_bjr',$dataIns);
				$updBjr = updateQuery($dbname,'bgt_bjr',$dataUpd,$whereUpd);
				if(!mysql_query($insBjr)) {
					if(!mysql_query($updBjr)) {
						exit("DB Error: ".mysql_error());
					}
				}
				
                ##UNTUK VALIDASI TAHUN DI BJR
                // $iCek="select tahunbudget from ".$dbname.".bgt_bjr where tahunbudget='".$thnbudget."' ";
                // exit("Error:$xCek");
                // $ada=false;

                // $nCek=mysql_query($iCek)or die(mysql_error());
                // while($dCek=mysql_fetch_assoc($nCek))
                // {
                        // $ada=true;
                // }
                // if ($ada==false)
                // {
                        // echo "warning : Budget year  ".$thnbudget." not found on FFB avg weight(BJR), (Anggaran->Transaksi->Kebun->BJR)";
                        // exit();	
                // }
        break;

        case 'getkodeblokHeader':
                $optKodeBlokHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
                $sThn = "SELECT distinct kodeblok FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' order by kodeblok";
                $qThn=mysql_query($sThn) or die(mysql_error($conn));
                while($rThn=mysql_fetch_assoc($qThn))
                {
                        $optKodeBlokHeader.="<option value='".$rThn['kodeblok']."'>".$rThn['kodeblok']."</option>";
                }
                echo $optKodeBlokHeader;
//                echo $sThn;
        break;

        case 'getthnbudgetHeader':
                //$bjr="select bjr from ".$dbname.".bgt_bjr WHERE kodeorg='".substr($kdblok,0,4)."'";
                $optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sThn = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' order by tahunbudget desc";
                $qThn=mysql_query($sThn) or die(mysql_error($conn));
                while($rThn=mysql_fetch_assoc($qThn))
                {
                        $optTahunBudgetHeader.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
                }
                echo $optTahunBudgetHeader;
        break;
        case 'getThn':
                $optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sql = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 order by tahunbudget desc";
                $qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
                while ($data=mysql_fetch_assoc($qry))
                                        {
                                        $optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
                                        }
                echo $optthnttp;
        break;


        case 'getOrg':
                $optorgclose="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sql = "SELECT distinct kodeunit FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 ";
                $qry = mysql_query($sql) or die ("SQL ERR : ".mysql_error());
                while ($data=mysql_fetch_assoc($qry))
                                        {
                                        $optorgclose.="<option value=".$data['kodeunit'].">".$optNm[$data['kodeunit']]."</option>";
                                        }
                echo $optorgclose;
        break;
        //--- cari kebun brow

        case'carikebun':
        if(isset($_POST['kebun']))
                {
                        $txt_search=$_POST['kebun'];
                }
                else
                {
                        $txt_search='';		
                }
                        if($txt_search!='')
                        {
                                $where=" kodeblok LIKE  '%".$txt_search."%'";
                        }
                        elseif($txt_tgl!='')
                        {
                                $where.=" tanggal LIKE '".$txt_tgl."'";
                        }
                        elseif(($txt_tgl!='')&&($txt_search!=''))
                        {
                                $where.=" notransaksi LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'";
                        }
                //echo $strx; exit();
                if($txt_search==''&&$txt_tgl=='')
                {
                        $strx="select * from ".$dbname.".vhc_penggantianht where  ".$where." order by tanggal desc";

                }
                else
                {
                                $strx="select * from ".$dbname.".vhc_penggantianht where   ".$where." order by tanggal desc";
                }

                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                                $ql2="select count(*) as jmlhrow from ".$dbname.".bgt_produksi_kebun where kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' ".$tmbhsimpan." ".$tmbh." order by kodeblok asc "; //tahunbudget='".$thnbudget."'
                                //exit("Error:$q12");
                                $query2=mysql_query($ql2) or die(mysql_error());
                while($jsl=mysql_fetch_object($query2)){
                        $jlhbrs= $jsl->jmlhrow;
                }
                $totRowDlm=count($arrBln);
                $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
                $tab.="<thead><tr class=rowheader><td width=15 align=center>No</td>";
                $tab.="<td align=center width=90>".$_SESSION['lang']['kodeblok']."</td>"; 
                                $tab.="<td align=center width=90>".$_SESSION['lang']['budgetyear']."</td>"; 
                                $tab.="<td align=center width=75>".$_SESSION['lang']['thntnm']."</td>";
                $tab.="<td align=center width=100>".$_SESSION['lang']['pkkproduktif']."</td>";
                $tab.="<td align=center width=50>".$_SESSION['lang']['bjr']."</td>";
                $tab.="<td align=center width=150>".$_SESSION['lang']['jenjangpokoktahun']."</td>";	
                                $tab.="<td align=center  width=50>".$_SESSION['lang']['kgThn']."</td>";	 
                foreach($arrBln as $brs7=>$dtBln7)
                {
                        $tab.="<td  width=45 align=center>".$dtBln7."</td>";
                }
                $tab.="<td align=center>Aksi</td></tr></thead><tbody>";	

                                $sList="select * from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeunit='".$_SESSION['empl']['lokasitugas']."' ".$tmbhsimpan." ".$tmbh."  order by kodeblok asc limit ".$offset.",".$limit."";
                                //echo $sList;
                                $qList=mysql_query($sList) or die(mysql_error());
                while($rList=mysql_fetch_assoc($qList))
                {
                    $pokok="select jjgperpkk,tutup from ".$dbname.".bgt_produksi_kebun WHERE kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
                    $qOpt=mysql_query($pokok) or die(mysql_error());
                    $rOpt=mysql_fetch_assoc($qOpt);

                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=left>".$rList['kodeblok']."</td>";
                                        $tab.="<td align=right>".$rList['tahunbudget']."</td>";
                    $tab.="<td align=right>".$rList['thntnm']."</td>";
                    $tab.="<td align=right>".$rList['pokokproduksi']."</td>";
                    $tab.="<td align=right>".$rList['bjr']."</td>";
                    $tab.="<td align=right>".$rOpt['jjgperpkk']."</td>";	
                                                $a1=$rOpt['jjgperpkk'];
                                                $a3=$rList['pokokproduksi'];
                                                $totala=$a1*$a3;																
                    $tab.="<td align=right>".number_format($totala)."</td>";


                    for($a=1;$a<=$totRowDlm;$a++)
                    {
                        if(strlen($a)=='1')
                        {
                            $b="0".$a;
                        }
                        else
                        {
                            $b=$a;
                        }
                        if($rList['kg'.$b]=='')
                        {
                            $rList['kg'.$b]=0;
                        }
                        $tab.="<td align='right'>".number_format($rList['kg'.$b],2)."</td>";
                    }


                                        if($rOpt['tutup']==0)
                    {
                     $tab.="<td align='center'>
                                                                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodeblok']."','".$rList['pokokproduksi']."','".$rList['bjr']."','".$rOpt['jjgperpkk']."','".$totala."','".$rList['thntnm']."');\">
                                                                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$rList['tahunbudget']."','".$rList['kodeblok']."');\">

                                                   </td>";
                                        }
                    else
                    {
                        $tab.="<td>".$_SESSION['lang']['tutup']."</td>";
                    }
                    $tab.="</tr>";
                                //total sebaran perbulan (harus dalam while)	
                                $a=array("1"=>"kg01","2"=>"kg02","3"=>"kg03","4"=>"kg04","5"=>"kg05","6"=>"kg06","7"=>"kg07","8"=>"kg08","9"=>"kg09","10"=>"kg10","11"=>"kg11","12"=>"kg12");
                                for($i=1;$i<=12;$i++)
                                {
                                        if(strlen($i)=='1')
                        {
                            $b="0".$i;
                        }
                        else
                        {
                            $b=$i;
                        }
                                        $totseb1="select kg".$b." from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
                                        $totseb2=mysql_query($totseb1) or die (mysql_error());
                                        $totseb3=mysql_fetch_array($totseb2) or die (mysql_error());
                                        $hasil['kg'.$b]+=$totseb3['kg'.$b];
                                }

                                //UNTUK TOTAL ,, GK DR DB
                                $totSemua+=$totala;
                                $totbjr+=$rList['bjr'];
                                $totpkkprod+=$rList['pokokproduksi'];
                                $totjpt+=$rOpt['jjgperpkk'];

                        }//tutup while

                                //-----------------------------------------------------------------------------------------------

                                $tab.="<thead><tr class=rowheader><td align=center colspan=4>".$_SESSION['lang']['total']."</td>";
                                $tab.="<td align=right>".number_format($totpkkprod)."</td>";
                                $tab.="<td align=right>".number_format($totbjr)."</td>";
                                $tab.="<td align=right>".number_format($totjpt)."</td>";
                                $tab.="<td align=right>".number_format($totSemua)."</td>";
                                for($i=1;$i<=12;$i++)
                                {
                                        $tab.="<td align=right>".number_format($hasil[$a[$i]],2)."</td>";
                                }
                $tab.="<td></td>";
                                $tab.="</tr></thead>";

                $spnCol=$totRowDlm+21;
                $tab.="
                        <tr><td colspan='".$spnCol."' align=center><br />
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>"; 
                $tab.="</tbody></table>";

                echo $tab;
        break;

        default:	
}