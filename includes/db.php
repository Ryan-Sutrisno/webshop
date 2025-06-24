<?php
class DB {
    private $connection = null;
    
    public function connect() {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        $username = "root";
        $password = "root"; // MAMP default password
        $dbname = "webshop";
        
        try {
            // Probeer eerst via TCP/IP
            try {
                $this->connection = new PDO(
                    "mysql:host=127.0.0.1;port=8889;dbname=$dbname",
                    $username, $password
                );
            } catch (PDOException $e) {
                // Als dat niet lukt, probeer via socket
                $this->connection = new PDO(
                    "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=$dbname",
                    $username, $password
                );
            }
            
            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            $this->connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );
            return $this->connection;
        }
        catch(PDOException $e) {
            die('Fout bij verbinden met database: ' . $e->getMessage());
        }
    }

    public function beginTransaction() {
        return $this->connect()->beginTransaction();
    }

    public function commit() {
        return $this->connect()->commit();
    }

    public function rollBack() {
        return $this->connect()->rollBack();
    }
    
    // Producten methoden
    public function getAllProducts() {
        $stmt = $this->connect()->prepare("SELECT * FROM producten ORDER BY naam");
        $stmt->execute();
        return $stmt;
    }

    public function getRandomProducts($limit = 3) {
        $stmt = $this->connect()->prepare("SELECT * FROM producten ORDER BY RAND() LIMIT :limit");
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getProduct($id) {
        $stmt = $this->connect()->prepare("SELECT * FROM producten WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function countProducts() {
        $stmt = $this->connect()->prepare("SELECT COUNT(*) FROM producten");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function addProduct($naam, $prijs, $beschrijving) {
        $stmt = $this->connect()->prepare("INSERT INTO producten (naam, prijs, beschrijving) VALUES (?, ?, ?)");
        $stmt->execute([$naam, $prijs, $beschrijving]);
        return $this->connect()->lastInsertId();
    }

    // Reserveringen methoden
    public function addReservation($naam, $email, $datum, $aantal_personen, $user_id = null) {
        $stmt = $this->connect()->prepare("INSERT INTO reserveringen (naam, email, datum, aantal_personen, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$naam, $email, $datum, $aantal_personen, $user_id]);
        return $this->connect()->lastInsertId();
    }

    public function getAllReservations() {
        $stmt = $this->connect()->prepare("SELECT * FROM reserveringen ORDER BY datum");
        $stmt->execute();
        return $stmt;
    }

    public function getUserReservations($user_id) {
        $stmt = $this->connect()->prepare("SELECT * FROM reserveringen WHERE user_id = ? ORDER BY datum DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Bestelling methoden
    public function addOrder($naam, $email, $telefoon, $adres, $postcode, $plaats, $totaalbedrag, $betaalwijze, $user_id = null) {
        $sql = "INSERT INTO bestellingen (naam, email, telefoon, adres, postcode, plaats, totaalbedrag, betaalwijze, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connect()->prepare($sql);
        
        // If user_id is not null, verify it exists
        if ($user_id !== null) {
            $userCheck = $this->connect()->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->execute([$user_id]);
            if (!$userCheck->fetch()) {
                $user_id = null; // Reset to null if user doesn't exist
            }
        }
        
        $stmt->execute([$naam, $email, $telefoon, $adres, $postcode, $plaats, $totaalbedrag, $betaalwijze, $user_id]);
        return $this->connect()->lastInsertId();
    }
    
    public function addOrderItem($bestelling_id, $product_id, $aantal, $prijs_per_stuk) {
        $stmt = $this->connect()->prepare("INSERT INTO bestelregels (bestelling_id, product_id, aantal, prijs_per_stuk) 
                                          VALUES (?, ?, ?, ?)");
        $stmt->execute([$bestelling_id, $product_id, $aantal, $prijs_per_stuk]);
        return $this->connect()->lastInsertId();
    }
    
    public function getOrder($id) {
        $stmt = $this->connect()->prepare("SELECT * FROM bestellingen WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }
    
    public function getOrderItems($bestelling_id) {
        $stmt = $this->connect()->prepare("SELECT br.*, p.naam FROM bestelregels br
                                          JOIN producten p ON br.product_id = p.id
                                          WHERE bestelling_id = :bestelling_id");
        $stmt->bindParam(":bestelling_id", $bestelling_id);
        $stmt->execute();
        return $stmt;
    }

    public function getUserOrders($user_id) {
        $stmt = $this->connect()->prepare("SELECT * FROM bestellingen WHERE user_id = ? ORDER BY besteldatum DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Winkelwagen methoden (voor persistente opslag, indien gewenst)
    public function saveCart($sessie_id, $product_id, $aantal) {
        // Controleer of item al in winkelwagen zit
        $stmt = $this->connect()->prepare("SELECT * FROM winkelwagens WHERE sessie_id = :sessie_id AND product_id = :product_id");
        $stmt->bindParam(":sessie_id", $sessie_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update bestaand item
            $stmt = $this->connect()->prepare("UPDATE winkelwagens SET aantal = :aantal WHERE sessie_id = :sessie_id AND product_id = :product_id");
            $stmt->bindParam(":aantal", $aantal);
            $stmt->bindParam(":sessie_id", $sessie_id);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->execute();
        } else {
            // Voeg nieuw item toe
            $stmt = $this->connect()->prepare("INSERT INTO winkelwagens (sessie_id, product_id, aantal) VALUES (?, ?, ?)");
            $stmt->execute([$sessie_id, $product_id, $aantal]);
        }
    }
    
    public function getCart($sessie_id) {
        $stmt = $this->connect()->prepare("SELECT w.*, p.naam, p.prijs, p.beschrijving FROM winkelwagens w
                                          JOIN producten p ON w.product_id = p.id
                                          WHERE sessie_id = :sessie_id");
        $stmt->bindParam(":sessie_id", $sessie_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function clearCart($sessie_id) {
        $stmt = $this->connect()->prepare("DELETE FROM winkelwagens WHERE sessie_id = :sessie_id");
        $stmt->bindParam(":sessie_id", $sessie_id);
        $stmt->execute();
    }

    // User-related methods
    public function createUser($username, $password, $email) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connect()->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $email]);
    }

    public function getUserByUsername($username) {
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getUserByEmail($email) {
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function verifyUser($username, $password) {
        $user = $this->getUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}

// Instantie maken voor compatibiliteit met bestaande code
$db = new DB();

// De oude PDO variabele behouden voor compatibiliteit met bestaande code
try {
    // Probeer eerst via TCP/IP
    try {
        $dbname = 'webshop';
        $user = 'root';
        $pass = 'root'; // MAMP default password
        $charset = 'utf8mb4';

        $dsn = "mysql:host=127.0.0.1;port=8889;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Als dat niet lukt, probeer via socket
        $dbname = 'webshop';
        $user = 'root';
        $pass = 'root'; // MAMP default password
        $charset = 'utf8mb4';

        $dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $user, $pass, $options);
    }
} catch (\PDOException $e) {
    die("Fout bij verbinden met database: " . $e->getMessage());
}
?> 