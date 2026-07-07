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

    public function createVenue($user_id, $name, $location, $description, $facilities, $image, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO venues (user_id, name, location, description, facilities, image, status) VALUES (:uid, :name, :loc, :desc, :fac, :img, :status)");
        return $stmt->execute([
            'uid' => $user_id,
            'name' => $name,
            'loc' => $location,
            'desc' => $description,
            'fac' => $facilities,
            'img' => $image,
            'status' => $status
        ]);
    }

    public function updateVenue($id, $name, $location, $description, $facilities, $image = null, $status = null) {
        $query = "UPDATE venues SET name = :name, location = :loc, description = :desc, facilities = :fac";
        $params = [
            'name' => $name,
            'loc' => $location,
            'desc' => $description,
            'fac' => $facilities,
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

    public function searchWithFilters($search_query = '', $floor_filter = [], $facility_filter = [], $sort_filter = '') {
        $query = "SELECT v.*, 
                       MIN(c.price_per_hour) as starting_price, 
                       (SELECT image FROM courts WHERE venue_id = v.id ORDER BY id ASC LIMIT 1) as image_file,
                       GROUP_CONCAT(DISTINCT c.category) as floor_types,
                       (SELECT IFNULL(AVG(rating), 5.0) FROM reviews WHERE venue_id = v.id) as avg_rating 
                FROM venues v 
                LEFT JOIN courts c ON v.id = c.venue_id 
                WHERE v.status = 'approved'";
        
        $params = [];

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
