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
require_once __DIR__ . '/slugify.php'; 



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
    'forgot-password' => 'Forgot Password',
    'pagamento'      => 'Payment',
    'riepilogo'      => 'Order Summary',
    // Admin pages -----------------------
    'tab-crud'      => 'Products table',
    'buttons'       => 'Buttons',
    'buttons-crud'  => 'CRUD Operations',
    //brands
    'brands-list'   => 'Brands list',
    'add-brand'     => 'Add Brand',
    'edit-brand'    => 'Edit Brand',
    'delete-brand'  => 'Delete Brand',
    //types
    'types-list'   => 'Types list',
    'add-type'     => 'Add Type',
    'edit-type'    => 'Edit Type',
    'delete-type'  => 'Delete Type',
    //categories
    'categories-list'   => 'Categories list',
    'add-category'     => 'Add Category',
    'edit-category'    => 'Edit Category',
    'delete-category'  => 'Delete Category',
    //families
    'families-list'   => 'Families list',
    'add-family'     => 'Add Family',
    'edit-family'    => 'Edit Family',
    'delete-family'  => 'Delete Family',
    //services
    'services-list'=> 'Services list',
    'add-service'     => 'Add Service',
    'edit-service'    => 'Edit Service',
    'delete-service'  => 'Delete Service',
    //products
    'products-list'=> 'Products list',
    'add-product'     => 'Add Product',
    'edit-product'    => 'Edit Product',
    'delete-product'  => 'Delete Product',
    //Variants
    'edit-variants'=> 'Edit Variants',
    'add-variants'     => 'Add Variants',
    'modify-variants'    => 'Modify Variants',
    'delete-variants'  => 'Delete Variants',
     //products
     'users-list'=> 'Users list',
     'add-user'     => 'Add User',
     'edit-user'    => 'Edit User',
     'delete-user'  => 'Delete User',
    //img
    'edit-images'  => 'Edit Images',
    

    
    // …altri titoli…
];


// 6) costruisci il titolo completo
$niceTitle  = $pageTitles[$page] ?? ucfirst($page);
$page_title = $niceTitle . ' | ' . $websiteName;

// 7) definisci quali slug sono pubblici e quali protetti

$publicPages    = ['home', 'login', 'shop', 'about', 'contact', 'productdetails', '404', 'logout', 'add-user', 'cart', 'orders', 'forgot-password'];
$protectedPages = ['checkout'];


if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $res = $conn->query("SELECT name FROM groups_has_services JOIN services ON groups_has_services.service_id = services.ID  WHERE group_id =" . $_SESSION['user']['group_id']);
    while ($next = $res->fetch_assoc()) {
        $protectedPages[] = $next['name'];
    }
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['user']['group_id'] === '2' || $_SESSION['user']['group_id'] === '3') {
        if (!in_array($page, $publicPages, true) && !in_array($page, $protectedPages, true)) {
            header('Location: admin.php?page=404');
            exit;
        }
    }
    /// ** Solo per il front‐end: redirect su login o 404 , cosi non tocca le pagine admin**
    if ($_SESSION['user']['group_id'] === '1') {
        // 9) se slug non in pubblico né in protetto → 404
        if (!in_array($page, $publicPages, true) && !in_array($page, $protectedPages, true)) {
            header('Location: index.php?page=404');
            exit;
        }
    } 
} else {
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
