<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in()) header('Location: ../auth/login.php');

 $course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) die('course_id missing');

// Ambil data kursus
 $stmt = $pdo->prepare("SELECT c.*, u.name AS instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id WHERE c.course_id = ?");
 $stmt->execute([$course_id]);
 $course = $stmt->fetch();
if (!$course) die('Course not found');

 $msg = null;
 $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $user_id = current_user()['user_id'];
    $class_id = intval($_POST['class_id'] ?? 0);

    if ($class_id === 0) {
        $error = "Anda harus memilih sebuah kelas.";
    } else {
        // Cek apakah user sudah enroll di SALAH SATU kelas di kursus ini
        $check = $pdo->prepare("
            SELECT e.enroll_id 
            FROM enrollments e 
            JOIN classes cl ON e.class_id = cl.class_id 
            WHERE e.user_id = ? AND cl.course_id = ?
        ");
        $check->execute([$user_id, $course_id]);
        if ($check->fetch()) {
            $msg = "Anda sudah terdaftar di salah satu kelas pada kursus ini.";
        } else {
            // Cek kapasitas kelas
            $capacityCheck = $pdo->prepare("
                SELECT cl.max_students, COUNT(e.enroll_id) as current_students 
                FROM classes cl 
                LEFT JOIN enrollments e ON cl.class_id = e.class_id 
                WHERE cl.class_id = ?
                GROUP BY cl.class_id, cl.max_students
            ");
            $capacityCheck->execute([$class_id]);
            $classData = $capacityCheck->fetch();

            if ($classData && $classData['current_students'] < $classData['max_students']) {
                // Kapasitas masih ada, lakukan enroll
                $ins = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, class_id) VALUES (?, ?, ?)");
                $ins->execute([$user_id, $course_id, $class_id]);
                $enroll_id = $pdo->lastInsertId();

                if (floatval($course['price']) > 0) {
                    header("Location: ../payment.php?enroll_id=$enroll_id"); exit;
                } else {
                    $msg = "Enroll berhasil! Akses materi di halaman dashboard.";
                }
            } else {
                $error = "Kelas yang Anda pilih sudah penuh. Silakan pilih kelas lain.";
            }
        }
    }
}

// Ambil semua kelas untuk kursus ini beserta jumlah siswa yang sudah mendaftar
 $classesStmt = $pdo->prepare("
    SELECT 
        cl.class_id, 
        cl.class_name, 
        cl.max_students, 
        COUNT(e.enroll_id) as enrolled_students
    FROM classes cl
    LEFT JOIN enrollments e ON cl.class_id = e.class_id
    WHERE cl.course_id = ?
    GROUP BY cl.class_id
    ORDER BY cl.class_name
");
 $classesStmt->execute([$course_id]);
 $availableClasses = $classesStmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title><?=htmlspecialchars($course['title'])?></title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand"><?=htmlspecialchars($course['title'])?></div>
    <div class="nav"><a href="index.php" class="btn-outline">Kembali</a></div>
  </div>

  <div class="card">
    <div class="course-title"><?=htmlspecialchars($course['title'])?></div>
    <div class="meta">Instruktur: <?=htmlspecialchars($course['instructor_name'])?> | Level: <?=htmlspecialchars($course['level'])?></div>
    <p style="margin-top:12px;"><?=nl2br(htmlspecialchars($course['description']))?></p>
    <p style="margin-top:12px;">Harga: <?= $course['price']==0 ? 'Gratis' : 'Rp '.number_format($course['price'],0,',','.') ?></p>

    <?php if ($msg): ?><div class="alert-success" style="color:#16a085;margin-bottom:10px;"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error" style="color:#e74c3c;margin-bottom:10px;"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <h3 style="margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Pilih Kelas</h3>

    <?php if (empty($availableClasses)): ?>
        <p>Belum ada kelas yang tersedia untuk kursus ini.</p>
    <?php else: ?>
        <form method="post">
            <div style="display: grid; gap: 10px; margin-bottom: 20px;">
                <?php foreach ($availableClasses as $class): ?>
                    <label style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
                        <div>
                            <input type="radio" name="class_id" value="<?= $class['class_id'] ?>" required>
                            <strong><?= htmlspecialchars($class['class_name']) ?></strong>
                        </div>
                        <div style="font-size: 0.9em; color: #555;">
                            Kapasitas: <?= $class['enrolled_students'] ?> / <?= $class['max_students'] ?> Student
                            <?php if ($class['enrolled_students'] >= $class['max_students']): ?>
                                <span style="color: #e74c3c; font-weight: bold;">(Penuh)</span>
                            <?php endif; ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <button class="btn" name="enroll" type="submit">Daftar / Enroll</button>
        </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>