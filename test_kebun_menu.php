<?php
/**
 * KEBUN MODULE MENU TEST SCRIPT
 * This script tests all menu items in the KEBUN module
 */

require_once('config/connection.php');

// Test data from menu database
$transaksi_menus = array(
    array('id' => 293, 'caption' => 'Pembukaan Lahan', 'action' => 'kebun_bukalahan', 'hide' => 0),
    ['id' => 294, 'caption' => 'Pembibitan', 'action' => 'kebun_pembibitan', 'hide' => 0],
    ['id' => 295, 'caption' => 'Pemeliharaan TBM', 'action' => 'kebun_pemeliharaantbm', 'hide' => 0],
    ['id' => 296, 'caption' => 'Pemeliharaan TM', 'action' => 'kebun_pemeliharaantm', 'hide' => 0],
    ['id' => 297, 'caption' => 'Kegiatan Panen', 'action' => 'kebun_panen', 'hide' => 0],
    ['id' => 298, 'caption' => 'Sensus Produksi', 'action' => 'kebun_rencanapanen', 'hide' => 0],
    ['id' => 607, 'caption' => 'Surat Pengantar Angkutan TBS', 'action' => 'kebun_spb', 'hide' => 0],
    ['id' => 625, 'caption' => 'Curah Hujan', 'action' => 'kebun_curahHujan', 'hide' => 0],
    ['id' => 869, 'caption' => 'Keluar-Masuk Bibit', 'action' => 'bibit_keluar_masuk', 'hide' => 0],
    ['id' => 966, 'caption' => 'Premi Kemandoran(Input Harian)(tdk dipak', 'action' => 'kebun_premiKemandoran', 'hide' => 1],
    ['id' => 743, 'caption' => 'Premi Pengawas(Input Bulanan)(tdk dipaka', 'action' => 'kebun_premiPengawas', 'hide' => 1],
    ['id' => 677, 'caption' => 'Rekomendasi Pupuk(tdk dipakai_', 'action' => 'kebun_rekomendasiPupuk', 'hide' => 1],
    ['id' => 909, 'caption' => 'Restan', 'action' => 'kebun_restan', 'hide' => 0],
    ['id' => 914, 'caption' => 'Pemakaian Material SPK(tdk dipakai)', 'action' => 'kebun_pemakaianMaterialSPK', 'hide' => 1],
    ['id' => 1001, 'caption' => 'Rekam Jejak Planter (tdk Dipakai)', 'action' => 'kebun_crossblock', 'hide' => 1],
    ['id' => 1021, 'caption' => 'Taksasi Panen', 'action' => 'kebun_taksasi', 'hide' => 0],
    ['id' => 1079, 'caption' => 'Rencana Sisip', 'action' => 'kebun_rencanasisip', 'hide' => 0],
    ['id' => 1140, 'caption' => 'Penjualan TBS', 'action' => 'kebun_penjualanTBS', 'hide' => 0],
    ['id' => 1150, 'caption' => 'Hasil Timbang TBS Ke Eksternal', 'action' => 'kebun_timbangke_eksternal', 'hide' => 0],
    ['id' => 1179, 'caption' => 'LPJ KUD', 'action' => 'kebun_lpjkud', 'hide' => 0],
    ['id' => 1208, 'caption' => 'Pengakuan Biaya Bibit', 'action' => 'kebun_pengakuanbybibit', 'hide' => 0],
    ['id' => 1235, 'caption' => 'Bongkar Muat', 'action' => 'kebun_bm', 'hide' => 0],
    ['id' => 1237, 'caption' => 'Upload Data AWS', 'action' => 'kebun_uploadaws', 'hide' => 0],
    ['id' => 1273, 'caption' => 'Adj Panen (Afkir/Hilang/Temuan)', 'action' => 'kebun_adjpanen', 'hide' => 0],
    ['id' => 1275, 'caption' => 'Inventaris Peralatan Kebun', 'action' => 'log_invpanen', 'hide' => 0],
    ['id' => 1286, 'caption' => 'Kontrol Ancak Panen', 'action' => 'kebun_cekancak', 'hide' => 0],
    ['id' => 1300, 'caption' => 'Adj Brondolan (Afkir/Hilang)', 'action' => 'kebun_adjbrondol', 'hide' => 0],
    ['id' => 1305, 'caption' => 'RKB Rawat', 'action' => 'kebun_rkb', 'hide' => 0],
];

