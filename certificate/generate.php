<?php
require '../config/db.php';
require '../libs/fpdf.php';

$uid = $_GET['user'];
$eid = $_GET['enroll'];

// Load data
$stmt = $pdo->prepare("
    SELECT u.name, c.title, e.enrolled_at
    FROM enrollments e
    JOIN users u ON e.user_id=u.user_id
    JOIN courses c ON e.course_id=c.course_id
    WHERE enroll_id=?
");
$stmt->execute([$eid]);
$data = $stmt->fetch();

$name = $data['name'];
$course = $data['title'];
$date = date("d M Y");

// Generate certificate code
$code = "CERT-" . strtoupper(substr(md5($eid.$name),0,10));

// Save to DB
$pdo->prepare("
    INSERT INTO certificates (enroll_id, issued_date, cert_code)
    VALUES (?,?,?)
")->execute([$eid, date("Y-m-d"), $code]);

// CREATE PDF
$pdf = new FPDF("L","mm","A4");
$pdf->AddPage();

// Background template
$pdf->Image("template_bg.png", 0, 0, 297, 210);

// Content
$pdf->SetFont("Arial","B",28);
$pdf->SetTextColor(70,60,50);
$pdf->Cell(0,30,"Certificate of Completion",0,1,'C');

$pdf->Ln(10);
$pdf->SetFont("Arial","",18);
$pdf->Cell(0,10,"This certificate is proudly presented to",0,1,'C');

$pdf->Ln(5);
$pdf->SetFont("Arial","B",26);
$pdf->Cell(0,15,$name,0,1,'C');

$pdf->Ln(10);
$pdf->SetFont("Arial","",16);
$pdf->MultiCell(0,10,"For successfully completing the course:\n" . $course,0,'C');

$pdf->Ln(20);
$pdf->SetFont("Arial","",12);
$pdf->Cell(0,10,"Issued on $date",0,1,'C');

$pdf->SetFont("Arial","",10);
$pdf->Cell(0,10,"Certificate Code: $code",0,1,'C');

// Output pdf
$pdf->Output("I", "certificate_$code.pdf");