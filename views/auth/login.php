<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/init.php';


// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /webshop/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Get user first
        $user = $db->getUserByUsername($username);
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Clear any existing session data
                session_unset();
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard
                header('Location: /webshop/dashboard');
                exit;
            } else {
                $error = 'Ongeldige gebruikersnaam of wachtwoord';
            }
        } else {
            $error = 'Ongeldige gebruikersnaam of wachtwoord';
        }
    } catch (PDOException $e) {
        $error = 'Er is een fout opgetreden bij het inloggen';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-md mx-auto mt-8">
    <h2 class="text-2xl font-bold text-teal-700 mb-6">Inloggen</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/webshop/login" class="space-y-4">
        <div>
            <label for="username" class="block text-gray-700 font-medium mb-2">Gebruikersnaam</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-teal-500">
        </div>
        
        <div>
            <label for="password" class="block text-gray-700 font-medium mb-2">Wachtwoord</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-teal-500">
        </div>
        
        <button type="submit" 
                class="w-full bg-teal-600 text-white py-2 px-4 rounded-lg hover:bg-teal-700 transition duration-200">
            Inloggen
        </button>
    </form>
    
    <div class="mt-6 text-center text-gray-600">
        Nog geen account? 
        <a href="/webshop/register" class="text-teal-600 hover:text-teal-700">
            Registreer hier
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 