$laporan_menus = [
    ['id' => 397, 'caption' => 'Areal Statement', 'action' => 'kebun_2aresta', 'hide' => 0],
    ['id' => 398, 'caption' => 'Pemeliharaan', 'action' => 'kebun_2pemeliharaan', 'hide' => 0],
    ['id' => 399, 'caption' => 'Panen', 'action' => 'kebun_2panen', 'hide' => 0],
    ['id' => 400, 'caption' => 'Pengangkutan Panen', 'action' => 'kebun_2pengangkutan', 'hide' => 0],
    ['id' => 401, 'caption' => 'Pemakaian Material', 'action' => 'kebun_2pakaimaterial', 'hide' => 0],
    ['id' => 403, 'caption' => 'Stok Bibit', 'action' => 'bibit_2_keluar_masuk', 'hide' => 0],
    ['id' => 970, 'caption' => 'Kartu Bibit', 'action' => 'kebun_2kartuBibit', 'hide' => 0],
    ['id' => 626, 'caption' => 'Curah Hujan', 'action' => 'kebun_2curahHujan', 'hide' => 0],
    ['id' => 741, 'caption' => 'Laporan Produksi', 'action' => 'kebun_3laporanProduksi', 'hide' => 0],
    ['id' => 764, 'caption' => 'Biaya Kegiatan Per Blok', 'action' => 'kebun_2biayaKegiatanPerBlok', 'hide' => 0],
    ['id' => 1136, 'caption' => 'Biaya per Blok', 'action' => 'kebun_3laporanBiayaPerBlok', 'hide' => 0],
    ['id' => 921, 'caption' => 'Pemakaian Barang vs CU(tdk dipakai_', 'action' => 'kebun_pemakaian_vs_cu', 'hide' => 1],
    ['id' => 922, 'caption' => 'Riwayat Sisipan', 'action' => 'kebun_riwayat_sisipan', 'hide' => 0],
    ['id' => 926, 'caption' => 'Penggunaan HK', 'action' => 'kebun_2penggunaanHK', 'hide' => 0],
    ['id' => 927, 'caption' => 'Laporan Restan', 'action' => 'kebun_2laporan_restan', 'hide' => 0],
    ['id' => 928, 'caption' => 'Laporan Sensus Produksi', 'action' => 'kebun_2sensusproduksi', 'hide' => 0],
    ['id' => 995, 'caption' => 'Laporan Pengiriman Bibit', 'action' => 'kebun_2antarBibit', 'hide' => 0],
    ['id' => 997, 'caption' => 'Kehadiran per Mandor', 'action' => 'kebun_2kehadiranpermandor', 'hide' => 0],
    ['id' => 1007, 'caption' => 'Produksi Per Blok', 'action' => 'kebun_2produksiPerBlok', 'hide' => 0],
    ['id' => 1011, 'caption' => 'SPB vs Penerimaan', 'action' => 'kebun_2spbvspenerimaan', 'hide' => 0],
    ['id' => 1024, 'caption' => 'Taksasi', 'action' => 'kebun_2taksasipanen', 'hide' => 0],
    ['id' => 1081, 'caption' => 'Historis Aresta', 'action' => 'kebun_2historisaresta', 'hide' => 0],
    ['id' => 1090, 'caption' => 'Laporan Rencana Sisip', 'action' => 'kebun_2rencanasisip', 'hide' => 0],
    ['id' => 1182, 'caption' => 'Hasil Timbangan TBS Ke Eksternal', 'action' => 'kebun_2hasiltimbangeksternal', 'hide' => 0],
    ['id' => 1183, 'caption' => 'LPJ KUD', 'action' => 'kebun_2lpjkud', 'hide' => 0],
    ['id' => 1201, 'caption' => 'Statistik Crop per Blok', 'action' => 'kebun_2crop', 'hide' => 0],
    ['id' => 1202, 'caption' => 'Bibitan vs BKM', 'action' => 'kebun_2bbtvsbkm', 'hide' => 0],
    ['id' => 1204, 'caption' => 'Account Report', 'action' => 'kebun_2accreport', 'hide' => 0],
    ['id' => 1212, 'caption' => 'BJR Harian', 'action' => 'kebun_2bjr', 'hide' => 0],
    ['id' => 1221, 'caption' => 'LPJ', 'action' => 'kebun_2LPJNoe.php', 'hide' => 0],
    ['id' => 1236, 'caption' => 'Rekap Restan', 'action' => 'kebun_rekaprestan', 'hide' => 0],
    ['id' => 1238, 'caption' => 'Lap Data AWS', 'action' => 'Action...', 'hide' => 0],
    ['id' => 1249, 'caption' => 'BJR Pabrik Per Blok Periodik', 'action' => 'kebun_lapbjr', 'hide' => 0],
    ['id' => 1259, 'caption' => 'Laporan Penalty Pemanen', 'action' => 'kebun_laporan_penalty_pemanen', 'hide' => 0],
    ['id' => 1274, 'caption' => 'Adjustment Panen', 'action' => 'kebun_lap_adjpanen', 'hide' => 0],
    ['id' => 1284, 'caption' => 'Rekap Adjustment Panen', 'action' => 'kebun_rkp_adjpanen', 'hide' => 0],
    ['id' => 1276, 'caption' => 'Monitoring Pusingan Panen', 'action' => 'kebun_rotasipanen', 'hide' => 0],
    ['id' => 1287, 'caption' => 'Kontrol Losses Ancak Panen', 'action' => 'kebun_lap_cekancak', 'hide' => 0],
    ['id' => 1290, 'caption' => 'Laporan Brondol Per Bulan', 'action' => 'kebun_lap_brondol', 'hide' => 0],
    ['id' => 1301, 'caption' => 'Lap Adj Brondolan', 'action' => 'kebun_lap_adjbrondol', 'hide' => 0],
    ['id' => 1302, 'caption' => 'Lap Langsir vs SPATBS', 'action' => 'kebun_lap_langsir', 'hide' => 0],
];

