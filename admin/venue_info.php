<?php
session_start();
require_once '../config/database.php';
require_once '../controllers/AdminController.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_lapangan') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

try {
    $controller = new AdminController($pdo);
    
    // Using the getDashboardData to get venue
    $dashboardData = $controller->getDashboardData($user_id);
    $venue = isset($dashboardData['venue']) ? $dashboardData['venue'] : null;
    $venue_id = $venue ? $venue['id'] : null;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $message = $controller->handleVenueUpdate($_POST, $_FILES, $user_id, $venue_id);
        
        // Refresh venue data
        $dashboardData = $controller->getDashboardData($user_id);
        $venue = isset($dashboardData['venue']) ? $dashboardData['venue'] : null;
    }
} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Info Gedung - ArenaGO</title>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
        <div class="admin-container admin-container-narrow">
            <h2 class="admin-title">Informasi Gedung (Venue)</h2>
            <?= $message ?>
            
            <form action="" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Nama Gedung Olahraga</label>
                    <input type="text" name="name" class="admin-input" value="<?= $venue ? htmlspecialchars($venue['name']) : '' ?>" required placeholder="Contoh: GOR Arena Merdeka">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Alamat Lengkap</label>
                    <textarea name="location" class="admin-textarea" rows="3" required placeholder="Jalan, RT/RW, Kota..."><?= $venue ? htmlspecialchars($venue['location']) : '' ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Fasilitas / Deskripsi Singkat</label>
                    <textarea name="description" class="admin-textarea" rows="3" placeholder="Ada kantin, toilet bersih, parkir luas..."><?= $venue ? htmlspecialchars($venue['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label class="admin-label">Fasilitas Ekstra Tersedia</label>
                    <?php 
                        $selected_fac = $venue && isset($venue['facilities']) ? explode(', ', $venue['facilities']) : [];
                        $available_fac = ['Kamar Mandi', 'Sewa Raket', 'Parkir Luas', 'Papan Skor'];
                        foreach($available_fac as $fac) {
                            $checked = in_array($fac, $selected_fac) ? 'checked' : '';
                            echo "<label style='display:inline-block; margin-right:15px; font-weight:normal; font-size:14px; color:#4A5568;'><input type='checkbox' name='facilities[]' value='$fac' $checked style='margin-right:5px;'> $fac</label>";
                        }
                    ?>
                </div>
                
                <button type="submit" class="admin-btn"><?= $venue ? 'Simpan Perubahan' : 'Daftarkan Gedung' ?></button>
            </form>
        </div>
    </div>

</body>
</html>