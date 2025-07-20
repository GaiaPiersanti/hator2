<?php
////////////////////////gestione filtri
// 0) Calcola prezzo minimo e massimo tra tutte le varianti
$priceRes = $conn->query(
  "SELECT 
     MIN(pv.price) AS min_price, 
     MAX(pv.price) AS max_price 
   FROM product_variants pv"
);
$priceRow     = $priceRes->fetch_assoc();
$minPrice     = (float)$priceRow['min_price'];
$maxPrice     = (float)$priceRow['max_price'];

// 0b) Valori selezionati (GET o di default)
$selMin = isset($_GET['price_min']) ? (float)$_GET['price_min'] : $minPrice;
$selMax = isset($_GET['price_max']) ? (float)$_GET['price_max'] : $maxPrice;

// 1) Carico tutti i tipi
$typeRes = $conn->query("SELECT id, name FROM types ORDER BY name");
$types   = $typeRes->fetch_all(MYSQLI_ASSOC);

// 1b) Carico tutte le families
$familyRes = $conn->query("SELECT id, name FROM families ORDER BY name");
$families = $familyRes->fetch_all(MYSQLI_ASSOC);

// 1c) Carico tutte le categories
$categoryRes = $conn->query("SELECT id,name FROM categories ORDER BY name");
$categories  = $categoryRes->fetch_all(MYSQLI_ASSOC);

// 2) Vengono passati in GET come types[]=1&types[]=3…
$selectedTypes = $_GET['types'] ?? [];
// 2b) families
$selectedFamilies = $_GET['families'] ?? [];
// 2c) GET per categories e flags

$selectedCategories    = $_GET['categories']    ?? [];
$selectedNewArrival    = isset($_GET['new_arrival']);
$selectedBestSeller    = isset($_GET['best_seller']);

