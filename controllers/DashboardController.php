<?php

class DashboardController {
    private $user;
    
    public function __construct($db) {
        require_once __DIR__ . '/../models/User.php';
        $this->user = new User($db);
        
        // Check if user is logged in
        if (!$this->user->isLoggedIn()) {
            header('Location: /webshop/views/auth/login.php');
            exit;
        }
    }
    
    public function showDashboard() {
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        
        // Haal bestellingen en reserveringen op
        $orders = $this->user->getUserOrders($userId);
        $reservations = $this->user->getUserReservations($userId);
        
        // Include the view
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
} 