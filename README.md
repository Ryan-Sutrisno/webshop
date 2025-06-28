# Restaurant Webshop met Winkelwagen, Checkout & Reserveringssysteem

Een volledige restaurant webshop applicatie ontwikkeld voor het portfolio-examen Software Developer (MBO niveau 4). Dit project demonstreert mijn vaardigheden in het ontwikkelen van webapplicaties met PHP, MySQL, en moderne frontend technieken.

## Functionaliteiten

- **Menukaart**: Overzicht van alle beschikbare gerechten
- **Winkelwagen**: Toevoegen, verwijderen en aanpassen van items
- **Checkout-systeem**: Bestelgegevens invullen en order plaatsen
- **Reserveringssysteem**: Online reserveren van een tafel

## Technische Specificaties

- PHP 7.4+
- MySQL database met PDO connectie
- Responsive design met Tailwind CSS
- JavaScript voor interactieve elementen
- Beveiliging tegen SQL-injectie
- Formuliervalidatie (client- en serverside)

## Installatie

1. Clone de repository naar je lokale omgeving
2. Importeer het `database.sql` bestand in je MySQL database
3. Pas eventueel de databasegegevens aan in `includes/db.php`
4. Start de applicatie via je lokale webserver

## Projectstructuur

```
webshop/
├── index.php                   # Homepage
├── cart.php                    # Winkelwagen
├── checkout.php                # Afrekenpagina
├── reserveer.php               # Reserveringspagina
├── confirm_reservation.php     # Verwerking reservering
├── includes/                   # Gedeelde componenten
│   ├── db.php                  # Database connectie
│   ├── header.php              # Header template
│   └── footer.php              # Footer template
├── producten/                  # Productgerelateerde pagina's
│   └── producten.php           # Menukaart
├── style/                      # CSS bestanden
│   └── style.css               # Custom styling
└── database.sql                # Database structuur
```

## Veiligheid

Dit project maakt gebruik van:
- Prepared statements (PDO) voor database queries
- Input validatie en sanitization
- CSRF-bescherming
- XSS-preventie door gebruik van htmlspecialchars()

## Portfolio-examenopdrachten

Dit project voldoet aan de volgende portfolio-examenopdrachten:

1. **Plant werkzaamheden en bewaakt de voortgang**
   - Gestructureerde directoryopbouw
   - Duidelijke codeorganisatie

2. **Ontwerp software**
   - Ontwikkeling van een gebruiksvriendelijke interface
   - Logisch gestructureerde applicatie-flow

3. **Realiseert (onderdelen van) software**
   - Volledig functionerende webshop
   - Implementatie van winkelwagenfunctionaliteit
   - Reserveringssysteem

4. **Test software**
   - Validatie van gebruikersinvoer
   - Foutafhandeling en -weergave

5. **Doet verbetervoorstellen voor de software**
   - Responsive design voor verschillende apparaten
   - Gebruiksvriendelijke interface

## Toekomstige verbeteringen

- Admin-paneel voor beheer van producten en reserveringen
- Betaalgateway-integratie
- E-mailbevestigingen voor bestellingen en reserveringen
- Gebruikersaccounts en ordergeschiedenis

## Auteur

[Jouw Naam]

---

© 2023 Restaurant Webshop | Portfolio-examen Software Developer 