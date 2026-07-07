<?php
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Court.php';
require_once __DIR__ . '/../models/Booking.php';

class AdminController {
    private $pdo;
    private $venueModel;
    private $courtModel;
    private $bookingModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->venueModel = new Venue($pdo);
        $this->courtModel = new Court($pdo);
        $this->bookingModel = new Booking($pdo);
    }

    public function getDashboardData($user_id) {
        $row_venue = $this->venueModel->getVenueByUserId($user_id);
        
        if (!$row_venue) {
            return ['error' => 'no_venue'];
        }
        if ($row_venue['status'] !== 'approved') {
            return ['error' => 'not_approved'];
        }

        $venue_id = $row_venue['id'];
        $venue_name = $row_venue['name'];
        $result_courts = $this->courtModel->getByVenueId($venue_id);
        $result_bookings = $this->bookingModel->getBookingsByVenue($venue_id);

        return [
            'venue_id' => $venue_id,
            'venue_name' => $venue_name,
            'result_courts' => $result_courts,
            'result_bookings' => $result_bookings,
            'venue' => $row_venue
        ];
    }

    public function handleOfflineBooking($postData) {
        $pesan = "";
        if (isset($postData['action_offline_booking'])) {
            $court_id = intval($postData['court_id']);
            $booking_date = $postData['booking_date'];
            $start_time = $postData['start_time'];
            $end_time = date('H:i:s', strtotime($start_time) + 3600);
            $customer_name = trim($postData['customer_name']);
            $customer_phone = trim($postData['customer_phone']);
            $price = intval($postData['price_snap']);

            $isOverlap = $this->bookingModel->checkOverlap($court_id, $booking_date, $start_time, $end_time);

            if ($isOverlap) {
                $pesan = "<div class='alert alert-danger'>Gagal! Sesi jadwal tersebut sudah dipesan oleh pengguna lain. Silakan pilih jam atau lapangan berbeda.</div>";
            } else {
                $this->bookingModel->createOfflineBooking($court_id, $customer_name, $customer_phone, $booking_date, $start_time, $end_time, $price);
                $pesan = "<div class='alert alert-success'>Sukses! Berhasil mengunci slot jadwal secara manual (Booking Offline).</div>";
            }
        }
        return $pesan;
    }

    public function getCourtsList($venue_id) {
        return $this->courtModel->getByVenueId($venue_id);
    }

    public function getCourtById($court_id) {
        return $this->courtModel->getCourtById($court_id);
    }

    public function handleCourtAction($getData, $postData, $filesData, $venue_id) {
        if (isset($getData['delete'])) {
            $id_to_delete = intval($getData['delete']);
            if ($this->courtModel->deleteCourt($id_to_delete, $venue_id)) {
                $_SESSION['flash_msg'] = "Unit Lapangan berhasil dihapus permanen.";
            } else {
                $_SESSION['flash_msg_error'] = "Gagal menghapus unit lapangan karena error pada sistem.";
            }
            return true;
        }

        if (isset($postData['action'])) {
            $action = $postData['action'];
            $court_name = trim($postData['court_name']);
            $price_per_hour = intval($postData['price_per_hour']);
            $category = trim($postData['category']);
            $image_name = null;

            if (isset($filesData['image']) && $filesData['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $filesData['image']['tmp_name'];
                $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $filesData['image']['name']);
                $upload_dir = '../assets/images/';
                if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                    $_SESSION['flash_msg_error'] = "Gagal memproses upload foto.";
                    return true;
                }
            }

            if ($action === 'add') {
                if (!$image_name) {
                    $_SESSION['flash_msg_error'] = "Wajib melampirkan foto / gambar lapangan fisik.";
                    return true;
                }
                $this->courtModel->createCourt($venue_id, $court_name, $price_per_hour, $category, $image_name);
                $_SESSION['flash_msg'] = "Berhasil menambahkan unit lapangan baru!";
            } elseif ($action === 'edit' && isset($postData['id'])) {
                $court_id = intval($postData['id']);
                $this->courtModel->updateCourt($court_id, $venue_id, $court_name, $price_per_hour, $category, $image_name);
                $_SESSION['flash_msg'] = "Data Unit Lapangan berhasil diperbarui!";
            }
            return true;
        }
        return false;
    }

    public function handleVenueUpdate($postData, $filesData, $user_id, $venue_id = null) {
        if (isset($postData['name']) && isset($postData['location'])) {
            $name = trim($postData['name']);
            $location = trim($postData['location']);
            $description = trim($postData['description'] ?? '');
            $facilities_arr = isset($postData['facilities']) ? $postData['facilities'] : [];
            $facilities = implode(', ', $facilities_arr);
            $image_name = null;

            if (isset($filesData['image']) && $filesData['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $filesData['image']['tmp_name'];
                $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $filesData['image']['name']);
                $upload_dir = '../assets/images/';
                if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                    return "<p style='color:red; font-weight:600;'>Gagal memproses unggahan foto utama venue.</p>";
                }
            }

            if ($venue_id) {
                $this->venueModel->updateVenue($venue_id, $name, $location, $description, $facilities, $image_name, null);
                return "<p style='color:green; font-weight:600;'>Profil gedung berhasil diperbarui!</p>";
            } else {
                $this->venueModel->createVenue($user_id, $name, $location, $description, $facilities, $image_name ?? '', 'pending');
                return "<p style='color:green; font-weight:600;'>Profil gedung berhasil dibuat! Menunggu validasi superadmin.</p>";
            }
        }
        return "";
    }

    public function getReportData($venue_id, $startDate, $endDate) {
        $bookings = $this->bookingModel->getBookingsByVenue($venue_id);
        
        $filtered = [];
        $totalRev = 0;
        $totalRevOnline = 0;
        $totalRevOffline = 0;
        $cntOnline = 0;
        $cntOffline = 0;
        
        $sTs = strtotime($startDate . ' 00:00:00');
        $eTs = strtotime($endDate . ' 23:59:59');

        foreach ($bookings as $b) {
            $bTs = strtotime($b['booking_date']);
            if ($bTs >= $sTs && $bTs <= $eTs) {
                $filtered[] = $b;
                $totalRev += $b['total_price'];
                if ($b['payment_type'] === 'online') {
                    $totalRevOnline += $b['total_price'];
                    $cntOnline++;
                } else {
                    $totalRevOffline += $b['total_price'];
                    $cntOffline++;
                }
            }
        }
        
        return [
            'filtered' => $filtered,
            'totalRev' => $totalRev,
            'totalRevOnline' => $totalRevOnline,
            'totalRevOffline' => $totalRevOffline,
            'cntOnline' => $cntOnline,
            'cntOffline' => $cntOffline
        ];
    }

    public function getRevenueStats($venue_id) {
        return [
            'online' => $this->bookingModel->getOnlineRevenueByVenue($venue_id),
            'offline' => $this->bookingModel->getOfflineRevenueByVenue($venue_id)
        ];
    }

    public function getBookingsList($venue_id) {
        return $this->bookingModel->getBookingsByVenue($venue_id);
    }

    public function handleBookingCancellation($getData, $venue_id) {
        if (isset($getData['cancel_id'])) {
            $cancel_id = intval($getData['cancel_id']);
            $this->bookingModel->cancelBooking($cancel_id, $venue_id);
            return true;
        }
        return false;
    }
}
