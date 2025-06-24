<?php require_once __DIR__ . '/init.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Webshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-teal-700 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Restaurant Deluxe</h1>
            <nav>
                <ul class="flex space-x-4 items-center">
                    <li><a href="/webshop/home" class="hover:underline">Home</a></li>
                    <li><a href="/webshop/menu" class="hover:underline">Menu</a></li>
                    <li><a href="/webshop/reserveer" class="hover:underline">Reserveren</a></li>
                    <li><a href="/webshop/cart" class="hover:underline flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Winkelwagen
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="bg-red-500 text-white rounded-full px-2 py-1 text-xs ml-1"><?= array_sum($_SESSION['cart']) ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li class="ml-4">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="/webshop/dashboard" class="bg-white text-teal-700 hover:bg-teal-50 py-2 px-4 rounded-lg transition flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Mijn Account
                            </a>
                        <?php else: ?>
                            <a href="/webshop/auth/login" class="bg-white text-teal-700 hover:bg-teal-50 py-2 px-4 rounded-lg transition flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Inloggen
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container mx-auto p-4">