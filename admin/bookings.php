<?php
include '../config/database.php';
require_once '../controllers/AdminController.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_lapangan') {
    die("Akses Ilegal!");
}

try {
    $controller = new AdminController($pdo);
    $dashboardData = $controller->getDashboardData($_SESSION['user_id']);
    
    if (isset($dashboardData['error'])) {
        die("Anda belum memiliki properti lapangan olahraga yang terdaftar atau belum di-approve.");
    }
    $venue_id = $dashboardData['venue_id'];

    if ($controller->handleBookingCancellation($_GET, $venue_id)) {
        header("Location: bookings.php");
        exit;
    }

    $bookings = $controller->getBookingsList($venue_id);

} catch (Exception $e) {
    die("Gagal memproses data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Manajemen Pesanan - ArenaGO Mitra</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
    <div class="admin-container">
        <h2 class="admin-title">Daftar Seluruh Reservasi Sesi Lapangan</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kategori Unit</th>
                    <th>Nama Pelanggan</th>
                    <th>Tanggal Main</th>
                    <th>Jam Sesi</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi Pengosongan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $b): ?>
                <tr>
                    <td>#<?php echo $b['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($b['court_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($b['payment_type'] === 'online' ? $b['online_name'] : $b['customer_name_offline']); ?></td>
                    <td><?php echo $b['booking_date']; ?></td>
                    <td><?php echo substr($b['start_time'],0,5) . " - " . substr($b['end_time'],0,5); ?></td>
                    <td><span class="admin-badge" style="background:#EAEAEA;"><?php echo strtoupper($b['payment_type']); ?></span></td>
                    <td>
                        <span class="admin-badge" style="background:<?php echo $b['status']=='success'?'#E6F4EA;color:#137333;':'#FCE8E6;color:#C5221F;'; ?>">
                            <?php echo strtoupper($b['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($b['status'] === 'success'): ?>
                            <a href="bookings.php?cancel_id=<?php echo $b['id']; ?>" class="admin-btn" style="background:#DC3545; padding:5px 10px; font-size:12px;" onclick="return confirm('Batalkan sesi jadwal ini?')">Batalkan</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
</body>
</html>