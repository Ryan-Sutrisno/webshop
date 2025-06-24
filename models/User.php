<?php

class User {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function register($username, $password, $email) {
        try {
            // Check if username or email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                return false; // User already exists
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            return $stmt->execute([$username, $hashedPassword, $email]);
        } catch (PDOException $e) {
            // Log error
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, password, email FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Start session and store user data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Log error
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getUserReservations($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM reserveringen 
                WHERE email = (SELECT email FROM users WHERE id = ?)
                ORDER BY datum DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get reservations error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserOrders($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, 
                       GROUP_CONCAT(CONCAT(br.aantal, 'x ', p.naam) SEPARATOR ', ') as producten
                FROM bestellingen b
                LEFT JOIN bestelregels br ON b.id = br.bestelling_id
                LEFT JOIN producten p ON br.product_id = p.id
                WHERE b.email = (SELECT email FROM users WHERE id = ?)
                GROUP BY b.id
                ORDER BY b.besteldatum DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get orders error: " . $e->getMessage());
            return [];
        }
    }
} 