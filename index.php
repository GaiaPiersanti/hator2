<?php
session_start();
require "include/dbms.inc.php";
require "include/template2.inc.php";

// 1) Prendo e sanifico la pagina richiesta
$requested = $_GET['page'] ?? 'home';
$requested = basename($requested);

// 2) Intercetto il controller “home.php”
if ($requested === 'home') {
    require __DIR__ . '/home.php';
    exit;
}

// Intercept the login page
if ($requested === 'login') {
    require __DIR__ . '/login.php';
    exit;
}

// 3) (in futuro) qui potrai intercettare anche “login”, “register” ecc.
//    es.: if ($requested==='login') { require __DIR__.'/login.php'; exit; }

// 4) Lista delle pagine “statiche” consentite
$allowed = [
    'about','contact','shop',
    'login','register','checkout','cart',
    'logout','product','productdetails','search'
];
// home l’abbiamo già gestita sopra

if (!in_array($requested, $allowed, true)) {
    // Se non è né home né una pagina consentita, fallback a home.php
    require __DIR__ . '/home.php';
    exit;
}

// 5) Percorso al template statico
$path = "dtml/hator/{$requested}.html";

// 6) Se manca il file, fallback a home.php
if (! file_exists($path)) {
    require __DIR__ . '/home.php';
    exit;
}

// 7) Render delle pagine statiche
$main = new Template("dtml/hator/frame");
$body = new Template("dtml/hator/{$requested}");
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
