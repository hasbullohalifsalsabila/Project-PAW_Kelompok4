<?php
session_start();
require '../config/db.php';

// 1. Cek Login
if (!isset($_SESSION['user']['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi ID Materi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='my_courses.php';</script>";
    exit;
}

$uid = $_SESSION['user']['user_id'];
$mid = $_GET['id'];

// 2. Ambil Data Materi
$stmt = $pdo->prepare("SELECT * FROM materials WHERE material_id=?");
$stmt->execute([$mid]);
$mat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mat) die("Materi tidak ditemukan.");
$cid = $mat['course_id'];

// Ambil Data Course untuk Header Sidebar
$stmtC = $pdo->prepare("SELECT title FROM courses WHERE course_id=?");
$stmtC->execute([$cid]);
$course = $stmtC->fetch();

// 3. Cek Enrollment
if ($_SESSION['user']['role'] === 'student') {
    $enrolled = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id=? AND course_id=?");
    $enrolled->execute([$uid, $cid]);
    if (!$enrolled->fetch()) die("Akses Ditolak. Silakan ambil kelas ini terlebih dahulu.");
}

// 4. Mark Progress
try {
    $check = $pdo->prepare("SELECT 1 FROM materials_progress WHERE user_id=? AND material_id=?");
    $check->execute([$uid, $mid]);
    if (!$check->fetch()) {
        $pdo->prepare("INSERT INTO materials_progress (user_id, material_id, is_completed, completed_at) VALUES (?,?,1,NOW())")
            ->execute([$uid, $mid]);
    }
} catch (Exception $e) { }

// 5. Sidebar List
$materials = $pdo->prepare("
    SELECT m.*, 
    (SELECT 1 FROM materials_progress mp WHERE mp.user_id = ? AND mp.material_id = m.material_id LIMIT 1) as is_done 
    FROM materials m WHERE course_id=? ORDER BY position ASC, material_id ASC
");
$materials->execute([$uid, $cid]);
$all_materials = $materials->fetchAll(PDO::FETCH_ASSOC);

// FUNGSI HELPER
function getYoutubeEmbedUrl($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? 'https://www.youtube.com/embed/' . $matches[1] : $url;
}

function isYoutube($url) {
    return (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($mat['title']) ?> - EduCourse</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- RESET & LAYOUT DASAR --- */
        * { box-sizing: border-box; }
        body { 
            background-color: #fdfbf7; 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
            color: #333;
        }
        
        .app-container { display: flex; flex: 1; height: 100%; overflow: hidden; }
        
        /* --- SIDEBAR (DAFTAR MATERI) --- */
        .sidebar { 
            width: 340px; 
            background: #fff; 
            border-right: 1px solid #e0dbd0; 
            display: flex; 
            flex-direction: column; 
            flex-shrink: 0; 
            box-shadow: 2px 0 10px rgba(0,0,0,0.02);
        }
        
        .sidebar-header { 
            padding: 25px; 
            border-bottom: 1px solid #f0ece3; 
            background: #fff;
        }
        
        .btn-back-nav { 
            text-decoration: none; color: #666; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; 
        }
        .btn-back-nav:hover { color: #2c3e50; transform: translateX(-3px); }

        .course-label { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.2rem; 
            margin-top: 15px; 
            font-weight: 700; 
            color: #2c3e50; 
            line-height: 1.3;
        }
        
        .sidebar-list { flex: 1; overflow-y: auto; padding: 15px; }
        
        /* ITEM LIST MATERI */
        .module-item { 
            display: flex; align-items: center; gap: 12px; 
            padding: 14px 16px; margin-bottom: 6px; 
            text-decoration: none; color: #555; 
            border-radius: 8px; transition: 0.2s; 
            font-size: 0.95rem; 
            border: 1px solid transparent;
        }
        
        .module-item:hover { background: #f9f9f9; color: #2c3e50; }
        
        .module-item.active { 
            background: #fffdf5; 
            border-color: #f0ece3; 
            color: #c49b63; /* Warna Emas Tema */
            font-weight: 700; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.03); 
        }

        .module-icon { font-size: 1rem; width: 20px; text-align: center; }
        .check-icon { margin-left: auto; color: #27ae60; font-size: 1rem; }
        
        /* --- KONTEN UTAMA --- */
        .main-content { 
            flex: 1; 
            overflow-y: auto; 
            padding: 40px; 
            background: #fdfbf7; 
        }
        
        .content-card { 
            max-width: 950px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); 
            border: 1px solid #f0ece3;
            padding: 50px; 
            min-height: 80vh; 
        }
        
        /* --- TYPOGRAPHY --- */
        .content-title { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.4rem; 
            color: #2c3e50; 
            margin: 0 0 15px 0; 
        }

        .meta-info {
            display: flex; gap: 15px; align-items: center;
            color: #888; font-size: 0.9rem;
            border-bottom: 1px solid #f0ece3;
            padding-bottom: 25px; margin-bottom: 35px;
        }

        /* Badge Tipe File (Sama seperti Instruktur) */
        .badge-type { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-video { background: #e3f2fd; color: #1565c0; }
        .badge-pdf { background: #ffebee; color: #c62828; }
        .badge-text { background: #f5f5f5; color: #616161; }

        /* --- VIDEO PLAYER --- */
        .video-wrapper { 
            position: relative; padding-bottom: 56.25%; height: 0; 
            background: #000; border-radius: 12px; overflow: hidden; 
            margin-bottom: 40px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.1); 
        }
        .video-wrapper iframe, .video-wrapper video { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; object-fit: cover; 
        }

        /* --- TEXT & PDF --- */
        .text-body { 
            font-size: 1.05rem; line-height: 1.8; color: #444; 
        }
        .text-body h3 { font-family: 'Playfair Display', serif; color: #2c3e50; margin-top: 0; }

        .btn-download { 
            display: inline-flex; align-items: center; gap: 10px; 
            background: #2c3e50; color: white; 
            padding: 12px 28px; border-radius: 8px; 
            text-decoration: none; font-weight: 600; 
            transition: 0.3s; 
            box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2);
        }
        .btn-download:hover { background: #c49b63; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(196, 155, 99, 0.3); }

        .pdf-viewer {
            width: 100%; height: 800px; border-radius: 12px; border: 1px solid #eee;
        }
        .pdf-fallback {
            text-align: center; padding: 60px; background: #f8f9fa; border-radius: 12px; border: 1px dashed #ccc;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) { 
            .app-container { flex-direction: column; } 
            .sidebar { width: 100%; height: auto; max-height: 300px; border-right: none; border-bottom: 1px solid #ddd; } 
            .main-content { padding: 20px; }
            .content-card { padding: 30px; }
            .content-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="view.php?id=<?=$cid?>" class="btn-back-nav"><i class="fas fa-arrow-left"></i> Kembali ke Course</a>
            <div class="course-label"><?= htmlspecialchars($course['title']) ?></div>
        </div>
        <div class="sidebar-list">
            <?php foreach ($all_materials as $m): 
                $active = ($m['material_id'] == $mid) ? 'active' : '';
                $icon = ($m['type'] == 'video') ? 'fa-play-circle' : 'fa-file-alt';
            ?>
            <a href="material_student_view.php?id=<?=$m['material_id']?>" class="module-item <?=$active?>">
                <i class="fas <?=$icon?> module-icon"></i>
                <span style="flex:1;"><?= htmlspecialchars($m['title']) ?></span>
                <?php if($m['is_done']): ?><i class="fas fa-check-circle check-icon"></i><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <main class="main-content">
        <div class="content-card">
            
            <h1 class="content-title"><?= htmlspecialchars($mat['title']) ?></h1>
            
            <div class="meta-info">
                <span><i class="far fa-clock"></i> <?= date('d F Y', strtotime($mat['created_at'] ?? 'now')) ?></span>
                
                <?php 
                    $badgeClass = 'badge-text';
                    if($mat['type'] == 'video') $badgeClass = 'badge-video';
                    if($mat['type'] == 'pdf') $badgeClass = 'badge-pdf';
                ?>
                <span class="badge-type <?= $badgeClass ?>"><?= $mat['type'] ?></span>
            </div>

            <?php if ($mat['type'] == 'video'): ?>
                <div class="video-wrapper">
                    <?php 
                        $url = $mat['file_path']; // Sudah diperbaiki ke file_path
                        if (isYoutube($url)): 
                    ?>
                        <iframe src="<?= getYoutubeEmbedUrl($url) ?>" allowfullscreen></iframe>
                    
                    <?php else: 
                        // Video Upload (MP4)
                        $videoPath = "../uploads/materials/" . htmlspecialchars($url);
                    ?>
                        <video controls preload="metadata" controlsList="nodownload">
                            <source src="<?= $videoPath ?>" type="video/mp4">
                            <source src="<?= $videoPath ?>" type="video/webm">
                            Maaf, browser Anda tidak mendukung pemutaran video ini.
                        </video>
                    <?php endif; ?>
                </div>
                
                <div class="text-body">
                    <h3>Deskripsi Video</h3>
                    <?= nl2br(htmlspecialchars($mat['description'] ?? '')) ?>
                </div>

            <?php elseif ($mat['type'] == 'pdf'): ?>
                
                <object data="../uploads/materials/<?= htmlspecialchars($mat['file_path']) ?>" type="application/pdf" class="pdf-viewer">
                    <div class="pdf-fallback">
                        <i class="fas fa-file-pdf" style="font-size: 3rem; color: #c62828; margin-bottom: 15px;"></i>
                        <p>Browser Anda tidak mendukung preview PDF secara langsung.</p>
                        <a href="../uploads/materials/<?= htmlspecialchars($mat['file_path']) ?>" class="btn-download">Download PDF</a>
                    </div>
                </object>
                
                <div class="text-body" style="margin-top:30px;">
                    <h3>Deskripsi Materi</h3>
                    <?= nl2br(htmlspecialchars($mat['description'] ?? '')) ?>
                </div>

            <?php else: ?>
                <div class="text-body">
                    <?= nl2br(htmlspecialchars($mat['description'] ?? '')) ?>
                </div>
                
                <?php if (!empty($mat['file_path'])): ?>
                    <div style="margin-top:40px; text-align:center; padding-top: 30px; border-top: 1px solid #eee;">
                        <a href="../uploads/materials/<?= htmlspecialchars($mat['file_path']) ?>" class="btn-download" download>
                            <i class="fas fa-download"></i> Download Lampiran
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>