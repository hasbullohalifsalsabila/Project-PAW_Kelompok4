<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Ambil Course + Nama Instrukturnya sekaligus (JOIN)
$query = "SELECT c.course_id, c.title, c.instructor_id, u.name AS instructor_name 
          FROM courses c 
          JOIN users u ON c.instructor_id = u.user_id 
          ORDER BY c.title ASC";
$courses = $pdo->query($query)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid     = $_POST['course'];
    $iid     = $_POST['instructor_id']; 
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($cid) && !empty($iid)) {
        $stmt = $pdo->prepare("INSERT INTO announcements (course_id, instructor_id, title, content) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$cid, $iid, $title, $content])) {
            echo "<script>
                    alert('✅ Pengumuman berhasil disimpan!');
                    window.location='announcements.php'; 
                  </script>";
            exit;
        } else {
            $error = "Gagal menyimpan ke database.";
        }
    } else {
        $error = "Mohon pilih kursus terlebih dahulu.";
    }
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px; /* Lebar Sidebar */
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* Mengatur posisi ke tengah */
        padding: 40px;
        padding-top: 80px; 
        
        display: flex;
        flex-direction: column;
        align-items: center; /* Tengah Horizontal */
    }

    /* HEADER HALAMAN */
    .page-header-flex {
        width: 100%;
        max-width: 600px; /* Samakan lebar dengan kartu */
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }
    
    /* Tombol Kembali */
    .btn-back {
        text-decoration: none; border: 1px solid #555; color: #555;
        padding: 6px 15px; border-radius: 20px; font-weight: 600; 
        transition: 0.3s; font-size: 13px;
    }
    .btn-back:hover { background: #555; color: white; }

    /* KARTU FORMULIR */
    .card-form {
        background: white;
        padding: 40px;
        border-radius: 12px;
        width: 100%;
        max-width: 600px; /* Lebar ideal */
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid #f0ece3;
        box-sizing: border-box;
    }

    /* FORM STYLING */
    .form-group { margin-bottom: 20px; }
    .form-label { 
        display: block; margin-bottom: 8px; 
        font-weight: bold; font-size: 14px; color: #555; 
    }
    
    .form-control {
        width: 100%; padding: 12px; 
        border: 1px solid #ccc; border-radius: 6px;
        box-sizing: border-box; font-size: 14px;
    }
    .form-control:focus { outline: none; border-color: #2c3e50; }
    
    /* Input Readonly (Abu-abu) */
    .input-readonly { background-color: #f9f9f9; color: #777; cursor: not-allowed; }

    /* TOMBOL SIMPAN */
    .btn-save {
        width: 100%; padding: 14px; 
        background: #2c3e50; color: white; 
        border: none; border-radius: 6px; 
        cursor: pointer; font-weight: bold; font-size: 15px; 
        margin-top: 10px; transition: 0.3s;
    }
    .btn-save:hover { background: #1a252f; }

    /* Responsif HP */
    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 80px 20px; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Tambah Pengumuman</h1>
        <a href="announcements.php" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="card-form">
        
        <?php if(isset($error)): ?>
            <div style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label class="form-label">Pilih Kursus</label>
                <select name="course" id="courseSelect" class="form-control" required onchange="updateInstructor()">
                    <option value="" data-inst-id="" data-inst-name="">-- Pilih Kursus --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?=$c['course_id']?>" 
                                data-inst-id="<?=$c['instructor_id']?>" 
                                data-inst-name="<?=$c['instructor_name']?>">
                            <?=$c['title']?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Instruktur (Otomatis)</label>
                <input type="text" id="instructorNameDisplay" class="form-control input-readonly" 
                       placeholder="Muncul otomatis..." readonly>
                <input type="hidden" name="instructor_id" id="instructorIdHidden">
            </div>

            <div class="form-group">
                <label class="form-label">Judul Pengumuman</label>
                <input type="text" name="title" class="form-control" required placeholder="Contoh: Jadwal Ujian...">
            </div>

            <div class="form-group">
                <label class="form-label">Isi Pengumuman</label>
                <textarea name="content" class="form-control" rows="6" required placeholder="Tulis pesan lengkap di sini..."></textarea>
            </div>

            <button type="submit" class="btn-save">Simpan Pengumuman</button>

        </form>
    </div>

</div>

<script>
function updateInstructor() {
    var selectBox = document.getElementById("courseSelect");
    var selectedOption = selectBox.options[selectBox.selectedIndex];
    var instId = selectedOption.getAttribute("data-inst-id");
    var instName = selectedOption.getAttribute("data-inst-name");
    
    document.getElementById("instructorIdHidden").value = instId;
    document.getElementById("instructorNameDisplay").value = instName;
}
</script>