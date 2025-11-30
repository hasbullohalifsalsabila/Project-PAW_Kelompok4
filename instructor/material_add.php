<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$iid = $_SESSION['user']['user_id'];
$cid = $_GET['course'] ?? '';
$edit = $_GET['edit'] ?? null;

// Validasi Course ID
if (!$cid) { echo "<script>alert('Course ID missing'); window.location='dashboard.php';</script>"; exit; }

$material = null;
if ($edit) {
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE material_id=? AND course_id=?");
    $stmt->execute([$edit, $cid]);
    $material = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title    = $_POST['title'];
    $type     = $_POST['type'];
    $position = $_POST['position'];
    $desc     = $_POST['description']; // Deskripsi Teks
    
    // Logika Menentukan File URL (Apakah Upload Baru, Link Youtube, atau File Lama)
    $file_url = $material['file_url'] ?? ''; 

    // 1. Cek apakah ada file yang diupload
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === 0) {
        $allowed = ['mp4', 'webm', 'pdf', 'jpg', 'png', 'jpeg', 'zip'];
        $filename = $_FILES['material_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Nama file unik: time_random_namafile
            $new_name = time() . "_" . rand(100,999) . "_" . str_replace(' ', '_', $filename);
            $destination = "../uploads/materials/" . $new_name;

            // Buat folder jika belum ada
            if (!is_dir("../uploads/materials/")) {
                mkdir("../uploads/materials/", 0777, true);
            }

            if (move_uploaded_file($_FILES['material_file']['tmp_name'], $destination)) {
                $file_url = $new_name; // Simpan nama file baru
            } else {
                echo "<script>alert('Gagal upload file ke server.');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak didukung.');</script>";
        }
    } 
    // 2. Jika tidak upload file, cek apakah ada input Link (misal Youtube)
    elseif (!empty($_POST['link_url'])) {
        $file_url = $_POST['link_url'];
    }

    // --- SIMPAN KE DATABASE ---
    if ($edit) {
        $upd = $pdo->prepare("
            UPDATE materials 
            SET title=?, type=?, file_url=?, description=?, position=?
            WHERE material_id=? AND course_id=?
        ");
        $upd->execute([$title, $type, $file_url, $desc, $position, $edit, $cid]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO materials (course_id, title, type, file_url, description, position)
            VALUES (?,?,?,?,?,?)
        ");
        $ins->execute([$cid, $title, $type, $file_url, $desc, $position]);
    }

    echo "<script>window.location='materials.php?course=$cid';</script>";
    exit;
}
?>

<link rel="stylesheet" href="instructor.css">

<div class="inst-container">

    <h1 class="page-title"><?= $edit ? "Edit Material" : "Add Material" ?></h1>

    <form method="POST" class="form-box" enctype="multipart/form-data">

        <label>Title</label>
        <input type="text" name="title" required value="<?=$material['title'] ?? ''?>">

        <label>Type</label>
        <select name="type" required>
            <option value="video" <?=isset($material) && $material['type']=='video'?'selected':''?>>Video</option>
            <option value="pdf" <?=isset($material) && $material['type']=='pdf'?'selected':''?>>PDF</option>
            <option value="text" <?=isset($material) && $material['type']=='text'?'selected':''?>>Text / Other</option>
        </select>

        <label>Description (Text Content)</label>
        <textarea name="description" rows="4"><?=$material['description'] ?? ''?></textarea>

        <label style="margin-top: 15px; font-weight:bold; color:#2c3e50;">Upload File (Video MP4 / PDF)</label>
        <input type="file" name="material_file" style="padding: 10px; border: 1px dashed #ccc; width: 100%;">
        <?php if(!empty($material['file_url'])): ?>
            <small style="color: green;">File saat ini: <?= htmlspecialchars($material['file_url']) ?></small>
        <?php endif; ?>

        <label style="margin-top: 15px; font-weight:bold;">Atau Masukkan Link (Youtube)</label>
        <input type="text" name="link_url" placeholder="https://youtube.com/..." value="<?= (strpos($material['file_url'] ?? '', 'http') !== false) ? $material['file_url'] : '' ?>">
        <small style="color: #666;">Isi ini jika menggunakan Youtube. Kosongkan jika upload file.</small>

        <label style="margin-top: 15px;">Position (Urutan)</label>
        <input type="number" name="position" required value="<?=$material['position'] ?? 0?>">

        <button class="btn-dark" style="margin-top: 20px;">Save Material</button>

    </form>

</div>