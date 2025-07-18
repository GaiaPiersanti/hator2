<?php
// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
/*.	La pagina productdetails
	•	È un file a sé stante, non incluso nel DOM di shop.php, che deve essere caricato con URL productdetails.php?slug=….
	•	Quando l’utente arriva lì, il browser non ha mai visto frame.html: serve un template HTML ( productdetails.html che hai) che deve essere incluso in un PHP (o trasformato in PHP) affinché il server ti restituisca una pagina valida.
	•	Se lasci solo un file .html statico, non hai modo di fargli “leggere” i dati PHP che hai estratto dal database: il browser scarica l’.html e stop.
*/
// 1) Prendi lo slug
if (!isset($_GET['slug'])) {
    header('Location: shop.php');
    exit;
}
$slug = $_GET['slug'];

// 2) Query prodotto + varianti + meta-info
$sql = "
  SELECT 
    p.slug, p.name, p.short_description, p.long_description,
    p.id             AS pid,
    p.category_id    AS category_id,
    p.brand_id       AS brand_id,
    p.family_id      AS family_id,
    p.img1_url, p.img2_url, p.img3_url, p.img4_url,
    t.name AS type_name,
    c.name AS category_name,
    b.name AS brand_name,
    f.name AS family_name,
    pv.id    AS variant_id,
    pv.size_ml, pv.price, pv.stock
  FROM products p
    JOIN types      t  ON t.id = p.type_id
    JOIN categories c  ON c.id = p.category_id
    JOIN brands     b  ON b.id = p.brand_id
    JOIN families   f  ON f.id = p.family_id
    JOIN product_variants pv ON pv.product_id = p.id
  WHERE p.slug = ?
  ORDER BY pv.size_ml
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Prodotto non trovato";
    exit;
}

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

// 3) Raggruppa in $product
$product = [
    'slug'               => $rows[0]['slug'],
    'name'               => $rows[0]['name'],
    'short_description'  => $rows[0]['short_description'],
    'long_description'   => $rows[0]['long_description'],
    'id'                 => $rows[0]['pid'],
    'category_id'        => $rows[0]['category_id'],
    'brand_id'           => $rows[0]['brand_id'],
    'family_id'          => $rows[0]['family_id'],
    'img1_url'           => $rows[0]['img1_url'],
    'img2_url'           => $rows[0]['img2_url'],
    'img3_url'           => $rows[0]['img3_url'],
    'img4_url'           => $rows[0]['img4_url'],
    'type_name'          => $rows[0]['type_name'],
    'category_name'      => $rows[0]['category_name'],
    'brand_name'         => $rows[0]['brand_name'],
    'family_name'        => $rows[0]['family_name'],
    'variants'           => []
];
foreach ($rows as $r) {
    $product['variants'][] = [
        'variant_id' => $r['variant_id'],
        'size_ml'    => $r['size_ml'],
        'price'      => $r['price'],
        'stock'      => $r['stock']
    ];
}


// 1) Prepara lo script PRODUCT per JS
$productScript = '<script>';
$productScript .= 'const PRODUCT = ' 
                . json_encode($product, JSON_HEX_TAG) 
                . ';';
$productScript .= '</script>';

// 2) Genera il markup del body via la tag‐library
$lib  = new product();
$bodyHtml = $lib->details('details', $product, []);

// 3) Aggiungi l’inclusione di product-page.js
$bodyHtml .= '<script src="js/product-page.js"></script>';



// 1) Dati del prodotto corrente
$currentId     = $product['id'];
$currentCat    = $product['category_id'];
$currentBrand  = $product['brand_id'];
$currentFamily = $product['family_id'];

