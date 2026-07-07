<?php
include '../config/database.php'; 
require_once '../controllers/AuthController.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['register'] = true;
    $authController = new AuthController($pdo);
    $result = $authController->handleRegister($_POST);
    $error = $result['error'];
    $success = $result['success'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Daftar Akun ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #FAFAFA; display: flex; justify-content: center; padding: 40px 0; }
        .reg-box { background: white; border: 1px solid #EAEAEA; width: 500px; padding: 35px; border-radius: 12px; }
        .form-group { margin-bottom: 15px; }
        label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; color:#505050; }
        input, select, textarea { width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; box-sizing: border-box; }
        .btn-submit { background:#004AC6; color:white; font-weight:600; border:none; padding:12px; border-radius:6px; cursor:pointer; width:100%; margin-top:10px; }
        .partner-only { display: none; background: #F4F8FF; padding: 15px; border-radius: 8px; border: 1px dashed #004AC6; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="reg-box">
        <h2 style="font-family:'Poppins'; margin-top:0; color:#004AC6;">Pendaftaran Baru</h2>
        
        <?php if(!empty($error)): ?><div style="color:red; margin-bottom:15px; font-weight:600;"><?php echo $error; ?></div><?php endif; ?>
        <?php if(!empty($success)): ?><div style="color:green; margin-bottom:15px; font-weight:600;"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Nomor Telepon Pribadi</label><input type="text" name="phone" placeholder="Contoh: 08123456789" required></div>
            <div class="form-group"><label>Kata Sandi (Password)</label><input type="password" name="password" required></div>
            <div class="form-group">
                <label>Daftar Sebagai</label>
                <select name="role" id="role-select" onchange="togglePartnerForm()" required>
                    <option value="customer">Penyewa Lapangan (Customer)</option>
                    <option value="admin_lapangan">Pemilik Tempat Olahraga (Mitra)</option>
                </select>
            </div>

            <div id="partner-form" class="partner-only">
                <h3 style="font-family:'Poppins'; margin-top:0; font-size:15px; color:#004AC6;">Detail Data Tempat Olahraga</h3>
                <div class="form-group"><label>Nama Tempat Olahraga (Venue)</label><input type="text" name="venue_name" placeholder="Contoh: Smash Arena Denpasar"></div>
                <div class="form-group"><label>Alamat Lengkap Lokasi Lapangan</label><textarea name="venue_location" rows="3" placeholder="Nama jalan, kota, dan koordinat singkat..."></textarea></div>
            </div>

            <button type="submit" class="btn-submit">Selesaikan Pendaftaran</button>
            <p style="font-size: 13px; text-align: center; margin-top: 15px;">Sudah punya akun? <a href="login.php" style="color:#004AC6; font-weight:600; text-decoration:none;">Masuk disini</a></p>
        </form>
    </div>

    <script>
        function togglePartnerForm() {
            var role = document.getElementById('role-select').value;
            var partnerForm = document.getElementById('partner-form');
            if(role === 'admin_lapangan') {
                partnerForm.style.display = 'block';
            } else {
                partnerForm.style.display = 'none';
            }
        }
    </script>
</body>
</html>