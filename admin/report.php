<?php
include '../config/database.php';
require_once '../controllers/AdminController.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_lapangan') {
    die("Akses Terlarang!");
}

try {
    $controller = new AdminController($pdo);
    $dashboardData = $controller->getDashboardData($_SESSION['user_id']);
    
    if (isset($dashboardData['error'])) {
        die("Anda belum memiliki properti lapangan olahraga yang terdaftar atau belum di-approve.");
    }
    $venue_id = $dashboardData['venue_id'];

    $stats = $controller->getRevenueStats($venue_id);
    $revenue_online = $stats['online'];
    $revenue_offline = $stats['offline'];

} catch (Exception $e) {
    die("Gagal memuat rekapitulasi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Keuangan Bisnis - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
    <h2 class="admin-title">Analisis Kas Rekapitulasi Lapangan</h2>
    
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-top:20px;">
        <div class="admin-container" style="text-align:center;">
            <h4 style="margin:0; color:#1A73E8;">Pendapatan Saldo Online Apps</h4>
            <p style="font-size:24px; font-weight:700; margin:10px 0 0 0; color:#1A73E8;">Rp <?php echo number_format($revenue_online, 0, ',', '.'); ?></p>
        </div>
        <div class="admin-container" style="text-align:center;">
            <h4 style="margin:0; color:#137333;">Pendapatan Tunai/Cash Offline</h4>
            <p style="font-size:24px; font-weight:700; margin:10px 0 0 0; color:#137333;">Rp <?php echo number_format($revenue_offline, 0, ',', '.'); ?></p>
        </div>
        <div class="admin-container" style="background:#004AC6; color:white; text-align:center;">
            <h4 style="margin:0; color:white;">Akumulasi Omzet Bersih</h4>
            <p style="font-size:24px; font-weight:700; margin:10px 0 0 0;">Rp <?php echo number_format($revenue_online + $revenue_offline, 0, ',', '.'); ?></p>
        </div>
    </div>
    </div>
</body>
</html>