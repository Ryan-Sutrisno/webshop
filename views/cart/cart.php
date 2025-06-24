<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-teal-700 mb-6">Winkelwagen</h2>
    
    <?php
    require_once __DIR__ . '/../../includes/db.php';
    
    // Verwijder een product uit de winkelwagen
    if (isset($_GET['remove'])) {
        $id = (int)$_GET['remove'];
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            ?>
            <div class="bg-green-100 text-green-600 p-4 rounded mb-4">Product verwijderd uit winkelwagen.</div>
            <?php
        }
    }
    
    // Update de hoeveelheid van een product
    if (isset($_POST['update'])) {
        foreach ($_POST['quantity'] as $id => $quantity) {
            $id = (int)$id;
            $quantity = (int)$quantity;
            
            if ($quantity > 0) {
                $_SESSION['cart'][$id] = $quantity;
            } else {
                unset($_SESSION['cart'][$id]);
            }
        }
        ?>
        <div class="bg-green-100 text-green-600 p-4 rounded mb-4">Winkelwagen bijgewerkt.</div>
        <?php
    }
    
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
        <form method="post" action="/webshop/cart" class="space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-left">Prijs</th>
                            <th class="px-4 py-2 text-left">Aantal</th>
                            <th class="px-4 py-2 text-left">Subtotaal</th>
                            <th class="px-4 py-2 text-left">Actie</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    <td class="px-4 py-4"><?= htmlspecialchars($product['naam']) ?></td>
                                    <td class="px-4 py-4">€<?= number_format($product['prijs'], 2, ',', '.') ?></td>
                                    <td class="px-4 py-4">
                                        <input type="number" name="quantity[<?= $id ?>]" value="<?= $quantity ?>" min="0" class="border rounded p-2 w-16">
                                    </td>
                                    <td class="px-4 py-4">€<?= number_format($subtotaal, 2, ',', '.') ?></td>
                                    <td class="px-4 py-4">
                                        <a href="/webshop/cart?remove=<?= $id ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Weet je zeker dat je dit product wilt verwijderen?');">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endif;
                        endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right">Totaal:</td>
                            <td class="px-4 py-3">€<?= number_format($totaal, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-6 flex justify-between">
                <button type="submit" name="update" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">Winkelwagen bijwerken</button>
                <a href="/webshop/checkout" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded">Afrekenen</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 