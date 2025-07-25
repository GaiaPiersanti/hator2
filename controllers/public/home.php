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

$main->setContent("body", $body->get());
$main->close();
