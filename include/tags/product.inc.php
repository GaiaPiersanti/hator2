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
    public $selectors = ['card'];
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
            class="quick-view"
            href="#"
        ></a>',
        htmlspecialchars($data['name'],            ENT_QUOTES),
        htmlspecialchars($data['short_description'], ENT_QUOTES),
        $variantsJson,
        $imagesJson
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
    $html  = '<tr>
                  <td class="product-thumbnail">
                      <a href="#"><img src="' . $imgUrl . '" alt="' . $title . '" /></a>
                  </td>
                  <input name= id type="hidden" value="' . $variantId . '">
                  <td class="product-name"> <a href="' . $urlDetails . '">' . $nameVal . '</a></td>
                  <td class="product-size" style="text-wrap: nowrap;">' . $sizeMl . ' ML' .'</td>
                  <td class="product-price"> <span class="amount">€' . $priceFormatted . '</span></td>
                  <td class="product-quantity"> <input type="number" value="' . $num . '" min="1" /></td>
                  <td class="product-subtotal">' . $totalFormatted . '</td>
                  <td class="product-remove"> <a href="index.php?page=cart&deleted=' . $variantId . '"> <i class="fa fa-times" aria-hidden="true"></i></a></td>
              </tr>';
    
    return $html;
  }
  
  
  
  
  
}