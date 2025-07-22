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

// 2) Related Products: select 4 products by correlation with min-price variant
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
    pv.id              AS filtered_variant_id,
    (p.category_id = ?) +
    (p.brand_id    = ?) +
    (p.family_id   = ?)  AS score
  FROM products p
    JOIN types      t ON t.id = p.type_id
    JOIN categories c ON c.id = p.category_id
    JOIN brands     b ON b.id = p.brand_id
    JOIN families   f ON f.id = p.family_id
    JOIN product_variants pv 
      ON pv.product_id = p.id
     AND pv.price = (
       SELECT MIN(price)
         FROM product_variants
        WHERE product_id = p.id
     )
  WHERE p.id <> ?
  GROUP BY p.id
  ORDER BY score DESC, RAND()
  LIMIT 4
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $currentCat, $currentBrand, $currentFamily, $currentId);
$stmt->execute();
$res = $stmt->get_result();

// 3) Initialize related with filtered variant
$related = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['pid'];
    if (!isset($related[$pid])) {
        $related[$pid] = [
            'pid'                 => $pid,
            'slug'                => $row['slug'],
            'name'                => $row['name'],
            'short_description'   => $row['short_description'],
            'long_description'    => $row['long_description'],
            'img1_url'            => $row['img1_url'],
            'img2_url'            => $row['img2_url'],
            'img3_url'            => $row['img3_url'],
            'img4_url'            => $row['img4_url'],
            'new_arrival'         => (bool)$row['new_arrival'],
            'best_seller'         => (bool)$row['best_seller'],
            'type_name'           => $row['type_name'],
            'category_name'       => $row['category_name'],
            'brand_name'          => $row['brand_name'],
            'family_name'         => $row['family_name'],
            'filtered_variant_id' => $row['filtered_variant_id'],
            'variants'            => []
        ];
    }
}
$related = array_values($related);

// 4) Fetch all variants and reorder, same as shop
$allRelVariants = [];
$relIds = array_column($related, 'pid');
if (!empty($relIds)) {
    $idsList = implode(',', array_map('intval', $relIds));
    $varRes = $conn->query("
        SELECT id AS variant_id, product_id, size_ml, price, stock
          FROM product_variants
         WHERE product_id IN ({$idsList})
         ORDER BY size_ml
    ");
    while ($vr = $varRes->fetch_assoc()) {
        $allRelVariants[$vr['product_id']][] = $vr;
    }
}
foreach ($related as &$prod) {
    $filteredId = $prod['filtered_variant_id'];
    $vars = $allRelVariants[$prod['pid']] ?? [];
    usort($vars, function($a, $b) use ($filteredId) {
        if ($a['variant_id'] == $filteredId) return -1;
        if ($b['variant_id'] == $filteredId) return 1;
        return 0;
    });
    $prod['variants'] = $vars;
}
unset($prod);

// 5) Render cards with the same tag library
$prodLib   = new product();
$cardsHtml = '';
foreach ($related as $prod) {
    $cardsHtml .= $prodLib->card_related('card', $prod, []);
}

// 6) Inject into the template and close
$body = new Template("dtml/hator/productdetails");
$body->setContent("productScript", $productScript);
$body->setContent("detailsHtml",   $bodyHtml);
$body->setContent("products4",      $cardsHtml);
$main->setContent("body", $body->get());
$main->close();