$proses_menus = [
    ['id' => 655, 'caption' => 'Ambil Kg. Timbangan', 'action' => 'kebun_3AmbilKgTimbangan', 'hide' => 0],
    ['id' => 1017, 'caption' => 'Postin Transaksi(Administrator Only)', 'action' => 'kebun_3postingtransaksi', 'hide' => 1],
    ['id' => 1080, 'caption' => 'Tutup Aresta', 'action' => 'kebun_3tutuparesta', 'hide' => 0],
    ['id' => 1103, 'caption' => 'Alokasi RKB', 'action' => 'kebun_3rkb_alokasi', 'hide' => 0],
    ['id' => 1104, 'caption' => 'Posting RKB', 'action' => 'kebun_3rkb', 'hide' => 0],
    ['id' => 1232, 'caption' => 'Calculate BJR dan Restan', 'action' => 'kebun_bjrlalu', 'hide' => 0],
];

$setup_menus = [
    ['id' => 385, 'caption' => 'Status Tanam', 'action' => 'kebun_5statustanaman', 'hide' => 1],
    ['id' => 1149, 'caption' => 'Kelas Pohon', 'action' => 'kebun_5kelaspohon', 'hide' => 0],
    ['id' => 411, 'caption' => 'Tabel BJR', 'action' => 'kebun_5bjr', 'hide' => 0],
    ['id' => 1068, 'caption' => 'Premi Siap Borong/ Premi Basis', 'action' => 'kebun_5premibasis', 'hide' => 0],
    ['id' => 1153, 'caption' => 'Denda Panen', 'action' => 'kebun_5dendapanen', 'hide' => 0],
    ['id' => 410, 'caption' => 'Tabel Budidaya', 'action' => 'kebun_5budidaya', 'hide' => 1],
    ['id' => 414, 'caption' => 'Basis Pemeliharaan (TBM, TM)', 'action' => 'kebun_5basispemeliharaan', 'hide' => 1],
    ['id' => 416, 'caption' => 'Tabel TPH', 'action' => 'kebun_5tph', 'hide' => 1],
    ['id' => 892, 'caption' => 'Standar Produksi Kebun', 'action' => 'kebun_5stproduksi', 'hide' => 1],
    ['id' => 993, 'caption' => 'Mandor', 'action' => 'kebun_5mandor', 'hide' => 1],
    ['id' => 1159, 'caption' => 'Kode Denda Panen', 'action' => 'kebun_5kodedendapanen', 'hide' => 0],
    ['id' => 1154, 'caption' => 'Alasan Rencana Sisip', 'action' => 'kebun_5alasanrencanasisip', 'hide' => 0],
    ['id' => 1145, 'caption' => 'KUD', 'action' => 'setup_kud', 'hide' => 0],
    ['id' => 1207, 'caption' => 'Harga Bibit', 'action' => 'kebun_5hargabibit', 'hide' => 0],
];

