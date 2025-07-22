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

// 1d) Carico tutti i brands
$brandRes = $conn->query("SELECT id, name FROM brands ORDER BY name");
$brands   = $brandRes->fetch_all(MYSQLI_ASSOC);

// 2) Vengono passati in GET come types[]=1&types[]=3…
$selectedTypes = $_GET['types'] ?? [];
// 2b) families
$selectedFamilies = $_GET['families'] ?? [];
// 2c) GET per categories e flags

$selectedCategories    = $_GET['categories']    ?? [];
$selectedNewArrival    = isset($_GET['new_arrival']);
$selectedBestSeller    = isset($_GET['best_seller']);
$selectedBrands = $_GET['brands'] ?? [];

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

// 4) Sort logic
$allowedSort = ['relevance','name_asc','name_desc','price_asc','price_desc'];
$selectedSort = $_GET['sort'] ?? 'relevance';
if (!in_array($selectedSort, $allowedSort)) {
    $selectedSort = 'relevance';
}
switch ($selectedSort) {
    case 'name_asc':
        $orderBySql = 'p.name ASC';
        break;
    case 'name_desc':
        $orderBySql = 'p.name DESC';
        break;
    case 'price_asc':
        $orderBySql = 'pv.price ASC';
        break;
    case 'price_desc':
        $orderBySql = 'pv.price DESC';
        break;
    default:
        $orderBySql = 'p.id ASC';
}


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

// 3c) Funzione helper per montare l’HTML dei checkbox dei brands
function buildBrandsFilter(array $brands, array $selectedBrands): string {
    $html = '<ul class="sidbar-style">';
    foreach ($brands as $b) {
        $checked = in_array($b['id'], $selectedBrands) ? ' checked' : '';
        $id      = 'brand-' . $b['id'];
        $label   = htmlspecialchars($b['name'], ENT_QUOTES);
        $html  .= <<<HTML
<li class="form-check">
  <input class="form-check-input" type="checkbox" name="brands[]" value="{$b['id']}" id="{$id}"{$checked}>
  <label class="form-check-label" for="{$id}">{$label}</label>
</li>
HTML;
    }
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
if (!empty($selectedBrands)) {
    $in = implode(',', array_map('intval', $selectedBrands));
    $where[] = "p.brand_id IN ({$in})";
}

// 3d) Search filter by product name, brand, category, or type
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $esc = $conn->real_escape_string($search);
    $where[] = "(
        p.name      LIKE '%{$esc}%'
     OR b.name      LIKE '%{$esc}%'
     OR c.name      LIKE '%{$esc}%'
     OR t.name      LIKE '%{$esc}%'
    )";
}

// 5) Pagination setup
$perPage  = 12;
$page_num = isset($_GET['page_num']) && is_numeric($_GET['page_num']) && $_GET['page_num'] > 0
            ? intval($_GET['page_num']) : 1;
$offset   = ($page_num - 1) * $perPage;

// count total matching products
$countSql = "
  SELECT COUNT(DISTINCT p.id) AS cnt
    FROM products p
    JOIN types      t ON t.id = p.type_id
    JOIN categories c ON c.id = p.category_id
    JOIN brands     b ON b.id = p.brand_id
    JOIN product_variants pv
      ON pv.product_id = p.id
     AND pv.price BETWEEN {$selectedMin} AND {$selectedMax}
   WHERE " . implode(' AND ', $where);
$countRes     = $conn->query($countSql);
$total_items  = (int)$countRes->fetch_assoc()['cnt'];


// main query with LIMIT/OFFSET for pagination
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
  ORDER BY {$orderBySql}
  LIMIT {$perPage} OFFSET {$offset}
";
/////filtri fine


$res = $conn->query($sql);

