<?php
// Start sessie voor alle tests
if (session_status() == PHP_SESSION_NONE) {
    @session_start();
}

use PHPUnit\Framework\TestCase;

class WebshopTest extends TestCase
{
    private $pdo;
    private $db;

    protected function setUp(): void
    {
        // Database connectie voor tests
        $this->pdo = new PDO(
            'mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=webshop_test',
            'root',
            'root',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Reset sessie voor elke test
        $_SESSION = [];
        
        // Maak een schone test database voor elke test
        $this->pdo->exec('DROP TABLE IF EXISTS bestelregels');
        $this->pdo->exec('DROP TABLE IF EXISTS winkelwagens');
        $this->pdo->exec('DROP TABLE IF EXISTS bestellingen');
        $this->pdo->exec('DROP TABLE IF EXISTS reserveringen');
        $this->pdo->exec('DROP TABLE IF EXISTS producten');
        $this->pdo->exec('DROP TABLE IF EXISTS users');
        
        // Voer database_test.sql uit om tabellen aan te maken
        $sql = file_get_contents(__DIR__ . '/../database_test.sql');
        $this->pdo->exec($sql);
    }

    protected function tearDown(): void
    {
        // Reset sessie na elke test
        $_SESSION = [];
    }

    /**
     * Test 1: Registratie van een nieuwe gebruiker
     */
    public function testUserRegistration()
    {
        $username = 'testuser';
        $password = 'test123';
        $email = 'test@example.com';
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)"
        );
        
