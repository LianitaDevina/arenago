<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

// Mengecek hak akses, hanya untuk Admin Lapangan (Mitra)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_lapangan') {
    die("Akses Ditolak! Halaman ini dikunci khusus akun Admin Lapangan.");
}

$user_id = $_SESSION['user_id'];
$pesan = "";

try {
    $controller = new AdminController($pdo);
    
    $dashboardData = $controller->getDashboardData($user_id);
    
    if (isset($dashboardData['error'])) {
        if ($dashboardData['error'] === 'no_venue') {
            die("Anda belum memiliki properti lapangan olahraga yang terdaftar.");
        } elseif ($dashboardData['error'] === 'not_approved') {
            die("
                <div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
                    <h2 style='color: #E53E3E;'>Akun Toko/Lapangan Anda Belum Aktif!</h2>
                    <p style='color: #4A5568;'>Pendaftaran tempat olahraga Anda masih dalam antrean peninjauan oleh tim Superadmin ArenaGO.<br>Silakan tunggu hingga status divalidasi berkas keasliannya.</p><br>
                    <a href='../auth/logout.php' style='background: #004AC6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Keluar Sistem</a>
                </div>
            ");
        }
    }

    $venue_id = $dashboardData['venue_id'];
    $venue_name = $dashboardData['venue_name'];
    $result_courts = $dashboardData['result_courts'];
    $result_bookings = $dashboardData['result_bookings'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pesan = $controller->handleOfflineBooking($_POST);
        // Refresh data setelah booking
        $result_bookings = $controller->getDashboardData($user_id)['result_bookings'];
    }

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Panel Mitra Lapangan - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
        <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 class="admin-title" style="margin:0; color:#174AE4;">Panel Operasional: <?= htmlspecialchars($venue_name) ?></h1>
            <p style="margin:4px 0 0 0; color:#666;">Kelola kuota penambahan unit sekat lapangan dan input pencatatan kasir main offline.</p>
        </div>
        <a href="../auth/logout.php" style="background:#DC3545; color:white; padding:8px 16px; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Keluar</a>
    </div>

    <?= $pesan ?>

    <div style="display: flex; gap: 25px; margin-top:30px;">
        <div class="admin-container" style="flex: 1; height: fit-content; padding: 25px;">
            <h3 class="admin-title" style="border-bottom:2px solid #F4F8FF; padding-bottom:10px; margin-bottom: 20px;">Pencatatan Sewa Offline</h3>
            <form method="POST">
                <input type="hidden" name="action_offline_booking" value="1">
                
                <label class="admin-label">Pilih Unit Lapangan</label>
                <select name="court_id" class="admin-select" required>
                    <?php foreach($result_courts as $mc): ?>
                        <option value="<?php echo $mc['id']; ?>"><?php echo htmlspecialchars($mc['court_name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="admin-label">Nama Tamu Lapangan</label>
                <input type="text" name="customer_name" class="admin-input" placeholder="Contoh: Budi Walk-In" required>

                <label class="admin-label">Nomor Telepon Tamu</label>
                <input type="text" name="customer_phone" class="admin-input" placeholder="Contoh: 08999888777" required>

                <label class="admin-label">Tanggal Bermain</label>
                <input type="date" name="booking_date" class="admin-input" value="<?php echo date('Y-m-d'); ?>" required>

                <label class="admin-label">Jam Mulai Sesi (Durasi 1 Jam)</label>
                <select name="start_time" class="admin-select" required>
                    <option value="08:00:00">08:00 - 09:00</option>
                    <option value="09:00:00">09:00 - 10:00</option>
                    <option value="10:00:00">10:00 - 11:00</option>
                    <option value="15:00:00">15:00 - 16:00</option>
                    <option value="19:00:00">19:00 - 20:00</option>
                </select>

                <label class="admin-label">Biaya Sewa Cash (Rp)</label>
                <input type="number" name="price_snap" class="admin-input" placeholder="Contoh: 75000" required>

                <button type="submit" class="admin-btn admin-btn-block">Kunci Jadwal Offline</button>
            </form>

        </div>

        <div class="admin-container" style="flex: 2; padding: 25px;">
            <h3 class="admin-title" style="border-bottom:2px solid #F4F8FF; padding-bottom:10px; margin-bottom: 20px;">Arus Sesi Log Pesanan Terjadwal</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tipe Unit</th>
                        <th>Nama Penyewa</th>
                        <th>Kontak Telepon</th>
                        <th>Sesi Jadwal</th>
                        <th>Metode</th>
                        <th>Jumlah Terima Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($result_bookings) > 0): ?>
                        <?php foreach($result_bookings as $ab): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ab['court_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ab['payment_type'] === 'online' ? $ab['cust_online_name'] : $ab['customer_name_offline']); ?></td>
                                <td>📞 <?php echo htmlspecialchars($ab['payment_type'] === 'online' ? $ab['cust_online_phone'] : $ab['customer_phone_offline']); ?></td>
                                <td>📅 <?php echo date('d/m/Y', strtotime($ab['booking_date'])); ?><br><small style="color:#666; font-weight:600;"><?php echo substr($ab['start_time'],0,5) . " - " . substr($ab['end_time'],0,5); ?></small></td>
                                <td>
                                    <span class="admin-badge" style="background: <?php echo $ab['payment_type'] === 'online' ? '#E8F0FE; color:#1A73E8;' : '#E6F4EA; color:#137333;'; ?>">
                                        <?php echo strtoupper($ab['payment_type']); ?>
                                    </span>
                                </td>
                                <td style="font-weight:700; color:#28A745;">Rp <?php echo number_format($ab['total_price'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:#777; font-style:italic;">Belum ada catatan aktivitas pemesanan terjadwal untuk saat ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</body>
</html>