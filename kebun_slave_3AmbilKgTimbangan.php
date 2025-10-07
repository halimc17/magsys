<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=$_POST['proses'];
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$noSpb=checkPostGet('noSpb','');
$noTrans=checkPostGet('noTrans','');
$intex=checkPostGet('intex','');

switch($proses)
{
	//load data
	case'getData':
		unset($_SESSION['temp']['tempNospb']);
		echo"<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>";
		echo"<table cellspacing=1 border=0>
		<thead>
		<tr><td align=center>".$_SESSION['lang']['kebun']."</td>
		<td align=center>".$_SESSION['lang']['pabrik']."</td></tr>
		</thead>
		<tbody><tr class=rowcontent><td>";
		echo"
		<table cellspacing=1 border=0 id=rkmndsiPupuk class='sortable'>
		<thead>
		<tr class=rowheader>
		<td>No</td>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>".$_SESSION['lang']['nospb']."</td>
		<td>".$_SESSION['lang']['tglNospb']."</td>
		<td>".$_SESSION['lang']['status']."</td>
		</tr>
		</thead>
		<tbody>
		";
		
		$limit=100;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;

		$sql2="select count(*) as jmlhrow from ".$dbname.".kebun_spbht  where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and tujuan='".$intex."' order by `tanggal` desc";
		$query2=mysql_query($sql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$slvhc="select kodeorg,nospb,tanggal,posting from ".$dbname.".kebun_spbht  where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and tujuan='".$intex."' order by `tanggal` desc limit ".$offset.",".$limit."";//echo $slvhc;
		$qlvhc=mysql_query($slvhc) or die(mysql_error());
		$row=mysql_num_rows($qlvhc);
		if($row>0)
		{
			while($res=mysql_fetch_assoc($qlvhc))
			{
				$sNospb="select nospb from ".$dbname.".kebun_spbht where kodeorg='".$kdOrg."' and tanggal='".$tgl."' and tujuan='".$intex."'"; //echo $sNospb;
				$qNospb=mysql_query($sNospb) or die(mysql_error());
				$rNospb=mysql_fetch_assoc($qNospb);
				$arrStat=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
				$stat=$arrStat[$res['posting']];
				$arrNospb[]=$res;

				$_SESSION['temp']['tempNospb']= $arrNospb;
				$no+=1;
				echo"
				<tr class=rowcontent>
				<td>".$no."</td>
				<td>". $res['kodeorg']."</td>
				<td>". $res['nospb']."</td>
				<td>". tanggalnormal($res['tanggal'])."</td>
				<td>". $stat."</td>";
			}
			echo"</tbody></table>";
		} else {
			echo"<tr class=rowcontent><td colspan='5' align='center'>Not Found</td></tr></tbody></table>";
		}
        echo"</td><td>";
		echo"
		<table cellspacing=1 border=0  class='sortable'>
		<thead>
		<tr class=rowheader>
		<td>No</td>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>".$_SESSION['lang']['nospb']."</td>
		<td>".$_SESSION['lang']['berat']."</td>
		<td>Action</td>
		</tr>
		</thead>
		<tbody>
		";
		
		if(isset($_SESSION['temp']['tempNospb']))
		{
			foreach($_SESSION['temp']['tempNospb'] as $rw => $dt)
			{
				$hslNosbp=$dt['nospb'];
				$sPabrik="select * from ".$dbname.".pabrik_timbangan where nospb='".$hslNosbp."' and kodeorg='".$kdOrg."'";//echo $sPabrik;
				$qPabrik=mysql_query($sPabrik) or die(mysql_error());
				$rowPabrik=mysql_num_rows($qPabrik);
				//echo $rowPabrik;
				$tr=$key=0;
				if($rowPabrik>0)
				{
					$res=mysql_fetch_assoc($qPabrik);
					$sNospb="select totalkg,nospb from ".$dbname.".kebun_spbdt  where nospb='".$res['nospb']."'"; //echo $sNospb;
					$qNospb=mysql_query($sNospb) or die(mysql_error());
					$rNospb=mysql_fetch_assoc($qNospb);
					
					$y=intval($res['beratbersih']);
					$tr+=1;
					echo"
					<tr class=rowcontent>
					<td>".$tr."</td>
					<td>".tanggalnormal($res['tanggal'])."</td>
					<td>".$res['nospb']."</td>
					<td>". $y."</td><td>";
					if($y==0)
					{
							echo"Data incomplete";
					}
					else
					{
						if($rNospb['totalkg']==0)
						{
							echo"[&nbsp;<a href=# onclick=prosesData('".$rNospb['nospb']."','".$res['notransaksi']."')>".$_SESSION['lang']['belumposting']."</a>&nbsp;]&nbsp; [&nbsp;";
							echo"<a href=# onclick=\"viewData('".$rNospb['nospb']."###".$res['notransaksi']."','".$_SESSION['lang']['detail']."','<fieldset><legend>".$_SESSION['lang']['AmbilKgTimbangan']."</legend><div id=container></div><input type=hidden id=detNospb name=detNospb value=".$key."></fieldset>',event)\";>".$_SESSION['lang']['detail']."</a>&nbsp;]";
						}
						else
						{
							echo"<a href=# onclick=\"viewData('".$rNospb['nospb']."###".$res['notransaksi']."','".$_SESSION['lang']['detail']."','<fieldset><legend>".$_SESSION['lang']['AmbilKgTimbangan']."</legend><div id=container></div><input type=hidden id=detNospb name=detNospb value=".$key."></fieldset>',event)\";>".$_SESSION['lang']['detail']."</a>";
						}
                    }
					echo"</td></tr>";
					$key++;
                } else {
                    // cek apakah buah pabrik luar luar
					$sNospb="select sum( a.totalkg ) AS totalkg, a.nospb, sum( a.kgwb ) AS kgwb, b.tanggal 
						from ".$dbname.".kebun_spbdt a LEFT JOIN ".$dbname.".kebun_spbht b ON a.nospb = b.nospb 
						where a.nospb='".$hslNosbp."' AND b.posting =0 and a.kgwb>0
						GROUP BY a.nospb"; //echo $sNospb;
					$qNospb=mysql_query($sNospb) or die(mysql_error());
					
					$rowNospb=mysql_num_rows($qNospb);
					//echo $rowPabrik;
					if($rowNospb>0)
					{
						// ada data (pabrik luar)
						$res=mysql_fetch_assoc($qNospb);
						
						$y=intval($res['kgwb']);
						$tr+=1;
						echo"
						<tr class=rowcontent>
						<td>".$tr."</td>
						<td>".tanggalnormal($res['tanggal'])."</td>
						<td>".$res['nospb']."</td>
						<td>". $y."</td><td>";
						if($y==0)
						{
							echo"Data incomplete";
						}
						else
						{
							if($res['totalkg']==0)
							{
								echo"[&nbsp;<a href=# onclick=prosesData2('".$res['nospb']."')>".$_SESSION['lang']['belumposting']."</a>&nbsp;]&nbsp; [&nbsp;";
								echo"<a href=# onclick=\"viewData('".$res['nospb']."###".$res['notransaksi']."','".$_SESSION['lang']['detail']."','<fieldset><legend>".$_SESSION['lang']['AmbilKgTimbangan']."</legend><div id=container></div><input type=hidden id=detNospb name=detNospb value=".$key."></fieldset>',event)\";>".$_SESSION['lang']['detail']."</a>&nbsp;]";
							}
							else
							{
								echo"<a href=# onclick=\"viewData('".$res['nospb']."###".$res['notransaksi']."','".$_SESSION['lang']['detail']."','<fieldset><legend>".$_SESSION['lang']['AmbilKgTimbangan']."</legend><div id=container></div><input type=hidden id=detNospb name=detNospb value=".$key."></fieldset>',event)\";>".$_SESSION['lang']['detail']."</a>";
							}
						}
						echo"</td></tr>";                                                            
                    }else{
						// kosong
						echo"<tr class=rowcontent><td colspan='5' align='center'>Not Found</td></tr>";
					}
                }
            }
        } else {
			echo"<tr class=rowcontent><td colspan='5' align='center'>Not Found</td></tr>";
		}
        echo"</tbody></table></td></tr></tbody><table></fieldset>";
        break;
	

    case'PostingData2':
        
        
   
                    // spb pabrik luar dengan kgwb
                    
                             //===================================   
                                $sUpd="update ".$dbname.".kebun_spbdt set totalkg=kgwb where nospb='".$noSpb."'"; //echo "warning:".$sUpd; echo"warning: berat__".$rNospb['kgbjr']."totalberat:".$rTotal['total']."___persen:".$persen;exit();
//echo "error:".$sUpd;             
//exit;
                                        if(mysql_query($sUpd))
                                        {
                                                $sUpdate="update ".$dbname.".kebun_spbht set posting='1',postingby='".$_SESSION['standard']['userid']."' where nospb='".$noSpb."'";
                                        if(mysql_query($sUpdate))
                                        echo"";
                                        else
                                        echo "DB Error : ".mysql_error($conn);	
                                        }
                                        else
                                        {
                                                echo "DB Error : ".mysql_error($conn);
                                        }
                
                break;
                
                
                
                
                
                case'PostingData':
                    
                    //indra
                   
                $sCek="select bjr from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
                    
                $qCek=mysql_query($sCek) or die(mysql_error());
                $b=0;
                while($rCek=mysql_fetch_assoc($qCek))
                {
                        if($rCek['bjr']!=0)
                        {
                                $b+=1;
                        }
                }
                
                $sCek2="select bjr from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
                $qCek=mysql_query($sCek2) or die(mysql_error());
                $rCek2=mysql_num_rows($qCek);
       
       
                if($b==$rCek2)
                {

                        $sNospb="select nospb,blok,kgbjr,brondolan,keterangan from ".$dbname.".kebun_spbdt where nospb='".$noSpb."'";
                        //echo"warning".$sNospb;
                        
                        $qNospb=mysql_query($sNospb) or die(mysql_error());
                        while($rNospb=mysql_fetch_assoc($qNospb))
                        {
                                //berat dan total berat
                                $sTotal="select sum(kgbjr) as total,sum(brondolan) as totalbrondolan from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
                                $qTotal=mysql_query($sTotal) or die(mysql_error($conn));
                                $rTotal=mysql_fetch_assoc($qTotal);
                                    $totKgkbn=$rTotal['total'];
                                    $totBrondolan=$rTotal['totalbrondolan'];
                                
                                $sTimbngn="select beratbersih,brondolan from ".$dbname.".pabrik_timbangan where notransaksi='".$noTrans."' and nospb='".$rNospb['nospb']."' ";
                                
                                $qTimbngn=mysql_query($sTimbngn) or die(mysql_error($conn));
                                $rTimbngn=mysql_fetch_assoc($qTimbngn);
                                $x=intval($rTimbngn['beratbersih']);
                      
                                //berat bersih dari PKS sudah termasuk brondolan    
                                //$y=$x+intval($rTimbngn['brondolan']);
                                     
                                $brondolanspb=$rNospb['brondolan'];
                                $kgkbn=$rNospb['kgbjr'];
                                
                              
                                $kgkbnbron=$kgkbn+$brondolanspb;
                                $totKgkbnbron=$totKgkbn+$totBrondolan;
                                
                                $kgWb=$kgkbnbron/$totKgkbnbron*$x;
                                
                               
                                //$kgWb=$brondolanspb+($kgkbn/$totKgkbn*($y-$totBrondolan));
                                //exit("Error:$kgkbn._.$brondolanspb._.$kgkbnbron._.$totKgkbnbron._.$x._.$kgWb");
                                //3936._.0._.3936._.6360._.8790._.5439.8490566038
                                  $totKg=$kgWb;
                                
                               /* 
                            //============================================update by ginting
                                //berat bersih dari PKS sudah termasuk brondolan    
                                //$y=$x+intval($rTimbngn['brondolan']);
                                
                                
                                @$persen=intval($rNospb['kgbjr'])/intval($rTotal['total']);	
                                if($persen==0){
                                    $sTotal="select count(*) as total from ".$dbname.".kebun_spbdt where nospb='".$rNospb['nospb']."'";
                                    $qTotal=mysql_query($sTotal) or die(mysql_error());
                                    $rTotal=mysql_fetch_assoc($qTotal);
                                    $kgWb=$x/$rTotal['total'];
                                    $totKg=$x/$rTotal['total'];                                   
                                }
                                else
                                {    
                                $kgWb=$persen*$x;
                                $totKg=$persen*$x;
                                }
                                */
                                
                             //===================================   
                                $sUpd="update ".$dbname.".kebun_spbdt set kgwb='".$kgWb."',totalkg='".$totKg."' where nospb='".$rNospb['nospb']."' and blok='".$rNospb['blok']."' and keterangan='".$rNospb['keterangan']."'"; //echo "warning:".$sUpd; echo"warning: berat__".$rNospb['kgbjr']."totalberat:".$rTotal['total']."___persen:".$persen;exit();
                                        if(mysql_query($sUpd))
                                        {
                                                $sUpdate="update ".$dbname.".kebun_spbht set posting='1',postingby='".$_SESSION['standard']['userid']."' where nospb='".$rNospb['nospb']."'";
                                        if(mysql_query($sUpdate))
                                        echo"";
                                        else
                                        echo "DB Error : ".mysql_error($conn);	
                                        }
                                        else
                                        {
                                                echo "DB Error : ".mysql_error($conn);
                                        }
                        }
                }
                else
                {
                        if($_SESSION['language']=='EN'){
                            echo"warning: There is no AVG weight in this transaction, confirmation can not be done";
                        }else{
                            echo"warning: Dalam No.SPB ini ada data belum terdapat BJR, posting tidak dapat dilanjutkan";
                        }
                        exit();
                }
                break;
                case'ShowData':
                $sShwData2="select * from ".$dbname.".kebun_spbht where nospb='".$noSpb."' ";
                $qShwData2=mysql_query($sShwData2) or die(mysql_error());
                $rShwData2=mysql_fetch_assoc($qShwData2);
                $arrStat=array($_SESSION['lang']['belumposting'],$_SESSION['lang']['posting']);
                $stat=$arrStat[$rShwData2['posting']];
                echo"
                <fieldset><legend>".$_SESSION['lang']['header']."</legend>
                <table cellspacing=1 border=0>
                <tr><td>".$_SESSION['lang']['nospb']."</td><td>:</td><td>".$rShwData2['nospb']."</td></tr>
                <tr><td>".$_SESSION['lang']['tglNospb']."</td><td>:</td><td>".tanggalnormal($rShwData2['tanggal'])."</td></tr>
                <tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td><td>".$rShwData2['kodeorg']."</td></tr>
                <tr><td>".$_SESSION['lang']['status']."</td><td>:</td><td>".$stat."</td></tr>
                </table></fieldset><br />
                ";
                echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>
                <table cellspacing=1 border=0>
                <thead>
                <tr class=rowheader>
                <td>No</td>
                <td>".$_SESSION['lang']['blok']."</td>
                <td>".$_SESSION['lang']['janjang']."</td>
                <td>".$_SESSION['lang']['bjr']."</td>
                </tr></thead>
                <tbody>
                ";

                $sShwData="select a.*,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb where a.nospb='".$noSpb."' ";
                $qShwData=mysql_query($sShwData) or die(mysql_error());
                while($rShwData=mysql_fetch_assoc($qShwData))
                {
                        $no+=1;
                        echo"<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$rShwData['blok']."</td>
                <td>".$rShwData['jjg']."</td>
                <td>".$rShwData['bjr']."</td>
                </tr>";

                }
                echo"</tbody></table>*nb.Satuan yang digunakan KG/Unit of Measurement is KG.</legend>";
                break;

                default:
                break;
        }
?>