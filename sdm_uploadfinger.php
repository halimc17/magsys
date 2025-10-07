<?
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script>
	function submitFile(){
		if(confirm('Are you sure..?')){
			document.getElementById('frm').submit();
		}
	}
	function loadlabel(txt){
		document.getElementById('keterangan').value=txt;
		//alert(txt);
	}
</script>
<?
	include('master_mainMenu.php');
	OPEN_BOX();
	echo"<fieldset><legend>Form</legend>
			<div id=uForm>
				<span id=sample><b>".$_SESSION['lang']['absensi']." Finger Print Uploader.</b></span><br><br>
				<form id=frm name=frm enctype=multipart/form-data method=post action=sdm_uploadfinger_slave.php target=frame>	
					<input type=hidden name=jenisdata id=jenisdata value='ABSENSI'>
					<input type=hidden name=MAX_FILE_SIZE value=8096000>File:
					<input name=filex type=file id=filex size=25 class=mybutton>
					Mulai Tanggal : 
					<input type='text' class='myinputtext' id='tanggal1' name='tanggal1' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\"> 
					sd 
					<input type='text' class='myinputtext' id='tanggal2' name='tanggal2' size='10' onmousemove=setCalendar(this.id) onkeypress=\"return false;\">
					<input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
					<br><br><input type=text id='keterangan' name='keterangan' value='' size=84 disabled>
				</form>
				<iframe frameborder=0 width=800px height=200px name=frame></iframe>
			</div>
		</fieldset>";
	CLOSE_BOX();
	echo close_body();
?>
