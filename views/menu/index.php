<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-teal-700 mb-6">Ons Menu</h2>
    
    <?php
    require_once __DIR__ . '/../../includes/db.php';
    
    try {
        // Haal alle producten op
        $stmt = $db->getAllProducts();
        $producten = $stmt->fetchAll();
        
        // Groepeer producten per categorie
        $categorien = [];
        foreach ($producten as $product) {
            $categorien[$product['categorie']][] = $product;
        }
        
        // Toon producten per categorie
        foreach ($categorien as $categorie => $producten): ?>
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-teal-600 mb-4"><?= htmlspecialchars($categorie) ?></h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($producten as $product): ?>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h4 class="text-xl font-semibold mb-2"><?= htmlspecialchars($product['naam']) ?></h4>
                            <p class="text-gray-600 mb-3"><?= htmlspecialchars($product['beschrijving']) ?></p>
            <div class="flex justify-between items-center">
                <span class="text-teal-600 font-bold">€<?= number_format($product['prijs'], 2, ',', '.') ?></span>
                                <a href="/webshop/menu?add=<?= $product['id'] ?>" class="bg-teal-600 hover:bg-teal-700 text-white py-1 px-3 rounded text-sm transition">
                                    Toevoegen aan winkelwagen
                                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
            </div>
        <?php endforeach;
    } catch (PDOException $e) { ?>
        <div class="bg-red-100 text-red-600 p-4 rounded">
            Er is een probleem bij het ophalen van de menukaart. Probeer het later opnieuw.
        </div>
    <?php } ?>
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
        window.location.href = "/webshop/menu";
        </script>
        <?php
    }
}
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 