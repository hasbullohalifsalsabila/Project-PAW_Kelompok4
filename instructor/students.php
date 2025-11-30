<?php
require '../config/db.php';
require 'header.php'; 
require 'sidebar.php';

// 1. KEAMANAN: Cek Login & Role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

$iid = $_SESSION['user']['user_id'];

// --- LOGIKA HAPUS SISWA ---
if (isset($_GET['remove_user_id']) && isset($_GET['course_id'])) {
    $rem_uid = $_GET['remove_user_id'];
    $rem_cid = $_GET['course_id'];

    // Pastikan kursus ini benar milik instruktur yang sedang login (Security Check)
    $check = $pdo->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
    $check->execute([$rem_cid, $iid]);

    if ($check->rowCount() > 0) {
        // Hapus data pendaftaran (enrollment)
        $del = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
        $del->execute([$rem_uid, $rem_cid]);

        echo "<script>alert('Siswa berhasil dikeluarkan dari kelas.'); window.location.href='students.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus. Anda tidak memiliki akses.'); window.location.href='students.php';</script>";
    }
}

// 2. LOGIKA FILTER & SEARCH
$search     = $_GET['q'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// Query Dasar: Ambil user_id dan course_id juga untuk keperluan tombol hapus
$sql = "
    SELECT u.user_id, u.name, u.email, c.course_id, c.title, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON e.user_id = u.user_id
    WHERE c.instructor_id = ?
";

$params = [$iid];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND DATE(e.enrolled_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql .= " ORDER BY e.enrolled_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<style>
    body { background-color: #fdfbf7; }

    /* MODIFIKASI: Wrapper Konten Utama */
    .inst-container { 
        /* Padding: [TOP] [RIGHT] [BOTTOM] [LEFT: offset sidebar 250px + 30px padding] */
        padding: 30px 40px 40px 280px; 
        flex: 1; 
        background-color: #fdfbf7; 
        min-height: 100vh;
    }

    .page-title { 
        font-family: 'Playfair Display', serif; 
        font-size: 2rem; 
        color: #1a1a1a; 
        margin-bottom: 30px; 
        border-bottom: 1px solid #e0dbd0; 
        padding-bottom: 15px; 
    }
    
    /* Filter Bar */
    .filter-bar { 
        background: #ffffff; 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.03); 
        border: 1px solid #f0ece3; 
        display: flex; 
        flex-wrap: wrap; 
        gap: 15px; 
        align-items: flex-end; 
        margin-bottom: 30px; 
    }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 0.85rem; font-weight: 600; color: #666; font-family: 'Inter', sans-serif; }
    .form-control { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Inter', sans-serif; outline: none; transition: border-color 0.3s; }
    .form-control:focus { border-color: #c49b63; }
    
    /* Buttons */
    .btn-filter { background-color: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; height: 40px; }
    .btn-filter:hover { background-color: #c49b63; }
    .btn-reset { background-color: white; color: #666; border: 1px solid #ddd; padding: 9px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; height: 40px; box-sizing: border-box; display: inline-flex; align-items: center; }
    .btn-reset:hover { background-color: #f9f9f9; color: #333; }

    /* Tombol Hapus Kecil */
    .btn-remove {
        color: #e74c3c; /* Merah */
        background: #fff0f0;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #fadbd8;
        transition: 0.2s;
    }
    .btn-remove:hover {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
    }

    /* Tabel */
    .table-responsive { overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: 1px solid #f0ece3; }
    .custom-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .custom-table th { background-color: #2c3e50; color: #ffffff; padding: 15px 20px; text-align: left; font-weight: 600; font-size: 0.9rem; letter-spacing: 0.5px; }
    .custom-table td { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; color: #444; font-size: 0.95rem; vertical-align: middle; }
    .custom-table tr:last-child td { border-bottom: none; }
    .custom-table tr:hover { background-color: #fdfbf7; }
    .course-badge { background-color: #fff8e1; color: #b08855; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
    .empty-state { padding: 40px; text-align: center; color: #888; font-style: italic; }

    /* Media Query untuk Responsiveness */
    @media(max-width: 900px) {
        .inst-container { 
            /* Di layar kecil, margin-left diatur ke 0 dan padding disesuaikan */
            padding: 100px 25px 25px 25px; 
        }
        .filter-bar {
            /* Izinkan filter bar stack di mobile */
            flex-direction: column;
            align-items: stretch;
        }
        .form-group {
            width: 100%;
        }
        .form-group:last-child {
            flex-direction: row !important;
        }
    }
</style>

<div class="inst-container">

    <h1 class="page-title">Daftar Siswa Saya</h1>

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label for="search">Cari Siswa</label>
            <input type="text" id="search" name="q" class="form-control" placeholder="Nama atau Email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group">
            <label for="start_date">Dari Tanggal Gabung</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>
        <div class="form-group">
            <label for="end_date">Sampai Tanggal Gabung</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>
        <div class="form-group" style="flex-direction: row; align-items: flex-end;">
            <button type="submit" class="btn-filter">🔍 Filter</button>
            <?php if($search || $start_date || $end_date): ?>
                <a href="students.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama Siswa</th>
                    <th width="25%">Email</th>
                    <th width="25%">Kelas Diambil</th>
                    <th width="15%">Tanggal Gabung</th>
                    <th width="10%">Aksi</th> 
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows) > 0): ?>
                    <?php $no = 1; foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="font-weight: 600; color: #2c3e50;"><?= htmlspecialchars($r['name']) ?></td>
                        <td style="color: #666;"><?= htmlspecialchars($r['email']) ?></td>
                        <td>
                            <span class="course-badge">
                                <?= htmlspecialchars($r['title']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($r['enrolled_at'])) ?></td>
                        <td>
                            <a href="students.php?remove_user_id=<?=$r['user_id']?>&course_id=<?=$r['course_id']?>" 
                                class="btn-remove"
                                onclick="return confirm('Yakin ingin mengeluarkan siswa ini dari kelas? Ini akan menghapus data enrollment.')">
                                Kick
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Tidak ada data siswa yang ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>