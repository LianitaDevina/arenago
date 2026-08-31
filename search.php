<?php
session_start();
require_once 'config/database.php';
require_once 'controllers/FrontController.php';

try {
    $controller = new FrontController($pdo);
    $searchData = $controller->handleSearch($_GET);
    
    $search_query = $searchData['search_query'];
    $floor_filter = $searchData['floor_filter'];
    $facility_filter = $searchData['facility_filter'];
    $sort_filter = $searchData['sort_filter'];
    $user_lat = $searchData['user_lat'] ?? '';
    $user_lng = $searchData['user_lng'] ?? '';
    $venues = $searchData['venues'];
} catch (Exception $e) {
    $venues = [];
    $search_query = ''; $floor_filter = []; $facility_filter = []; $sort_filter = '';
    $user_lat = ''; $user_lng = '';
    echo "<script>alert('Gagal mengambil data pencarian: " . addslashes($e->getMessage()) . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksplorasi Lapangan - ArenaGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/search.css"> 
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8F9FA; margin: 0; padding: 0; color: #1A202C; }

        .container { display: flex; padding: 30px 5%; gap: 30px; max-width: 1400px; margin: auto; }
        .sidebar { width: 260px; flex-shrink: 0; }
        .filter-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .filter-title { font-size: 20px; font-weight: 700; margin: 0; }
        .filter-reset { color: #004AC6; font-size: 13px; font-weight: 600; text-decoration: none; }
        
        .filter-section { margin-bottom: 25px; }
        .filter-label { font-size: 14px; font-weight: 600; margin-bottom: 12px; display: block; color: #4A5568; }
        .radio-group label, .checkbox-group label { display: block; margin-bottom: 10px; font-size: 14px; color: #4A5568; cursor: pointer; }

        .main-content { flex: 1; }
        .search-top-bar { display: flex; gap: 15px; margin-bottom: 20px; }
        .search-input { flex: 1; padding: 12px 15px; border: 1px solid #CBD5E0; border-radius: 6px; font-size: 15px; }

        .btn-view { padding: 10px 15px; border: 1px solid #CBD5E0; background: white; border-radius: 6px; cursor: pointer; color: #4A5568; font-weight: 500; }
        .btn-view.active { background: #004AC6; color: white; border-color: #004AC6; }

        .results-info { font-size: 14px; color: #718096; margin-bottom: 15px; }
        .grid-lapangan { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

        .card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; display: flex; flex-direction: column; position: relative; }
        .card-img-container { position: relative; height: 180px; background-color: #2D3748; }
        .card-img-container img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { position: absolute; top: 10px; left: 10px; background: #38A169; color: white; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .price-badge { position: absolute; bottom: 10px; right: 10px; background: white; color: #004AC6; font-size: 13px; font-weight: 700; padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .card-title { font-size: 18px; font-weight: 700; margin: 0; color: #1A202C; }
        .card-rating { font-size: 13px; font-weight: 700; color: #D69E2E; display: flex; align-items: center; gap: 4px; }
        .card-address { font-size: 13px; color: #718096; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .card-tags { display: flex; gap: 8px; margin-bottom: 15px; }
        .tag { background: #EDF2F7; color: #4A5568; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 500; }
        
        .btn-pesan { background-color: #004AC6; color: white; text-align: center; padding: 10px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: auto; display: block; transition: 0.2s; }
        .btn-pesan:hover { background-color: #003794; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
    <form method="GET" action="search.php" style="display:flex; width:100%; gap: 30px;" id="searchForm">
        <input type="hidden" name="user_lat" id="user_lat" value="<?php echo htmlspecialchars($user_lat); ?>">
        <input type="hidden" name="user_lng" id="user_lng" value="<?php echo htmlspecialchars($user_lng); ?>">
        <div class="sidebar">
        <div class="filter-header">
            <h2 class="filter-title">Filter</h2>
            <a href="search.php" class="filter-reset">Reset</a>
        </div>
        
        <div class="filter-section radio-group">
            <span class="filter-label">Urutkan Berdasarkan</span>
            <label><input type="radio" name="sort" value="termurah" <?php echo ($sort_filter == 'termurah') ? 'checked' : ''; ?>> Termurah</label>
            <label><input type="radio" name="sort" value="terdekat" <?php echo ($sort_filter == 'terdekat') ? 'checked' : ''; ?>> Terdekat</label>
            <label><input type="radio" name="sort" value="tertinggi" <?php echo ($sort_filter == 'tertinggi') ? 'checked' : ''; ?>> Rating Tertinggi</label>
        </div>

        <div class="filter-section checkbox-group">
            <span class="filter-label">Tipe Lantai</span>
            <label><input type="checkbox" name="floor[]" value="Lantai Vynil" <?php echo in_array('Lantai Vynil', $floor_filter) ? 'checked' : ''; ?>> Lantai Vynil</label>
            <label><input type="checkbox" name="floor[]" value="Lantai Kayu" <?php echo in_array('Lantai Kayu', $floor_filter) ? 'checked' : ''; ?>> Lantai Kayu</label>
        </div>

        <div class="filter-section checkbox-group">
            <span class="filter-label">Fasilitas Tambahan</span>
            <?php 
                $facs = ['Sewa Raket', 'Kamar Mandi', 'Parkir Luas', 'Papan Skor'];
                foreach($facs as $f) {
                    $checked = in_array($f, $facility_filter) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='facility[]' value='$f' $checked> $f</label>";
                }
            ?>
        </div>
        <button type="submit" style="width:100%; padding: 12px; background: #004AC6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Terapkan Filter</button>
    </div>

    <div class="main-content">
        <div class="search-top-bar">
            <div style="display:flex; flex:1; margin:0;">
                <input type="text" name="query" class="search-input" placeholder="Cari lapangan bulutangkis..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
        </div>
        
        <div class="results-info">Menampilkan <?php echo count($venues); ?> hasil pencarian</div>
        
        <div class="grid-lapangan">
            <?php if (count($venues) > 0): ?>
                <?php foreach ($venues as $row): 
                    $name = $row['name'];
                    $location = $row['location'];
                    $rating = isset($row['avg_rating']) ? number_format((float)$row['avg_rating'], 1) : '5.0';
                    $image_file = !empty($row['image_file']) ? $row['image_file'] : 'default_court.jpg';
                    $price = $row['starting_price'] ?? 0;
                    $floor = !empty($row['floor_types']) ? $row['floor_types'] : 'Belum diatur';
                    $distance = (isset($row['distance']) && $row['distance'] !== null) ? number_format((float)$row['distance'], 1) . ' km' : null;
                ?>
                    <div class="card">
                        <div class="card-img-container">
                            <div class="status-badge">Available</div>
                            <img src="assets/images/<?php echo htmlspecialchars($image_file); ?>" alt="<?php echo htmlspecialchars($name); ?>" onerror="this.style.display='none'">
                            <div class="price-badge">Mulai Rp <?php echo number_format((float)$price, 0, ',', '.'); ?> / jam</div>
                        </div>
                        
                        <div class="card-body">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($name); ?></h3>
                                <div class="card-rating">⭐ <?php echo htmlspecialchars($rating); ?></div>
                            </div>
                            
                            <div class="card-address">
                                📍 <?php echo htmlspecialchars($location); ?>
                                <?php if ($distance): ?>
                                    <span style="display:inline-block; margin-left: 6px; background:#EBF8FF; color:#2B6CB0; font-size:11px; padding:2px 6px; border-radius:4px; font-weight:600; vertical-align:middle;"><?php echo $distance; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-tags">
                                <span class="tag">Bulutangkis</span>
                                <span class="tag"><?php echo htmlspecialchars($floor); ?></span>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="detail.php?id=<?php echo $row['id'] ?? 1; ?>" class="btn-pesan">Pesan Sekarang</a>
                            <?php else: ?>
                                <a href="auth/login.php?redirect=search" class="btn-pesan" onclick="return confirm('Anda harus login terlebih dahulu!');">Pesan Sekarang</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px dashed #CBD5E0;">
                    <h3 style="color: #4A5568;">Tidak ada hasil pencarian</h3>
                    <p style="color: #718096; font-size: 14px;">Coba gunakan kata kunci lain atau hapus filter Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('searchForm');
    const radioSorts = document.querySelectorAll('input[name="sort"]');
    const inputLat = document.getElementById('user_lat');
    const inputLng = document.getElementById('user_lng');

    // Handle sort radio button click
    radioSorts.forEach(radio => {
        radio.addEventListener('change', function(e) {
            if (this.value === 'terdekat') {
                e.preventDefault();
                // If coordinates already exist, just submit
                if (inputLat.value && inputLng.value) {
                    form.submit();
                    return;
                }
                
                requestLocationAndSubmit();
            } else {
                // For other sort options, clear coords to avoid passing old values
                inputLat.value = '';
                inputLng.value = '';
                form.submit();
            }
        });
    });

    // Intercept form submit button if sort "terdekat" is selected but coordinates are empty
    form.addEventListener('submit', function(e) {
        const selectedSort = document.querySelector('input[name="sort"]:checked');
        if (selectedSort && selectedSort.value === 'terdekat') {
            if (!inputLat.value || !inputLng.value) {
                e.preventDefault();
                requestLocationAndSubmit();
            }
        }
    });

    function requestLocationAndSubmit() {
        if (navigator.geolocation) {
            // Show loading dialog
            const loadingText = document.createElement('div');
            loadingText.id = 'geo-loading';
            loadingText.style = 'position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(14, 30, 56, 0.95); color:white; padding:20px 30px; border-radius:12px; z-index:9999; font-family:"Inter", sans-serif; font-weight:600; font-size:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3); display:flex; align-items:center; gap:12px; transition: 0.3s;';
            loadingText.innerHTML = "<span class='loader-spin' style='width: 18px; height: 18px; border: 3px solid #FFF; border-bottom-color: transparent; border-radius: 50%; display: inline-block; animation: rotation 1s linear infinite;'></span>Mendeteksi lokasi terdekat Anda...";
            
            // Add style for rotating animation dynamically
            const style = document.createElement('style');
            style.innerHTML = `@keyframes rotation { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
            document.head.appendChild(style);
            document.body.appendChild(loadingText);

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    inputLat.value = position.coords.latitude;
                    inputLng.value = position.coords.longitude;
                    document.getElementById('geo-loading')?.remove();
                    form.submit();
                },
                function(error) {
                    document.getElementById('geo-loading')?.remove();
                    let msg = "Gagal mendapatkan lokasi Anda. ";
                    if (error.code === error.PERMISSION_DENIED) {
                        msg += "Izin lokasi ditolak. Silakan aktifkan izin lokasi di browser Anda untuk menggunakan filter terdekat.";
                    } else {
                        msg += "Silakan periksa koneksi internet atau GPS Anda.";
                    }
                    alert(msg);
                    
                    // Uncheck "terdekat" and fallback to default (re-check previous or check termurah)
                    const termurahRadio = document.querySelector('input[name="sort"][value="termurah"]');
                    if (termurahRadio) {
                        termurahRadio.checked = true;
                    }
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        } else {
            alert("Browser Anda tidak mendukung pencarian lokasi otomatis.");
        }
    }
});
</script>

</body>
</html>