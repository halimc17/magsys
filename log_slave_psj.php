<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

	
$method=$_POST['method'];	
$notran=isset($_POST['notran'])? $_POST['notran']: '';
$txt=	isset($_POST['txt'])? $_POST['txt']: '';
$hrini=date('Ymd');
$jumlahditerima=isset($_POST['jumlahditerima'])? $_POST['jumlahditerima']: '';
$kodebarang=	isset($_POST['kodebarang'])? $_POST['kodebarang']: '';



$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$optKend = makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc',"kelompokvhc='KD'");
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');




switch($method)
{
	case'posting':
		$sekarang=date('Y-m-d');
		$i="update  ".$dbname.".log_suratjalanht set postingpenerimaan=1,penerima='".$_SESSION['empl']['name']."',tanggaltiba='".$sekarang."' where nosj='".$notran."'";
		//exit("Error:$i");
		if(mysql_query($i))
		{
		}
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;
		
		
	case'savePenerimaan':
	
		$i="update ".$dbname.".`log_suratjalandt`  set jumlahditerima='".$jumlahditerima."' where nosj='".$notran."' and kodebarang='".$kodebarang."'";
	//exit("Error:$i");	
		if(mysql_query($i))
		{
		}
		else
		{
                    echo " Gagal,".addslashes(mysql_error($conn));
		}
		echo $notran;
	break;
	
	case'getIsi':
		echo"
			
                    <fieldset style=float:left><legend>Posting</legend>
                    <button class=mybutton onclick=posting('".$notran."')>".$_SESSION['lang']['posting']."</button>
                    </fieldset>
                    <br />
                    <fieldset style=float:left><legend>".$_SESSION['lang']['list']."</legend>
                    <table cellspacing=1 border=0 class='sortable'>
                    <thead>
                        <tr class=rowheader>
                            <td align=center>".$_SESSION['lang']['nourut']."</td>
                            <td align=center>".$_SESSION['lang']['nosj']."</td>
                            <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                            <td align=center>".$_SESSION['lang']['namabarang']."</td>
                            <td align=center>".$_SESSION['lang']['sumber']."</td>
                            <td align=center>".$_SESSION['lang']['jumlah']."</td>
                            <td align=center>QTY ".$_SESSION['lang']['diterima']."</td>
                            <td align=center>".$_SESSION['lang']['satuan']."</td>
                            <td align=center>".$_SESSION['lang']['save']."</td>
                        </tr>
		</thead>
		</tbody>";
	
		$i="select * from ".$dbname.".log_suratjalandt where nosj='".$notran."'";	
		$n=mysql_query($i) or die (mysql_error($conn));
		$no=0;
		while ($d=mysql_fetch_assoc($n))
		{
			$str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$d['kodebarang']."'";
                                                            $res=mysql_query($str);
                                                            while($bare=mysql_fetch_object($res)){
                                                                $nmBarang[$bare->kodebarang]=$bare->namabarang;
                                                            }
                                                            $no+=1;
			echo"<tr class=rowcontent>
				<td>".$no."</td>
				<td>".$d['nosj']."</td>
				<td align=right>".$d['kodebarang']."</td>
				<td>".(isset($nmBarang[$d['kodebarang']])? $nmBarang[$d['kodebarang']]: '')."</td>
				<td>".$d['jenis']."</td>
				<td id=jumlah".$no.">".$d['jumlah']."</td>
				<td><input type=text id=jumlahditerima".$no." value='".$d['jumlahditerima']."' onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"></td>
				<td>".$d['satuanpo']."</td>
				<td><img src=images/icons/Grey/PNG/save.png class=resicon  title='update' onclick=\"savePenerimaan('".$d['nosj']."','".$d['kodebarang']."',".$no.");\" ></td>

			</tr>
		";
		}
		echo"</fieldset>";		
	break;
	

	case'loadData'://<table class=sortable cellspacing=1 border=2px style=\"border-collapse:collapse\" cellpadding=5px>

		echo"
		
		<table cellspacing='1' border='0' class='sortable'>
		
			<thead>
				<tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['nosj']."</td>
					<td align=center>".$_SESSION['lang']['kodept']."</td>
                                        <td align=center>".$_SESSION['lang']['unit']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
                                        <td align=center>".$_SESSION['lang']['tanggalkirim']."</td>
                                            
					<td align=center>".$_SESSION['lang']['expeditor']."</td>
					<td align=center>".$_SESSION['lang']['jeniskend']."</td>
					<td align=center>".$_SESSION['lang']['nopol']."</td>
					<td align=center>Driver</td>
                                            
					<td align=center>".$_SESSION['lang']['pengirim']."</td>
					<td align=center>".$_SESSION['lang']['action']."</td>

				</tr>
			</thead>
		<tbody>";
		
		$limit=30;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		if($txt!='')
			$txt="and nosj like '%".$txt."%'";
		else
			$txt="";
			
		$ql2="select count(*) as jmlhrow from ".$dbname.".log_suratjalanht where posting='1'  ".$txt."   order by tanggal desc";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		
		$ha="SELECT * FROM ".$dbname.".log_suratjalanht where posting='1' ".$txt." order by tanggal desc  limit ".$offset.",".$limit."";
		//echo $ha;
		$hi=mysql_query($ha) or die(mysql_error());
		$no=$maxdisplay;
		while($hu=mysql_fetch_assoc($hi))
		{
			$no+=1;//<td>".$nmFranco[$hu['franco']]."</td>
			echo"
			<tr class=rowcontent>
				<td>".$no."</td>
				<td>".$hu['nosj']."</td>
				<td>".$hu['kodept']."</td>
				<td>".$hu['kodeorg']."</td>
				<td>".tanggalnormal($hu['tanggal'])."</td>
				<td>".tanggalnormal($hu['tanggalkirim'])."</td>
				<td>".$nmSup[$hu['expeditor']]."</td>
                                <td>".$optKend[$hu['jeniskend']]."</td>
				<td>".$hu['nopol']."</td>
                                <td>".$hu['driver']."</td>
                                <td>".$nmKar[$hu['pengirim']]."</td>";  
				if($hu['postingpenerimaan']=='0')
				{
                                    $post="<td align=center><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=a class=resicon onclick=listBarang('".$hu['nosj']."','".$_SESSION['lang']['find']."',event)></td>";		
				}
				else
				{
                                    $post="<td align=center>".$_SESSION['lang']['posted']."  ".$hu['penerima']."</td>";
				}	
				echo $post;	
			echo"</tr>";
		}
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
    break;
	

	
	
	

	
	default;	
}

?>