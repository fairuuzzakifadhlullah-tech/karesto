<?php
session_start();

if (file_exists(__DIR__ . '/koneksi.php')) {
    require_once __DIR__ . '/koneksi.php';
} elseif (file_exists(__DIR__ . '/../koneksi.php')) {
    require_once __DIR__ . '/../koneksi.php';
}

/** @var mysqli $koneksi */

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// 1. Logika Pembatalan Pesanan
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $id_batal = (int)$_GET['id'];
    
    if (isset($koneksi) && $koneksi && $id_batal > 0) {
        $stmt_cancel = mysqli_prepare($koneksi, "UPDATE reservations SET status = 'Dibatalkan' WHERE id = ?");
        mysqli_stmt_bind_param($stmt_cancel, "i", $id_batal);
        mysqli_stmt_execute($stmt_cancel);
        mysqli_stmt_close($stmt_cancel);
    }
    header("Location: dashboard.php");
    exit();
}

// 2. Logika Pembaruan Diskon Dinamis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_discount') {
    $discount_id = (int)$_POST['discount_id'];
    $persen = (int)$_POST['persen_diskon'];
    $status = ($_POST['status'] === 'Aktif') ? 'Aktif' : 'Nonaktif';

    if (isset($koneksi) && $koneksi && $discount_id > 0) {
        $stmt_disc = mysqli_prepare($koneksi, "UPDATE discounts SET persen_diskon = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_disc, "isi", $persen, $status, $discount_id);
        mysqli_stmt_execute($stmt_disc);
        mysqli_stmt_close($stmt_disc);
    }
    header("Location: dashboard.php");
    exit();
}

