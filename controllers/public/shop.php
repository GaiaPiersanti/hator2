<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);



// 3) Carico prodotti + tutte le loro varianti e raggruppo per prodotto
$sql = "
  SELECT 
    p.id               AS pid,
    p.slug,
    p.name,
    p.short_description,
    p.long_description,
    p.img1_url, 
    p.img2_url, 
    p.img3_url, 
    p.img4_url,
    p.new_arrival,
    p.best_seller,
    -- join per leggere i nomi di type, category, brand, family
    t.name             AS type_name,
    c.name             AS category_name,
    b.name             AS brand_name,
    f.name             AS family_name,
    -- campi delle varianti
    pv.id              AS variant_id,
    pv.size_ml,
    pv.price,
    pv.stock
  FROM products p
    JOIN types      t  ON t.id = p.type_id
    JOIN categories c  ON c.id = p.category_id
    JOIN brands     b  ON b.id = p.brand_id
    JOIN families   f  ON f.id = p.family_id
    JOIN product_variants pv 
      ON pv.product_id = p.id
  ORDER BY p.id, pv.size_ml
";

$res = $conn->query($sql);

$products = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['pid'];

    if (!isset($products[$pid])) {
        // inizializzo TUTTI i campi del prodotto
        $products[$pid] = [
            'slug'               => $row['slug'],
            'name'               => $row['name'],
            'short_description'  => $row['short_description'],
            'long_description'   => $row['long_description'],
            'img1_url'           => $row['img1_url'],
            'img2_url'           => $row['img2_url'],
            'img3_url'           => $row['img3_url'],
            'img4_url'           => $row['img4_url'],
            'new_arrival'        => (bool)$row['new_arrival'],
            'best_seller'        => (bool)$row['best_seller'],
            // i nuovi campi
            'type_name'          => $row['type_name'],
            'category_name'      => $row['category_name'],
            'brand_name'         => $row['brand_name'],
            'family_name'        => $row['family_name'],
            // array per le varianti
            'variants'           => []
        ];
    }
    // aggiungo la variante corrente
  $products[$pid]['variants'][] = [
    'variant_id'=> $row['variant_id'],
    'size_ml'   => $row['size_ml'],
    'price'     => $row['price'],
    'stock'     => $row['stock']
  ];
}
$products = array_values($products);

// Normalizziamo gli indici in un array 0-based:
//$products = array_values($products);
//inizio product 2 (list view)
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




// 4) Istanzio il sotto‐template per la pagina shop
$body = new Template("dtml/hator/shop");
// 5) Passo i dati già raggruppati
//$body->setContent("products",       $products);
$body->setContent("product_cards",  $cardsHtml);
$body->setContent("product_cards2", $cards2Html);
// 3) Calcolo il numero di prodotti
$body->setContent("product_count",  count($products));
// 6) Inietto e chiudo
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