<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';
// nambah

// Pastikan parameter student dan course ada
if (!isset($_GET['student']) || !isset($_GET['course'])) {
    die("Parameter tidak lengkap.");
}

$student_id = $_GET['student'];
$course_id  = $_GET['course'];

// Ambil nama mahasiswa
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id=?");
$stmt->execute([$student_id]);
$student = $stmt->fetchColumn() ?? 'Mahasiswa Tidak Ditemukan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = trim($_POST['grade']);
    
    // Validasi sederhana
    if (empty($grade)) {
         echo "<script>alert('Nilai tidak boleh kosong!');location='input_grade.php?student=$student_id&course=$course_id'</script>";
         exit;
    }

    // Cek apakah nilai sudah ada
    $check = $pdo->prepare("SELECT * FROM grades WHERE student_id=? AND course_id=?");
    $check->execute([$student_id, $course_id]);

    if ($check->rowCount() > 0) {
        // Update nilai
        $update = $pdo->prepare("UPDATE grades SET grade=? WHERE student_id=? AND course_id=?");
        $update->execute([$grade, $student_id, $course_id]);
    } else {
        // Insert nilai baru
        $insert = $pdo->prepare("INSERT INTO grades(student_id, course_id, grade) VALUES (?, ?, ?)");
        $insert->execute([$student_id, $course_id, $grade]);
    }

    // Redirect kembali ke daftar mahasiswa di kelas tersebut
    echo "<script>alert('Nilai disimpan!');location='grade_students.php?course=$course_id'</script>";
}

// Ambil nilai yang sudah ada untuk ditampilkan di form (jika ada)
$current_grade_stmt = $pdo->prepare("SELECT grade FROM grades WHERE student_id=? AND course_id=?");
$current_grade_stmt->execute([$student_id, $course_id]);
$current_grade = $current_grade_stmt->fetchColumn() ?? '';
?>

<style>
/* CSS UNTUK MENYESUAIKAN POSISI KONTEN UTAMA */
.instr-main-content {
    /* Form input_grade.php biasanya diletakkan di tengah */
    display: flex;
    flex-direction: column;
    align-items: center; 
    
    /* Padding dipertahankan untuk rapat ke sidebar dan header */
    padding: 30px 40px 40px 280px;
    min-height: 100vh;
}
@media(max-width: 900px) {
    .instr-main-content {
        /* Di layar kecil, padding diatur ulang tanpa offset sidebar */
        padding: 100px 25px 25px 25px; 
    }
}


/* CSS LAMA UNTUK STYLE FORM */
.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin-bottom: 25px;
    text-align: center; /* Dipusatkan karena card juga dipusatkan */
}

.card {
    background: #ffffff;
    padding: 30px;
    border-radius: 20px;
    border: 1px solid #e8e4dd;
    width: 450px;
    max-width: 100%;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
}

.form-label {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    display: block; /* Agar label berada di baris sendiri */
    margin-bottom: 5px;
}

.input-box {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    font-size: 15px;
    border: 1px solid #cfcac0;
    margin-top: 0;
    margin-bottom: 20px;
    background: #fcfaf7;
}

.btn-save {
    background: #1a1a1a;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 15px;
    border-radius: 12px;
    cursor: pointer;
    width: 100%;
    transition: .2s;
}
.btn-save:hover {
    background: #333;
}
</style>

<div class="instr-main-content">

    <h2 class="page-title">Input Nilai untuk **<?= htmlspecialchars($student) ?>**</h2>

    <div class="card">
        <form method="POST">

            <label class="form-label">Nilai:</label>
            <input type="text" name="grade" class="input-box" placeholder="Contoh: A / 90 / 85" value="<?= htmlspecialchars($current_grade) ?>" required>

            <button type="submit" class="btn-save">Simpan Nilai</button>
        </form>
    </div>

</div>