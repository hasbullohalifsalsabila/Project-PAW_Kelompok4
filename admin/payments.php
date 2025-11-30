<?php
// filename: admin/payments.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Cek Login Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>window.location='../auth/login.php';</script>";
    exit;
}

$msg = "";

// --- 1. PROSES UBAH STATUS (KONFIRMASI PEMBAYARAN) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $pid = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'confirm') {
        // Ubah status payment menjadi 'paid'
        $stmtUpdate = $pdo->prepare("UPDATE payments SET status = 'paid' WHERE payment_id = ?");
        if ($stmtUpdate->execute([$pid])) {
            
            // Update juga status di tabel enrollments menjadi 'active' agar siswa bisa akses kursus
            $stmtGetEnroll = $pdo->prepare("SELECT enroll_id FROM payments WHERE payment_id = ?");
            $stmtGetEnroll->execute([$pid]);
            $enrollData = $stmtGetEnroll->fetch();
            
            if ($enrollData) {
                $pdo->prepare("UPDATE enrollments SET status = 'active' WHERE enroll_id = ?")->execute([$enrollData['enroll_id']]);
            }

            $msg = "<div class='alert success'>✅ Pembayaran #$pid berhasil dikonfirmasi (Lunas).</div>";
        }
    } elseif ($action == 'reject') {
        $pdo->prepare("UPDATE payments SET status = 'failed' WHERE payment_id = ?")->execute([$pid]);
        $msg = "<div class='alert error'>❌ Pembayaran #$pid ditolak.</div>";
    }
}

// --- 2. AMBIL DATA KURSUS (Untuk Dropdown Filter) ---
try {
    $courses = $pdo->query("SELECT course_id, title FROM courses ORDER BY title ASC")->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}

// --- 3. TANGKAP INPUT FILTER ---
$f_student = $_GET['student'] ?? '';
$f_course  = $_GET['course'] ?? '';
$f_start   = $_GET['start'] ?? '';
$f_end     = $_GET['end'] ?? '';

