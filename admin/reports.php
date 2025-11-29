<?php
// filename: admin/reports.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Ambil Parameter Filter dari URL
$start_date = $_GET['start'] ?? date('Y-m-01'); // Default: Awal bulan ini
$end_date   = $_GET['end']   ?? date('Y-m-d'); // Default: Hari ini
$period     = $_GET['period'] ?? 'daily';      // Default: Harian

// 2. Tentukan Logika Query Grouping (Harian / Bulanan)
if ($period == 'monthly') {
    // Format MySQL: YYYY-MM (Misal: 2023-10)
    $group_by_sql = "DATE_FORMAT(paid_at, '%Y-%m')";
    $label_format = "F Y"; // Format Tampilan: October 2023
    $chart_title  = "Grafik Pendapatan Bulanan";
} else {
    // Format MySQL: YYYY-MM-DD (Misal: 2023-10-25)
    $group_by_sql = "DATE(paid_at)";
    $label_format = "d M Y"; // Format Tampilan: 25 Oct 2023
    $chart_title  = "Grafik Pendapatan Harian";
}

// 3. Eksekusi Query ke Database
// Pastikan hanya mengambil data yang statusnya 'paid' (sudah lunas)
$sql = "SELECT 
            $group_by_sql as tgl, 
            SUM(amount) as total_pendapatan,
            COUNT(*) as jumlah_transaksi
        FROM payments 
        WHERE status = 'paid' 
        AND DATE(paid_at) BETWEEN :start AND :end
        GROUP BY $group_by_sql
        ORDER BY tgl ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['start' => $start_date, 'end' => $end_date]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reports = [];
    // Opsi: Tampilkan error jika perlu debugging
    // echo "Error: " . $e->getMessage();
}

// 4. Siapkan Data untuk Chart.js
$chart_labels = [];
$chart_data   = [];
$total_income = 0;
$total_trx    = 0;

