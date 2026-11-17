<?php
session_start();

// Deteksi lokasi file koneksi.php
if (file_exists('koneksi.php')) {
    include_once 'koneksi.php';
} elseif (file_exists(__DIR__ . '/../koneksi.php')) {
    include_once __DIR__ . '/../koneksi.php';
}

/** @var mysqli $koneksi */

$raw_cabang = isset($_GET['cabang']) ? $_GET['cabang'] : 'Solo';
$cabang_clean = trim(str_ireplace('karesto', '', $raw_cabang));
$cabang = !empty($cabang_clean) ? ucfirst($cabang_clean) : 'Solo';

// -------------------------------------------------------------
// 1. AMBIL KONFIGURASI DISKON TERBARU DARI MYSQL
// -------------------------------------------------------------
$disc_weekend_percent = 10;
$disc_weekend_active = true;

$disc_ultah_percent = 20;
$disc_ultah_active = true;

$disc_nasional_percent = 15;
$disc_nasional_active = true;

if (isset($koneksi) && $koneksi) {
    $q_disc = mysqli_query($koneksi, "SELECT * FROM discounts");
    if ($q_disc) {
        while ($d = mysqli_fetch_assoc($q_disc)) {
            if ($d['tipe_promo'] === 'weekend') {
                $disc_weekend_percent = (int)$d['persen_diskon'];
                $disc_weekend_active = ($d['status'] === 'Aktif');
            } elseif ($d['tipe_promo'] === 'ultah') {
                $disc_ultah_percent = (int)$d['persen_diskon'];
                $disc_ultah_active = ($d['status'] === 'Aktif');
            } elseif ($d['tipe_promo'] === 'nasional') {
                $disc_nasional_percent = (int)$d['persen_diskon'];
                $disc_nasional_active = ($d['status'] === 'Aktif');
            }
        }
    }
}

// Ambil data paket dari DB jika ada
$packages = [];
if (isset($koneksi) && $koneksi) {
    $query_packages = mysqli_query($koneksi, "SELECT * FROM packages");
    if ($query_packages) {
        while ($row = mysqli_fetch_assoc($query_packages)) {
            $packages[] = $row;
        }
    }
}

// Fallback data paket jika DB offline
if (empty($packages)) {
    $packages = [
        ['nama_paket' => 'Starter Package', 'durasi' => '90 mnt', 'harga_dewasa' => 142000, 'harga_anak' => 97000, 'harga_senior' => 125000, 'deskripsi' => 'Termasuk pilihan Daging Sapi Regular & Side Dish'],
        ['nama_paket' => 'Regular Package', 'durasi' => '90 mnt', 'harga_dewasa' => 155000, 'harga_anak' => 101000, 'harga_senior' => 129000, 'deskripsi' => 'Pilihan daging lebih lengkap & Shabu-Shabu'],
        ['nama_paket' => 'Premium Beef', 'durasi' => '150 mnt', 'harga_dewasa' => 279000, 'harga_anak' => 198000, 'harga_senior' => 249000, 'deskripsi' => 'Wagyu Slices, Saikoro Beef, & All Star Buffet']
    ];
}

