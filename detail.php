<?php
include 'config/database.php';
require_once 'controllers/FrontController.php';

$venue_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_GET['action']) && $_GET['action'] === 'get_booked') {
    header('Content-Type: application/json');
    $court_id = isset($_GET['court_id']) ? intval($_GET['court_id']) : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    if ($court_id <= 0 || empty($date)) {
        echo json_encode([]);
        exit;
    }
    
    try {
        $controller = new FrontController($pdo);
        $bookings = $controller->getBookedSlots($court_id, $date);
        $booked_times = [];
        foreach ($bookings as $b) {
            $booked_times[] = $b['start_time'];
        }
        echo json_encode($booked_times);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

include 'includes/header.php';

if ($venue_id <= 0) {
    header("Location: search.php");
    exit;
}

try {
    $controller = new FrontController($pdo);
    $detailData = $controller->getVenueDetail($venue_id);

    if (!$detailData) {
        header("Location: search.php");
        exit;
    }

    $venue = $detailData['venue'];
    $courts = $detailData['courts'];
    $reviews = $detailData['reviews'];

    $gallery_images = [];
    foreach ($courts as $c) {
        if (!empty($c['image']) && !in_array($c['image'], $gallery_images)) {
            $gallery_images[] = $c['image'];
        }
    }
    if (empty($gallery_images)) {
        $gallery_images[] = 'default_court.jpg';
    }
    $gallery_images = array_slice($gallery_images, 0, 4);

    $courtPrices = [];
    foreach ($courts as $c) {
        $courtPrices[$c['id']] = intval($c['price_per_hour']);
    }

} catch (Exception $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($venue['name']); ?> - ArenaGO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/home.css?v=1.2">
    <link rel="stylesheet" href="assets/css/detail.css?v=1.2">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <main class="detail-wrapper">
        <section class="main-info">
            <div class="image-gallery count-<?php echo count($gallery_images); ?>">
                <?php foreach ($gallery_images as $index => $img): ?>
                    <div class="img-wrap img-<?php echo $index + 1; ?>">
                        <img src="assets/images/<?php echo htmlspecialchars($img); ?>" class="gallery-img" alt="Foto <?php echo $index + 1; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="venue-header" style="margin-bottom: 25px;">
                <h1 class="venue-title"><?php echo htmlspecialchars($venue['name']); ?></h1>
                <p class="venue-location" style="color: #718096; margin-top: 5px;">📍 <?php echo htmlspecialchars($venue['location']); ?></p>
                <?php if (!empty($venue['phone'])): ?>
                    <p class="venue-phone" style="color: #004AC6; margin-top: 5px; font-weight: 500;">📞 <?php echo htmlspecialchars($venue['phone']); ?></p>
                <?php endif; ?>
            </div>

            <div class="description-section">
                <h2>Tentang Venue</h2>
                <p><?php echo nl2br(htmlspecialchars($venue['description'] ?? 'Belum ada deskripsi untuk venue ini.')); ?></p>
            </div>

            <?php if (!empty($venue['facilities'])): ?>
                <div class="description-section" style="margin-top: 40px;">
                    <h2>Fasilitas</h2>
                    <div class="facilities-grid">
                        <?php 
                        $facs = explode(',', $venue['facilities']);
                        $iconMap = [
                            'Kamar Mandi' => 'bx bx-bath',
                            'Sewa Raket' => 'bx bx-tennis-ball',
                            'Parkir Luas' => 'bx bx-car',
                            'Papan Skor' => 'bx bx-chalkboard'
                        ];
                        foreach($facs as $f) {
                            $f = trim($f);
                            if(!empty($f)) {
                                $icon = $iconMap[$f] ?? 'bx bx-check-circle';
                                echo "<div class='facility-item' style='display:flex; flex-direction:column; align-items:center; gap:10px;'>
                                        <i class='$icon' style='font-size:32px; color:#004AC6;'></i>
                                        <span>".htmlspecialchars($f)."</span>
                                      </div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="description-section" style="margin-top: 40px;">
                <h2>Ulasan Pengguna (<?php echo count($reviews); ?>)</h2>
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div style="background: #FAFAFA; border-left: 4px solid #004AC6; padding: 15px; margin-bottom: 15px; border-radius: 0 8px 8px 0;">
                            <div style="display: flex; justify-content: space-between;">
                                <strong><?php echo htmlspecialchars($rev['reviewer_name'] ?? ''); ?></strong>
                                <span style="color: #FFB300;">
                                    <?php echo str_repeat('⭐', $rev['rating']); ?>
                                </span>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #555; font-size: 14px;"><?php echo htmlspecialchars($rev['review_text']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; font-style: italic;">Belum ada ulasan untuk tempat ini. Jadilah yang pertama memberikan ulasan setelah bermain!</p>
                <?php endif; ?>
            </div>
        </section>

        <aside class="booking-sidebar">
            <div class="sticky-card">
                <h3>Pesan Jadwal</h3>
                
                <form action="booking.php" method="GET" id="bookingForm">
                    <div class="form-group">
                        <label>Pilih Lapangan</label>
                        <select name="court_id" id="courtSelect" class="form-input" required>
                            <option value="">-- Pilih Lapangan --</option>
                            <?php foreach ($courts as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['court_name']); ?> - <?php echo htmlspecialchars($c['category']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pilih Tanggal</label>
                        <input type="date" name="date" id="dateSelect" class="form-input" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="margin-bottom: 0;">Pilih Waktu</label>
                            <span id="priceLabel" style="font-size: 12px; color: #666; font-weight: 500;">Rp 0 / Jam</span>
                        </div>
                        <div class="time-slots" id="timeGrid">
                            <button type="button" class="slot-btn" data-time="08:00:00">08:00</button>
                            <button type="button" class="slot-btn" data-time="09:00:00">09:00</button>
                            <button type="button" class="slot-btn" data-time="10:00:00">10:00</button>
                            <button type="button" class="slot-btn" data-time="11:00:00">11:00</button>
                            <button type="button" class="slot-btn" data-time="12:00:00">12:00</button>
                            <button type="button" class="slot-btn" data-time="13:00:00">13:00</button>
                            <button type="button" class="slot-btn" data-time="14:00:00">14:00</button>
                            <button type="button" class="slot-btn" data-time="15:00:00">15:00</button>
                            <button type="button" class="slot-btn" data-time="16:00:00">16:00</button>
                            <button type="button" class="slot-btn" data-time="17:00:00">17:00</button>
                            <button type="button" class="slot-btn" data-time="18:00:00">18:00</button>
                            <button type="button" class="slot-btn" data-time="19:00:00">19:00</button>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 15px; font-size: 12px; color: #555; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; border: 1px solid #ccc; border-radius: 50%;"></div> Tersedia</span>
                            <span style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #004AC6; border-radius: 50%;"></div> Dipilih</span>
                            <span style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #E2E8F0; border: 1px solid #eaeaea; border-radius: 50%;"></div> Penuh</span>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #eaeaea; margin: 20px 0; padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #666; font-size: 14px;">Total Pembayaran (<span id="hourCount">0</span> Jam)</span>
                        <span id="totalPrice" style="color: #004AC6; font-size: 22px; font-weight: 700;">Rp 0</span>
                    </div>

                    <!-- Hidden input to store multiple selected times -->
                    <input type="hidden" name="time_slots" id="timeSlotsInput" value="">
                    <button type="submit" class="btn-primary-block" id="btnSubmit" disabled>Lanjut Pembayaran &rarr;</button>
                </form>
            </div>
        </aside>

    </main>

    <script>
        const courtPrices = <?php echo json_encode($courtPrices); ?>;
        const courtSelect = document.getElementById('courtSelect');
        const dateSelect = document.getElementById('dateSelect');
        const priceLabel = document.getElementById('priceLabel');
        const hourCountLabel = document.getElementById('hourCount');
        const totalPriceLabel = document.getElementById('totalPrice');
        const timeSlotsInput = document.getElementById('timeSlotsInput');
        const btnSubmit = document.getElementById('btnSubmit');
        const slotBtns = document.querySelectorAll('.slot-btn');
        
        let currentPrice = 0;
        let selectedSlots = [];

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function calculateTotal() {
            const total = selectedSlots.length * currentPrice;
            hourCountLabel.textContent = selectedSlots.length;
            totalPriceLabel.textContent = 'Rp ' + formatRupiah(total);
            
            if (selectedSlots.length > 0 && currentPrice > 0) {
                btnSubmit.removeAttribute('disabled');
            } else {
                btnSubmit.setAttribute('disabled', 'true');
            }
            
            timeSlotsInput.value = selectedSlots.join(',');
        }

        function resetSlots() {
            selectedSlots = [];
            slotBtns.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('booked');
                btn.removeAttribute('disabled');
            });
            calculateTotal();
        }

        async function fetchBookedTimes() {
            const courtId = courtSelect.value;
            const dateStr = dateSelect.value;

            if (!courtId || !dateStr) {
                resetSlots();
                return;
            }

            resetSlots(); // Reset first before applying new ones

            try {
                const response = await fetch(`detail.php?action=get_booked&court_id=${courtId}&date=${dateStr}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const bookedTimes = await response.json();

                slotBtns.forEach(btn => {
                    const time = btn.getAttribute('data-time');
                    if (bookedTimes.includes(time)) {
                        btn.classList.add('booked');
                        btn.setAttribute('disabled', 'true');
                    }
                });
            } catch (error) {
                console.error("Error fetching booked times:", error);
            }
        }

        courtSelect.addEventListener('change', function() {
            const val = this.value;
            if(val && courtPrices[val]) {
                currentPrice = courtPrices[val];
                priceLabel.textContent = 'Rp ' + formatRupiah(currentPrice) + ' / Jam';
            } else {
                currentPrice = 0;
                priceLabel.textContent = 'Rp 0 / Jam';
            }
            fetchBookedTimes();
        });

        dateSelect.addEventListener('change', fetchBookedTimes);

        slotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.classList.contains('booked')) return; // Extra safety

                const time = this.getAttribute('data-time');
                if (this.classList.contains('active')) {
                    this.classList.remove('active');
                    selectedSlots = selectedSlots.filter(t => t !== time);
                } else {
                    this.classList.add('active');
                    selectedSlots.push(time);
                }
                calculateTotal();
            });
        });

        // Trigger fetch on load if values are pre-selected
        fetchBookedTimes();
    </script>

</body>
</html>