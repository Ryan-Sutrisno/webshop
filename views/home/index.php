<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-teal-700 mb-6">Welkom bij Restaurant Deluxe</h2>
    
    <div class="mb-8">
        <div class="w-full">
            <p class="text-gray-700 mb-4">Bij Restaurant Deluxe geniet u van de lekkerste culinaire gerechten in een sfeervolle ambiance. Onze chef-kok bereidt dagelijks verse gerechten met seizoensgebonden ingrediënten van de hoogste kwaliteit.</p>
            <p class="text-gray-700 mb-4">Of u nu komt voor een romantisch diner, een zakelijke lunch of een gezellige avond met vrienden, bij ons bent u altijd welkom.</p>
            <div class="flex space-x-4">
                <a href="/webshop/menu" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded transition">Bekijk ons menu</a>
                <a href="/webshop/reserveer" class="bg-amber-500 hover:bg-amber-600 text-white py-2 px-4 rounded transition">Reserveer nu</a>
            </div>
        </div>
    </div>
    
    <h3 class="text-2xl font-bold text-teal-700 mb-4">Onze specialiteiten</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        require_once __DIR__ . '/../../includes/db.php';
        
        // Haal 3 random producten op om te tonen op de homepagina
        try {
            $productenStmt = $db->getRandomProducts(3);
            $producten = $productenStmt->fetchAll();
            
            foreach ($producten as $product): ?>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h4 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['naam']) ?></h4>
                    <p class="text-gray-600 mb-3"><?= htmlspecialchars($product['beschrijving']) ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-teal-600 font-bold">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                        <a href="/webshop/menu?add=<?= $product['id'] ?>" class="bg-teal-600 hover:bg-teal-700 text-white py-1 px-3 rounded text-sm transition">Toevoegen</a>
                    </div>
                </div>
            <?php endforeach;
        } catch (PDOException $e) { ?>
            <div class="bg-red-100 text-red-600 p-4 rounded mb-4">Er zijn momenteel geen aanbevelingen beschikbaar.</div>
        <?php } ?>
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
        window.location.href = "/webshop/";
        </script>
        <?php
    }
}
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 