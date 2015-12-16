<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
require_once('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');

//$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";
$_POST['proses']==''?$proses=$_GET['proses']:$proses=$_POST['proses'];
$_POST['psrId']==''?$psrId=$_GET['psrId']:$psrId=$_POST['psrId'];
$_POST['periodePsr']==''?$periodePsr=$_GET['periodePsr']:$periodePsr=$_POST['periodePsr'];
$_POST['komoditi']==''?$komoditi=$_GET['komoditi']:$komoditi=$_POST['komoditi'];
$idPasar=$_POST['idPasar'];
$idMatauang=$_POST['idMatauang'];
$hrgPasar=$_POST['hrgPasar'];
$tglHarga=tanggalsystem($_POST['tglHarga']);
$whr="kelompokbarang='400'";
$optNmBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',$whr);

if($psrId!='')
{
    $where.=" and pasar='".$psrId."'";
}
else
{
    exit("Warning : Pasar Tidak Boleh Kosong");
}
if($periodePsr!='')
{
    $where.=" and tanggal like '".$periodePsr."%'";
}
else
{
    exit("Warning : Periode Tidak Boleh Kosong");
}

if($komoditi!='')
{
    $where.=" and kodeproduk = '".$komoditi."'";
}
else
{
    exit("Warning : Komoditi Tidak Boleh Kosong");
}
	switch($proses)
	{
	
		case'preview':
	
		echo"
    <table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td>".$_SESSION['lang']['komoditi']."</td>
	   <td>".$_SESSION['lang']['satuan']."</td>
	   <td>".$_SESSION['lang']['pasar']."</td>
	   <td>".$_SESSION['lang']['matauang']."</td>
           <td>".$_SESSION['lang']['harga']."</td></tr></thead><tbody>";
		$str="select * from ".$dbname.".pmn_hargapasar where tanggal!='' ".$where." order by `tanggal` desc";
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
                        </tr>";
                        }	 	
                        
                    }
                    else
                    {
                        echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
		}	
		else
		{
		echo " Gagal,".(mysql_error($conn));
		}	
		echo"</tbody></table>";
		break;
		case'jpgraph':
                    $bln=array();
                    $num=date('t',mktime(0,0,0,substr($periodePsr,5,2),2,substr($periodePsr,0,4)));
                    $labels=array();
                    for($x=1;$x<=$num;$x++)
                    {
						array_push($labels, $x);
						if($x<10)
						  $y='0'.$x;
						else
						  $y=$x;  
						array_push($bln, $y);
                    }
                    $datay1=array();
                    $datay2=array();
                    for($x=0;$x<count($bln);$x++)
                    {
                            $str="select
                                      harga,kodeproduk,matauang
                                      from ".$dbname.".pmn_hargapasar 
                                      where pasar='".$psrId."' and tanggal = '".$periodePsr."-".$bln[$x]."' and kodeproduk = '".$komoditi."'";  
                            $datay1[$x]=0;//cpo
//                            $datay2[$x]=0;//kernel
//                            $datay3[$x]=0;//Fresh Fruit Bunch (TBS)
//                            $datay4[$x]=0;//Janjang Kosong
//                            $datay5[$x]=0;//CANGKANG SAWIT
                            $res=mysql_query($str);
                           while($bar=mysql_fetch_object($res))
                            {
								if($bar->harga!=0)
                                {
                                   
                                        $datay1[$x]=$bar->harga;
                                    
//                                    elseif($bar->kodeproduk=='40000002')
//                                    {
//                                        $datay2[$x]=$bar->harga;
//                                    }
//                                    elseif($bar->kodeproduk=='40000003')
//                                    {
//                                        $datay3[$x]=$bar->harga;
//                                    }
//                                    elseif($bar->kodeproduk=='40000004')
//                                    {
//                                        $datay4[$x]=$bar->harga;
//                                    }
//                                    elseif($bar->kodeproduk=='40000005')
//                                    {
//                                        $datay5[$x]=$bar->harga;
//                                    }
                                }
                             }	
                    }
                    //===========

                    $graph = new Graph(750,450);
                    $graph->img->SetMargin(40,40,40,80);    
                    $graph->img->SetAntiAliasing();
                    $graph->SetScale("textlin");
                    $graph->SetShadow();
                    $graph->title->Set(strtoupper($psrId)."  ".$periodePsr);
                    $graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

                    $graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
                    $graph->xaxis->SetTickLabels($labels);
                    $graph->xaxis->SetLabelAngle(45);
