<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class ProductTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        try {
            // First connect without database to create it if needed
            $user = 'root';
            $pass = 'root'; // MAMP default password
            
            $dsn = "mysql:host=127.0.0.1;port=8889";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS webshop");
            
            // Now connect to the database
            $dbname = 'webshop';
            $charset = 'utf8mb4';
            $dsn = "mysql:host=127.0.0.1;port=8889;dbname=$dbname;charset=$charset";
            
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            
            // Create tables if they don't exist
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS producten (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    naam VARCHAR(255) NOT NULL,
                    prijs DECIMAL(10, 2) NOT NULL,
                    beschrijving TEXT
                )
            ");
            
        } catch (PDOException $e) {
            // If TCP/IP fails, try socket connection
            try {
                // First connect without database
                $dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock";
                $pdo = new PDO($dsn, $user, $pass, $options);
                
                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS webshop");
                
                // Now connect to the database
                $dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=$dbname;charset=$charset";
                $this->pdo = new PDO($dsn, $user, $pass, $options);
                
                // Create tables if they don't exist
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS producten (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        naam VARCHAR(255) NOT NULL,
                        prijs DECIMAL(10, 2) NOT NULL,
                        beschrijving TEXT
                    )
                ");
            } catch (PDOException $e2) {
                throw new PDOException("Could not connect via TCP/IP or socket: " . $e->getMessage() . " and " . $e2->getMessage());
            }
        }

        // Clean up any test data
        $this->pdo->exec("DELETE FROM producten WHERE naam = 'Test Product'");
    }

    public function testDatabaseConnection()
    {
        $this->assertTrue($this->pdo instanceof PDO, "Database connection should be established");
        
        // Try to select from the producten table
        $stmt = $this->pdo->query("SELECT * FROM producten LIMIT 1");
        $this->assertTrue($stmt !== false, "Should be able to query the producten table");
    }

    public function testCanCreateProduct()
    {
        // Test product toevoegen
        $stmt = $this->pdo->prepare("INSERT INTO producten (naam, prijs, beschrijving) VALUES (?, ?, ?)");
        $result = $stmt->execute(['Test Product', 9.99, 'Test beschrijving']);
        
        $this->assertTrue($result);
        
        // Controleer of het product is toegevoegd
        $stmt = $this->pdo->query("SELECT * FROM producten WHERE naam = 'Test Product'");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($product);
        $this->assertEquals('Test Product', $product['naam']);
        $this->assertEquals(9.99, (float)$product['prijs']);
    }

    public function testCanUpdateProduct()
    {
        // First create a test product
        $stmt = $this->pdo->prepare("INSERT INTO producten (naam, prijs, beschrijving) VALUES (?, ?, ?)");
        $stmt->execute(['Test Product', 9.99, 'Test beschrijving']);
        
        // Test product updaten
        $stmt = $this->pdo->prepare("UPDATE producten SET prijs = ? WHERE naam = ?");
        $result = $stmt->execute([19.99, 'Test Product']);
        
        $this->assertTrue($result);
        
        // Controleer of de prijs is geüpdatet
        $stmt = $this->pdo->query("SELECT * FROM producten WHERE naam = 'Test Product'");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(19.99, (float)$product['prijs']);
    }

    public function testCanDeleteProduct()
    {
        // First create a test product
        $stmt = $this->pdo->prepare("INSERT INTO producten (naam, prijs, beschrijving) VALUES (?, ?, ?)");
        $stmt->execute(['Test Product', 9.99, 'Test beschrijving']);
        
        // Test product verwijderen
        $stmt = $this->pdo->prepare("DELETE FROM producten WHERE naam = ?");
        $result = $stmt->execute(['Test Product']);
        
        $this->assertTrue($result);
        
        // Controleer of het product is verwijderd
        $stmt = $this->pdo->query("SELECT * FROM producten WHERE naam = 'Test Product'");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertFalse($product);
    }

    protected function tearDown(): void
    {
        // Clean up any remaining test products
        $this->pdo->exec("DELETE FROM producten WHERE naam = 'Test Product'");
    }
} 