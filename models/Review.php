<?php

class Review {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createReview($venue_id, $user_id, $rating, $review_text) {
        $stmt = $this->pdo->prepare("INSERT INTO reviews (venue_id, user_id, rating, review_text) VALUES (:vid, :uid, :rating, :rtext)");
        return $stmt->execute([
            'vid' => $venue_id,
            'uid' => $user_id,
            'rating' => $rating,
            'rtext' => $review_text
        ]);
    }

    public function getReviewsByVenue($venue_id) {
        $stmt = $this->pdo->prepare("SELECT r.*, u.name as reviewer_name 
                                     FROM reviews r 
                                     JOIN users u ON r.user_id = u.id 
                                     WHERE r.venue_id = :vid 
                                     ORDER BY r.created_at DESC");
        $stmt->execute(['vid' => $venue_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating($venue_id) {
        $stmt = $this->pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE venue_id = :vid");
        $stmt->execute(['vid' => $venue_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    }
    
    public function getReviewCount($venue_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE venue_id = :vid");
        $stmt->execute(['vid' => $venue_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
