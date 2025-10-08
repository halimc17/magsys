<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$periode=date('m');

$str="select namakaryawan,lokasitugas,tanggalmasuk from ".$dbname.".datakaryawan
      where MONTH(tanggalmasuk)=".$periode." and tanggalmasuk not like '".date('Y-m')."%'
      and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') order by lokasitugas,namakaryawan,tanggalmasuk";//0=staff 	
	  
$res=mysql_query($str);
if(mysql_num_rows($res)>0)
{
     $stream="<table>
              <thead>
              <tr>
              <td>No.</td>
              <td>Nama</td>
              <td>Tanggal Masuk</td>
              <td>Lokasi Tugas</td>
              </tr>
              </thead>
              <tbody>
              ";   
     while($bar=mysql_fetch_object($res))
        {
                $no+=1;
                $stream.="<tr><td>".$no."</td>
                        <td>".$bar->namakaryawan."</td>
                        <td>".tanggalnormal($bar->tanggalmasuk)."</td>	
                        <td>".$bar->lokasitugas."</td>  
                      </tr>";
        }
     $stream.="</tbody>
               <tfoot>
               </tfoot>
               </table>"; 
     
  //ambilemail
     $to='';
     $str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='RCUTI'";
     $res=mysql_query($str);
     while($bar=mysql_fetch_object($res))
     {
         $to=trim($bar->nilai);
     }
         
        $subject="[Notifikasi] Hak Cuti karyawan periode ".date('Y-m');
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Berikut ini adalah karyawan yang akan memperoleh cuti baru bulan ini:
                   <br>
                    ".$stream."
                   <br>
                   Regards,<br>
                   Owl-Plantation System.
                 </body>
                 </head>
               </html>
               ";
       if($to!=''){ 
        $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;     
       }
}	
?>
