<?php

class Venue {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllVenues() {
        $stmt = $this->pdo->query("SELECT v.*, u.name as owner_name FROM venues v JOIN users u ON v.user_id = u.id ORDER BY v.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingVenues() {
        $stmt = $this->pdo->query("SELECT v.*, u.name as owner_name FROM venues v JOIN users u ON v.user_id = u.id WHERE v.status = 'pending' ORDER BY v.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedVenues() {
        $stmt = $this->pdo->query("SELECT v.*, u.name as owner_name FROM venues v JOIN users u ON v.user_id = u.id WHERE v.status = 'approved' ORDER BY v.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVenueById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM venues WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVenueByUserId($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM venues WHERE user_id = :uid");
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createVenue($user_id, $name, $location, $phone, $description, $facilities, $image, $status, $latitude = null, $longitude = null) {
        $latitude = ($latitude !== '' && $latitude !== null) ? $latitude : null;
        $longitude = ($longitude !== '' && $longitude !== null) ? $longitude : null;

        $stmt = $this->pdo->prepare("INSERT INTO venues (user_id, name, location, phone, description, facilities, image, status, latitude, longitude) VALUES (:uid, :name, :loc, :phone, :desc, :fac, :img, :status, :lat, :lng)");
        return $stmt->execute([
            'uid' => $user_id,
            'name' => $name,
            'loc' => $location,
            'phone' => $phone,
            'desc' => $description,
            'fac' => $facilities,
            'img' => $image,
            'status' => $status,
            'lat' => $latitude,
            'lng' => $longitude
        ]);
    }

    public function updateVenue($id, $name, $location, $phone, $description, $facilities, $image = null, $status = null, $latitude = null, $longitude = null) {
        $latitude = ($latitude !== '' && $latitude !== null) ? $latitude : null;
        $longitude = ($longitude !== '' && $longitude !== null) ? $longitude : null;

        $query = "UPDATE venues SET name = :name, location = :loc, phone = :phone, description = :desc, facilities = :fac, latitude = :lat, longitude = :lng";
        $params = [
            'name' => $name,
            'loc' => $location,
            'phone' => $phone,
            'desc' => $description,
            'fac' => $facilities,
            'lat' => $latitude,
            'lng' => $longitude,
            'id' => $id
        ];

        if ($image) {
            $query .= ", image = :img";
            $params['img'] = $image;
        }

        if ($status) {
            $query .= ", status = :status";
            $params['status'] = $status;
        }

        $query .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteVenue($id) {
        $stmt = $this->pdo->prepare("DELETE FROM venues WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE venues SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function searchVenues($keyword) {
        $stmt = $this->pdo->prepare("SELECT * FROM venues WHERE status = 'approved' AND (name LIKE :kw OR location LIKE :kw)");
        $stmt->execute(['kw' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchWithFilters($search_query = '', $floor_filter = [], $facility_filter = [], $sort_filter = '', $user_lat = null, $user_lng = null) {
        $params = [];
        $distance_select = "";

        if ($user_lat !== null && $user_lat !== '' && $user_lng !== null && $user_lng !== '') {
            $distance_select = ", (6371 * acos(cos(radians(:user_lat)) * cos(radians(v.latitude)) * cos(radians(v.longitude) - radians(:user_lng)) + sin(radians(:user_lat)) * sin(radians(v.latitude)))) AS distance";
            $params[':user_lat'] = $user_lat;
            $params[':user_lng'] = $user_lng;
        }

        $query = "SELECT v.*, 
                       MIN(c.price_per_hour) as starting_price, 
                       (SELECT image FROM courts WHERE venue_id = v.id ORDER BY id ASC LIMIT 1) as image_file,
                       GROUP_CONCAT(DISTINCT c.category) as floor_types,
                       (SELECT IFNULL(AVG(rating), 5.0) FROM reviews WHERE venue_id = v.id) as avg_rating 
                       $distance_select
                 FROM venues v 
                 LEFT JOIN courts c ON v.id = c.venue_id 
                 WHERE v.status = 'approved'";
        
        if (!empty($search_query)) {
            $query .= " AND (v.name LIKE :q OR v.location LIKE :q)";
            $params[':q'] = "%" . $search_query . "%";
        }

        if (!empty($floor_filter) && is_array($floor_filter)) {
            $floor_conditions = [];
            foreach ($floor_filter as $k => $fl) {
                $paramKey = ":fl" . $k;
                $floor_conditions[] = "c.category = $paramKey";
                $params[$paramKey] = $fl;
            }
            if (count($floor_conditions) > 0) {
                $query .= " AND (" . implode(" OR ", $floor_conditions) . ")";
            }
        }

        if (!empty($facility_filter) && is_array($facility_filter)) {
            foreach ($facility_filter as $k => $fac) {
                $paramKey = ":fac" . $k;
                $query .= " AND v.facilities LIKE $paramKey";
                $params[$paramKey] = "%" . trim($fac) . "%";
            }
        }

        $query .= " GROUP BY v.id";

        if (!empty($sort_filter)) {
            if ($sort_filter == 'termurah') {
                $query .= " ORDER BY starting_price ASC";
            } elseif ($sort_filter == 'tertinggi') {
                $query .= " ORDER BY avg_rating DESC";
            } elseif ($sort_filter == 'terdekat' && $user_lat !== null && $user_lat !== '' && $user_lng !== null && $user_lng !== '') {
                $query .= " ORDER BY (distance IS NULL) ASC, distance ASC";
            } else {
                $query .= " ORDER BY v.id DESC";
            }
        } else {
            $query .= " ORDER BY v.id DESC";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM venues WHERE status = 'approved'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