// PROSES SIMPAN RESERVASI KE DATABASE MYSQL
$is_success = false;
$kode_booking = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_reservation') {
    $outlet_name = "Karesto " . $cabang;
    $paket_name = $_POST['post_paket_nama'];
    $tanggal = $_POST['post_tanggal'];
    $jam = $_POST['post_jam'];
    $dewasa = (int)$_POST['post_dewasa'];
    $anak = (int)$_POST['post_anak'];
    $senior = (int)$_POST['post_senior'];
    $nama = trim($_POST['post_nama']);
    $wa = trim($_POST['post_wa']);
    $email = trim($_POST['post_email']);
    $catatan = trim($_POST['post_catatan']);
    $is_ultah = isset($_POST['post_is_ultah']) && $_POST['post_is_ultah'] === '1';
    $is_nasional = isset($_POST['post_is_nasional']) && $_POST['post_is_nasional'] === '1';

    // Validasi Tanggal Lampau
    $today_str = date('Y-m-d');
    if ($tanggal < $today_str) {
        $tanggal = $today_str;
    }

    // Ambil harga paket
    $p_dewasa = 279000; $p_anak = 198000; $p_senior = 249000;
    foreach ($packages as $pkg) {
        if ($pkg['nama_paket'] === $paket_name) {
            $p_dewasa = $pkg['harga_dewasa'];
            $p_anak = $pkg['harga_anak'];
            $p_senior = $pkg['harga_senior'];
            break;
        }
    }

    $subtotal = ($dewasa * $p_dewasa) + ($anak * $p_anak) + ($senior * $p_senior);
    $total_tamu = $dewasa + $anak + $senior;
    $running_subtotal = $subtotal;
    $total_diskon = 0;

    // 1. Diskon Pay 4 Get 5
    if ($total_tamu >= 5 && $paket_name !== 'Starter Package') {
        $gratis_count = floor($total_tamu / 5);
        $pay4get5Disc = ($gratis_count * $p_dewasa);
        $total_diskon += $pay4get5Disc;
        $running_subtotal = max(0, $running_subtotal - $pay4get5Disc);
    }

    // 2. Diskon Ulang Tahun (Dinamis DB)
    if ($is_ultah && $disc_ultah_active && $running_subtotal > 0) {
        $ultahDisc = round($running_subtotal * ($disc_ultah_percent / 100));
        $total_diskon += $ultahDisc;
        $running_subtotal = max(0, $running_subtotal - $ultahDisc);
    }

    // 3. Diskon Hari Besar / Nasional (Dinamis DB)
    if ($is_nasional && $disc_nasional_active && $running_subtotal > 0) {
        $nasionalDisc = round($running_subtotal * ($disc_nasional_percent / 100));
        $total_diskon += $nasionalDisc;
        $running_subtotal = max(0, $running_subtotal - $nasionalDisc);
    }

    // 4. Diskon Weekend (Dinamis DB)
    $dayOfWeek = date('w', strtotime($tanggal));
    if (($dayOfWeek == 0 || $dayOfWeek == 5 || $dayOfWeek == 6) && $disc_weekend_active && $running_subtotal > 0) {
        $weekendDisc = round($running_subtotal * ($disc_weekend_percent / 100));
        $total_diskon += $weekendDisc;
        $running_subtotal = max(0, $running_subtotal - $weekendDisc);
    }

    $subtotal_after_diskon = max(0, $subtotal - $total_diskon);
    $service = round($subtotal_after_diskon * 0.05);
    $ppn = round(($subtotal_after_diskon + $service) * 0.10);
    $total = $subtotal_after_diskon + $service + $ppn;

    $kode_booking = "KRS-" . strtoupper(substr(md5(uniqid()), 0, 6));

    if (isset($koneksi) && $koneksi) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO reservations (kode_reservasi, nama_outlet, nama_paket, tanggal_reservasi, jam_reservasi, count_dewasa, count_anak, count_senior, subtotal, service_fee, ppn, total_harga, nama_pemesan, no_wa, email, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssiiiiiiiisss", $kode_booking, $outlet_name, $paket_name, $tanggal, $jam, $dewasa, $anak, $senior, $subtotal_after_diskon, $service, $ppn, $total, $nama, $wa, $email, $catatan);
            mysqli_stmt_execute($stmt);
        }
    }
    $is_success = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Karesto <?php echo htmlspecialchars($cabang); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f0f4f8; color: #333; }

        header { background-color: #8B0000; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header-title h2 { margin: 0; font-size: 18px; }
        .header-title p { margin: 3px 0 0 0; font-size: 12px; opacity: 0.8; }

        .stepper { display: flex; gap: 15px; align-items: center; }
        .step-item { display: flex; align-items: center; gap: 6px; font-size: 12px; opacity: 0.6; }
        .step-item.active { opacity: 1; font-weight: bold; }
        .step-number { width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .step-item.active .step-number { background: white; color: #8B0000; }

        .container { max-width: 520px; margin: 30px auto 60px auto; padding: 0 15px; }
        
        .alert-guest {
            background-color: #fde8e8; color: #d32f2f; border: 1px solid #f8b4b4;
            padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: bold;
            text-align: center; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(211, 47, 47, 0.08);
        }

        .card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 20px; }
        .card-title { font-size: 12px; font-weight: bold; color: #555; letter-spacing: 1px; margin-bottom: 12px; text-transform: uppercase; }

        .date-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .selected-date-text { font-size: 16px; font-weight: bold; color: #222; }
        .btn-calendar-trigger { background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 6px 10px; cursor: pointer; font-size: 14px; }

        .date-strip-wrapper { display: flex; align-items: center; gap: 6px; }
        .btn-strip-nav { background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 50%; width: 28px; height: 28px; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .btn-strip-nav:hover { background: #e0e0e0; }

        .date-strip { display: flex; gap: 6px; flex: 1; overflow-x: auto; scrollbar-width: none; }
        .date-strip::-webkit-scrollbar { display: none; }

        .date-card {
            flex: 1; min-width: 48px; padding: 8px 4px; border: 1px solid #e0e0e0; border-radius: 10px;
            text-align: center; cursor: pointer; background: #fff; transition: all 0.2s;
        }
        .date-card.selected { background: #8B0000; color: white; border-color: #8B0000; font-weight: bold; }
        .date-card.disabled { opacity: 0.35; background: #f5f5f5; border-color: #eee; color: #888; cursor: not-allowed; pointer-events: none; }

        .date-card-num { font-size: 15px; font-weight: bold; }
        .date-card-day { font-size: 11px; opacity: 0.8; margin-top: 2px; }

        .time-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-top: 10px; }
        .time-btn { padding: 10px 2px; border: 1px solid #ddd; background: #fff; border-radius: 6px; font-size: 12px; cursor: pointer; text-align: center; }
        .time-btn.selected { background-color: #8B0000; color: white; border-color: #8B0000; font-weight: bold; }
        .last-order-note { font-size: 11px; color: #777; margin-top: 12px; text-align: left; display: flex; align-items: center; gap: 4px; }

        .package-item { border: 2px solid #eee; border-radius: 8px; padding: 15px; margin-bottom: 12px; cursor: pointer; }
        .package-item.selected { border-color: #8B0000; background-color: #fff9f9; }
        .package-header { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-bottom: 5px; }
        .package-price { color: #8B0000; }

        .guest-row { display: flex; align-items: center; gap: 20px; padding: 15px 0; border-bottom: 1px dashed #eee; }
        .guest-row:last-child { border-bottom: none; }
        .counter-control { display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #fafafa; }
        .counter-btn { background: #f0f0f0; border: none; width: 38px; height: 38px; font-size: 18px; cursor: pointer; }
        .counter-value { width: 45px; text-align: center; font-weight: bold; font-size: 15px; background: #fff; line-height: 38px; }
        .guest-info { flex: 1; }
        .guest-title { font-weight: bold; font-size: 15px; color: #111; display: flex; align-items: center; gap: 6px; }
        .guest-desc { font-size: 12px; color: #777; margin-top: 2px; }
        .guest-price-label { font-size: 13px; color: #8B0000; font-weight: bold; margin-top: 4px; }

        .promo-box { background: #fdf8f8; border: 1px solid #f0d8d8; border-radius: 10px; padding: 12px 15px; margin-top: 10px; }
        .promo-checkbox-label { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: bold; color: #8B0000; cursor: pointer; }
        .promo-checkbox-label input { width: 16px; height: 16px; accent-color: #8B0000; cursor: pointer; }
        .discount-badge { display: inline-block; background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 6px; }

        .estimate-card { background: #faf5f5; border: 1px solid #f0d8d8; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .estimate-title { font-size: 13px; font-weight: bold; color: #333; margin-bottom: 12px; }
        .price-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; color: #444; }
        .price-row.discount-line { color: #2e7d32; font-weight: bold; }
        .price-row.total-line { border-top: 1px solid #e0c8c8; padding-top: 10px; margin-top: 10px; font-size: 16px; font-weight: bold; }
        .price-row.total-line .total-val { color: #8B0000; }

        .summary-list { display: flex; flex-direction: column; }
        .summary-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 0; border-bottom: 1px solid #eef2f5; font-size: 13px; }
        .summary-label { color: #777; }
        .summary-value { color: #222; font-weight: 600; text-align: right; max-width: 65%; }
        .summary-row.total-row { border-bottom: none; padding-top: 14px; font-size: 15px; }
        .summary-row.total-row .summary-label { font-weight: bold; color: #222; }
        .summary-row.total-row .summary-value { color: #8B0000; font-size: 16px; font-weight: bold; }

        .info-note { background-color: #fffde7; border: 1px solid #ffe082; border-radius: 8px; padding: 12px 15px; font-size: 12px; color: #795548; margin-top: 20px; line-height: 1.4; display: flex; gap: 10px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; color: #444; margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }

        .nav-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn-next { flex: 1; background-color: #8B0000; color: white; padding: 14px; border: none; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; text-align: center; }
        .btn-back { background-color: #fff; border: 1px solid #ccc; padding: 14px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <header>
        <div class="header-title">
            <h2>Reservasi Karesto <?php echo htmlspecialchars($cabang); ?></h2>
            <p>Jl. Ir. Soekarno No. 123, Solo Baru, Surakarta</p>
        </div>

        <div class="stepper">
            <div class="step-item <?php echo !$is_success ? 'active' : ''; ?>" id="indicator-1"><span class="step-number">1</span><span>Paket</span></div>
            <div class="step-item" id="indicator-2"><span class="step-number">2</span><span>Data Diri</span></div>
            <div class="step-item" id="indicator-3"><span class="step-number">3</span><span>Ringkasan</span></div>
            <div class="step-item <?php echo $is_success ? 'active' : ''; ?>" id="indicator-4"><span class="step-number">4</span><span>Selesai</span></div>
        </div>
    </header>

    <div class="container">
        
        <?php if (!$is_success): ?>
        <!-- STEP 1 -->
        <div id="step-1">
            
            <div id="guest-alert-box" class="alert-guest" style="display: none;">
                Masukkan jumlah tamu minimal 1 orang
            </div>

            <!-- Kartu Tanggal Reservasi -->
            <div class="card">
                <div class="card-title">TANGGAL RESERVASI</div>
                
                <div class="date-header-row">
                    <div class="selected-date-text" id="display-date-text">Kamis, 5 November 2026</div>
                    <button type="button" class="btn-calendar-trigger" onclick="document.getElementById('hidden-date-picker').showPicker()">📅</button>
                    <input type="date" id="hidden-date-picker" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" style="opacity: 0; width: 0; height: 0; position: absolute;" onchange="onCalendarSelect(this.value)">
                </div>

                <div class="date-strip-wrapper">
                    <button type="button" class="btn-strip-nav" onclick="shiftDateStrip(-7)">&lt;</button>
                    <div class="date-strip" id="date-strip-container"></div>
                    <button type="button" class="btn-strip-nav" onclick="shiftDateStrip(7)">&gt;</button>
                </div>

                <div id="weekend-discount-banner" style="display: none;">
                    <span class="discount-badge" id="weekend-badge-text">🎉 Diskon Weekend Aktif!</span>
                </div>
            </div>

            <!-- Kartu Jam Mulai -->
            <div class="card">
                <div class="card-title">JAM MULAI</div>
                <div class="time-grid">
                    <button type="button" class="time-btn" onclick="selectTime(this)">11:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">11:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">12:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">12:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">13:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">13:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">14:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">14:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">15:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">15:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">16:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">16:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">17:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">17:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">18:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">18:30</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">19:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">19:30</button>
                    <button type="button" class="time-btn selected" onclick="selectTime(this)">20:00</button>
                    <button type="button" class="time-btn" onclick="selectTime(this)">20:30</button>
                </div>
                <div class="last-order-note">🕒 Last order : 20:30 WIB</div>
            </div>

            <!-- Kartu Pilih Paket -->
            <div class="card">
                <div class="card-title">PILIH PAKET</div>
                <?php foreach ($packages as $pkg): ?>
                    <div class="package-item <?php echo ($pkg['nama_paket'] == 'Premium Beef') ? 'selected' : ''; ?>" 
                         onclick="selectPackage(this, '<?php echo $pkg['nama_paket']; ?>', '<?php echo $pkg['durasi']; ?>', <?php echo $pkg['harga_dewasa']; ?>, <?php echo $pkg['harga_anak']; ?>, <?php echo $pkg['harga_senior']; ?>)">
                        <div class="package-header">
                            <span><?php echo strtoupper($pkg['nama_paket']); ?> (<?php echo $pkg['durasi']; ?>)</span>
                            <span class="package-price">Rp <?php echo number_format($pkg['harga_dewasa'], 0, ',', '.'); ?>++</span>
                        </div>
                        <small style="color:#777;"><?php echo htmlspecialchars($pkg['deskripsi']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Kartu Jumlah Tamu & Promo Spesial -->
            <div class="card">
                <div class="card-title">JUMLAH TAMU</div>
                
                <div class="guest-row">
                    <div class="counter-control">
                        <button type="button" class="counter-btn" onclick="updateGuest('dewasa', -1)">-</button>
                        <span class="counter-value" id="count-dewasa">0</span>
                        <button type="button" class="counter-btn" onclick="updateGuest('dewasa', 1)">+</button>
                    </div>
                    <div class="guest-info">
                        <div class="guest-title">👤 Dewasa</div>
                        <div class="guest-desc">Usia umum</div>
                        <div class="guest-price-label" id="lbl-price-dewasa">Rp 279.000++ / orang</div>
                    </div>
                </div>

                <div class="guest-row">
                    <div class="counter-control">
                        <button type="button" class="counter-btn" onclick="updateGuest('anak', -1)">-</button>
                        <span class="counter-value" id="count-anak">0</span>
                        <button type="button" class="counter-btn" onclick="updateGuest('anak', 1)">+</button>
                    </div>
                    <div class="guest-info">
                        <div class="guest-title">🧒 Anak</div>
                        <div class="guest-desc">Tinggi ≥100 cm</div>
                        <div class="guest-price-label" id="lbl-price-anak">Rp 198.000++ / orang</div>
                    </div>
                </div>

                <div class="guest-row">
                    <div class="counter-control">
                        <button type="button" class="counter-btn" onclick="updateGuest('senior', -1)">-</button>
                        <span class="counter-value" id="count-senior">0</span>
                        <button type="button" class="counter-btn" onclick="updateGuest('senior', 1)">+</button>
                    </div>
                    <div class="guest-info">
                        <div class="guest-title">👴 Senior</div>
                        <div class="guest-desc">Min. 65 tahun</div>
                        <div class="guest-price-label" id="lbl-price-senior">Rp 249.000++ / orang</div>
                    </div>
                </div>

                <!-- Opsi Diskon Ulang Tahun Dinamis -->
                <div class="promo-box">
                    <label class="promo-checkbox-label">
                        <input type="checkbox" id="chk-ultah" onchange="updateLiveEstimate()">
                        🎂 Klaim Diskon Ulang Tahun (<span id="lbl-ultah-percent"><?php echo $disc_ultah_percent; ?>%</span>)
                    </label>
                    <small style="display:block; color:#777; font-size:11px; margin-top:4px; margin-left:26px;">Tunjukkan KTP/Identitas diri saat kedatangan di kedai.</small>
                </div>

                <!-- Opsi Diskon Hari Besar / Nasional (HANYA MUNCUL PADA HARI BESAR) -->
                <div class="promo-box" id="box-promo-nasional" style="display: none;">
                    <label class="promo-checkbox-label">
                        <input type="checkbox" id="chk-nasional" onchange="updateLiveEstimate()">
                        🇮🇩 Klaim Diskon Hari Besar / Nasional (<span id="lbl-nasional-percent"><?php echo $disc_nasional_percent; ?>%</span>)
                    </label>
                    <small style="display:block; color:#777; font-size:11px; margin-top:4px; margin-left:26px;">Berlaku untuk pemesanan pada Hari Libur & Hari Besar Nasional.</small>
                </div>

                <div id="pay4get5-banner" style="display: none;">
                    <span class="discount-badge">🎁 Promo Pay 4 Get 5 Aktif (Gratis 1 Orang)!</span>
                </div>
            </div>

            <!-- Card Estimasi Harga -->
            <div class="estimate-card">
                <div class="estimate-title">Estimasi Harga</div>
                <div id="live-price-rows"></div>
                <div class="info-note"><span>ⓘ</span><div>Hanya estimasi pembayaran. Untuk promo dan potongan diskon, total valid ditentukan saat pembayaran di kasir.</div></div>
            </div>

            <button type="button" class="btn-next" onclick="goToStep(2)">Data Diri &rarr;</button>
        </div>

        <!-- STEP 2 -->
        <div id="step-2" style="display: none;">
            <div class="card">
                <div class="card-title">INFORMASI TAMU</div>
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" id="input-nama" placeholder="Nama sesuai identitas" value="Fairuuz Zaki Fadhlullah" required></div>
                <div class="form-group"><label>Nomor WhatsApp *</label><input type="tel" id="input-wa" placeholder="+62 08xxxxxxxxxx" value="+6281234567890" required></div>
                <div class="form-group"><label>Email *</label><input type="email" id="input-email" placeholder="nama@email.com" value="fairuuzzakifadhlullah@gmail.com" required></div>
                <div class="form-group"><label>Catatan (opsional)</label><textarea id="input-catatan" rows="3" placeholder="Permintaan khusus, area duduk, dll..."></textarea></div>

                <div class="nav-buttons">
                    <button type="button" class="btn-back" onclick="goToStep(1)">&larr;</button>
                    <button type="button" class="btn-next" onclick="goToStep(3)">Ringkasan &rarr;</button>
                </div>
            </div>
        </div>

        <!-- STEP 3 -->
        <div id="step-3" style="display: none;">
            <form id="form-reservasi-submit" method="POST" action="">
                <input type="hidden" name="action" value="save_reservation">
                <input type="hidden" name="post_paket_nama" id="post_paket_nama" value="Premium Beef">
                <input type="hidden" name="post_tanggal" id="post_tanggal">
                <input type="hidden" name="post_jam" id="post_jam">
                <input type="hidden" name="post_dewasa" id="post_dewasa" value="0">
                <input type="hidden" name="post_anak" id="post_anak" value="0">
                <input type="hidden" name="post_senior" id="post_senior" value="0">
                <input type="hidden" name="post_nama" id="post_nama">
                <input type="hidden" name="post_wa" id="post_wa">
                <input type="hidden" name="post_email" id="post_email">
                <input type="hidden" name="post_catatan" id="post_catatan">
                <input type="hidden" name="post_is_ultah" id="post_is_ultah" value="0">
                <input type="hidden" name="post_is_nasional" id="post_is_nasional" value="0">

                <div class="card">
                    <div class="card-title">RINGKASAN PESANAN</div>
                    
                    <div class="summary-list">
                        <div class="summary-row"><span class="summary-label">Outlet</span><span class="summary-value" id="sum-outlet">Karesto <?php echo htmlspecialchars($cabang); ?></span></div>
                        <div class="summary-row"><span class="summary-label">Paket</span><span class="summary-value" id="sum-paket">Premium Beef - 150 mnt</span></div>
                        <div class="summary-row"><span class="summary-label">Tanggal</span><span class="summary-value" id="sum-tanggal">-</span></div>
                        <div class="summary-row"><span class="summary-label">Jam</span><span class="summary-value" id="sum-jam">20:00</span></div>
                        <div class="summary-row"><span class="summary-label">Tamu</span><span class="summary-value" id="sum-tamu">0 Tamu</span></div>
                        <div class="summary-row"><span class="summary-label">Nama</span><span class="summary-value" id="sum-nama">-</span></div>
                        <div class="summary-row"><span class="summary-label">WhatsApp</span><span class="summary-value" id="sum-wa">-</span></div>
                        <div class="summary-row"><span class="summary-label">Email</span><span class="summary-value" id="sum-email">-</span></div>
                        <div class="summary-row" style="margin-top: 8px;"><span class="summary-label">Subtotal</span><span class="summary-value" id="sum-subtotal">Rp 0</span></div>
                        <div class="summary-row" id="sum-diskon-row" style="display: none; color: #2e7d32;"><span class="summary-label" style="color: #2e7d32; font-weight: bold;">Total Diskon Promo</span><span class="summary-value" id="sum-diskon">-Rp 0</span></div>
                        <div class="summary-row"><span class="summary-label">Service 5%</span><span class="summary-value" id="sum-service">Rp 0</span></div>
                        <div class="summary-row"><span class="summary-label">PPN 10%</span><span class="summary-value" id="sum-ppn">Rp 0</span></div>
                        <div class="summary-row total-row"><span class="summary-label">Total</span><span class="summary-value" id="sum-total">Rp 0</span></div>
                    </div>

                    <div class="info-note"><span>ⓘ</span><div>Hanya estimasi pembayaran. Untuk promo dan potongan diskon, total valid ditentukan saat pembayaran di kasir.</div></div>
                </div>

                <div class="nav-buttons">
                    <button type="button" class="btn-back" onclick="goToStep(2)">&larr;</button>
                    <button type="submit" class="btn-next">✓ Konfirmasi & Simpan Reservasi</button>
                </div>
            </form>
        </div>
        <?php else: ?>

        <!-- STEP 4 -->
        <div id="step-4">
            <div class="card" style="text-align: center; padding: 40px 20px;">
                <h2 style="color: #8B0000; margin-bottom: 5px;">RESERVASI BERHASIL!</h2>
                <p style="font-size: 13px; color: #888; margin-bottom: 20px;">Kode Booking: <strong><?php echo htmlspecialchars($kode_booking); ?></strong></p>
                <p style="color: #666; font-size: 14px; line-height: 1.5;">
                    Terima kasih telah melakukan reservasi di Karesto <?php echo htmlspecialchars($cabang); ?>.<br>
                    Data pesanan Anda telah tersimpan otomatis di sistem kami.
                </p>
                <a href="index.php" class="btn-next" style="display: inline-block; text-decoration: none; margin-top: 25px; width: auto; padding: 12px 30px;">Kembali ke Halaman Utama</a>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        // Data Diskon Dinamis dari PHP
        const configDiskon = {
            weekendPercent: <?php echo $disc_weekend_percent; ?>,
            weekendActive: <?php echo $disc_weekend_active ? 'true' : 'false'; ?>,
            ultahPercent: <?php echo $disc_ultah_percent; ?>,
            ultahActive: <?php echo $disc_ultah_active ? 'true' : 'false'; ?>,
            nasionalPercent: <?php echo $disc_nasional_percent; ?>,
            nasionalActive: <?php echo $disc_nasional_active ? 'true' : 'false'; ?>
        };

        // DAFTAR TANGGAL HARI BESAR / LIBUR NASIONAL (Format: YYYY-MM-DD)
        const daftarHariBesar = [
            "2026-01-01", // Tahun Baru Masehi
            "2026-01-16", // Isra Mi'raj
            "2026-02-17", // Tahun Baru Imlek
            "2026-03-19", // Hari Raya Nyepi
            "2026-03-20", // Idul Fitri (Hari 1)
            "2026-03-21", // Idul Fitri (Hari 2)
            "2026-04-03", // Wafat Yesus Kristus
            "2026-05-01", // Hari Buruh
            "2026-05-14", // Kenaikan Yesus Kristus
            "2026-05-27", // Idul Adha
            "2026-05-31", // Hari Raya Waisak
            "2026-06-01", // Hari Lahir Pancasila
            "2026-06-16", // Tahun Baru Islam
            "2026-08-17", // Hari Kemerdekaan RI
            "2026-08-25", // Maulid Nabi Muhammad SAW
            "2026-12-25"  // Hari Natal
        ];

        const todayIsoStr = "<?php echo date('Y-m-d'); ?>";
        let selectedDateStr = todayIsoStr;
        let stripBaseDate = new Date(todayIsoStr + "T00:00:00");

        const namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        const namaHariShort = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
        const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        let selectedPackage = { name: 'Premium Beef', duration: '150 mnt', priceDewasa: 279000, priceAnak: 198000, priceSenior: 249000 };
        let guestCounts = { dewasa: 0, anak: 0, senior: 0 };
        const formatRp = (num) => "Rp " + num.toLocaleString('id-ID');

        // Pengecekan Apakah Tanggal Terpilih Adalah Hari Besar
        function checkHariBesar() {
            let boxNasional = document.getElementById('box-promo-nasional');
            let chkNasional = document.getElementById('chk-nasional');

            if (!boxNasional || !chkNasional) return;

            let isHariBesar = daftarHariBesar.includes(selectedDateStr);

            if (isHariBesar && configDiskon.nasionalActive) {
                boxNasional.style.display = 'block';
            } else {
                boxNasional.style.display = 'none';
                chkNasional.checked = false; // Otomatis matikan centang jika memilih tanggal biasa
            }
        }

        function renderDateStrip() {
            let container = document.getElementById('date-strip-container');
            if (!container) return;
            container.innerHTML = '';

            let currentSelObj = new Date(selectedDateStr + "T00:00:00");
            
            let dayName = namaHari[currentSelObj.getDay()];
            let dateNum = currentSelObj.getDate();
            let monthName = namaBulan[currentSelObj.getMonth()];
            let yearNum = currentSelObj.getFullYear();
            document.getElementById('display-date-text').innerText = `${dayName}, ${dateNum} ${monthName} ${yearNum}`;

            let dayOfWeek = currentSelObj.getDay();
            let isWeekend = (dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6);
            
            let weekendBanner = document.getElementById('weekend-discount-banner');
            if (isWeekend && configDiskon.weekendActive) {
                weekendBanner.style.display = 'block';
                document.getElementById('weekend-badge-text').innerText = `🎉 Diskon ${configDiskon.weekendPercent}% Weekend (Jumat - Minggu) Aktif!`;
            } else {
                weekendBanner.style.display = 'none';
            }

            // Jalankan Filter Pengecekan Hari Besar
            checkHariBesar();

            for (let i = 0; i < 7; i++) {
                let tempDate = new Date(stripBaseDate);
                tempDate.setDate(stripBaseDate.getDate() + i);

                let yyyy = tempDate.getFullYear();
                let mm = String(tempDate.getMonth() + 1).padStart(2, '0');
                let dd = String(tempDate.getDate()).padStart(2, '0');
                let isoStr = `${yyyy}-${mm}-${dd}`;

                let isPast = isoStr < todayIsoStr;

                let card = document.createElement('div');
                card.className = 'date-card' + (isoStr === selectedDateStr ? ' selected' : '') + (isPast ? ' disabled' : '');

                if (!isPast) {
                    card.onclick = function() {
                        selectedDateStr = isoStr;
                        renderDateStrip();
                        updateLiveEstimate();
                    };
                }

                card.innerHTML = `
                    <div class="date-card-num">${tempDate.getDate()}</div>
                    <div class="date-card-day">${namaHariShort[tempDate.getDay()]}</div>
                `;

                container.appendChild(card);
            }
        }

        function shiftDateStrip(days) {
            let newBase = new Date(stripBaseDate);
            newBase.setDate(newBase.getDate() + days);
            
            let todayObj = new Date(todayIsoStr + "T00:00:00");
            if (newBase >= todayObj) {
                stripBaseDate = newBase;
            } else {
                stripBaseDate = new Date(todayIsoStr + "T00:00:00");
            }
            renderDateStrip();
        }

        function onCalendarSelect(val) {
            if (val) {
                if (val < todayIsoStr) {
                    alert('Maaf, Anda tidak dapat memilih tanggal yang sudah lewat.');
                    val = todayIsoStr;
                    document.getElementById('hidden-date-picker').value = todayIsoStr;
                }
                selectedDateStr = val;
                stripBaseDate = new Date(val + "T00:00:00");
                renderDateStrip();
                updateLiveEstimate();
            }
        }

        function goToStep(step) {
            if (step === 2) {
                let totalTamu = guestCounts.dewasa + guestCounts.anak + guestCounts.senior;
                if (totalTamu < 1) {
                    let alertBox = document.getElementById('guest-alert-box');
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    return;
                } else {
                    let alertBox = document.getElementById('guest-alert-box');
                    if (alertBox) alertBox.style.display = 'none';
                }
            }

            document.getElementById('step-1').style.display = (step === 1) ? 'block' : 'none';
            document.getElementById('step-2').style.display = (step === 2) ? 'block' : 'none';
            document.getElementById('step-3').style.display = (step === 3) ? 'block' : 'none';

            for (let i = 1; i <= 3; i++) {
                let ind = document.getElementById('indicator-' + i);
                if (ind) ind.classList.toggle('active', i === step);
            }

            if (step === 3) calculateSummary();
        }

        // Hitung Estimasi Harga Secara Dinamis Berdasarkan Data DB
        function updateLiveEstimate() {
            let container = document.getElementById('live-price-rows');
            let html = '';

            let totalDewasa = guestCounts.dewasa * selectedPackage.priceDewasa;
            let totalAnak = guestCounts.anak * selectedPackage.priceAnak;
            let totalSenior = guestCounts.senior * selectedPackage.priceSenior;
            let subtotal = totalDewasa + totalAnak + totalSenior;
            let totalTamu = guestCounts.dewasa + guestCounts.anak + guestCounts.senior;

            if (guestCounts.dewasa > 0) html += `<div class="price-row"><span>${guestCounts.dewasa} Dewasa (Umum)</span><span>${formatRp(totalDewasa)}</span></div>`;
            if (guestCounts.anak > 0) html += `<div class="price-row"><span>${guestCounts.anak} Anak (≥100cm)</span><span>${formatRp(totalAnak)}</span></div>`;
            if (guestCounts.senior > 0) html += `<div class="price-row"><span>${guestCounts.senior} Senior (≥65th)</span><span>${formatRp(totalSenior)}</span></div>`;

            let runningSubtotal = subtotal;

            // 1. Promo Pay 4 Get 5
            let pay4get5Disc = 0;
            if (totalTamu >= 5 && selectedPackage.name !== 'Starter Package') {
                let freeCount = Math.floor(totalTamu / 5);
                pay4get5Disc = freeCount * selectedPackage.priceDewasa;
                document.getElementById('pay4get5-banner').style.display = 'block';
                html += `<div class="price-row discount-line"><span>Promo Pay 4 Get 5 (${freeCount} Orang Gratis)</span><span>-${formatRp(pay4get5Disc)}</span></div>`;
            } else {
                document.getElementById('pay4get5-banner').style.display = 'none';
            }
            runningSubtotal = Math.max(0, runningSubtotal - pay4get5Disc);

            // 2. Promo Ulang Tahun (Dinamis DB)
            let isUltah = document.getElementById('chk-ultah').checked;
            let ultahDisc = 0;
            if (isUltah && configDiskon.ultahActive && runningSubtotal > 0) {
                ultahDisc = Math.round(runningSubtotal * (configDiskon.ultahPercent / 100));
                html += `<div class="price-row discount-line"><span>Diskon Ulang Tahun ${configDiskon.ultahPercent}%</span><span>-${formatRp(ultahDisc)}</span></div>`;
            }
            runningSubtotal = Math.max(0, runningSubtotal - ultahDisc);

            // 3. Promo Hari Besar / Nasional (Dinamis DB & Hanya jika Hari Besar)
            let isHariBesar = daftarHariBesar.includes(selectedDateStr);
            let isNasional = document.getElementById('chk-nasional').checked && isHariBesar;
            let nasionalDisc = 0;
            if (isNasional && configDiskon.nasionalActive && runningSubtotal > 0) {
                nasionalDisc = Math.round(runningSubtotal * (configDiskon.nasionalPercent / 100));
                html += `<div class="price-row discount-line"><span>Diskon Hari Nasional ${configDiskon.nasionalPercent}%</span><span>-${formatRp(nasionalDisc)}</span></div>`;
            }
            runningSubtotal = Math.max(0, runningSubtotal - nasionalDisc);

            // 4. Diskon Weekend (Dinamis DB)
            let dateObj = new Date(selectedDateStr + "T00:00:00");
            let dayOfWeek = dateObj.getDay();
            let isWeekend = (dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6);
            let weekendDisc = 0;

            if (isWeekend && configDiskon.weekendActive && runningSubtotal > 0) {
                weekendDisc = Math.round(runningSubtotal * (configDiskon.weekendPercent / 100));
                html += `<div class="price-row discount-line"><span>Diskon Weekend ${configDiskon.weekendPercent}%</span><span>-${formatRp(weekendDisc)}</span></div>`;
            }
            runningSubtotal = Math.max(0, runningSubtotal - weekendDisc);

            let service = Math.round(runningSubtotal * 0.05);
            let ppn = Math.round((runningSubtotal + service) * 0.10);
            let total = runningSubtotal + service + ppn;

            html += `<div class="price-row"><span>Service 5%</span><span>${formatRp(service)}</span></div>`;
            html += `<div class="price-row"><span>PPN 10%</span><span>${formatRp(ppn)}</span></div>`;
            html += `<div class="price-row total-line"><span>Total</span><span class="total-val">${formatRp(total)}</span></div>`;

            container.innerHTML = html;
        }

        function calculateSummary() {
            let selectedTimeBtn = document.querySelector('.time-btn.selected');
            let timeVal = selectedTimeBtn ? selectedTimeBtn.innerText : '20:00';

            let nama = document.getElementById('input-nama').value || '-';
            let wa = document.getElementById('input-wa').value || '-';
            let email = document.getElementById('input-email').value || '-';
            let catatan = document.getElementById('input-catatan').value || '';
            let isUltah = document.getElementById('chk-ultah').checked;

            let isHariBesar = daftarHariBesar.includes(selectedDateStr);
            let isNasional = document.getElementById('chk-nasional').checked && isHariBesar;

            let subtotal = (guestCounts.dewasa * selectedPackage.priceDewasa) + (guestCounts.anak * selectedPackage.priceAnak) + (guestCounts.senior * selectedPackage.priceSenior);
            let totalTamu = guestCounts.dewasa + guestCounts.anak + guestCounts.senior;
            
            let runningSubtotal = subtotal;

            let pay4get5Disc = (totalTamu >= 5 && selectedPackage.name !== 'Starter Package') ? Math.floor(totalTamu / 5) * selectedPackage.priceDewasa : 0;
            runningSubtotal = Math.max(0, runningSubtotal - pay4get5Disc);

            let ultahDisc = (isUltah && configDiskon.ultahActive) ? Math.round(runningSubtotal * (configDiskon.ultahPercent / 100)) : 0;
            runningSubtotal = Math.max(0, runningSubtotal - ultahDisc);

            let nasionalDisc = (isNasional && configDiskon.nasionalActive) ? Math.round(runningSubtotal * (configDiskon.nasionalPercent / 100)) : 0;
            runningSubtotal = Math.max(0, runningSubtotal - nasionalDisc);

            let dateObj = new Date(selectedDateStr + "T00:00:00");
            let dayOfWeek = dateObj.getDay();
            let isWeekend = (dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6);
            let weekendDisc = (isWeekend && configDiskon.weekendActive) ? Math.round(runningSubtotal * (configDiskon.weekendPercent / 100)) : 0;
            runningSubtotal = Math.max(0, runningSubtotal - weekendDisc);

            let totalDiskon = pay4get5Disc + ultahDisc + nasionalDisc + weekendDisc;

            let service = Math.round(runningSubtotal * 0.05);
            let ppn = Math.round((runningSubtotal + service) * 0.10);
            let total = runningSubtotal + service + ppn;

            let guestTextArr = [];
            if (guestCounts.dewasa > 0) guestTextArr.push(`${guestCounts.dewasa} Dewasa`);
            if (guestCounts.anak > 0) guestTextArr.push(`${guestCounts.anak} Anak (≥100cm)`);
            if (guestCounts.senior > 0) guestTextArr.push(`${guestCounts.senior} Senior (≥65th)`);
            let guestText = guestTextArr.join(' · ') || '0 Tamu';

            let formattedDate = `${namaHari[dayOfWeek]}, ${dateObj.getDate()} ${namaBulan[dateObj.getMonth()]} ${dateObj.getFullYear()}`;

            document.getElementById('sum-paket').innerText = `${selectedPackage.name} - ${selectedPackage.duration}`;
            document.getElementById('sum-tanggal').innerText = formattedDate;
            document.getElementById('sum-jam').innerText = timeVal;
            document.getElementById('sum-tamu').innerText = guestText;
            document.getElementById('sum-nama').innerText = nama;
            document.getElementById('sum-wa').innerText = wa;
            document.getElementById('sum-email').innerText = email;

            document.getElementById('sum-subtotal').innerText = formatRp(subtotal);
            
            let diskonRow = document.getElementById('sum-diskon-row');
            if (totalDiskon > 0) {
                diskonRow.style.display = 'flex';
                document.getElementById('sum-diskon').innerText = `-${formatRp(totalDiskon)}`;
            } else {
                diskonRow.style.display = 'none';
            }

            document.getElementById('sum-service').innerText = formatRp(service);
            document.getElementById('sum-ppn').innerText = formatRp(ppn);
            document.getElementById('sum-total').innerText = formatRp(total);

            document.getElementById('post_paket_nama').value = selectedPackage.name;
            document.getElementById('post_tanggal').value = selectedDateStr;
            document.getElementById('post_jam').value = timeVal;
            document.getElementById('post_dewasa').value = guestCounts.dewasa;
            document.getElementById('post_anak').value = guestCounts.anak;
            document.getElementById('post_senior').value = guestCounts.senior;
            document.getElementById('post_nama').value = nama;
            document.getElementById('post_wa').value = wa;
            document.getElementById('post_email').value = email;
            document.getElementById('post_catatan').value = catatan;
            document.getElementById('post_is_ultah').value = isUltah ? '1' : '0';
            document.getElementById('post_is_nasional').value = isNasional ? '1' : '0';
        }

        function selectTime(element) {
            document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('selected'));
            element.classList.add('selected');
        }

        function selectPackage(element, name, duration, pDewasa, pAnak, pSenior) {
            document.querySelectorAll('.package-item').forEach(item => item.classList.remove('selected'));
            element.classList.add('selected');

            selectedPackage = { name: name, duration: duration, priceDewasa: pDewasa, priceAnak: pAnak, priceSenior: pSenior };
            document.getElementById('lbl-price-dewasa').innerText = `${formatRp(pDewasa)}++ / orang`;
            document.getElementById('lbl-price-anak').innerText = `${formatRp(pAnak)}++ / orang`;
            document.getElementById('lbl-price-senior').innerText = `${formatRp(pSenior)}++ / orang`;

            updateLiveEstimate();
        }

        function updateGuest(type, change) {
            let current = guestCounts[type] || 0;
            let updated = current + change;
            if (updated >= 0) {
                guestCounts[type] = updated;
                document.getElementById('count-' + type).innerText = updated;
                
                let totalTamu = guestCounts.dewasa + guestCounts.anak + guestCounts.senior;
                if (totalTamu > 0) {
                    let alertBox = document.getElementById('guest-alert-box');
                    if (alertBox) alertBox.style.display = 'none';
                }

                updateLiveEstimate();
            }
        }

        window.onload = function() {
            renderDateStrip();
            updateLiveEstimate();
        };
    </script>
</body>
</html>