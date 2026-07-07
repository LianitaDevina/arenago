<?php
session_start();
include '../config/database.php';
require_once '../controllers/SuperadminController.php';

// Cek hak akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    die("Akses ilegal.");
}

// Mengecek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_msg'] = "Metode request tidak valid (Gunakan POST).";
    header("Location: dashboard.php");
    exit;
}

try {
    $controller = new SuperadminController($pdo);
    $controller->handleValidationAction($_POST);
} catch (Exception $e) {
    $_SESSION['flash_msg'] = "Error Database: " . $e->getMessage();
}

header("Location: dashboard.php");
exit;
?>