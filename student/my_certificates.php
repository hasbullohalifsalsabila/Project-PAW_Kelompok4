<?php
require '../config/db.php';
require 'header.php'; // Pastikan header memuat layout & CSS
require 'sidebar.php';

// Pastikan SESSION user ada dan berisi user_id
if (!isset($_SESSION['user']['user_id'])) {
    // Redirect atau handle error jika pengguna belum login
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user']['user_id'];

// Ambil data sertifikat
$certs = $pdo->query("
    SELECT ce.*, c.title AS course_title, c.course_id
    FROM certificates ce
    JOIN enrollments e ON ce.enroll_id = e.enroll_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = " . $pdo->quote($uid) . "
    ORDER BY ce.issued_date DESC
")->fetchAll();
?>

<style>
    /* --- LAYOUT GRID (Sama seperti My Courses) --- */
    .main-content {
        /* Jarak dari Sidebar */
        margin-left: 260px;
        /* Padding untuk konten utama, PENTING: Menambahkan padding-top 80px untuk memberi jarak dari header */
        padding: 80px 40px 40px 40px; 
        background-color: #fdfbf7;
        min-height: 100vh;
    }

    /* PENTING: Tambahkan media query untuk tampilan mobile/tablet */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            padding: 80px 20px 20px 20px; /* Sesuaikan padding untuk mobile */
        }
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: #1a1a1a;
        text-align: left; /* Sesuaikan ke kiri agar lebih modern */
        margin-bottom: 40px;
        border-bottom: 2px solid #e0dbd0;
        display: block; /* Pastikan menggunakan seluruh lebar */
        padding-bottom: 10px;
        width: 100%;
    }

    .cert-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    /* --- DESAIN KARTU SERTIFIKAT --- */
    .cert-card {
        background: #ffffff;
        border: 1px solid #f0ece3;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 250px;
    }

    .cert-card:hover {
        transform: translateY(-5px);
        border-color: #c49b63;
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    }

    .cert-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        display: block;
    }

    .cert-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        color: #2c3e50;
        margin: 0 0 10px 0;
    }

    .cert-meta {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 20px;
    }

    /* Tombol */
    .btn-view-cert {
        background-color: #2c3e50;
        color: white;
        padding: 12px 0;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
        display: block;
        width: 100%;
    }
    .btn-view-cert:hover { background-color: #c49b63; }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
        color: #888;
        padding: 50px;
        border: 2px dashed #ddd;
        border-radius: 12px;
        background-color: #fff;
    }

    .empty-state p {
        margin-bottom: 15px;
    }
</style>

<div class="main-content">

    <h1 class="page-title">My Certificates</h1>

    <div class="cert-grid">
        <?php if (count($certs) > 0): ?>
            <?php foreach ($certs as $ce): ?>
            <div class="cert-card">
                
                <div>
                    <span class="cert-icon">🎓</span> <h3><?= htmlspecialchars($ce['course_title']) ?></h3>
                    <div class="cert-meta">
                        <p style="margin:5px 0;">Issued: <b><?= date('d M Y', strtotime($ce['issued_date'])) ?></b></p>
                        <p style="margin:0; font-size:0.8rem; color:#999;">ID: <?= htmlspecialchars($ce['cert_code']) ?></p>
                    </div>
                </div>

                <a href="certificate_view.php?code=<?= urlencode($ce['cert_code']) ?>" class="btn-view-cert">
                    Lihat & Download 🖨️
                </a>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Anda belum memiliki sertifikat kelulusan.</p>
                <a href="my_courses.php" style="color:#c49b63; font-weight:bold;">Selesaikan kursus untuk mendapatkannya!</a>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>