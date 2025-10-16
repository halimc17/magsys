<?php
// Test script to check all PENGADAAN menu items
require_once('config/connection.php');

$menuItems = [
    // TRANSAKSI - Main level
    ['id' => 282, 'name' => 'Permintaan Pembelian', 'file' => 'log_pp.php', 'parent' => 'Transaksi'],
    ['id' => 519, 'name' => 'Persetujuan Permintaan Pembelian', 'file' => 'log_persetuuanPp.php', 'parent' => 'Transaksi'],
    ['id' => 288, 'name' => 'Kontrak Perintah Kerja', 'file' => 'log_spk.php', 'parent' => 'Transaksi'],
    ['id' => 289, 'name' => 'BA Pelaksanaan Pekerjaan', 'file' => 'log_realisasispk.php', 'parent' => 'Transaksi'],
    ['id' => 1270, 'name' => 'Inventaris Barang', 'file' => 'log_invbarang.php', 'parent' => 'Transaksi'],

    // TRANSAKSI > Purchasing
    ['id' => 518, 'name' => 'Verifikasi PP', 'file' => 'log_verifikasiPp.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 283, 'name' => 'Riwayat Perbandingan Harga', 'file' => 'log_pnwrharga.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 1174, 'name' => 'Perbandingan Harga', 'file' => 'log_cmpharga.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 526, 'name' => 'Persetujuan Order Pembelian', 'file' => 'log_persetujuan_po.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 284, 'name' => 'PO Pusat', 'file' => 'log_po.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 567, 'name' => 'PO Lokal', 'file' => 'log_POLokal.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 528, 'name' => 'PO Release', 'file' => 'log_release_po.php', 'parent' => 'Transaksi > Purchasing'],
    ['id' => 530, 'name' => 'Cetak PO', 'file' => 'log_cetak_po.php', 'parent' => 'Transaksi > Purchasing'],

    // TRANSAKSI > Administrasi Gudang
    ['id' => 285, 'name' => 'Penerimaan Barang Dari Supplier', 'file' => 'log_penerimaanBarang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 286, 'name' => 'Mutasi Barang', 'file' => 'log_mutasibarang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 513, 'name' => 'Penerimaan Barang Mutasi', 'file' => 'log_penerimaanMutasi.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 287, 'name' => 'Pemakaian Barang', 'file' => 'log_pakaibarang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 618, 'name' => 'Retur Ke Gudang', 'file' => 'log_returKeGudang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 524, 'name' => 'Posting', 'file' => 'log_postingGudang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 972, 'name' => 'Retur Ke Supplier', 'file' => 'log_returKeSupplier.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 1056, 'name' => 'Rekalkulasi Stock (tidak dipakai)', 'file' => 'log_rekalgudang.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 1142, 'name' => 'Pembebanan Biaya Pengiriman', 'file' => 'log_biayakirim.php', 'parent' => 'Transaksi > Administrasi Gudang'],
    ['id' => 1283, 'name' => 'Pemakaian Bahan Baku ke Bahan Jadi', 'file' => 'log_brgjadi.php', 'parent' => 'Transaksi > Administrasi Gudang'],

    // LAPORAN
    ['id' => 380, 'name' => 'Persediaan Fisik', 'file' => 'log_2persediaanFisik.php', 'parent' => 'Laporan'],
    ['id' => 527, 'name' => 'Persediaan Fisik dan Harga', 'file' => 'log_2persediaanFisikHarga.php', 'parent' => 'Laporan'],
    ['id' => 381, 'name' => 'Keluar / Masuk Persediaan', 'file' => 'log_2keluarmasukbrg.php', 'parent' => 'Laporan'],
    ['id' => 629, 'name' => 'Riwayat Permintaan Barang', 'file' => 'log_2riwayat_baru.php', 'parent' => 'Laporan'],
    ['id' => 747, 'name' => 'Daftar PO', 'file' => 'log_2daftarPo.php', 'parent' => 'Laporan'],
    ['id' => 382, 'name' => 'Alokasi Biaya Pembelian', 'file' => 'log_2alokasibiaya.php', 'parent' => 'Laporan'],
    ['id' => 1184, 'name' => 'Alokasi Pemakaian Barang', 'file' => 'log_2pemakaianbarang.php', 'parent' => 'Laporan'],
    ['id' => 512, 'name' => 'Daftar Gudang', 'file' => 'log_5daftarGudang.php', 'parent' => 'Laporan'],
    ['id' => 752, 'name' => 'Hutang Berdasarkan BPB', 'file' => 'log_2hutangsupplier.php', 'parent' => 'Laporan'],
    ['id' => 753, 'name' => 'Laporan Alokasi Pemakaian Barang', 'file' => 'log_2alokasi_pemakaiBrg.php', 'parent' => 'Laporan'],
    ['id' => 757, 'name' => 'Penerimaan-Pengeluaran/Barang', 'file' => 'log_2transaksigudang.php', 'parent' => 'Laporan'],
    ['id' => 789, 'name' => 'Daftar Penerimaan Barang', 'file' => 'log_2penerimaan.php', 'parent' => 'Laporan'],
    ['id' => 911, 'name' => 'Mutasi Stock', 'file' => 'log_2kalkulasi_stock.php', 'parent' => 'Laporan'],
    ['id' => 913, 'name' => 'Realisasi PK', 'file' => 'log_laporanRealisasiSPK.php', 'parent' => 'Laporan'],
    ['id' => 920, 'name' => 'Summary Progress PK', 'file' => 'summary_progress_spk.php', 'parent' => 'Laporan'],
    ['id' => 1055, 'name' => 'Gudang Vs Accounting', 'file' => 'log_2gdangAccounting.php', 'parent' => 'Laporan'],
    ['id' => 1077, 'name' => 'PO yang dibatalkan', 'file' => 'log_2daftarPo_batal.php', 'parent' => 'Laporan'],
    ['id' => 1097, 'name' => 'Daftar Barang', 'file' => 'log_2daftarbarang.php', 'parent' => 'Laporan'],
    ['id' => 1138, 'name' => 'Penerimaan Barang Inventaris', 'file' => 'log_2pengeluaranBarangInventaris.php', 'parent' => 'Laporan'],
    ['id' => 1148, 'name' => 'Reminder Stok', 'file' => 'log_2rb.php', 'parent' => 'Laporan'],
    ['id' => 1211, 'name' => 'Daftar Supplier', 'file' => 'log_2skc.php', 'parent' => 'Laporan'],
    ['id' => 1285, 'name' => 'Daftar SPK', 'file' => 'log_lap_spk.php', 'parent' => 'Laporan'],

    // LAPORAN > Purchasing
    ['id' => 742, 'name' => 'Detail Pembelian', 'file' => 'log_2detail_pembelian.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 760, 'name' => 'Detail Pembelian Per Barang', 'file' => 'log_2detail_pembelian_brg.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 880, 'name' => 'Laporan PP', 'file' => 'log_2pp_histori.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 933, 'name' => 'Laporan Status PO', 'file' => 'log_2laporan_statuspo.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 821, 'name' => 'Perbandingan Harga', 'file' => 'log_2perbandingan_harga.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 817, 'name' => 'Pembelian Terakhir', 'file' => 'log_2pembelian_terakhir.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 939, 'name' => 'Laporan Produktivitas', 'file' => 'log_2produktivitas.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 965, 'name' => 'Laporan Status Pengiriman Barang', 'file' => 'log_2posisiBarang.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 986, 'name' => 'Riwayat Pembayaran', 'file' => 'log_2pembayaran.php', 'parent' => 'Laporan > Purchasing'],
    ['id' => 1003, 'name' => 'PP BLM Realisasi', 'file' => 'lbm_proc_pprealisasi.php', 'parent' => 'Laporan > Purchasing'],

    // PROSES
    ['id' => 1214, 'name' => '1. Integrity Check BKM (Sblm tutup buku)', 'file' => 'log_3integrity.php', 'parent' => 'Proses'],
    ['id' => 788, 'name' => '2. Rekalkulasi Stock', 'file' => 'log_3rekalkulasi_stock.php', 'parent' => 'Proses'],
    ['id' => 525, 'name' => '3. Tutup Buku Fisik', 'file' => 'log_pindahPeriodeGudang.php', 'parent' => 'Proses'],
    ['id' => 384, 'name' => 'Perhitungan Harga Akhir Bulan', 'file' => 'log_3prosesAkhirBulan.php', 'parent' => 'Proses'],

    // SETUP
    ['id' => 392, 'name' => 'Kelompok Barang', 'file' => 'log_5kelompokbarang.php', 'parent' => 'Setup'],
    ['id' => 1188, 'name' => 'Sub Kelompok Barang', 'file' => 'log_5subkelompokbarang.php', 'parent' => 'Setup'],
    ['id' => 394, 'name' => 'Master Barang', 'file' => 'log_5masterbarang.php', 'parent' => 'Setup'],
    ['id' => 393, 'name' => 'Konversi Satuan', 'file' => 'log_5satuankonversi.php', 'parent' => 'Setup'],
    ['id' => 395, 'name' => 'Kelompok Supplier', 'file' => 'log_5kelompoksupplier.php', 'parent' => 'Setup'],
    ['id' => 396, 'name' => 'Data Supplier/Kontraktor', 'file' => 'log_5dataSupplier.php', 'parent' => 'Setup'],
    ['id' => 490, 'name' => 'Rek.Bank Supplier/Kontraktor', 'file' => 'log_5akunSupplier.php', 'parent' => 'Setup'],
    ['id' => 739, 'name' => 'Master Franco', 'file' => 'log_5masterfranco.php', 'parent' => 'Setup'],
    ['id' => 1089, 'name' => 'Adjustment Stock Opname', 'file' => 'log_5stocOpname.php', 'parent' => 'Setup'],
    ['id' => 1096, 'name' => 'Kartu Bin', 'file' => 'log_5kartubin.php', 'parent' => 'Setup'],
    ['id' => 1141, 'name' => 'Syarat Bayar', 'file' => 'log_5syaratbayar.php', 'parent' => 'Setup'],
];

echo "PENGADAAN MODULE FILE CHECK\n";
echo "============================\n\n";

$totalFiles = count($menuItems);
$existingFiles = 0;
$missingFiles = 0;

foreach ($menuItems as $item) {
    $filePath = __DIR__ . '/' . $item['file'];
    $exists = file_exists($filePath);

    if ($exists) {
        $existingFiles++;
        echo "[OK] ";
    } else {
        $missingFiles++;
        echo "[MISSING] ";
    }

    echo $item['parent'] . " > " . $item['name'] . " => " . $item['file'] . "\n";
}

echo "\n\nSUMMARY\n";
echo "=======\n";
echo "Total Menu Items: " . $totalFiles . "\n";
echo "Files Found: " . $existingFiles . "\n";
echo "Files Missing: " . $missingFiles . "\n";
?>