        $result = $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email]);
        
        $this->assertTrue($result);
        
        // Controleer of de gebruiker bestaat
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $this->assertEquals($email, $user['email']);
    }

    /**
     * Test 2: Inloggen met correcte gegevens
     */
    public function testUserLogin()
    {
        // Maak eerst een gebruiker aan
        $username = 'logintest';
        $password = 'login123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, password, email) VALUES (?, ?, ?)"
        );
        $stmt->execute([$username, $hashedPassword, 'login@test.com']);
        
        // Test login
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $this->assertTrue(password_verify($password, $user['password']));
    }

    /**
     * Test 3: Product toevoegen aan winkelwagen
     */
    public function testAddToCart()
    {
        // Voeg een product toe
        $productId = 1;
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][$productId] = 1;
        
        $this->assertArrayHasKey($productId, $_SESSION['cart']);
        $this->assertEquals(1, $_SESSION['cart'][$productId]);
    }

    /**
     * Test 4: Reservering maken
     */
    public function testCreateReservation()
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reserveringen (naam, email, datum, aantal_personen) 
             VALUES (?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            'Test Persoon',
            'reservering@test.com',
            '2024-03-20 19:00:00',
            4
        ]);
        
        $this->assertTrue($result);
        
        // Controleer of de reservering bestaat
        $stmt = $this->pdo->prepare("SELECT * FROM reserveringen WHERE email = ?");
        $stmt->execute(['reservering@test.com']);
        $reservering = $stmt->fetch();
        
        $this->assertEquals(4, $reservering['aantal_personen']);
    }

    /**
     * Test 5: Product categorieën ophalen
     */
    public function testGetProductCategories()
    {
        // Voeg test producten toe
        $stmt = $this->pdo->prepare(
            "INSERT INTO producten (naam, prijs, beschrijving, categorie) 
             VALUES (?, ?, ?, ?)"
        );
        
        $testProducts = [
            ['Soep', 5.95, 'Tomatensoep', 'Voorgerechten'],
            ['Steak', 24.95, 'Biefstuk', 'Hoofdgerechten'],
            ['IJs', 6.95, 'Vanille ijs', 'Desserts']
        ];
        
        foreach ($testProducts as $product) {
            $stmt->execute($product);
        }
        
        // Haal categorieën op
        $stmt = $this->pdo->query("SELECT DISTINCT categorie FROM producten");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $this->assertCount(3, $categories);
        $this->assertContains('Voorgerechten', $categories);
        $this->assertContains('Hoofdgerechten', $categories);
        $this->assertContains('Desserts', $categories);
    }

    /**
     * Test 6: Bestelling plaatsen
     */
    public function testCreateOrder()
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO bestellingen (naam, email, telefoon, adres, postcode, plaats, totaalbedrag, betaalwijze)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            'Test Klant',
            'bestelling@test.com',
            '0612345678',
            'Teststraat 1',
            '1234AB',
            'Amsterdam',
            29.95,
            'ideal'
        ]);
        
        $this->assertTrue($result);
        $orderId = $this->pdo->lastInsertId();
        
        // Controleer of de bestelling bestaat
        $stmt = $this->pdo->prepare("SELECT * FROM bestellingen WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        $this->assertEquals(29.95, $order['totaalbedrag']);
    }

    /**
     * Test 7: Winkelwagen totaal berekenen
     */
    public function testCalculateCartTotal()
    {
        // Voeg producten toe aan de database
        $stmt = $this->pdo->prepare(
            "INSERT INTO producten (naam, prijs, beschrijving, categorie) 
             VALUES (?, ?, ?, ?)"
        );
        
        $products = [
            ['Product 1', 10.00, 'Test 1', 'Test'],
            ['Product 2', 20.00, 'Test 2', 'Test']
        ];
        
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        
        // Simuleer winkelwagen
        $_SESSION['cart'] = [
            1 => 2, // 2x Product 1
            2 => 1  // 1x Product 2
        ];
        
        // Bereken totaal
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $quantity) {
            $stmt = $this->pdo->prepare("SELECT prijs FROM producten WHERE id = ?");
            $stmt->execute([$id]);
            $price = $stmt->fetchColumn();
            $total += $price * $quantity;
        }
        
        $this->assertEquals(40.00, $total); // (10.00 * 2) + (20.00 * 1)
    }

    /**
     * Test 8: Controleer beschikbaarheid reservering
     */
    public function testReservationAvailability()
    {
        // Voeg een bestaande reservering toe
        $stmt = $this->pdo->prepare(
            "INSERT INTO reserveringen (naam, email, datum, aantal_personen)
             VALUES (?, ?, ?, ?)"
        );
        
        $datum = '2024-03-20 19:00:00';
        $stmt->execute(['Bestaand', 'bestaand@test.com', $datum, 4]);
        
        // Tel aantal reserveringen op die datum
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM reserveringen 
             WHERE datum BETWEEN ? AND DATE_ADD(?, INTERVAL 2 HOUR)"
        );
        $stmt->execute([$datum, $datum]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(1, $count);
    }

    /**
     * Test 9: Product zoeken
     */
    public function testSearchProducts()
    {
        // Voeg test producten toe
        $stmt = $this->pdo->prepare(
            "INSERT INTO producten (naam, prijs, beschrijving, categorie)
             VALUES (?, ?, ?, ?)"
        );
        
        $products = [
            ['Pasta Carbonara', 15.95, 'Romige pasta', 'Hoofdgerechten'],
            ['Pizza Margherita', 12.95, 'Klassieke pizza', 'Hoofdgerechten'],
            ['Tiramisu', 6.95, 'Italiaans dessert', 'Desserts']
        ];
        
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        
        // Zoek producten met 'pasta'
        $stmt = $this->pdo->prepare(
            "SELECT * FROM producten WHERE naam LIKE ? OR beschrijving LIKE ?"
        );
        $searchTerm = '%pasta%';
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = $stmt->fetchAll();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Pasta Carbonara', $results[0]['naam']);
    }

    /**
     * Test 10: Gebruiker uitloggen
     */
    public function testUserLogout()
    {
        // Simuleer een ingelogde gebruiker
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['logged_in'] = true;
        
        // Test uitloggen
        $_SESSION = [];
        
        $this->assertArrayNotHasKey('user_id', $_SESSION);
        $this->assertArrayNotHasKey('username', $_SESSION);
        $this->assertArrayNotHasKey('logged_in', $_SESSION);
    }
} 