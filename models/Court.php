<?php

class Court {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByVenueId($venue_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM courts WHERE venue_id = :vid ORDER BY id DESC");
        $stmt->execute(['vid' => $venue_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourtById($id, $venue_id = null) {
        if ($venue_id) {
            $stmt = $this->pdo->prepare("SELECT * FROM courts WHERE id = :id AND venue_id = :vid");
            $stmt->execute(['id' => $id, 'vid' => $venue_id]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM courts WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCourt($venue_id, $court_name, $price_per_hour, $image, $category) {
        $stmt = $this->pdo->prepare("INSERT INTO courts (venue_id, court_name, price_per_hour, image, category) VALUES (:vid, :cname, :price, :img, :cat)");
        return $stmt->execute([
            'vid' => $venue_id,
            'cname' => $court_name,
            'price' => $price_per_hour,
            'img' => $image,
            'cat' => $category
        ]);
    }

    public function updateCourt($id, $venue_id, $court_name, $price_per_hour, $image = null, $category = null) {
        $query = "UPDATE courts SET court_name = :cname, price_per_hour = :price, category = :cat";
        $params = [
            'cname' => $court_name,
            'price' => $price_per_hour,
            'cat' => $category,
            'id' => $id,
            'vid' => $venue_id
        ];

        if ($image) {
            $query .= ", image = :img";
            $params['img'] = $image;
        }

        $query .= " WHERE id = :id AND venue_id = :vid";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteCourt($id, $venue_id) {
        $stmt = $this->pdo->prepare("DELETE FROM courts WHERE id = :id AND venue_id = :vid");
        return $stmt->execute(['id' => $id, 'vid' => $venue_id]);
    }
}
