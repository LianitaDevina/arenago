<?php
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/index.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<div class="hero-section">
    <div class="hero-content">
        <h1>Temukan Lapangan Olahraga Terbaikmu</h1>
        <p>Cari, pilih, dan sewa lapangan olahraga bulu tangkis favoritmu secara instan. Bebas antre, main kapan saja dengan mudah.</p>
        <form action="search.php" method="GET" class="search-container">
            <input type="text" name="query" class="search-input" placeholder="Cari nama lapangan atau lokasi bermain...">
            <button type="submit" class="search-btn">Cari Sekarang</button>
        </form>
    </div>
</div>

<div class="features-section">
    <h2>Kenapa Memilih ArenaGO?</h2>
    <p class="subtitle">Berbagai keuntungan dan kemudahan menyewa lapangan melalui platform kami</p>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class='bx bx-search-alt'></i>
            </div>
            <h3>Pencarian Mudah</h3>
            <p>Temukan lapangan terdekat atau sesuai dengan kriteria yang kamu butuhkan dengan cepat menggunakan filter pintar kami.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class='bx bx-calendar-check'></i>
            </div>
            <h3>Booking Instan</h3>
            <p>Pilih jadwal, bayar, dan lapangan langsung menjadi milikmu tanpa perlu menunggu konfirmasi lama dari pengelola.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class='bx bx-shield-quarter'></i>
            </div>
            <h3>Aman & Terpercaya</h3>
            <p>Semua mitra tempat olahraga telah diverifikasi dengan standar tinggi untuk memastikan keamanan dan kenyamananmu.</p>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>