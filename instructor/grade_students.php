<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// --- 1. KEAMANAN & VALIDASI ---
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}
$iid = $_SESSION['user']['user_id'];

if (!isset($_GET['course']) || empty($_GET['course'])) {
    header("Location: student_grades.php");
    exit;
}
$course_id = $_GET['course'];

// Cek kepemilikan kelas
$stmt_check = $pdo->prepare("SELECT title FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt_check->execute([$course_id, $iid]);
$course = $stmt_check->fetch();

if (!$course) {
    echo "<script>alert('Akses ditolak!'); window.location='student_grades.php';</script>";
    exit;
}

// --- 2. AMBIL DAFTAR KUIS (Untuk Dropdown Filter) ---
$stmt_q = $pdo->prepare("SELECT quiz_id, title FROM quizzes WHERE course_id = ? ORDER BY quiz_id ASC");
$stmt_q->execute([$course_id]);
$quiz_list = $stmt_q->fetchAll();

// --- 3. PROSES UPDATE NILAI (Hanya untuk Nilai Akhir / Manual) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grade'])) {
    $enroll_id_to_update = $_POST['enroll_id'];
    $new_grade = $_POST['grade_input'];

    if ($new_grade !== '' && is_numeric($new_grade) && $new_grade >= 0 && $new_grade <= 100) {
        $stmt_update = $pdo->prepare("UPDATE enrollments SET grade = ? WHERE enroll_id = ?");
        if ($stmt_update->execute([$new_grade, $enroll_id_to_update])) {
            echo "<script>alert('Nilai Akhir berhasil diperbarui!');</script>";
        } else {
            echo "<script>alert('Gagal memperbarui nilai.');</script>";
        }
    } else {
        echo "<script>alert('Nilai harus berupa angka 0 - 100.');</script>";
    }
}

// --- 4. LOGIKA FILTER & SORTING ---
$search_student = $_GET['student'] ?? ''; 
$sort_grade     = $_GET['sort'] ?? 'desc'; 
$filter_quiz    = $_GET['quiz_filter'] ?? ''; // ID Kuis yang dipilih (kosong = Nilai Akhir)

// --- 5. QUERY DATA DINAMIS ---
// Kita mulai Query dasar ke tabel enrollments dan users
$params = ['cid' => $course_id];

if (!empty($filter_quiz)) {
    // === SKENARIO A: Menampilkan Nilai KUIS TERTENTU ===
    // Kita join ke tabel quiz_results untuk mengambil skor spesifik kuis tersebut
    $sql = "SELECT u.name, u.email, e.enroll_id, e.enrolled_at,
            qr.score as displayed_grade, -- Ambil skor kuis
            'quiz' as grade_type
            FROM enrollments e
            JOIN users u ON e.user_id = u.user_id
            LEFT JOIN quiz_results qr ON qr.user_id = u.user_id AND qr.quiz_id = :qid
            WHERE e.course_id = :cid";
    $params['qid'] = $filter_quiz;

} else {
    // === SKENARIO B: Menampilkan NILAI AKHIR (Default) ===
    // Kita ambil kolom grade dari tabel enrollments
    $sql = "SELECT u.name, u.email, e.enroll_id, e.enrolled_at,
            e.grade as displayed_grade, -- Ambil nilai akhir manual
            'final' as grade_type
            FROM enrollments e
            JOIN users u ON e.user_id = u.user_id
            WHERE e.course_id = :cid";
}

// Filter Pencarian Nama
if (!empty($search_student)) {
    $sql .= " AND u.name LIKE :sname";
    $params['sname'] = "%$search_student%";
}

