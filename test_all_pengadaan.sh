#!/bin/bash

echo "PENGADAAN MODULE COMPREHENSIVE TEST REPORT"
echo "==========================================="
echo "Test Date: $(date)"
echo "Test Type: File Existence, PHP Syntax, and Code Analysis"
echo ""

# Define all menu items with their details
declare -a files=(
    "Transaksi|Permintaan Pembelian|log_pp.php"
    "Transaksi|Persetujuan Permintaan Pembelian|log_persetuuanPp.php"
    "Transaksi|Kontrak Perintah Kerja|log_spk.php"
    "Transaksi|BA Pelaksanaan Pekerjaan|log_realisasispk.php"
    "Transaksi|Inventaris Barang|log_invbarang.php"
    "Transaksi>Purchasing|Verifikasi PP|log_verifikasiPp.php"
    "Transaksi>Purchasing|Riwayat Perbandingan Harga|log_pnwrharga.php"
    "Transaksi>Purchasing|Perbandingan Harga|log_cmpharga.php"
    "Transaksi>Purchasing|Persetujuan Order Pembelian|log_persetujuan_po.php"
    "Transaksi>Purchasing|PO Pusat|log_po.php"
    "Transaksi>Purchasing|PO Lokal|log_POLokal.php"
    "Transaksi>Purchasing|PO Release|log_release_po.php"
    "Transaksi>Purchasing|Cetak PO|log_cetak_po.php"
    "Transaksi>Administrasi Gudang|Penerimaan Barang Dari Supplier|log_penerimaanBarang.php"
    "Transaksi>Administrasi Gudang|Mutasi Barang|log_mutasibarang.php"
    "Transaksi>Administrasi Gudang|Penerimaan Barang Mutasi|log_penerimaanMutasi.php"
    "Transaksi>Administrasi Gudang|Pemakaian Barang|log_pakaibarang.php"
    "Transaksi>Administrasi Gudang|Retur Ke Gudang|log_returKeGudang.php"
    "Transaksi>Administrasi Gudang|Posting|log_postingGudang.php"
    "Transaksi>Administrasi Gudang|Retur Ke Supplier|log_returKeSupplier.php"
    "Transaksi>Administrasi Gudang|Rekalkulasi Stock|log_rekalgudang.php"
    "Transaksi>Administrasi Gudang|Pembebanan Biaya Pengiriman|log_biayakirim.php"
    "Transaksi>Administrasi Gudang|Pemakaian Bahan Baku ke Bahan Jadi|log_brgjadi.php"
    "Laporan|Persediaan Fisik|log_2persediaanFisik.php"
    "Laporan|Persediaan Fisik dan Harga|log_2persediaanFisikHarga.php"
    "Laporan|Keluar / Masuk Persediaan|log_2keluarmasukbrg.php"
    "Laporan|Riwayat Permintaan Barang|log_2riwayat_baru.php"
    "Laporan|Daftar PO|log_2daftarPo.php"
    "Laporan|Alokasi Biaya Pembelian|log_2alokasibiaya.php"
    "Laporan|Alokasi Pemakaian Barang|log_2pemakaianbarang.php"
    "Laporan|Daftar Gudang|log_5daftarGudang.php"
    "Laporan|Hutang Berdasarkan BPB|log_2hutangsupplier.php"
    "Laporan|Laporan Alokasi Pemakaian Barang|log_2alokasi_pemakaiBrg.php"
    "Laporan|Penerimaan-Pengeluaran/Barang|log_2transaksigudang.php"
    "Laporan|Daftar Penerimaan Barang|log_2penerimaan.php"
    "Laporan|Mutasi Stock|log_2kalkulasi_stock.php"
    "Laporan|Realisasi PK|log_laporanRealisasiSPK.php"
    "Laporan|Summary Progress PK|summary_progress_spk.php"
    "Laporan|Gudang Vs Accounting|log_2gdangAccounting.php"
    "Laporan|PO yang dibatalkan|log_2daftarPo_batal.php"
    "Laporan|Daftar Barang|log_2daftarbarang.php"
    "Laporan|Penerimaan Barang Inventaris|log_2pengeluaranBarangInventaris.php"
    "Laporan|Reminder Stok|log_2rb.php"
    "Laporan|Daftar Supplier|log_2skc.php"
    "Laporan|Daftar SPK|log_lap_spk.php"
    "Laporan>Purchasing|Detail Pembelian|log_2detail_pembelian.php"
    "Laporan>Purchasing|Detail Pembelian Per Barang|log_2detail_pembelian_brg.php"
    "Laporan>Purchasing|Laporan PP|log_2pp_histori.php"
    "Laporan>Purchasing|Laporan Status PO|log_2laporan_statuspo.php"
    "Laporan>Purchasing|Perbandingan Harga|log_2perbandingan_harga.php"
    "Laporan>Purchasing|Pembelian Terakhir|log_2pembelian_terakhir.php"
    "Laporan>Purchasing|Laporan Produktivitas|log_2produktivitas.php"
    "Laporan>Purchasing|Laporan Status Pengiriman Barang|log_2posisiBarang.php"
    "Laporan>Purchasing|Riwayat Pembayaran|log_2pembayaran.php"
    "Laporan>Purchasing|PP BLM Realisasi|lbm_proc_pprealisasi.php"
    "Proses|Integrity Check BKM|log_3integrity.php"
    "Proses|Rekalkulasi Stock|log_3rekalkulasi_stock.php"
    "Proses|Tutup Buku Fisik|log_pindahPeriodeGudang.php"
    "Proses|Perhitungan Harga Akhir Bulan|log_3prosesAkhirBulan.php"
    "Setup|Kelompok Barang|log_5kelompokbarang.php"
    "Setup|Sub Kelompok Barang|log_5subkelompokbarang.php"
    "Setup|Master Barang|log_5masterbarang.php"
    "Setup|Konversi Satuan|log_5satuankonversi.php"
    "Setup|Kelompok Supplier|log_5kelompoksupplier.php"
    "Setup|Data Supplier/Kontraktor|log_5dataSupplier.php"
    "Setup|Rek.Bank Supplier/Kontraktor|log_5akunSupplier.php"
    "Setup|Master Franco|log_5masterfranco.php"
    "Setup|Adjustment Stock Opname|log_5stocOpname.php"
    "Setup|Kartu Bin|log_5kartubin.php"
    "Setup|Syarat Bayar|log_5syaratbayar.php"
)

