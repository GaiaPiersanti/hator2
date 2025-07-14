<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$websiteName = 'Hator';
// carico tutto il bootstrap
require __DIR__ . '/include/init.inc.php';

// dispaccio al controller giusto
switch ($page) {
    case 'home':           require __DIR__ . "/controllers/public/home.php";           break;
    case 'about':          require __DIR__ . "/controllers/public/about.php";          break;
    case 'contact':        require __DIR__ . "/controllers/public/contact.php";        break;
    case 'login':          require __DIR__ . "/controllers/public/login.php";          break;
    case 'add-user':       require __DIR__ . "/controllers/public/add-user.php";       break;
    case 'logout':         require __DIR__ . "/controllers/public/logout.php";         break;
    case 'shop':           require __DIR__ . "/controllers/public/shop.php";           break;
    case 'productdetails': require __DIR__ . "/controllers/public/productdetails.php"; break;
    case 'cart':           require __DIR__ . "/controllers/public/cart.php";           break;
    case 'checkout':       require __DIR__ . "/controllers/public/checkout.php";       break;
    case 'account':        require __DIR__ . "/controllers/public/account.php";        break;
    case 'wishlist':       require __DIR__ . "/controllers/public/wishlist.php";       break;
    case '404':            require __DIR__ . "/controllers/public/404.php";            break;
    default:               require __DIR__ . "/controllers/public/home.php";           break;




}