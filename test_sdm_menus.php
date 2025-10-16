<?php
// Test SDM Menu Items
// This script tests all SDM menu items and reports their status

require_once('config/connection.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['namauser'])) {
    die('ERROR: Not logged in. Please login first at http://localhost/erpmill/');
}

// Define all SDM menu items to test
$menuItems = array(
    // TRANSAKSI
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Absensi', 'url' => 'sdm_absensi.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Lembur', 'url' => 'sdm_lembur.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Administrasi Cuti', 'url' => 'sdm_cuti.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Potongan', 'url' => 'sdm_potongan.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Pembagian Catu', 'url' => 'sdm_pembagianCatu.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Potongan Premi Sudah Dibayar/Hk', 'url' => 'sdm_potongan_premi_sby.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Upload Absensi', 'url' => 'sdm_uploadabsensi.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Verifikasi Lembur', 'url' => 'sdm_lemburverifikasi.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Upload Data Karyawan', 'url' => 'sdm_upload_data_karyawan.php'),
    array('category' => 'Transaksi > Administrasi Personalia', 'name' => 'Upload Finger Print', 'url' => 'sdm_uploadfinger.php'),

    array('category' => 'Transaksi', 'name' => 'Angsuran Karyawan', 'url' => 'sdm_angsurankaryawan.php'),

    array('category' => 'Transaksi > Pengobatan Karyawan', 'name' => 'Pembayaran Claim', 'url' => 'sdm_pembayaranKlaim.php'),
    array('category' => 'Transaksi > Pengobatan Karyawan', 'name' => 'Klaim Pengobatan', 'url' => 'sdm_pengobatan.php'),

    array('category' => 'Transaksi', 'name' => 'Data Karyawan', 'url' => 'sdm_data_karyawan.php'),
    array('category' => 'Transaksi', 'name' => 'Penggantian Transport', 'url' => 'sdm_penggantianTransport.php'),
    array('category' => 'Transaksi', 'name' => 'Promosi/Demosi/Mutasi', 'url' => 'sdm_promosi.php'),

    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Pengajuan Perjalanan Dinas', 'url' => 'sdm_pjdinas.php'),
    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Persetujuan Perjalanan Dinas', 'url' => 'sdm_3persetujuanPJD.php'),
    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Pembayaran uang Muka PJD', 'url' => 'sdm_pembayaranUMukaPJD.php'),
    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Pertanggung-jawaban PJD', 'url' => 'sdm_pertanggungjawabanPJD.php'),
    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Verifikasi Pertanggungjawaban PJD', 'url' => 'sdm_verifikasiPertanggungjawabanPJD.php'),
    array('category' => 'Transaksi > Perjalanan Dinas', 'name' => 'Penyelesaian Biaya PJD', 'url' => 'sdm_penyelesaianPJD.php'),

    array('category' => 'Transaksi', 'name' => 'Mutasi Antar Kebun', 'url' => 'sdm_rotasiSecurity.php'),
    array('category' => 'Transaksi', 'name' => 'Surat Peringatan', 'url' => 'sdm_suratPeringatan.php'),

    array('category' => 'Transaksi > Pengajuan Ijin/Cuti', 'name' => 'Pengajuan Cuti Tahunan', 'url' => 'sdm_ijin_meninggalkan_kantor.php'),
    array('category' => 'Transaksi > Pengajuan Ijin/Cuti', 'name' => 'Pengajuan Ijin/Cuti Khusus', 'url' => 'sdm_ijin_cuti_khusus.php'),
    array('category' => 'Transaksi > Pengajuan Ijin/Cuti', 'name' => 'Cuti Bersama', 'url' => 'sdm_cutibersama.php'),
    array('category' => 'Transaksi > Pengajuan Ijin/Cuti', 'name' => 'Re-Calculate Cuti', 'url' => 'sdm_recalculatecuti.php'),

    array('category' => 'Transaksi', 'name' => 'Struktur Jabatan', 'url' => 'sdm_orgchart.php'),
    array('category' => 'Transaksi', 'name' => 'Pesangon', 'url' => 'sdm_pesangon.php'),
    array('category' => 'Transaksi', 'name' => 'Training', 'url' => 'sdm_training.php'),
    array('category' => 'Transaksi', 'name' => 'Pencapaian Pekerjaan', 'url' => 'sdm_pekerjaanharian.php'),

    // LAPORAN
    array('category' => 'Laporan', 'name' => 'Angsuran Karyawan', 'url' => 'sdm_laporanAngsurankaryawan.php'),
    array('category' => 'Laporan', 'name' => 'Pengobatan Karyawan', 'url' => 'sdm_2laporanKlaimPengobatan.php'),
    array('category' => 'Laporan', 'name' => 'Biaya Pengobatan', 'url' => 'sdm_2biayapengobatan.php'),
    array('category' => 'Laporan', 'name' => 'Data Karyawan', 'url' => 'sdm_2datakaryawan.php'),
    array('category' => 'Laporan', 'name' => 'Hasil Kerja Perjalanan Dinas', 'url' => 'sdm_2hasilKerjaPjd.php'),
    array('category' => 'Laporan', 'name' => 'Rekap Perjalanan Dinas', 'url' => 'sdm_2rekapperjalanandinas.php'),
    array('category' => 'Laporan', 'name' => 'Kehadiran Karyawan Unit', 'url' => 'sdm_2laporanKehadiranUnit.php'),
    array('category' => 'Laporan', 'name' => 'Kehadiran Karyawan HO', 'url' => 'sdm_2laporanKehadiranHO.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Cuti', 'url' => 'sdm_laporanCuti.php'),
    array('category' => 'Laporan > Penggajian(Unit)', 'name' => 'PPh 21', 'url' => 'sdm_2pajak.php'),
    array('category' => 'Laporan > Penggajian(Unit) > Print Slip Gaji', 'name' => 'Harian', 'url' => 'sdm_2slipGajiHarian.php'),
    array('category' => 'Laporan > Penggajian(Unit) > Print Slip Gaji', 'name' => 'Bulanan', 'url' => 'sdm_2slipGajiBulanan.php'),
    array('category' => 'Laporan > Penggajian(Unit) > Print Slip Gaji', 'name' => 'Slip Bonus THR', 'url' => 'sdm_2slipBonusThr.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Lembur', 'url' => 'sdm_2laporanLembur.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Potongan Pendapatan', 'url' => 'sdm_2potongan_pendapatan.php'),
    array('category' => 'Laporan', 'name' => 'Daftar Perjalanan Dinas', 'url' => 'sdm_2laporanPjdinas.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Premi', 'url' => 'sdm_2laporanPremi.php'),
    array('category' => 'Laporan', 'name' => 'Premi Per Hari', 'url' => 'sdm_2laporanPremiPerhari.php'),
    array('category' => 'Laporan', 'name' => 'Premi Panen Per Kemandoran', 'url' => 'sdm_2laporanPremiPerTransaksi.php'),
    array('category' => 'Laporan', 'name' => 'Rincian Gaji per Bagian', 'url' => 'sdm_2rincianGajiBagian.php'),
    array('category' => 'Laporan', 'name' => 'Daftar Jamsostek', 'url' => 'sdm_2daftarIuran_jamsostek.php'),
    array('category' => 'Laporan', 'name' => 'Biaya Perjalanan Dinas', 'url' => 'sdm_2perjalananDinas.php'),
    array('category' => 'Laporan', 'name' => 'Daftar Ijin/Cuti', 'url' => 'sdm_laporan_ijin_keluar_kantor.php'),
    array('category' => 'Laporan', 'name' => 'Daftar Upah Remise I', 'url' => 'sdm_2upah_remise.php'),
    array('category' => 'Laporan', 'name' => 'Daftar Karyawan NPWP', 'url' => 'sdm_2daftarKaryNpwp.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Catu Beras', 'url' => 'sdm_2laporan_catu_beras.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Realisasi Gaji', 'url' => 'sdm_2realisasiGaji.php'),
    array('category' => 'Laporan', 'name' => 'KPI-Input dan Posting', 'url' => 'sdm_kpiData.php'),
    array('category' => 'Laporan', 'name' => 'Summary Karyawan', 'url' => 'sdm_2summarykaryawan.php'),
    array('category' => 'Laporan', 'name' => 'BKM vs Finger Print', 'url' => 'sdm_2bkmvsfp.php'),
    array('category' => 'Laporan', 'name' => 'Riwayat Perubahan Gaji', 'url' => 'sdm_2histgaji.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Total per Komponen Gaji', 'url' => 'sdm_2totalkomponengaji.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Training Karyawan', 'url' => 'sdm_laporan_training.php'),
    array('category' => 'Laporan', 'name' => 'Riwayat Perubahan Data Karyawan', 'url' => 'sdm_2histkaryawan.php'),
    array('category' => 'Laporan', 'name' => 'BPJS', 'url' => 'sdm_2bpjs.php'),
    array('category' => 'Laporan', 'name' => 'Premi Mandor/Kerani Panen', 'url' => 'sdm_2laporanPremiMandorPanen.php'),
    array('category' => 'Laporan', 'name' => 'Rekap Training', 'url' => 'sdm_laporantraining.php'),
    array('category' => 'Laporan', 'name' => 'Lap. Pencapaian Pekerjaan', 'url' => 'sdm_lappekerjaanharian.php'),
    array('category' => 'Laporan', 'name' => 'Perhitungan Premi Per Mandor Panen', 'url' => 'sdm_2laporanPremiPerSupervisi.php'),
    array('category' => 'Laporan', 'name' => 'Laporan Absensi', 'url' => 'sdm_lap_absensi.php'),

    // PROSES
    array('category' => 'Proses', 'name' => 'Tunjangan', 'url' => 'sdm_3tunjangan.php'),
    array('category' => 'Proses', 'name' => 'UM / Tunj Absensi / Premi', 'url' => 'sdm_3uangmakan.php'),
    array('category' => 'Proses', 'name' => 'Penggajian Bulanan', 'url' => 'sdm_3prosesgjbulanan.php'),
    array('category' => 'Proses', 'name' => 'Penggajian Harian', 'url' => 'sdm_3prosesgjharian.php'),
    array('category' => 'Proses', 'name' => 'Hapus Slip Gaji', 'url' => 'sdm_3hapusSlipGaji.php'),
    array('category' => 'Proses', 'name' => 'Rapel', 'url' => 'sdm_rapel_kebun.php'),
    array('category' => 'Proses', 'name' => 'Revisi PJD', 'url' => 'sdm_3revisipjd.php'),
    array('category' => 'Proses', 'name' => 'Pendapatan Lain', 'url' => 'sdm_3pl.php'),

    // SETUP
    array('category' => 'Setup', 'name' => 'Absensi', 'url' => 'sdm_5absensi.php'),
    array('category' => 'Setup', 'name' => 'Gaji Pokok', 'url' => 'sdm_5gajipokok.php'),
    array('category' => 'Setup', 'name' => 'Uang Muka PJD', 'url' => 'sdm_5uangmukapjd.php'),
    array('category' => 'Setup', 'name' => 'Jenis Biaya Perjalanan Dinas', 'url' => 'sdm_5jenisByPJD.php'),
    array('category' => 'Setup', 'name' => 'Jenis SP/PHK', 'url' => 'sdm_5jenissp.php'),
    array('category' => 'Setup', 'name' => 'Lembur', 'url' => 'sdm_5lembur.php'),
    array('category' => 'Setup', 'name' => 'Natura', 'url' => 'sdm_5natura.php'),
    array('category' => 'Setup', 'name' => 'Pendidikan', 'url' => 'sdm_5pendidikan.php'),

    array('category' => 'Setup > Pengobatan', 'name' => 'Plafon Pengobatan', 'url' => 'sdm_5plafonPengobatan.php'),
    array('category' => 'Setup > Pengobatan', 'name' => 'Rumah Sakit/Apotik/Klinik', 'url' => 'sdm_5rumahSakit.php'),
    array('category' => 'Setup > Pengobatan', 'name' => 'Kelompok Diagnosa', 'url' => 'sdm_5kldiagnosa.php'),
    array('category' => 'Setup > Pengobatan', 'name' => 'Daftar Diagnosa', 'url' => 'sdm_5diagnosa.php'),
    array('category' => 'Setup > Pengobatan', 'name' => 'Jenis Biaya Pengobatan', 'url' => 'sdm_5jenisBiayaPengobatan.php'),

    array('category' => 'Setup', 'name' => 'Periode Penggajian Unit', 'url' => 'sdm_5periodegaji.php'),

    array('category' => 'Setup > Struktur', 'name' => 'Golongan', 'url' => 'sdm_5golongan.php'),
    array('category' => 'Setup > Struktur', 'name' => 'Jabatan', 'url' => 'sdm_5jabatan.php'),
    array('category' => 'Setup > Struktur', 'name' => 'Departemen', 'url' => 'sdm_5departemen.php'),
    array('category' => 'Setup > Struktur', 'name' => 'Tipe Karyawan', 'url' => 'sdm_5tipekaryawan.php'),

    array('category' => 'Setup', 'name' => 'Standard Uang Saku', 'url' => 'sdm_5standarUsaku.php'),
    array('category' => 'Setup', 'name' => 'Harga Ticket', 'url' => 'sdm_5hargaTicket.php'),
    array('category' => 'Setup', 'name' => 'Fasilitas MPP', 'url' => 'sdm_5fasilitasMpp.php'),
    array('category' => 'Setup', 'name' => 'Hari Kerja Efektif', 'url' => 'sdm_5hkEfektif.php'),
    array('category' => 'Setup', 'name' => 'Standard Tunjangan', 'url' => 'sdm_5sttunjangan.php'),
    array('category' => 'Setup', 'name' => 'ID Fingerprint', 'url' => 'setup_fingerprint.php'),
    array('category' => 'Setup', 'name' => 'Premi Tetap', 'url' => 'sdm_5premitetap.php'),

    array('category' => 'Setup > Perhitungan', 'name' => 'Pensiun', 'url' => 'sdm_5pensiun.php'),
    array('category' => 'Setup > Perhitungan', 'name' => 'Pajak Pesangon', 'url' => 'sdm_5pajakpesangon.php'),

    array('category' => 'Setup', 'name' => 'Hari Libur', 'url' => 'sdm_5harilibur.php'),
    array('category' => 'Setup', 'name' => 'Pot BPJS', 'url' => 'sdm_5bpjs.php'),
    array('category' => 'Setup', 'name' => 'Jenis Training', 'url' => 'sdm_5jenistraining.php'),
);

