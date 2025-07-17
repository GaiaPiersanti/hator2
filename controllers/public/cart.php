<?php

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

$cart = "";
// questa parte è per testare la vvariabile di sessione 'cart'
/*unset($_SESSION['cart']);
$_SESSION['cart'][5] = 2; // Prodotto con ID 5, quantità 2
$_SESSION['cart'][10] = 1; // Prodotto con ID 10, quantità 1
$_SESSION['cart'][15] = 3; // Prodotto con ID 15, quantità 3*/

if(isset($_GET)){
    // ce c'è un aggiornamento
    if(isset($_GET['action']) && $_GET['action'] === 'update' && isset($_GET['variant_id']) && isset($_GET['quantity'])) {
        $quantity = intval($_GET['quantity']);
        $variantId = intval($_GET['variant_id']);
        if(isset($_SESSION['user'])){
            $userId = $_SESSION['user']['user_id'];
            $conn->query("UPDATE carts SET quantity = $quantity WHERE user_id = $userId AND product_id = $variantId");
        }else{
            // Se l'utente non è loggato, aggiorno il carrello di sessione
            if(isset($_SESSION['cart'][$variantId])) {
                $_SESSION['cart'][$variantId] = $quantity;
            }
        }
        header("Location: index.php?page=cart");
        exit;
    }
    if(isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['variant_id']) && isset($_GET['quantity'])) {
        $quantity = intval($_GET['quantity']);
        $variantId = intval($_GET['variant_id']);
        if(isset($_SESSION['user'])){
            $userId = $_SESSION['user']['user_id'];
            $conn->query("INSERT INTO carts (user_id, product_id, quantity) 
                              VALUES ($userId, $variantId, $quantity)
                              ON DUPLICATE KEY UPDATE quantity = quantity + $quantity");
        }else{
           if(isset($_SESSION['cart'])) {
                $_SESSION['cart'][$variantId] = $quantity;
            }
        }
        header("Location: index.php?page=cart");
        exit;
    }
}


if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    // Se l'utente non è loggato, prendo il carrello di sessione
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Preparo il carrello per la query    
    foreach ($_SESSION['cart'] as $variantId => $quantity) {
        // Preparo il carrello per la query
        $cart .= "$variantId,";
    }
    //inserisco un numero di defoult che non esiste nel db   
    $cart .= "0";
    // Eseguo la query per ottenere i prodotti nel carrello
    $res = $conn->query("
        SELECT p.slug,p.img1_url,p.name,pv.price,pv.id AS variant_id ,pv.size_ml, pv.stock
        FROM products p
        JOIN product_variants pv ON pv.product_id = p.id
        WHERE pv.id IN ($cart)
        ");

}else{
    // Se l'utente è loggato, prendo il carrello dal database
    $userId = $_SESSION['user']['user_id'];
    $res = $conn->query("
        SELECT p.slug,p.img1_url,p.name,pv.price,pv.id AS variant_id ,pv.size_ml,  pv.stock, c.quantity
        FROM carts c
        JOIN product_variants pv ON c.product_id = pv.id
        JOIN products p ON pv.product_id = p.id
        WHERE c.user_id = $userId
        ");  
        
}

// Preparo l'array dei prodotti nel carrello
$products = [];
if ($res) {
    // Se la query ha avuto successo, recupero i risultati
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}
// -> Costruisco l’HTML delle carrello qui, in PHP
require_once __DIR__ . '/../../include/tags/product.inc.php'; // se serve includere la libreria
$prodLib = new product();
$cartHtml = "";
$subtotale = 0;
if(isset($_SESSION['user'])) {
    foreach ($products as $prod) {
    // il primo argomento 'prod' è il nome del tag, il secondo i dati, il terzo i parametri (vuoti)
    $cartHtml .= $prodLib->carted('prod', $prod, []);
    $subtotale += $prod['price'] * ($prod['quantity'] ?? 1); // Calcolo il subtotale    
    }
} else {
   foreach ($products as $prod) {
    // il primo argomento 'prod' è il nome del tag, il secondo i dati, il terzo i parametri (vuoti)
    $cartHtml .= $prodLib->carted('prod', $prod, []);
    $subtotale += $prod['price'] * ($_SESSION['cart'][$prod['variant_id']] ?? 1); // Calcolo il subtotale    
    }
}

if(isset($_GET['deleted'])) {
    $elem = $_GET['deleted'];
    // metodo per cancellare un prodotto dal carrello
    if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
        // Rimuovi il prodotto dal carrello di sessione
        if (isset($_SESSION['cart'][$elem])) {
            unset($_SESSION['cart'][$elem]);
        }
        // Redirect o logica aggiuntiva dopo la rimozione
        header("Location: index.php?page=cart");
        exit;
        }else {
        $userId = $_SESSION['user']['user_id'];
        // Se l'utente è loggato, gestisci la rimozione dal database
        $conn->query("DELETE FROM carts WHERE user_id = $userId AND product_id = $elem");
        header("Location: index.php?page=cart");
        exit;
    }
}
if ($subtotale <= 0) {
    $totale = 0;
}else{
    $totale = $subtotale + 10; // Aggiungo 10 come spese di spedizione fisse
}
//formatto i totali
$subtotale = number_format($subtotale, 2, '.', ',');
$totale = number_format($totale, 2, '.', ',');
$carttotal =   '<tr class="cart-subtotal">
                    <th>Subtotal</th>
                    <td><span class="amount">€' . $subtotale . '</span></td>
                </tr>
                <tr class="order-total">
                    <th>Total</th>
                    <td>
                        <strong><span class="amount">€' . $totale . '</span></strong>
                    </td>
                </tr>';

// 2) Istanzio il sotto‐template per la pagina cart
$body = new Template("dtml/hator/cart");

// 3) Passo contenuti
$body->setContent("prodotti", $cartHtml);
$body->setContent("totali", $carttotal);
// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();

