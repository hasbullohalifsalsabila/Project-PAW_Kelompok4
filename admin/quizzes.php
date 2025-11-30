<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Ambil course
$courses = $pdo->query("SELECT course_id,title FROM courses")->fetchAll();

$course_id = $_GET['course'] ?? '';

$query = "
    SELECT q.quiz_id, q.title, c.title AS course_title
    FROM quizzes q
    JOIN courses c ON q.course_id = c.course_id
    WHERE 1
";

if ($course_id !== '') $query .= " AND q.course_id = :cid";

$stmt = $pdo->prepare($query);

if ($course_id !== '') $stmt->bindValue(':cid', $course_id);

$stmt->execute();
$quizzes = $stmt->fetchAll();
?>

<div class="admin-container">

    <h1 class="page-title">Manage Quizzes</h1>

    <div class="filter-bar">
        <form method="GET" class="filter-form">

            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?=$course_id==$c['course_id']?'selected':''?>><?=$c['title']?></option>
                <?php endforeach ?>
            </select>

            <button class="btn-dark">Filter</button>

            <a href="quiz_add.php" class="btn-add">+ Add Quiz</a>
        </form>
    </div>

    <div class="table-card">
        <table>
            <tr>
                <th>ID</th>
                <th>Quiz Title</th>
                <th>Course</th>
                <th width="200">Action</th>
            </tr>

            <?php foreach ($quizzes as $q): ?>
            <tr>
                <td><?=$q['quiz_id']?></td>
                <td><?=$q['title']?></td>
                <td><?=$q['course_title']?></td>

                <td>
                    <a href="questions.php?quiz=<?=$q['quiz_id']?>" class="btn-small">Questions</a>
                    <a href="quiz_edit.php?id=<?=$q['quiz_id']?>" class="btn-small">Edit</a>
                    <a href="quiz_delete.php?id=<?=$q['quiz_id']?>" class="btn-small delete"
                       onclick="return confirm('Delete quiz?')">Delete</a>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    </div>
</div>