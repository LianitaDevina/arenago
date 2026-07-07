<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Venue.php';

class AuthController {
    private $pdo;
    private $userModel;
    private $venueModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->venueModel = new Venue($pdo);
    }

    public function handleLogin($postData, $redirect = '') {
        $error = '';
        
        if (isset($postData['email']) && isset($postData['password'])) {
            $email = trim($postData['email']);
            $password = trim($postData['password']);

            if (!empty($email) && !empty($password)) {
                try {
                    $user = $this->userModel->getByEmail($email);

                    if ($user) {
                        $login_sukses = false;

                        if (password_verify($password, $user['password'])) {
                            $login_sukses = true;
                        } elseif (md5($password) === $user['password']) {
                            $login_sukses = true;
                        }

                        if ($login_sukses) {
                            session_regenerate_id(true);

                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['name']    = $user['name'];
                            $_SESSION['role']    = $user['role'];

                            if ($redirect === 'search') {
                                header("Location: ../search.php");
                                exit;
                            }

                            if ($user['role'] === 'superadmin') {
                                header("Location: ../superadmin/dashboard.php");
                                exit;
                            } elseif ($user['role'] === 'admin_lapangan') {
                                header("Location: ../admin/dashboard.php");
                                exit;
                            } else {
                                header("Location: ../index.php");
                                exit;
                            }
                        } else {
                            $error = "Email atau password salah!";
                        }
                    } else {
                        $error = "Email atau password salah!";
                    }
                } catch (Exception $e) {
                    $error = "Terjadi kesalahan sistem: " . $e->getMessage();
                }
            } else {
                $error = "Silakan isi semua kolom!";
            }
        }
        return $error;
    }

    public function handleRegister($postData) {
        $error = '';
        $success = '';

        if (isset($postData['register'])) {
            $name = trim($postData['name']);
            $email = trim($postData['email']);
            $phone = trim($postData['phone']);
            $password = trim($postData['password']);
            $role = isset($postData['role']) ? trim($postData['role']) : ''; 

            if (!empty($name) && !empty($email) && !empty($password) && !empty($role)) {
                try {
                    $this->pdo->beginTransaction();
                    
                    // Check if email exists
                    $existing = $this->userModel->getByEmail($email);

                    if ($existing) {
                        throw new Exception("Alamat email ini sudah terdaftar!");
                    }

                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $this->userModel->register($name, $email, $phone, $hashed_password, $role);
                    $user_id = $this->pdo->lastInsertId();

                    if ($role === 'admin_lapangan') {
                        $venue_name = trim($postData['venue_name'] ?? '');
                        $venue_location = trim($postData['venue_location'] ?? '');
                        
                        if (empty($venue_name) || empty($venue_location)) {
                            throw new Exception("Seluruh informasi detail data lapangan wajib diisi lengkap!");
                        }

                        $this->venueModel->createVenue($user_id, $venue_name, $venue_location, '', '', '', 'pending');
                        $success = "Pendaftaran Mitra Berhasil! Akun Anda sedang ditinjau oleh Superadmin.";
                    } else {
                        $success = "Pendaftaran Berhasil! Silakan masuk dengan akun Anda.";
                    }
                    
                    $this->pdo->commit();
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $error = "Terjadi kegagalan pendaftaran: " . $e->getMessage();
                }
            } else {
                $error = "Semua bidang bertanda wajib harus diisi!";
            }
        }
        
        return ['error' => $error, 'success' => $success];
    }
}
