<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src='js/pmn_kontrakjual.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zMaster.js"></script>
<?php
OPEN_BOX('',$_SESSION['lang']['kontrakjual']);

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where `kelompokbarang`='400'";
$qBrg=mysql_query($sBrg) or die(mysql_error());
while($rBrg=mysql_fetch_assoc($qBrg))
{
        $optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$sCust="select kodecustomer,namacustomer  from ".$dbname.".pmn_4customer order by namacustomer";
$qCust=mysql_query($sCust) or die(mysql_error($sCust));
while($rCust=mysql_fetch_assoc($qCust))
{
        $optCust.="<option value=".$rCust['kodecustomer'].">".$rCust['namacustomer']."</option>";
}	
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'"; //echo $sOrg;
$qOrg=mysql_query($sOrg) or die(mysql_error());
while($rOrg=mysql_fetch_assoc($qOrg))
{
        $optPt.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$arrKurs=array("IDR","USD");
foreach($arrKurs as $dt){
        $optKurs.="<option value=".$dt.">".$dt."</option>";
}
#ambil franco
$optByrke=$optTermin=$optFranco="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sFranco="select distinct id_franco,franco_name from ".$dbname.".pmn_5franco order by franco_name asc";
$qFranco=mysql_query($sFranco) or die(mysql_error($conn));
while($rFranco=mysql_fetch_assoc($qFranco)){
	$optFranco.="<option value='".$rFranco['id_franco']."'>".$rFranco['franco_name']."</option>";
}
#termin pembayaran
$sFranco2="select distinct kode from ".$dbname.".pmn_5terminbayar order by kode asc";
$qFranco2=mysql_query($sFranco2) or die(mysql_error($conn));
while($rFranco2=mysql_fetch_assoc($qFranco2)){
	$optTermin.="<option value='".$rFranco2['kode']."'>".$rFranco2['kode']."</option>";
}
$arrStatPPn=array("0"=>"Exclude","1"=>"Include");
$optSat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrStatPPn as $row=>$lstNm){
	$optSat.="<option value='".$row."'>".$lstNm."</option>";
}
$sByr="select * from ".$dbname.".keu_5akunbank order by namabank";
$qbyr=mysql_query($sByr) or die(mysql_error($conn));
while($rByr=mysql_fetch_assoc($qbyr)){
	$optByrke.="<option value='".$rByr['rekening']."'>".$rByr['pemilik'].":".$rByr['namabank']." ".$rByr['rekening']."</option>";
}

$optNoref=$optTtdjual="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTtd="select * from ".$dbname.".pmn_5ttd";
$nTtd=mysql_query($iTtd) or die(mysql_error($conn));
while($dTtd=mysql_fetch_assoc($nTtd)){
	$optTtdjual.="<option value='".$dTtd['nama']."'>".$dTtd['nama']."</option>";
}



//=========================
$frm[0].="
<input type=hidden id=method name=method value='insert' />

          <div class='card mb-3'>
            <div class='card-header bg-primary text-white'>
              <h6 class='mb-0'>".$_SESSION['lang']['header']."</h6>
            </div>
            <div class='card-body'>
              <div class='row g-3'>
                <div class='col-md-4'>
                  <label class='form-label'>".$_SESSION['lang']['NoKontrak']."</label>
                  <input type=text class='form-control form-control-sm' id=noKtrk name=noKtrk maxlength=20 onkeypress=\"return tanpa_kutip(event)\" disabled />
                </div>
                <div class='col-md-4'>
                  <label class='form-label'>".$_SESSION['lang']['pt']."</label>
                  <select id=kdPt name=kdPt class='form-select form-select-sm' onchange='getRek()'><option value=''></option>".$optPt."</select>
                </div>
                <div class='col-md-4'>
                  <label class='form-label'>".$_SESSION['lang']['tglKontrak']."</label>
                  <input type=text id=tlgKntrk class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this) />
                </div>
              </div>
            </div>
          </div>

          <div class='card mb-3'>
            <div class='card-header bg-primary text-white'>
              <h6 class='mb-0'>".$_SESSION['lang']['custInformation']."</h6>
            </div>
            <div class='card-body'>
              <div class='row g-3'>
                <div class='col-md-6'>
                  <label class='form-label'>".$_SESSION['lang']['nmcust']."</label>
                  <select id=custId name=custId class='form-select form-select-sm' onchange=\"getDataCust(0)\"><option value=></option>".$optCust."</select>
                  <select id=nmPerson class='form-select form-select-sm mt-2' style='display:none;'><option value=''>".$_SESSION['lang']['pilihdata']."</option></select>
                </div>
                <div class='col-md-6'>
                  <label class='form-label'>".$_SESSION['lang']['nokontrakinduk']."</label>
                  <select id='kntrkRef' class='form-select form-select-sm'>".$optNoref."</select>
                </div>
              </div>
            </div>
          </div>

          <div class='card mb-3'>
            <div class='card-header bg-primary text-white'>
              <h6 class='mb-0'>".$_SESSION['lang']['orderInfor']."</h6>
            </div>
            <div class='card-body'>
              <h6 class='mb-3'>".$_SESSION['lang']['goodsDesc']."</h6>
              <div class='row g-3 mb-3'>
                <div class='col-md-3'>
                  <label class='form-label'>".$_SESSION['lang']['namabarang']."</label>
                  <select id=kdBrg name=kdBrg class='form-select form-select-sm' onchange=\"getSatuan(0,0,0)\"><option value=''>".$_SESSION['lang']['pilihdata']."</option></select>
                </div>
                <div class='col-md-2'>
                  <label class='form-label'>".$_SESSION['lang']['satuan']."</label>
                  <select id=stn name=stn class='form-select form-select-sm'><option value=''></option></select>
                </div>
                <div class='col-md-2'>
                  <label class='form-label'>".$_SESSION['lang']['hargasatuan']."</label>
                  <input type=text class='form-control form-control-sm' name=HrgStn id=HrgStn onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('HrgStn',3);hitungHarga();rupiahkan(getById('tmpHarga'),'tBlg',true)\" onblur=\"rupiahkan(this,'tBlg',true)\" />
                </div>
                <div class='col-md-1'>
                  <label class='form-label'>".$_SESSION['lang']['matauang']."</label>
                  <select id=kurs name=kurs class='form-select form-select-sm'>".$optKurs."</select>
                </div>
                <div class='col-md-2'>
                  <label class='form-label'>".$_SESSION['lang']['jmlhBrg']."</label>
                  <input type=text class='form-control form-control-sm' name=jmlh id=jmlh onkeypress=\"return angka_doang(event);\" onkeyup=\"z.numberFormat('jmlh',2);hitungHarga();getBerat();\" />
                  <input id=tmpHarga type=hidden value=0>
                </div>
                <div class='col-md-2'>
                  <label class='form-label'>".$_SESSION['lang']['ppn']."</label>
                  <select id=ppnId name=ppnId class='form-select form-select-sm'>".$optSat."</select>
                </div>
              </div>
              <div class='row'>
                <div class='col-12'>
                  <label class='form-label'>".$_SESSION['lang']['terbilang']."</label>
                  <div class='alert alert-info mb-0'><span id=tBlg></span></div>
                </div>
              </div>
            </div>
          </div>

          <div class='card mb-3'>
            <div class='card-header bg-secondary text-white'>
              <h6 class='mb-0'>".$_SESSION['lang']['penyerahan']."</h6>
            </div>
            <div class='card-body'>
              <div class='table-responsive'>
                <table class='table table-sm table-bordered'>
                  <thead class='table-light'>
                    <tr>
                      <th>".$_SESSION['lang']['tgl_kirim']."</th>
                      <th>".$_SESSION['lang']['jumlah']."</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class='d-flex align-items-center gap-2'>
                          <input type=text id=tglKrm0 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                          <span>s.d.</span>
                          <input type=text id=tglSd0 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                        </div>
                      </td>
                      <td><input type=text class='form-control form-control-sm' name=jmlh0 id=jmlh0 onkeypress=\"return angka_doang(event);\" /></td>
                    </tr>
                    <tr>
                      <td>
                        <div class='d-flex align-items-center gap-2'>
                          <input type=text id=tglKrm1 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                          <span>s.d.</span>
                          <input type=text id=tglSd1 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                        </div>
                      </td>
                      <td><input type=text class='form-control form-control-sm' name=jmlh1 id=jmlh1 onkeypress=\"return angka_doang(event);\" /></td>
                    </tr>
                    <tr>
                      <td>
                        <div class='d-flex align-items-center gap-2'>
                          <input type=text id=tglKrm2 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                          <span>s.d.</span>
                          <input type=text id=tglSd2 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                        </div>
                      </td>
                      <td><input type=text class='form-control form-control-sm' name=jmlh2 id=jmlh2 onkeypress=\"return angka_doang(event);\" /></td>
                    </tr>
                    <tr>
                      <td>
                        <div class='d-flex align-items-center gap-2'>
                          <input type=text id=tglKrm3 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                          <span>s.d.</span>
                          <input type=text id=tglSd3 class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                        </div>
                      </td>
                      <td><input type=text class='form-control form-control-sm' name=jmlh3 id=jmlh3 onkeypress=\"return angka_doang(event);\" /></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class='row g-3 mb-3'>
            <div class='col-md-6'>
              <div class='card h-100'>
                <div class='card-header bg-secondary text-white'>
                  <h6 class='mb-0'>".$_SESSION['lang']['kualitas']."</h6>
                </div>
                <div class='card-body'>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['tempatpenyerahan']."</label>
                    <select name=tmbngn id=tmbngn class='form-select form-select-sm'>".$optFranco."</select>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>FFA</label>
                    <div class='input-group input-group-sm'>
                      <input class='form-control' id=ffa onkeypress='return angka_doang(event)' />
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>DOBI</label>
                    <input class='form-control form-control-sm' id=dobi onkeypress='return angka_doang(event)' />
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>M & I</label>
                    <div class='input-group input-group-sm'>
                      <input class='form-control' id=mdani onkeypress='return angka_doang(event)' />
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>Moisture</label>
                    <div class='input-group input-group-sm'>
                      <input class='form-control' id=moist onkeypress='return angka_doang(event)' />
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>Impurities</label>
                    <div class='input-group input-group-sm'>
                      <input class='form-control' id=dirt onkeypress='return angka_doang(event)' />
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>Grading</label>
                    <div class='input-group input-group-sm'>
                      <input class='form-control' id=grading onkeypress='return angka_doang(event)' />
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                  <div class='mb-0'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['toleransi']."</label>
                    <textarea name=tlransi id=tlransi class='form-control form-control-sm' onkeypress=\"return tanpa_kutip(event);\" rows='2'></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class='col-md-6'>
              <div class='card h-100'>
                <div class='card-header bg-secondary text-white'>
                  <h6 class='mb-0'>".$_SESSION['lang']['syaratPem']."</h6>
                </div>
                <div class='card-body'>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['payment']."</label>
                    <select name=syrtByr id=syrtByr class='form-select form-select-sm'>".$optTermin."</select>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['tanggalbayar']."</label>
                    <input type=text id=tglByr class='form-control form-control-sm' onkeypress=\"return false;\" onmouseover=setCalendar(this)>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['bayarke']."</label>
                    <select name=byrKe id=byrKe class='form-select form-select-sm'>".$optByrke."</select>
                  </div>
                  <div class='mb-2'>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['tndaTangan']."</label>
                    <select name=tndtng id=tndtng class='form-select form-select-sm'>".$optTtdjual."</select>
                  </div>
                  <div class='mb-2' hidden>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['penjual']."</label>
                    <input type=text name=tndtngJbtn id=tndtngJbtn class='form-control form-control-sm' />
                  </div>
                  <div class='mb-2' hidden>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['tandatangan']." ".$_SESSION['lang']['Pembeli']."</label>
                    <input type=text name=tndtngPembli id=tndtngPembli class='form-control form-control-sm' />
                  </div>
                  <div class='mb-0' hidden>
                    <label class='form-label small mb-1'>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['Pembeli']."</label>
                    <input type=text name=jtbnPembli id=jtbnPembli class='form-control form-control-sm' />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class='card mb-3'>
            <div class='card-header bg-secondary text-white'>
              <h6 class='mb-0'>".$_SESSION['lang']['lainlain']."</h6>
            </div>
            <div class='card-body'>
              <textarea onkeypress=\"return tanpa_kutip(event);\" id=cttnLain class='form-control' rows='6'>".
				 //"Kualitas mutu FFA berasarkan hasil analisa Sucofindo yang sudah ditentukan oleh kedua belah pihak, dimana hasilnya akan dipakai sebagai acuan penetapan mutu barang. Tenggang waktu penyerahan barang maksimal 4 (empat) hari. Penjual dapat melakukan pembatalan penyerahan sepihak bila pembeli tidak melakukan pengangkutan dari tempat yang disepakati dalam batas tenggang waktu. 
				 //Bila kualitas  diluar standar, maka klaim akan ditentukan sbb:
				 //- FFA 5.00%-5.50% harga akan dipotong sebesar Rp 100,-/kg.
				 //- FFA 5.51%-6.00% harga akan dipotong sebesar Rp 200,-/kg.
				 //- FFA 6.01%-6.50% harga akan dipotong sebesar Rp 300,-/kg.
				 //- FFA 6.51%-7.00% harga akan dipotong sebesar Rp 400,-/kg.
				 //- FFA > 7.00% maka pembeli berhak menolak barang.
				 //- Klaim DOBI: (2-DOBI Pemuatan Hasil Analisa Sucofindo)/100 x harga x kuantitas".
				 "</textarea>
            </div>
          </div>

          <div class='d-flex gap-2 justify-content-center mb-3'>
            <button class='btn btn-primary btn-sm' onclick=saveKP()>".$_SESSION['lang']['save']."</button>
            <button class='btn btn-secondary btn-sm' onclick=clearFrom()>".$_SESSION['lang']['new']."</button>
          </div>
