<?php
require "../config/db.php";

// Cek Login
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$cid = $_GET['course'] ?? null;
if (!$cid) die("Course not found.");

// Ambil Detail Kursus
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$cid]);
$course = $stmt->fetch();

if (!$course) die("Kursus tidak ditemukan.");

// --- LOGIKA PROSES UPLOAD & PENDAFTARAN ---
$success_msg = "";
$error_msg = "";

if (isset($_POST['btn_upload'])) {
    $uid = $_SESSION['user']['user_id'];
    
    // 1. Validasi File
    if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['proof_file']['name'];
        $filetmp = $_FILES['proof_file']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Buat nama file unik
            $new_filename = "PAY_" . $uid . "_" . $cid . "_" . time() . "." . $ext;
            $destination = "../uploads/payments/" . $new_filename;

            // Pastikan folder ada
            if (!is_dir("../uploads/payments/")) {
                mkdir("../uploads/payments/", 0777, true);
            }

            // 2. Upload File & Simpan Database
            if (move_uploaded_file($filetmp, $destination)) {
                
                // Cek dulu apakah sudah terdaftar (untuk menghindari duplikat)
                $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id=? AND course_id=?");
                $check->execute([$uid, $cid]);
                
                if ($check->rowCount() == 0) {
                    try {
                        $pdo->beginTransaction(); // Mulai Transaksi Database

                        // A. INSERT ke Enrollments
                        $sqlEnroll = "INSERT INTO enrollments (user_id, course_id, enrolled_at, payment_proof, status) VALUES (?, ?, NOW(), ?, 'pending')";
                        $stmtEnroll = $pdo->prepare($sqlEnroll);
                        $stmtEnroll->execute([$uid, $cid, $new_filename]);
                        
                        // Dapatkan ID enrollment yang baru saja dibuat
                        $enroll_id = $pdo->lastInsertId();

                        // B. INSERT ke Payments (PENTING UNTUK LAPORAN KEUANGAN)
                        // Kita simpan harga kursus saat ini ke tabel payments agar tercatat
                        $sqlPay = "INSERT INTO payments (enroll_id, amount, method, status, paid_at) VALUES (?, ?, 'Bank Transfer', 'pending', NOW())";
                        $stmtPay = $pdo->prepare($sqlPay);
                        $stmtPay->execute([$enroll_id, $course['price']]);

                        $pdo->commit(); // Simpan Perubahan
                        
                        // Set flag sukses untuk trigger JS di bawah
                        $success_msg = "Pembayaran berhasil dikirim! Mohon tunggu verifikasi admin.";

                    } catch (PDOException $e) {
                        $pdo->rollBack(); // Batalkan jika ada error database
                        $error_msg = "Database Error: " . $e->getMessage();
                    }
                } else {
                    $error_msg = "Anda sudah terdaftar di kursus ini sebelumnya.";
                }

            } else {
                $error_msg = "Gagal mengupload file ke server. Cek folder permission.";
            }
        } else {
            $error_msg = "Format file tidak valid. Gunakan JPG, PNG, atau PDF.";
        }
    } else {
        $error_msg = "Mohon pilih file bukti pembayaran.";
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload Bukti - <?=$course['title']?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdfbf7;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .payment-card {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f0ece3;
            text-align: center;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            margin-top: 0;
            color: #1a1a1a;
            font-size: 1.8rem;
        }

        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: #c49b63;
            margin-bottom: 10px;
        }

        .qris-container {
            margin: 20px 0;
            padding: 15px;
            border: 2px dashed #c49b63;
            border-radius: 12px;
            background-color: #fffdf5;
            display: inline-block;
        }

        .qris-img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            display: block;
        }

        /* Form Styling */
        .upload-area {
            margin-top: 20px;
            text-align: left;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        .file-input {
            width: 100%;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background-color: #1a252f;
        }

        .btn-cancel {
            display: inline-block;
            margin-top: 15px;
            color: #999;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="payment-card">
        <h1>Pembayaran</h1>
        
        <p style="margin-bottom: 5px; color: #666;">Transfer sebesar:</p>
        <div class="price-display">
            Rp <?=number_format($course['price'], 0, ',', '.')?>
        </div>

        <div class="qris-container">
            <img src="../assets/images/qris.png" alt="Scan QRIS" class="qris-img">
        </div>
        
        <p style="color:#888; font-size:0.85rem; margin-top:0;">
            Scan QRIS di atas, lalu upload bukti transfer di bawah ini.
        </p>

        <?php if($error_msg): ?>
            <div class="alert-error"><?=$error_msg?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="upload-area">
            <label class="form-label">Upload Bukti Transfer (JPG/PNG/PDF)</label>
            <input type="file" name="proof_file" class="file-input" required accept=".jpg,.jpeg,.png,.pdf">
            
            <button type="submit" name="btn_upload" class="btn-submit">
                Kirim Bukti & Daftar Kelas
            </button>
        </form>

        <a href="course_detail.php?course=<?=$cid?>" class="btn-cancel">Batal</a>
    </div>

    <?php if($success_msg): ?>
    <script>
        alert("✅ <?= $success_msg ?>");
        // Redirect ke halaman My Courses
        // Sesuaikan path jika file ini ada di folder 'student' atau root
        window.location.href = '../student/my_courses.php'; 
    </script>
    <?php endif; ?>

</body>
</html>