<?php
session_start();
include_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Outlet - Karesto</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f9f9f9; color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        header {
            background-color: #8B0000;
            background-image: linear-gradient(rgba(139, 0, 0, 0.95), rgba(139, 0, 0, 0.95)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100');
            color: white; padding: 12px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Pengaturan agar logo PNG dan teks sejajar rapi */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { height: 52px; width: auto; object-fit: contain; }
        .logo-text h1 { font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #fff; line-height: 1.2; }
        .logo-text span { font-size: 10px; display: block; letter-spacing: 1px; color: #ffcccc; }
        
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a { font-size: 13px; font-weight: 600; color: #fff; transition: color 0.2s; }
        nav ul li a:hover, nav ul li a.active { color: #ffcccc; }

        .page-header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200') center/cover;
            color: white; text-align: center; padding: 60px 20px;
        }
        .page-header h2 { font-size: 32px; letter-spacing: 2px; }

        .container { max-width: 1100px; margin: 50px auto; padding: 0 20px; }
        .outlet-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }

        .outlet-card {
            background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #8B0000; display: flex; flex-direction: column; justify-content: space-between;
        }
        .outlet-card h3 { font-size: 18px; color: #8B0000; margin-bottom: 8px; }
        .outlet-card p { font-size: 13px; color: #666; margin-bottom: 15px; }

        .btn-reserve {
            display: inline-block; background: #8B0000; color: white; padding: 10px 18px;
            font-size: 12px; font-weight: bold; border-radius: 6px; text-align: center;
        }

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
                <li><a href="about.php">About Us</a></li>
                <li><a href="pricelist.php">Paket & Harga</a></li>
                <li><a href="outlet.php" class="active">Outlet</a></li>
                <li><a href="promo.php">Promo</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-header">
        <h2>LOKASI OUTLET KARESTO</h2>
    </div>

    <div class="container">
        <div class="outlet-grid">
            <div class="outlet-card">
                <div>
                    <h3>Karesto Solo</h3>
                    <p>Jl. Adi Sucipto No.42, Kerten, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57143</p>
                    <p style="font-size: 12px; color: #888;">Buka: 10:00 - 22:00 WIB</p>
                </div>
                <div style="margin-top: 15px;">
                    <a href="reservasi.php?cabang=solo" class="btn-reserve">Reservasi Solo &rarr;</a>
                </div>
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