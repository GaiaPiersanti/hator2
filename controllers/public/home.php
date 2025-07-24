<?php
// 1) init (connessione $conn, Template, ecc.)


// 2) prendo i banner slider
$stmt = $conn->prepare("SELECT * FROM banners WHERE tipo = 'slider' ORDER BY ordine");
$stmt->execute();
$slides = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3) prendo i banner hero
$stmt = $conn->prepare("SELECT * FROM banners WHERE tipo = 'hero' ORDER BY ordine");
$stmt->execute();
$heros  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4) costruisco l’HTML dello slider
$sliderHtml  = '<div class="slider-area slider-style-three">';
$sliderHtml .=   '<div class="slider-activation owl-carousel">';
foreach ($slides as $b) {
    $id    = htmlspecialchars($b['id'],    ENT_QUOTES);
    $img   = htmlspecialchars($b['img_url'],ENT_QUOTES);
    $link  = htmlspecialchars($b['link'],   ENT_QUOTES);
    $text  = htmlspecialchars($b['testo'],  ENT_QUOTES);
    $label = htmlspecialchars($b['nome'],   ENT_QUOTES);

    $sliderHtml .= "<div class=\"slide align-center-left fullscreen animation-style-{$id}\" style=\"background-image:url('{$img}')\">";
    $sliderHtml .=   '<div class="slider-progress"></div>';
    $sliderHtml .=   '<div class="container"><div class="row"><div class="col-lg-12"><div class="slider-content">';
    if ($text !== '') {
        $sliderHtml .= "<p style=\"color: black;\">{$text}</p>";
    }
    $sliderHtml .=     "<div class=\"slide-btn white-color\"><a href=\"{$link}\">{$label}</a></div>";
    $sliderHtml .=   '</div></div></div></div>';
    $sliderHtml .= '</div>';
}
$sliderHtml .=   '</div>';
$sliderHtml .= '</div>';

// 5) costruisco l’HTML dell’hero-banner
$heroHtml   = '<div class="hero-banner-area">';
$heroHtml  .=   '<div class="container-fluid"><div class="row">';
foreach ($heros as $b) {
    $link = htmlspecialchars($b['link'],   ENT_QUOTES);
    $img  = htmlspecialchars($b['img_url'],ENT_QUOTES);
    $alt  = htmlspecialchars($b['nome'],   ENT_QUOTES);

    $heroHtml .= '<div class="col-md-6 col-sm-6 mb-xsm-30">';
    $heroHtml .=   '<div class="single-banner zoom">';
    $heroHtml .=     "<a href=\"{$link}\"><img src=\"{$img}\" alt=\"{$alt}\"></a>";
    $heroHtml .=   '</div>';
    $heroHtml .= '</div>';
}
$heroHtml  .=   '</div></div>';
$heroHtml  .= '</div>';

// 6) renderizzo il template
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

$body = new Template("dtml/hator/home");
$body->setContent("slider_section", $sliderHtml);
$body->setContent("hero_section",   $heroHtml);

$main->setContent("body", $body->get());
$main->close();
