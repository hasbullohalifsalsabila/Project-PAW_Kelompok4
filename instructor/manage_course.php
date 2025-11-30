<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// --- 1. KEAMANAN & CEK ID ---
if (!isset($_GET['id'])) {
    echo "<script>window.location='courses.php';</script>";
    exit;
}
$course_id = $_GET['id'];
$iid = $_SESSION['user']['user_id']; 

// Cek Validasi Kursus
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $iid]);
$course = $stmt->fetch();

if (!$course) {
    echo "<script>alert('Akses ditolak atau kursus tidak ditemukan!'); window.location='courses.php';</script>";
    exit;
}

// --- 2. LOGIKA HAPUS (DELETE) ---
if (isset($_GET['delete_material'])) {
    $mat_id = $_GET['delete_material'];
    
    // Ambil data (Gunakan file_path)
    $stmt = $pdo->prepare("SELECT file_path FROM materials WHERE material_id = ?");
    $stmt->execute([$mat_id]);
    $data = $stmt->fetch();

    if ($data) {
        // Hapus file fisik jika bukan link youtube
        if (!filter_var($data['file_path'], FILTER_VALIDATE_URL)) {
            $file_full_path = "../uploads/materials/" . $data['file_path'];
            if (file_exists($file_full_path)) {
                unlink($file_full_path); 
            }
        }
        // Hapus data di Database
        $pdo->prepare("DELETE FROM materials WHERE material_id = ?")->execute([$mat_id]);
        echo "<script>alert('Materi berhasil dihapus!'); window.location.href='manage_course.php?id=$course_id';</script>";
        exit;
    }
}

if (isset($_GET['delete_quiz'])) {
    $quiz_id = $_GET['delete_quiz'];
    $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ?")->execute([$quiz_id]);
    echo "<script>alert('Kuis berhasil dihapus!'); window.location.href='manage_course.php?id=$course_id';</script>";
    exit;
}

// --- 3. LOGIKA UPLOAD & TAMBAH ---

// A. Upload Materi
if (isset($_POST['upload_material'])) {
    $title = $_POST['material_title'];
    $desc  = $_POST['material_desc'] ?? '';
    $position = $_POST['position'] ?? 0;
    
    $final_path = ''; // Variabel untuk menyimpan nama file/link
    $type = 'text';   // Default

    // CEK 1: Apakah ada file yang diupload?
    if (isset($_FILES['file_content']) && $_FILES['file_content']['error'] === 0) {
        $target_dir = "../uploads/materials/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = $_FILES["file_content"]["name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Deteksi Tipe
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
            $type = 'video';
        } elseif ($ext == 'pdf') {
            $type = 'pdf';
        } else {
            $type = 'text'; 
        }

        $new_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
        
        if (move_uploaded_file($_FILES["file_content"]["tmp_name"], $target_dir . $new_name)) {
            $final_path = $new_name;
        } else {
            $err = "Gagal upload file ke server.";
        }
    } 
    // CEK 2: Jika tidak upload file, cek Link YouTube
    elseif (!empty($_POST['youtube_link'])) {
        $final_path = $_POST['youtube_link'];
        $type = 'video'; 
    }

    // Simpan ke Database (Gunakan file_path sesuai database Anda)
    if (!isset($err)) {
        // PERBAIKAN DI SINI: Menggunakan 'file_path' bukan 'file_url'
        $sql = "INSERT INTO materials (course_id, title, description, file_path, type, position) VALUES (?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$course_id, $title, $desc, $final_path, $type, $position]);
        
        echo "<script>alert('Materi berhasil ditambahkan!'); window.location='manage_course.php?id=$course_id';</script>";
        exit;
    }
}

// B. Tambah Kuis
if (isset($_POST['add_quiz'])) {
    $quiz_title = $_POST['quiz_title'];
    $quiz_desc  = $_POST['quiz_desc'] ?? '';

    $sql = "INSERT INTO quizzes (course_id, title, description) VALUES (?, ?, ?)";
    $pdo->prepare($sql)->execute([$course_id, $quiz_title, $quiz_desc]);
    $msg_quiz = "Kuis dibuat!";
}
?>

