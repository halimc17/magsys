<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method			=$_POST['method'];

switch($method)
{
    
    case'goCariGudang':
            echo"
                    <table cellspacing=1 border=0 class=data>
                    <thead>
                            <tr class=rowheader>
                                    <td>No</td>
                                    <td>".$_SESSION['lang']['nopo']."</td>
                                        <td>".$_SESSION['lang']['keterangan']."</td>

                            </tr>
            </thead>
            </tbody>";

            if($_POST['tipedoc']=='po')
            {
                $i="select * from ".$dbname.".log_poht where nopo like '%".$_POST['noGudang']."%' ";
                $n=mysql_query($i) or die (mysql_error($conn));
                while ($d=mysql_fetch_assoc($n))
                {
                    $no+=1;
                     echo"
                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickGudang('".$d['nopo']."')>
                            <td>".$no."</td>
                            <td>".$d['nopo']."</td>
                                <td>".$d['keterangan']."</td>
                    </tr>
                ";
                }
            }
            else
            {
                $i="select * from ".$dbname.".log_spkht where notransaksi like '%".$_POST['noGudang']."%' ";
                $n=mysql_query($i) or die (mysql_error($conn));
                while ($d=mysql_fetch_assoc($n))
                {
                    $no+=1;
                     echo"
                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickGudang('".$d['notransaksi']."')>
                            <td>".$no."</td>
                            <td>".$d['notransaksi']."</td>
                                <td>".$d['keterangan']."</td>

                    </tr>
                ";
                } 
            }
                

    break;
    
    
       case'goCariInduk':
            echo"
                    <table cellspacing=1 border=0 class=data>
                    <thead>
                            <tr class=rowheader>
                                    <td>No</td>
                                    <td>".$_SESSION['lang']['kodeasset']."</td>
                                        <td>".$_SESSION['lang']['namaasset']."</td>

                            </tr>
            </thead>
            </tbody>";

            $i="select kodeasset,namasset from ".$dbname.".sdm_daftarasset where kodeasset like '%".$_POST['noInduk']."%'"
                    . " or namasset like '%".$_POST['noInduk']."%' ";
            //echo $i;
            $n=mysql_query($i) or die (mysql_error($conn));
            while ($d=mysql_fetch_assoc($n))
            {
                    $no+=1;
            echo"
                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickInduk('".$d['kodeasset']."')>
                            <td>".$no."</td>
                            <td>".$d['kodeasset']."</td>
                                <td>".$d['namasset']."</td>
                    </tr>
            ";
            }

    break; 
    
    
    
case'getKodeAkhir':
    //exit("Error:Masuk");
    $sPt="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
    $qPt=mysql_query($sPt) or die(mysql_error($conn));
    $rPt=mysql_fetch_assoc($qPt);
    $kpl=$rPt['induk']."-".$_POST['kdAset'].$_POST['sub'];
    //exit("error:$kpl");
    $tppenyusutan=makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,metodepenyusutan');
//    $scek="select distinct kodeasset from ".$dbname.".sdm_daftarasset 
//          where tipeasset='".$_POST['kdAset']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."' order by kodeasset desc limit 0,1";
       $scek="select distinct kodeasset from ".$dbname.".sdm_daftarasset 
          where kodeasset like '".$kpl."%' order by kodeasset desc limit 0,1";
    //exit("Error:".$scek);
    $urut=0;
    $qcek=mysql_query($scek) or die(mysql_error($conn));
    $rcek=mysql_fetch_assoc($qcek);
    if($rcek['kodeasset']!='')
    {
        //if(strlen($_POST['kdAset'])==3)
        //{
        //    $urut=substr($rcek['kodeasset'],-7);
        //}
        //else
        //{
            $urut=substr($rcek['kodeasset'],-6);
        //}
    }
   // exit("Error:".);
$rer=intval($urut);
$kdcrt=$rer+1;
$kdcrt=addZero($kdcrt, 5);
if(strlen($_POST['kdAset'])<3)
{
    $kdcrt=addZero($kdcrt, 6);    
}

$kdasst=$kpl.$kdcrt;
echo $kdasst."#####".$tppenyusutan[$_POST['kdAset']];


break;


case'getSub':
   
    $optSub="<option value=''>".$_SESSION['empl']['pilihdata']."</option>";
    $iSub="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$_POST['tipe']."' ";
    $nSub=  mysql_query($iSub) or die (mysql_error($conn));
    while($dSub=  mysql_fetch_assoc($nSub))
    {
        
        if($_POST['sub']==$dSub['kodesub'])
        {
            $select="selected=selected";
        }
        else
        {
            $select="";
        }
       
        
        $optSub.="<option ".$select." value='".$dSub['kodesub']."'>".$dSub['namasub']."</option>";
    }
    
    echo $optSub;
    break;
    
    
case'getSusut':
   
   
    $iSub="select umurpenyusutan from ".$dbname.".sdm_5subtipeasset where kodetipe='".$_POST['tipe']."' and  kodesub='".$_POST['sub']."' ";
   // exit("Error:$iSub");
    $nSub=  mysql_query($iSub) or die (mysql_error($conn));
    $dSub=  mysql_fetch_assoc($nSub);
    $susut=$dSub['umurpenyusutan'];
    
    
    echo $susut;
    
    break;    



default:
break;					
}
?>
