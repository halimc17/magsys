<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
?>
<body>
    
<?
echo OPEN_THEME('Login History:');
$status_logout=$_SESSION['standard']['logged']==1?"Not LogOut":"Normal";
$x=str_replace("-","",$_SESSION['standard']['lastupdate']);
$mark=mktime(0,0,0,substr($x,4,2),substr($x,6,2),substr($x,0,4));
echo"<table>
	     <tr>
		 <tr><td><u>Last Login</u></td><td>: ".$status_logout."</td></tr>
		 <tr><td><u>Last Login Date</u></td><td>: ".date('l',$mark).",".tanggalnormal(substr($_SESSION['standard']['lastupdate'],0,10))."</td></tr>
		 <tr><td><u>Last Login Time</u></td><td>: ".substr($_SESSION['standard']['lastupdate'],10,9)."</td></tr>
		 <tr><td><u>Last Login IP</u></td><td>: ".$_SESSION['standard']['lastip']."</td></tr>
		 <tr><td><u>Computer Name</u></td><td>: ".$_SESSION['standard']['lastcomp']."</td></tr> 
     </table>";

echo CLOSE_THEME();
?>
    
<div style='position: absolute; top:70px; left:260px; border:orange solid 1px; background-color:#CFE9FA;'>
<iframe frameborder=0 width=250px height=177px name=notifications id=notifications src=login_notifications.php<? echo "?karyawanid=".$_SESSION['standard']['userid'].'&bahasa='.$_SESSION['language'].'&jabatan='.$_SESSION['empl']['kodejabatan'].'&lokasitugas='.$_SESSION['empl']['lokasitugas']; ?>>
</iframe>    
</div>      
    
    
</body>



   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    




    
    
    
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?if(MD5($_SESSION['org']['holding'])!='70f1d810d4bbb35fc7c9f84beaef04eb'){session_destroy();exit();}?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          














































































                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														