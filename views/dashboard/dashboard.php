<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/init.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /webshop/login');
    exit;
}

// Get user data
$username = $_SESSION['username'] ?? 'Unknown';
$user_id = $_SESSION['user_id'] ?? 0;

// Get user's orders
try {
    $orders = $db->getUserOrders($user_id);
} catch (PDOException $e) {
    $orders = [];
}

// Get user's reservations
try {
    $reservations = $db->getUserReservations($user_id);
} catch (PDOException $e) {
    $reservations = [];
}

// Include the dashboard view
include __DIR__ . '/index.php'; 