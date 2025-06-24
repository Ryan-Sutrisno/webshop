<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-teal-700 mb-6">Welkom <?php echo htmlspecialchars($username); ?></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Bestellingen -->
        <div class="dashboard-section">
                <h3 class="text-2xl font-semibold text-teal-600 mb-4">Mijn Bestellingen</h3>
            <?php if (empty($orders)): ?>
                    <p class="text-gray-600">Je hebt nog geen bestellingen geplaatst.</p>
            <?php else: ?>
                    <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">Bestelling #<?php echo $order['id']; ?></h4>
                                <p class="text-sm text-gray-600">
                                    Datum: <?php echo date('d-m-Y H:i', strtotime($order['besteldatum'])); ?><br>
                                    Totaal: €<?php echo number_format($order['totaalbedrag'], 2, ',', '.'); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
            <?php endif; ?>
        </div>
        
            <!-- Reserveringen -->
        <div class="dashboard-section">
                <h3 class="text-2xl font-semibold text-teal-600 mb-4">Mijn Reserveringen</h3>
            <?php if (empty($reservations)): ?>
                    <p class="text-gray-600">Je hebt nog geen reserveringen gemaakt.</p>
            <?php else: ?>
                    <div class="space-y-4">
                <?php foreach ($reservations as $reservation): ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold">Reservering #<?php echo $reservation['id']; ?></h4>
                                <p class="text-sm text-gray-600">
                                    Datum: <?php echo date('d-m-Y H:i', strtotime($reservation['datum'])); ?><br>
                                    Aantal personen: <?php echo $reservation['aantal_personen']; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
                    </div>
            <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-8 flex space-x-4">
            <a href="/webshop/" class="text-teal-600 hover:text-teal-800">
                Terug naar home
            </a>
            <span class="text-gray-300">|</span>
            <a href="/webshop/login?logout=1" class="text-red-600 hover:text-red-800">
                Uitloggen
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 