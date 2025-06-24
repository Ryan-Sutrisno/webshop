<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-teal-700 mb-6">Afrekenen</h2>
    
    <?php
    require_once __DIR__ . '/../../includes/db.php';
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../includes/mail_config.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;
    
    // Controleer of er items in de winkelwagen zitten
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
        <div class="bg-yellow-50 p-6 rounded-lg text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-yellow-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-xl font-semibold mb-2">Je winkelwagen is leeg</h3>
            <p class="text-gray-600 mb-4">Voeg producten toe aan je winkelwagen om te bestellen.</p>
            <a href="/webshop/menu" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded inline-block">Bekijk menu</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Linker kolom - Bestelgegevens -->
            <div>
                <h3 class="text-xl font-semibold mb-4">Bestelgegevens</h3>
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <table class="w-full">
                        <?php
                        $totaal = 0;
                        
                        foreach ($_SESSION['cart'] as $id => $quantity):
                            // Haal product informatie op
                            $productStmt = $db->getProduct($id);
                            $product = $productStmt->fetch();
                            
                            if ($product):
                                $subtotaal = $product['prijs'] * $quantity;
                                $totaal += $subtotaal;
                                ?>
                                <tr class="border-b">
                                    <td class="py-2"><?= $quantity ?>x <?= htmlspecialchars($product['naam']) ?></td>
                                    <td class="py-2 text-right">€<?= number_format($subtotaal, 2, ',', '.') ?></td>
                                </tr>
                            <?php endif;
                        endforeach; ?>
                        
                        <tr class="font-semibold">
                            <td class="py-2">Totaal:</td>
                            <td class="py-2 text-right">€<?= number_format($totaal, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="/webshop/cart" class="text-teal-600 hover:text-teal-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Terug naar winkelwagen
                    </a>
                </div>
            </div>
            
            <!-- Rechter kolom - Klantgegevens -->
            <div>
                <h3 class="text-xl font-semibold mb-4">Vul je gegevens in</h3>
                
                <?php
                // Verwerk formulier
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Valideer input
                    $errors = [];
                    $naam = trim($_POST['naam'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $telefoon = trim($_POST['telefoon'] ?? '');
                    $adres = trim($_POST['adres'] ?? '');
                    $postcode = trim($_POST['postcode'] ?? '');
                    $plaats = trim($_POST['plaats'] ?? '');
                    $betaalwijze = trim($_POST['betaalwijze'] ?? '');
                    
                    if (empty($naam)) {
                        $errors[] = 'Naam is verplicht';
                    }
                    
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Geldig e-mailadres is verplicht';
                    }
                    
                    if (empty($telefoon)) {
                        $errors[] = 'Telefoonnummer is verplicht';
                    }
                    
                    if (empty($adres)) {
                        $errors[] = 'Adres is verplicht';
                    }
                    
                    if (empty($postcode)) {
                        $errors[] = 'Postcode is verplicht';
                    }
                    
                    if (empty($plaats)) {
                        $errors[] = 'Plaats is verplicht';
                    }
                    
                    if (empty($betaalwijze)) {
                        $errors[] = 'Betaalwijze is verplicht';
                    }
                    
                    if (empty($errors)): 
                        try {
                            // Start transaction
                            $db->beginTransaction();
                            
                            // Get user_id if logged in
                            $user_id = null;
                            if (isset($_SESSION['user_id'])) {
                                // Verify user exists before using their ID
                                $user = $db->getUserByUsername($_SESSION['username']);
                                if ($user) {
                                    $user_id = $user['id'];
                                }
                            }
                            
                            // Sla de bestelling op
                            $bestelling_id = $db->addOrder(
                                $naam, 
                                $email, 
                                $telefoon, 
                                $adres, 
                                $postcode, 
                                $plaats, 
                                $totaal, 
                                $betaalwijze,
                                $user_id
                            );
                            
                            // Sla de bestelregels op
                            foreach ($_SESSION['cart'] as $id => $quantity) {
                                $productStmt = $db->getProduct($id);
                                $product = $productStmt->fetch();
                                
                                if ($product) {
                                    $db->addOrderItem($bestelling_id, $id, $quantity, $product['prijs']);
                                }
                            }
                            
                            // Commit de transactie
                            $db->commit();
                            
                            // Stuur een bevestigingsmail
                            $onderwerp = "Bestelbevestiging #" . $bestelling_id . " - Restaurant Deluxe";
                            
                            // E-mail inhoud
                            $bericht = "
                            <html>
                            <head>
                                <title>Bestelbevestiging</title>
                                <style>
                                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                    .container { max-width: 600px; margin: 0 auto; }
                                    h1 { color: #2c7a7b; }
                                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                                    th { background-color: #f2f2f2; }
                                    .total { font-weight: bold; }
                                    .footer { margin-top: 30px; font-size: 12px; color: #777; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <h1>Bedankt voor je bestelling!</h1>
                                    <p>Beste " . htmlspecialchars($naam) . ",</p>
                                    <p>We hebben je bestelling ontvangen en verwerken deze zo snel mogelijk.</p>
                                    
                                    <h2>Bestelnummer: #" . $bestelling_id . "</h2>
                                    
                                    <h3>Jouw gegevens:</h3>
                                    <p>
                                        " . htmlspecialchars($naam) . "<br>
                                        " . htmlspecialchars($adres) . "<br>
                                        " . htmlspecialchars($postcode) . " " . htmlspecialchars($plaats) . "<br>
                                        Tel: " . htmlspecialchars($telefoon) . "
                                    </p>
                                    
                                    <h3>Bestelde items:</h3>
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>Aantal</th>
                                            <th>Prijs</th>
                                            <th>Subtotaal</th>
                                        </tr>";
                            
                            // Voeg bestellingen toe aan de e-mail
                            foreach ($_SESSION['cart'] as $id => $quantity) {
                                $productStmt = $db->getProduct($id);
                                $product = $productStmt->fetch();
                                
                                if ($product) {
                                    $subtotaal = $product['prijs'] * $quantity;
                                    $bericht .= "
                                        <tr>
                                            <td>" . htmlspecialchars($product['naam']) . "</td>
                                            <td>" . $quantity . "</td>
                                            <td>€" . number_format($product['prijs'], 2, ',', '.') . "</td>
                                            <td>€" . number_format($subtotaal, 2, ',', '.') . "</td>
                                        </tr>";
                                }
                            }
                            
                            $bericht .= "
                                        <tr class='total'>
                                            <td colspan='3'>Totaal:</td>
                                            <td>€" . number_format($totaal, 2, ',', '.') . "</td>
                                        </tr>
                                    </table>
                                    
                                    <p>Betaalwijze: " . htmlspecialchars($betaalwijze) . "</p>
                                    
                                    <p>We hopen je binnenkort weer te zien bij Restaurant Deluxe!</p>
                                    
                                    <div class='footer'>
                                        <p>Dit is een automatisch gegenereerde e-mail. Beantwoord deze e-mail niet.</p>
                                        <p>Bij vragen over je bestelling kun je contact opnemen via " . SMTP_FROM_EMAIL . " of 0612345678.</p>
                                    </div>
                                </div>
                            </body>
                            </html>";
                            
                            // Verstuur e-mail met PHPMailer
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
                                $mail->Subject = $onderwerp;
                                $mail->Body = $bericht;
                                $mail->CharSet = 'UTF-8';
                                
                                $mail->send();
                                
                            } catch (Exception $mailError) {
                                error_log('E-mail versturen mislukt: ' . $mailError->getMessage());
                            }
                            
                            // Toon bevestiging
                            ?>
                            <div class="bg-green-100 text-green-600 p-4 rounded mb-4">
                                <h4 class="font-semibold mb-2">Bestelling geplaatst!</h4>
                                <p>Bedankt voor je bestelling, <?= htmlspecialchars($naam) ?>!</p>
                                <p>We hebben een bevestiging gestuurd naar <?= htmlspecialchars($email) ?>.</p>
                                <p class="mt-2">Je bestelling wordt zo snel mogelijk bereid en bezorgd op het opgegeven adres.</p>
                                <p class="mt-2">Bestelnummer: <strong><?= $bestelling_id ?></strong></p>
                            </div>
                            
                            <?php
                            // Reset winkelwagen
                            $_SESSION['cart'] = [];
                            ?>
                            
                            <a href="/webshop/" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded inline-block mt-4">Terug naar homepagina</a>
                            <?php
                        } catch (Exception $e) {
                            // Rollback bij fouten
                            $db->rollBack();
                            ?>
                            <div class="bg-red-100 text-red-600 p-4 rounded mb-4">
                                <h4 class="font-semibold mb-2">Er is een fout opgetreden</h4>
                                <p>Er is een probleem bij het verwerken van je bestelling. Probeer het later opnieuw.</p>
                                <?php if(in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])): // Alleen foutdetails tonen op lokale omgeving ?>
                                    <p class="mt-2 text-sm"><?= $e->getMessage() ?></p>
                                    <p class="mt-1 text-xs"><?= $e->getTraceAsString() ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php displayForm($naam, $email, $telefoon, $adres, $postcode, $plaats); ?>
                            <?php
                        }
                    else: ?>
                        <!-- Toon errors -->
                        <div class="bg-red-100 text-red-600 p-4 rounded mb-4">
                            <ul class="list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <?php displayForm($naam, $email, $telefoon, $adres, $postcode, $plaats); ?>
                    <?php endif;
                } else {
                    // Toon formulier
                    displayForm();
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Functie om formulier te tonen
function displayForm($naam = '', $email = '', $telefoon = '', $adres = '', $postcode = '', $plaats = '') {
    ?>
    <form method="post" action="/webshop/checkout" class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="naam" class="block text-gray-700 font-medium mb-1">Naam</label>
                <input type="text" id="naam" name="naam" value="<?= htmlspecialchars($naam) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
            
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1">E-mail</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
            
            <div>
                <label for="telefoon" class="block text-gray-700 font-medium mb-1">Telefoonnummer</label>
                <input type="tel" id="telefoon" name="telefoon" value="<?= htmlspecialchars($telefoon) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
            
            <div class="md:col-span-2">
                <label for="adres" class="block text-gray-700 font-medium mb-1">Adres</label>
                <input type="text" id="adres" name="adres" value="<?= htmlspecialchars($adres) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
            
            <div>
                <label for="postcode" class="block text-gray-700 font-medium mb-1">Postcode</label>
                <input type="text" id="postcode" name="postcode" value="<?= htmlspecialchars($postcode) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
            
            <div>
                <label for="plaats" class="block text-gray-700 font-medium mb-1">Plaats</label>
                <input type="text" id="plaats" name="plaats" value="<?= htmlspecialchars($plaats) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>
        </div>
        
        <div class="mt-6">
            <h4 class="font-semibold mb-2">Betaalwijze</h4>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="radio" name="betaalwijze" value="ideal" checked class="mr-2">
                    iDEAL
                </label>
                <label class="flex items-center">
                    <input type="radio" name="betaalwijze" value="creditcard" class="mr-2">
                    Creditcard
                </label>
                <label class="flex items-center">
                    <input type="radio" name="betaalwijze" value="contant" class="mr-2">
                    Contant (bij bezorging)
                </label>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded">Bestelling plaatsen</button>
        </div>
        
    </form>
    <?php
}
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 