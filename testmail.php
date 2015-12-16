 <?php 
require_once('config/connection.php');
include 'lib/nangkoelib.php';
$subject="[Notifikasi] user nonaktif";
                                $body="<html>
                                         <head>
                                         <body>
                                           <dd>Dengan Hormat,</dd><br>
                                           <br>
                                         test                                 <br>
                                           Regards,<br>
                                           Owl-Plantation System.
                                         </body>
                                         </head>
                                       </html>
                                       ";
                                //$to='teddy.s@medcoagro.co.id','nangkoel@gmail.com';
				$to='nangkoel@gmail.com';
				$cc='';
                                //$to='nangkoel@gmail.com';
                               if($to!=''){ 
                                $kirim=kirimEmail($to,$cc,$subject,$body);#this has return but disobeying;     
                               }
echo $kirim;
?>
