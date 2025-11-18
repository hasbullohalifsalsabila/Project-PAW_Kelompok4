<?php
require __DIR__ . '/config/db.php';
if (!is_logged_in()) header('Location: auth/login.php');

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) die('course missing');

$user_id = current_user()['user_id'];
$en = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$en->execute([$user_id, $course_id]);
$enr = $en->fetch();
if (!$enr) die('Belum terdaftar');

$q = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? LIMIT 1");
$q->execute([$course_id]);
$quiz = $q->fetch();
if (!$quiz) die('Belum ada kuis untuk kursus ini');

$qs = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
$qs->execute([$quiz['quiz_id']]);
$questions = $qs->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        $correct = 0; $total = count($questions);
        foreach ($questions as $qitem) {
            $qid = $qitem['question_id'];
            $ans = strtoupper(substr(trim($_POST['q'.$qid] ?? ''),0,1));
            $is_correct = ($ans === strtoupper($qitem['correct_answer'])) ? 1 : 0;
            if ($is_correct) $correct++;
            $pdo->prepare("INSERT INTO quiz_answers (question_id, user_id, user_answer, is_correct) VALUES (?, ?, ?, ?)")->execute([$qid, $user_id, $ans, $is_correct]);
        }
        $pdo->commit();
        $score = $total > 0 ? ($correct/$total*100) : 0;
        if ($score >= 70) {
            $pdo->prepare("UPDATE enrollments SET status='completed' WHERE enroll_id = ?")->execute([$enr['enroll_id']]);
            $cert_code = 'CERT-'.strtoupper(bin2hex(random_bytes(4)));
            $pdo->prepare("INSERT INTO certificates (enroll_id, issued_date, cert_code) VALUES (?, CURDATE(), ?)")->execute([$enr['enroll_id'], $cert_code]);
            $msg = "Selamat! Anda lulus. Sertifikat: $cert_code";
        } else {
            $msg = "Belum lulus. Nilai: $correct/$total ($score%).";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "Gagal: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Kuis</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar"><div class="brand"><?=htmlspecialchars($quiz['title'])?></div><div class="nav"><a href="dashboard.php" class="btn-outline">Dashboard</a></div></div>
  <?php if ($msg): ?><div class="card"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <form method="post">
    <?php foreach ($questions as $qitem): ?>
      <div class="card">
        <p class="course-title"><?=htmlspecialchars($qitem['question_text'])?></p>
        <label><input type="radio" name="q<?=$qitem['question_id']?>" value="A"> <?=htmlspecialchars($qitem['option_a'])?></label><br>
        <label><input type="radio" name="q<?=$qitem['question_id']?>" value="B"> <?=htmlspecialchars($qitem['option_b'])?></label><br>
        <label><input type="radio" name="q<?=$qitem['question_id']?>" value="C"> <?=htmlspecialchars($qitem['option_c'])?></label><br>
        <label><input type="radio" name="q<?=$qitem['question_id']?>" value="D"> <?=htmlspecialchars($qitem['option_d'])?></label><br>
      </div>
    <?php endforeach; ?>
    <button class="btn" type="submit">Submit Jawaban</button>
  </form>
</div>
</body>
</html>
