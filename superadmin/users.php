<?php
session_start();
include '../config/database.php';
require_once '../controllers/SuperadminController.php';

// Cek akses Superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Akses ditolak.");
}

try {
    $controller = new SuperadminController($pdo);

    // Mengecek apakah tombol hapus user ditekan
    if (isset($_GET['delete'])) {
        $id_to_delete = intval($_GET['delete']);
        
        // Hapus user dari database
        $controller->handleDeleteUser($id_to_delete);
        header("Location: users.php");
        exit;
    }

    // Mengambil semua data pengguna dari tabel users
    $all_users = $controller->getUsersList();

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Master Users - Superadmin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background:#F8F9FA; padding:30px; margin:0; }
        table { width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden; border:1px solid #EAEAEA; }
        table th, table td { padding:12px; border-bottom:1px solid #EAEAEA; text-align:left; }
        table th { background:#F4F8FF; color:#004AC6; }
    </style>
</head>
<body>
    <a href="dashboard.php" style="color:#004AC6; text-decoration:none; font-weight:600;"><- Kembali ke Utama</a>
    <h2>Manajemen Seluruh Akun Pengguna Terdaftar</h2>
    <table>
        <thead>
            <tr>
                <th>ID Akun</th>
                <th>Nama Lengkap</th>
                <th>Alamat Email</th>
                <th>No. Telepon Kontak</th>
                <th>Hak Akses Peran</th>
                <th>Tanggal Bergabung</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($all_users as $u): ?>
            <tr>
                <td>#<?php echo htmlspecialchars($u['id']); ?></td>
                <td><strong><?php echo htmlspecialchars($u['name'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($u['email'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                <td>
                    <?php 
                        $rawRole = $u['role'] ?? '';
                        $displayRole = !empty($rawRole) ? strtoupper($rawRole) : 'CUSTOMER';
                        $roleColor = $rawRole == 'superadmin' ? 'red' : ($rawRole == 'admin_lapangan' ? 'orange' : 'green');
                    ?>
                    <span style="font-weight:700; color:<?php echo $roleColor; ?>"><?php echo htmlspecialchars($displayRole); ?></span>
                </td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>