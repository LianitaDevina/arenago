<?php
session_start();
include '../config/database.php';
require_once '../controllers/SuperadminController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("
        <div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
            <h2 style='color: #E53E3E;'>Akses Terlarang!</h2>
            <p style='color: #4A5568;'>Halaman ini adalah Pusat Kendali khusus untuk Superadmin.</p><br>
            <a href='../auth/logout.php' style='background: #004AC6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Keluar Sistem</a>
        </div>
    ");
}

try {
    $controller = new SuperadminController($pdo);
    
    // Mengambil statistik
    $stats = $controller->getDashboardStats();
    
    $revenue = $stats['revenue'];
    $count_users = $stats['count_users'];
    $count_venues = $stats['count_venues'];
    $pending_venues = $stats['pending_venues'];

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Superadmin Dashboard - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background:#F8F9FA; margin:0; padding:30px; }
        .grid-stats { display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom:40px; }
        .card-stat { background:white; padding:20px; border:1px solid #E3E3E3; border-radius:10px; text-align:center; }
        .box-table { background:white; padding:25px; border-radius:12px; border:1px solid #E3E3E3; }
        table { width:100%; border-collapse:collapse; }
        table th, table td { padding:12px; border-bottom:1px solid #EAEAEA; text-align:left; }
        table th { background:#F4F8FF; color:#004AC6; }
        .btn-approve { background:#28A745; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; font-size:12px; font-weight:600; }
        .btn-reject { background:#DC3545; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; font-size:12px; font-weight:600; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h1 style="margin:0; font-family:'Poppins'; color:#004AC6;">Halaman superadmin</h1>
            <p style="margin:5px 0 0 0; color:#666;">Validasi berkas kemitraan fisik dan pantau statistik makro aplikasi.</p>
        </div>
        <div>
            <a href="users.php" style="background:#00BCD4; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:600; margin-right:10px;">Kelola User</a>
            <a href="venues.php" style="background:#28A745; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:600; margin-right:10px;">Kelola Data Venue</a>
            <a href="../auth/logout.php" style="background:#666; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:600;">Keluar Panel</a>
        </div>
    </div>

    <?php if(isset($_SESSION['flash_msg'])): ?>
        <div style="background:#D4EDDA; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:500; border:1px solid #C3E6CB;">
            <?php 
                echo htmlspecialchars($_SESSION['flash_msg']); 
                unset($_SESSION['flash_msg']);
            ?>
        </div>
    <?php endif; ?>

    <div class="grid-stats">
        <div class="card-stat"><h3>Omzet Transaksi Aplikasi</h3><p style="font-size:24px; font-weight:700; color:#28A745;">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p></div>
        <div class="card-stat"><h3>Total Keanggotaan User</h3><p style="font-size:24px; font-weight:700; color:#004AC6;"><?php echo $count_users; ?> Akun</p></div>
        <div class="card-stat"><h3>Mitra Lapangan Aktif</h3><p style="font-size:24px; font-weight:700; color:#333;"><?php echo $count_venues; ?> Lokasi</p></div>
    </div>

    <div class="box-table">
        <h2 style="font-family:'Poppins'; color:#333; margin-top:0;">Daftar Pengajuan Berkas Kemitraan Lapangan</h2>
        <table>
            <thead>
                <tr>
                    <th>Pemilik</th>
                    <th>Nama Tempat (Venue)</th>
                    <th>Lokasi Operasional</th>
                    <th>Kontak Lapangan</th>
                    <th>Aksi Keputusan</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($pending_venues) > 0): ?>
                    <?php foreach($pending_venues as $pv): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($pv['owner_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($pv['name']); ?></td>
                            <td><?php echo htmlspecialchars($pv['location']); ?></td>
                            <td>📞 <?php echo htmlspecialchars($pv['phone'] ?? '-'); ?></td>
                            <td>
                                <form action="validate.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $pv['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve" onclick="return confirm('Setujui lokasi lapangan ini agar tayang online?')">Setujui</button>
                                </form>
                                <form action="validate.php" method="POST" style="display:inline-block; margin-left:5px;">
                                    <input type="hidden" name="id" value="<?php echo $pv['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject" onclick="return confirm('Tolak pendaftaran berkas tempat olahraga ini?')">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#777; font-style:italic;">Saat ini tidak ada pengajuan pendaftaran mitra baru yang tertunda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>