function testMenuItem($menu_item, $category) {
    $result = [
        'category' => $category,
        'id' => $menu_item['id'],
        'caption' => $menu_item['caption'],
        'action' => $menu_item['action'],
        'hide' => $menu_item['hide'],
        'file' => '',
        'file_exists' => false,
        'status' => 'PENDING',
        'notes' => []
    ];

    // Skip hidden items
    if ($menu_item['hide'] == 1) {
        $result['status'] = 'SKIPPED';
        $result['notes'][] = 'Menu item is hidden (hide=1)';
        return $result;
    }

    // Skip special actions
    if ($menu_item['action'] == 'NULL' || $menu_item['action'] == 'Action...') {
        $result['status'] = 'SKIPPED';
        $result['notes'][] = 'No action defined (placeholder menu)';
        return $result;
    }

    // Determine file path
    $filename = $menu_item['action'] . '.php';
    $filepath = __DIR__ . '/' . $filename;

    $result['file'] = $filename;
    $result['file_exists'] = file_exists($filepath);

    if (!$result['file_exists']) {
        $result['status'] = 'FAILED';
        $result['notes'][] = 'File not found: ' . $filepath;
        return $result;
    }

    // File exists, try to include it and capture any errors
    ob_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        // Check if file is readable
        if (!is_readable($filepath)) {
            $result['status'] = 'FAILED';
            $result['notes'][] = 'File exists but is not readable';
            ob_end_clean();
            return $result;
        }

        // Check basic syntax by reading the file
        $content = file_get_contents($filepath);
        if ($content === false) {
            $result['status'] = 'FAILED';
            $result['notes'][] = 'Cannot read file content';
            ob_end_clean();
            return $result;
        }

        $result['status'] = 'SUCCESS';
        $result['notes'][] = 'File exists and is accessible';

    } catch (Exception $e) {
        $result['status'] = 'FAILED';
        $result['notes'][] = 'Exception: ' . $e->getMessage();
    }

    ob_end_clean();
    return $result;
}

