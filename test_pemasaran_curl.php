<?php
// Simple HTTP test for all PEMASARAN menu items
$base_url = 'http://localhost/erpmill/';

// Cookie for session (you'll need to update this after login)
$cookie_file = __DIR__ . '/cookie.txt';

// Menu items to test
$menu_items = array(
    'Transaksi' => array(
        'Kontrak Penjualan' => 'pmn_kontrakjual.php',
        'Faktur Pajak' => 'pmn_pajak.php',
        'Harga Pasar Palm Product' => 'pmn_hargaPasar.php',
        'Surat Perintah Pengiriman' => 'pmn_suratperintahpengiriman.php',
        'Upload Harga Pasar' => 'pmn_uploadhargapasar.php',
        'Kontrak External' => 'pmn_trader.php',
        'Kirim dari External' => 'pmn_deliveryext.php',
        'Berita Acara Jual Beli' => 'pmn_bajualbeli.php'
    ),
    'Laporan' => array(
        'Penjualan' => 'pmn_2penjualan.php',
        'Stok CPO/PK' => 'pabrik_4persediaan.php',
        'Pemenuhan Kontrak' => 'pmn_laporanPemenuhanKontrak.php',
        'Harga Harian Palm Product' => 'pmn_2hargaharian.php',
        'Realisasi Pengiriman Per transportir' => 'pmn_2transportir.php',
        'Rekap SPP' => 'pmn_2suratperintahpengiriman.php',
        'tidak terpakai' => 'pmn_2rekapdo.php',
        'Rekap Kontrak, Invoice dan Faktur' => 'pmn_2rekapkontrak.php',
        'Rekap Penjualan' => 'pmn_2rekappenjualan.php',
        'Laporan Kontrak External' => 'pmn_laporantrader.php',
        'Laporan Hutang Transportir' => 'pmn_laphutangtransportir.php',
        'Lap. Penjualan Per Kontrak' => 'pmn_lapjualperkontrak.php',
        'Outstanding Kontrak Penjualan' => 'pmn_lap_os_penjualan.php'
    ),
    'Setup' => array(
        'Kelompok Pelanggan' => 'pmn_5klcustomer.php',
        'Pelanggan' => 'pmn_5customer.php',
        'Kode Pengenaan Pajak' => 'pmn_5kodePengenaanPajak.php',
        'Tempat Penyerahan' => 'pmn_5franco.php',
        'Termin Bayar' => 'pmn_5terminbayar.php',
        'Transportir' => 'pmn_5transportir.php',
        'Daftar Pasar' => 'pmn_5pasar.php',
        'TTD' => 'pmn_5ttd.php',
        'Tujuan Pengiriman Surat DO' => 'pmn_5kepada.php'
    )
);

$results = array();
$total = 0;
$success = 0;
$failed = 0;

foreach ($menu_items as $category => $items) {
    foreach ($items as $name => $file) {
        $total++;
        $url = $base_url . $file;
        $file_path = __DIR__ . '/' . $file;

        $result = array(
            'category' => $category,
            'name' => $name,
            'url' => $url,
            'file' => $file,
            'file_exists' => file_exists($file_path),
            'http_code' => 'N/A',
            'status' => 'UNKNOWN',
            'errors' => array(),
            'notes' => array()
        );

        // Check if file exists
        if (!file_exists($file_path)) {
            $result['status'] = 'FAILED';
            $result['errors'][] = 'File not found';
            $failed++;
        } else {
            // File exists
            $result['status'] = 'SUCCESS';
            $result['notes'][] = 'File exists';
            $success++;
        }

        $results[] = $result;
    }
}

// Output JSON
header('Content-Type: application/json');
echo json_encode(array(
    'summary' => array(
        'total' => $total,
        'success' => $success,
        'failed' => $failed,
        'success_rate' => round(($success / $total) * 100, 2)
    ),
    'results' => $results
));
?>
