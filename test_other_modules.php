<?
// Test script for all other modules
session_start();
$_SESSION['namauser'] = 'kingking.firdaus';
$_SESSION['access_type'] = 'detail';

require_once('config/connection.php');

// Define all modules to test
$modules = array(
    'SETUP' => array(
        array('name' => 'Periode Akuntansi', 'file' => 'setup_periodeakuntansi.php'),
        array('name' => 'Mata Uang dan Kurs', 'file' => 'setup_mtuang.php'),
        array('name' => 'Blok', 'file' => 'setup_blok.php'),
        array('name' => 'Kegiatan', 'file' => 'setup_kegiatan.php'),
        array('name' => 'Kelompok Kegiatan', 'file' => 'setup_klpkegiatan.php'),
        array('name' => 'Satuan Barang', 'file' => 'setup_satuan.php'),
        array('name' => 'Jenis Bibit', 'file' => 'setup_jenisBibit.php'),
        array('name' => 'Parameter Aplikasi', 'file' => 'setup_parameterappl.php'),
        array('name' => 'IP Timbangan', 'file' => 'setup_remoteTimbangan.php'),
        array('name' => 'Posting', 'file' => 'setup_posting.php'),
        array('name' => 'Pindah Lokasi Tugas', 'file' => 'setup_pindahLokasiTugas.php'),
        array('name' => 'Approval', 'file' => 'setup_approval.php')
    ),
    'TRAKSI' => array(
        array('name' => 'Master', 'file' => 'budget_master.php'),
        array('name' => 'Upah-TRK', 'file' => 'budget_upah.php'),
        array('name' => '1. Total Jam Bengkel', 'file' => 'budget_traksi_total_jam_bengkel.php'),
        array('name' => '2. Total Alokasi Jam Kendaraan', 'file' => 'budget_total_jam_vhc.php'),
        array('name' => '3. Budget Biaya Bengkel', 'file' => 'budget_ws_biaya.php'),
        array('name' => '4. Budget Kendaraan-Mesin-Alat Berat', 'file' => 'budget_vhc.php'),
        array('name' => '5. Biaya Umum', 'file' => 'budget_by_umum.php'),
        array('name' => 'Biaya Bengkel', 'file' => 'bgt_laporan_biaya_bengkel.php'),
        array('name' => 'Biaya Kendaraan', 'file' => 'bgt_laporan_biaya_kendaraan.php'),
        array('name' => 'Daftar Kendaraan', 'file' => 'bgt_laporan_daftar_kendaraan.php')
    ),
    'ANGGARAN' => array(),
    'UMUM' => array(
        array('name' => 'Perumahan', 'file' => 'sdm_perumahan.php'),
        array('name' => 'Prasarana', 'file' => 'sdm_prasarana.php'),
        array('name' => 'Kondisi Prasarana', 'file' => 'sdm_5kondisi_prasarana.php'),
        array('name' => 'Preventive Maintenance', 'file' => 'sdm_preventivemaintenance.php'),
        array('name' => 'Reservasi Ruang Rapat', 'file' => 'sdm_ruangrapat.php'),
        array('name' => 'User OWL', 'file' => 'sdm_2userowl.php'),
        array('name' => 'File Upload(Data)', 'file' => 'rencana_gis.php'),
        array('name' => 'Struktur Unit', 'file' => 'master_laporan_organisasi.php'),
        array('name' => 'Login Report', 'file' => 'sdm_2loginreport.php')
    ),
    'PAD/GIS' => array(
        array('name' => 'Rencana GRTT', 'file' => '.')
    ),
    'GUDANG LAPORAN' => array(
        array('name' => 'LBM-Kebun', 'file' => 'lbm_main.php'),
        array('name' => 'LHD -Kebun', 'file' => 'lha_main.php'),
        array('name' => 'LBM-PKS', 'file' => 'lbm_main_pks.php'),
        array('name' => 'LBM-Procurement', 'file' => 'lbm_main_procurement.php'),
        array('name' => 'LBM-HRD', 'file' => 'lbm_hrd.php'),
        array('name' => 'Transaksi Belum Posting', 'file' => 'kebun_lapposting.php')
    ),
    'MY ACCOUNT' => array(
        array('name' => 'Change Password', 'file' => 'main_changePassword.php'),
        array('name' => 'Show Home', 'file' => 'master.php')
    ),
    'HELP' => array(
        array('name' => 'Tambah', 'file' => 'help_tambah.php'),
        array('name' => 'Bantuan', 'file' => 'help_bantuan.php')
    ),
    'IT' => array(
        array('name' => 'Request Management', 'file' => 'it_requestManagement.php'),
        array('name' => 'Request Response', 'file' => 'it_requestResponse.php'),
        array('name' => 'Permintaan Layanan', 'file' => 'it_permintaanUser.php'),
        array('name' => 'Prestasi Staf IT', 'file' => 'it_2prestasi.php')
    )
);

// Get ANGGARAN items from database
$str = "SELECT caption, action FROM menu WHERE parent=771 AND hide=0 ORDER BY urut";
$res = mysql_query($str);
$modules['ANGGARAN'] = array();
while($row = mysql_fetch_object($res)) {
    if($row->action && $row->action != '') {
        $modules['ANGGARAN'][] = array('name' => $row->caption, 'file' => $row->action . '.php');
    }
}

$results = array();
$totalModules = 0;
$totalItems = 0;
$totalSuccess = 0;
$totalFailed = 0;