<style>
    .main-content { background-color: #fdfbf7; padding: 40px; min-height: 100vh; margin-left: 260px; }
    .page-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 2px solid #e0dbd0; padding-bottom: 20px; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #2c3e50; margin: 0; }
    .course-subtitle { color: #666; font-size: 1rem; margin-top: 5px; }
    .btn-back { text-decoration: none; color: #2c3e50; border: 1px solid #2c3e50; padding: 8px 20px; border-radius: 30px; font-weight: 600; transition: 0.3s; }
    .btn-back:hover { background-color: #2c3e50; color: white; }

    .input-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; margin-bottom: 50px; }
    .card-modern { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3; height: 100%; display: flex; flex-direction: column; }
    
    .form-group { margin-bottom: 15px; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #555; display: block; margin-bottom: 5px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
    textarea.form-control { resize: vertical; }

    .btn-submit { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: auto; transition: 0.3s; }
    .btn-primary-custom { background-color: #2c3e50; color: white; }
    .btn-primary-custom:hover { background-color: #c49b63; }
    .btn-warning-custom { background-color: #fff3e0; color: #e67e22; border: 1px solid #e67e22; }
    .btn-warning-custom:hover { background-color: #e67e22; color: white; }

    .content-list-section { background: white; border-radius: 12px; border: 1px solid #f0ece3; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { background: #f8f9fa; padding: 15px 20px; text-align: left; color: #666; font-size: 0.9rem; border-bottom: 2px solid #eee; }
    .table-custom td { padding: 15px 20px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    .badge-type { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
    .badge-video { background: #e3f2fd; color: #1565c0; }
    .badge-pdf { background: #ffebee; color: #c62828; }
    .badge-text { background: #f5f5f5; color: #616161; }
    .badge-kuis { background: #fff3e0; color: #ef6c00; }
    .badge-latihan { background: #e8f5e9; color: #2e7d32; }

    .btn-delete { color: #e74c3c; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 5px; transition: 0.2s; }
    .btn-delete:hover { background: #fee; }
    .btn-edit-quiz { background: #2c3e50; color: white; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; }
    
    .or-divider { text-align: center; margin: 10px 0; font-size: 0.85rem; color: #999; font-weight: bold; position: relative; }
    .or-divider::before, .or-divider::after { content: ""; display: inline-block; width: 30%; height: 1px; background: #eee; vertical-align: middle; margin: 0 10px; }
    
    @media (max-width: 900px) { .main-content { margin-left: 0; padding: 100px 20px; } }
</style>

<div class="main-content">
    
    <div class="page-header-flex">
        <div>
            <h2 class="page-title">Manage Content</h2>
            <div class="course-subtitle">Kursus: <strong><?= htmlspecialchars($course['title']) ?></strong></div>
        </div>
        <a href="courses.php" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="input-grid">
        <div class="card-modern">
            <h4 style="margin: 0 0 20px 0; color: #2c3e50;">📂 Upload Materi</h4>
            <?php if(isset($msg)) echo "<div style='color:green; font-size:0.9rem; margin-bottom:10px;'>✅ $msg</div>"; ?>
            <?php if(isset($err)) echo "<div style='color:red; font-size:0.9rem; margin-bottom:10px;'>❌ $err</div>"; ?>
            
            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; height:100%;">
                <div class="form-group">
                    <label class="form-label">Judul Materi</label>
                    <input type="text" name="material_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea name="material_desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="position" class="form-control" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Upload File (PDF / MP4)</label>
                    <input type="file" name="file_content" class="form-control">
                </div>
                
                <div class="or-divider">ATAU</div>

                <div class="form-group">
                    <label class="form-label">Link YouTube</label>
                    <input type="text" name="youtube_link" class="form-control" placeholder="https://youtube.com/...">
                </div>

                <button type="submit" name="upload_material" class="btn-submit btn-primary-custom">Simpan Materi</button>
            </form>
        </div>

        <div class="card-modern">
            <h4 style="margin: 0 0 20px 0; color: #e67e22;">📝 Buat Kuis</h4>
            <?php if(isset($msg_quiz)) echo "<div style='color:green; font-size:0.9rem; margin-bottom:10px;'>✅ $msg_quiz</div>"; ?>
            
            <form method="POST" style="display:flex; flex-direction:column; height:100%;">
                <div class="form-group">
                    <label class="form-label">Judul Kuis</label>
                    <input type="text" name="quiz_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Instruksi</label>
                    <textarea name="quiz_desc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-top:auto;">
                    <p style="font-size:0.8rem; color:#888;">Gunakan "<strong>[Latihan]</strong>" di awal judul untuk soal acak.</p>
                </div>
                <button type="submit" name="add_quiz" class="btn-submit btn-warning-custom">+ Buat Kuis</button>
            </form>
        </div>
    </div>

    <div class="content-list-section">
        <h3 style="background:#2c3e50; color:white; padding:15px 25px; margin:0; font-size:1.1rem;">Daftar Konten</h3>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Urutan</th>
                    <th>Judul</th>
                    <th>File/Link</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query Materi (Menggunakan file_path)
                $mats = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY position ASC, material_id ASC");
                $mats->execute([$course_id]);
                while($m = $mats->fetch()): 
                    $badgeClass = 'badge-text';
                    if($m['type'] == 'video') $badgeClass = 'badge-video';
                    if($m['type'] == 'pdf') $badgeClass = 'badge-pdf';
                ?>
                <tr>
                    <td><span class="badge-type <?= $badgeClass ?>"><?= htmlspecialchars($m['type']) ?></span></td>
                    <td><?= htmlspecialchars($m['position']) ?></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($m['title']) ?></div>
                    </td>
                    <td style="font-size:0.85rem; color:#666;">
                        <?php if (filter_var($m['file_path'], FILTER_VALIDATE_URL)): ?>
                            🔗 <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank">Link</a>
                        <?php else: ?>
                            📄 <?= htmlspecialchars(basename($m['file_path'])) ?>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="manage_course.php?id=<?=$course_id?>&delete_material=<?=$m['material_id']?>" 
                           class="btn-delete" onclick="return confirm('Hapus?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php
                // Query Kuis
                $quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY quiz_id DESC");
                $quizzes->execute([$course_id]);
                while($q = $quizzes->fetch()): 
                ?>
                <tr>
                    <td><span class="badge-type badge-kuis">Kuis</span></td>
                    <td>-</td>
                    <td><div style="font-weight:600;"><?= htmlspecialchars($q['title']) ?></div></td>
                    <td>-</td>
                    <td style="text-align:center;">
                        <a href="edit_quiz.php?quiz_id=<?= $q['quiz_id'] ?>&course_id=<?=$course_id?>" class="btn-edit-quiz">⚙</a>
                        <a href="manage_course.php?id=<?=$course_id?>&delete_quiz=<?=$q['quiz_id']?>" class="btn-delete" onclick="return confirm('Hapus?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>