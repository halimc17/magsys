<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script language=javascript1.2 src='js/log_biayakirim.js'></script>
<?php
include('master_mainMenu.php');
OPEN_BOX();

// Options
$optTrp = makeOption($dbname,'log_5supplier','supplierid,namasupplier',
                     "kodekelompok like 'T%'");

echo"<fieldset style='float:left;'>";
    echo"<legend><b>Biaya Kirim</b></legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
            <tr>
                <td>".$_SESSION['lang']['nodok']."</td>
                <td>:</td>
                <td colspan=2>
                    <input type=text  disabled id=nodok onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\"></td>
                <td>
                <button class=mybutton  id=tmblCariNoDok onclick=tambahDok('".$_SESSION['lang']['find']."',event)>".$_SESSION['lang']['find']."</button>                       
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['kodebarang']."</td> 
                <td>:</td>
                <td>".makeElement('kodebarang','select','',array('style'=>'width:200px','onchange'=>'getGudang()'))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['gudang']."</td> 
                <td>:</td>
                <td>".makeElement('kodegudang','select','',array('style'=>'width:200px'))."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['transporter']."</td> 
                <td>:</td>
                <td>".makeElement('transporter','select','',array('style'=>'width:200px'),$optTrp)."</td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['jumlah']." (Rp)</td> 
                <td>:</td>
                <td colspan=3><input type=jumlah   id=jumlah onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber style=\"width:200px;\"></td>
            </tr>
            <tr><td colspan=2></td>
                    <td colspan=3>
                            <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                            <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                    </td>
            </tr>

        </table></fieldset>
        <input type=hidden id=method value='insert'>";
        
        
        echo"<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['keterangan']."</b></legend>
    <table>
        <tr>
            <td>No. Dok</td>
            <td>:</td>
            <td>Nomor document yang akan dilakukan proses penambahan biaya, gunakan tombol cari</td>
        </tr>
        <tr>
            <td>Kode Barang</td>
            <td>:</td>
            <td>Pemilihan barang yang akan dilakukan proses penambahan biaya</td>
        </tr>
        <tr>
            <td>Gudang</td>
            <td>:</td>
            <td>Gudang Barang diterimakan</td>
        </tr>
        <tr>
            <td>Transporter</td>
            <td>:</td>
            <td>Transporter yang digunakan</td>
        </tr>
        <tr>
            <td>Jumlah</td>
            <td>:</td>
            <td>Jumlah rupiah perbarang didalam dokumen yang akan diproses</td>
        </tr>
    </table></fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
                    ".$_SESSION['lang']['nodok']."<input type=text   id=nodoksch onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:100px;\">
                        <button class=mybutton onclick=cari()>".$_SESSION['lang']['find']."</button>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>