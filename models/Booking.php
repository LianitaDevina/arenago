<?php

class Booking {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOnlineBooking($user_id, $court_id, $booking_date, $start_time, $end_time, $total_price) {
        $stmt = $this->pdo->prepare("INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_price, payment_type, status) VALUES (:uid, :cid, :bdate, :bstart, :bend, :price, 'online', 'pending')");
        return $stmt->execute([
            'uid' => $user_id,
            'cid' => $court_id,
            'bdate' => $booking_date,
            'bstart' => $start_time,
            'bend' => $end_time,
            'price' => $total_price
        ]);
    }

    public function createOfflineBooking($court_id, $customer_name, $customer_phone, $booking_date, $start_time, $end_time, $total_price) {
        $stmt = $this->pdo->prepare("INSERT INTO bookings (court_id, customer_name_offline, customer_phone_offline, booking_date, start_time, end_time, total_price, payment_type, status) VALUES (:cid, :name, :phone, :bdate, :bstart, :bend, :price, 'offline', 'success')");
        return $stmt->execute([
            'cid' => $court_id,
            'name' => $customer_name,
            'phone' => $customer_phone,
            'bdate' => $booking_date,
            'bstart' => $start_time,
            'bend' => $end_time,
            'price' => $total_price
        ]);
    }

    public function getBookingsByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT b.*, c.court_name, v.name as venue_name, v.location, c.venue_id 
                                     FROM bookings b 
                                     JOIN courts c ON b.court_id = c.id 
                                     JOIN venues v ON c.venue_id = v.id 
                                     WHERE b.user_id = :uid 
                                     ORDER BY b.booking_date DESC, b.start_time ASC");
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByVenue($venue_id) {
        $stmt = $this->pdo->prepare("SELECT b.*, c.court_name, u.name as cust_online_name, u.phone as cust_online_phone 
                                     FROM bookings b 
                                     JOIN courts c ON b.court_id = c.id 
                                     LEFT JOIN users u ON b.user_id = u.id 
                                     WHERE c.venue_id = :vid 
                                     ORDER BY b.id DESC");
        $stmt->execute(['vid' => $venue_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkOverlap($court_id, $date, $start, $end) {
        $stmt = $this->pdo->prepare("SELECT id FROM bookings 
                                     WHERE court_id = :cid AND booking_date = :bdate 
                                     AND ((start_time <= :bstart AND end_time > :bstart) 
                                     OR (start_time < :bend AND end_time >= :bend))");
        $stmt->execute([
            'cid' => $court_id,
            'bdate' => $date,
            'bstart' => $start,
            'bend' => $end
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getTotalRevenue() {
        $stmt = $this->pdo->query("SELECT SUM(total_price) as total_rev FROM bookings WHERE status = 'success'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rev'] ? $row['total_rev'] : 0;
    }
    
    public function getOnlineRevenueByVenue($venue_id) {
        $stmt = $this->pdo->prepare("SELECT SUM(total_price) as total_rev FROM bookings b JOIN courts c ON b.court_id = c.id WHERE c.venue_id = :vid AND b.payment_type = 'online' AND b.status = 'success'");
        $stmt->execute(['vid' => $venue_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rev'] ? $row['total_rev'] : 0;
    }
    
    public function getOfflineRevenueByVenue($venue_id) {
        $stmt = $this->pdo->prepare("SELECT SUM(total_price) as total_rev FROM bookings b JOIN courts c ON b.court_id = c.id WHERE c.venue_id = :vid AND b.payment_type = 'offline' AND b.status = 'success'");
        $stmt->execute(['vid' => $venue_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rev'] ? $row['total_rev'] : 0;
    }
    
    public function cancelBooking($booking_id, $venue_id) {
        $stmt = $this->pdo->prepare("UPDATE bookings b JOIN courts c ON b.court_id = c.id SET b.status = 'cancelled' WHERE b.id = :bid AND c.venue_id = :vid");
        return $stmt->execute(['bid' => $booking_id, 'vid' => $venue_id]);
    }

    public function getBookingsForCourtOnDate($court_id, $date) {
        $stmt = $this->pdo->prepare("SELECT start_time, end_time FROM bookings WHERE court_id = :cid AND booking_date = :bdate AND status = 'success'");
        $stmt->execute(['cid' => $court_id, 'bdate' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserBookings($user_id) {
        $query = "SELECT b.*, c.court_name, v.name as venue_name, v.location, c.venue_id 
                  FROM bookings b 
                  JOIN courts c ON b.court_id = c.id 
                  JOIN venues v ON c.venue_id = v.id 
                  WHERE b.user_id = :uid 
                  ORDER BY b.booking_date DESC, b.start_time ASC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
