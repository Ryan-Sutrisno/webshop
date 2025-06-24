<?php

class AuthController {
    private $user;
    
    public function __construct($pdo) {
        require_once __DIR__ . '/../models/User.php';
        $this->user = new User($pdo);
    }
    
    public function handleLogin() {
        // Check for logout request
        if (isset($_GET['logout'])) {
            $this->handleLogout();
            return;
        }

        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($this->user->login($username, $password)) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Ongeldige gebruikersnaam of wachtwoord';
            }
        }
        
        // Include the view
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function handleRegister() {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if (strlen($password) < 6) {
                $error = 'Wachtwoord moet minimaal 6 karakters bevatten';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ongeldig email adres';
            } else {
                if ($this->user->register($username, $password, $email)) {
                    $success = 'Registratie succesvol! U kunt nu inloggen.';
                } else {
                    $error = 'Gebruikersnaam of email is al in gebruik';
                }
            }
        }
        
        // Include the view
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    public function handleLogout() {
        $this->user->logout();
        header('Location: login.php');
        exit;
    }
} 