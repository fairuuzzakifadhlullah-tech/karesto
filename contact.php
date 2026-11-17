<?php
session_start();
include_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Karesto</title>
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

        .container { max-width: 800px; margin: 50px auto; padding: 0 20px; }
        .contact-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; color: #444; margin-bottom: 5px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        
        .btn-submit {
            background: #8B0000; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;
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
                <li><a href="outlet.php">Outlet</a></li>
                <li><a href="promo.php">Promo</a></li>
                <li><a href="contact.php" class="active">Contact Us</a></li>
            </ul>
        </nav>
    </header>

    <div class="page-header">
        <h2>HUBUNGI KAMI</h2>
    </div>

    <div class="container">
        <div class="contact-card">
            <h3 style="color: #8B0000; margin-bottom: 15px;">Kirim Pesan</h3>
            <form action="#" method="POST" onsubmit="alert('Pesan Anda berhasil dikirim!'); return false;">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label>Pesan / Pertanyaan</label>
                    <textarea rows="4" placeholder="Tuliskan pesan Anda..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Kirim Pesan</button>
            </form>
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