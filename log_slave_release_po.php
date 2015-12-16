<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method=		$_POST['method'];
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$user_id=		$_SESSION['standard']['userid'];
$rlse_user_id=	isset($_POST['id_user'])? $_POST['id_user']: '';
$this_date=		date("Y-m-d");
$tglR=			isset($_POST['tglR'])? $_POST['tglR']: '';
$ket=			isset($_POST['ket'])? $_POST['ket']: '';
$texkKrsi=		isset($_POST['texkKrsi'])? $_POST['texkKrsi']: '';

switch ($method) {
	case 'release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_assoc($query);
		if(($res['persetujuan1']!='') || ($res['persetujuan2']!='')) {
			if(($res['stat_release']==0) && ($res['useridreleasae']==0000000000)) {		
				$unopo="update ".$dbname.".log_poht set stat_release='1', useridreleasae='".$rlse_user_id."',tglrelease='".$this_date."' where nopo='".$nopo."' ";
				$qnopo=mysql_query($unopo) or die(mysql_error());
			} else {
				exit("warning:PO Sudah Di Release atau sedang koreksi");
			}
		} else {
			exit("Error: Belum Ada Penanda Tangan Dari P0 ".$nopo."");
		}
		break;
	
	case 'un_release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=mysql_query($sql) or die(mysql_error());
		$res=mysql_fetch_assoc($query);

		if(($res['stat_release']==1) && ($res['useridreleasae']==$rlse_user_id)&&($res['tglrelease']==$this_date))
		{		
			$unopo="update ".$dbname.".log_poht set stat_release='0', useridreleasae='0000000000',tglrelease='0000-00-00' where nopo='".$nopo."' ";
			$qnopo=mysql_query($unopo) or die(mysql_error());
		}
		else
		{
			echo "warning:You Don`t Have Autorize to Unrelease This PO No. ".$nopo;
			exit();
		}
		break;
	
	case 'list_new_data_release_po':
            
            
            
           #query cek jumlah penerimaan di gudang
           $sCek="select sum(jumlahpesan-jumlahterima) as selisih,kodebarang,nopo from ".$dbname.".log_po_terima_vw
                   where kodeorg='".$_SESSION['org']['kodeorganisasi']."' group by nopo,kodebarang order by nopo asc";
            $qCek=mysql_query($sCek) or die(mysql_error($conn));
            while($rCek=  mysql_fetch_assoc($qCek)){
                if($nomoPo!=$rCek['nopo']){
                    $nomoPo=$rCek['nopo'];
                    $sJmlhBrg="select count(kodebarang) as jmlbrg from ".$dbname.".log_podt where nopo='".$rCek['nopo']."'";
                    $qJmlBrg=mysql_query($sJmlhBrg) or die(mysql_error($conn));
                    $rJmlBrg=mysql_fetch_assoc($qJmlBrg);
                    $totBrg[$nomoPo]=$rJmlBrg['jmlbrg'];
                }
                if($rCek['selisih']==0){
                    $brgCompr[$rCek['nopo']]+=1;
                }
            }
            
		/*$add="";
		if($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
			$add=" and lokalpusat=1 ";
		}*/
		$limit=20;
		$page=0;
		if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		
		// Search Query
		$txt_search='';
		$txt_tgl='';
		if(!empty($_POST['txtSearchrpo'])) {
			$txt_search=$_POST['txtSearchrpo'];
		}
		if(!empty($_POST['tglCarirpo'])) {
			$txt_tgl=tanggalsystem($_POST['tglCarirpo']);
			$txt_tgl_a=substr($txt_tgl,0,4);
			$txt_tgl_b=substr($txt_tgl,4,2);
			$txt_tgl_c=substr($txt_tgl,6,2);
			$txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
		}
		
		$where = "";
		if(!empty($txt_search)) {
			$where .= " and nopo LIKE  '%".$txt_search."%' ";
		}
		
		if(!empty($txt_tgl)) {
			$where .= " and tanggal LIKE '%".$txt_tgl."%' ";
		}
                
                
                if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
                {
                    $where.=" and lokalpusat=1";
                    $where.=" and kodeorg='".$_SESSION['empl']['kodeorganisasi']."' ";
                }
		
		$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where statuspo>1 ".$add." ".$where."  ORDER BY tanggal DESC";
		$query2=mysql_query($sql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$maxPage = ceil($jlhbrs/$limit) - 1;
                
                if($jlhbrs<1)
                {
                    echo"data kosong";
                }$arrlp=array("0"=>"Pusat","1"=>"Lokal");
		
		$str="SELECT * FROM ".$dbname.".log_poht where statuspo>1 ".$add." ".$where." ORDER BY tanggal DESC limit ".$offset.",".$limit." ";
		//echo $str;
		if($res=mysql_query($str))
		{
			$no=0;
			while($bar=mysql_fetch_assoc($res))
			{
				$this_date=date("Y-m-d");
				$kodeorg=$bar['kodeorg'];
				$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$kodeorg."' or induk='".$kodeorg."'"; //echo $spr;
				$rep=mysql_query($spr) or die(mysql_error($conn));
				$bas=mysql_fetch_object($rep);
				$no+=1;
				echo"<tr id='tr_".$no."' ".($bar['stat_release']==2?"bgcolor='orange'":"class=rowcontent")."  >
					<td>".$no."</td>
					<td id=td_".$no.">".$bar['nopo']."</td>
                                            <td id=td_".$no.">".$arrlp[$bar['lokalpusat']]."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td align=center>".$kodeorg."</td>";
				
				//ambil catatan release untuk menginput informasi koreksi
				$sKrsi="select catatanrelease from ".$dbname.".log_poht where nopo='".$bar['nopo']."'";
				$qKrsi=mysql_query($sKrsi) or die(mysql_error($conn));
				$rKrasi=mysql_fetch_assoc($qKrsi);
				
				//cek transaksi penerimaan barang dari supplier apakah sudah ada
				$sCountPO="select * from ".$dbname.".log_transaksidt where nopo='".$bar['nopo']."'";
				$qCountPO=mysql_query($sCountPO) or die(mysql_error($conn));
				$countPO=mysql_num_rows($qCountPO);
				
				//mengambil namakaryawan untuk informasi signature(penanda tangan)
				$sql="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar['persetujuan1']."'";
				$query=mysql_query($sql) or die(mysql_error());
				$yrs=mysql_fetch_assoc($query);
                                
                                
                                $disbtn="disabled";
                                if($bar['closed']=='0'){#jika close blm satu blm disable
                                        $disbtn="";
                                }
                                
                                if($brgCompr[$bar['nopo']]!=0){
                                    if($brgCompr[$bar['nopo']]==$totBrg[$bar['nopo']]){
                                        $disbtn="disabled";
                                    }
                                }
                                
                                if($bar['stat_release']=='0' || $bar['stat_release']=='' || is_null($bar['stat_release']))
                                {
                                    $disbtn="disabled";
                                }
                                
                                
				if($_SESSION['empl']['tipelokasitugas']!='KANWIL') 
                                {    
                                    if($rKrasi['catatanrelease']!='') 
                                    {
                                        $isi=" disabled";
                                    }
                                    else 
                                    {
                                        $isi="";
                                    }
					
                                    if(($bar['stat_release']!=1)||($bar['stat_release']=="")) 
                                    {
                                            echo"<td align=left>".$yrs['namakaryawan']."</td>
                                                    <td align=center valign=\"middle\" onclick=\"undisable(".$no.")\" ><input type=text class=myinputtext style=widht:150px maxlength=150 id=krksiText_".$no." name=krksiText_".$no." value='".$rKrasi['catatanrelease']."' ".$isi." /> <button class=\"mybutton\" id=btnSave_".$no." name=btnSave_".$no." onclick=\"saveKoreksi(".$no.")\" ".$isi." )\"  >".$_SESSION['lang']['save']."</button></td>   
                        <td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";
                                    } 
                                    elseif($bar['stat_release']==1) 
                                    {
                                            echo"<td align=center>".$yrs['namakaryawan']."</td><td >&nbsp;</td><td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";
                                    }
                                } 
                                else 
                                {
					echo"<td align=left colspan=2>".$yrs['namakaryawan']."</td>";
					echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";
                                }
				
				if(($bar['statuspo']>1)) 
                                {
                                    if(($bar['stat_release']=='1')) 
                                    {
                                        $disbled="<td align=center>".tanggalnormal($bar['tglrelease'])."</td>";
                                    } 
                                    else 
                                    {
                                        $disbled="<td><button class=mybutton onclick=\"release_po('".$bar['nopo']."')\" >
                                                ".$_SESSION['lang']['release_po']."</button>&nbsp;
                                                <!--<img src=images/onebit_33.png class=resicon  title='".$_SESSION['lang']['ditolak']."' onclick=\"get_data_po('".$bar['nopo']."');\" style=\"vertical-align:middle;\">--></td>";
                                    }
					
                                    
                                    if(($bar['stat_release']=='0')&&($bar['useridreleasae']=='0000000000') && $countPO <= 0) 
                                    { 
                                        //$disbled2="<td align=center>".$_SESSION['lang']['un_release_po']." asd</td>";
                                        $disbled2="<td><button class=mybutton disabled >".$_SESSION['lang']['un_release_po']."</button></td>";
                                    
                                    } 
                                    else 
                                    {
                                        if($bar['tglrelease']==$this_date && $countPO <= 0 && $bar['closed']==0) 
                                        {	
                                            $disbled2="<td><button class=mybutton onclick=\"un_release_po('".$bar['nopo']."','".$this_date."') \">".$_SESSION['lang']['un_release_po']."</button></td>";
                                        } 
                                        else 
                                        {
                                            $disbled2="<td><button class=mybutton disabled >".$_SESSION['lang']['un_release_po']."</button></td>";
                                        }
                                        
                                        
                                        
				    }
                                    $disbled2.="<td><button class=mybutton  ".$disbtn." onclick=closeedPo('".$_SESSION['lang']['tutup']."','".$bar['nopo']."',event)>".$_SESSION['lang']['tutup']."</button></td>";
					
                                    
                                    ?>
                            
					<?php echo $disbled; echo $disbled2; ?>
				<?php } else {?>
				<td colspan="2" align="center"><?php echo $_SESSION['lang']['wait_approval']?></td>
				<?
				}
				echo"</tr><input type=hidden id=nopo_".$no." name=nopo_".$no." value='".$bar['nopo']."' />";
			}
			
			// Back Button
			if($page==0) {
				$disBack = " disabled ";
			} else {
				$disBack = "";
			}
			
			// Next Button
			if($page>=$maxPage) {
				$disNext = " disabled ";
			} else {
				$disNext = "";
			}
			
			echo" <tr><td colspan=9 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton ".$disBack." onclick=cariPage(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton ".$disNext." onclick=cariPage(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";   	
		} else {
			echo " Gagal,".(mysql_error($conn));
		}
		
		break;
	
	/*case 'cari_rpo' :
		//echo "warning:masuk";exit();
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
            $add="";
            if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
            {
                $add=" and lokalpusat=1 ";
            }
			if((isset($_POST['txtSearchrpo']))||(isset($_POST['tglCarirpo'])))
			{
			$txt_search=$_POST['txtSearchrpo'];
			//$txt_tgl=str_replace('-','',$_POST['tglCari']);
			$txt_tgl=tanggalsystem($_POST['tglCarirpo']);
			//$txt_tgl=$txt_tgl);
			$txt_tgl_a=substr($txt_tgl,0,4);
			$txt_tgl_b=substr($txt_tgl,4,2);
			$txt_tgl_c=substr($txt_tgl,6,2);
			$txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
			//echo $txt_tgl_a;
			}
			else
			{
			$txt_search='';
			$txt_tgl='';			
			}//			
			
			if($txt_search!='')
			{
			$where=" and nopo LIKE  '%".$txt_search."%' ";
			
			}
			elseif($txt_tgl!='')
			{
			$where=" and tanggal LIKE '%".$txt_tgl."%' ";
			
			}
			//elseif(($txt_tgl!='')&&($txt_search!=''))
//			{
//			$where="  nopo LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'"; 
//			}//
			
			
			$strx="select * from ".$dbname.".log_poht where statuspo>1 ".$add."  ".$where."order by tanggal desc";
		
			//echo "warning :".$strx;exit();
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where statuspo>1 ".$add."  ".$where."order by tanggal desc";
		$query2=mysql_query($sql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
			if($res=mysql_query($strx))
			{
				$numrows=mysql_num_rows($res);
				if($numrows<1)
				{
					echo"<tr class=rowcontent><td colspan=9>Not Found</td></tr>";
				}
				else
				{
					while($bar=mysql_fetch_assoc($res))
					{
						$kodeorg=$bar['kodeorg'];
						$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
						$rep=mysql_query($spr) or die(mysql_error($conn));
						$bas=mysql_fetch_object($rep);
                                                //ambil catatan release untuk menginput informasi koreksi
                                               $sKrsi="select catatanrelease from ".$dbname.".log_poht where nopo='".$bar['nopo']."'";
                                               $qKrsi=mysql_query($sKrsi) or die(mysql_error($conn));
                                               $rKrasi=mysql_fetch_assoc($qKrsi);

                                               //mengambil namakaryawan untuk informasi signature(penanda tangan)
                                                $sql="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar['persetujuan1']."'";
                                                $query=mysql_query($sql) or die(mysql_error());
                                                $yrs=mysql_fetch_assoc($query);
						$no+=1;
						echo"<tr id='tr_".$no."' ".($bar['stat_release']==2?"bgcolor='orange'":"class=rowcontent")." >
						<td>".$no."</td>
						<td id=td_".$no.">".$bar['nopo']."</td>
						<td>".tanggalnormal($bar['tanggal'])."</td>
						<td>".$kodeorg."</td>";                            
						
						if($rKrasi['catatanrelease']!='')
						{ $isi=" disabled";}
						else
						{ $isi="";}
						if($_SESSION['empl']['tipelokasitugas']!='KANWIL')
                                                {
						
						echo"<td align=left>".$yrs['namakaryawan']."</td>
						<td align=center valign=\"middle\" onclick=\"undisable(".$no.")\" ><input type=text class=myinputtext style=widht:150px maxlength=150 id=krksiText_".$no." name=krksiText_".$no." value='".$rKrasi['catatanrelease']."' ".$isi." /> <button class=\"mybutton\" id=btnSave_".$no." name=btnSave_".$no." onclick=\"saveKoreksi(".$no.")\" ".$isi." )\"  >".$_SESSION['lang']['save']."</button></td>   
						<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";
                                                }
                                                else
                                                {
                                                    echo"<td align=left colspan=2>".$yrs['namakaryawan']."</td>";
                                                    echo"<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";
                                                }
						  if(($bar['statuspo']>1))
                                            {
					  if(($bar['stat_release']=='1')&&($bar['useridreleasae']!='0000000000'))
					 { 	$disbled="<td align=center>".tanggalnormal($bar['tglrelease'])."</td>";}
					  else
					  {	$disbled="<td><button class=mybutton onclick=\"release_po('".$bar['nopo']."')\" >".$_SESSION['lang']['release_po']."</button></td>";}
					if(($bar['stat_release']=='0')&&($bar['useridreleasae']=='0000000000'))
					  { 
					 	 $disbled2="<td>".$_SESSION['lang']['un_release_po']."</td>";
					  }
					  else
					  {	if($bar['tglrelease']==$this_date)
					  	{	
					  		$disbled2="<td><button class=mybutton onclick=\"un_release_po('".$bar['nopo']."','".$this_date."') \">".$_SESSION['lang']['un_release_po']."</button></td>";
						}
						else
						{
							$disbled2="<td><button class=mybutton disabled \">".$_SESSION['lang']['un_release_po']."</button></td>";
						}
					  }
					  ?>
					<?php echo $disbled; echo $disbled2; ?>
				 <?php } else {?>
				 <td colspan="2" align="center"><?php echo $_SESSION['lang']['wait_approval']?></td>
				 
				 <?
				 }
						echo"</tr><input type=hidden id=nopo_".$no." name=nopo_".$no." value='".$bar['nopo']."' />";
					}//while
					echo" <tr><td colspan=9 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariPage(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariPage(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";  
				 }//else
				
			}//	
			else
			{
			echo "Gagal,".(mysql_error($conn));
			}	
		break;*/
		case'getFormTolak':
		echo"<br /><div id=rejected_form>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo." class=myinputtext  style=\"width:150px;\" maxlength=\"50\" /></legend>
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		Apakah Anda Akan Menolak No.PO Di Atas </td></tr>
		<tr><td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event)\" id=ket name=ket style=\"width:150px;\" /></td></tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=tolakPo() >".$_SESSION['lang']['yes']."</button>
		<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button>
		</td></tr></table>
		
		</fieldset>
		</div>
		<input type=hidden name=method id=method  /> 
		<input type=hidden name=user_id id=user_id value=".$user_id." />
		<input type=hidden name=nopo id=nopo value=".$nopo."  />
		";
		break;
            
            
		case'tolakPo':
		if($ket=="")
		{
			echo"warning:Keterangan Tidak Boleh Kosong";
			exit();
		}
		$sUp="update ".$dbname.".log_poht set hasilpersetujuan2='2',persetujuan2='".$user_id."',tglp2='".$this_date."',keterangan='".$ket."',stat_release='1', useridreleasae='".$user_id."',tglrelease='".$this_date."', tanggal='".$this_date."' where nopo='".$nopo."'";
		//echo"warning:".$sUp;exit();
		if($res=mysql_query($sUp))
		echo"";
		else
		echo $sUp."Gagal,".(mysql_error($conn));
		break;
		case'insertKoreksi':
		$sUpd="update ".$dbname.".log_poht set catatanrelease='".$texkKrsi."',stat_release='2' where nopo='".$nopo."'";
		if(!mysql_query($sUpd))
		{
			echo $sUpd."Gagal,".(mysql_error($conn));
		}
		break;
	
                
                case'closeForm':
                    $aarpil=array("0"=>"Tutup PO","1"=>"Outstanding PO");
                    foreach($aarpil as $lstPil=>$disPil){
                        $optPil.="<option value='".$lstPil."'>".$disPil."</option>";
                    }
                    $tab.="<script language=JavaScript1.2 src=js/generic.js></script>
                           <script type=\"text/javascript\" src=\"js/log_release_po.js\"></script>";
                    $tab.="<link rel=stylesheet type=text/css href=style/generic.css>";
                    $tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend><table cellpadding=1 cellspacing=1>";
                    $tab.="<tr><td>".$_SESSION['lang']['pilih']."</td><td><select id=pilId style=width:150px>".$optPil."</select></td></tr>";
                    $tab.="<tr><td>".$_SESSION['lang']['keterangan']."</td><td><input type=text id=ketClose style=width:150px class=myinputtext></td></tr>";
                    $tab.="<tr><td colspan=2><button class=mybutton onclick=tutpDt('".$_POST['nopo']."')>".$_SESSION['lang']['tutup']."</button></td></tr></table></fieldset>";
                    echo $tab;
                break;
               case'tutupData':
                   
                   
                
                   
                   $isiketerangan=$_POST['ketClose'].' ,tanggal tutup : '.date('d-m-Y');
                   
                    if($_POST['pilDt']==0){//tutup 
                        if($_POST['ketClose']==''){
                            exit("error: ".$_SESSION['lang']['keterangan']." can't empty");
                        }
                        $sdata="select kodebarang,nopp,jumlahpesan from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
                        $qdata=mysql_query($sdata) or die(mysql_error($conn));
                        while($rdata=  mysql_fetch_assoc($qdata)){
                            $sup="update ".$dbname.".log_prapodt set status=1,ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$_POST['ketClose']."'
                                  where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."'";
                            if(!mysql_query($sup)){
                                exit("error:db error ".mysql_error($conn)."___".$sup);
                            }
                        }
                        $supdate="update ".$dbname.".log_poht set closed=1,keterangan='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$_POST['nopo']."'";
                        if(!mysql_query($supdate)){
                                exit("error:db error ".mysql_error($conn)."___".$supdate);
                        }
                    }else{
                        $subTotal=0;
                        $sdata="select kodebarang,nopp,jumlahpesan,hargasbldiskon from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
                        $qdata=mysql_query($sdata) or die(mysql_error($conn));
                        while($rdata=  mysql_fetch_assoc($qdata)){
                            $hitung=0;
                            $sjmlhgdng="select distinct sum(jumlah) as jmlh from ".$dbname.".log_transaksi_vw 
                                        where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."' and tipetransaksi=1";
						    
                            $qjmlhgdng=mysql_query($sjmlhgdng) or die(mysql_error($conn));
                            $angkPengurang=0;
                           
                                    $rjmlgdng=mysql_fetch_assoc($qjmlhgdng);
                                    if(($rjmlgdng['jmlh']=='')||intval($rjmlgdng['jmlh'])==0){
                                            $angkPengurang=0;
                                    }else{
                                            $angkPengurang=$rjmlgdng['jmlh'];
                                    }
                            
                            //$hitung=$rdata['jumlahpesan']-$angkPengurang;
                            
                            if($angkPengurang!=''){
                                $jmlclose=$rdata['jumlahpesan']-$angkPengurang;
                                $hitung=$rdata['jumlahpesan']-$jmlclose;
                                
                            }else{
                                $jmlclose=$rdata['jumlahpesan'];
                                $hitung=0;  
                            }
                            /*if($hitung==0){
                                $hitung=$rdata['jumlahpesan'];
                                $jmlclose=0;
                            }else{
                                //exit("error:".$rdata['kodebarang']."__masuk sini".$rdata['jumlahpesan']."__".$angkPengurang);
                                $jmlclose=$hitung;
                                $hitung=0;  
                            }*/
                            $subTotal+=$hitung*$rdata['hargasbldiskon'];
                                $supdate="update ".$dbname.".log_podt set jmlhstlhclose='".$jmlclose."',jumlahpesan='".$hitung."'
                                where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."'";
                                 if(!mysql_query($supdate)){
                                        exit("error:db error ".mysql_error($conn)."___".$supdate);
                                 }else{
                                     $sup="update ".$dbname.".log_prapodt set create_po=0,status=0 where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."' ";
                                     if(!mysql_query($sup)){
                                         xit("error:db error ".mysql_error($conn)."___".$sup);
                                     }
                                 }
                            
                        }
                        $sHt="select distinct diskonpersen,subtotal,nilaidiskon,ppn from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
                        $qHt=  mysql_query($sHt) or die(mysql_error($conn));
                        $rHt=  mysql_fetch_assoc($qHt);
                        
                        $persenPPn=($rHt['ppn']/(($rHt['subtotal'])-$rHt['nilaidiskon']))*100;
                        
                        @$nilDis=($subTotal*$rHt['diskonpersen'])/100;
                        @$ppn=((($subTotal)-$nilDis)*intval($persenPPn))/100;
                        $nilTotal=(($subTotal)-$nilDis)+$rHt['ongkirimppn']+$rHt['ongkosangkutan']+$ppn;
                        //$isiketerangan='';
                        
                        $supdateht="update ".$dbname.".log_poht set "
                                . "closed=1,keteranganclose='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."',nilaidiskon='".$nilDis."' "
                                . ",subtotal='".$subTotal."',ppn='".$ppn."',nilaipo='".$nilTotal."' "
                                . "where nopo='".$_POST['nopo']."'";
                        
                        if(!mysql_query($supdateht)){
                                exit("error:db error ".mysql_error($conn)."___".$supdateht);
                        }
                    }
                break;
	default:
	break;
	}
	    