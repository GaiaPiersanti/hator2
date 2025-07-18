<?php
class product extends tagLibrary {

  
    /**
     * Renderizza un singolo prodotto come card.
     *
     * @param string $name  Nome del tag (qui sempre "card")
     * @param array  $data  Array associativo con i dati del prodotto:
     *                      ['slug','img1_url','name','price','new_arrival','best_seller','variant_id']
     * @param array  $pars  Parametri extra se servono (qui non li usiamo)
     */
    public $selectors = ['card','card2','carted','details'];
    public function getSelectors() {
    return $this->selectors;
}

  //card è una versione per i prodotti nella grid-view
  //usata nella pagina shop.php

public function card($name, $data, $pars) {
     
    // dati base
    $urlDetails = "index.php?page=productdetails&slug=" . urlencode($data['slug']);
    $title      = htmlspecialchars($data['name'], ENT_QUOTES);
    $imgUrl     = htmlspecialchars($data['img1_url'], ENT_QUOTES);

    // marchio (new/bestseller)
    $sticker = !empty($data['new_arrival']) 
               ? '<span class="sticker-new">new</span>' 
               : '';

    // serializzo le varianti in un data-attr JSON
    $variantsJson = htmlspecialchars(json_encode($data['variants']), ENT_QUOTES);

    // Formatta il prezzo in anticipo
    $priceFormatted = number_format(
        $data['variants'][0]['price'] ?? 0,
        2,
        '.',
        ','
    );

    $imagesJson = htmlspecialchars(json_encode([
      $data['img1_url'],
      $data['img2_url'],
      $data['img3_url'],
      $data['img4_url']
    ]), ENT_QUOTES);


    $html  = '<div class="col-lg-4 col-md-6 mb-4">';
    $html .= '<div class="single-makal-product">';
    $html .= '<div class="pro-img">';
    $html .=   '<a href="'. $urlDetails .'">';
    $html .=     '<img src="'. $imgUrl .'" alt="'. $title .'" class="img-fluid">';
    $html .=   '</a>';
    $html .=   $sticker;
    $html .=   '<div class="quick-view-pro">';

    // qui “uscimo” dalla stringa e facciamo lo sprintf
    $html .= sprintf(
        '<a 
            data-bs-toggle="modal"
            data-bs-target="#product-window"
            data-title="%s"
            data-desc="%s"
            data-variants=\'%s\'
            data-images=\'%s\'
            data-type="%s"
            data-category="%s"
            data-brand="%s"
            data-family="%s"
            class="quick-view"
            href="#"
        ></a>',
        htmlspecialchars($data['name'],            ENT_QUOTES),
        htmlspecialchars($data['short_description'], ENT_QUOTES),
        $variantsJson,
        $imagesJson,
        htmlspecialchars($data['type_name'],     ENT_QUOTES),
        htmlspecialchars($data['category_name'], ENT_QUOTES),
        htmlspecialchars($data['brand_name'],    ENT_QUOTES),
        htmlspecialchars($data['family_name'],   ENT_QUOTES)
    );

    // poi richiudiamo il resto dell’HTML
    $html .=   '</div>';           // chiude .quick-view-pro
    $html .= '</div>';            // chiude .pro-img
    $html .= '<div class="pro-content">';
    $html .=   '<h4 class="pro-title"><a href="'. $urlDetails .'">'. $title .'</a></h4>';
    $html .=   '<p><span class="price">€'. $priceFormatted .'</span></p>';
    $html .=   '<div class="pro-actions">';
    $html .=     '<a href="index.php?page=cart&action=add&variant_id='
                . $data['variants'][0]['variant_id']
                .'" class="add-to-cart">Add To Cart</a>';
    $html .=   '</div>';           // chiude .pro-actions
    $html .= '</div>';            // chiude .pro-content
    $html .= '</div>';            // chiude .single-makal-product
    $html .= '</div>';            // chiude .col-...

    return $html;
}

