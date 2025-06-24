<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-3xl font-bold text-teal-700 mb-6">Reserveer een tafel</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <img src="https://source.unsplash.com/random/800x600/?restaurant,table" alt="Restaurant tafel" class="rounded-lg shadow-md w-full">
            
            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Openingstijden</h3>
                <ul class="space-y-2">
                    <li class="flex justify-between">
                        <span>Maandag t/m vrijdag:</span>
                        <span>16:00 - 22:00</span>
                    </li>
                    <li class="flex justify-between">
                        <span>Zaterdag:</span>
                        <span>12:00 - 23:00</span>
                    </li>
                    <li class="flex justify-between">
                        <span>Zondag:</span>
                        <span>12:00 - 22:00</span>
                    </li>
                </ul>
                
                <h3 class="text-xl font-semibold mt-6 mb-3">Reserveringsbeleid</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1">
                    <li>Reserveringen kunnen tot 2 uur voor aanvang worden gemaakt</li>
                    <li>Bij vertraging van meer dan 15 minuten kan uw tafel aan andere gasten worden gegeven</li>
                    <li>Voor groepen groter dan 8 personen vragen we u telefonisch contact op te nemen</li>
                    <li>Annulering is kosteloos tot 24 uur voor uw reservering</li>
                </ul>
            </div>
        </div>
        
        <div>
            <?php
            // Toon succes bericht als we terugkomen van confirm_reservation.php
            if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="bg-green-100 text-green-600 p-4 rounded mb-6">
                    <h3 class="font-semibold text-lg mb-2">Reservering bevestigd!</h3>
                    <p>Uw reservering is succesvol aangemaakt. We hebben een bevestiging gestuurd naar het opgegeven e-mailadres.</p>
                    <p class="mt-2">We kijken ernaar uit u te ontvangen in ons restaurant!</p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/webshop/confirm-reservation" class="space-y-4">
                <div>
                    <label for="naam" class="block text-gray-700 font-medium mb-1">Naam</label>
                    <input type="text" id="naam" name="naam" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">E-mail</label>
                    <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                
                <div>
                    <label for="telefoon" class="block text-gray-700 font-medium mb-1">Telefoonnummer</label>
                    <input type="tel" id="telefoon" name="telefoon" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="datum" class="block text-gray-700 font-medium mb-1">Datum</label>
                        <input type="date" id="datum" name="datum" min="<?= date('Y-m-d') ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    
                    <div>
                        <label for="tijd" class="block text-gray-700 font-medium mb-1">Tijd</label>
                        <select id="tijd" name="tijd" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Selecteer een tijd</option>
                            <option value="17:00">17:00</option>
                            <option value="17:30">17:30</option>
                            <option value="18:00">18:00</option>
                            <option value="18:30">18:30</option>
                            <option value="19:00">19:00</option>
                            <option value="19:30">19:30</option>
                            <option value="20:00">20:00</option>
                            <option value="20:30">20:30</option>
                            <option value="21:00">21:00</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="aantal_personen" class="block text-gray-700 font-medium mb-1">Aantal personen</label>
                    <input type="number" id="aantal_personen" name="aantal_personen" min="1" max="8" value="2" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <p class="text-sm text-gray-500 mt-1">Voor groepen groter dan 8 personen, bel ons op 020-1234567</p>
                </div>
                
                <div>
                    <label for="opmerkingen" class="block text-gray-700 font-medium mb-1">Opmerkingen (optioneel)</label>
                    <textarea id="opmerkingen" name="opmerkingen" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded">Reservering plaatsen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?> 