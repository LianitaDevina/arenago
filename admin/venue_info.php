<?php
session_start();
require_once '../config/database.php';
require_once '../config/maps.php';
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
            
            <form action="" method="POST" id="venueForm">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Nama Gedung Olahraga</label>
                    <input type="text" name="name" class="admin-input" value="<?= $venue ? htmlspecialchars($venue['name']) : '' ?>" required placeholder="Contoh: GOR Arena Merdeka">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Alamat Lengkap</label>
                    <textarea name="location" class="admin-textarea" rows="3" required placeholder="Jalan, RT/RW, Kota..."><?= $venue ? htmlspecialchars($venue['location']) : '' ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Nomor Telepon Gedung</label>
                    <input type="text" name="phone" class="admin-input" value="<?= $venue ? htmlspecialchars($venue['phone'] ?? '') : '' ?>" placeholder="Contoh: 08123456789">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Koordinat Lokasi (Pilih dari peta di bawah)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="latitude" id="lat-input" class="admin-input" value="<?= $venue ? htmlspecialchars($venue['latitude'] ?? '') : '' ?>" readonly placeholder="Latitude" style="background: #F1F5F9; cursor: not-allowed; font-weight: 500; color: #475569;">
                        <input type="text" name="longitude" id="lng-input" class="admin-input" value="<?= $venue ? htmlspecialchars($venue['longitude'] ?? '') : '' ?>" readonly placeholder="Longitude" style="background: #F1F5F9; cursor: not-allowed; font-weight: 500; color: #475569;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="admin-label">Cari & Tandai Lokasi di Peta</label>
                    <input type="text" id="map-search" class="admin-input" placeholder="Ketik area atau alamat untuk melompat ke peta..." style="margin-bottom: 12px; border-color: #004AC6; box-shadow: 0 0 0 1px rgba(0,74,198,0.15);">
                    <div id="map-picker" style="width: 100%; height: 350px; border-radius: 8px; border: 1px solid #CBD5E0; margin-bottom: 8px;"></div>
                    <small style="color: #64748B; display: block; line-height: 1.4;">💡 Seret penanda (marker) merah atau klik area di peta untuk menyesuaikan letak koordinat gedung badminton Anda.</small>
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

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GMAPS_API_KEY ?>&libraries=places"></script>
    <script>
        function initMapPicker() {
            // Default center coordinates (Bali/Denpasar)
            let initialLat = parseFloat(document.getElementById('lat-input').value) || -8.6500;
            let initialLng = parseFloat(document.getElementById('lng-input').value) || 115.2167;
            
            let mapOptions = {
                center: { lat: initialLat, lng: initialLng },
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            };
            
            let map = new google.maps.Map(document.getElementById('map-picker'), mapOptions);
            
            let marker = new google.maps.Marker({
                position: { lat: initialLat, lng: initialLng },
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            // If we have existing coordinates, set map center
            if(document.getElementById('lat-input').value && document.getElementById('lng-input').value) {
                map.setCenter({ lat: initialLat, lng: initialLng });
                map.setZoom(16);
            }

            // Sync marker drag coordinates to inputs
            google.maps.event.addListener(marker, 'dragend', function() {
                let position = marker.getPosition();
                document.getElementById('lat-input').value = position.lat().toFixed(8);
                document.getElementById('lng-input').value = position.lng().toFixed(8);
            });

            // Sync map click coordinates to marker and inputs
            google.maps.event.addListener(map, 'click', function(event) {
                let latLng = event.latLng;
                marker.setPosition(latLng);
                document.getElementById('lat-input').value = latLng.lat().toFixed(8);
                document.getElementById('lng-input').value = latLng.lng().toFixed(8);
            });

            // Google Places Autocomplete search box integration
            let searchInput = document.getElementById('map-search');
            let autocomplete = new google.maps.places.Autocomplete(searchInput);
            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', function() {
                let place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }

                marker.setPosition(place.geometry.location);
                document.getElementById('lat-input').value = place.geometry.location.lat().toFixed(8);
                document.getElementById('lng-input').value = place.geometry.location.lng().toFixed(8);
            });
            
            // Prevent form submit when Enter is pressed on Autocomplete input
            searchInput.addEventListener('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });
        }

        google.maps.event.addDomListener(window, 'load', initMapPicker);
    </script>
</body>
</html>