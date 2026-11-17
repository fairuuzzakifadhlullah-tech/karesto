<?php
session_start();
include_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket & Harga - Karesto</title>
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
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1200') center/cover;
            color: white; text-align: center; padding: 60px 20px;
        }
        .page-header h2 { font-size: 32px; letter-spacing: 2px; }

        .container { max-width: 1100px; margin: 50px auto; padding: 0 20px; }
        .packages-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }

        .package-card {
            background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 2px solid #eee; display: flex; flex-direction: column; justify-content: space-between;
        }
        .package-card.popular { border-color: #8B0000; position: relative; }
        .popular-badge {
            position: absolute; top: 10px; right: 10px; background: #8B0000; color: white; font-size: 10px;
            font-weight: bold; padding: 4px 8px; border-radius: 4px; text-transform: uppercase;
        }

        .pkg-header { background: #fff8f8; padding: 20px; text-align: center; border-bottom: 1px solid #eee; }
        .pkg-header h3 { font-size: 18px; color: #8B0000; margin-bottom: 5px; }
        .pkg-price { font-size: 22px; font-weight: bold; color: #222; }
        .pkg-price small { font-size: 12px; color: #777; font-weight: normal; }

        .pkg-body { padding: 20px; }
        .pkg-body ul { list-style: none; font-size: 13px; color: #555; }
        .pkg-body ul li { margin-bottom: 10px; padding-left: 20px; position: relative; }
        .pkg-body ul li::before { content: "✓"; position: absolute; left: 0; color: #8B0000; font-weight: bold; }

        .btn-order {
            display: block; width: 85%; margin: 0 auto 20px auto; text-align: center; background: #8B0000; color: white;
            padding: 12px; border-radius: 6px; font-weight: bold; font-size: 13px;
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
                <li><a href="pricelist.php" class="active">Paket & Harga</a></li>
                <li><a href="outlet.php">Outlet</a></li>
                <li><a href="promo.php">Promo</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-header">
        <h2>PAKET & HARGA AYCE</h2>
    </div>

    <div class="container">
        <div class="packages-grid">
            <div class="package-card">
                <div class="pkg-header">
                    <h3>STARTER PACKAGE</h3>
                    <div class="pkg-price">Rp 142.000++ <small>/ orang</small></div>
                    <small style="color:#777;">Durasi: 90 Menit</small>
                </div>
                <div class="pkg-body">
                    <ul>
                        <li>Pilihan Daging Sapi Regular</li>
                        <li>Shabu-Shabu Soup Choice</li>
                        <li>Side Dish & Dessert Buffet</li>
                        <li>Free Flow Drink</li>
                    </ul>
                </div>
                <a href="reservasi.php?cabang=solo" class="btn-order">Pesan Sekarang</a>
            </div>

            <div class="package-card">
                <div class="pkg-header">
                    <h3>REGULAR PACKAGE</h3>
                    <div class="pkg-price">Rp 155.000++ <small>/ orang</small></div>
                    <small style="color:#777;">Durasi: 90 Menit</small>
                </div>
                <div class="pkg-body">
                    <ul>
                        <li>Semua Menu Starter Package</li>
                        <li>Tambahan Marinated Beef</li>
                        <li>Aneka Meatball & Seafood</li>
                        <li>Buffet Seafood Special</li>
                    </ul>
                </div>
                <a href="reservasi.php?cabang=solo" class="btn-order">Pesan Sekarang</a>
            </div>

            <div class="package-card popular">
                <div class="popular-badge">RECOMMENDED</div>
                <div class="pkg-header">
                    <h3>PREMIUM BEEF</h3>
                    <div class="pkg-price">Rp 279.000++ <small>/ orang</small></div>
                    <small style="color:#777;">Durasi: 150 Menit</small>
                </div>
                <div class="pkg-body">
                    <ul>
                        <li>Wagyu Slices & Saikoro Beef</li>
                        <li>Semua Pilihan Daging BBQ Premium</li>
                        <li>All Star Buffet & Live Teppanyaki</li>
                        <li>Spesial Dessert & Ice Cream</li>
                    </ul>
                </div>
                <a href="reservasi.php?cabang=solo" class="btn-order">Pesan Sekarang</a>
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