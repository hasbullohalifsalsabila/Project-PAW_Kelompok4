<?php /*
// seed.php (run once). Delete after successful run.
require __DIR__ . '/config/db.php';
try {
    $pdo->beginTransaction();

    // users (admin, instructor, students)
    $u = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)");
    $u->execute(['Admin Utama','admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    $u->execute(['Instruktur A','instructor@example.com', password_hash('inst123', PASSWORD_DEFAULT), 'instructor']);
    $u->execute(['Mahasiswa Satu','student1@example.com', password_hash('stud123', PASSWORD_DEFAULT), 'student']);
    $u->execute(['Mahasiswa Dua','student2@example.com', password_hash('stud456', PASSWORD_DEFAULT), 'student']);

    // instructor profile (instructor id = 2)
    $stmt = $pdo->prepare("INSERT INTO instructors_profile (instructor_id,bio,photo_url,linkedin,website) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([2, 'Instruktur berpengalaman Web Dev dan Database.', '', 'https://linkedin.com/in/instrukturA', 'https://instruktora.example.com']);

    // categories
    $cats = ['Web Development','Programming','Database','Mobile Development','Design'];
    $cstmt = $pdo->prepare("INSERT INTO categories (name,description) VALUES (?, ?)");
    foreach ($cats as $c) $cstmt->execute([$c, $c . ' courses']);

    // courses
    $courses = [
        ['Belajar HTML Dasar','Dasar membangun halaman web','HTML lengkap dengan contoh nyata',0,'beginner'],
        ['CSS Fundamental','Styling website modern','CSS responsive, layouting, animasi',0,'beginner'],
        ['JavaScript Modern ES6+','JavaScript generasi baru','DOM, ES6+, modul, fetch API',150000,'intermediate'],
        ['PHP dan MySQL Lengkap','Backend Web Development','CRUD, autentikasi, OOP',120000,'beginner'],
        ['Laravel Framework','Laravel dari dasar hingga mahir','Routing, Blade, ORM, API',250000,'advanced'],
        ['React JS Pemula','Frontend modern','Hooks, component, routing',180000,'intermediate'],
        ['Python Dasar','Pemrograman Python','Syntax, OOP',0,'beginner'],
        ['Data Science Python','Analisis data','Pandas, Numpy, ML dasar',280000,'advanced'],
        ['UI/UX Design','Dasar desain aplikasi','Wireframe, prototype',0,'beginner'],
        ['Android Kotlin','Aplikasi Android modern','Kotlin, activity, API',200000,'intermediate']
    ];
    $cins = $pdo->prepare("INSERT INTO courses (instructor_id,title,short_desc,description,price,level) VALUES (2,?,?,?,?,?)");
    foreach ($courses as $cs) $cins->execute([$cs[0], $cs[1], $cs[2], $cs[3], $cs[4]]);

    // course categories mapping (course_id assumed 1..10, category_id 1..5)
    $map = [
        [1,1],[1,2],
        [2,1],
        [3,1],[3,2],
        [4,1],[4,3],
        [5,1],
        [6,1],
        [7,2],
        [8,2],[8,3],
        [9,5],
        [10,4]
    ];
    $mstmt = $pdo->prepare("INSERT INTO courses_categories (course_id, category_id) VALUES (?, ?)");
    foreach ($map as $m) $mstmt->execute($m);

    // materials (some)
    $m = $pdo->prepare("INSERT INTO materials (course_id,title,type,file_url,position) VALUES (?, ?, ?, ?, ?)");
    $m->execute([1,'Pengenalan HTML','text','materi/html_intro.html',1]);
    $m->execute([1,'Struktur Dokumen HTML','pdf','materi/html_structure.pdf',2]);
    $m->execute([2,'Dasar CSS','text','materi/css_basic.html',1]);
    $m->execute([2,'Flexbox dan Grid','video','https://example.com/css_flexbox.mp4',2]);
    $m->execute([3,'JavaScript Dasar ES6+','video','https://example.com/js_intro.mp4',1]);
    $m->execute([4,'Dasar PHP','text','materi/php_basic.html',1]);
    $m->execute([4,'Koneksi MySQL','video','https://example.com/php_mysql.mp4',2]);
    $m->execute([5,'Instalasi Laravel','video','https://example.com/laravel_install.mp4',1]);
    $m->execute([5,'Routing dan Blade','text','materi/laravel_routing.html',2]);

    // quizzes & questions (simple)
    $qins = $pdo->prepare("INSERT INTO quizzes (course_id,title) VALUES (?, ?)");
    $qqins = $pdo->prepare("INSERT INTO quiz_questions (quiz_id,question_text,option_a,option_b,option_c,option_d,correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    // quiz course 1
    $qins->execute([1,'Kuis HTML Dasar']); $quiz1 = $pdo->lastInsertId();
    $qqins->execute([$quiz1, 'Tag pembuka HTML adalah?', '<html>', '<body>', '<div>', '<head>', 'A']);
    $qqins->execute([$quiz1, 'Elemen teks tebal adalah?', '<b>', '<strong>', '<bold>', '<text>', 'B']);
    // quiz course 3
    $qins->execute([3,'Kuis JavaScript ES6']); $quiz2 = $pdo->lastInsertId();
    $qqins->execute([$quiz2, 'Keyword ES6 untuk variabel?', 'var', 'let', 'const', 'define', 'B']);
    $qqins->execute([$quiz2, 'Jenis fungsi ES6 modern?', 'arrow function', 'regular function', 'prototype', 'async', 'A']);

    $pdo->commit();
    echo "Seed berhasil. Silakan hapus atau amankan file seed.php";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seed gagal: " . $e->getMessage();
}*/
