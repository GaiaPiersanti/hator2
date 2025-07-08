<?php


require "include/template2.inc.php";
//define('ROOT', '/hator2/');  // se il progetto è in .../htdocs/hator2


$requested = $_GET['page'] ?? 'home';               // es: about
$requested = basename($requested);                 // sanifica /, .. , ecc.

// pagine consentite
$allowed = ['home',
            'about',
            'contact',
            'shop',
            'login',
            'register',
            'checkout',
            'cart',
            'logout',
            'product',
            'productdetails',
            'search']; 

if (!in_array($requested, $allowed)) {
    $requested = 'home';
}

$path = "dtml/hator/$requested.html";
if (!file_exists($path)) {              // fallback se manca il file
    $requested = 'home';
}

$main = new Template("dtml/hator/frame");
$body = new Template("dtml/hator/$requested");
if (!file_exists("dtml/hator/$requested.html")) {
    die("File non trovato: dtml/hator/$requested.html");
}
$main->setContent("body", $body->get());
$main->close();



// // Usa l’engine del prof
// require "include/template2.inc.php";

// // oppure, per usare il nostro engine personalizzato
// // require "include/engine_custom/template2.inc.php";

// // Carica il template principale (frame)
// $main = new Template("dtml/hator/frame");

// // Carica il contenuto della pagina iniziale (es. home.html)
// $body = new Template("dtml/hator/home");

// // Inserisce il body nel frame
// $main->setContent("body", $body->get());

// // Mostra la pagina finale
// $main->close();




// $page = $_GET['page'] ?? 'home';
// $allowed_pages = ['home', 'about', 'contact', 'shop', 'login', 'register'];

// if (!in_array($page, $allowed_pages)) {
//     $page = 'home'; // fallback di sicurezza
// }

// $main = new Template("dtml/hator/frame");
// $body = new Template("dtml/hator/$page.html");
// $main->setContent("body", $body->get());
// $main->close();
