<?php //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param=$_POST;
$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

$optPt=  makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept');

switch($param['proses']){
	case'loadData':
		if(!empty($param['nodo'])) {
            $where=" a.nodo like '%".$param['nodo']."%'";
        }
        if(!empty($param['tanggalCr'])) {            
            $tgrl=explode("-",$param['tanggalCr']);
            $ert=$tgrl[2]."-".$tgrl[1]."-".$tgrl[0];
            $where=" left(a.tanggaldo,10) = '".$ert."'";
        }
		$limit=10;
        $page=0;
        if(isset($_POST['page'])) {
            $page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
        $sql="select count(*) jmlhrow from ".$dbname.".pmn_suratperintahpengiriman where nodo like '%".$param['nodo']."%' and tanggaldo like '%".tanggalsystem($param['tanggalCr'])."%' order by tanggaldo desc";
        $query=mysql_query($sql) or die(mysql_error());
        while($jsl=mysql_fetch_object($query)){
            $jlhbrs= $jsl->jmlhrow;
        }
		
        
        if($_POST['nokontrak']!='')
        {
            $schkontrak=" and a.nokontrak like '%".$_POST['nokontrak']."%'";
        }
        

        
        $str="select a.*,c.namacustomer,d.namabarang,b.kuantitaskontrak from ".$dbname.".pmn_suratperintahpengiriman a
			left join ".$dbname.".pmn_kontrakjual b
			on a.nokontrak = b.nokontrak
			left join ".$dbname.".pmn_4customer c
			on b.koderekanan = c.kodecustomer
			left join ".$dbname.".log_5masterbarang d
			on b.kodebarang = d.kodebarang 
			where a.nodo like '%".$param['nodo']."%' "
                        . " ".$schkontrak." and tanggaldo like '%".tanggalsystem($param['tanggalCr'])."%' 
			order by a.tanggaldo desc
            limit ".$offset.",".$limit." ";
        
        $qstr=mysql_query($str) or die(mysql_error($conn));
		$tab='';$nor=0;
        while($rstr=  mysql_fetch_assoc($qstr)) {
            $nor+=1;
			$tab.="<tr class=rowcontent>
				<td>".$rstr['nodo']."</td>
				<td>".tanggalnormal($rstr['tanggaldo'])."</td>
				<td>".$rstr['nokontrak']."</td>
				<td>".$rstr['namacustomer']."</td>
				<td>".$rstr['namabarang']."</td>
				<td align=right>".number_format($rstr['qty'],0)."</td>
				<td>".$rstr['dibuatoleh']."</td>
				<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit ".$rstr['nodo']."' onclick=\"fillField('".$rstr['nodo']."');\" ></td>
				<td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus ".$rstr['nodo']."' onclick=\"delData('".$rstr['nodo']."');\" ></td>
				<td align=center><img src=images/pdf.jpg class=resicon  title='Detail ".$rstr['nodo']."' onclick=\"masterPDF('pmn_suratperintahpengiriman','".$rstr['nodo']."','','pmn_slave_print_pdf_suratperintahpengiriman',event);\" ></td>
				</tr>"; 
        }
		$skeupenagih="select count(*) as rowd from ".$dbname.".pmn_suratperintahpengiriman";
		$qkeupenagih=mysql_query($skeupenagih) or die(mysql_error($conn));
		$rkeupenagih=mysql_num_rows($qkeupenagih);
		$totrows=ceil($rkeupenagih/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
			$isiRow.="<option value='".$er."'>".$er."</option>";
		}
		$footd="</tr>
            <tr><td colspan=10 align=center>
            
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
		echo $tab."####".$footd;
		break;

	case'getFormNosipb':
        $optSupplierCr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sSuplier="select distinct supplierid,namasupplier,substr(kodekelompok,1,1) as status from ".$dbname.".log_5supplier order by namasupplier asc";
        $qSupplier=mysql_query($sSuplier) or die(mysql_error($sSupplier));
        while($rSupplier=mysql_fetch_assoc($qSupplier))
        {
            $optSupplierCr.="<option value='".$rSupplier['supplierid']."'>".$rSupplier['namasupplier']." [".$rSupplier['status']."]</option>";
        }
        $form="<fieldset style=float: left;>
               <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."</legend>
               ".$_SESSION['lang']['NoKontrak']." ".$param['status']."&nbsp;<input type=text class=myinputtext id=nosipbcr />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=findNosipb('".$param['status']."')>".$_SESSION['lang']['find']."</button></fieldset>
               <fieldset><legend>".$_SESSION['lang']['result']."</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
		break;
		
	case'getnosibp':
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
		$tab.="<thead>";
		$tab.="<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab.="<td>".$_SESSION['lang']['kodecustomer']."</td>";
		$tab.="<td>".$_SESSION['lang']['namacust']."</td>";
		$tab.="<td style='text-align:center'>Qty Outstanding</td></tr></thead><tbody>";
		
		if($param['status']=='Eksternal'){
			$whereStatus = " b.statusinteks = 'Eksternal'";
		}else{
			$whereStatus = " b.statusinteks = 'Internal' and a.koderekanan = 'API'";
		}
		
		$sdata="select distinct a.franco,a.nokontrak,a.koderekanan,b.namacustomer,a.kodept,a.matauang,a.hargasatuan,a.kuantitaskontrak,a.kuantitaskirim,a.kodeorg,a.tanggalkirim,a.sdtanggal from ".$dbname.".pmn_kontrakjual a
				left join ".$dbname.".pmn_4customer b on a.koderekanan=b.kodecustomer
				where ".$whereStatus." and a.nokontrak like '%".$param['txtfind']."%'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		
		$sQty = selectQuery($dbname,'pmn_suratperintahpengiriman',"SUM(qty) as QTY, nokontrak")." group by nokontrak";
		$rQty = fetchData($sQty);
		$optQty = array();
		foreach($rQty as $row) {
			$optQty[$row['nokontrak']] = $row['QTY'];
		}
		
		while($rdata=  mysql_fetch_assoc($qdata)){
			$brt="style=cursor:pointer; onclick=setData('".$rdata['nokontrak']."','".$rdata['koderekanan']."','".$rdata['kodept']."','".$rdata['kodeorg']."','".tanggalnormal($rdata['tanggalkirim'])."','".tanggalnormal($rdata['sdtanggal'])."','".$rdata['kuantitaskontrak']."','".$param['status']."','".$rdata['franco']."')";
			$tab.="<tr ".$brt." class=rowcontent><td>".$rdata['nokontrak']."</td>";
			$tab.="<td>".$rdata['koderekanan']."</td>";
			$tab.="<td>".$optnmcust[$rdata['koderekanan']]."</td>";
			$tab.="<td style='text-align:right'>".number_format(($rdata['kuantitaskontrak'] - $optQty[$rdata['nokontrak']]),2)."</td></tr>";
		}
		$tab.="</tbody></table>";
		echo $tab;
		break;

    case'insert':
        
       
        
        if($param['tanggalsurat']==''){
            exit("error: Tanggal surat tidak boleh kosong");
        }
		if($param['waktupenyerahan']==''){
            exit("error: Waktu Penyerahan tidak boleh kosong");
        }
		if($param['tempatpenyerahan']==''){
            exit("error: Tempat Penyerahan tidak boleh kosong");
        }
//        if($param['dibuat']==''){
//            exit("error: Dibuat tidak boleh kosong");
//        }
//		if($param['jabatan']==''){
//            exit("error: Jabatan tidak boleh kosong");
//        }
        
        
        
      
        $iCekDo=" select count(*) as jumlah from ".$dbname.".pmn_suratperintahpengiriman  where nodo='".$param['nodo']."' ";
      
        $nCekDo=mysql_query($iCekDo) or die (mysql_error($conn));
        $dCekDo=  mysql_fetch_assoc($nCekDo);
        
		if($dCekDo['jumlah']>0)
        { 
            //$sdel="delete from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$param['nodo']."'";
            //if(mysql_query($sdel)){
            //
            //}else{
            //        exit("error: code 1125\n ".  mysql_error($conn)."___".$sdel);
            //}
            $nodo=$param['nodo'];  
        }//generateNoDO();
        else 
        {
            $nodo=generateNoDO();
        }
         
       
      
        $iCek="select sum(qty) as qtysipb from ".$dbname.".pmn_suratperintahpengiriman where nokontrak='".$param['nokontrak']."' ";
        $nCek=  mysql_query($iCek) or die (mysql_error($conn));
        $dCek=mysql_fetch_assoc($nCek);
            $qtysipb=$dCek['qtysipb'];
            
            if($qtysipb=='' || $qtysipb==NULL)
            {
                $qtysipb=0;
            }
            
      
        $iQty="select kuantitaskontrak from ".$dbname.".pmn_kontrakjual where nokontrak='".$param['nokontrak']."' ";
        
        $nQty=  mysql_query($iQty) or die (mysql_error($conn));
        $dQty=mysql_fetch_assoc($nQty);
		$qtykontrak=$dQty['kuantitaskontrak'];
		
		if($dCekDo['jumlah']>0) {
			$totalKeseluruhan=$qtysipb;
		} else {
			$totalKeseluruhan=$qtysipb+$param['qty'];
		}
        
        $sisa=$qtykontrak-$totalKeseluruhan;
        
        
        if($totalKeseluruhan>$qtykontrak)
        {  
            exit("Error:QTY telah melebihi kontrak\n kontrak : $qtykontrak, total DO $totalKeseluruhan, sisa yang di perbolehkan : $sisa ");
        }
         
        $sinser="insert into ".$dbname.".pmn_suratperintahpengiriman 
			(nodo,tanggaldo,nokontrak,nokontrakinternal,tempatpenyerahan,
			waktupenyerahan,keterangan,dibuatoleh,jabatan,kepada,ttd,qty) values 
			('".$nodo."','".tanggalsystem($param['tanggalsurat'])."',"
			. "'".$param['nokontrak']."','".$param['nokontrakInternal']."','".$param['tempatpenyerahan']."',"
			. "'".$param['waktupenyerahan']."','".$param['lain']."',"
			. "'".$param['dibuat']."','".$param['jabatan']."',"
			. "'".$param['kepada']."','".$param['ttd']."','".$param['qty']."')";
		if(!mysql_query($sinser)){
			//exit("error: code 1125\n ".  mysql_error($conn)."___".$sinser);
			$dataUpd = array(
				'tanggaldo' => tanggalsystem($param['tanggalsurat']),
				'nokontrak' => $param['nokontrak'],
				'nokontrakinternal' => $param['nokontrakInternal'],
				'tempatpenyerahan' => $param['tempatpenyerahan'],
				'waktupenyerahan' => $param['waktupenyerahan'],
				'keterangan' => $param['lain'],
				'dibuatoleh' => $param['dibuat'],
				'jabatan' => $param['jabatan'],
				'kepada' => $param['kepada'],
				'ttd' => $param['ttd'],
				'qty' => $param['qty']
			);
			$sUpd = updateQuery($dbname,'pmn_suratperintahpengiriman',$dataUpd,
								"nodo='".$nodo."'");
			if(!mysql_query($sUpd)){
				exit("error: code 1125\n ".  mysql_error($conn)."___".$sUpd);
			}
		}
        break;
		
	case'getData':
		$sdata="select distinct a.*,b.koderekanan,b.kodept from ".$dbname.".pmn_suratperintahpengiriman a
				left join ".$dbname.".pmn_kontrakjual b
				on a.nokontrak = b.nokontrak
				where a.nodo='".$param['nodo']."'";
		$qdata=mysql_query($sdata) or die(mysql_error($conn));
		$rdata=mysql_fetch_assoc($qdata);
		echo $rdata['nokontrak']."###".$rdata['nodo']."###".$rdata['koderekanan']."###".$rdata['kodept']."###".tanggalnormal($rdata['tanggaldo'])."###".$rdata['waktupenyerahan']."###".$rdata['tempatpenyerahan']."###".$rdata['dibuatoleh']."###".$rdata['keterangan']."###".$rdata['jabatan']."###".$rdata['kepada']."###".$rdata['ttd']."###".number_format($rdata['qty'],2)."###".$rdata['nokontrakinternal'];
		break;
		
    case'getQty':
		$sQty = "SELECT SUM(qty) as QTY FROM ".$dbname.".pmn_suratperintahpengiriman WHERE nokontrak = '".$param['nokontrak']."'";
		$qQty = mysql_query($sQty) or die(mysql_error($conn));
		$rQty = mysql_fetch_assoc($qQty);
		$vQty = $param['kuantitaskontrak'] - $rQty['QTY'];
		if($vQty < 0){
			$hQty = 0;
		}else{
			$hQty = $vQty;
		}
		echo number_format($hQty,2);
		break;
            
            
	case'delData':
        $sdel="delete from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$param['nodo']."'";
        if(!mysql_query($sdel)){
            exit("error: gak berhasil".mysql_error($conn)."___".$sdel);
        }
		break;
		
	default:
		break;
}

function generateNoDO(){
	global $dbname;
	global $conn;
	global $_POST;
        global $optPt;
	
	$bulan = substr($_POST['tanggalsurat'],3,2);
	$arrayRomawi = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
	$resultRomawi = $arrayRomawi[(int)$bulan-1];
	$ql="select `nodo` from ".$dbname.".`pmn_suratperintahpengiriman`";
	$ql.= " where nodo like '%".$optPt[$_POST['nokontrak']]."_%' and tanggaldo like '".substr($_POST['tanggalsurat'],6,4)."%'
		order by nodo desc limit 1";
	$qr=mysql_query($ql) or die('error: '.mysql_error());
	$data = mysql_fetch_object($qr);
	$countNoDO = substr($data->nodo,0,3);
	$noInvoice = addZero($countNoDO+1,3)."/".$optPt[$_POST['nokontrak']]."_".$_POST['kodecustomer']."/".$resultRomawi."/".substr($_POST['tanggalsurat'],6,4);
	
	return $noInvoice;
}