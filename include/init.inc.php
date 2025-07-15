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
    
    // …altri titoli…
];


// 6) costruisci il titolo completo
$niceTitle  = $pageTitles[$page] ?? ucfirst($page);
$page_title = $niceTitle . ' | ' . $websiteName;

// 7) definisci quali slug sono pubblici e quali protetti
$publicPages    = ['home', 'login', 'shop', 'about', 'contact', 'productdetails', '404', 'logout', 'add-user', 'cart', 'orders'];
$protectedPages = [];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $res = $conn->query("SELECT name FROM groups_has_services JOIN services ON groups_has_services.service_id = services.ID  WHERE group_id =" . $_SESSION['user']['group_id']);
    while ($next = $res->fetch_assoc()) {
        $protectedPages[] = $next['name'];
    }
}

if ('IN_ADMIN') {
    if (!in_array($page, $publicPages, true) && !in_array($page, $protectedPages, true)) {
        header('Location: admin.php?page=404');
        exit;
    }
}
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

// 10) definisco i bottoni per il menu
$buttons_not_loged = '<li>
<!--LOG IN LINK-->                            <a href="index.php?page=login">Log In</a>
                                        </li>
                                        <li>
<!--REGISTER LINK-->                          <a href="index.php?page=add-user">Register</a>
                                        </li>';
$buttons_loged =  '                                           
                                        <!--aggiungi qui tutto ciò che vuoi mostrare 
                                        SOLO agli utenti loggati e aggiungi nelle
                                        funzioni a cui puoi accedere solo se sei loggato
                                        $user= $this->loadModel("User");
                                        if(! $user->check_logged_in()) {
                                            header("Location: " . ROOT . "login");
                                            die;   MINUTO 230 
                                        } -->
                                        <li>
<!--LOG OUT LINK-->                          <a href="index.php?page=logout">Log Out</a>
                                        </li>';
$settings = '<li>
                                        <a href="#">Settings
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <!-- Dropdown Start -->
                                        <ul class="ht-dropdown">
                                             <li>
                                                <a href="index.php?page=account">my account</a>
                                            </li>
                                            <li>
                                                <a href="index.php?page=wishlist">my wishlist</a>
                                            </li>
                                            
                                     

                                        </ul>
                                        <!-- Dropdown End -->
                                    </li>';


 // ||||||||||||||||||||||||ADMIN header('Location: admin.php?page=prova');