total=0
success=0
failed=0

cd "C:\XAMPP\xampp\htdocs\erpmill"

echo "DETAILED TEST RESULTS"
echo "====================="
echo ""

for item in "${files[@]}"; do
    IFS='|' read -r section menu file <<< "$item"
    total=$((total + 1))

    echo "[$total] Menu: $section > $menu"
    echo "    File: $file"

    # Check if file exists
    if [ -f "$file" ]; then
        echo "    File Status: EXISTS"

        # Check PHP syntax
        syntax_check=$("C:\XAMPP\xampp\php\php.exe" -l "$file" 2>&1)
        if echo "$syntax_check" | grep -q "No syntax errors"; then
            echo "    PHP Syntax: VALID"

            # Get file size
            filesize=$(stat -c%s "$file" 2>/dev/null || stat -f%z "$file" 2>/dev/null || echo "Unknown")
            echo "    File Size: $filesize bytes"

            # Check for key includes
            has_connection=$(grep -c "connection.php" "$file" 2>/dev/null || echo "0")
            has_nangkoe=$(grep -c "nangkoelib.php" "$file" 2>/dev/null || echo "0")
            has_session=$(grep -c "session_start" "$file" 2>/dev/null || echo "0")

            echo "    Includes connection.php: $has_connection"
            echo "    Includes nangkoelib.php: $has_nangkoe"
            echo "    Has session_start: $has_session"

            echo "    Status: SUCCESS"
            success=$((success + 1))
        else
            echo "    PHP Syntax: ERROR"
            echo "    Syntax Error: $syntax_check"
            echo "    Status: FAILED (Syntax Error)"
            failed=$((failed + 1))
        fi
    else
        echo "    File Status: MISSING"
        echo "    Status: FAILED (File Not Found)"
        failed=$((failed + 1))
    fi

    echo ""
done

echo ""
echo "==============================================="
echo "SUMMARY"
echo "==============================================="
echo "Total Menu Items Tested: $total"
echo "Successful: $success"
echo "Failed: $failed"
echo "Success Rate: $(awk "BEGIN {printf \"%.2f\", ($success/$total)*100}")%"
echo ""
echo "Test completed at: $(date)"
