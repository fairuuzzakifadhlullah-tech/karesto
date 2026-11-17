<?php
session_start();

// Load koneksi database
if (file_exists(__DIR__ . '/koneksi.php')) {
    require_once __DIR__ . '/koneksi.php';
} elseif (file_exists(__DIR__ . '/../koneksi.php')) {
    require_once __DIR__ . '/../koneksi.php';
}

/** @var mysqli $koneksi */

// Proteksi Sesi Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "Akses ditolak. Silakan login terlebih dahulu.";
    exit();
}

// Query Mengambil Data 7 Hari Terakhir
$query_rekap = mysqli_query($koneksi, "
    SELECT * FROM reservations 
    WHERE tanggal_reservasi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY tanggal_reservasi DESC
");

$total_omset_masuk = 0;
$total_kerugian_batal = 0;
$jumlah_berhasil = 0;
$jumlah_batal = 0;

$data_reservasi = [];
if ($query_rekap) {
    while ($row = mysqli_fetch_assoc($query_rekap)) {
        $data_reservasi[] = $row;
        if ($row['status'] === 'Dibatalkan') {
            $total_kerugian_batal += $row['total_harga'];
            $jumlah_batal++;
        } else {
            $total_omset_masuk += $row['total_harga'];
            $jumlah_berhasil++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Omset Mingguan - Karesto</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 30px; color: #333; background: #fff; margin: 0; }
        
        .header { text-align: center; border-bottom: 2px solid #8B0000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #8B0000; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0 0; font-size: 13px; color: #666; }

        .summary-box { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 25px; }
        .box { flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fdfdfd; }
        .box h3 { margin: 0 0 8px 0; font-size: 12px; color: #555; text-transform: uppercase; }
        .box .value { font-size: 18px; font-weight: bold; }
        .box.success .value { color: #2e7d32; }
        .box.danger .value { color: #c62828; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background-color: #8B0000; color: white; }

        /* Style Kelompok Tombol Aksi */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s;
        }
        .btn-back:hover { background-color: #5a6268; }

        .btn-print {
            background-color: #28a745;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background-color: #218838; }

        /* Sembunyikan Tombol Saat Mencetak/Simpan PDF */
        @media print {
            .action-buttons { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Container Tombol Navigasi & Cetak -->
    <div class="action-buttons">
        <a href="dashboard.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        <button onclick="window.print()" class="btn-print">📄 Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>RESTORAN KARESTO</h1>
        <p>Laporan Rekapitulasi Omset & Pembatalan Pemesanan (7 Hari Terakhir)</p>
        <p><small>Dicetak pada: <?php echo date('d/m/Y H:i'); ?> WIB</small></p>
    </div>

    <!-- Ringkasan Keuntungan & Kerugian -->
    <div class="summary-box">
        <div class="box success">
            <h3>Total Keuntungan (Omset Real)</h3>
            <div class="value">Rp <?php echo number_format($total_omset_masuk, 0, ',', '.'); ?></div>
            <small><?php echo $jumlah_berhasil; ?> Pesanan Terkonfirmasi</small>
        </div>
        <div class="box danger">
            <h3>Potensi Kerugian (Batal)</h3>
            <div class="value">Rp <?php echo number_format($total_kerugian_batal, 0, ',', '.'); ?></div>
            <small><?php echo $jumlah_batal; ?> Pesanan Dibatalkan</small>
        </div>
    </div>

    <h3>Rincian Transaksi Mingguan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Pemesan</th>
                <th>Paket</th>
                <th>Jumlah Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data_reservasi)): ?>
                <?php $no = 1; foreach ($data_reservasi as $row): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['kode_reservasi']); ?></strong></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_reservasi'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pemesan']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_paket']); ?></td>
                    <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td style="font-weight: bold; color: <?php echo ($row['status'] === 'Dibatalkan') ? '#c62828' : '#2e7d32'; ?>;">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #888;">Tidak ada transaksi dalam 7 hari terakhir.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>