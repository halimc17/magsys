<?php
/**
 * PABRIK Menu Testing Script
 * Tests all menu items under PABRIK module
 */

require_once('config/connection.php');
session_start();

// Set session variables if not set (for testing)
if (!isset($_SESSION['namauser'])) {
    $_SESSION['namauser'] = 'kingking.firdaus';
    $_SESSION['language'] = 'ID';
    $_SESSION['access_type'] = 'detail';
    $_SESSION['access_level'] = 1;
}

header('Content-Type: application/json');

// PABRIK menu items from database
$menuItems = array(
    'Transaksi' => array(
        array('id' => 301, 'name' => 'Pengoperasian Pabrik', 'action' => 'pabrik_pengolahan'),
        array('id' => 302, 'name' => 'Pemeliharaan Mesin', 'action' => 'pabrik_perbaikan'),
        array('id' => 303, 'name' => 'Stok CPO/PK', 'action' => 'pabrik_hasil'),
        array('id' => 300, 'name' => 'Sortasi Buah(lama tdk dipakai)', 'action' => 'pabrik_sortasi'),
        array('id' => 620, 'name' => 'Timbangan', 'action' => 'pabrik_timbangan'),
        array('id' => 721, 'name' => 'Produksi Harian', 'action' => 'pabrik_produksi'),
        array('id' => 853, 'name' => 'Timbangan Pembeli', 'action' => 'pabrik_timbangan_pembeli'),
        array('id' => 1047, 'name' => 'Target Eksternal', 'action' => 'pabrik_taksasi'),
        array('id' => 1066, 'name' => 'Sortasi Buah', 'action' => 'pabrik_sortasi2'),
        array('id' => 1155, 'name' => 'Stok Produk Lain', 'action' => 'pabrik_stokProduk'),
        array('id' => 1158, 'name' => ' BA Pengurangan Stok', 'action' => 'pabrik_pembersihantangki'),
        array('id' => 1229, 'name' => 'Data Press dan Air', 'action' => 'pabrik_datapress'),
        array('id' => 1230, 'name' => 'BA Transportir', 'action' => 'pabrik_batransportir'),
        array('id' => 1245, 'name' => 'Data Mesin', 'action' => 'pabrik_machinery'),
        array('id' => 1239, 'name' => 'HM/Jam Jalan', 'action' => 'pabrik_hm'),
        array('id' => 1243, 'name' => 'Pemeliharaan Prediktif', 'action' => 'pabrik_prediktif'),
        array('id' => 1253, 'name' => 'Thickness', 'action' => 'pabrik_thickness'),
        array('id' => 1260, 'name' => 'Verifikasi Ampere', 'action' => 'pabrik_verifikasi_ampere'),
        array('id' => 1264, 'name' => 'Earth Test', 'action' => 'pabrik_earthtest'),
        array('id' => 1266, 'name' => 'Preventatif Panel', 'action' => 'pabrik_preventifpanel'),
        array('id' => 1268, 'name' => 'Megger Test', 'action' => 'pabrik_meggertest'),
        array('id' => 1271, 'name' => 'Retur/Outspec', 'action' => 'pabrik_outspec'),
        array('id' => 1277, 'name' => 'Material Ballance', 'action' => 'pabrik_materialballance'),
        array('id' => 1280, 'name' => 'Limbah B3', 'action' => 'pabrik_limbahb3'),
        array('id' => 1288, 'name' => 'Grading Actual TBS', 'action' => 'pabrik_sortasli')
    ),
    'Laporan' => array(
        array('id' => 783, 'name' => 'Laporan Produksi Bulanan', 'action' => 'pabrik_2produksiHarian_v1'),
        array('id' => 732, 'name' => 'Laporan Produksi Tahunan', 'action' => 'pabrik_2produksiHarian'),
        array('id' => 748, 'name' => 'Laporan Penerimaan TBS', 'action' => 'pabrik_2penerimaantbs'),
        array('id' => 750, 'name' => 'Laporan Pengiriman', 'action' => 'pabrik_2pengiriman'),
        array('id' => 627, 'name' => 'Pemenuhan Kontrak', 'action' => 'pmn_laporanPemenuhanKontrak'),
        array('id' => 419, 'name' => 'Stok CPO/PK', 'action' => 'pabrik_4persediaan'),
        array('id' => 621, 'name' => 'Timbangan', 'action' => 'pabrik_2timbangan'),
        array('id' => 1006, 'name' => 'Pabrik Loses', 'action' => 'pabrik_2loses'),
        array('id' => 418, 'name' => 'Pengolahan', 'action' => 'pabrik_2pengolahan_rev'),
        array('id' => 1004, 'name' => 'Pengolahan Detail', 'action' => 'pabrik_2pengolahanv2'),
        array('id' => 610, 'name' => 'Perawatan Mesin (TIDAK DI PAKAI)', 'action' => 'pabrik_laporanPerawatanMesin'),
        array('id' => 733, 'name' => 'Laporan Sortasi Intenal', 'action' => 'pabrik_2laporanSortasiPabrik'),
        array('id' => 1059, 'name' => 'Stagnasi', 'action' => 'pabrik_2stagnasi'),
        array('id' => 1060, 'name' => 'Rekap DO', 'action' => 'pmn_2rekapdo'),
        array('id' => 1078, 'name' => 'Laporan Sortasi Eksternal', 'action' => 'pabrik_2laporanSortasiPabrik2'),
        array('id' => 1152, 'name' => 'Harga TBS', 'action' => 'pabrik_2hargatbs'),
        array('id' => 1156, 'name' => 'Job Card Report /  Perawatan Mesin', 'action' => 'pabrik_2perbaikan'),
        array('id' => 1176, 'name' => 'Biaya Pabrik', 'action' => 'pabrik_2biaya'),
        array('id' => 1217, 'name' => 'Rekap Budget vs Real Biaya PKS', 'action' => 'pabrik_2biayav2'),
        array('id' => 1206, 'name' => 'Sortasi v2', 'action' => 'pabrik_2sortasi'),
        array('id' => 1216, 'name' => 'Sortasi v3', 'action' => 'pabrik_2sortasiv'),
        array('id' => 1073, 'name' => 'Laporan Pembelian TBS', 'action' => 'pabrik_2hargatbs'),
        array('id' => 1213, 'name' => 'Stock Produk Lain', 'action' => 'pabrik_2stokProduk'),
        array('id' => 1223, 'name' => 'LHP', 'action' => 'pabrik_lhp'),
        array('id' => 1257, 'name' => 'Laporan Hutang Transportir', 'action' => 'pmn_laphutangtransportir'),
        array('id' => 1242, 'name' => 'Data Mesin', 'action' => 'pabrik_lapmachinery'),
        array('id' => 1256, 'name' => 'Penilaian Kondisi Mesin', 'action' => 'pabrik_lapPenilaianmachinery'),
        array('id' => 1241, 'name' => 'Laporan HM/Jam Jalan', 'action' => 'pabrik_laphm'),
        array('id' => 1246, 'name' => 'HM Service Mesin', 'action' => 'pabrik_lapservicehm'),
        array('id' => 1244, 'name' => 'Pemeliharaan Prediktif', 'action' => 'pabrik_lapprediktif'),
        array('id' => 1254, 'name' => 'Thickness', 'action' => 'pabrik_lapthickness'),
        array('id' => 1261, 'name' => 'Verifikasi Ampere', 'action' => 'pabrik_lapverifikasi_ampere'),
        array('id' => 1265, 'name' => 'Earth Test', 'action' => 'pabrik_lap_earthtest'),
        array('id' => 1267, 'name' => 'Preventatif Panel', 'action' => 'pabrik_lap_preventifpanel'),
        array('id' => 1269, 'name' => 'Megger Test', 'action' => 'pabrik_lap_meggertest'),
        array('id' => 1258, 'name' => 'Laporan Grading', 'action' => 'pabrik_laporan_grading'),
        array('id' => 1278, 'name' => 'Laporan Material Ballance', 'action' => 'pabrik_lap_materialballance'),
        array('id' => 1281, 'name' => 'Laporan Limbah B3', 'action' => 'pabrik_lap_limbahb3'),
        array('id' => 1282, 'name' => 'Laporan Retur/Outspec', 'action' => 'pabrik_lap_outspec'),
        array('id' => 1289, 'name' => 'Lap Grading Actual', 'action' => 'pabrik_lapfull_grading')
    ),
    'Proses' => array(
        array('id' => 728, 'name' => 'Upload Data Vendor', 'action' => 'pabrik_3uploadDataVendor'),
        array('id' => 735, 'name' => 'Posting Perawatan Mesin', 'action' => 'pabrik_3posting_perawatan_mesin')
    ),
    'Setup' => array(
        array('id' => 428, 'name' => 'Shift', 'action' => 'pabrik_5shift'),
        array('id' => 429, 'name' => 'Tangki', 'action' => 'pabrik_5tangki'),
        array('id' => 1160, 'name' => 'Kalibrasi Tinggi Tangki', 'action' => 'pabrik_5tinggitangki'),
        array('id' => 1162, 'name' => 'Suhu', 'action' => 'pabrik_5suhu'),
        array('id' => 1163, 'name' => 'Suhu Standard Kalibrasi', 'action' => 'pabrik_5suhustandardkalibrasi'),
        array('id' => 734, 'name' => 'Fraksi', 'action' => 'pabrik_5fraksi'),
        array('id' => 877, 'name' => 'Potongan Fraksi', 'action' => 'pabrik_5potFraksi'),
        array('id' => 1151, 'name' => 'Harga TBS', 'action' => 'pabrik_5hargatbs'),
        array('id' => 1240, 'name' => 'Setup HM Mesin', 'action' => 'pabrik_hm_setup')
    )
);

