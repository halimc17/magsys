<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=		isset($_POST['pt'])? $_POST['pt']: '';
$gudang=	isset($_POST['gudang'])? $_POST['gudang']: '';
$periode=	isset($_POST['periode'])? $_POST['periode']: '';

if(isset($_POST['unitDt']))//ini dari tab laporan stok per unit (tab 3)
{
	if($_POST['unitDt']=='')
	{
	   exit("Error: Unit Tidak Boleh Kosong");
	}
        $str="select 
              a.kodeorg,
              a.kodebarang,
              sum(a.saldoakhirqty) as salakqty,
              sum(a.nilaisaldoakhir) as salakrp,
              sum(a.qtymasuk) as masukqty,
              sum(a.qtykeluar) as keluarqty,
              sum(qtymasukxharga) as masukrp,
              sum(qtykeluarxharga) as keluarrp,                      
              sum(a.saldoawalqty) as sawalqty,
              sum(a.nilaisaldoawal) as sawalrp,
                b.namabarang,b.satuan    
                from ".$dbname.".log_5saldobulanan a
                left join ".$dbname.".log_5masterbarang b
                on a.kodebarang=b.kodebarang
              where kodegudang like '".$_POST['unitDt']."%' 
              and periode='".$periode."' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
              group by a.kodebarang order by a.kodebarang";
}
    else if($gudang=='')
    {
			if($pt=='')
			{
			   exit("Error: Perusahaan Tidak Boleh Kosong");
			}
            $str="select 
                      a.kodeorg,
                      a.kodebarang,
                      sum(a.saldoakhirqty) as salakqty,
                      sum(a.nilaisaldoakhir) as salakrp,
                      sum(a.qtymasuk) as masukqty,
                      sum(a.qtykeluar) as keluarqty,
                      sum(qtymasukxharga) as masukrp,
                      sum(qtykeluarxharga) as keluarrp,                      
                      sum(a.saldoawalqty) as sawalqty,
                      sum(a.nilaisaldoawal) as sawalrp,
                        b.namabarang,b.satuan    
                        from ".$dbname.".log_5saldobulanan a
                        left join ".$dbname.".log_5masterbarang b
                        on a.kodebarang=b.kodebarang
                      where kodeorg='".$pt."' 
                      and periode='".$periode."' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang order by a.kodebarang";
    }
    else
    {
			if($pt=='')
			{
			   exit("Error: Perusahaan Tidak Boleh Kosong");
			}
            $str="select
                      a.kodeorg,
                      a.kodebarang,
                      a.saldoakhirqty as salakqty,
                      a.hargarata as harat,
                      a.nilaisaldoakhir as salakrp,
                      a.qtymasuk as masukqty,
                      a.qtykeluar as keluarqty,
                      a.qtymasukxharga as masukrp,
                      a.qtykeluarxharga as keluarrp,
                      a.saldoawalqty as sawalqty,
                      a.hargaratasaldoawal as sawalharat,
                      a.nilaisaldoawal as sawalrp,
                      b.namabarang,b.satuan 		 		      
                      from ".$dbname.".log_5saldobulanan a
                       left join ".$dbname.".log_5masterbarang b
                      on a.kodebarang=b.kodebarang
                      where kodeorg='".$pt."' 
                      and periode='".$periode."'
                      and kodegudang='".$gudang."' and (a.qtymasuk!=0 or a.qtykeluar!=0 or a.saldoakhirqty!=0)
                      group by a.kodebarang
                      order by a.kodebarang";
    }	
//exit("error: ".$str);
//=================================================
    $salakqty	=0;
    $harat	=0;
    $salakrp	=0;
    $masukqty	=0;
    $keluarqty	=0;
    $masukrp	=0;
    $keluarrp	=0;
    $sawalQTY	=0;
    $sawalharat	=0;
    $sawalrp	=0;
    $namabarang	=0;
	 

    //
    $res=mysql_query($str);
    $no=0;
    if(mysql_num_rows($res)<1)
    {
            echo"<tr class=rowcontent><td colspan=17>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
            {
			$totkeluarrp=$totmasukrp=$totsawalrp=$totsalakrp=0;
            while($bar=mysql_fetch_object($res))
            {
                    $no+=1;
                    $kodebarang=$bar->kodebarang;
                    $namabarang=$bar->namabarang; 
                    $salakqty	=$bar->salakqty;
                    $salakrp	=$bar->salakrp;
                    $masukqty	=$bar->masukqty;
                    $keluarqty	=$bar->keluarqty;
                    $masukrp	=$bar->masukrp;
                    $keluarrp	=$bar->keluarrp;
                    $sawalQTY	=$bar->sawalqty;
                    $sawalrp	=$bar->sawalrp;

                    @$sawalharat=$bar->sawalrp/$bar->sawalqty;
                    @$haratmasuk=$bar->masukrp/$bar->masukqty;
                    @$haratkeluar=$bar->keluarrp/$bar->keluarqty;
                    @$harat	=$bar->salakrp/$bar->salakqty;

if(isset($_POST['unitDt']))//ini dari tab laporan stok per unit (tab 3)
{
        echo"<tr class=rowcontent> ";                           
}
else {
        echo"<tr class=rowcontent  style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarangHargaExcel(event,'".$pt."','".$periode."','".$gudang."','".$kodebarang."','".$namabarang."','".$bar->satuan."','log_laporanMutasiDetailPerBarangHarga_Excel.php');\"> ";
}

echo "                   <td>".$no."</td>
                              <td>".$periode."</td>
                              <td>".$kodebarang."</td>
                              <td>".$namabarang."</td>
                              <td>".$bar->satuan."</td>
                               <td align=right class=firsttd>".number_format($sawalQTY,2,'.',',')."</td>
                               <td align=right>".number_format($sawalharat,2,'.',',')."</td>
                               <td align=right>".number_format($sawalrp,2,'.',',')."</td>
                               <td align=right class=firsttd>".number_format($masukqty,2,'.',',')."</td>
                               <td align=right>".number_format($haratmasuk,2,'.',',')."</td>
                               <td align=right>".number_format($masukrp,2,'.',',')."</td>
                               <td align=right class=firsttd>".number_format($keluarqty,2,'.',',')."</td>
                               <td align=right>".number_format($haratkeluar,2,'.',',')."</td>
                               <td align=right>".number_format($keluarrp,2,'.',',')."</td>
                               <td align=right class=firsttd>".number_format($salakqty,2,'.',',')."</td>
                               <td align=right>".number_format($harat,2,'.',',')."</td>
                               <td align=right>".number_format($salakrp,2,'.',',')."</td>			   
                            </tr>"; 	

                            //while total
                            $totsawalrp+=$sawalrp;
                            $totmasukrp+=$masukrp;
                            $totkeluarrp+=$keluarrp;
                            $totsalakrp+=$salakrp;
                            
                            

            }		
            
        echo"<tr class=rowcontent>";
        echo"<td colspan=4 align=center><b>".$_SESSION['lang']['total']."</b></td>";
        echo"<td colspan=3></td>";
        echo"<td colspan align=right><b>".number_format($totsawalrp,2)."</b></td>";
        echo"<td colspan=2></td>";
        echo"<td colspan align=right><b>".number_format($totmasukrp,2)."</b></td>";
        echo"<td colspan=2></td>";
        echo"<td colspan align=right><b>".number_format($totkeluarrp,2)."</b></td>";
        echo"<td colspan=2></td>";
        echo"<td colspan align=right><b>".number_format($totsalakrp,2)."</b></td>";
        echo"</tr>";
    }
?>