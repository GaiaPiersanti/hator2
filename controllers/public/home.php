<?php

// 6) renderizzo il template
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 1) Prendi tutti i banner di tipo “hero”, ordinati per “ordine”
$stmt = $conn->prepare("
  SELECT b.img_url,
         b.link AS brand_slug,
         br.id   AS brand_id
    FROM banners b
LEFT JOIN brands br ON br.slug = b.link
   WHERE b.tipo = 'hero'
ORDER BY b.ordine ASC
");
$stmt->execute();
$banners = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 2) Costruisci un HTML dinamico
$heroHtml = '<div class="hero-banner-area"><div class="container-fluid"><div class="row">';

foreach ($banners as $index => $ban) {
    // Decidi la struttura in base alla posizione (index)
    if ($index === 0) {
        // primo banner a tutta larghezza col-md-6
        $heroHtml .= '
        <div class="col-md-6 col-sm-6 mb-xsm-30 banner-large" style="padding-right: 15px; padding-left: 15px;">
          <div class="single-banner zoom">
            <a href="' . htmlspecialchars('index.php?page=shop&brands[]=' . intval($ban['brand_id'])) . '">
              <img src="' . htmlspecialchars($ban['img_url']) . '" alt="banner-img">
            </a>
          </div>
        </div>';
    } elseif ($index >= 1 && $index <= 2) {
        // secondo e terzo banner nella col-md-6 superiore
        if ($index === 1) {
            $heroHtml .= '<div class="col-md-6 col-sm-6">
                           <div class="banner-inner-top"><div class="row">';
        }
        $heroHtml .= '
        <div class="col-md-6 col-sm-6 col-6 banner-small">
          <div class="single-banner zoom">
            <a href="' . htmlspecialchars('index.php?page=shop&brands[]=' . intval($ban['brand_id'])) . '">
              <img src="' . htmlspecialchars($ban['img_url']) . '" alt="banner-img">
            </a>
          </div>
        </div>';
        if ($index === 2) {
            $heroHtml .= '</div></div>';
        }
    } elseif ($index === 3) {
        // quarto banner in basso, dentro lo stesso col-md-6 dei banner-small
        $heroHtml .= '
          <div class="banner-inner-bottom single-banner zoom banner-wide" style="margin-top: 30px;">
            <a href="' . htmlspecialchars('index.php?page=shop&brands[]=' . intval($ban['brand_id'])) . '">
              <img src="' . htmlspecialchars($ban['img_url']) . '" alt="banner-img">
            </a>
          </div>
        </div>';  // chiude il div col-md-6 aperto per i banner-small
    }
}

// chiudi i div di row/container
$heroHtml .= '</div></div></div>';

// 3) Passa al template



$body = new Template("dtml/hator/home");
$body->setContent('heroBanner', $heroHtml);

// 4) Fetch all best-seller products
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
    pv.id              AS filtered_variant_id
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
  WHERE p.best_seller = 1
  GROUP BY p.id
  ORDER BY p.name ASC
";
$stmtBest = $conn->prepare($sql);
$stmtBest->execute();
$resBest = $stmtBest->get_result();

// 5) Build best-sellers array
$bestSellers = [];
while ($row = $resBest->fetch_assoc()) {
    $bestSellers[$row['pid']] = $row;
}
$bestSellers = array_values($bestSellers);

