<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("welcome_message", $welcome);
$main->setContent("buttons", isset($_SESSION['loggedin']) ? $buttons_not_loged : $buttons_loged);


// 3) Carico i prodotti dal database
$products = [];
$res = $conn->query("
    SELECT p.slug,p.img1_url,p.name,pv.price,p.new_arrival,p.best_seller,pv.id AS variant_id
    FROM products p
    JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
");
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

$products2 = [];
$res = $conn->query("
    SELECT p.slug,p.img1_url,p.name,pv.price,p.new_arrival,p.best_seller,p.short_description,pv.id AS variant_id
    FROM products p
    JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
");
while ($row = $res->fetch_assoc()) {
    $products2[] = $row;
}

// -> Costruisco l’HTML delle card qui, in PHP
require_once __DIR__ . '/../../include/tags/product.inc.php'; // se serve includere la libreria
$prodLib = new product();
$cardsHtml = "";
foreach ($products as $prod) {
    // il primo argomento 'card' è il nome del tag, il secondo i dati, il terzo i parametri (vuoti)
    $cardsHtml .= $prodLib->card('card', $prod, []);
}
$cards2Html = "";
foreach ($products2 as $prod2) {
    // il primo argomento 'card' è il nome del tag, il secondo i dati, il terzo i parametri (vuoti)
    $cards2Html .= $prodLib->card2('card', $prod2, []);
}
// 3) Calcolo il numero di prodotti
$productCount = count($products);
// 4) Istanzio il sotto‐template per la pagina shop
$body = new Template("dtml/hator/shop");

// ** passo contenuti **
$body->setContent("product_cards", $cardsHtml);
$body->setContent("product_cards2", $cards2Html);
$body->setContent("product_count", $productCount);

// 5) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();

// //debug
// //error_log("Shop loaded " . count($products) . " products.");
// echo "<p>DEBUG shop.php: loaded " . count($products) . " products.</p>";

// // 2) Istanzio il sotto‐template per la pagina about
// $body = new Template("dtml/hator/shop");

// $body->setContent("products", $products);



// // 4) Inietto il body nel frame e chiudo
// $main->setContent("body", $body->get());
// $main->close();