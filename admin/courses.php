<?php
// filename: admin/courses.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Ambil semua user dengan role 'instructor'
try {
    $stmt = $pdo->query("SELECT user_id, name, email FROM users WHERE role = 'instructor' ORDER BY name ASC");
    $instructors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

// Konstanta Batasan
const MAX_STUDENTS_PER_CLASS = 10;
const MAX_COURSES_PER_INSTRUCTOR = 5;
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        padding-top: 80px;
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 50px;
    }

    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 2rem; color: #2c3e50; margin: 0; 
    }
    .page-desc { color: #777; margin: 5px 0 0 0; font-size: 14px; }
    
    .table-card { 
        background: white; padding: 25px; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
    }
    table { width: 100%; border-collapse: collapse; }
    th { 
        background: #f8f9fa; padding: 15px; text-align: left; 
        font-size: 14px; font-weight: bold; color: #555; 
        border-bottom: 2px solid #eee; 
    }
    td { 
        padding: 15px; border-bottom: 1px solid #eee; 
        color: #333; vertical-align: top;
    }

    .course-list { display: flex; flex-direction: column; gap: 8px; }
    
    .course-item {
        display: flex; justify-content: space-between; align-items: center;
        background-color: #fff; border: 1px solid #eee;
        padding: 8px 12px; border-radius: 6px;
        transition: 0.2s;
    }
    .course-item:hover { background-color: #f9f9f9; border-color: #ddd; }
    
    .course-title { font-weight: 600; color: #444; font-size: 13px; display: flex; align-items: center; gap: 5px;}

    .badge-full {
        background-color: #27ae60; color: white; 
        font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px;
    }

    .btn-manage { 
        font-size: 11px; color: #2980b9; text-decoration: none; font-weight: bold; 
        background: #eaf2f8; padding: 4px 8px; border-radius: 4px;
    }
    .btn-manage:hover { background: #d4e6f1; }

    .badge-empty { 
        background-color: #f0f0f0; color: #888; padding: 5px 10px; 
        border-radius: 4px; font-size: 12px; font-style: italic; display: inline-block;
    }

    .action-group { display: flex; flex-direction: column; gap: 8px; }

    .btn-add-course {
        display: inline-block; text-align: center; width: 100%;
        background: #2c3e50; color: white; text-decoration: none;
        padding: 8px 0; border-radius: 6px; font-size: 13px; font-weight: bold;
        transition: 0.3s;
    }
    .btn-add-course:hover { background: #1a252f; }

    .btn-disabled {
        background: #ccc; color: #666; cursor: not-allowed;
        pointer-events: none;
    }

    .btn-delete-instructor {
        display: inline-block; text-align: center; width: 100%;
        background: #fff; color: #c0392b; text-decoration: none;
        padding: 6px 0; border-radius: 6px; font-size: 12px; font-weight: bold;
        border: 1px solid #e74c3c; transition: 0.3s;
    }
    .btn-delete-instructor:hover { background: #c0392b; color: white; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 80px 20px; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <div>
            <h1 class="page-title">Manage Courses</h1>
            <p class="page-desc">Daftar Instruktur dan Kelas yang mereka ajar.</p>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 25%;">Instruktur</th>
                    <th style="width: 50%;">Daftar Kelas (Course)</th>
                    <th style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody>

                <?php $no = 1; ?>  <!-- NOMOR URUT DIMULAI DARI 1 -->

                <?php foreach ($instructors as $ins): 

                    $sql_courses = "
                        SELECT c.course_id, c.title, 
                        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) as total_students
                        FROM courses c 
                        WHERE c.instructor_id = ? 
                        ORDER BY c.created_at DESC
                    ";
                    $stmt_c = $pdo->prepare($sql_courses);
                    $stmt_c->execute([$ins['user_id']]);
                    $my_courses = $stmt_c->fetchAll();
                    
                    $course_count = count($my_courses);
                    $is_limit_reached = $course_count >= MAX_COURSES_PER_INSTRUCTOR;
                ?>

                <tr>
                    <td><?= $no++ ?></td> <!-- NOMOR URUT -->

                    <td>
                        <strong style="font-size:15px; color:#2c3e50;"><?= htmlspecialchars($ins['name']) ?></strong><br>
                        <small style="color:#888;"><?= htmlspecialchars($ins['email']) ?></small><br>
                        <small style="color:<?= $is_limit_reached ? '#c0392b' : '#27ae60' ?>; font-weight:bold;">
                            <?= $course_count ?> / <?= MAX_COURSES_PER_INSTRUCTOR ?> Kelas
                        </small>
                    </td>

                    <td>
                        <?php if($course_count > 0): ?>
                            <div class="course-list">
                                <?php foreach($my_courses as $c): 
                                    $student_count = $c['total_students'];
                                    $is_full = $student_count >= MAX_STUDENTS_PER_CLASS;
                                ?>
                                    <div class="course-item" style="<?= $is_full ? 'border-left: 3px solid #27ae60;' : '' ?>">
                                        <span class="course-title">
                                            📘 <?= htmlspecialchars($c['title']) ?>
                                            <?php if($is_full): ?>
                                                <span class="badge-full">✓ FULL</span>
                                            <?php endif; ?>
                                            <span style="color:#999; font-size:11px; margin-left:5px;">
                                                (<?= $student_count ?>/<?= MAX_STUDENTS_PER_CLASS ?> Siswa)
                                            </span>
                                        </span>

                                        <div style="display:flex; gap:8px;">
                                            <a href="manage_course.php?id=<?=$c['course_id']?>" class="btn-manage">
                                                Kelola →
                                            </a>

                                            <a href="course_delete.php?id=<?=$c['course_id']?>" 
                                               onclick="return confirm('Yakin ingin menghapus kelas ini? Semua materi di dalamnya akan hilang!')"
                                               style="background:#fdecea; color:#c0392b; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold; text-decoration:none;">
                                                Hapus
                                            </a>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge-empty">Belum ada kelas yang dibuat.</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="action-group">
                            <?php if($is_limit_reached): ?>
                                <span class="btn-add-course btn-disabled" title="Batas tercapai">
                                    🚫 Batas Tercapai
                                </span>
                            <?php else: ?>
                                <a href="course_add.php?instructor_id=<?= $ins['user_id'] ?>" class="btn-add-course">
                                    + Tambah Kelas
                                </a>
                            <?php endif; ?>

                            <a href="instructor_delete.php?id=<?= $ins['user_id'] ?>" 
                               class="btn-delete-instructor"
                               onclick="return confirm('PERINGATAN: Menghapus instruktur ini akan menghapus semua kelas mereka! Yakin?')">
                               🗑 Hapus Instruktur
                            </a>
                        </div>
                    </td>
                </tr>

                <?php endforeach; ?>
                
                <?php if(count($instructors) == 0): ?>
                    <tr><td colspan="4" align="center" style="padding:30px; color:#999;">Belum ada instruktur terdaftar.</td></tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>
