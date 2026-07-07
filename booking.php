<?php
include 'config/database.php';
require_once 'controllers/FrontController.php';

include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda wajib login atau daftar akun terlebih dahulu sebelum memesan lapangan!'); window.location.href='auth/login.php';</script>";
    exit;
}

$court_id = isset($_GET['court_id']) ? intval($_GET['court_id']) : 0;
$booking_date = isset($_GET['date']) ? $_GET['date'] : '';
$time_slots_str = isset($_GET['time_slots']) ? trim($_GET['time_slots']) : '';

if ($court_id <= 0 || empty($booking_date) || empty($time_slots_str)) {
    die("<script>alert('Informasi jadwal tidak valid!'); window.location.href='search.php';</script>");
}

// Parse time slots
$time_slots = explode(',', $time_slots_str);
sort($time_slots); // Ensure chronological order

try {
    require_once 'models/Court.php';
    require_once 'models/User.php';

    $courtModel = new Court($pdo);
    $userModel = new User($pdo);
    
    $court = $courtModel->getCourtDetails($court_id);
    if (!$court) { die("Data lapangan tidak ditemukan."); }

    $user_info = $userModel->getUserById($_SESSION['user_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new FrontController($pdo);
        $result = $controller->handleBookingCheckout(
            $_POST, 
            $_SESSION['user_id'], 
            $court_id, 
            $booking_date, 
            $time_slots, 
            $court['price_per_hour']
        );
        
        if (!empty($result['error'])) {
            echo "<script>alert('" . addslashes($result['error']) . "');</script>";
        } else {
            echo "<script>alert('" . addslashes($result['success']) . "'); window.location.href='my_bookings.php';</script>";
            exit;
        }
    }
} catch (Exception $e) {
    die("Gagal memproses transaksi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Selesaikan Pesanan Anda - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/home.css?v=1.2">
    <link rel="stylesheet" href="assets/css/booking.css?v=1.2">
</head>
<body>
    <main class="booking-wrapper">
        <section class="booking-main">
            <h2>Selesaikan Pesanan Anda</h2>
            <p style="color: #666; margin-top: -20px; margin-bottom: 30px;">Pastikan detail pesanan dan data diri Anda sudah benar.</p>
            
            <div class="booking-section-card">
                <h3 style="display:flex; align-items:center; gap:10px; color:#004AC6; border-bottom:none; margin-bottom:15px; font-size:18px;">
                    <i class='bx bx-user'></i> Detail Pemesan
                </h3>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Nama Lengkap</label>
                    <div class="input-with-icon">
                        <i class='bx bx-id-card'></i>
                        <input type="text" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" class="form-input" readonly>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <div class="input-with-icon">
                            <i class='bx bx-phone'></i>
                            <input type="text" value="<?php echo htmlspecialchars($user_info['phone']); ?>" class="form-input" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Email</label>
                        <div class="input-with-icon">
                            <i class='bx bx-envelope'></i>
                            <input type="text" value="<?php echo htmlspecialchars($user_info['email'] ?? 'email@contoh.com'); ?>" class="form-input" readonly>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="booking-section-card">
                <h3 style="display:flex; align-items:center; gap:10px; color:#004AC6; border-bottom:none; margin-bottom:15px; font-size:18px;">
                    <i class='bx bx-wallet-alt'></i> Metode Pembayaran
                </h3>
                
                <div class="payment-options">
                    <div class="payment-method active">
                        <div class="payment-icon-box"><i class='bx bxs-bank'></i></div>
                        <div class="payment-details">
                            <span class="method-title">Transfer Bank</span>
                            <span class="method-desc">BCA, Mandiri, BNI, BRI</span>
                        </div>
                    </div>
                    <div class="payment-method">
                        <div class="payment-icon-box"><i class='bx bx-qr-scan'></i></div>
                        <div class="payment-details">
                            <span class="method-title">QRIS</span>
                            <span class="method-desc">Scan dengan aplikasi e-wallet</span>
                        </div>
                    </div>
                    <div class="payment-method">
                        <div class="payment-icon-box" style="color: #00A550;"><i class='bx bx-wallet'></i></div>
                        <div class="payment-details">
                            <span class="method-title">GoPay</span>
                            <span class="method-desc">Bayar instan via aplikasi Gojek</span>
                        </div>
                    </div>
                    <div class="payment-method">
                        <div class="payment-icon-box" style="color: #4C3494;"><i class='bx bx-credit-card-front'></i></div>
                        <div class="payment-details">
                            <span class="method-title">OVO</span>
                            <span class="method-desc">Bayar instan via aplikasi OVO</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="booking-sidebar">
            <div class="summary-card">
                <h3 style="font-size: 22px; margin-bottom: 25px;">Ringkasan Pesanan</h3>
                
                <div style="background: url('assets/images/<?php echo htmlspecialchars($court['image'] ?? 'default_court.jpg'); ?>') center/cover; height: 160px; border-radius: 8px; margin-bottom: 20px;"></div>

                <div class="venue-summary">
                    <h4><?php echo htmlspecialchars($court['venue_name']); ?></h4>
                    <p><?php echo htmlspecialchars($court['court_name']); ?> <?php if(!empty($court['category'])) echo " - " . htmlspecialchars($court['category']); ?></p>
                </div>
                
                <hr class="divider">
                
                <div class="booking-details-list">
                    <div class="detail-row">
                        <span><i class='bx bx-calendar' style="margin-right:5px; vertical-align:middle;"></i> Tanggal</span>
                        <strong><?php echo date('d M Y', strtotime($booking_date)); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span><i class='bx bx-time-five' style="margin-right:5px; vertical-align:middle;"></i> Waktu</span>
                        <div style="text-align: right;">
                            <?php 
                            foreach($time_slots as $t) {
                                $e = date('H:i', strtotime($t) + 3600);
                                echo "<strong>" . substr($t,0,5) . " - " . $e . "</strong><br>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span><i class='bx bx-timer' style="margin-right:5px; vertical-align:middle;"></i> Durasi</span>
                        <strong><?php echo count($time_slots); ?> Jam</strong>
                    </div>
                </div>
                
                <hr class="divider">
                
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Harga Sewa (<?php echo count($time_slots); ?> Jam)</span>
                        <span>Rp <?php echo number_format($court['price_per_hour'] * count($time_slots), 0, ',', '.'); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Biaya Layanan</span>
                        <span>Rp 2.000</span>
                    </div>
                    
                    <div class="price-row total">
                        <span>Total Pembayaran</span>
                        <span class="total-amount">Rp <?php echo number_format(($court['price_per_hour'] * count($time_slots)) + 2000, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <form method="POST" style="margin-top: 25px;">
                    <button type="submit" class="btn-pay-now">Bayar Sekarang &rarr;</button>
                </form>
                
                <div style="text-align: center; margin-top: 15px; font-size: 11px; color: #888;">
                    <i class='bx bx-lock-alt'></i> Pembayaran aman & terenkripsi
                </div>
            </div>
        </aside>
    </main>
    
    <script>
        const paymentMethods = document.querySelectorAll('.payment-method');
        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                paymentMethods.forEach(m => m.classList.remove('active'));
                method.classList.add('active');
            });
        });
    </script>
</body>
</html>