<?php
session_start();
include_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karesto - Japanese BBQ & Shabu-Shabu</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #fff; color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        header {
            background-color: #8B0000;
            background-image: linear-gradient(rgba(139, 0, 0, 0.95), rgba(139, 0, 0, 0.95)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100');
            color: white; padding: 12px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Pengaturan agar logo gambar dan teks sejajar rapi */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { height: 52px; width: auto; object-fit: contain; border-radius: 6px; }
        .logo-text h1 { font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #fff; line-height: 1.2; }
        .logo-text span { font-size: 10px; display: block; letter-spacing: 1px; color: #ffcccc; }
        
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a { font-size: 13px; font-weight: 600; color: #fff; transition: color 0.2s; }
        nav ul li a:hover, nav ul li a.active { color: #ffcccc; }

        .hero-banner {
            height: 350px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1544025162-d76694265947?w=1200') center/cover no-repeat;
        }

        .welcome-section { padding: 60px 20px; max-width: 1100px; margin: 0 auto; display: flex; align-items: center; gap: 40px; flex-wrap: wrap; }
        .welcome-text { flex: 1; min-width: 300px; }
        .welcome-text h4 { color: #8B0000; font-size: 12px; letter-spacing: 2px; margin-bottom: 5px; }
        .welcome-text h2 { font-size: 26px; margin-bottom: 15px; color: #222; }

        .stats-box { display: flex; background-color: #8B0000; color: white; padding: 15px; border-radius: 4px; margin-top: 20px; text-align: center; }
        .stat-item { flex: 1; border-right: 1px solid rgba(255,255,255,0.2); padding: 0 10px; }
        .stat-item:last-child { border-right: none; }
        .stat-item strong { display: block; font-size: 18px; }
        .stat-item span { font-size: 11px; opacity: 0.9; }

        .welcome-gallery { flex: 1; min-width: 300px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .welcome-gallery img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }

        .teppanyaki-banner {
            height: 220px; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=1200') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center; color: white;
        }
        .teppanyaki-banner h2 { font-size: 32px; letter-spacing: 3px; border-bottom: 2px solid #8B0000; padding-bottom: 5px; }

        .ayce-section { padding: 60px 20px; max-width: 1100px; margin: 0 auto; }
        .ayce-container { display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap; }
        .ayce-main-img { flex: 1; min-width: 320px; }
        .ayce-main-img img { width: 100%; height: 320px; object-fit: cover; border-radius: 8px; }
        .ayce-content { flex: 1; min-width: 300px; }
        .ayce-content h3 { font-size: 22px; color: #222; margin-bottom: 12px; }
        .ayce-content p { font-size: 13px; color: #666; margin-bottom: 20px; }
        
        .btn-more { display: inline-block; background-color: #8B0000; color: white; padding: 10px 20px; font-size: 12px; font-weight: bold; border-radius: 4px; }
        .menu-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 30px; }
        .menu-card { position: relative; height: 120px; border-radius: 6px; overflow: hidden; }
        .menu-card img { width: 100%; height: 100%; object-fit: cover; }
        .menu-card-title { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; font-size: 11px; padding: 5px; text-align: center; font-weight: bold; }

        .halal-banner {
            height: 180px; background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1200') center/cover no-repeat;
            display: flex; align-items: center; justify-content: center; gap: 15px; color: white;
        }
        .halal-badge { background: white; color: #111; padding: 8px 12px; font-weight: bold; border-radius: 4px; font-size: 14px; }
        .halal-text { font-size: 24px; font-weight: bold; letter-spacing: 2px; }

        footer {
            background-color: #7A0000; background-image: linear-gradient(rgba(122, 0, 0, 0.96), rgba(122, 0, 0, 0.96)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100');
            color: white; padding: 50px 50px 20px 50px;
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
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="pricelist.php">Paket & Harga</a></li>
                <li><a href="outlet.php">Outlet</a></li>
                <li><a href="promo.php">Promo</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero-banner"></section>

    <section class="welcome-section">
        <div class="welcome-text">
            <h4>WELCOME TO</h4>
            <h2>KARESTO JAPANESE BBQ & SHABU - SHABU</h2>
            <p>Nikmati kelezatan hidangan Japanese BBQ dan Shabu-Shabu pilihan dengan bahan-bahan berkualitas tinggi dan suasana restoran yang nyaman untuk keluarga maupun kerabat.</p>

            <div class="stats-box">
                <div class="stat-item"><strong>100%</strong><span>Halal</span></div>
                <div class="stat-item"><strong>100%</strong><span>Kualitas Terjamin</span></div>
                <div class="stat-item"><strong>10+</strong><span>Outlet di seluruh Indonesia</span></div>
            </div>
        </div>

        <div class="welcome-gallery">
            <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=400" alt="BBQ Grill">
            <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400" alt="Teppanyaki">
            <img src="https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400" alt="Vegetables">
            <img src="https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=400" alt="Shabu Soup">
        </div>
    </section>

    <section class="teppanyaki-banner">
        <h2>LIVE TEPPANYAKI</h2>
    </section>

    <section class="ayce-section">
        <div class="ayce-container">
            <div class="ayce-main-img">
                <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=600" alt="All You Can Eat Grill">
            </div>

            <div class="ayce-content">
                <h3>ALL YOU CAN EAT</h3>
                <p>Selamat datang di Karesto! Karesto adalah restoran Jepang yang memiliki konsep All You Can Eat BBQ Grill dan Shabu-Shabu. Kami menyajikan beragam Daging Impor Segar, Side Dish, serta Pilihan Kuah Gurih Autentik.</p>
                <a href="pricelist.php" class="btn-more">MORE INFO</a>

                <div class="menu-grid">
                    <div class="menu-card">
                        <img src="https://images.unsplash.com/photo-1558030006-450675393462?w=300" alt="Popular Beef">
                        <div class="menu-card-title">POPULAR BEEF</div>
                    </div>
                    <div class="menu-card">
                        <img src="https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=300" alt="Fresh Vegetables">
                        <div class="menu-card-title">FRESH VEGETABLES</div>
                    </div>
                    <div class="menu-card">
                        <img src="https://images.unsplash.com/photo-1541544741938-0af808871cc0?w=300" alt="Shabu-Shabu">
                        <div class="menu-card-title">SHABU-SHABU</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="halal-banner">
        <div class="halal-badge">HALAL INDONESIA</div>
        <div class="halal-text">100% HALAL</div>
    </section>

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
                <p>Email:fairuuzzakifadhlullah@gmail.com</p>
            </div>
        </div>

        <div class="copyright">
            © 2026 Karesto. All Rights Reserved. | 
            <a href="login.php" style="color: inherit; text-decoration: none; cursor: pointer;">Admin Area</a>
        </div>
    </footer>

</body>
</html>