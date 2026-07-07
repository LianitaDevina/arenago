<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Venue.php';
require_once __DIR__ . '/../models/Booking.php';

class SuperadminController {
    private $pdo;
    private $userModel;
    private $venueModel;
    private $bookingModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->venueModel = new Venue($pdo);
        $this->bookingModel = new Booking($pdo);
    }

    public function getDashboardStats() {
        return [
            'revenue' => $this->bookingModel->getTotalRevenue(),
            'count_users' => $this->userModel->getUserCount(),
            'count_venues' => $this->venueModel->getApprovedCount(),
            'pending_venues' => $this->venueModel->getPendingVenues()
        ];
    }

    public function getUsersList() {
        return $this->userModel->getAllUsers();
    }

    public function getVenuesList() {
        return $this->venueModel->getAllVenues();
    }

    public function handleValidationAction($postData) {
        if (isset($postData['id']) && isset($postData['action'])) {
            $id = intval($postData['id']);
            $action = $postData['action'];

            if ($action === 'approve') {
                $this->venueModel->updateStatus($id, 'approved');
                $_SESSION['flash_msg'] = "Venue ID $id berhasil di-approve dan tayang publik!";
            } elseif ($action === 'reject') {
                $this->venueModel->updateStatus($id, 'rejected');
                $_SESSION['flash_msg'] = "Venue ID $id telah ditolak dari kemitraan.";
            } elseif ($action === 'delete') {
                $this->venueModel->deleteVenue($id);
                $_SESSION['flash_msg'] = "Venue ID $id berhasil dihapus dari sistem.";
            }
        }
    }

    public function handleDeleteUser($id) {
        if ($this->userModel->deleteUser($id)) {
            $_SESSION['flash_msg'] = "Akun pengguna berhasil dihapus permanen.";
        } else {
            $_SESSION['flash_msg_error'] = "Gagal menghapus pengguna.";
        }
    }

    public function getVenueById($id) {
        return $this->venueModel->getVenueById($id);
    }

    public function handleVenueAction($getData, $postData) {
        if (isset($getData['delete'])) {
            $id_to_delete = intval($getData['delete']);
            if ($this->venueModel->deleteVenue($id_to_delete)) {
                $_SESSION['flash_msg'] = "Data tempat olahraga (Venue) berhasil dihapus permanen.";
            } else {
                $_SESSION['flash_msg_error'] = "Gagal menghapus venue. Mungkin masih ada lapangan yang terikat dengannya.";
            }
            return true;
        }

        if (isset($postData['id'])) {
            $venue_id = intval($postData['id']);
            $name = trim($postData['name']);
            $location = trim($postData['location']);
            $description = trim($postData['description'] ?? '');
            $status = trim($postData['status'] ?? 'pending');
            $facilities_arr = isset($postData['facilities']) ? $postData['facilities'] : [];
            $facilities = implode(', ', $facilities_arr);

            if (!empty($name) && !empty($location)) {
                $success = $this->venueModel->updateVenue($venue_id, $name, $location, $description, $facilities, null, $status);
                if ($success) {
                    $_SESSION['flash_msg'] = "Data tempat olahraga berhasil diperbarui.";
                } else {
                    $_SESSION['flash_msg_error'] = "Gagal memperbarui data tempat olahraga.";
                }
                return true;
            }
        }
        return false;
    }
}