//print_r($datay4);
//exit("Error");
                    
                    $p1 = new LinePlot($datay1);
//                    $p2 = new ScatterPlot($datay2);
//                    $p3 = new ScatterPlot($datay3);
//                    $p4 = new ScatterPlot($datay4);
//                    $p5 = new ScatterPlot($datay5);

                    $p1->SetLegend($optNmBarang[$komoditi]);
//                    $p2->SetLegend($optNmBarang[40000002]);
//                    $p3->SetLegend($optNmBarang[40000003]);
//                    $p4->SetLegend($optNmBarang[40000004]);
//                    $p5->SetLegend($optNmBarang[40000005]);
                    $graph->legend->Pos(0.02,0.03);

                    $p1->mark->SetType(MARK_SQUARE);
                    // $p1->SetImpuls();
                    $p1->mark->SetFillColor("red");
                    $p1->mark->SetWidth(4);
                    $p1->SetColor("blue");
//                    $p2->mark->SetType(MARK_FILLEDCIRCLE);
//                    $p2->SetImpuls();
//                    $p2->mark->SetFillColor("orange");
//                    $p2->mark->SetWidth(4);
//                    $p2->SetColor("black");
//                    $p3->mark->SetType(MARK_SQUARE);
//                    $p3->SetImpuls();
//                    $p3->mark->SetFillColor("red");
//                    $p3->mark->SetWidth(4);
//                    $p3->SetColor("blue");
//                    $p4->mark->SetType(MARK_FILLEDCIRCLE);
//                    $p4->SetImpuls();
//                    $p4->mark->SetFillColor("orange");
//                    $p4->mark->SetWidth(4);
//                    $p4->SetColor("black");
//                    $p5->mark->SetType(MARK_SQUARE);
//                    $p5->SetImpuls();
//                    $p5->mark->SetFillColor("red");
//                    $p5->mark->SetWidth(4);
//                    $p5->SetColor("blue");
                    $p1->SetCenter();
                    $graph->Add(array($p1));	
                    $graph->Stroke();
                    break;
					
                case'excel':
                    $bgcoloraja="bgcolor=#DEDEDE align=center";
                   $tab.="
    <table class=sortable cellspacing=1 border=1>
     <thead>
	  <tr class=rowheader>
	   <td ".$bgcoloraja.">No</td>
	   <td ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</td>
	   <td ".$bgcoloraja.">".$_SESSION['lang']['komoditi']."</td>
	   <td ".$bgcoloraja.">".$_SESSION['lang']['satuan']."</td>
	   <td ".$bgcoloraja.">".$_SESSION['lang']['pasar']."</td>
	   <td ".$bgcoloraja.">".$_SESSION['lang']['matauang']."</td>
           <td ".$bgcoloraja.">".$_SESSION['lang']['harga']."</td></tr></thead><tbody>";
		$str="select * from ".$dbname.".pmn_hargapasar where tanggal!='' ".$where." order by `tanggal` desc";
		// echo "warning:".$str;exit();
		if($res=mysql_query($str))
		{
                    $barisData=mysql_num_rows($res);
                    if($barisData>0)
                    {
                        while($bar=mysql_fetch_object($res))
                        {

                        $no+=1;


                        $tab.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".tanggalnormal($bar->tanggal)."</td>
                        <td>".$optNmBarang[$bar->kodeproduk]."</td>
                        <td>".$bar->satuan."</td>
                        <td>".$bar->pasar."</td>
                        <td>".$bar->matauang."</td>
                        <td align=right>".number_format($bar->harga,2)."</td>
                        </tr>";
                        }	 	
                        
                    }
                    else
                    {
                        $tab.="<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
		}	
		else
		{
		echo " Gagal,".(mysql_error($conn));
		}	
		$tab.="</tbody></table>";
                $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
                $dte=date("Hms");
                $nop_="hargaPasar_".$dte;
                $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                gzwrite($gztralala, $tab);
                gzclose($gztralala);
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";	
                break;
		default:
		break;
	}

?>