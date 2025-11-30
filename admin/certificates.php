<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Cek Login
if (!isset($_SESSION['user'])) {
    echo "<script>window.location='../auth/login.php';</script>";
    exit;
}

$role = $_SESSION['user']['role'];
$uid  = $_SESSION['user']['user_id'];
$msg = "";

// --- MENANGKAP PARAMETER FILTER DARI URL ---
$search_text = $_GET['q'] ?? '';      // Untuk Nama Siswa atau Nama Kelas
$search_date = $_GET['date'] ?? '';   // Untuk Tanggal

// --- LOGIKA QUERY DATA ---
try {
    // Base SQL Query
    // Pastikan kolom 'cert_code' ikut terambil dalam cert.*
    $sql = "SELECT cert.*, u.name AS student_name, c.title AS course_title 
            FROM certificates cert
            JOIN enrollments e ON cert.enroll_id = e.enroll_id
            JOIN courses c ON e.course_id = c.course_id
            JOIN users u ON e.user_id = u.user_id
            WHERE 1=1 "; 

    $params = [];

    // 1. Filter Role (Siswa cuma liat punya sendiri)
    if ($role !== 'admin') {
        $sql .= " AND u.user_id = :uid";
        $params[':uid'] = $uid;
    }

    // 2. Filter Teks (Nama Siswa ATAU Nama Kelas)
    if (!empty($search_text)) {
        $sql .= " AND (u.name LIKE :text OR c.title LIKE :text)";
        $params[':text'] = "%$search_text%";
    }

    // 3. Filter Tanggal
    if (!empty($search_date)) {
        $sql .= " AND DATE(cert.issued_date) = :date";
        $params[':date'] = $search_date;
    }

    // Order By
    $sql .= " ORDER BY cert.issued_date DESC";

    // Eksekusi
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div style='margin-left: 280px; padding: 20px; color: red;'><strong>SQL Error:</strong> " . $e->getMessage() . "</div>";
    exit;
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

    .main-content {
        margin-left: 260px;
        padding: 40px; 
        min-height: 100vh;
        padding-top: 100px; 
    }

    .page-header { margin-bottom: 30px; border-bottom: 2px solid #e0dbd0; padding-bottom: 15px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #1a1a1a; margin: 0; }

    /* Desain Filter Bar */
    .filter-bar {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }

    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 0.85rem; font-weight: bold; color: #666; }
    .form-control {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        min-width: 200px;
    }
    
    .btn-filter {
        background-color: #2c3e50; color: white; border: none;
        padding: 11px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;
        transition: 0.3s;
    }
    .btn-filter:hover { background-color: #1a252f; }

    .btn-reset {
        background-color: #f1f1f1; color: #333; border: 1px solid #ddd;
        padding: 11px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;
        display: inline-block; transition: 0.3s;
    }
    .btn-reset:hover { background-color: #e2e2e2; }

    /* Desain Tabel */
    .table-box {
        background: white; padding: 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
    }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; color: #444; }
    td { padding: 12px; border-bottom: 1px solid #eee; color: #333; }

    .btn-action {
        text-decoration: none;
        padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: bold;
        display: inline-flex; align-items: center; gap: 8px;
        transition: 0.2s;
    }
    
    /* Tombol Lihat - Biru Elegan */
    .btn-view { 
        background: #2c3e50; 
        color: white; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-view:hover { 
        background: #c49b63; 
        transform: translateY(-2px);
    }

    @media (max-width: 900px) {
        .main-content { margin-left: 0; padding: 100px 20px; width: 100%; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .form-control { width: 100%; }
    }
</style>

<div class="main-content">
    
    <div class="page-header">
        <h1 class="page-title">Daftar Sertifikat</h1>
    </div>

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label>Cari Nama / Kelas</label>
            <input type="text" name="q" class="form-control" placeholder="Contoh: Budi atau PHP" value="<?= htmlspecialchars($search_text) ?>">
        </div>

        <div class="form-group">
            <label>Tanggal Terbit</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($search_date) ?>">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-filter">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
            
            <?php if(!empty($search_text) || !empty($search_date)): ?>
                <a href="certificates.php" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?= $msg ?>

    <div class="table-box">
        <?php if (count($certificates) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Kursus</th>
                        <th>Tanggal Terbit</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $row): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['course_title'] ?? 'Kursus Tidak Dikenal') ?></strong>
                            <br><small style="color:#888;">Siswa: <?= htmlspecialchars($row['student_name'] ?? 'Siswa') ?></small>
                        </td>
                        <td><?= date('d M Y', strtotime($row['issued_date'])) ?></td>
                        <td>
                            <a href="../student/certificate_view.php?code=<?= $row['cert_code'] ?>" target="_blank" class="btn-action btn-view">
                                <i class="fa-solid fa-certificate"></i> Lihat Sertifikat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#999;">
                <i class="fa-solid fa-filter-circle-xmark" style="font-size:3rem; margin-bottom:10px; opacity:0.5;"></i>
                <p>Data tidak ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

</div>