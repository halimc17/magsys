<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodehead=checkPostGet('kodehead','');
$kodeheadedit=checkPostGet('kodeheadedit','');	
$matauangheadedit=checkPostGet('matauangheadedit','');	
$simbolheadedit=checkPostGet('simbolheadedit','');	
$kodeisoheadedit=checkPostGet('kodeisoheadedit','');	

$per=checkPostGet('per','');
$kode=checkPostGet('kode','');
$kodedetail=checkPostGet('kodedetail','');
$matauang=checkPostGet('matauang','');
$simbol=checkPostGet('simbol','');
$kodeiso=checkPostGet('kodeiso','');

$kodedetail=checkPostGet('kodedetail','');
$kodedet=checkPostGet('kodedet','');

$jm=checkPostGet('jm','');
$mn=checkPostGet('mn','');
$jmsavedet=$jm.':'.$mn;
$tgl=tanggalsystem(checkPostGet('tgl',''));
$kursdet=checkPostGet('kursdet','');

$jam=checkPostGet('jam','');
$daritanggal=tanggalsystem(checkPostGet('daritanggal',''));

$kodetambah=checkPostGet('kodetambah','');
$matauangtambah=checkPostGet('matauangtambah','');
$simboltambah=checkPostGet('simboltambah','');
$kodeisotambah=checkPostGet('kodeisotambah','');
$method=checkPostGet('method','');

$optPer="<option selected value=''>".$_SESSION['lang']['all']."</option>";
$iPer = "SELECT distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$nPer = mysql_query($iPer) or die ("SQL ERR : ".mysql_error());
//$optPer="";
while ($dPer=mysql_fetch_assoc($nPer))
{
    if($dPer['periode']==$per)
    {
        $optPer.="<option selected value=".$dPer['periode'].">".$dPer['periode']."</option>";
    }
    else
    {
        $optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
    }
}

##untuk jam dan menit option
$jm="";
for($t=0;$t<24;)
{
	if(strlen($t)<2)
	{
		$t="0".$t;
	}
	$jm.="<option value=".$t." ".($t==00?'selected':'').">".$t."</option>";
	$t++;
}
$mnt="";
for($y=0;$y<60;)
{
	if(strlen($y)<2)
	{
		$y="0".$y;
	}
	$mnt.="<option value=".$y." ".($y==00?'selected':'').">".$y."</option>";
	$y++;
}	
?>

<?php
switch($method)
{
	case 'insert':
		$str="insert into ".$dbname.".setup_matauang (`kode`,`matauang`,`simbol`,`kodeiso`)
		values ('".$kodetambah."','".$matauangtambah."','".$simboltambah."','".$kodeisotambah."')";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;
	//daritanggal	jam	kurs
	case 'simpandetail':
		$str="insert into ".$dbname.".setup_matauangrate (`kode`,`daritanggal`,`jam`,`kurs`)
		values ('".$kodedet."','".$tgl."','".$jmsavedet."','".$kursdet."')";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

	case 'edithead':
		$str="update ".$dbname.".setup_matauang set kode='".$kodeheadedit."',matauang='".$matauangheadedit."',simbol='".$simbolheadedit."',kodeiso='".$kodeisoheadedit."'
				where kode='".$kodehead."' ";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;
	
	
	
	
		
case'loadData':
	if ($kode=='')
	{
		$kode=$kodedetail;
	}
	$perSch="";
	if($per!='')
	{
		$perSch="and daritanggal like '%".$per."%' ";
	}
	
	echo"
		<tr>
			 <td>Periode</td>
			 <td>:</td>
			 <td><select id=per onchange=loadData('".$kode."') style='widht:150px'>".$optPer."</select></td>
		</tr>";  
    echo"
	<table class=sortable cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>Kode</td>
				<td align=center>Tanggal</td>
				<td align=center hidden>Jam</td>
				<td align=center>Kurs</td>
				<td align=center>*</td>
			</tr>
		</thead>
		<tbody>";
		
		$limit=31;
		$page=0;
		if(isset($_POST['page']))
		{
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".setup_matauangrate where kode='".$kode."' ".$perSch." ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		$ha="select * from ".$dbname.". setup_matauangrate where kode='".$kode."' ".$perSch." order by daritanggal desc limit ".$offset.",".$limit."";
		//exit("Error:$ha");
                $hi=mysql_query($ha) or die(mysql_error());
		$no=$maxdisplay;
		while($hu=mysql_fetch_assoc($hi))
		{
		
                    
                    
                    
		$no+=1;
		echo"
		<tr class=rowcontent>
			<td>".$no."</td>
			<td>".$hu['kode']."</td>
			<td>".tanggalnormal($hu['daritanggal'])."</td>
			<td hidden>".$hu['jam']."</td>
			<td align=right>".number_format($hu['kurs'],2)."</td>
			<td>
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldetail('".$hu['kode']."','".tanggalnormal($hu['daritanggal'])."','".$hu['jam']."');\" >
			</td>
		</tr>
		";
		}
		echo"<tr class=rowcontent><td></td>
			<td><input type=text maxlength=3 id=kodedet value=".$kode." disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\"></td>
			<td><input type='text' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:75px; /></td>
			<td hidden><select id=jm>".$jm."</select>:<select id=mn>".$mnt."</select></td>
			<td><input type=text  id=kursdet onkeypress=\"return angka_doang(event);\" class=myinputtext style=\"width:50px;\"></td>
			<td><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpandetail('".$kode."')></td>
		</tr>";	
		echo"</tbody></table>";
    break;

	case 'delhead':
	//exit("Error:hahaha");delhead(kode,matauang,simbol,kodeiso)
		$str="delete from ".$dbname.".setup_matauang where kode='".$kode."' and matauang='".$matauang."' and simbol='".$simbol."' and kodeiso='".$kodeiso."'";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;
	
	case 'deldetail':
	//exit("Error:hahaha");delhead(kode,matauang,simbol,kodeiso)
		$str="delete from ".$dbname.".setup_matauangrate where kode='".$kode."' and daritanggal='".$daritanggal."' and jam='".$jam."'";
		//exit("Error.$str");
		if(mysql_query($str))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));
	break;

default:
}
?>
