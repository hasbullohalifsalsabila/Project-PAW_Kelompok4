<?php
require '../config/db.php';

// --- CATATAN: Header & Sidebar dimatikan dulu untuk tes ---
// Jika nanti halaman ini berhasil dibuka (tidak mental),
// berarti masalahnya ada di file header.php (konflik hak akses).
// require '../student/header.php'; 
// require '../student/sidebar.php'; 

// ==========================================
// 1. TANGKAP ID (Sesuai hasil debug Anda: quiz_id)
// ==========================================
$quiz_id = $_GET['quiz_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

// Validasi ID
if (!$quiz_id) {
    die("❌ Error: Quiz ID tidak ditemukan di URL. Pastikan link dari Manage Course benar.");
}

// Ambil Data Kuis
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("❌ Error: Data kuis dengan ID $quiz_id tidak ditemukan di database.");
}

// Cek tipe (Latihan / Kuis)
$is_latihan = (strpos($quiz['title'], '[Latihan]') !== false);
$page_title_text = $is_latihan ? "Edit Latihan Mandiri" : "Edit Kuis Evaluasi";
$clean_title = str_replace('[Latihan] ', '', $quiz['title']);

// ==========================================
// 2. PROSES TAMBAH SOAL
// ==========================================
if (isset($_POST['add_question'])) {
    try {
        $q_text = $_POST['question_text'];
        $opt_a  = $_POST['opt_a'];
        $opt_b  = $_POST['opt_b'];
        $opt_c  = $_POST['opt_c'];
        $opt_d  = $_POST['opt_d'];
        $correct = $_POST['correct_option']; 
        
        $stmt = $pdo->prepare("INSERT INTO quiz_questions 
            (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->execute([$quiz_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct]);

        // Refresh halaman
        echo "<script>alert('Soal berhasil disimpan!'); window.location.href='edit_quiz.php?quiz_id=$quiz_id&course_id=$course_id';</script>";
    } catch (PDOException $e) {
        die("Gagal menyimpan: " . $e->getMessage());
    }
}

// ==========================================
// 3. PROSES HAPUS SOAL
// ==========================================
if (isset($_GET['delete_q'])) {
    $pdo->prepare("DELETE FROM quiz_questions WHERE id = ?")->execute([$_GET['delete_q']]);
    echo "<script>window.location.href='edit_quiz.php?quiz_id=$quiz_id&course_id=$course_id';</script>";
}
?>

<style>
    body { font-family: sans-serif; background: #fdfbf7; padding: 20px; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    input, textarea, select { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;}
    .btn { background: #2c3e50; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; font-weight: bold;}
    .btn:hover { background: #34495e; }
    .item-soal { border-bottom: 1px solid #eee; padding: 15px 0; }
    .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; display: inline-block; margin-bottom: 10px;}
</style>

<div class="container">
    <a href="manage_course.php?id=<?=$course_id?>" style="text-decoration:none; color: #555;">&larr; Kembali ke Manage Course</a>
    
    <h2 style="margin-top: 10px; color: #2c3e50;">
        <?= $page_title_text ?>: <?= htmlspecialchars($clean_title) ?>
    </h2>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
        <h3 style="margin-top:0;">➕ Tambah Soal Baru</h3>
        <form method="POST">
            <label><b>Pertanyaan:</b></label>
            <textarea name="question_text" rows="3" required placeholder="Tulis soal disini..."></textarea>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div><label>Opsi A</label><input type="text" name="opt_a" required></div>
                <div><label>Opsi B</label><input type="text" name="opt_b" required></div>
                <div><label>Opsi C</label><input type="text" name="opt_c" required></div>
                <div><label>Opsi D</label><input type="text" name="opt_d" required></div>
            </div>

            <label><b>Kunci Jawaban:</b></label>
            <select name="correct_option" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <button type="submit" name="add_question" class="btn">Simpan Soal</button>
        </form>
    </div>

    <h3>📋 Daftar Soal</h3>
    <?php
    $qs = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
    $qs->execute([$quiz_id]);
    $no = 1;
    while($q = $qs->fetch()):
    ?>
        <div class="item-soal">
            <div style="display:flex; justify-content:space-between;">
                <b>Soal <?= $no++ ?></b>
                <a href="edit_quiz.php?quiz_id=<?=$quiz_id?>&course_id=<?=$course_id?>&delete_q=<?=$q['id']?>" 
                   onclick="return confirm('Hapus?')" style="color:red; text-decoration:none;">[Hapus]</a>
            </div>
            <p><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>
            <small style="color:#666;">
                A: <?= $q['option_a'] ?> <br>
                B: <?= $q['option_b'] ?> <br>
                C: <?= $q['option_c'] ?> <br>
                D: <?= $q['option_d'] ?> <br>
                <b>Kunci: <?= $q['correct_answer'] ?></b>
            </small>
        </div>
    <?php endwhile; ?>
</div>