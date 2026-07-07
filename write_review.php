<?php
include 'config/database.php';
require_once 'controllers/FrontController.php';

include 'includes/header.php';

$venue_id = isset($_GET['venue_id']) ? intval($_GET['venue_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['venue_id'] = $venue_id;
    $_POST['comment'] = $_POST['review_text']; // Mapping field name
    try {
        $controller = new FrontController($pdo);
        $result = $controller->handleReview($_POST, $_SESSION['user_id']);
        
        if (isset($result['success'])) {
            echo "<script>alert('" . addslashes($result['success']) . "'); window.location.href='my_bookings.php';</script>";
            exit;
        } else {
            echo "<script>alert('" . addslashes($result['error']) . "');</script>";
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Tulis Ulasan - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/home.css">
    <style>
        .container { padding: 50px 8%; max-width: 600px; margin: 0 auto; font-family: 'Inter', sans-serif; }
        .card { background: white; border: 1px solid #EAEAEA; padding: 30px; border-radius: 12px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        select, textarea { width: 100%; padding: 12px; border: 1px solid #CCC; border-radius: 8px; box-sizing: border-box; font-family: 'Inter'; }
        .btn-submit { background: #004AC6; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 style="font-family:'Poppins'; margin-top:0;">Tulis Ulasan Pengalaman Anda</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Beri Nilai (Rating)</label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                        <option value="3">⭐⭐⭐ (3 - Cukup Oke)</option>
                        <option value="2">⭐⭐ (2 - Kurang Puas)</option>
                        <option value="1">⭐ (1 - Buruk)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi Ulasan</label>
                    <textarea name="review_text" rows="5" placeholder="Ceritakan bagaimana kondisi lapangan dan pelayanan di lokasi..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Kirim Ulasan</button>
            </form>
        </div>
    </div>
</body>
</html>