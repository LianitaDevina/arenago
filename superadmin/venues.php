<?php
session_start();
include '../config/database.php';
require_once '../controllers/SuperadminController.php';

// Mengecek hak akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Akses ditolak.");
}

$editVenue = null;

try {
    $controller = new SuperadminController($pdo);

    if ($controller->handleVenueAction($_GET, $_POST)) {
        header("Location: venues.php");
        exit;
    }

    // Mengecek apakah tombol edit ditekan
    if (isset($_GET['edit'])) {
        $venue_id_edit = intval($_GET['edit']);
        $editVenue = $controller->getVenueById($venue_id_edit);
    }

    // Mengambil semua data venue
    $all_venues = $controller->getVenuesList();

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Master Venue - Superadmin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background:#F8F9FA; padding:30px; margin:0; }
        .box-container { background:white; padding:25px; border-radius:12px; border:1px solid #E3E3E3; margin-bottom: 20px; }
        table { width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden; border:1px solid #EAEAEA; }
        table th, table td { padding:12px; border-bottom:1px solid #EAEAEA; text-align:left; vertical-align: middle; }
        table th { background:#F4F8FF; color:#004AC6; font-weight: 600; }
        .btn-edit { background:#D69E2E; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; font-size:12px; font-weight:600; display:inline-block; }
        .btn-delete { background:#E53E3E; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; font-size:12px; font-weight:600; display:inline-block; margin-left: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#505050; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        .btn-submit { background:#004AC6; color:white; font-weight:600; border:none; padding:12px; border-radius:6px; cursor:pointer; width:100%; margin-top:10px; }
        .badge { padding:4px 8px; font-size:11px; border-radius:4px; font-weight: 600; }
        .badge-approved { background: #D4EDDA; color: #155724; }
        .badge-pending { background: #FFF3CD; color: #856404; }
        .badge-rejected { background: #F8D7DA; color: #721C24; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <a href="dashboard.php" style="color:#004AC6; text-decoration:none; font-weight:600;"><- Kembali ke Utama</a>
    </div>

    <?php if(isset($_SESSION['flash_msg'])): ?>
        <div style="background:#D4EDDA; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:500; border:1px solid #C3E6CB;">
            <?php 
                echo htmlspecialchars($_SESSION['flash_msg']); 
                unset($_SESSION['flash_msg']);
            ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_msg_error'])): ?>
        <div style="background:#F8D7DA; color:#721C24; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:500; border:1px solid #F5C6CB;">
            <?php 
                echo htmlspecialchars($_SESSION['flash_msg_error']); 
                unset($_SESSION['flash_msg_error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($editVenue): ?>
    <div class="box-container" style="max-width: 600px;">
        <h3 style="font-family:'Poppins'; margin-top:0; color:#333;">Edit Data Tempat Olahraga (Venue)</h3>
        <form method="POST" action="venues.php">
            <input type="hidden" name="id" value="<?php echo $editVenue['id']; ?>">
            
            <div class="form-group">
                <label>Nama Tempat Olahraga (Venue)</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editVenue['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Alamat & Lokasi Lengkap</label>
                <textarea name="location" rows="3" required><?php echo htmlspecialchars($editVenue['location']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Fasilitas Ekstra Tersedia</label>
                <?php 
                    $selected_fac = $editVenue && isset($editVenue['facilities']) ? explode(', ', $editVenue['facilities']) : [];
                    $available_fac = ['Kamar Mandi', 'Sewa Raket', 'Parkir Luas', 'Papan Skor'];
                    foreach($available_fac as $fac) {
                        $checked = in_array($fac, $selected_fac) ? 'checked' : '';
                        echo "<label style='display:inline-block; margin-right:15px; font-weight:normal; font-size:14px; color:#4A5568;'><input type='checkbox' name='facilities[]' value='$fac' $checked style='margin-right:5px; width:auto;'> $fac</label>";
                    }
                ?>
            </div>

            <div class="form-group">
                <label>Deskripsi Tambahan</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($editVenue['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Status Sistem (Visibility)</label>
                <select name="status" required>
                    <option value="approved" <?php echo ($editVenue['status'] == 'approved') ? 'selected' : ''; ?>>Tayang Publik (Approved)</option>
                    <option value="pending" <?php echo ($editVenue['status'] == 'pending') ? 'selected' : ''; ?>>Menunggu Validasi (Pending)</option>
                    <option value="rejected" <?php echo ($editVenue['status'] == 'rejected') ? 'selected' : ''; ?>>Ditolak (Rejected/Banned)</option>
                </select>
            </div>
            
            <button type="submit" class="btn-submit">Simpan Perubahan Venue</button>
            <a href="venues.php" style="display:block; text-align:center; margin-top:10px; color:#555; text-decoration:none; font-size:13px; font-weight:600;">Batal Edit</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="box-container">
        <h2 style="font-family:'Poppins'; color:#333; margin-top:0;">Daftar Seluruh Tempat Olahraga (Read, Update, Delete)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Tempat Olahraga</th>
                    <th>Lokasi Cabang</th>
                    <th>Pemilik/Mitra</th>
                    <th>Status Visibility</th>
                    <th>Aksi Khusus</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($all_venues) > 0): ?>
                    <?php foreach($all_venues as $v): ?>
                    <tr>
                        <td style="color:#666;">#<?php echo $v['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($v['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($v['location']); ?></td>
                        <td><?php echo htmlspecialchars($v['owner_name']); ?></td>
                        <td>
                            <?php if($v['status'] == 'approved'): ?>
                                <span class="badge badge-approved">Approved</span>
                            <?php elseif($v['status'] == 'pending'): ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-rejected">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="venues.php?edit=<?php echo $v['id']; ?>" class="btn-edit">Edit</a>
                            <a href="venues.php?delete=<?php echo $v['id']; ?>" class="btn-delete" onclick="return confirm('Peringatan: Yakin ingin menghapus permanen venue ini beserta seluruh lapangannya?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#777; font-style:italic;">Belum ada data tempat olahraga yang terdaftar di sistem.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