$products = [];
while ($row = $res->fetch_assoc()) {
    $pid = $row['pid'];

    if (!isset($products[$pid])) {
        // inizializzo TUTTI i campi del prodotto
        $products[$pid] = [
            'pid'                 => $pid,
            'filtered_variant_id' => $row['variant_id'],
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
            // i nuovi campi
            'type_name'           => $row['type_name'],
            'category_name'       => $row['category_name'],
            'brand_name'          => $row['brand_name'],
            'family_name'         => $row['family_name'],
            // array per le varianti
            'variants'            => []
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

// 6) Pagination item range
$shownCount = count($products);
$start_item = $offset + 1;
$end_item   = $offset + $shownCount;
if ($end_item > $total_items) {
    $end_item = $total_items;
}



// build pagination links
$total_pages = ceil($total_items / $perPage);
$linksHtml = '';
if ($total_pages > 1) {
    // Previous link
    if ($page_num > 1) {
        $prev = $page_num - 1;
        $qs = http_build_query(array_merge($_GET, ['page_num' => $prev]));
        $linksHtml .= '<li class="float-left prev"><a href="?' . $qs . '"><i class="fa fa-angle-left" aria-hidden="true"></i> Previous</a></li>';
    } else {
        $linksHtml .= '<li class="float-left prev disabled"><span><i class="fa fa-angle-left" aria-hidden="true"></i> Previous</span></li>';
    }
    // Page number links
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i === $page_num) ? ' class="active"' : '';
        $qs = http_build_query(array_merge($_GET, ['page_num' => $i]));
        $linksHtml .= '<li' . $active . '><a href="?' . $qs . '">' . $i . '</a></li>';
    }
    // Next link
    if ($page_num < $total_pages) {
        $next = $page_num + 1;
        $qs = http_build_query(array_merge($_GET, ['page_num' => $next]));
        $linksHtml .= '<li class="float-right next"><a href="?' . $qs . '">Next <i class="fa fa-angle-right" aria-hidden="true"></i></a></li>';
    } else {
        $linksHtml .= '<li class="float-right next disabled"><span>Next <i class="fa fa-angle-right" aria-hidden="true"></i></span></li>';
    }
}


// fetch full list of variants for each product for the modal
$allVariants = [];
$productIds = array_column($products, 'pid');
if (!empty($productIds)) {
    $ids = implode(',', array_map('intval', $productIds));
    $varRes = $conn->query("
        SELECT id AS variant_id, product_id, size_ml, price, stock
          FROM product_variants
         WHERE product_id IN ({$ids})
         ORDER BY size_ml
    ");
    while ($vr = $varRes->fetch_assoc()) {
        $allVariants[$vr['product_id']][] = $vr;
    }
}
// reorder variants: filtered variant first, then all others
foreach ($products as &$prod) {
    $filteredId = $prod['filtered_variant_id'];
    $variants = $allVariants[$prod['pid']] ?? [];
    usort($variants, function($a, $b) use ($filteredId) {
        if ($a['variant_id'] == $filteredId) return -1;
        if ($b['variant_id'] == $filteredId) return 1;
        return 0;
    });
    $prod['variants'] = $variants;
}
unset($prod);

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
    JOIN types      t ON t.id = p.type_id
    JOIN categories c ON c.id = p.category_id
    JOIN brands     b ON b.id = p.brand_id
    JOIN product_variants pv 
      ON pv.product_id = p.id
     AND pv.price = (
       SELECT MIN(price)
         FROM product_variants
        WHERE product_id = p.id
          AND price BETWEEN {$selectedMin} AND {$selectedMax}
     )
    WHERE {$whereSql}
    ORDER BY {$orderBySql}
    LIMIT {$perPage} OFFSET {$offset}
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
$body->setContent('brands_filter', buildBrandsFilter($brands, $selectedBrands));
//filtri tipi +new e best
$body->setContent('categories_filter',
    buildCategoriesFilter($categories, $selectedCategories, $selectedNewArrival, $selectedBestSeller)
);
// build sort options for the toolbar
function buildSortOptions(string $selectedSort): string {
    $options = [
        'relevance'  => 'Relevance',
        'name_asc'   => 'Name, A to Z',
        'name_desc'  => 'Name, Z to A',
        'price_asc'  => 'Price low to high',
        'price_desc' => 'Price high to low',
    ];
    $html = '';
    foreach ($options as $value => $label) {
        $sel = ($selectedSort === $value) ? ' selected' : '';
        $html .= "<option value=\"{$value}\"{$sel}>{$label}</option>";
    }
    return $html;
}

$body->setContent('sort_options', buildSortOptions($selectedSort));

// pass search term to template
$body->setContent('search', htmlspecialchars($search, ENT_QUOTES));

$body->setContent("product_cards",  $cardsHtml);
$body->setContent("product_cards2", $cards2Html);
// top product count = total matching items
$body->setContent("product_count",  $total_items);
// Pass price slider values to frame template
$main->setContent("selected_min", number_format($selectedMin, 2, '.', ''));
$main->setContent("selected_max", number_format($selectedMax, 2, '.', ''));
$main->setContent("global_min",   number_format($globalMin,   2, '.', ''));
$main->setContent("global_max",   number_format($globalMax,   2, '.', ''));
// pass min_price/max_price for slider JS
$main->setContent("min_price", number_format($globalMin, 2, '.', ''));
$main->setContent("max_price", number_format($globalMax, 2, '.', ''));

//pagination
$body->setContent('pagination_links', $linksHtml);
// pass pagination data to view
$body->setContent('start_item', $start_item);
$body->setContent('end_item',   $end_item);
$body->setContent('total_items',$total_items);
$body->setContent('current_page',$page_num);
$total_pages = ceil($total_items / $perPage);
$body->setContent('total_pages', $total_pages);

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