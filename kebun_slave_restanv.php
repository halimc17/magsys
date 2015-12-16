<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$divisi = checkPostGet('divisi', '');
$method = checkPostGet('method', '');
$per = checkPostGet('per', '');
$jjg = checkPostGet('jjg', '');
$blok = checkPostGet('blok', '');

$persch = checkPostGet('persch', '');
$divisisch = checkPostGet('divisisch', '');

switch ($method) 
{
    
    case'detail':
     
        #bentuk data awal
		$str="select * from ".$dbname.".setup_blok where kodeorg like '".$divisi."%'";
		$res=mysql_query($str) or die (mysql_error($conn));
		$row=mysql_num_rows($res);
		while($bar=mysql_fetch_assoc($res))
		{
			$kdblok[$bar['kodeorg']]=$bar['kodeorg'];
			$luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
			$pokok[$bar['kodeorg']]=$bar['jumlahpokok'];
			$tt[$bar['kodeorg']]=$bar['tahuntanam'];
		}
		
		
		$str="select * from ".$dbname.".kebun_restanv where blok like '".$divisi."%' and periode='".$per."' ";		
		$res=mysql_query($str) or die (mysql_error($conn));
		while($bar=mysql_fetch_assoc($res))
		{
			$jjg[$bar['blok']]=$bar['jjg'];
		}
		
	   $stream="";
        $stream.="<link rel=stylesheet type=text/css href=style/generic.css>";
        $stream.="<fieldset><legend>Detail Per Blok</legend>";
		$stream.="
			<table border=0 class=sortable  cellspacing=1>
				<thead>
					<tr class=rowheader>
						<td align=center rowspan=4 width=30px >".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=4 width=100px >".$_SESSION['lang']['blok']."</td> 
						<td align=center rowspan=4 width=90px >".$_SESSION['lang']['tahuntanam']."</td>						
						<td align=center rowspan=4 width=60px >".$_SESSION['lang']['luas']."</td>
						<td align=center rowspan=4 width=70px >".$_SESSION['lang']['pokok']."</td> 
						<td align=center rowspan=4 width=70px >".$_SESSION['lang']['jjg']."</td> 
					</tr>
				</thead>";
		
      
		foreach($kdblok as $blok)
		{
			$no+=1;
			$stream.="<tr class=rowcontent>";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center>".$blok."</td>";
			$stream.="<td align=center>".$tt[$blok]."</td>";
			$stream.="<td align=right>".$luas[$blok]."</td>";
			$stream.="<td align=right>".@number_format($pokok[$blok])."</td>";
			$stream.="<td align=right>".$jjg[$blok]."</td>";			
			$stream.="</tr>";
		}
		$stream.="</fieldset>";
		
        echo $stream;
        
    break;
    
    case'posting':
        $str="update ".$dbname.".kebun_restanv set posting=1 where "
                . " blok like '".$divisi."%' and periode='".$per."' ";
        if(!mysql_query($str))
        { 
            echo " Gagal,".addslashes(mysql_error($conn));
        }    
        
    break;
    
    case'delete':
        $str="delete from ".$dbname.".kebun_restanv where "
                . " blok like '".$divisi."%' and periode='".$per."' ";
        if(!mysql_query($str))
        { 
            echo " Gagal,".addslashes(mysql_error($conn));
        }    
        
    break;
    
    
    case'loaddata':
		
        $where.=" and substr(blok,1,4) ='".$_SESSION['empl']['lokasitugas']."' ";	
        if($persch!='')
        {
                $where.=" and periode='".$persch."' ";
        }
       
        if($divisisch!='')
        {
            $where.=" and substr(blok,1,6) = '".$divisisch."' ";	
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
        $maxdisplay=($page*$limit);

        $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_restanv where 1=1 ".$where."
				group by substr(blok,1,4),substr(blok,1,6),periode ";
		$query2=mysql_query($ql2) or die(mysql_error());
		$jlhbrs=mysql_num_rows($query2);
       
        $no=0;
        $str=" select sum(jjg) as jjg,substr(blok,1,6) as divisi,periode,posting 
				from ".$dbname.".kebun_restanv where 1=1 ".$where." group by substr(blok,1,6),periode order by periode desc,divisi asc limit ".$offset.",".$limit."";
        $tab="";
        $res=mysql_query($str) or die(mysql_error($conn));
        $no=$maxdisplay;
        while($bar=mysql_fetch_assoc($res))
        {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['periode']."</td>";
			$tab.="<td align=center>".$bar['divisi']."</td>";
            $tab.="<td align=center>".$bar['jjg']."</td>";
            if($bar['posting']==1)
            {
                $tab.="
                <td align=center>
                    <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                         onclick=\"detail('".$bar['periode']."','".$bar['divisi']."','event');\">           
                    <img src=images/skyblue/posted.png class=zImgOffBtn title='Posting');\">           
                </td>
                ";
            }
            else
            {
                $tab.="
                <td align=center>
                    <img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                         onclick=\"edit('".$bar['periode']."','".$bar['divisi']."');\">
                    <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                         onclick=\"del('".$bar['periode']."','".$bar['divisi']."');\">
                    <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                         onclick=\"detail('".$bar['periode']."','".$bar['divisi']."','event');\">         
                    <img src=images/skyblue/posting.png class=zImgBtn title='Posting' 
                         onclick=\"posting('".$bar['periode']."','".$bar['divisi']."');\">           
                </td>
                ";
            }
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=40 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
	
	case'savedata':
	
            if($jjg=='')
            {
                $jjg=0;
            }
			
			//exit("Error:$blok._.$per._.$jjg");
			
			##hapus dulu
			##kodeorg, kodeblok, tahun, bulan
			$str="delete from ".$dbname.".kebun_restanv where blok='".$blok."' and periode='".$per."' ";
			if(!mysql_query($str))
			{ 
				echo " Gagal,".addslashes(mysql_error($conn));
			}

			if($jjg==0)
			{
			}
			else
			{
				$str=" INSERT INTO ".$dbname.".`kebun_restanv` (`periode`, `blok`, `jjg`, `updateby`)
							values ('".$per."','".$blok."','".$jjg."','".$_SESSION['standard']['userid']."')";
				if(!mysql_query($str))
				{
					 echo " Gagal,".addslashes(mysql_error($conn));
				}
			}    
			
	break;
	
	
	
	case'detailinput':// class=rowheader
	
          
		##palidasi oentoek poesting
		$str="select * from ".$dbname.".kebun_restanv where blok like '".$divisi."%' and periode='".$per."' and posting=1 ";        
		$res=mysql_query($str) or die (mysql_error($conn));
		$rowposting=mysql_num_rows($res);
		if($rowposting>1)
		{
			exit("Warning : Data sudah pernah di-input dan di posting.");
		}
	
	
		#bentuk data awal
		$str="select * from ".$dbname.".setup_blok where kodeorg like '".$divisi."%'";
		$res=mysql_query($str) or die (mysql_error($conn));
		$row=mysql_num_rows($res);
		while($bar=mysql_fetch_assoc($res))
		{
			$kdblok[$bar['kodeorg']]=$bar['kodeorg'];
			$luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
			$pokok[$bar['kodeorg']]=$bar['jumlahpokok'];
			$tt[$bar['kodeorg']]=$bar['tahuntanam'];
		}
		
		
		$str="select * from ".$dbname.".kebun_restanv where blok like '".$divisi."%' and periode='".$per."' ";		
		$res=mysql_query($str) or die (mysql_error($conn));
		while($bar=mysql_fetch_assoc($res))
		{
			$jjg[$bar['blok']]=$bar['jjg'];
		}
		
		
		$stream="";
		$stream.="<fieldset><legend>Detail Input</legend>";
		$stream.="<button class=mybutton onclick=saveall(".$row.");>".$_SESSION['lang']['proses']."</button>";
		$stream.="
			<table border=0  cellspacing=1 >
				<thead>
					<tr>
						<td align=center rowspan=4 width=30px >".$_SESSION['lang']['nourut']."</td>    
						<td align=center rowspan=4 width=100px >".$_SESSION['lang']['blok']."</td> 
						<td align=center rowspan=4 width=90px >".$_SESSION['lang']['tahuntanam']."</td>						
						<td align=center rowspan=4 width=60px >".$_SESSION['lang']['luas']."</td>
						<td align=center rowspan=4 width=70px >".$_SESSION['lang']['pokok']."</td> 
						<td align=center rowspan=4 width=70px >".$_SESSION['lang']['jjg']."</td> 
					</tr>
				</thead>";
		
      
		foreach($kdblok as $blok)
		{
			$no+=1;
			$stream.="<tr class=rowcontent id=row".$no.">";
			$stream.="<td align=center>".$no."</td>";
			$stream.="<td align=center id=blok".$no.">".$blok."</td>";
			$stream.="<td align=center id=luas".$no.">".$tt[$blok]."</td>";
			$stream.="<td align=right id=luas".$no.">".$luas[$blok]."</td>";
			$stream.="<td align=right id=pokok".$no.">".@number_format($pokok[$blok])."</td>";
			$stream.="<td align=right><input type=text value='".@$jjg[$blok]."' id=jjg".$no." onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:70px;\"></td>";			
			$stream.="</tr>";
		}
		$stream.="</fieldset>";
		echo $stream;
		
	break;
	
}

?>