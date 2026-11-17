<?php
session_start();
include_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Karesto Japanese BBQ</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #fff; color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        header {
            background-color: #8B0000;
            background-image: linear-gradient(rgba(139, 0, 0, 0.95), rgba(139, 0, 0, 0.95)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100');
            color: white; padding: 12px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Pengaturan agar gambar logo PNG dan teks sejajar rapi */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { height: 52px; width: auto; object-fit: contain; }
        .logo-text h1 { font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #fff; line-height: 1.2; }
        .logo-text span { font-size: 10px; display: block; letter-spacing: 1px; color: #ffcccc; }
        
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a { font-size: 13px; font-weight: 600; color: #fff; transition: color 0.2s; }
        nav ul li a:hover, nav ul li a.active { color: #ffcccc; }

        .page-header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1544025162-d76694265947?w=1200') center/cover;
            color: white; text-align: center; padding: 60px 20px;
        }
        .page-header h2 { font-size: 32px; letter-spacing: 2px; }

        .content-container { max-width: 900px; margin: 50px auto; padding: 0 20px; text-align: center; }
        .content-container h3 { font-size: 24px; color: #8B0000; margin-bottom: 20px; }
        .content-container p { font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.8; text-align: justify; }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px; }
        .feature-card { background: #fdf8f8; border: 1px solid #f0d8d8; padding: 25px; border-radius: 8px; }
        .feature-card h4 { color: #8B0000; margin-bottom: 10px; }

        footer {
            background-color: #7A0000; background-image: linear-gradient(rgba(122, 0, 0, 0.96), rgba(122, 0, 0, 0.96)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100');
            color: white; padding: 50px 50px 20px 50px; margin-top: 60px;
        }
        .footer-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1.5fr 1fr 1fr; gap: 30px; }
        .footer-col h4 { font-size: 14px; margin-bottom: 15px; color: #fff; }
        .footer-col p { font-size: 12px; color: #ddd; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 6px; }
        .footer-col ul li a { font-size: 12px; color: #eee; }
        .copyright { text-align: center; font-size: 11px; color: #ccc; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <img src="logo-karesto-removebg-preview.png" alt="Logo Karesto">
            <div class="logo-text">
                <h1>KARESTO</h1>
                <span>BBQ & SHABU - SHABU</span>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php" class="active">About Us</a></li>
                <li><a href="pricelist.php">Paket & Harga</a></li>
                <li><a href="outlet.php">Outlet</a></li>
                <li><a href="promo.php">Promo</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-header">
        <h2>TENTANG KARESTO</h2>
    </div>

    <div class="content-container">
        <h3>Restoran Japanese All You Can Eat Terbaik</h3>
        <p>Karesto berdiri dengan komitmen menyajikan hidangan All You Can Eat Japanese BBQ dan Shabu-Shabu yang lezat, higienis, dan 100% Halal. Kami percaya bahwa momen bersantap bersama keluarga dan kerabat adalah waktu yang berharga.</p>
        <p>Setiap potongan daging sapi premium yang kami sediakan dipilih secara teliti untuk menjaga kualitas serta kelembutan rasa terbaik saat dipanggang di atas grill khas Jepang.</p>

        <div class="feature-grid">
            <div class="feature-card">
                <h4>Daging Segar Premium</h4>
                <p style="font-size: 12px; text-align: center; margin:0;">Pilihan irisan daging Impor, Wagyu, dan Saikoro berkualitas tinggi.</p>
            </div>
            <div class="feature-card">
                <h4>100% Halal Certified</h4>
                <p style="font-size: 12px; text-align: center; margin:0;">Seluruh bahan baku dan proses pengolahan dijamin kehalalannya.</p>
            </div>
            <div class="feature-card">
                <h4>Kuah Shabu Autentik</h4>
                <p style="font-size: 12px; text-align: center; margin:0;">Pilihan rasa kuah Shabu-Shabu gurih yang memanjakan lidah.</p>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo" style="margin-bottom: 15px;">
                    <img src="logo-karesto-removebg-preview.png" alt="Logo Karesto" style="height: 45px;">
                    <div class="logo-text">
                        <h1 style="font-size: 18px;">KARESTO</h1>
                        <span>BBQ & SHABU - SHABU</span>
                    </div>
                </div>
                <p>Autentik All You Can Eat<br>Momen Rasa Yang Semakin Erat<br>karesto.co.id</p>
            </div>

            <div class="footer-col">
                <h4>Outlet</h4>
                <ul>
                    <li><a href="outlet.php?cabang=solo">Solo</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="pricelist.php">Paket & Harga</a></li>
                    <li><a href="outlet.php">Outlet</a></li>
                    <li><a href="promo.php">Promo</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contact</h4>
                <p>Email: fairuuzzakifadhlullah@gmail.com</p>
            </div>
        </div>

        <div class="copyright">© 2026 Karesto. All Rights Reserved.</div>
    </footer>

</body>
</html>