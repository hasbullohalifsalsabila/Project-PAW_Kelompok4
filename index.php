<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LMS Modern – Belajar Lebih Mudah</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
    /* --- 1. RESET & GLOBAL STYLE --- */
    * { box-sizing: border-box; }
    
    body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        background-color: #fdfbf7; /* Krem Hangat (Sama seperti Dashboard) */
        color: #4a4a4a;
        line-height: 1.6;
    }

    h1, h2, h3 {
        font-family: 'Playfair Display', serif; /* Font Judul Elegan */
        color: #1a1a1a;
        margin-top: 0;
    }

    a { text-decoration: none; }

    /* Container agar konten tidak terlalu lebar di layar besar */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* --- 2. NAVBAR --- */
    .navbar {
        background-color: #ffffff;
        padding: 15px 0;
        box-shadow: 0 2px 15px rgba(0,0,0,0.03);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .nav-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 1.2rem;
        color: #2c3e50;
        font-family: 'Playfair Display', serif;
    }

    .logo img {
        height: 40px;
        border-radius: 50%;
    }

    .nav-right a {
        margin-left: 15px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .btn-login { color: #2c3e50; }
    .btn-login:hover { color: #c49b63; }

    /* --- 3. TOMBOL UMUM --- */
    .btn-dark {
        background-color: #2c3e50; /* Biru Gelap */
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
        transition: all 0.3s;
        box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2);
    }

    .btn-dark:hover {
        background-color: #c49b63; /* Emas saat hover */
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(196, 155, 99, 0.3);
    }

    /* --- 4. HERO SECTION --- */
    .hero {
        padding: 80px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 50px;
    }

    .hero-left { flex: 1; }
    
    .hero-title {
        font-size: 3.5rem;
        line-height: 1.2;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .hero-desc {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 30px;
        max-width: 90%;
    }

    .hero-right { flex: 1; text-align: center; }

    .hero-img {
        width: 100%;
        max-width: 500px;
        border-radius: 20px;
        box-shadow: 20px 20px 0px #e0dbd0; /* Efek Frame Artistik */
    }

    /* --- 5. FEATURES SECTION --- */
    .features {
        padding: 80px 0;
        background-color: #faf9f6; /* Warna sedikit lebih gelap dari body */
    }

    .section-title {
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 50px;
        color: #2c3e50;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        border: 1px solid #f0ece3;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        text-align: center;
        transition: transform 0.3s, border-color 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        border-color: #c49b63;
    }

    /* Menggunakan ikon Font Awesome */
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        display: block;
        color: #c49b63; /* Memberi warna emas pada ikon */
    }

    .feature-card h3 {
        font-size: 1.25rem;
        margin-bottom: 15px;
        color: #2c3e50;
    }

    .feature-card p {
        font-size: 0.95rem;
        color: #777;
    }

    /* --- 6. CTA SECTION --- */
    .cta {
        padding: 100px 0;
        text-align: center;
        background-color: #2c3e50; /* Background Gelap */
        color: white;
    }

    .cta h1 { color: #fdfbf7; margin-bottom: 10px; }
    .cta p { color: #b0b8c1; margin-bottom: 30px; font-size: 1.1rem; }
    
    .cta .btn-dark {
        background-color: #c49b63; /* Tombol Emas */
        color: white;
    }
    .cta .btn-dark:hover {
        background-color: white;
        color: #2c3e50;
    }

    /* --- 7. FOOTER --- */
    .footer {
        padding: 30px 0;
        text-align: center;
        background-color: #1a252f;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    /* --- RESPONSIF (HP) --- */
    @media (max-width: 768px) {
        .hero { flex-direction: column-reverse; text-align: center; padding: 40px 0; }
        .hero-title { font-size: 2.5rem; }
        .hero-desc { margin: 0 auto 30px; }
        .hero-img { max-width: 80%; margin-bottom: 30px; }
    }
</style>
</head>

<body>

<nav class="navbar">
    <div class="container nav-content">
        <div class="logo">
            <img src="assets/img/logo.jpeg" alt="Logo">
            <span>EduCourse</span>
        </div>

        <div class="nav-right">
            <a class="btn-login" href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Masuk</a>
            <a class="btn-dark" href="auth/register.php"><i class="fas fa-user-plus"></i> Daftar Sekarang</a>
        </div>
    </div>
</nav>

<div class="container">
    <section class="hero">
        <div class="hero-left" data-aos="fade-right" data-aos-duration="1000">
            <h1 class="hero-title">Belajar Skill Baru<br>Dengan Cara Elegan.</h1>
            <p class="hero-desc">
                Platform pembelajaran online dengan desain modern, materi berkualitas, dan sertifikasi resmi. Tingkatkan karir Anda mulai hari ini.
            </p>
            <a href="auth/register.php" class="btn-dark">Mulai Belajar Gratis &rarr;</a>
        </div>

        <div class="hero-right" data-aos="fade-left" data-aos-duration="1000">
            <img src="assets/img/logo.jpeg" class="hero-img" alt="Hero Image">
        </div>
    </section>
</div>

<section class="features">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Kenapa Memilih Kami?</h2>

        <div class="feature-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <span class="feature-icon"><i class="fas fa-award"></i></span>
                <h3>Kursus Berkualitas</h3>
                <p>Materi disusun oleh para ahli industri dengan kurikulum yang terupdate dan relevan.</p>
            </div>

            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <span class="feature-icon"><i class="fas fa-mobile-alt"></i></span>
                <h3>Akses Fleksibel</h3>
                <p>Belajar kapan saja dan di mana saja. Akses materi seumur hidup melalui HP atau Laptop.</p>
            </div>

            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <span class="feature-icon"><i class="fas fa-certificate"></i></span>
                <h3>Sertifikat Resmi</h3>
                <p>Validasi skill Anda dengan sertifikat resmi yang diakui setelah menyelesaikan kursus.</p>
            </div>

            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <span class="feature-icon"><i class="fas fa-gem"></i></span>
                <h3>Investasi Cerdas</h3>
                <p>Harga kompetitif untuk materi premium. Investasi terbaik untuk masa depan karir Anda.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container" data-aos="zoom-in" data-aos-duration="1000">
        <h1>Siap Menjadi Versi Terbaik Anda?</h1>
        <p>Bergabung bersama ribuan pelajar lainnya dan raih mimpi Anda sekarang!</p>
        <a class="btn-dark" href="auth/register.php">Buat Akun Sekarang</a>
    </div>
</section>

<footer class="footer">
    <p>© <?=date('Y')?> EduCourse. All rights reserved.</p>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        once: true // Animasi hanya berjalan sekali saat scrolling ke bawah
    });
</script>

</body>
</html>