foreach ($reports as $row) {
    // Konversi tanggal SQL ke format yang mudah dibaca manusia
    // Jika bulanan, tambahkan '-01' agar strtotime bisa membaca sebagai tanggal valid
    $date_string = ($period == 'monthly') ? $row['tgl'] . '-01' : $row['tgl'];
    
    $chart_labels[] = date($label_format, strtotime($date_string));
    $chart_data[]   = $row['total_pendapatan'];
    
    // Hitung Total Keseluruhan untuk Summary Card
    $total_income  += $row['total_pendapatan'];
    $total_trx     += $row['jumlah_transaksi'];
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

    /* LAYOUT UTAMA */
    .main-content {
        margin-left: 250px; /* Lebar Sidebar */
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        padding-top: 80px; /* Jarak Atas Aman */
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 50px;
    }

    /* HEADER HALAMAN */
    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Playfair Display', serif; 
        font-size: 2rem; color: #2c3e50; margin: 0; 
    }

    /* KARTU UMUM */
    .card-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
        margin-bottom: 30px;
    }

    /* FILTER SECTION */
    .filter-row {
        display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;
    }
    .input-group { display: flex; flex-direction: column; }
    .input-group label { font-size: 13px; font-weight: bold; margin-bottom: 8px; color: #555; }
    
    .form-control { 
        padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; 
        min-width: 150px;
    }
    .btn-dark { 
        background: #2c3e50; color: white; border: none; padding: 11px 20px; 
        border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s;
    }
    .btn-dark:hover { background: #c49b63; }

    /* SUMMARY CARDS */
    .summary-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;
    }
    .card-stat {
        background: white; padding: 30px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        border-left: 5px solid #27ae60; /* Hijau */
        display: flex; flex-direction: column; justify-content: center;
    }
    .card-stat.blue { border-left-color: #2980b9; } /* Biru */

    .card-stat h3 { margin: 0 0 10px 0; font-size: 14px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; }
    .card-stat .money { font-size: 28px; font-weight: 800; color: #2c3e50; margin: 0; }
    .card-stat .count { font-size: 28px; font-weight: 800; color: #2c3e50; margin: 0; }
    .card-stat small { color: #95a5a6; margin-top: 5px; font-size: 13px; }

    /* TABEL */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: bold; color: #444; border-bottom: 2px solid #ddd; }
    td { padding: 15px; border-bottom: 1px solid #eee; color: #333; vertical-align: middle; }

    /* RESPONSIF */
    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 80px 20px; }
        .summary-grid { grid-template-columns: 1fr; }
        .filter-row { flex-direction: column; align-items: stretch; }
        .btn-dark { width: 100%; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Laporan Keuangan</h1>
    </div>

    <div class="card-box">
        <form method="GET" class="filter-row">
            <div class="input-group">
                <label>Dari Tanggal</label>
                <input type="date" name="start" value="<?=$start_date?>" class="form-control">
            </div>
            <div class="input-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="end" value="<?=$end_date?>" class="form-control">
            </div>
            <div class="input-group">
                <label>Periode</label>
                <select name="period" class="form-control">
                    <option value="daily" <?= $period == 'daily' ? 'selected' : '' ?>>Harian</option>
                    <option value="monthly" <?= $period == 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                </select>
            </div>
            <div class="input-group">
                <label>&nbsp;</label> 
                <button type="submit" class="btn-dark">Tampilkan Laporan</button>
            </div>
        </form>
    </div>

    <div class="summary-grid">
        <div class="card-stat">
            <h3>Total Pendapatan</h3>
            <p class="money">Rp <?= number_format($total_income, 0, ',', '.') ?></p>
            <small>Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></small>
        </div>
        
        <div class="card-stat blue">
            <h3>Total Transaksi Berhasil</h3>
            <p class="count"><?= $total_trx ?> <span style="font-size:16px; font-weight:normal; color:#777;">Transaksi</span></p>
            <small>Status: Paid (Lunas)</small>
        </div>
    </div>

    <div class="card-box">
        <h3 style="margin-top:0; color:#2c3e50; margin-bottom:20px;"><?= $chart_title ?></h3>
        
        <?php if(count($chart_data) > 0): ?>
            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="incomeChart"></canvas>
            </div>
        <?php else: ?>
            <p style="text-align:center; color:#999; padding: 30px; border: 2px dashed #eee; border-radius: 8px;">
                <i class="fa-regular fa-face-frown" style="font-size: 2rem; margin-bottom: 10px; display:block;"></i>
                Belum ada data transaksi yang <strong>Lunas (Paid)</strong> pada periode ini.
            </p>
        <?php endif; ?>
    </div>

    <div class="card-box">
        <h3 style="margin-top:0; color:#2c3e50; margin-bottom:20px;">Rincian Data</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal / Periode</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Pendapatan (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($reports) > 0): ?>
                    <?php foreach($reports as $r): ?>
                    <tr>
                        <td>
                            <?php 
                                $d_str = ($period == 'monthly') ? $r['tgl'] . '-01' : $r['tgl'];
                                echo date($label_format, strtotime($d_str));
                            ?>
                        </td>
                        <td>
                            <span style="background:#e3f2fd; color:#1565c0; padding:4px 10px; border-radius:10px; font-weight:bold; font-size:12px;">
                                <?= $r['jumlah_transaksi'] ?> Trx
                            </span>
                        </td>
                        <td style="font-weight:bold; color:#27ae60;">
                            Rp <?= number_format($r['total_pendapatan'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" align="center" style="padding:30px; color:#999;">Tidak ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Ambil data dari PHP
    const labels = <?= json_encode($chart_labels) ?>;
    const dataIncome = <?= json_encode($chart_data) ?>;
    const periodType = "<?= $period ?>"; 

    const chartCanvas = document.getElementById('incomeChart');
    
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar', // Jenis grafik: Bar (Batang)
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataIncome,
                    backgroundColor: periodType === 'monthly' ? 'rgba(41, 128, 185, 0.7)' : 'rgba(39, 174, 96, 0.7)',
                    borderColor: periodType === 'monthly' ? 'rgba(41, 128, 185, 1)' : 'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0f0f0' },
                        ticks: {
                            // Format angka jadi Rupiah di sumbu Y
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }, // Sembunyikan legenda karena cuma 1 data
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
</script>