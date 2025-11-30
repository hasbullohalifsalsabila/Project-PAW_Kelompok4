<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}
// Ambil ID instruktur yang sedang login
$iid = $_SESSION['user']['user_id'];

// --- LOGIKA PENCARIAN ---
$search_keyword = $_GET['search'] ?? ''; // Ambil kata kunci dari URL jika ada

// --- QUERY UTAMA (DENGAN JOIN & SEARCH) ---
// Kita gunakan LEFT JOIN ke tabel 'enrollments' untuk menghitung jumlah siswa per kelas
// GROUP BY c.course_id diperlukan karena kita menggunakan fungsi agregat COUNT()
$sql = "
    SELECT 
        c.*, 
        COUNT(e.enroll_id) AS total_students 
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    WHERE c.instructor_id = :iid
";

$params = ['iid' => $iid];

// Jika ada pencarian, tambahkan kondisi ke WHERE
if (!empty($search_keyword)) {
    $sql .= " AND c.title LIKE :search";
    $params['search'] = "%$search_keyword%";
}

$sql .= " GROUP BY c.course_id ORDER BY c.title ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();
?>

<style>
/* CSS UNTUK MENYESUAIKAN POSISI KONTEN UTAMA */
.instr-main-content {
    padding: 30px 40px 40px 280px; 
    min-height: 100vh;
    background-color: #fdfbf7;
}
@media(max-width: 900px) {
    .instr-main-content {
        padding: 100px 25px 25px 25px; 
    }
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: #1a1a1a;
    margin: 0;
}

/* STYLE PENCARIAN */
.search-box {
    display: flex;
    gap: 10px;
}
.search-input {
    padding: 10px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    width: 250px;
    font-size: 14px;
}
.btn-search {
    padding: 10px 15px;
    background: #2c3e50;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-search:hover { background: #1a252f; }

/* KARTU & TABEL */
.card {
    background: #ffffff;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid #e8e4dd;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.table th {
    background: #f2eee8;
    padding: 12px;
    text-align: left;
    font-size: 15px;
    color: #1a1a1a;
    font-weight: 600;
}

.table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e4dfd6;
    color: #555;
}

/* BADGE JUMLAH SISWA */
.badge-students {
    display: inline-block;
    background-color: #e3f2fd;
    color: #0d47a1;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.badge-zero {
    background-color: #f5f5f5;
    color: #999;
}

.btn {
    padding: 8px 15px;
    background: #1a1a1a;
    border-radius: 10px;
    color: white;
    text-decoration: none;
    font-size: 13px;
    transition: 0.2s;
    font-weight: 500;
}
.btn:hover { background: #333333; }
</style>

<div class="instr-main-content">

    <div class="page-header">
        <h2 class="page-title">Input Nilai – Kelas Saya</h2>
        
        <!-- FORM PENCARIAN -->
        <form method="GET" class="search-box">
            <input type="text" name="search" class="search-input" placeholder="Cari nama kelas..." value="<?= htmlspecialchars($search_keyword) ?>">
            <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            <?php if(!empty($search_keyword)): ?>
                <a href="?" class="btn" style="background:#e74c3c;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <?php if (count($courses) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="45%">Nama Kelas</th>
                        <th width="20%">Jumlah Siswa</th>
                        <th width="30%" style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($courses as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['title']) ?></strong>
                            <?php if(!empty($search_keyword)): ?>
                                <br><small style="color:#e67e22;">Hasil pencarian: "<?= htmlspecialchars($search_keyword) ?>"</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['total_students'] > 0): ?>
                                <span class="badge-students">
                                    <i class="fa-solid fa-users"></i> <?= $row['total_students'] ?> Siswa
                                </span>
                            <?php else: ?>
                                <span class="badge-students badge-zero">Belum ada siswa</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($row['total_students'] > 0): ?>
                                <a href="grade_students.php?course=<?= $row['course_id'] ?>" class="btn">
                                    <i class="fa-solid fa-pen-to-square"></i> Kelola Nilai
                                </a>
                            <?php else: ?>
                                <button class="btn" style="background:#ccc; cursor:not-allowed;" disabled>Kosong</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#999;">
                <i class="fa-solid fa-box-open" style="font-size:40px; margin-bottom:10px;"></i>
                <p>Tidak ada kelas yang ditemukan.</p>
                <?php if(!empty($search_keyword)): ?>
                    <a href="?" style="color:#2980b9; text-decoration:none;">Hapus filter pencarian</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>