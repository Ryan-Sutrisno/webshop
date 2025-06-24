<?php
require_once __DIR__ . '/../../includes/db.php';

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
        // Check if username or email already exists
        if ($db->getUserByUsername($username)) {
            $error = 'Gebruikersnaam is al in gebruik';
        } elseif ($db->getUserByEmail($email)) {
            $error = 'Email is al in gebruik';
        } else {
            // Create new user
            try {
                if ($db->createUser($username, $password, $email)) {
                    $success = 'Registratie succesvol! U kunt nu inloggen.';
                } else {
                    $error = 'Er is een fout opgetreden bij het registreren.';
                }
            } catch (PDOException $e) {
                $error = 'Er is een fout opgetreden bij het registreren.';
                error_log($e->getMessage());
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-md mx-auto mt-8">
    <h2 class="text-2xl font-bold text-teal-700 mb-6">Registreren</h2>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label for="username" class="block text-gray-700 font-medium mb-2">Gebruikersnaam</label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   required 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-teal-500">
        </div>
        
        <div>
            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
            <input type="email" 
                   id="email" 
                   name="email" 
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
            <p class="text-sm text-gray-500 mt-1">Minimaal 6 karakters</p>
        </div>
        
        <button type="submit" 
                class="w-full bg-teal-600 text-white py-2 px-4 rounded-lg hover:bg-teal-700 transition duration-200">
            Registreren
        </button>
    </form>
    
    <div class="mt-6 text-center text-gray-600">
        Al een account? 
        <a href="/webshop/login" class="text-teal-600 hover:text-teal-700">
            Login hier
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 