$results = array();
$successCount = 0;
$failCount = 0;

echo "<html><head><title>SDM Menu Test Results</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #1E3A8A; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th { background-color: #1E3A8A; color: white; padding: 10px; text-align: left; }
td { border: 1px solid #ddd; padding: 8px; }
tr:nth-child(even) { background-color: #f2f2f2; }
.success { color: green; font-weight: bold; }
.failed { color: red; font-weight: bold; }
.summary { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #1E3A8A; }
</style></head><body>";

echo "<h1>SDM MODULE TEST REPORT</h1>";
echo "<p>Testing all SDM menu items systematically...</p>";

echo "<table>";
echo "<tr><th>#</th><th>Category</th><th>Menu Name</th><th>URL</th><th>Status</th><th>Notes</th></tr>";

foreach($menuItems as $index => $item) {
    $num = $index + 1;
    $filePath = 'C:/XAMPP/xampp/htdocs/erpmill/' . $item['url'];
    $fileExists = file_exists($filePath);

    if($fileExists) {
        // Check if file has PHP errors by trying to parse it
        $content = file_get_contents($filePath);
        $hasRequire = (strpos($content, 'require') !== false || strpos($content, 'include') !== false);

        $status = "SUCCESS";
        $notes = "File exists and accessible";
        $statusClass = "success";
        $successCount++;
    } else {
        $status = "FAILED";
        $notes = "File not found: " . $filePath;
        $statusClass = "failed";
        $failCount++;
    }

    echo "<tr>";
    echo "<td>{$num}</td>";
    echo "<td>{$item['category']}</td>";
    echo "<td>{$item['name']}</td>";
    echo "<td><a href='http://localhost/erpmill/{$item['url']}' target='_blank'>{$item['url']}</a></td>";
    echo "<td class='{$statusClass}'>{$status}</td>";
    echo "<td>{$notes}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<div class='summary'>";
echo "<h2>Test Summary</h2>";
echo "<p><strong>Total menu items tested:</strong> " . count($menuItems) . "</p>";
echo "<p><strong>Successful:</strong> <span class='success'>{$successCount}</span></p>";
echo "<p><strong>Failed:</strong> <span class='failed'>{$failCount}</span></p>";
echo "<p><strong>Success Rate:</strong> " . round(($successCount / count($menuItems)) * 100, 2) . "%</p>";
echo "</div>";

if($failCount > 0) {
    echo "<div class='summary'>";
    echo "<h2>Failed Items Detail</h2>";
    echo "<ul>";
    foreach($menuItems as $index => $item) {
        $filePath = 'C:/XAMPP/xampp/htdocs/erpmill/' . $item['url'];
        if(!file_exists($filePath)) {
            echo "<li><strong>{$item['category']} > {$item['name']}</strong>: {$item['url']} - File not found</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
}

echo "<p style='color: #666; margin-top: 30px;'><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
echo "</body></html>";
?>