";

$optSch.="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPt="select * from ".$dbname.".organisasi where tipe='PT' ";
$nPt=  mysql_query($iPt) or die (mysql_error($conn));
while($dPt=  mysql_fetch_assoc($nPt))
{
    $optSch.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['namaorganisasi']."</option>";
}

$optKomoditi="<option value=''>".$_SESSION['lang']['all']."</option>";
$sKomoditi="select distinct(a.kodebarang),b.namabarang from ".$dbname.".pmn_kontrakjual a left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang";
$qKomoditi= mysql_query($sKomoditi) or die (mysql_error($conn));
while($dKomoditi=  mysql_fetch_assoc($qKomoditi))
{
    $optKomoditi.="<option value='".$dKomoditi['kodebarang']."'>".$dKomoditi['namabarang']."</option>";
}

$optCust="<option value=''>".$_SESSION['lang']['all']."</option>";
$sCust="select distinct(a.koderekanan),b.namacustomer from ".$dbname.".pmn_kontrakjual a left join ".$dbname.".pmn_4customer b on a.koderekanan=b.kodecustomer";
$qCust= mysql_query($sCust) or die (mysql_error($conn));
while($dCust=  mysql_fetch_assoc($qCust))
{
    $optCust.="<option value='".$dCust['koderekanan']."'>".$dCust['namacustomer']."</option>";
}