foreach($modules as $moduleName => $menuItems) {
    $totalModules++;
    $results[$moduleName] = array();

    foreach($menuItems as $item) {
        $totalItems++;
        $menuName = $item['name'];
        $fileName = $item['file'];

        $testResult = array(
            'menu_name' => $menuName,
            'file' => $fileName,
            'status' => 'UNKNOWN',
            'file_exists' => false,
            'php_errors' => array(),
            'notes' => array()
        );

        // Check if file exists
        if($fileName == '.' || $fileName == 'x' || $fileName == '') {
            $testResult['status'] = 'SKIPPED';
            $testResult['notes'][] = 'No file specified';
            $totalFailed++;
        } else {
            $fullPath = __DIR__ . '/' . $fileName;
            if(file_exists($fullPath)) {
                $testResult['file_exists'] = true;

                // Try to check for basic PHP syntax errors
                ob_start();
                $output = shell_exec("php -l \"$fullPath\" 2>&1");
                ob_end_clean();

                if(strpos($output, 'No syntax errors') !== false) {
                    $testResult['status'] = 'SUCCESS';
                    $totalSuccess++;
                } else {
                    $testResult['status'] = 'PHP_SYNTAX_ERROR';
                    $testResult['php_errors'][] = $output;
                    $totalFailed++;
                }
            } else {
                $testResult['status'] = 'FILE_NOT_FOUND';
                $testResult['notes'][] = "File does not exist: $fullPath";
                $totalFailed++;
            }
        }

        $results[$moduleName][] = $testResult;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Other Modules Test Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-success { background-color: #d4edda; }
        .status-failed { background-color: #f8d7da; }
        .status-skipped { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4">OTHER MODULES TEST REPORT</h1>
        <p class="text-muted">Generated: <?= date('Y-m-d H:i:s') ?></p>

        <? foreach($results as $moduleName => $moduleResults): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">MODULE: <?= $moduleName ?></h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Menu Name</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>File Exists</th>
                                <th>PHP Errors</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach($moduleResults as $result):
                                $rowClass = '';
                                if($result['status'] == 'SUCCESS') $rowClass = 'status-success';
                                elseif($result['status'] == 'SKIPPED') $rowClass = 'status-skipped';
                                else $rowClass = 'status-failed';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= htmlspecialchars($result['menu_name']) ?></td>
                                <td><code><?= htmlspecialchars($result['file']) ?></code></td>
                                <td><strong><?= $result['status'] ?></strong></td>
                                <td><?= $result['file_exists'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <? if(!empty($result['php_errors'])): ?>
                                        <pre class="mb-0" style="font-size: 0.8rem;"><?= htmlspecialchars(implode("\n", $result['php_errors'])) ?></pre>
                                    <? else: ?>
                                        None
                                    <? endif; ?>
                                </td>
                                <td>
                                    <? if(!empty($result['notes'])): ?>
                                        <?= htmlspecialchars(implode(", ", $result['notes'])) ?>
                                    <? else: ?>
                                        -
                                    <? endif; ?>
                                </td>
                            </tr>
                            <? endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?
                $moduleSuccess = count(array_filter($moduleResults, create_function('$r', 'return $r["status"] == "SUCCESS";')));
                $moduleFailed = count(array_filter($moduleResults, create_function('$r', 'return $r["status"] != "SUCCESS" && $r["status"] != "SKIPPED";')));
                $moduleSkipped = count(array_filter($moduleResults, create_function('$r', 'return $r["status"] == "SKIPPED";')));
                ?>
                <div class="alert alert-info">
                    <strong>Module Summary:</strong>
                    Total: <?= count($moduleResults) ?> |
                    Success: <?= $moduleSuccess ?> |
                    Failed: <?= $moduleFailed ?> |
                    Skipped: <?= $moduleSkipped ?>
                </div>
            </div>
        </div>
        <? endforeach; ?>

        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h2 class="h4">OVERALL SUMMARY</h2>
                <ul class="list-unstyled mb-0">
                    <li><strong>Total Modules Tested:</strong> <?= $totalModules ?></li>
                    <li><strong>Total Menu Items Tested:</strong> <?= $totalItems ?></li>
                    <li><strong>Total Successful:</strong> <?= $totalSuccess ?></li>
                    <li><strong>Total Failed:</strong> <?= $totalFailed ?></li>
                    <li><strong>Success Rate:</strong> <?= $totalItems > 0 ? round(($totalSuccess / $totalItems) * 100, 2) : 0 ?>%</li>
                </ul>

                <h3 class="h5 mt-4">Failed Items:</h3>
                <?
                $failedItems = array();
                foreach($results as $moduleName => $moduleResults) {
                    foreach($moduleResults as $result) {
                        if($result['status'] != 'SUCCESS' && $result['status'] != 'SKIPPED') {
                            $failedItems[] = array(
                                'module' => $moduleName,
                                'menu' => $result['menu_name'],
                                'file' => $result['file'],
                                'status' => $result['status'],
                                'errors' => $result['php_errors'],
                                'notes' => $result['notes']
                            );
                        }
                    }
                }
                ?>
                <? if(count($failedItems) > 0): ?>
                <ol>
                    <? foreach($failedItems as $failed): ?>
                    <li>
                        <strong><?= $failed['module'] ?> - <?= htmlspecialchars($failed['menu']) ?></strong><br>
                        File: <code><?= htmlspecialchars($failed['file']) ?></code><br>
                        Status: <?= $failed['status'] ?><br>
                        <? if(!empty($failed['errors'])): ?>
                            Errors: <pre><?= htmlspecialchars(implode("\n", $failed['errors'])) ?></pre>
                        <? endif; ?>
                        <? if(!empty($failed['notes'])): ?>
                            Notes: <?= htmlspecialchars(implode(", ", $failed['notes'])) ?>
                        <? endif; ?>
                    </li>
                    <? endforeach; ?>
                </ol>
                <? else: ?>
                <p class="text-success">No failed items!</p>
                <? endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
