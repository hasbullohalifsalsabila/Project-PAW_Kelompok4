<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Ambil ID
$id = $_GET['id'] ?? null;
if(!$id) { echo "<script>window.location='announcements.php';</script>"; exit; }

// 2. Ambil Data Lama
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE announcement_id=?");
$stmt->execute([$id]);
$ann = $stmt->fetch();

// 3. Ambil Data Dropdown
$courses = $pdo->query("SELECT course_id, title FROM courses")->fetchAll();
$instructors = $pdo->query("SELECT user_id, name FROM users WHERE role='instructor'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid     = $_POST['course'];
    $iid     = $_POST['instructor'];
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']); // Sesuaikan nama kolom DB: content

    // Update Database
    $stmt = $pdo->prepare("UPDATE announcements SET course_id=?, instructor_id=?, title=?, content=? WHERE announcement_id=?");
    
    if ($stmt->execute([$cid, $iid, $title, $content, $id])) {
        echo "<script>alert('✅ Perubahan disimpan!'); window.location='announcements.php';</script>";
        exit;
    }
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        padding-top: 80px; 
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 40px;
    }

    /* HEADER (Judul Kiri, Tombol Kanan) */
    .page-header-flex {
        display: flex; 
        justify-content: space-between; /* Kunci agar tombol ada di kanan */
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }
    
    .btn-back {
        text-decoration: none; border: 1px solid #2c3e50; color: #2c3e50;
        padding: 8px 20px; border-radius: 30px; font-weight: 600; 
        transition: 0.3s; font-size: 13px;
    }
    .btn-back:hover { background: #2c3e50; color: white; }

    /* KARTU FORM (PRESISI DI TENGAH) */
    .card-compact {
        background: white; 
        padding: 40px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
        border: 1px solid #f0ece3;
        
        /* Agar posisi di tengah */
        max-width: 600px; 
        margin: 0 auto; 
    }

    /* FORM STYLING */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
    
    .form-control {
        width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;
        box-sizing: border-box; font-size: 14px;
    }
    .form-control:focus { outline: none; border-color: #2c3e50; }

    .btn-save {
        width: 100%; background: #2c3e50; color: white; border: none; padding: 12px;
        border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; margin-top: 10px;
    }
    .btn-save:hover { background: #1a252f; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding-top: 80px; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Edit Announcement</h1>
        <a href="announcements.php" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="card-compact">
        <form method="POST">

            <div class="form-group">
                <label class="form-label">Course</label>
                <select name="course" class="form-control">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?=$c['course_id']?>" <?=$ann['course_id']==$c['course_id']?'selected':''?>>
                            <?=$c['title']?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Instructor</label>
                <select name="instructor" class="form-control">
                    <?php foreach ($instructors as $i): ?>
                        <option value="<?=$i['user_id']?>" <?=$ann['instructor_id']==$i['user_id']?'selected':''?>>
                            <?=$i['name']?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ann['title']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($ann['content']) ?></textarea>
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

</div>