$frm[1]="
          <div class='card mb-3'>
            <div class='card-header bg-light'>
              <h6 class='mb-0'>".$_SESSION['lang']['find']."</h6>
            </div>
            <div class='card-body'>
              <div class='row g-3 mb-3'>
                <div class='col-md-3'>
                  <label class='form-label small'>".$_SESSION['lang']['NoKontrak']."</label>
                  <input type=text id=txtnokntrk class='form-control form-control-sm' onkeypress=\"return tanpa_kutip(event);\" />
                </div>
                <div class='col-md-3'>
                  <label class='form-label small'>".$_SESSION['lang']['pt']."</label>
                  <select name=ptSch id=ptSch class='form-select form-select-sm'>".$optSch."</select>
                </div>
                <div class='col-md-3'>
                  <label class='form-label small'>".$_SESSION['lang']['komoditi']."</label>
                  <select name=ptKomoditi id=ptKomoditi class='form-select form-select-sm'>".$optKomoditi."</select>
                </div>
                <div class='col-md-3'>
                  <label class='form-label small'>".$_SESSION['lang']['nmcust']."</label>
                  <select name=ptCust id=ptCust class='form-select form-select-sm'>".$optCust."</select>
                </div>
              </div>
              <div class='text-end'>
                <button class='btn btn-primary btn-sm' onclick=cariNoKntrk()>".$_SESSION['lang']['find']."</button>
              </div>
            </div>
          </div>

          <div class='table-responsive'>
          <table class='table table-sm table-striped table-hover table-bordered'>
      <thead>
          <tr class='table-primary text-white'>
          <th>No.</th>
          <th>".$_SESSION['lang']['NoKontrak']."</th>
          <th>".$_SESSION['lang']['nm_perusahaan']."</th>
          <th>".$_SESSION['lang']['nmcust']."</th>
          <th>".$_SESSION['lang']['tglKontrak']."</th>
          <th>".$_SESSION['lang']['produk']."</th>
          <th>".$_SESSION['lang']['hargasatuan']."</th>
          <th>".$_SESSION['lang']['ppn']." Incl/Excl</th>
          <th>".$_SESSION['lang']['tgl_kirim']."</th>
          <th style='width:8%'>Action</th>
          </tr>
          </thead>
           <tbody id=containerlist>
           <script>
           loadNewData();
           </script>
           </tbody>
           </table>
          </div>
";

?>

<!-- Bootstrap Nav Tabs -->
<ul class="nav nav-tabs" id="kontrakPenjualanTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form-content" type="button" role="tab" aria-controls="form-content" aria-selected="true">
      <?php echo $_SESSION['lang']['form']; ?>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-content" type="button" role="tab" aria-controls="list-content" aria-selected="false">
      <?php echo $_SESSION['lang']['list']; ?>
    </button>
  </li>
</ul>

<!-- Bootstrap Tab Content -->
<div class="tab-content border border-top-0 p-3 bg-white" id="kontrakPenjualanTabContent">
  <div class="tab-pane fade show active" id="form-content" role="tabpanel" aria-labelledby="form-tab">
    <?php echo $frm[0]; ?>
  </div>
  <div class="tab-pane fade" id="list-content" role="tabpanel" aria-labelledby="list-tab">
    <?php echo $frm[1]; ?>
  </div>
</div>

<?php
CLOSE_BOX();
echo close_body();
?>