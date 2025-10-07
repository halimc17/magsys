<?php
require_once('master_validation.php');
require_once('config/connection.php');

$kd_bag=$_POST['rkd_bag'];
if((isset($_POST['txtfind3']))!='')
{
    $txtfind=$_POST['txtfind3'];
    $str="select * from ".$dbname.".vhc_5master where kodevhc like '%".$txtfind."%' ";
    if($res=mysql_query($str))
    {
        echo"
        <fieldset>
        <legend>Result</legend>
        <div style=\"overflow:auto; height:300px;\" >
        <table class=data cellspacing=1 cellpadding=2  border=0>
            <thead>
                <tr class=rowheader>
                    <td class=firsttd>No.</td>
                    <td>".$_SESSION['lang']['kodevhc']."</td>
                    <td>".$_SESSION['lang']['kodeorg']."</td>
                    <td>".$_SESSION['lang']['jenisvch']."</td>
                    </tr>
                    </thead>
                    <tbody>";
                    //			$no=0;	 
                    while($bar=mysql_fetch_object($res))
                    {
                        echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setVhc('".$bar->kodevhc."')\" title='Click' >
                            <td class=firsttd>".$no."</td>
                            <td>".$bar->kodevhc."</td>
                            <td>".$bar->kodeorg."</td>
                            <td>".$bar->jenisvhc."</td>
                            </tr>";
                    }	 
                    echo "</tbody>
                    <tfoot>
                    </tfoot>
                    </table></div></fieldset>";
    }	
    else
    {
        echo " Gagal,".addslashes(mysql_error($conn));
    }	
}
?>