<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();

// Controleer of het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect naar reserveringspagina als niet via POST
    header('Location: /webshop/reserveer');
    exit;
}

// Valideer en verwerk de invoer
$errors = [];

// Valideer naam
if (empty($_POST['naam'])) {
    $errors[] = 'Naam is verplicht';
} else {
    $naam = trim($_POST['naam']);
}

// Valideer email
if (empty($_POST['email'])) {
    $errors[] = 'E-mail is verplicht';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Ongeldig e-mailadres';
} else {
    $email = trim($_POST['email']);
}

// Valideer telefoon (eenvoudige validatie)
if (empty($_POST['telefoon'])) {
    $errors[] = 'Telefoonnummer is verplicht';
} else {
    $telefoon = trim($_POST['telefoon']);
}

// Valideer datum en tijd
if (empty($_POST['datum']) || empty($_POST['tijd'])) {
    $errors[] = 'Datum en tijd zijn verplicht';
} else {
    $date = trim($_POST['datum']);
    $time = trim($_POST['tijd']);
    
    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Ongeldige datumformaat. Gebruik YYYY-MM-DD.';
    }
    
    // Validate time format (HH:MM)
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        $errors[] = 'Ongeldige tijdformaat. Gebruik HH:MM.';
    }
    
    if (empty($errors)) {
        try {
            // Create DateTime object with validated date and time
            $reserveringsDatum = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            
            if ($reserveringsDatum === false) {
                $errors[] = 'Ongeldige datum of tijd.';
            } else {
                $nu = new DateTime();
                
                if ($reserveringsDatum <= $nu) {
                    $errors[] = 'Reserveringsdatum moet in de toekomst liggen';
                } else {
                    // Formatteren als datetime voor MySQL
                    $datum = $reserveringsDatum->format('Y-m-d H:i:s');
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Er is een fout opgetreden bij het verwerken van de datum.';
        }
    }
}

// Valideer aantal personen
if (empty($_POST['aantal_personen']) || !is_numeric($_POST['aantal_personen'])) {
    $errors[] = 'Aantal personen is verplicht';
} elseif ($_POST['aantal_personen'] < 1 || $_POST['aantal_personen'] > 8) {
    $errors[] = 'Aantal personen moet tussen 1 en 8 zijn';
} else {
    $aantal_personen = (int)$_POST['aantal_personen'];
}

// Opmerkingen zijn optioneel
$opmerkingen = isset($_POST['opmerkingen']) ? trim($_POST['opmerkingen']) : '';

// Als er fouten zijn, toon die dan
if (!empty($errors)) {
    include __DIR__ . '/../../includes/header.php';
    ?>
    <div class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold text-red-600 mb-6">Er is een probleem met je reservering</h2>
            
            <div class="bg-red-100 text-red-600 p-4 rounded mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <a href="javascript:history.back()" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded inline-block">
                Terug naar reserveringsformulier
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

// Als er geen fouten zijn, sla de reservering op in de database
try {
    // Get user_id if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Gebruik de addReservation methode van de DB-klasse
    $db->addReservation($naam, $email, $datum, $aantal_personen, $user_id);
    
    // Stuur bevestigingsmail
    try {
        $mail = new PHPMailer(true);
        
        // Server instellingen
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Ontvangers
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $naam);
        $mail->addBCC(SMTP_FROM_EMAIL);
        
        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Reserveringsbevestiging - Restaurant Deluxe";
        
        // E-mail inhoud
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; }
                h1 { color: #2c7a7b; }
                .details { background-color: #f8f8f8; padding: 20px; margin: 20px 0; }
                .footer { margin-top: 30px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Bedankt voor uw reservering!</h1>
                <p>Beste " . htmlspecialchars($naam) . ",</p>
                <p>We hebben uw reservering succesvol ontvangen en bevestigen deze graag.</p>
                
                <div class='details'>
                    <h2>Reserveringsdetails:</h2>
                    <p>
                        <strong>Datum:</strong> " . date('d-m-Y', strtotime($datum)) . "<br>
                        <strong>Tijd:</strong> " . date('H:i', strtotime($datum)) . "<br>
                        <strong>Aantal personen:</strong> " . $aantal_personen . "<br>
                        " . ($opmerkingen ? "<strong>Opmerkingen:</strong> " . htmlspecialchars($opmerkingen) . "<br>" : "") . "
                    </p>
                </div>
                
                <p>We kijken ernaar uit u te mogen verwelkomen in ons restaurant!</p>
                
                <div class='footer'>
                    <p><strong>Restaurant Deluxe</strong><br>
                    Restaurantstraat 1<br>
                    1234 AB Amsterdam<br>
                    Tel: 020-1234567</p>
                    
                    <p>Dit is een automatisch gegenereerde e-mail. U kunt deze e-mail niet beantwoorden.</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $emailBody;
        
        $mail->send();
        
    } catch (Exception $e) {
        error_log("Fout bij verzenden reserveringsbevestiging: " . $e->getMessage());
    }
    
    // Redirect naar reserveringspagina met succesbericht
    header('Location: /webshop/reserveer?success=1');
    exit;
} catch (PDOException $e) {
    // Log de fout (in een echte applicatie)
    // error_log($e->getMessage());
    
    include __DIR__ . '/../../includes/header.php';
    ?>
    <div class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold text-red-600 mb-6">Er is een fout opgetreden</h2>
            
            <div class="bg-red-100 text-red-600 p-4 rounded mb-6">
                <p>Er is een fout opgetreden bij het opslaan van je reservering. Probeer het later opnieuw of neem telefonisch contact met ons op.</p>
            </div>
            
            <a href="/webshop/reserveer" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded inline-block">
                Terug naar reserveringspagina
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?> 