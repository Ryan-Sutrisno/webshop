-- Drop bestaande tabellen (in juiste volgorde vanwege foreign keys)
DROP TABLE IF EXISTS bestelregels;
DROP TABLE IF EXISTS winkelwagens;
DROP TABLE IF EXISTS bestellingen;
DROP TABLE IF EXISTS reserveringen;
DROP TABLE IF EXISTS producten;
DROP TABLE IF EXISTS users;

-- Gebruikers tabel
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Producten tabel
CREATE TABLE IF NOT EXISTS producten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(255) NOT NULL,
    prijs DECIMAL(10, 2) NOT NULL,
    beschrijving TEXT,
    categorie VARCHAR(50) NOT NULL DEFAULT 'Overig'
);

-- Reserveringen tabel
CREATE TABLE IF NOT EXISTS reserveringen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    datum DATETIME NOT NULL,
    aantal_personen INT NOT NULL
);

-- Bestellingen tabel
CREATE TABLE IF NOT EXISTS bestellingen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefoon VARCHAR(20) NOT NULL,
    adres VARCHAR(255) NOT NULL,
    postcode VARCHAR(10) NOT NULL,
    plaats VARCHAR(100) NOT NULL,
    totaalbedrag DECIMAL(10, 2) NOT NULL,
    status ENUM('nieuw', 'in_behandeling', 'verzonden', 'afgerond') DEFAULT 'nieuw',
    betaalwijze VARCHAR(50) NOT NULL,
    besteldatum TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bestelregels tabel (items in een bestelling)
CREATE TABLE IF NOT EXISTS bestelregels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bestelling_id INT NOT NULL,
    product_id INT NOT NULL,
    aantal INT NOT NULL,
    prijs_per_stuk DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (bestelling_id) REFERENCES bestellingen(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES producten(id)
);

-- Winkelwagens tabel
CREATE TABLE IF NOT EXISTS winkelwagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sessie_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    aantal INT NOT NULL,
    toegevoegd_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES producten(id)
); 