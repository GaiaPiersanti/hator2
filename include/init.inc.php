<?php

//define('ROOT', dirname(__DIR__)); // se init.inc.php è in hator2/include, dirname(__DIR__) è hator2

// 1) session_start() idempotente
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2) include delle librerie comuni
require_once __DIR__ . '/dbms.inc.php';
require_once __DIR__ . '/template2.inc.php';
require_once __DIR__ . '/tags/product.inc.php';

// 3) prepara il welcome_message
if (!empty($_SESSION['user']['first_name'])) {
    $welcome = 'Welcome back '
             . htmlspecialchars($_SESSION['user']['first_name'], ENT_QUOTES)
             . ' '
             . htmlspecialchars($_SESSION['user']['last_name'],   ENT_QUOTES)
             . '!';
} else {
    $welcome = '';
}

// 4) determina lo slug di pagina
$page = $_GET['page'] ?? 'home';

// 5) mappa i titoli
$pageTitles = [
    'home'           => 'Home',
    'login'          => 'Login',
    'add-user'       => 'Register',
    'productdetails' => 'Product Details',
    '404'            => 'Page Not Found',
    'shop'           => 'Shop',
    'cart'           => 'Cart',
    'checkout'       => 'Checkout',
    'logout'         => 'Logout',
    'about'          => 'About Us',
    'contact'        => 'Contact Us',
    'orders'         => 'Your Orders',
    'account'        => 'Your Account',
    'wishlist'       => 'Your Wishlist',
    // …altri titoli…
];

// 6) costruisci il titolo completo
$niceTitle  = $pageTitles[$page] ?? ucfirst($page);
$page_title = $niceTitle . ' | Hator';

// 7) definisci quali slug sono pubblici e quali protetti
$publicPages    = ['home','login','shop','about','contact','productdetails','404','logout','add-user','cart','orders'];
$protectedPages = ['account','wishlist', 'checkout'];

/// ** Solo per il front‐end: redirect su login o 404 , cosi non tocca le pagine admin**
if (!defined('IN_ADMIN')) {
    // 8) se pagina protetta e non loggato → login
    if (in_array($page, $protectedPages, true) && empty($_SESSION['loggedin'])) {
        header('Location: index.php?page=login');
        exit;
    }

    // 9) se slug non in pubblico né in protetto → 404
    if (!in_array($page, $publicPages, true) && !in_array($page, $protectedPages, true)) {
        header('Location: index.php?page=404');
        exit;
    }
}

 // ||||||||||||||||||||||||ADMIN header('Location: admin.php?page=prova');

