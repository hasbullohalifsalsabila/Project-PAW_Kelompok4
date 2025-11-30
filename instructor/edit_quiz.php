<?php
require '../config/db.php';

// ==========================================
// 1. TANGKAP ID
// ==========================================
$quiz_id = $_GET['quiz_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if (!$quiz_id) {
    die("❌ Error: Quiz ID tidak ditemukan di URL.");
}

// Ambil Data Kuis
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("❌ Error: Data kuis tidak ditemukan.");
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

// ==========================================
// 4. LOGIKA PEMBOBOTAN NILAI 100
// ==========================================
// Kita ambil semua soal dulu ke dalam array untuk dihitung
$qs = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
$qs->execute([$quiz_id]);
$questions = $qs->fetchAll(); // Simpan semua data soal di variabel $questions

$total_soal = count($questions); // Hitung jumlah soal
$bobot_per_soal = ($total_soal > 0) ? (100 / $total_soal) : 0; // Rumus: 100 dibagi jumlah soal
?>

<style>
    body { font-family: 'Inter', sans-serif; background: #fdfbf7; padding: 20px; color: #333; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
    
    /* Input Style */
    input[type="text"], textarea, select { width: 100%; padding: 12px; margin: 8px 0 20px 0; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
    textarea { resize: vertical; }
    
    /* Button Style */
    .btn { background: #2c3e50; color: white; padding: 12px 25px; border: none; cursor: pointer; border-radius: 8px; font-weight: 600; transition: 0.3s; }
    .btn:hover { background: #34495e; transform: translateY(-1px); }
    
    /* Item Soal Style */
    .item-soal { border-bottom: 1px solid #f0f0f0; padding: 20px 0; }
    .item-soal:last-child { border-bottom: none; }
    
    /* Info Box Style */
    .info-box {
        background: #e3f2fd; border: 1px solid #90caf9; color: #1565c0;
        padding: 15px; border-radius: 8px; margin-bottom: 25px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .info-number { font-size: 1.5rem; font-weight: bold; }
</style>

<div class="container">
    <a href="manage_course.php?id=<?=$course_id?>" style="text-decoration:none; color: #7f8c8d; font-weight: 600;">&larr; Kembali ke Manage Course</a>
    
    <h2 style="margin-top: 15px; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <?= $page_title_text ?>: <span style="color:#e67e22;"><?= htmlspecialchars($clean_title) ?></span>
    </h2>

    <div class="info-box">
        <div>
            <i class="fa-solid fa-circle-info"></i> <b>Info Penilaian Otomatis</b><br>
        </div>
        <div style="text-align: right;">
            <div>Total Soal: <b><?= $total_soal ?></b></div>
            <div>Bobot per Soal: <span style="background:white; padding:2px 8px; border-radius:4px; font-weight:bold;"><?= number_format($bobot_per_soal, 2) ?> Poin</span></div>
        </div>
    </div>

    <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; border: 1px solid #e9ecef; margin-bottom: 40px;">
        <h3 style="margin-top:0; color:#2c3e50;">➕ Tambah Soal Baru</h3>
        <form method="POST">
            <label><b>Pertanyaan:</b></label>
            <textarea name="question_text" rows="3" required placeholder="Tulis pertanyaan disini..."></textarea>

            <label><b>Pilihan Jawaban:</b></label>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div><input type="text" name="opt_a" placeholder="Opsi A" required></div>
                <div><input type="text" name="opt_b" placeholder="Opsi B" required></div>
                <div><input type="text" name="opt_c" placeholder="Opsi C" required></div>
                <div><input type="text" name="opt_d" placeholder="Opsi D" required></div>
            </div>

            <label><b>Kunci Jawaban (Benar):</b></label>
            <select name="correct_option" required style="background: #fff;">
                <option value="" disabled selected>-- Pilih Kunci Jawaban --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <button type="submit" name="add_question" class="btn">Simpan Soal</button>
        </form>
    </div>

    <h3 style="color:#2c3e50;">📋 Daftar Soal (<?= $total_soal ?>)</h3>
    
    <?php if ($total_soal > 0): ?>
        <?php 
        $no = 1;
        // Kita looping array $questions yang sudah diambil di atas
        foreach($questions as $q): 
        ?>
            <div class="item-soal">
                <div style="display:flex; justify-content:space-between; margin-bottom: 10px;">
                    <div>
                        <span style="background:#2c3e50; color:white; padding: 4px 10px; border-radius:4px; font-size:0.85rem;">Soal <?= $no++ ?></span>
                        <span style="margin-left:10px; color:#16a085; font-weight:bold; font-size:0.9rem;">
                            (Bernilai <?= number_format($bobot_per_soal, 2) ?> poin)
                        </span>
                    </div>
                    <a href="edit_quiz.php?quiz_id=<?=$quiz_id?>&course_id=<?=$course_id?>&delete_q=<?=$q['id']?>" 
                       onclick="return confirm('Yakin ingin menghapus soal ini? Bobot nilai soal lain akan berubah.')" 
                       style="color:#c0392b; text-decoration:none; font-weight:bold; font-size:0.9rem;">
                       <i class="fa-solid fa-trash"></i> Hapus
                    </a>
                </div>
                
                <p style="font-size: 1.1rem; margin-bottom: 15px; font-weight: 500;"><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>
                
                <div style="background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div <?= $q['correct_answer'] == 'A' ? 'style="color:green; font-weight:bold;"' : 'style="color:#666;"' ?>>A. <?= htmlspecialchars($q['option_a']) ?></div>
                    <div <?= $q['correct_answer'] == 'B' ? 'style="color:green; font-weight:bold;"' : 'style="color:#666;"' ?>>B. <?= htmlspecialchars($q['option_b']) ?></div>
                    <div <?= $q['correct_answer'] == 'C' ? 'style="color:green; font-weight:bold;"' : 'style="color:#666;"' ?>>C. <?= htmlspecialchars($q['option_c']) ?></div>
                    <div <?= $q['correct_answer'] == 'D' ? 'style="color:green; font-weight:bold;"' : 'style="color:#666;"' ?>>D. <?= htmlspecialchars($q['option_d']) ?></div>
                </div>
                <div style="margin-top:5px; font-size:0.85rem; color:#27ae60;">
                    <i class="fa-solid fa-check"></i> Kunci Jawaban: <b><?= $q['correct_answer'] ?></b>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align:center; padding: 40px; color: #999; border: 2px dashed #ddd; border-radius: 10px;">
            Belum ada soal yang dibuat.
        </div>
    <?php endif; ?>

</div>