// 3. Rekap Statistik dari DB
if (isset($koneksi) && $koneksi) {
    $query_res = mysqli_query($koneksi, "SELECT * FROM reservations ORDER BY id DESC");
    $total_reservasi = $query_res ? mysqli_num_rows($query_res) : 0;

    $query_sum = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total_omset FROM reservations WHERE status != 'Dibatalkan'");
    $row_sum = $query_sum ? mysqli_fetch_assoc($query_sum) : null;
    $total_omset = $row_sum['total_omset'] ?? 0;

    $query_mingguan = mysqli_query($koneksi, "
        SELECT 
            SUM(CASE WHEN status != 'Dibatalkan' THEN total_harga ELSE 0 END) AS omset_minggu,
            SUM(CASE WHEN status = 'Dibatalkan' THEN total_harga ELSE 0 END) AS kerugian_minggu
        FROM reservations 
        WHERE tanggal_reservasi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $row_minggu = $query_mingguan ? mysqli_fetch_assoc($query_mingguan) : null;
    $omset_minggu = $row_minggu['omset_minggu'] ?? 0;
    $kerugian_minggu = $row_minggu['kerugian_minggu'] ?? 0;

    $query_discounts = mysqli_query($koneksi, "SELECT * FROM discounts ORDER BY id ASC");
} else {
    $total_reservasi = 0; $total_omset = 0; $omset_minggu = 0; $kerugian_minggu = 0;
    $query_res = false; $query_discounts = false;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel Admin - KARESTO</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f0f4f8; color: #333; }
        header { background-color: #8B0000; color: white; padding: 12px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }
        
        /* Pengaturan agar logo PNG dan judul sejajar rapi */
        .header-brand { display: flex; align-items: center; gap: 12px; }
        .logo-img { height: 48px; width: auto; object-fit: contain; }
        .header-title h1 { font-size: 20px; }
        
        .user-info { font-size: 13px; display: flex; align-items: center; gap: 15px; }
        .btn-logout { background: #fff; color: #8B0000; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: bold; color: #8B0000; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 18px; border-radius: 10px; border-left: 5px solid #8B0000; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .stat-card.warning { border-left-color: #dc3545; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card h4 { font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 6px; }
        .stat-card div { font-size: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle; }
        th { background: #fafafa; font-weight: bold; color: #555; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; display: inline-block; font-size: 11px; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-danger { background: #ffebee; color: #c62828; }
        .btn-cancel { background-color: #dc3545; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; }
        .btn-pdf { background-color: #28a745; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save { background-color: #0d6efd; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>

    <header>
        <div class="header-brand">
            <img src="logo-karesto-removebg-preview.png" alt="Logo Karesto" class="logo-img">
            <div class="header-title"><h1>KARESTO - Control Panel Admin</h1></div>
        </div>
        <div class="user-info">
            <span>Halo, <strong><?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator Karesto'); ?></strong></span>
            <a href="logout.php" class="btn-logout">Keluar (Logout)</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Pemesanan</h4>
                <div><?php echo $total_reservasi; ?> Pesanan</div>
            </div>
            <div class="stat-card success">
                <h4>Estimasi Total Omset</h4>
                <div>Rp <?php echo number_format($total_omset, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card">
                <h4>Omset 7 Hari Terakhir</h4>
                <div style="color: #28a745;">Rp <?php echo number_format($omset_minggu, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card warning">
                <h4>Potensi Batal (7 Hari)</h4>
                <div style="color: #dc3545;">Rp <?php echo number_format($kerugian_minggu, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Pengaturan Diskon & Promo Restoran</div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Promo</th>
                            <th>Tipe Promo</th>
                            <th>Persentase Diskon (%)</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_discounts && mysqli_num_rows($query_discounts) > 0): ?>
                            <?php while ($d = mysqli_fetch_assoc($query_discounts)): ?>
                            <tr>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_discount">
                                    <input type="hidden" name="discount_id" value="<?php echo $d['id']; ?>">
                                    <td><strong><?php echo htmlspecialchars($d['nama_promo']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($d['tipe_promo']); ?></code></td>
                                    <td>
                                        <input type="number" name="persen_diskon" value="<?php echo $d['persen_diskon']; ?>" min="0" max="100" style="width: 70px; padding: 5px; border: 1px solid #ccc; border-radius: 4px;" required> %
                                    </td>
                                    <td>
                                        <select name="status" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                                            <option value="Aktif" <?php echo $d['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="Nonaktif" <?php echo $d['status'] === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="submit" class="btn-save">Simpan Perubahan</button>
                                    </td>
                                </form>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header-flex">
                <div class="card-title" style="border-bottom: none; margin-bottom: 0;">Daftar Pemesanan Reservasi Pelanggan</div>
                <a href="cetak_rekap.php" target="_blank" class="btn-pdf">📄 Download Rekap PDF Mingguan</a>
            </div>
            
            <?php if ($query_res && $total_reservasi > 0): ?>
            <div style="overflow-x: auto; margin-top: 15px;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pemesan</th>
                            <th>Outlet / Paket</th>
                            <th>Tanggal & Jam</th>
                            <th>Tamu</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($query_res, 0);
                        while ($r = mysqli_fetch_assoc($query_res)): 
                            $is_cancelled = ($r['status'] === 'Dibatalkan');
                        ?>
                        <tr style="<?php echo $is_cancelled ? 'background-color: #fafafa; opacity: 0.7;' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($r['kode_reservasi']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($r['nama_pemesan']); ?></strong><br>
                                <small style="color:#777;"><?php echo htmlspecialchars($r['no_wa']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($r['nama_outlet']); ?><br>
                                <small style="color:#8B0000; font-weight:bold;"><?php echo htmlspecialchars($r['nama_paket']); ?></small>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($r['tanggal_reservasi'])); ?><br>
                                <small style="color:#555;"><?php echo htmlspecialchars($r['jam_reservasi']); ?> WIB</small>
                            </td>
                            <td>
                                <?php echo $r['count_dewasa']; ?> Dewasa · <?php echo $r['count_anak']; ?> Anak · <?php echo $r['count_senior']; ?> Senior
                            </td>
                            <td style="font-weight: bold; color: <?php echo $is_cancelled ? '#777' : '#8B0000'; ?>;">
                                Rp <?php echo number_format($r['total_harga'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?php if ($is_cancelled): ?>
                                    <span class="badge badge-danger">Dibatalkan</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Terkonfirmasi</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!$is_cancelled): ?>
                                    <a href="dashboard.php?action=cancel&id=<?php echo $r['id']; ?>" class="btn-cancel" onclick="return confirm('Apakah Anda yakin ingin MEMBATALKAN pesanan ini?');">Batalkan</a>
                                <?php else: ?>
                                    <span style="color:#999; font-size:11px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>