// Sorting (Berlaku untuk Nilai Kuis maupun Nilai Akhir)
if ($sort_grade == 'asc') {
    $sql .= " ORDER BY displayed_grade ASC"; 
} else {
    $sql .= " ORDER BY displayed_grade DESC"; 
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<style>
    .instr-main-content { padding: 30px 40px 40px 280px; min-height: 100vh; background-color: #fdfbf7; font-family: 'Inter', sans-serif; }
    @media(max-width: 900px) { .instr-main-content { padding: 100px 25px 25px 25px; } }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 28px; color: #1a1a1a; margin: 0; }
    .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
    
    .btn-back { text-decoration: none; color: #555; font-weight: 600; display: flex; align-items: center; gap: 8px; background: #e0e0e0; padding: 8px 15px; border-radius: 8px; }
    .btn-back:hover { background: #d6d6d6; color: #000; }

    .filter-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #eee; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
    .filter-input, .filter-select { padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
    .btn-apply { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }

    .card { background: #ffffff; padding: 25px; border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e8e4dd; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f2eee8; padding: 12px; text-align: left; font-weight: 600; color: #333; }
    td { padding: 15px 12px; border-bottom: 1px solid #eee; color: #555; vertical-align: middle; }
    
    .grade-badge { padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 13px; }
    .g-high { background: #e8f5e9; color: #2e7d32; }
    .g-mid { background: #fff3e0; color: #ef6c00; }
    .g-low { background: #ffebee; color: #c62828; }

    .btn-edit-grade {
        background: #fff; border: 1px solid #ccc; color: #333;
        padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 12px;
        display: inline-flex; align-items: center; gap: 5px; transition: 0.2s;
    }
    .btn-edit-grade:hover { background: #f0f0f0; border-color: #aaa; }
    
    .btn-disabled {
        background: #f9f9f9; border: 1px solid #eee; color: #aaa; cursor: not-allowed;
        padding: 5px 10px; border-radius: 6px; font-size: 12px;
    }

    /* MODAL */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
    .modal-box { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    .modal-input { width: 100%; padding: 12px; margin: 15px 0; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; text-align: center; box-sizing: border-box; }
    .modal-input:focus { border-color: #2c3e50; outline: none; }
    .btn-save { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px; }
    .btn-cancel { background: white; color: #888; border: none; cursor: pointer; font-weight: bold; }
</style>

<div class="instr-main-content">

    <div class="page-header">
        <div>
            <h2 class="page-title">Detail Nilai Siswa</h2>
            <div class="subtitle">Kelas: <strong><?= htmlspecialchars($course['title']) ?></strong></div>
        </div>
        <a href="student_grades.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>

    <form method="GET" class="filter-card">
        <input type="hidden" name="course" value="<?= htmlspecialchars($course_id) ?>">
        
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="student" class="filter-input" style="width: 100%; box-sizing: border-box;" 
                   placeholder="Cari nama siswa..." value="<?= htmlspecialchars($search_student) ?>">
        </div>

        <div style="flex: 1; min-width: 200px;">
            <select name="quiz_filter" class="filter-select" style="width: 100%;" onchange="this.form.submit()">
                <option value="" <?= empty($filter_quiz) ? 'selected' : '' ?>>📊 Semua (Nilai Akhir)</option>
                <?php foreach($quiz_list as $q): ?>
                    <option value="<?= $q['quiz_id'] ?>" <?= $filter_quiz == $q['quiz_id'] ? 'selected' : '' ?>>
                        📝 Kuis: <?= htmlspecialchars($q['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="desc" <?= $sort_grade == 'desc' ? 'selected' : '' ?>>Nilai Tertinggi</option>
                <option value="asc" <?= $sort_grade == 'asc' ? 'selected' : '' ?>>Nilai Terendah</option>
            </select>
        </div>
        
        <button type="submit" class="btn-apply">Terapkan</button>
    </form>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Siswa</th>
                    <th>Email</th>
                    <th>
                        <?php if(empty($filter_quiz)): ?>
                            Nilai Akhir (Manual)
                        <?php else: ?>
                            Skor Kuis (Otomatis)
                        <?php endif; ?>
                    </th>
                    <th width="15%" style="text-align:center;">Aksi 1</th>
                    <th width="15%" style="text-align:center;">Aksi 2</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php $no = 1; foreach ($students as $s): ?>
                        <?php 
                            $val = $s['displayed_grade']; // Nilai (bisa nilai akhir atau skor kuis)
                            $displayVal = ($val !== null) ? number_format($val, 0) : '-';
                            
                            // Logic Warna Badge
                            if ($val === null) $badgeClass = '';
                            elseif ($val >= 85) $badgeClass = 'g-high';
                            elseif ($val >= 70) $badgeClass = 'g-mid';
                            else $badgeClass = 'g-low';
                            
                            // Logic Tombol Edit (Hanya aktif jika mode Nilai Akhir)
                            $canEdit = ($s['grade_type'] === 'final');
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td>
                                <?php if ($val !== null): ?>
                                    <span class="grade-badge <?= $badgeClass ?>"><?= $displayVal ?></span>
                                <?php else: ?>
                                    <span style="color:#999; font-style:italic;">Belum ada nilai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="graduate_student.php">
                                    <input type="hidden" name="enroll_id" value="<?= $s['enroll_id'] ?>">
                                    <button type="submit" name="graduate" class="btn-lulus" onclick="return confirm('Nyatakan siswa ini LULUS?')">
                                        🎓 Luluskan
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                <?php if($canEdit): ?>
                                    <button type="button" class="btn-edit-grade" 
                                            onclick="openModal('<?= $s['enroll_id'] ?>', '<?= $val ?>', '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>')">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                <?php else: ?>
                                    <span class="btn-disabled" title="Nilai kuis otomatis dari sistem">
                                        <i class="fa-solid fa-lock"></i> Auto
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#999;">
                            Tidak ada data siswa ditemukan untuk kategori ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<div id="gradeModal" class="modal-overlay">
    <div class="modal-box">
        <h3 style="margin-top:0;">Input Nilai Akhir</h3>
        <p id="studentNameDisplay" style="color:#666; margin-bottom:20px;"></p>
        
        <form method="POST">
            <input type="hidden" name="enroll_id" id="modalEnrollId">
            
            <label for="gradeInput" style="font-size:14px; font-weight:bold;">Masukkan Nilai (0-100)</label>
            <input type="number" name="grade_input" id="modalGradeInput" class="modal-input" min="0" max="100" step="0.01" required placeholder="0">
            
            <button type="submit" name="submit_grade" class="btn-save">Simpan Perubahan</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('gradeModal');
    const enrollIdInput = document.getElementById('modalEnrollId');
    const gradeInput = document.getElementById('modalGradeInput');
    const nameDisplay = document.getElementById('studentNameDisplay');

    function openModal(id, currentGrade, studentName) {
        enrollIdInput.value = id;
        nameDisplay.textContent = "Siswa: " + studentName;
        if(currentGrade !== '') {
            gradeInput.value = currentGrade;
        } else {
            gradeInput.value = '';
        }
        modal.style.display = 'flex';
        gradeInput.focus();
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>