  //card2 è una versione per i prodotti nella list-view
  //usata nella pagina shop.php
  public function card2($name, $data, $pars) {
    // if (is_array($data)) {
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>'; }

    // Se non abbiamo un array di dati valido, esci silenziosamente
    if (!is_array($data)) {
        return "";
    }

    // 1) Preleva con null‐coalesce i valori chiave
    $slug       = $data['slug']        ?? '';
    $imgUrl     = $data['img1_url']    ?? '';
    $nameVal    = $data['name']        ?? '';
    $priceVal   = $data['price']       ?? 0.00;
    $newArrival = !empty($data['new_arrival']);
    $bestSeller = !empty($data['best_seller']);
    $variantId  = $data['variant_id']  ?? '';
    $shortDesc  = $data['short_description'] ?? '';

    // Link alla pagina di dettaglio
    $urlDetails = "index.php?page=productdetails&slug=" . urlencode($slug);

    // Quale sticker mostrare?
    $sticker = "";
    if ($newArrival) {
        $sticker = '<span class="sticker-new2">new</span>';
    } 

    // Formatta il prezzo
    $priceFormatted = number_format($priceVal, 2, '.', ',');

    // Html escaping del titolo
    $title = htmlspecialchars($nameVal, ENT_QUOTES);

    // Se non c’è immagine, puoi usare un placeholder da inserire
    if ($imgUrl === "") {
        $imgUrl = "assets/img/placeholder.png";
    }

    // Ora costruisco l’HTML
    // Inizia con stringa vuota (niente wrapper di colonna)
    $html = '';

    // Blocco principale .single-makal-product
    $html .= '
    <!-- Single Product Start Here -->
    <div class="single-makal-product">
      <div class="pro-img2">
        <a href="'.$urlDetails.'">
          <img src="'.$imgUrl.'" alt="'.$title.'">
        </a>
        '.$sticker.'
      </div>
      <div class="pro-content">
        <h4 class="pro-title">
          <a href="'.$urlDetails.'">'.$title.'</a>
        </h4>
        <br>
        <p><span class="price rating">€'.$priceFormatted.'</span></p>
        <p>'.htmlspecialchars($shortDesc, ENT_QUOTES).'</p>
        <div class="pro-actions">
          <div class="actions-primary">
            
          </div>
        </div>
      </div>
    </div>
    <!-- Single Product End Here -->
    ';

    return $html;
  }


  public function carted($name, $data, $pars){
    // if (is_array($data)) {
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>'; }
    
    // Se non abbiamo un array di dati valido, esci silenziosamente
    if (!is_array($data)) {
        return "";
    }

    // 1) Preleva con null‐coalesce i valori chiave
    $slug       = $data['slug']        ?? '';
    $imgUrl     = $data['img1_url']    ?? '';
    $nameVal    = $data['name']        ?? '';
    $priceVal   = $data['price']       ?? 0.00;
    $sizeMl     = $data['size_ml']     ?? '';
    $variantId  = $data['variant_id']  ?? '';
    $stock = $data['stock'] ?? 0; // Disponibilità del prodotto

    // preparo la variabile $num rispetto a se è loggato omeno
    if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
        // Se l'utente non è loggato, prendo il carrello di sessione
        $num = $_SESSION['cart'][$variantId] ?? 1; // Default a 1 se non presente
    } else {
        // Se l'utente è loggato, prendo il numero dal database
        $num = $data['quantity'] ?? 1; // Default a 1 se non presente
    }
    // Link alla pagina di dettaglio
    $urlDetails = "index.php?page=productdetails&slug=" . urlencode($slug);

    // Formatta il prezzo
    $priceFormatted = number_format($priceVal, 2, '.', ',');
    
    // calcolo il totale
    $total = $num * $priceVal;

    // formatto il totale
    $totalFormatted = number_format($total, 2, '.', ',');

    // Html escaping del titolo
    $title = htmlspecialchars($nameVal, ENT_QUOTES);

