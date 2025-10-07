<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_payrollHO.js></script>
<link rel=stylesheet type=text/css href=style/payroll.css>
<?
include('master_mainMenu.php');

	OPEN_BOX('','<b>PPn21 COMPONENT</b>');
		echo"<div id=EList>";
		echo OPEN_THEME('Component Gaji yang dikenai PPh21:')."<br>";

$str="select id,name,pph21 from ".$dbname.".sdm_ho_component
      where plus=1 order by id";
$res=mysql_query($str,$conn);

$va="Beri tanda check(V) pada komponen yang kena pajak.
     <table class=sortable cellspacing=1 border=0 width=500px>
      <thead>
	  <tr class=rowheader>
	    <td>ID.</td><td align=center>Nama.Komponen</td><td align=center>Yes/No</td>
	  </tr>	
	  </thead>
	  <tbody>";
while($bar=mysql_fetch_object($res))
{
	if($bar->pph21==1){
		$ch='checked';
		if($bar->id==1)
		{
			$ch.=" disabled";
		}
	}
	else
	    $ch='';
	$va.="<tr class=rowcontent>
	        <td class=firsttd align=center>".$bar->id."</td>
			<td>".$bar->name."</td>
			<td align=center><input type=checkbox id=ch".$bar->id." ".$ch." onclick=savePPh21Component(this,this.value) value=".$bar->id."></td>
	    </tr>"; 
}	  
$va.="</tbody><tfoot></tfoot></table>";	  	  
$hfrm[0]='Komponen Gaji';
$frm[0]="<br>".$va."<br>
		";

drawTab('FRM',$hfrm,$frm,150,600);  	  			 
		echo"</div>";
		echo CLOSE_THEME();		
	CLOSE_BOX();
	echo close_body();	
?>