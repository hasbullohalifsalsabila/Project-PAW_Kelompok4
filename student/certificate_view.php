<?php
require '../config/db.php';

// Ambil Kode Sertifikat dari URL
$code = $_GET['code'] ?? '';

// Ambil Detail Sertifikat dari Database
$stmt = $pdo->prepare("
    SELECT ce.*, c.title AS course_title, u.name AS student_name
    FROM certificates ce
    JOIN enrollments e ON ce.enroll_id = e.enroll_id
    JOIN courses c ON e.course_id = c.course_id
    JOIN users u ON e.user_id = u.user_id
    WHERE ce.cert_code = ?
");
$stmt->execute([$code]);
$cert = $stmt->fetch();

if (!$cert) {
    die("Sertifikat tidak ditemukan. Pastikan URL Anda benar.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - <?= htmlspecialchars($cert['course_title']) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Pinyon+Script&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        /* --- RESET DAN STYLE DASAR --- */
        body {
            background-color: #1a252f; /* Latar belakang luar lebih gelap agar sertifikat menonjol */
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 30px;
        }

        /* --- TOMBOL NAVIGASI (Tidak dicetak) --- */
        .nav-buttons {
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-back {
            background: rgba(255,255,255,0.15); color: white;
            backdrop-filter: blur(5px);
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }
        
        .btn-print {
            background: linear-gradient(135deg, #c49b63 0%, #b08855 100%); /* Gradien Emas */
            color: white;
            box-shadow: 0 8px 20px rgba(196, 155, 99, 0.3);
        }
        .btn-print:hover {
            box-shadow: 0 12px 25px rgba(196, 155, 99, 0.5);
            transform: translateY(-3px);
        }

        /* --- WADAH KERTAS SERTIFIKAT (Peningkatan Gaya) --- */
        .certificate-paper {
            width: 1000px; /* A4 Landscape */
            height: 700px;
            background-color: #fffdf5; /* Dasar Krem */
            /* Menambahkan tekstur halus latar belakang (opsional) */
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23c49b63' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            position: relative;
            /* Bingkai Berlapis yang Lebih Elegan */
            border: 12px solid #2c3e50; /* Lapisan Biru Luar */
            outline: 4px solid #c49b63; /* Lapisan Emas Tengah */
            outline-offset: -18px;
            
            padding: 50px;
            box-sizing: border-box;
            /* Bayangan yang lebih lembut dan dalam */
            box-shadow: 0 30px 60px rgba(0,0,0,0.3), 0 0 100px rgba(196, 155, 99, 0.1) inset;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Ornamen Sudut (Opsional - memberikan kesan klasik) */
        .corner-ornament {
            position: absolute;
            width: 60px; height: 60px;
            border: 3px solid #c49b63;
            pointer-events: none;
            opacity: 0.6;
        }
        .top-left { top: 25px; left: 25px; border-right: none; border-bottom: none; }
        .top-right { top: 25px; right: 25px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 25px; left: 25px; border-right: none; border-top: none; }
        .bottom-right { bottom: 25px; right: 25px; border-left: none; border-top: none; }

        /* --- KONTEN AREA --- */
        .cert-content {
            border: 2px solid transparent; /* Placeholder agar layout stabil */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* --- TIPOGRAFI --- */
        .cert-header {
            font-family: 'Cinzel', serif; /* Font Klasik */
            font-size: 3.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .cert-subheader {
            font-family: 'Cinzel', serif;
            font-size: 1.3rem;
            color: #c49b63;
            margin-top: 8px;
            letter-spacing: 3px;
            text-transform: uppercase;
            border-bottom: 2px solid #c49b63;
            padding-bottom: 10px;
            display: inline-block;
        }

        .cert-present {
            font-size: 1.15rem;
            color: #555;
            margin-top: 45px;
            margin-bottom: 15px;
            font-style: italic;
        }

        /* Nama Siswa */
        .student-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 5rem;
            color: #c49b63;
            margin: 15px 0;
            line-height: 1.1;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }

        /* Isi Body */
        .cert-body {
            font-size: 1.25rem;
            color: #444;
            max-width: 75%;
            line-height: 1.7;
            margin-top: 15px;
        }

        .course-name {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.6rem;
            display: block; /* Agar nama kursus di baris baru */
            margin: 10px 0;
        }

        /* --- FOOTER & TANDA TANGAN --- */
        .cert-footer {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
            width: 85%;
        }

        .signature-block {
            text-align: center;
            width: 240px;
            position: relative;
        }

        /* Garis Tanda Tangan yang Lebih Halus */
        .sign-line {
            width: 220px;
            border-top: 2px solid #c49b63; /* Ubah jadi emas agar lebih elegan */
            margin: 10px auto; /* Auto kiri-kanan = presisi tengah */
            opacity: 0.7;
        }

        .sign-name {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .sign-title {
            font-size: 0.85rem;
            color: #777;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Credential ID */
        .cert-id {
            position: absolute;
            bottom: 30px;
            font-size: 0.8rem;
            color: #aaa;
            letter-spacing: 1px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            width: 50%;
            left: 25%;
        }

        /* --- MEDIA QUERY: CETAK (PRINT) --- */
        @media print {
            body { 
                background: none; 
                margin: 0; padding: 0;
                -webkit-print-color-adjust: exact; /* Agar warna background tercetak */
            }
            .nav-buttons { display: none !important; }
            .certificate-paper {
                box-shadow: none;
                width: 100%;
                height: 100vh; /* Full halaman */
                /* Penyesuaian bingkai saat print agar tidak terpotong */
                border-width: 5px;
                outline-offset: -10px;
                outline-width: 2px;
                page-break-after: always;
            }
            @page {
                size: landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="nav-buttons">
        <button onclick="history.back()" class="btn btn-back">
            <span>&larr;</span> Kembali
        </button>
        <button onclick="window.print()" class="btn btn-print">
            <span>🖨️</span> Download / Print PDF
        </button>
    </div>

    <div class="certificate-paper">
        
        <div class="corner-ornament top-left"></div>
        <div class="corner-ornament top-right"></div>
        <div class="corner-ornament bottom-left"></div>
        <div class="corner-ornament bottom-right"></div>

        <div class="cert-content">
            
            <h1 class="cert-header">Certificate</h1>
            <div class="cert-subheader">of Completion</div>

            <p class="cert-present">This is to certify that</p>

            <div class="student-name"><?= htmlspecialchars($cert['student_name']) ?></div>

            <div class="cert-body">
                has successfully completed the course
                <span class="course-name">“<?= htmlspecialchars($cert['course_title']) ?>”</span>
                on <?= date('F d, Y', strtotime($cert['issued_date'])) ?>
            </div>

            <div class="cert-footer">
                
                <div class="signature-block">
                    <div class="sign-name" style="margin-bottom: 5px; font-family: 'Inter';">
                        <?= date('d M Y', strtotime($cert['issued_date'])) ?>
                    </div>
                    <div class="sign-line"></div>
                    <div class="sign-title">Date Issued</div>
                </div>

                <div class="signature-block">
                    <div style="font-family: 'Pinyon Script'; font-size: 2rem; color: #2c3e50; position: absolute; bottom: 45px; width: 100%;">
                        EduCourse Team
                    </div>
                    <div class="sign-line" style="margin-top: 55px;"></div>
                    <div class="sign-name">Instructor / Director</div>
                    <div class="sign-title">Authorized Signature</div>
                </div>

            </div>

            <div class="cert-id">Credential ID: <?= htmlspecialchars($cert['cert_code']) ?></div>

        </div>
    </div>

</body>
</html>