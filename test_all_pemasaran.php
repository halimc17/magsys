<?php
// Comprehensive test script for all PEMASARAN menu items
require_once('config/connection.php');
session_start();

// Set session variables
$_SESSION['namauser'] = 'kingking.firdaus';
$_SESSION['language'] = 'ID';
$_SESSION['access_type'] = 'detail';
$_SESSION['access_level'] = 1;

// Define all menu items to test
$menu_items = array(
    'Transaksi' => array(
        array('caption' => 'Kontrak Penjualan', 'file' => 'pmn_kontrakjual.php'),
        array('caption' => 'Faktur Pajak', 'file' => 'pmn_pajak.php'),
        array('caption' => 'Harga Pasar Palm Product', 'file' => 'pmn_hargaPasar.php'),
        array('caption' => 'Surat Perintah Pengiriman', 'file' => 'pmn_suratperintahpengiriman.php'),
        array('caption' => 'Upload Harga Pasar', 'file' => 'pmn_uploadhargapasar.php'),
        array('caption' => 'Kontrak External', 'file' => 'pmn_trader.php'),
        array('caption' => 'Kirim dari External', 'file' => 'pmn_deliveryext.php'),
        array('caption' => 'Berita Acara Jual Beli', 'file' => 'pmn_bajualbeli.php')
    ),
    'Laporan' => array(
        array('caption' => 'Penjualan', 'file' => 'pmn_2penjualan.php'),
        array('caption' => 'Stok CPO/PK', 'file' => 'pabrik_4persediaan.php'),
        array('caption' => 'Pemenuhan Kontrak', 'file' => 'pmn_laporanPemenuhanKontrak.php'),
        array('caption' => 'Harga Harian Palm Product', 'file' => 'pmn_2hargaharian.php'),
        array('caption' => 'Realisasi Pengiriman Per transportir', 'file' => 'pmn_2transportir.php'),
        array('caption' => 'Rekap SPP', 'file' => 'pmn_2suratperintahpengiriman.php'),
        array('caption' => 'tidak terpakai', 'file' => 'pmn_2rekapdo.php'),
        array('caption' => 'Rekap Kontrak, Invoice dan Faktur', 'file' => 'pmn_2rekapkontrak.php'),
        array('caption' => 'Rekap Penjualan', 'file' => 'pmn_2rekappenjualan.php'),
        array('caption' => 'Laporan Kontrak External', 'file' => 'pmn_laporantrader.php'),
        array('caption' => 'Laporan Hutang Transportir', 'file' => 'pmn_laphutangtransportir.php'),
        array('caption' => 'Lap. Penjualan Per Kontrak', 'file' => 'pmn_lapjualperkontrak.php'),
        array('caption' => 'Outstanding Kontrak Penjualan', 'file' => 'pmn_lap_os_penjualan.php')
    ),
    'Setup' => array(
        array('caption' => 'Kelompok Pelanggan', 'file' => 'pmn_5klcustomer.php'),
        array('caption' => 'Pelanggan', 'file' => 'pmn_5customer.php'),
        array('caption' => 'Kode Pengenaan Pajak', 'file' => 'pmn_5kodePengenaanPajak.php'),
        array('caption' => 'Tempat Penyerahan', 'file' => 'pmn_5franco.php'),
        array('caption' => 'Termin Bayar', 'file' => 'pmn_5terminbayar.php'),
        array('caption' => 'Transportir', 'file' => 'pmn_5transportir.php'),
        array('caption' => 'Daftar Pasar', 'file' => 'pmn_5pasar.php'),
        array('caption' => 'TTD', 'file' => 'pmn_5ttd.php'),
        array('caption' => 'Tujuan Pengiriman Surat DO', 'file' => 'pmn_5kepada.php'),
        array('caption' => 'Franco', 'file' => 'pmn_5franco.php')
    )
);

$results = array();
$total_tested = 0;
$total_success = 0;
$total_failed = 0;

// Test each menu item
foreach($menu_items as $category => $items) {
    foreach($items as $item) {
        $total_tested++;
        $file_path = __DIR__ . '/' . $item['file'];
        $url = 'http://localhost/erpmill/' . $item['file'];

        $test_result = array(
            'category' => $category,
            'menu_name' => $item['caption'],
            'file' => $item['file'],
            'url' => $url,
            'file_exists' => file_exists($file_path),
            'status' => 'UNKNOWN',
            'errors' => array()
        );

        // Check if file exists
        if (!file_exists($file_path)) {
            $test_result['status'] = 'FAILED';
            $test_result['errors'][] = 'File not found: ' . $file_path;
            $total_failed++;
        } else {
            // Try to check for syntax errors
            $output = array();
            $return_var = 0;
            exec('php -l "' . $file_path . '" 2>&1', $output, $return_var);

            if ($return_var !== 0) {
                $test_result['status'] = 'FAILED';
                $test_result['errors'][] = 'PHP syntax error: ' . implode(', ', $output);
                $total_failed++;
            } else {
                $test_result['status'] = 'SUCCESS';
                $total_success++;
            }
        }

        $results[] = $test_result;
    }
}

// Generate HTML report
?>
<!DOCTYPE html>
<html>
<head>
    <title>PEMASARAN Module Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #1E3A8A; }
        h2 { color: #1E40AF; margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #1E3A8A; color: white; }
        .success { background-color: #d4edda; color: #155724; }
        .failed { background-color: #f8d7da; color: #721c24; }
        .summary { background-color: #f9fafb; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .category-header { background-color: #1E40AF; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <h1>PEMASARAN MODULE TEST REPORT</h1>
    <p>Test Date: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p>Tested By: Automated Test Script</p>

    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Menu Items Tested:</strong> <?php echo $total_tested; ?></p>
        <p><strong>Total Successful:</strong> <?php echo $total_success; ?></p>
        <p><strong>Total Failed:</strong> <?php echo $total_failed; ?></p>
        <p><strong>Success Rate:</strong> <?php echo round(($total_success / $total_tested) * 100, 2); ?>%</p>
    </div>

    <h2>Detailed Test Results</h2>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Category</th>
                <th>Menu Name</th>
                <th>URL</th>
                <th>Status</th>
                <th>Errors/Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $current_category = '';
            foreach($results as $result):
                if ($current_category != $result['category']) {
                    $current_category = $result['category'];
                    echo '<tr class="category-header"><td colspan="6">' . strtoupper($result['category']) . '</td></tr>';
                }
            ?>
            <tr class="<?php echo strtolower($result['status']); ?>">
                <td><?php echo $no++; ?></td>
                <td><?php echo $result['category']; ?></td>
                <td><?php echo $result['menu_name']; ?></td>
                <td><a href="<?php echo $result['url']; ?>" target="_blank"><?php echo $result['url']; ?></a></td>
                <td><strong><?php echo $result['status']; ?></strong></td>
                <td>
                    <?php
                    if (!empty($result['errors'])) {
                        echo implode('<br>', $result['errors']);
                    } else {
                        echo 'None';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Failed Items Detail</h2>
    <?php
    $failed_items = array_filter($results, function($r) { return $r['status'] == 'FAILED'; });
    if (count($failed_items) > 0):
    ?>
    <ul>
        <?php foreach($failed_items as $item): ?>
        <li>
            <strong><?php echo $item['menu_name']; ?></strong> (<?php echo $item['category']; ?>)<br>
            File: <?php echo $item['file']; ?><br>
            Errors: <?php echo implode(', ', $item['errors']); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p style="color: green;">No failed items! All menu items passed the test.</p>
    <?php endif; ?>

</body>
</html>