// --- 4. QUERY DATA PEMBAYARAN ---
try {
    $sql = "
        SELECT p.payment_id, p.amount, p.method, p.status, p.paid_at,
               u.name AS student,
               c.title AS course,
               e.payment_proof
        FROM payments p
        JOIN enrollments e ON p.enroll_id = e.enroll_id
        JOIN users u ON e.user_id = u.user_id
        JOIN courses c ON e.course_id = c.course_id
        WHERE 1=1 
    ";

    $params = [];

    if (!empty($f_student)) {
        $sql .= " AND u.name LIKE ?";
        $params[] = "%$f_student%";
    }
    if (!empty($f_course)) {
        $sql .= " AND c.course_id = ?";
        $params[] = $f_course;
    }
    if (!empty($f_start) && !empty($f_end)) {
        $sql .= " AND DATE(p.paid_at) BETWEEN ? AND ?";
        $params[] = $f_start;
        $params[] = $f_end;
    }

    $sql .= " ORDER BY p.payment_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $payments = [];
    $msg = "<div class='alert error'>Error Database: " . $e->getMessage() . "</div>";
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    .main-content {
        margin-left: 250px;
        padding: 40px;
        padding-top: 80px;
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
    }

    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px; border-bottom: 2px solid #e0dbd0; padding-bottom: 15px;
    }
    .page-title { font-family: 'Times New Roman', serif; font-size: 2rem; color: #2c3e50; margin: 0; }

    /* FILTER BOX */
    .filter-box {
        background: white; padding: 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        margin-bottom: 30px;
        display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;
    }

    .form-group { display: flex; flex-direction: column; flex: 1; min-width: 150px; }
    .form-group label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #555; }
    
    .form-control { padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box; }

    .btn-search { background: #2c3e50; color: white; border: none; padding: 11px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; height: 40px; }
    .btn-search:hover { background: #1a252f; }

    .btn-reset { background: #95a5a6; color: white; text-decoration: none; padding: 11px 20px; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block; height: 40px; line-height: 18px; box-sizing: border-box; }
    .btn-reset:hover { background: #7f8c8d; }

    /* TABEL */
    .table-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #f0ece3; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: bold; color: #444; border-bottom: 2px solid #ddd; white-space: nowrap; }
    td { padding: 15px; border-bottom: 1px solid #eee; color: #333; vertical-align: middle; }

    /* BADGES */
    .badge { padding: 5px 10px; border-radius: 4px; color: white; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .bg-green { background: #27ae60; }
    .bg-orange { background: #f39c12; }
    .bg-red { background: #c0392b; }
    .bg-gray { background: #95a5a6; }

    /* ACTION BUTTONS */
    .btn-small { padding: 6px 10px; text-decoration: none; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-block; margin-right: 5px; }
    .btn-confirm { background: #27ae60; color: white; }
    .btn-confirm:hover { background: #219150; }
    .btn-reject { background: #c0392b; color: white; }
    .btn-reject:hover { background: #a93226; }
    .btn-view { background: #3498db; color: white; }

    /* ALERT */
    .alert { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; font-size: 14px; }
    .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding-top: 80px; }
        .filter-box { flex-direction: column; align-items: stretch; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Kelola Pembayaran</h1>
    </div>

    <?= $msg ?>

    <form method="GET" class="filter-box">
        <div class="form-group">
            <label>Cari Siswa</label>
            <input type="text" name="student" class="form-control" placeholder="Nama siswa..." value="<?= htmlspecialchars($f_student) ?>">
        </div>
        <div class="form-group">
            <label>Filter Kelas</label>
            <select name="course" class="form-control">
                <option value="">-- Semua Kelas --</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?= $f_course == $c['course_id'] ? 'selected' : '' ?>><?=$c['title']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Dari Tanggal</label>
            <input type="date" name="start" class="form-control" value="<?= $f_start ?>">
        </div>
        <div class="form-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="end" class="form-control" value="<?= $f_end ?>">
        </div>
        <div style="display:flex; gap:10px; align-items: flex-end;">
            <button type="submit" class="btn-search">🔍 Cari</button>
            <a href="payments.php" class="btn-reset">✖ Reset</a>
        </div>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Siswa</th>
                    <th>Kursus</th>
                    <th>Jumlah</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payments) > 0): ?>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td>#<?= $p['payment_id'] ?></td>
                        <td><strong><?= htmlspecialchars($p['student']) ?></strong></td>
                        <td><?= htmlspecialchars($p['course']) ?></td>
                        <td>Rp <?= number_format($p['amount'], 0, ',', '.') ?></td>
                        <td>
                            <?php if(!empty($p['payment_proof'])): ?>
                                <a href="../uploads/payments/<?= $p['payment_proof'] ?>" target="_blank" class="btn-small btn-view">Lihat</a>
                            <?php else: ?>
                                <span style="color:#999; font-size:11px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                $statusClass = 'bg-gray';
                                if($p['status'] == 'paid') $statusClass = 'bg-green';
                                if($p['status'] == 'pending') $statusClass = 'bg-orange';
                                if($p['status'] == 'failed') $statusClass = 'bg-red';
                            ?>
                            <span class="badge <?= $statusClass ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($p['paid_at'])) ?></td>
                        <td>
                            <?php if($p['status'] == 'pending'): ?>
                                <a href="?action=confirm&id=<?= $p['payment_id'] ?>" class="btn-small btn-confirm" onclick="return confirm('Konfirmasi pembayaran ini?')">✔ Terima</a>
                                <a href="?action=reject&id=<?= $p['payment_id'] ?>" class="btn-small btn-reject" onclick="return confirm('Tolak pembayaran ini?')">✖ Tolak</a>
                            <?php else: ?>
                                <span style="color:#999; font-size:11px;">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:#888;">Data tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>