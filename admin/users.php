<?php
// filename: admin/users.php

// 1. Deteksi Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 2. Logika Filter
$search = $_GET['search'] ?? '';
$role   = $_GET['role'] ?? '';

$query = "SELECT user_id, name, email, role, created_at FROM users WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($role !== '') {
    $query .= " AND role = :role";
    $params[':role'] = $role;
}

$query .= " ORDER BY user_id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
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
        padding-bottom: 40px;
    }

    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }

    .filter-box {
        background: white; padding: 20px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        margin-bottom: 25px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 15px;
    }
    .filter-left { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    
    .form-control { 
        padding: 10px; border: 1px solid #ccc; border-radius: 6px; 
        font-size: 14px; min-width: 200px;
    }

    .btn-dark { 
        background: #2c3e50; color: white; border: none; 
        padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;
    }
    .btn-dark:hover { background: #1a252f; }

    .btn-reset { 
        background: #95a5a6; color: white; text-decoration: none; 
        padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 14px;
    }
    .btn-reset:hover { background: #7f8c8d; }

    .btn-add { 
        background: #27ae60; color: white; text-decoration: none; 
        padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 14px; 
    }
    .btn-add:hover { background: #219150; }

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
        padding: 15px; border-bottom: 1px solid #eee; color: #333; 
        font-size: 14px; vertical-align: middle;
    }

    .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: capitalize; }
    .role-admin { background: #2c3e50; color: white; }
    .role-instructor { background: #e3f2fd; color: #1565c0; }
    .role-student { background: #f0f0f0; color: #555; border: 1px solid #ddd; }

    .btn-action { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; }
    .btn-edit { background: #ecf0f1; color: #2c3e50; }
    .btn-del { background: #ffebee; color: #c62828; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding-top: 80px; }
        .filter-box { flex-direction: column; align-items: stretch; }
        .filter-left { flex-direction: column; align-items: stretch; }
        .form-control { width: 100%; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Manage Users</h1>
    </div>

    <div class="filter-box">
        <form method="GET" class="filter-left">
            <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="<?=htmlspecialchars($search)?>">

            <select name="role" class="form-control">
                <option value="">-- Semua Role --</option>
                <option value="admin" <?=$role=='admin'?'selected':''?>>Admin</option>
                <option value="instructor" <?=$role=='instructor'?'selected':''?>>Instructor</option>
                <option value="student" <?=$role=='student'?'selected':''?>>Student</option>
            </select>

            <button class="btn-dark">Filter</button>
            <a href="users.php" class="btn-reset">✖ Reset</a>
        </form>
        
        <a href="users_add.php" class="btn-add">+ Add User</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="20%">Name</th>
                    <th width="25%">Email</th>
                    <th width="15%">Role</th>
                    <th width="20%">Joined At</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if(count($users) > 0): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($users as $u): 
                        $badgeClass = 'role-student';
                        if($u['role'] == 'admin') $badgeClass = 'role-admin';
                        if($u['role'] == 'instructor') $badgeClass = 'role-instructor';
                    ?>
                    <tr>
                        <td><?=$no++?></td>
                        <td><strong><?=htmlspecialchars($u['name'])?></strong></td>
                        <td><?=htmlspecialchars($u['email'])?></td>
                        <td>
                            <span class="badge <?=$badgeClass?>"><?=ucfirst($u['role'])?></span>
                        </td>
                        <td><?=date('d M Y', strtotime($u['created_at']))?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="users_edit.php?id=<?=$u['user_id']?>" class="btn-action btn-edit">Edit</a>
                                <a href="users_delete.php?id=<?=$u['user_id']?>" class="btn-action btn-del" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" align="center" style="padding:30px; color:#999;">User tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>
