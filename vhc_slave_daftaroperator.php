<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$company_id = checkPostGet('company_id','');
$company_name = checkPostGet('company_name','');
$jnsVhc = checkPostGet('jnsVhc','');
$jnsVhc_name = checkPostGet('jnsVhc_name','');

switch($proses)
{
	case 'cari':
		if($company_id==''){
			echo"warning: Unit harus dipilih.";
			exit();
		}
		
		echo"<img onclick=dataKeExcel(event,'vhc_slave_daftaroperator.php') src='images/excel.jpg' class=resicon title='MS.Excel'> 
			<img onclick='dataKePDF(event)' title='PDF' class=resicon src='images/pdf.jpg'>";
		
        echo"<table cellspacing=1 border=0>
				<thead>
				<tr class=rowheader>
					<td>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['operator']."/".$_SESSION['lang']['karyawan']."</td>
					<td align=center>".$_SESSION['lang']['jenisvch']."</td>
					<td align=center>".$_SESSION['lang']['kodealat']."/".$_SESSION['lang']['nopol']."</td>
				</tr>
				</thead>
				<tbody>";
				
        $sql="select a.nama,b.kodevhc,c.namajenisvhc from ".$dbname.".vhc_5operator a 
			left join ".$dbname.".vhc_5master b
			on a.vhc=b.kodevhc 
			left join ".$dbname.".vhc_5jenisvhc c
			on b.jenisvhc=c.jenisvhc 
            where b.kodeorg like '%".substr($company_id, 0,4)."%' and b.jenisvhc like '%".$jnsVhc."%' 
			order by c.namajenisvhc asc, b.kodevhc asc";
			
		$qry=mysql_query($sql) or die(mysql_error());
        $row=mysql_num_rows($qry);
        
		if($row>=1){
			while($res=mysql_fetch_assoc($qry)){
				$no+=1;
               echo"<tr class=rowcontent>
						<td>".$no."</td>
						<td>".$res['nama']."</td>
						<td>".$res['namajenisvhc']."</td>
						<td>".$res['kodevhc']."</td>
					</tr>";
			}
        }else{
            echo"<tr class=rowcontent align=center><td colspan=11>Not Found</td></tr>";
        }
		
        echo"</tbody></table></div>";
        break;
        
        case'getExcel':
            if($company_id==''){
				echo"warning: Unit harus dipilih.";
				exit();
			}
			
			$stream.="<table cellspacing=1 border=1>
					<tr>
						<td colspan=4 style='text-align:center'><b>Daftar Operator</b></td>
					</tr>
					<thead>
					<tr class=rowheader>
						<td>".$_SESSION['lang']['nourut']."</td>
						<td align=center>".$_SESSION['lang']['operator']."/".$_SESSION['lang']['karyawan']."</td>
						<td align=center>".$_SESSION['lang']['jenisvch']."</td>
						<td align=center>".$_SESSION['lang']['kodealat']."/".$_SESSION['lang']['nopol']."</td>
					</tr>
					</thead>
					<tbody>";
					
			$sql="select a.nama,b.kodevhc,c.namajenisvhc from ".$dbname.".vhc_5operator a 
				left join ".$dbname.".vhc_5master b
				on a.vhc=b.kodevhc 
				left join ".$dbname.".vhc_5jenisvhc c
				on b.jenisvhc=c.jenisvhc 
				where b.kodeorg like '%".substr($company_id, 0,4)."%' and b.jenisvhc like '%".$jnsVhc."%' 
				order by c.namajenisvhc asc, b.kodevhc asc";
				
			$qry=mysql_query($sql) or die(mysql_error());
			$row=mysql_num_rows($qry);
			
			if($row>=1){
				while($res=mysql_fetch_assoc($qry)){
					$no+=1;
				   $stream.="<tr class=rowcontent>
							<td>".$no."</td>
							<td>".$res['nama']."</td>
							<td>".$res['namajenisvhc']."</td>
							<td>".$res['kodevhc']."</td>
						</tr>";
				}
			}else{
				$stream.="<tr class=rowcontent align=center><td colspan=11>Not Found</td></tr>";
			}
			
			$stream.="</tbody></table></div>";

            $stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHis");
            $nop_="DaftarOperator_".$dte;
            $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                         gzwrite($gztralala, $stream);
                         gzclose($gztralala);
                         echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls.gz';
                            </script>";

        break;
		
		case 'pdf':
			if($company_id==''){
				echo"warning: Unit harus dipilih.";
				exit();
			}
			
        class PDF extends FPDF
        {
            function Header() {
                global $conn;
                global $dbname;
				global $company_id;
				global $company_name;
				global $jnsVhc;
				global $jnsVhc_name;

                # Alamat & No Telp
                $query = selectQuery($dbname,'organisasi','alamat,telepon',
                    "kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'");
                $orgData = fetchData($query);

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 12;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,40);	
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(80);   
                $this->Cell($width-80,$height,$_SESSION['org']['namaorganisasi'],0,1,'L');	 
                $this->SetX(80); 		
                $this->Cell($width-80,$height,$orgData[0]['alamat'],0,1,'L');	
                $this->SetX(80); 			
                $this->Cell($width-80,$height,"Tel: ".$orgData[0]['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                    $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial','',8);
                                if($comId!='')
                                {
                $this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['kodeorg'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/80*$width,$height,$comId,'',0,'L');
                                }
                $this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['user'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/80*$width,$height, $_SESSION['standard']['username'],0,0,'L');
                $this->Ln();
                                if($comId!='')
                                {

                                $query2 = selectQuery($dbname,'organisasi','namaorganisasi',
                                "kodeorganisasi='".$comId."'");
                                $orgData2 = fetchData($query2);
                $this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['unit'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(45/80*$width,$height,$orgData2[0]['namaorganisasi'],'',0,'L');
                                }
                $this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/80*$width,$height,date('d-m-Y H:i:s'),'',1,'L');
				
				$this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['unit'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/80*$width,$height,$company_name,'',1,'L');
				
				$this->Cell((20/80*$width)-5,$height,$_SESSION['lang']['jenisvch'],'',0,'L');
                $this->Cell(5,$height,':','',0,'L');
                $this->Cell(15/80*$width,$height,$jnsVhc_name,'',1,'L');


                $this->Ln();
                $this->SetFont('Arial','U',12);
                $this->Cell($width,$height,$_SESSION['lang']['daftaroperator'],0,1,'C');	
                $this->Ln();	
                 $this->SetFont('Arial','',8);
                $this->SetFillColor(220,220,220);
                                $this->Cell(10/100*$width,$height,$_SESSION['lang']['nourut'],1,0,'C',1);
                                $this->Cell(30/100*$width,$height,$_SESSION['lang']['operator']."/".$_SESSION['lang']['karyawan'],1,0,'C',1);
                                $this->Cell(25/100*$width,$height,$_SESSION['lang']['jenisvch'],1,0,'C',1);
                                $this->Cell(25/100*$width,$height,$_SESSION['lang']['kodealat']."/".$_SESSION['lang']['nopol'],1,0,'C',1);
								$this->Ln();
			}

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
        }
		
        $pdf=new PDF('P','pt','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 12;
		$pdf->AddPage();

		$pdf->SetFillColor(255,255,255);
		$pdf->SetFont('Arial','',7);	
		
		
        $sql="select a.nama,b.kodevhc,c.namajenisvhc from ".$dbname.".vhc_5operator a 
			left join ".$dbname.".vhc_5master b
			on a.vhc=b.kodevhc 
			left join ".$dbname.".vhc_5jenisvhc c
			on b.jenisvhc=c.jenisvhc 
			where b.kodeorg like '%".substr($company_id, 0,4)."%' and b.jenisvhc like '%".$jnsVhc."%' 
			order by c.namajenisvhc asc, b.kodevhc asc";
			
		$qry=mysql_query($sql) or die(mysql_error());
		$row=mysql_num_rows($qry);
        if($row>=1)
        {
			$no=0;
			while($res=mysql_fetch_assoc($qry))
			{
				$no+=1;
				$pdf->Cell(10/100*$width,$height,$no,'LBR',0,'C',1);
				$pdf->Cell(30/100*$width,$height,$res['nama'],'LBR',0,'L',1);
				$pdf->Cell(25/100*$width,$height,$res['namajenisvhc'],'LBR',0,'L',1);
				$pdf->Cell(25/100*$width,$height,$res['kodevhc'],'LBR',0,'L',1);
				$pdf->Ln();
			}
        }
        else
        {
                $pdf->Cell(90/100*$width,$height,'Not Found',1,1,'C',1);
        }
        $pdf->Output();
        break;
		
        default:
        break;
}


?>