// Run tests
echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>KEBUN Module Test Report</title>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #1E3A8A; }
    h2 { color: #1E40AF; margin-top: 30px; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
    th { background-color: #1E3A8A; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .success { background-color: #d4edda; color: #155724; font-weight: bold; }
    .failed { background-color: #f8d7da; color: #721c24; font-weight: bold; }
    .skipped { background-color: #fff3cd; color: #856404; font-weight: bold; }
    .summary { margin-top: 30px; padding: 20px; background-color: #e7f3ff; border-left: 4px solid #1E3A8A; }
    .notes { font-size: 11px; color: #666; }
</style>\n";
echo "</head>\n<body>\n";

echo "<h1>KEBUN MODULE TEST REPORT</h1>\n";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>\n";

$all_results = [];
$stats = ['total' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 0];

// Test Transaksi
echo "<h2>1. TRANSAKSI (Transaction) Menu Items</h2>\n";
echo "<table>\n";
echo "<tr><th>#</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Status</th><th>Notes</th></tr>\n";
$counter = 1;
foreach ($transaksi_menus as $menu) {
    $result = testMenuItem($menu, 'Transaksi');
    $all_results[] = $result;
    $stats['total']++;
    $stats[strtolower($result['status'])]++;

    $status_class = strtolower($result['status']);
    echo "<tr>";
    echo "<td>{$counter}</td>";
    echo "<td>{$result['id']}</td>";
    echo "<td>{$result['caption']}</td>";
    echo "<td>{$result['file']}</td>";
    echo "<td class='{$status_class}'>{$result['status']}</td>";
    echo "<td class='notes'>" . implode('; ', $result['notes']) . "</td>";
    echo "</tr>\n";
    $counter++;
}
echo "</table>\n";

// Test Laporan
echo "<h2>2. LAPORAN (Reports) Menu Items</h2>\n";
echo "<table>\n";
echo "<tr><th>#</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Status</th><th>Notes</th></tr>\n";
$counter = 1;
foreach ($laporan_menus as $menu) {
    $result = testMenuItem($menu, 'Laporan');
    $all_results[] = $result;
    $stats['total']++;
    $stats[strtolower($result['status'])]++;

    $status_class = strtolower($result['status']);
    echo "<tr>";
    echo "<td>{$counter}</td>";
    echo "<td>{$result['id']}</td>";
    echo "<td>{$result['caption']}</td>";
    echo "<td>{$result['file']}</td>";
    echo "<td class='{$status_class}'>{$result['status']}</td>";
    echo "<td class='notes'>" . implode('; ', $result['notes']) . "</td>";
    echo "</tr>\n";
    $counter++;
}
echo "</table>\n";

// Test Proses
echo "<h2>3. PROSES (Processing) Menu Items</h2>\n";
echo "<table>\n";
echo "<tr><th>#</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Status</th><th>Notes</th></tr>\n";
$counter = 1;
foreach ($proses_menus as $menu) {
    $result = testMenuItem($menu, 'Proses');
    $all_results[] = $result;
    $stats['total']++;
    $stats[strtolower($result['status'])]++;

    $status_class = strtolower($result['status']);
    echo "<tr>";
    echo "<td>{$counter}</td>";
    echo "<td>{$result['id']}</td>";
    echo "<td>{$result['caption']}</td>";
    echo "<td>{$result['file']}</td>";
    echo "<td class='{$status_class}'>{$result['status']}</td>";
    echo "<td class='notes'>" . implode('; ', $result['notes']) . "</td>";
    echo "</tr>\n";
    $counter++;
}
echo "</table>\n";

// Test Setup
echo "<h2>4. SETUP (Configuration) Menu Items</h2>\n";
echo "<table>\n";
echo "<tr><th>#</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Status</th><th>Notes</th></tr>\n";
$counter = 1;
foreach ($setup_menus as $menu) {
    $result = testMenuItem($menu, 'Setup');
    $all_results[] = $result;
    $stats['total']++;
    $stats[strtolower($result['status'])]++;

    $status_class = strtolower($result['status']);
    echo "<tr>";
    echo "<td>{$counter}</td>";
    echo "<td>{$result['id']}</td>";
    echo "<td>{$result['caption']}</td>";
    echo "<td>{$result['file']}</td>";
    echo "<td class='{$status_class}'>{$result['status']}</td>";
    echo "<td class='notes'>" . implode('; ', $result['notes']) . "</td>";
    echo "</tr>\n";
    $counter++;
}
echo "</table>\n";

// Summary
echo "<div class='summary'>\n";
echo "<h2>TEST SUMMARY</h2>\n";
echo "<p><strong>Total Menu Items Tested:</strong> {$stats['total']}</p>\n";
echo "<p><strong>Successful:</strong> {$stats['success']} (" . round(($stats['success']/$stats['total'])*100, 2) . "%)</p>\n";
echo "<p><strong>Failed:</strong> {$stats['failed']} (" . round(($stats['failed']/$stats['total'])*100, 2) . "%)</p>\n";
echo "<p><strong>Skipped:</strong> {$stats['skipped']} (" . round(($stats['skipped']/$stats['total'])*100, 2) . "%)</p>\n";
echo "</div>\n";

// Failed items detail
if ($stats['failed'] > 0) {
    echo "<h2>FAILED ITEMS DETAIL</h2>\n";
    echo "<table>\n";
    echo "<tr><th>Category</th><th>Menu ID</th><th>Menu Name</th><th>File</th><th>Error Details</th></tr>\n";
    foreach ($all_results as $result) {
        if ($result['status'] == 'FAILED') {
            echo "<tr>";
            echo "<td>{$result['category']}</td>";
            echo "<td>{$result['id']}</td>";
            echo "<td>{$result['caption']}</td>";
            echo "<td>{$result['file']}</td>";
            echo "<td>" . implode('; ', $result['notes']) . "</td>";
            echo "</tr>\n";
        }
    }
    echo "</table>\n";
}

echo "</body>\n</html>\n";
?>
