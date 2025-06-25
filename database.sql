-- Aanmaken van de database (indien nodig)
CREATE DATABASE IF NOT EXISTS webshop;

-- Selecteer de database
USE webshop;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop bestaande tabellen (in juiste volgorde vanwege foreign keys)
DROP TABLE IF EXISTS winkelwagens;
DROP TABLE IF EXISTS bestelregels;
DROP TABLE IF EXISTS bestellingen;
DROP TABLE IF EXISTS reserveringen;
DROP TABLE IF EXISTS producten;
DROP TABLE IF EXISTS users;

-- Enable foreign key checks again
SET FOREIGN_KEY_CHECKS = 1;

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
    aantal_personen INT NOT NULL,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
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
    besteldatum TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
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

-- Winkelwagens tabel (optioneel, voor het bewaren van winkelwagens voor ingelogde gebruikers)
CREATE TABLE IF NOT EXISTS winkelwagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sessie_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    aantal INT NOT NULL,
    toegevoegd_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES producten(id)
);

-- Voeg voorbeeldproducten toe met categorieën
INSERT INTO producten (naam, prijs, beschrijving, categorie) VALUES
-- Voorgerechten
('Carpaccio', 14.50, 'Dun gesneden rundvlees met Parmezaanse kaas, pijnboompitten en truffelmayonaise', 'Voorgerechten'),
('Tomatensoep', 6.75, 'Huisgemaakte soep van verse tomaten en basilicum', 'Voorgerechten'),
('Caesar Salade', 12.95, 'Romaine sla met gegrilde kip, croutons, Parmezaanse kaas en Caesar dressing', 'Voorgerechten'),

-- Hoofdgerechten
('Burger Deluxe', 17.50, '200 gram rundvlees burger met cheddar, bacon, sla, tomaat en truffelmayonaise', 'Hoofdgerechten'),
('Pasta Carbonara', 16.75, 'Verse pasta met romige saus, pancetta, eigeel en Parmezaanse kaas', 'Hoofdgerechten'),
('Zalmfilet', 22.50, 'Op de huid gebakken zalmfilet met seizoensgroenten en hollandaisesaus', 'Hoofdgerechten'),
('Risotto', 18.95, 'Romige risotto met bospaddenstoelen en truffel', 'Hoofdgerechten'),

-- Desserts
('Tiramisu', 8.50, 'Klassiek Italiaans dessert met koffie, mascarpone en cacao', 'Desserts'),
('Crème Brûlée', 8.75, 'Vanille custard met een laagje gekarameliseerde suiker', 'Desserts'),
('Dame Blanche', 7.50, 'Vanille-ijs met warme chocoladesaus en slagroom', 'Desserts'),

-- Dranken
('Huiswijn Rood', 4.50, 'Glas rode huiswijn', 'Dranken'),
('Huiswijn Wit', 4.50, 'Glas witte huiswijn', 'Dranken'),
('Heineken', 3.50, 'Tap bier 25cl', 'Dranken'),
('Verse Jus', 3.75, 'Vers geperste sinaasappelsap', 'Dranken'); 