<?php
session_start();
include '../config/database.php';
require_once '../controllers/AdminController.php';

// Cek hak akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_lapangan') {
    die("Akses ilegal.");
}

$msg = "";
$user_id = $_SESSION['user_id'];

try {
    $controller = new AdminController($pdo);

    // 1. Mengambil ID Venue milik admin yang sedang login
    $dashboardData = $controller->getDashboardData($user_id);
    if (isset($dashboardData['error'])) {
        die("Anda belum memiliki properti lapangan olahraga yang terdaftar atau belum di-approve.");
    }
    
    $venue_id = $dashboardData['venue_id'];
    $editCourt = null;
    
    // 2 & 4. Memproses Aksi (Hapus/Tambah/Edit)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['action'])) {
            $_POST['action'] = isset($_POST['id']) ? 'edit' : 'add';
        }
    }
    
    if ($controller->handleCourtAction($_GET, $_POST, $_FILES, $venue_id)) {
        if (isset($_SESSION['flash_msg'])) {
            $msg = "<p style='color:green; font-weight:700;'>" . htmlspecialchars($_SESSION['flash_msg']) . "</p>";
            unset($_SESSION['flash_msg']);
        }
        if (isset($_SESSION['flash_msg_error'])) {
            $msg = "<p style='color:red; font-weight:700;'>" . htmlspecialchars($_SESSION['flash_msg_error']) . "</p>";
            unset($_SESSION['flash_msg_error']);
        }
    }

    // 3. Mengecek apakah tombol edit ditekan (GET parameter 'edit')
    if (isset($_GET['edit'])) {
        $court_id = intval($_GET['edit']);
        $editCourt = $controller->getCourtById($court_id);
    }

    // 5. Mengambil semua data lapangan aktif untuk ditampilkan di tabel/list bawah
    $courts = $controller->getCourtsList($venue_id);

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Kelola Unit Lapangan - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
        <div class="admin-container admin-container-narrow">
        <h2 class="admin-title"><?php echo $editCourt ? 'Edit Unit Lapangan' : 'Tambah Kategori Unit Lapangan'; ?></h2>
        
        <?php echo $msg; ?>
        
        <form method="POST" action="courts.php" enctype="multipart/form-data">
            <?php if ($editCourt): ?>
                <input type="hidden" name="id" value="<?php echo $editCourt['id']; ?>">
            <?php endif; ?>
            <label class="admin-label">Nama/Nomor Sekat Lapangan</label>
            <input type="text" name="court_name" class="admin-input" placeholder="Contoh: Lapangan Utama Premium, Lapangan 2" value="<?php echo $editCourt ? htmlspecialchars($editCourt['court_name']) : ''; ?>" required>
            
            <label class="admin-label">Kategori Lapangan</label>
            <select name="category" class="admin-select" required>
                <option value="Lantai Vynil" <?php echo ($editCourt && isset($editCourt['category']) && $editCourt['category'] == 'Lantai Vynil') ? 'selected' : ''; ?>>Lantai Vynil</option>
                <option value="Lantai Kayu" <?php echo ($editCourt && isset($editCourt['category']) && $editCourt['category'] == 'Lantai Kayu') ? 'selected' : ''; ?>>Lantai Kayu</option>
            </select>
            
            <label class="admin-label">Tarif Biaya Sewa per Jam (Rp)</label>
            <input type="number" name="price_per_hour" class="admin-input" placeholder="Contoh: 70000" value="<?php echo $editCourt ? htmlspecialchars($editCourt['price_per_hour']) : ''; ?>" required>
            
            <label class="admin-label">Foto Lapangan <?php echo $editCourt ? '(Biarkan kosong jika tidak ingin mengubah)' : '(Opsional)'; ?></label>
            <input type="file" name="image" class="admin-input" accept="image/*" style="padding: 6px;">
            
            <button type="submit" class="admin-btn admin-btn-block"><?php echo $editCourt ? 'Update Data Lapangan' : 'Simpan Data Lapangan'; ?></button>
            <?php if ($editCourt): ?>
                <a href="courts.php" style="display:block; text-align:center; margin-top:10px; color:#555; text-decoration:none; font-size:13px; font-weight:600;">Batal Edit</a>
            <?php endif; ?>
        </form>

        <h3 class="admin-title" style="margin-top:35px; border-top:1px solid #EAEAEA; padding-top:20px;">Daftar Lapangan Aktif</h3>
        <ul style="padding-left:0; line-height:1.8; list-style:none;">
            <?php foreach($courts as $c): ?>
                <li style="display:flex; align-items:center; margin-bottom:15px; border-bottom:1px solid #EEE; padding-bottom:10px;">
                    <img src="../assets/images/<?php echo htmlspecialchars($c['image'] ?? 'default_court.jpg'); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px; margin-right:15px;" alt="foto">
                    <div style="flex:1;">
                        <strong style="display:block; color:#333; font-size:15px;">
                            <?php echo htmlspecialchars($c['court_name']); ?> 
                            <?php if(!empty($c['category'])): ?>
                                <span class="admin-badge" style="background:#E2E8F0; color:#4A5568; font-weight:600; margin-left:5px; padding:2px 6px; font-size:11px;"><?php echo htmlspecialchars($c['category']); ?></span>
                            <?php endif; ?>
                        </strong>
                        <span style="color:#004AC6; font-weight:600; font-size:14px;">Rp <?php echo number_format($c['price_per_hour'], 0, ',', '.'); ?>/jam</span>
                    </div>
                    <div>
                        <a href="courts.php?edit=<?php echo $c['id']; ?>" style="color:#fff; background:#D69E2E; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:12px; font-weight:600; margin-right:5px;">Edit</a>
                        <a href="courts.php?delete=<?php echo $c['id']; ?>" onclick="return confirm('Yakin ingin menghapus lapangan ini?');" style="color:#fff; background:#E53E3E; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:12px; font-weight:600;">Hapus</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    </div>
</body>
</html>