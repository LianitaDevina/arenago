<?php
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Court.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Review.php';

class FrontController {
    private $pdo;
    private $venueModel;
    private $courtModel;
    private $bookingModel;
    private $reviewModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->venueModel = new Venue($pdo);
        $this->courtModel = new Court($pdo);
        $this->bookingModel = new Booking($pdo);
        $this->reviewModel = new Review($pdo);
    }

    public function handleSearch($getData) {
        $search_query = isset($getData['query']) ? trim($getData['query']) : '';
        $floor_filter = isset($getData['floor']) ? $getData['floor'] : [];
        $facility_filter = isset($getData['facility']) ? $getData['facility'] : [];
        $sort_filter = isset($getData['sort']) ? $getData['sort'] : '';

        $venues = $this->venueModel->searchWithFilters($search_query, $floor_filter, $facility_filter, $sort_filter);

        return [
            'search_query' => $search_query,
            'floor_filter' => $floor_filter,
            'facility_filter' => $facility_filter,
            'sort_filter' => $sort_filter,
            'venues' => $venues
        ];
    }

    public function getVenueDetail($venue_id) {
        $stmtVenue = $this->pdo->prepare("SELECT v.*, u.email as contact_email FROM venues v JOIN users u ON v.user_id = u.id WHERE v.id = :vid");
        $stmtVenue->execute(['vid' => $venue_id]);
        $venue = $stmtVenue->fetch();

        if (!$venue || $venue['status'] !== 'approved') {
            return null;
        }

        $courts = $this->courtModel->getByVenueId($venue_id);
        $reviews = $this->reviewModel->getReviewsByVenue($venue_id);

        return [
            'venue' => $venue,
            'courts' => $courts,
            'reviews' => $reviews
        ];
    }

    public function handleBookingCheckout($postData, $user_id, $court_id, $booking_date, $time_slots, $price_per_hour) {
        $error = "";
        $success = "";

        if ($court_id > 0 && !empty($booking_date) && !empty($time_slots) && $price_per_hour > 0) {
            $this->pdo->beginTransaction();
            try {
                foreach ($time_slots as $t) {
                    $start_time = $t;
                    $end_time = date('H:i:s', strtotime($t) + 3600);
                    
                    $isOverlap = $this->bookingModel->checkOverlap($court_id, $booking_date, $start_time, $end_time);
                    if ($isOverlap) {
                        throw new Exception("Sesi jadwal $start_time sudah dipesan oleh pengguna lain.");
                    }

                    $this->bookingModel->createOnlineBooking($user_id, $court_id, $booking_date, $start_time, $end_time, $price_per_hour);
                }
                $this->pdo->commit();
                $success = "Pemesanan lapangan Anda berhasil diproses!";
            } catch (Exception $e) {
                $this->pdo->rollBack();
                $error = $e->getMessage();
            }
        } else {
            $error = "Data pemesanan tidak valid. Silakan ulangi proses pemesanan.";
        }

        return ['error' => $error, 'success' => $success];
    }

    public function getUserBookings($user_id) {
        return $this->bookingModel->getUserBookings($user_id);
    }

    public function handleReview($postData, $user_id) {
        $venue_id = intval($postData['venue_id']);
        $rating = intval($postData['rating']);
        $comment = trim($postData['comment']);
        
        if ($rating > 0 && $rating <= 5) {
            if ($this->reviewModel->createReview($user_id, $venue_id, $rating, $comment)) {
                return ['success' => "Terima kasih! Ulasan Anda berhasil ditambahkan."];
            } else {
                return ['error' => "Gagal menyimpan ulasan."];
            }
        }
        return ['error' => "Data tidak valid."];
    }

    public function getBookedSlots($court_id, $date) {
        return $this->bookingModel->getBookingsForCourtOnDate($court_id, $date);
    }
}