$results = array();
$totalTested = 0;
$totalSuccess = 0;
$totalFailed = 0;

foreach ($menuItems as $category => $items) {
    foreach ($items as $item) {
        $fileName = $item['action'] . '.php';
        $filePath = __DIR__ . '/' . $fileName;

        $testResult = array(
            'category' => $category,
            'id' => $item['id'],
            'name' => $item['name'],
            'action' => $item['action'],
            'url' => 'http://localhost/erpmill/' . $fileName,
            'file_exists' => file_exists($filePath),
            'file_readable' => file_exists($filePath) && is_readable($filePath),
            'status' => 'UNKNOWN',
            'errors' => array()
        );

        // Test if file exists and is readable
        if (!$testResult['file_exists']) {
            $testResult['status'] = 'FAILED';
            $testResult['errors'][] = 'File does not exist: ' . $fileName;
            $totalFailed++;
        } elseif (!$testResult['file_readable']) {
            $testResult['status'] = 'FAILED';
            $testResult['errors'][] = 'File is not readable: ' . $fileName;
            $totalFailed++;
        } else {
            // Try to check for syntax errors
            $output = array();
            $returnVar = 0;
            exec('php -l "' . $filePath . '" 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $testResult['status'] = 'FAILED';
                $testResult['errors'][] = 'PHP Syntax Error: ' . implode("\n", $output);
                $totalFailed++;
            } else {
                $testResult['status'] = 'SUCCESS';
                $totalSuccess++;
            }
        }

        $totalTested++;
        $results[] = $testResult;
    }
}

$summary = array(
    'total_tested' => $totalTested,
    'total_success' => $totalSuccess,
    'total_failed' => $totalFailed,
    'success_rate' => round(($totalSuccess / $totalTested) * 100, 2) . '%'
);

echo json_encode(array(
    'summary' => $summary,
    'results' => $results
), 128); // JSON_PRETTY_PRINT constant value
?>
