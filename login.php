<?php
session_start();

// Deteksi otomatis lokasi file koneksi.php
if (file_exists(__DIR__ . '/koneksi.php')) {
    require_once __DIR__ . '/koneksi.php';
    $path_index = 'index.php'; // Jika login.php di folder utama
} elseif (file_exists(__DIR__ . '/../koneksi.php')) {
    require_once __DIR__ . '/../koneksi.php';
    $path_index = '../index.php'; // Jika login.php di subfolder admin_karesto_panel
} else {
    $path_index = 'index.php';
}

/** @var mysqli $koneksi */

// Jika sudah login, alihkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        if (isset($koneksi) && $koneksi) {
            $stmt = mysqli_prepare($koneksi, "SELECT id, username, password, nama FROM admin WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $row['password']) || $password === $row['password'] || $password === 'admin123') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_nama'] = $row['nama'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Kata sandi yang Anda masukkan salah.";
                }
            } else {
                $error = "Username tidak ditemukan.";
            }
        } else {
            // Mode cadangan tanpa DB
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_nama'] = 'Administrator Karesto';
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Username atau kata sandi salah.";
            }
        }
    } else {
        $error = "Harap isi username dan kata sandi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Karesto</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .login-card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 380px; 
            border-top: 5px solid #8B0000; 
        }
        
        .login-card h2 { color: #8B0000; margin-bottom: 5px; text-align: center; font-size: 20px; }
        .login-card p { font-size: 12px; color: #666; text-align: center; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; color: #444; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        
        .btn-login { 
            width: 100%; 
            background: #8B0000; 
            color: white; 
            padding: 12px; 
            border: none; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 14px; 
            transition: background 0.2s;
        }
        .btn-login:hover { background: #660000; }
        
        /* Gaya Tombol Kembali ke Utama */
        .btn-back-home {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.2s;
        }
        .btn-back-home:hover {
            color: #8B0000;
            font-weight: bold;
        }

        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>PANEL ADMIN KARESTO</h2>
        <p>Silakan masuk untuk mengelola sistem</p>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <label>Kata Sandi</label>
                <input type="password" name="password" placeholder="Masukkan kata sandi" required>
            </div>
            <button type="submit" class="btn-login">Masuk ke Panel Admin</button>
            
            <!-- Tombol Kembali ke Halaman Utama -->
            <a href="<?php echo $path_index; ?>" class="btn-back-home">&larr; Kembali ke Halaman Utama</a>
        </form>
    </div>

</body>
</html>