// Price filter: global min/max and selected range
$priceRes = $conn->query("
  SELECT MIN(price) AS min_price, MAX(price) AS max_price
    FROM product_variants
");
$priceRow      = $priceRes->fetch_assoc();
$globalMin     = (float)$priceRow['min_price'];
$globalMax     = (float)$priceRow['max_price'];
$pm = $_GET['price_min'] ?? '';
$selectedMin = ($pm !== '' && is_numeric($pm))
               ? floatval($pm)
               : $globalMin;
$pm = $_GET['price_max'] ?? '';
$selectedMax = ($pm !== '' && is_numeric($pm))
               ? floatval($pm)
               : $globalMax;

// 3) Funzione helper per montare l’HTML della lista dei types
function buildTypesFilter(array $types, array $selectedTypes): string {
    $html = '<ul class="sidbar-style">';
    foreach ($types as $t) {
        $checked = in_array($t['id'], $selectedTypes) ? ' checked' : '';
        $id      = 'type-' . $t['id'];
        $label   = htmlspecialchars($t['name'], ENT_QUOTES);
        $html   .= <<<HTML
<li class="form-check">
  <input class="form-check-input"
         type="checkbox"
         name="types[]"
         value="{$t['id']}"
         id="{$id}"{$checked}>
  <label class="form-check-label" for="{$id}">{$label}</label>
</li>
HTML;
    }
    $html .= '</ul>';
    return $html;
}

//famiglie
// 3b) Funzione helper per montare l’HTML dei checkbox delle families
function buildFamiliesFilter(array $families, array $selectedFamilies): string {
    $html = '<ul class="sidbar-style">';
    foreach ($families as $f) {
        $checked = in_array($f['id'], $selectedFamilies) ? ' checked' : '';
        $id      = 'family-' . $f['id'];
        $label   = htmlspecialchars($f['name'], ENT_QUOTES);
        $html  .= <<<HTML
<li class="form-check">
  <input class="form-check-input"
         type="checkbox"
         name="families[]"
         value="{$f['id']}"
         id="{$id}"{$checked}>
  <label class="form-check-label" for="{$id}">{$label}</label>
</li>
HTML;
    }
    $html .= '</ul>';
    return $html;
}

// 3b) Funzione helper per montare l’HTML dei checkbox delle categories
function buildCategoriesFilter(array $categories, array $selected, bool $newArr, bool $bestSell): string {
    $html = '<ul class="sidbar-style">';
    foreach ($categories as $c) {
        $checked = in_array($c['id'], $selected) ? ' checked' : '';
        $id      = 'category-' . $c['id'];
        $label   = htmlspecialchars($c['name'], ENT_QUOTES);
        $html  .= <<<HTML
<li class="form-check">
  <input class="form-check-input" type="checkbox" name="categories[]" value="{$c['id']}" id="{$id}"{$checked}>
  <label class="form-check-label" for="{$id}">{$label}</label>
</li>
HTML;
    }
    $checkedNew  = $newArr    ? ' checked' : '';
    $checkedBest = $bestSell  ? ' checked' : '';
    // two boolean filters
    $html .= <<<HTML
<li class="form-check mt-2">
  <input class="form-check-input" type="checkbox" name="new_arrival" id="filter-new"{$checkedNew}>
  <label class="form-check-label" for="filter-new">New Arrivals</label>
</li>
<li class="form-check">
  <input class="form-check-input" type="checkbox" name="best_seller" id="filter-best"{$checkedBest}>
  <label class="form-check-label" for="filter-best">Best Sellers</label>
</li>
HTML;
    $html .= '</ul>';
    return $html;
}

/////////////////////////gestione filtri fine

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

///////filtri + prodotti
$where = ['1=1'];
if (!empty($selectedTypes)) {
    $in = implode(',', array_map('intval', $selectedTypes));
    $where[] = "p.type_id IN ({$in})";
}
if (!empty($selectedFamilies)) {
    $in = implode(',', array_map('intval', $selectedFamilies));
    $where[] = "p.family_id IN ($in)";
}
if (!empty($selectedCategories)) {
    $in = implode(',', array_map('intval', $selectedCategories));
    $where[] = "p.category_id IN ({$in})";
}

if ($selectedNewArrival) {
    $where[] = "p.new_arrival = 1";
}
if ($selectedBestSeller) {
    $where[] = "p.best_seller = 1";
}

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
    -- variante con prezzo minore
    JOIN product_variants pv 
      ON pv.product_id = p.id
     AND pv.price = (
       SELECT MIN(price)
         FROM product_variants
        WHERE product_id = p.id
          AND price BETWEEN {$selectedMin} AND {$selectedMax}
     )
      WHERE " . implode(' AND ', $where) . "
  ORDER BY p.id, pv.size_ml
";
/////filtri fine


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
// build WHERE clause using existing filters
$whereSql = implode(' AND ', $where);
$res = $conn->query("
    SELECT 
      p.slug,
      p.img1_url,
      p.name,
      pv.price,
      p.new_arrival,
      p.best_seller,
      p.short_description,
      pv.id AS variant_id
    FROM products p
    JOIN product_variants pv 
      ON pv.product_id = p.id
     AND pv.price = (
       SELECT MIN(price)
         FROM product_variants
        WHERE product_id = p.id
          AND price BETWEEN {$selectedMin} AND {$selectedMax}
     )
      WHERE {$whereSql}
      ORDER BY p.id
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

//$products array con tutti i dati del prodotto lo passo alla view
$productJson = json_encode($products, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT);

// 4) Istanzio il sotto‐template per la pagina shop
$body = new Template("dtml/hator/shop");
// 5) Passo i dati già raggruppati
//$body->setContent("products",       $products);
//filtri tipo
$body->setContent('types_filter', buildTypesFilter($types, $selectedTypes));
//filtri famiglie
$body->setContent('families_filter', buildFamiliesFilter($families, $selectedFamilies));
//filtri tipi +new e best
$body->setContent('categories_filter',
    buildCategoriesFilter($categories, $selectedCategories, $selectedNewArrival, $selectedBestSeller)
);

$body->setContent("product_cards",  $cardsHtml);
$body->setContent("product_cards2", $cards2Html);
// 3) Calcolo il numero di prodotti
// 3) Calcolo il numero di prodotti
$body->setContent("product_count",  count($products));
// Pass price slider values to frame template
$main->setContent("selected_min", number_format($selectedMin, 2, '.', ''));
$main->setContent("selected_max", number_format($selectedMax, 2, '.', ''));
$main->setContent("global_min",   number_format($globalMin,   2, '.', ''));
$main->setContent("global_max",   number_format($globalMax,   2, '.', ''));
// pass min_price/max_price for slider JS
$main->setContent("min_price", number_format($globalMin, 2, '.', ''));
$main->setContent("max_price", number_format($globalMax, 2, '.', ''));

$main->setContent("productJson", $productJson);
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