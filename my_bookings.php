<?php
session_start();
include 'config/database.php';
require_once 'controllers/FrontController.php';

// Memastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

try {
    $controller = new FrontController($pdo);
    
    // Query untuk mengambil riwayat pemesanan beserta nama lapangan dan venue
    $raw_bookings = $controller->getUserBookings($_SESSION['user_id']);

    // Menggabungkan (grouping) jam yang sama pada pemesanan sekali checkout
    $grouped_bookings = [];
    foreach ($raw_bookings as $row) {
        $key = $row['court_id'] . '_' . $row['booking_date'] . '_' . $row['status'];
        if (!isset($grouped_bookings[$key])) {
            $grouped_bookings[$key] = $row;
            $grouped_bookings[$key]['time_slots'] = [substr($row['start_time'], 0, 5)];
            $grouped_bookings[$key]['total_price_sum'] = $row['total_price'];
        } else {
            $grouped_bookings[$key]['time_slots'][] = substr($row['start_time'], 0, 5);
            $grouped_bookings[$key]['total_price_sum'] += $row['total_price'];
        }
    }
    // Re-index array
    $result = array_values($grouped_bookings);
} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/home.css">
    <style>
        body { background-color: #F8F9FA; font-family: 'Inter', sans-serif; }
        .container-box { padding: 50px 8%; max-width: 1100px; margin: 0 auto; }
        
        .page-header { margin-bottom: 30px; border-bottom: 2px solid #E2E8F0; padding-bottom: 15px; }
        .page-title { font-family: 'Poppins', sans-serif; color: #1A202C; margin: 0; font-size: 28px; }
        .page-subtitle { color: #718096; font-size: 15px; margin-top: 5px; }
        
        .booking-card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; padding: 25px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: stretch; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.2s; }
        .booking-card:hover { border-color: #CBD5E0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transform: translateY(-2px); }
        
        .info-section { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .venue-name { margin: 0 0 8px 0; font-family: 'Poppins', sans-serif; font-size: 20px; color: #2B3674; }
        .court-location { margin: 0 0 12px 0; color: #4A5568; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .court-badge { background: #EDF2F7; color: #2D3748; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }
        
        .time-box { display: inline-flex; align-items: center; gap: 10px; background: #F4F7FE; padding: 10px 15px; border-radius: 8px; color: #4318FF; font-weight: 600; font-size: 14px; }
        
        .action-section { text-align: right; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; border-left: 1px dashed #E2E8F0; padding-left: 25px; min-width: 200px; }
        
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-success { background: #E6F4EA; color: #137333; }
        .status-pending { background: #FEF08A; color: #854D0E; }
        
        .price-total { color: #05CD99; font-size: 22px; font-weight: 700; margin-top: 15px; }
        
        .btn-rev { background: #4318FF; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; transition: 0.3s; margin-top: 15px; text-align: center; width: 100%; box-shadow: 0 4px 10px rgba(67, 24, 255, 0.2); }
        .btn-rev:hover { background: #3311DB; }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; border: 1px dashed #CBD5E0; }
        .empty-state h3 { color: #4A5568; margin-bottom: 10px; }
        .empty-state p { color: #718096; margin-bottom: 20px; }
        .btn-explore { background: #004AC6; color: white; padding: 10px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-box">
        <div class="page-header">
            <h2 class="page-title">Riwayat Transaksi Pemesanan</h2>
            <p class="page-subtitle">Pantau seluruh jadwal lapangan yang telah Anda pesan dan tulis ulasan pengalaman bermain Anda.</p>
        </div>

        <?php if (count($result) > 0): ?>
            <?php foreach ($result as $row): ?>
                <div class="booking-card">
                    <div class="info-section">
                        <h3 class="venue-name"><?= htmlspecialchars($row['venue_name']) ?></h3>
                        <p class="court-location">
                            📍 <?= htmlspecialchars($row['location']) ?> 
                            <span class="court-badge"><?= htmlspecialchars($row['court_name']) ?></span>
                        </p>
                        <div class="time-box">
                            📅 <?= date('d M Y', strtotime($row['booking_date'])) ?> 
                            <span style="color:#A3AED0;">|</span> 
                            ⏱️ Jam: <?= implode(', ', $row['time_slots']) ?>
                        </div>
                    </div>
                    
                    <div class="action-section">
                        <?php if ($row['status'] === 'success'): ?>
                            <span class="status-badge status-success">Berhasil (Lunas)</span>
                        <?php else: ?>
                            <span class="status-badge status-pending"><?= strtoupper($row['status']) ?></span>
                        <?php endif; ?>
                        
                        <!-- Menambahkan fee layanan Rp 2.000 seperti di Checkout -->
                        <div class="price-total">Rp <?= number_format($row['total_price_sum'] + 2000, 0, ',', '.') ?></div>
                        
                        <a href="write_review.php?venue_id=<?= $row['venue_id'] ?>" class="btn-rev">Tulis Ulasan Permainan</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Belum Ada Riwayat Pemesanan</h3>
                <p>Anda belum pernah melakukan pemesanan lapangan bulutangkis di ArenaGO.</p>
                <a href="search.php" class="btn-explore">Cari Lapangan Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>