    // Se non c’è immagine, puoi usare un placeholder
    if ($imgUrl === "") {
        $imgUrl = "assets/img/placeholder.png";
    }
    // Ora costruisci l’HTML
    $html  = '<tr data-variant-id="'. $variantId .'">
                  <td class="product-thumbnail">
                      <a href="#"><img src="' . $imgUrl . '" alt="' . $title . '" /></a>
                  </td>
                  <input name= "variant_id" type="hidden" value="' . $variantId . '">
                  <td class="product-name"> <a href="' . $urlDetails . '">' . $nameVal . '</a></td>
                  <td class="product-size" style="text-wrap: nowrap;">' . $sizeMl . ' ML' .'</td>
                  <td class="product-price"> <span class="amount">€' . $priceFormatted . '</span></td>
                  <td class="product-quantity"> <input type="number" class="quantity form-control" id = "quantity['. $variantId .']" value="' . $num . '" min="1" max="' . $stock . '" /></td>
                  <td class="product-subtotal">' . $totalFormatted . '</td>
                  <td class="product-remove"> <a href="index.php?page=cart&deleted=' . $variantId . '"> <i class="fa fa-times" aria-hidden="true"></i></a></td>
              </tr>';
    
    return $html;
  }
  
  
  
  
  


















/**
 * Ritorna il markup della pagina dettagli prodotto con placeholder vuoti.
 * Viene poi riempito in JavaScript.
 */
public function details($name, $data, $pars) {
    // NOTA: $data è l'array $product costruito in productdetails.php
    $html  = '<div class="main-product-thumbnail white-bg ptb-90">';
    $html .= '  <div class="container">';
    $html .= '    <div class="row">';
    $html .= '      <div class="col-lg-4 col-md-6 mb-all-40">';
    $html .= '        <div class="tab-content" id="myTabContent"></div>';
    $html .= '        <div class="product-thumbnail">';
    $html .= '          <ul class="thumb-menu owl-carousel nav tabs-area nav nav-tabs" '
           . 'id="myTab" role="tablist"></ul>';
    $html .= '        </div>';
    $html .= '      </div>';
    $html .= '      <div class="col-lg-8 col-md-6">';
    $html .= '        <div class="thubnail-desc fix">';
    $html .= '          <h3 class="product-header"></h3>';
    $html .= '          <ul class="product-meta list-unstyled mb-8">';
    $html .= '            <li class="mb-2">'
           . '<span class="product-type"></span>'
           . '<span class="product-category ms-2"></span></li>';
    $html .= '            <li class="mb-2"><strong>Brand:</strong> '
           . '<span class="product-brand"></span></li>';
    $html .= '            <li class="mb-8"><strong>Olfactory Family:</strong> '
           . '<span class="product-family"></span></li>';
    $html .= '          </ul>';
    $html .= '          <p class="pro-desc-details"></p>';
    $html .= '          <div class="pro-thumb-price mt-25">';
    $html .= '            <p class="d-flex align-items-center">'
           . '<span class="price fw-bold"></span></p>';
    $html .= '          </div>';
    $html .= '          <div class="product-size mtb-30 clearfix">';
    $html .= '            <label>Size</label>';
    $html .= '            <div class="select-wrapper">';
    $html .= '              <select id="page-variant-select" class="form-control"></select>';
    $html .= '            </div>';
    $html .= '          </div>';
    $html .= '          <div class="quatity-stock">';
    $html .= '            <label>Quantity</label>';
    $html .= '            <ul class="d-flex flex-wrap align-items-center">';
    $html .= '              <li class="box-quantity">'
           . '<!-- qui JS inietterà <input …> -->';
    $html .= '              </li>';
    $html .= '              <li>'
           . '<button id="add-to-cart-btn" class="pro-cart">'
           . 'add to cart</button></li>';
    $html .= '              <li class="pro-ref">'
           . '<p><span class="in-stock"></span></p></li>';
    $html .= '            </ul>';
    $html .= '          </div>';
    $html .= '        </div>';
    $html .= '      </div>';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '</div>';
    $html .= '<div class="thumnail-desc">';
    $html .= '  <div class="container">';
    $html .= '    <div class="thumb-desc-inner">';
    $html .= '      <div class="row">';
    $html .= '        <div class="col-12">';
    $html .= '          <ul class="main-thumb-desc nav tabs-area" role="tablist">';
    $html .= '            <li><h6><b>Description</b></h6></li>';
    $html .= '          </ul>';
    $html .= '          <div class="tab-content thumb-content">';
    $html .= '            <div id="dtail" class="tab-pane fade show active">';
    $html .= '              <p class="long-desc"></p>';
    $html .= '            </div>';
    $html .= '          </div>';
    $html .= '        </div>';
    $html .= '      </div>';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '</div>';
    return $html;
}

}