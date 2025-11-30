<?php
require "../config/db.php";


$cid = $_GET['course'] ?? null;
if (!$cid) die("Course not found.");

// ambil course
$stmt = $pdo->prepare("SELECT c.*, u.name AS instructor 
                       FROM courses c 
                       JOIN users u ON u.user_id = c.instructor_id
                       WHERE c.course_id=?");
$stmt->execute([$cid]);
$course = $stmt->fetch();

if (!$course) die("Course not found");
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$course['title']?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">

    <style>
        /* --- GLOBAL STYLES --- */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fdfbf7; /* Krem Hangat */
            color: #333;
            margin: 0;
            padding: 30px 20px;
            line-height: 1.6;
        }

        /* --- TOMBOL KEMBALI (DI ATAS) --- */
        .top-nav {
            max-width: 850px;
            margin: 0 auto 20px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            background-color: #2c3e50; /* Biru Gelap */
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #1a252f;
            transform: translateX(-3px);
        }

        /* --- CONTAINER KARTU UTAMA --- */
        .course-detail-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff; /* Putih Bersih */
            border-radius: 12px;
            /* Bayangan Lembut & Border Tipis */
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #f0ece3;
            overflow: hidden;
            padding: 40px;
        }

        /* --- HEADER KURSUS --- */
        h1 {
            font-family: 'Playfair Display', serif; /* Font Elegan */
            font-size: 2.2rem;
            color: #1a1a1a;
            margin-bottom: 10px;
            font-weight: 700;
            line-height: 1.2;
        }

        p.short-desc {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
            font-weight: 400;
        }

        /* --- INFO BOX --- */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            background-color: #faf9f6; /* Abu-abu sangat muda (hampir putih) */
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #f0ece3;
            margin-bottom: 35px;
        }

        .info-item strong {
            display: block;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .info-item span {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .price-tag {
            color: #c49b63 !important; /* Warna Emas */
            font-size: 1.2rem !important;
        }

        /* --- DESKRIPSI --- */
        .full-desc {
            font-size: 1rem;
            color: #444;
            margin-bottom: 40px;
            white-space: pre-line;
        }

        /* --- TOMBOL ENROLL --- */
        .btn-enroll {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #2c3e50; /* Biru Gelap */
            color: white;
            padding: 18px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.2);
        }

        .btn-enroll:hover {
            background-color: #c49b63; /* Berubah jadi Emas saat hover */
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(196, 155, 99, 0.3);
        }

        hr {
            border: 0;
            border-top: 1px solid #f0ece3;
            margin: 40px 0;
        }

        /* --- REVIEWS --- */
        h2, h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        
        h2 { font-size: 1.8rem; }
        h3 { font-size: 1.4rem; margin-top: 0; }

        .review-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .review-card {
            background: #fff;
            border: 1px solid #f0ece3;
            padding: 20px;
            border-radius: 10px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: 700;
            color: #2c3e50;
        }

        .star-rating {
            color: #c49b63; /* Bintang Emas */
            font-weight: bold;
            background: #fff8e1; /* Background kuning sangat muda */
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* --- FORM INPUT --- */
        .review-form-card {
            background: #faf9f6;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #f0ece3;
        }

        label {
            font-weight: 700;
            color: #555;
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdcdc; 
            border-radius: 8px;
            margin-bottom: 15px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .btn-dark {
            background: #2c3e50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.3s;
        }
        .btn-dark:hover {
            background-color: #1a252f;
        }
    </style>
</head>
<body>

    <div class="top-nav">
        <a class="btn-back" href="../courses/courses_list.php">
            &larr; Kembali Ke Daftar Kursus
        </a>
    </div>

    <div class="course-detail-container">

        <h1><?=$course['title']?></h1>
        <p class="short-desc"><?=$course['short_desc']?></p>

        <div class="info-grid">
            <div class="info-item">
                <strong>👨‍🏫 Instructor</strong>
                <span><?=$course['instructor']?></span>
            </div>
            <div class="info-item">
                <strong>📊 Level</strong>
                <span><?=$course['level']?></span>
            </div>
            <div class="info-item">
                <strong>💰 Harga</strong>
                <span class="price-tag">
                    <?=($course['price'] == 0 ? "Gratis" : "Rp ".number_format($course['price'],0,',','.'))?>
                </span>
            </div>
        </div>

        <div class="full-desc">
            <?=$course['description']?>
        </div>

       <?php if ($course['price'] == 0): ?>
            
            <a class="btn-enroll" href="course_enroll.php?course=<?=$course['course_id']?>">
                Daftar Gratis Sekarang 🚀
            </a>

        <?php else: ?>

            <a class="btn-enroll" href="payment.php?course=<?=$course['course_id']?>">
                Beli Kursus Ini 💳
            </a>

        <?php endif; ?>

        <hr>

        <h2>🌟 Rating & Reviews</h2>

        <div class="review-list">
            <?php
            $rev = $pdo->prepare("
                SELECT r.*, u.name 
                FROM reviews r 
                JOIN users u ON u.user_id=r.user_id
                WHERE course_id=? ORDER BY reviewed_at DESC
            ");
            $rev->execute([$cid]);
            
            if ($rev->rowCount() == 0) {
                echo "<p style='color:#888; font-style:italic;'>Belum ada review untuk kursus ini.</p>";
            }

            foreach ($rev as $r):
            ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="reviewer-name"><?=$r['name']?></span>
                        <span class="star-rating">⭐ <?=$r['rating']?>/5</span>
                    </div>
                    <p style="margin:0; color:#555;">"<?=$r['comment']?>"</p>
                </div>
            <?php endforeach; ?>
        </div>

        <br><br>

        <?php if(isset($_SESSION['user'])): ?>
        <div class="review-form-card">
            <h3>📝 Tulis Pengalaman Anda</h3>
            <form action="../review/review_submit.php" method="post">
                <input type="hidden" name="course_id" value="<?=$cid?>">
                
                <label>Rating:</label>
                <select name="rating" required>
                    <option value="">-- Pilih Bintang --</option>
                    <option value="5">⭐⭐⭐⭐⭐ - Sangat Bagus</option>
                    <option value="4">⭐⭐⭐⭐ - Bagus</option>
                    <option value="3">⭐⭐⭐ - Cukup</option>
                    <option value="2">⭐⭐ - Kurang</option>
                    <option value="1">⭐ - Sangat Kurang</option>
                </select>

                <label>Komentar:</label>
                <textarea name="comment" rows="4" placeholder="Bagaimana pendapat Anda tentang materi ini?" required></textarea>

                <button class="btn-dark" style="width:100%">Kirim Review</button>
            </form>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>