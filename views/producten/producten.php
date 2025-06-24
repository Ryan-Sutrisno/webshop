<?php include '../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-teal-700">Onze menukaart</h2>
        <a href="../cart.php" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Naar winkelwagen
        </a>
    </div>
    
    <?php
    require_once '../includes/db.php';
    
    // Controleer of er producten in de database zitten
    $count = $db->countProducts();
    
    // Als er geen producten zijn, voeg dan wat testdata toe
    if ($count == 0) {
        $testProducts = [
            ['Carpaccio', 14.50, 'Dun gesneden rundvlees met Parmezaanse kaas, pijnboompitten en truffelmayonaise'],
            ['Tomatensoep', 6.75, 'Huisgemaakte soep van verse tomaten en basilicum'],
            ['Caesar Salade', 12.95, 'Romaine sla met gegrilde kip, croutons, Parmezaanse kaas en Caesar dressing'],
            ['Burger Deluxe', 17.50, '200 gram rundvlees burger met cheddar, bacon, sla, tomaat en truffelmayonaise'],
            ['Pasta Carbonara', 16.75, 'Verse pasta met romige saus, pancetta, eigeel en Parmezaanse kaas'],
            ['Zalmfilet', 22.50, 'Op de huid gebakken zalmfilet met seizoensgroenten en hollandaisesaus'],
            ['Risotto', 18.95, 'Romige risotto met bospaddenstoelen en truffel'],
            ['Tiramisu', 8.50, 'Klassiek Italiaans dessert met koffie, mascarpone en cacao'],
            ['Crème Brûlée', 8.75, 'Vanille custard met een laagje gekarameliseerde suiker']
        ];
        
        foreach ($testProducts as $product) {
            $db->addProduct($product[0], $product[1], $product[2]);
        }
        ?>
        <div class="bg-green-100 text-green-600 p-4 rounded mb-4">Testproducten toegevoegd aan de database.</div>
        <?php
    }
    
    // Haal alle producten op
    $productenStmt = $db->getAllProducts();
    $producten = $productenStmt->fetchAll();
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($producten as $product): ?>
        <div class="bg-gray-50 p-5 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition">
            <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['naam']) ?></h3>
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['beschrijving']) ?></p>
            <div class="flex justify-between items-center">
                <span class="text-teal-600 font-bold">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                <a href="?add=<?= $product['id'] ?>" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded text-sm transition">Toevoegen aan winkelwagen</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Voeg product toe aan winkelwagen
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    
    // Controleer of het product bestaat
    $productStmt = $db->getProduct($id);
    $product = $productStmt->fetch();
    
    if ($product) {
        // Voeg toe aan winkelwagen
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $_SESSION['cart'][$id] = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] + 1 : 1;
        
        // Toon success bericht en redirect
        ?>
        <script>
        alert("Product toegevoegd aan winkelwagen!");
        window.location.href = "producten.php";
        </script>
        <?php
    }
}
?>

<?php include '../includes/footer.php'; ?> 