// 6) Fetch and attach variants for best sellers
$allVariants = [];
$ids = array_column($bestSellers, 'pid');
if (!empty($ids)) {
    $idsList = implode(',', array_map('intval', $ids));
    $varRes = $conn->query("
        SELECT id AS variant_id, product_id, size_ml, price, stock
          FROM product_variants
         WHERE product_id IN ({$idsList})
         ORDER BY size_ml
    ");
    while ($vr = $varRes->fetch_assoc()) {
        $allVariants[$vr['product_id']][] = $vr;
    }
}
foreach ($bestSellers as &$prod) {
    $filteredId = $prod['filtered_variant_id'];
    $vars = $allVariants[$prod['pid']] ?? [];
    usort($vars, function($a, $b) use ($filteredId) {
        if ($a['variant_id'] == $filteredId) return -1;
        if ($b['variant_id'] == $filteredId) return 1;
        return 0;
    });
    $prod['variants'] = $vars;
}
unset($prod);

// 7) Render best-seller cards
$prodLib = new product();
$bestHtml = '';
foreach ($bestSellers as $prod) {
    $bestHtml .= $prodLib->card_related('card', $prod, []);
}

// 8) Pass best-sellers HTML to template
$body->setContent('bestSellers', $bestHtml);

// Fetch IDs di Men e Women per i banner
$catRes = $conn->query("
  SELECT id, name
    FROM categories
   WHERE name IN ('Men', 'Women')
");
$menCatId   = 0;
$womenCatId = 0;
while ($row = $catRes->fetch_assoc()) {
    if ($row['name'] === 'Men') {
        $menCatId = (int)$row['id'];
    } elseif ($row['name'] === 'Women') {
        $womenCatId = (int)$row['id'];
    }
}
// Passo gli ID al template
$body->setContent('menCatId',   $menCatId);
$body->setContent('womenCatId', $womenCatId);


// 9) Fetch all new-arrival products
$sqlNew = "
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
    pv.id              AS filtered_variant_id
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
  WHERE p.new_arrival = 1
  GROUP BY p.id
  ORDER BY p.name ASC
";
$stmtNew = $conn->prepare($sqlNew);
$stmtNew->execute();
$resNew = $stmtNew->get_result();

// 10) Build new-arrivals array
$newArrivals = [];
while ($row = $resNew->fetch_assoc()) {
    $newArrivals[$row['pid']] = $row;
}
$newArrivals = array_values($newArrivals);

// 11) Fetch and attach variants for new arrivals
$allNewVariants = [];
$newIds = array_column($newArrivals, 'pid');
if (!empty($newIds)) {
    $idsListNew = implode(',', array_map('intval', $newIds));
    $varResNew = $conn->query("
        SELECT id AS variant_id, product_id, size_ml, price, stock
          FROM product_variants
         WHERE product_id IN ({$idsListNew})
         ORDER BY size_ml
    ");
    while ($vr = $varResNew->fetch_assoc()) {
        $allNewVariants[$vr['product_id']][] = $vr;
    }
}
foreach ($newArrivals as &$prodNew) {
    $filteredIdNew = $prodNew['filtered_variant_id'];
    $varsNew = $allNewVariants[$prodNew['pid']] ?? [];
    usort($varsNew, function($a, $b) use ($filteredIdNew) {
        if ($a['variant_id'] == $filteredIdNew) return -1;
        if ($b['variant_id'] == $filteredIdNew) return 1;
        return 0;
    });
    $prodNew['variants'] = $varsNew;
}
unset($prodNew);

// 12) Render new-arrival cards
$newHtml = '';
foreach ($newArrivals as $prodNew) {
    $newHtml .= $prodLib->card_related('card', $prodNew, []);
}

// 13) Pass new-arrivals HTML to template

$body->setContent('newArrivals', $newHtml);

// 14) Fetch the four main types for the category slider
$typeNames = ['Eau de cologne', 'eau de parfum', 'eau de toilette', 'parfum'];
$quoted = "'" . implode("','", $typeNames) . "'";
$typeRes = $conn->query("
  SELECT id, name
    FROM types
   WHERE name IN ($quoted)
ORDER BY FIELD(name, $quoted)
");
$typeHtml = '';
while ($type = $typeRes->fetch_assoc()) {
    $slug = strtolower(str_replace(' ', '-', $type['name']));
    $imgPath = 'dtml/hator/assets/img/category/' . $slug . '.svg';
    $href    = 'index.php?page=shop&types[]=' . intval($type['id']);
    $typeHtml .= '
    <div class="single-categorie">
      <div class="cat-img">
        <a  href="' . htmlspecialchars($href) . '">
          <img src="' . htmlspecialchars($imgPath) . '" alt="' . htmlspecialchars($type['name']) . '">
        </a>
        <div class="cat-content">
          <a href="' . htmlspecialchars($href) . '">' . htmlspecialchars($type['name']) . '</a>
        </div>
      </div>
    </div>';
}
$body->setContent('typeSlider', $typeHtml);

$main->setContent("body", $body->get());
$main->close();