// 2) Query “related” con punteggio + RAND(), limit 4
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
    t.name             AS type_name,
    c.name             AS category_name,
    b.name             AS brand_name,
    f.name             AS family_name,
    pv.id              AS variant_id,
    pv.size_ml,
    pv.price,
    pv.stock,
    /* punteggio di affinità */
    (p.category_id = ?) +
    (p.brand_id    = ?) +
    (p.family_id   = ?)  AS score
  FROM products p
    JOIN types         t  ON t.id = p.type_id
    JOIN categories    c  ON c.id = p.category_id
    JOIN brands        b  ON b.id = p.brand_id
    JOIN families      f  ON f.id = p.family_id
    JOIN product_variants pv ON pv.product_id = p.id
  WHERE p.id <> ?
  ORDER BY score DESC, RAND()
  LIMIT 4
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
  'iiii',
  $currentCat,
  $currentBrand,
  $currentFamily,
  $currentId
);
$stmt->execute();
$res = $stmt->get_result();

// 3) Raggruppa per prodotto
$related = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['pid'];
    if (!isset($related[$pid])) {
        $related[$pid] = [
            'pid'               => $row['pid'],
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
            'type_name'          => $row['type_name'],
            'category_name'      => $row['category_name'],
            'brand_name'         => $row['brand_name'],
            'family_name'        => $row['family_name'],
            'variants'           => []
        ];
    }
    $related[$pid]['variants'][] = [
        'variant_id' => $row['variant_id'],
        'size_ml'    => $row['size_ml'],
        'price'      => $row['price'],
        'stock'      => $row['stock']
    ];
}
$related = array_values($related);
// Fallback: se meno di 4, aggiungi prodotti casuali per arrivare a 4
$found = count($related);
if ($found < 4) {
    // ID da escludere (prodotto corrente + già presenti)
    $excludeIds = array_merge([$product['id']], array_column($related, 'pid'));
    // Prepara placeholders
    $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
    $sqlFb = "
      SELECT p.id AS pid, p.slug, p.name, p.short_description, p.long_description,
             p.img1_url, p.img2_url, p.img3_url, p.img4_url,
             p.new_arrival, p.best_seller,
             t.name AS type_name, c.name AS category_name,
             b.name AS brand_name, f.name AS family_name,
             pv.id AS variant_id, pv.size_ml, pv.price, pv.stock
      FROM products p
        JOIN types t ON t.id = p.type_id
        JOIN categories c ON c.id = p.category_id
        JOIN brands b ON b.id = p.brand_id
        JOIN families f ON f.id = p.family_id
        JOIN product_variants pv ON pv.product_id = p.id
      WHERE p.id NOT IN ($placeholders)
      ORDER BY RAND()
      LIMIT " . (4 - $found);
    $stmtFb = $conn->prepare($sqlFb);
    $types = str_repeat('i', count($excludeIds));
    $stmtFb->bind_param($types, ...$excludeIds);
    $stmtFb->execute();
    $resFb = $stmtFb->get_result();
    while ($row = $resFb->fetch_assoc()) {
        $pid = $row['pid'];
        if (!isset($related[$pid])) {
            $related[$pid] = [
                'pid'               => $row['pid'],
                'slug' => $row['slug'],
                'name' => $row['name'],
                'short_description' => $row['short_description'],
                'long_description' => $row['long_description'],
                'img1_url' => $row['img1_url'],
                'img2_url' => $row['img2_url'],
                'img3_url' => $row['img3_url'],
                'img4_url' => $row['img4_url'],
                'new_arrival' => (bool)$row['new_arrival'],
                'best_seller' => (bool)$row['best_seller'],
                'type_name' => $row['type_name'],
                'category_name' => $row['category_name'],
                'brand_name' => $row['brand_name'],
                'family_name' => $row['family_name'],
                'variants' => []
            ];
        }
        $related[$pid]['variants'][] = [
            'variant_id' => $row['variant_id'],
            'size_ml'    => $row['size_ml'],
            'price'      => $row['price'],
            'stock'      => $row['stock']
        ];
    }
    // Riassegna indice numerico
    $related = array_values($related);
}

// 4) Genera le 4 card “product4”
$prodLib   = new product();
$cardsHtml = '';
foreach ($related as $prod) {
    $cardsHtml .= $prodLib->card('product4', $prod, []);
}

// 5) Carica nel template
$body = new Template("dtml/hator/productdetails");
$body->setContent("productScript", $productScript);
$body->setContent("detailsHtml",   $bodyHtml);
$body->setContent("products4",      $cardsHtml);
$main->setContent("body", $body->get());
$main->close();