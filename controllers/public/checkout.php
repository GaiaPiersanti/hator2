<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 2) Istanzio il sotto‐template per la pagina checkout
$body = new Template("dtml/hator/checkout");

if(isset($_SESSION['back']))
{
    $id =$_SESSION['back']['id'];
    $tot =$_SESSION['back']['totale'];
    header("Location: index.php?page=pagamento&id=$id&total=$tot");
}

// carico i dati nei campi
if(isset($_SESSION['dati_checkout']))
{//in caso ci sia stato un errore
    if(isset($_SESSION['dati_checkout']['error'])){
        $body->setContent("check_error", $_SESSION['dati_checkout']['error'] ?? '');
    }
    
    if(isset($_SESSION['dati_checkout']['ship-box']))
    {//carico i campi del shipping to another address (tic)
        $body->setContent('first_name2', $_SESSION['dati_checkout']['first_name2'] ?? '');
        $body->setContent('last_name2', $_SESSION['dati_checkout']['last_name2'] ?? '');
        $body->setContent('address_street2', $_SESSION['dati_checkout']['address_street2'] ?? '');
        $body->setContent('town2', $_SESSION['dati_checkout']['town2'] ?? '');
        $body->setContent('state2', $_SESSION['dati_checkout']['state2'] ?? '');
        $body->setContent('postcode2', $_SESSION['dati_checkout']['postcode2'] ?? '');
        $body->setContent('email2', $_SESSION['dati_checkout']['email2'] ?? '');
        $body->setContent('phone2', $_SESSION['dati_checkout']['phone2'] ?? '');
        $body->setContent('address_dettail2', $_SESSION['dati_checkout']['address_dettail2'] ?? '');
        $body->setContent('checkout-mess', $_SESSION['dati_checkout']['checkout-mess'] ?? '');
    }else
    {//carico i campi del billing details
        $body->setContent("first_name", $_SESSION['dati_checkout']['first_name'] ?? '');
        $body->setContent("last_name", $_SESSION['dati_checkout']['last_name'] ?? '');
        $body->setContent("address_street", $_SESSION['dati_checkout']['address_street'] ?? '');
        $body->setContent("town", $_SESSION['dati_checkout']['town'] ?? '');
        $body->setContent("state", $_SESSION['dati_checkout']['state'] ?? '');
        $body->setContent("postcode", $_SESSION['dati_checkout']['postcode'] ?? '');
        $body->setContent("email", $_SESSION['dati_checkout']['email'] ?? '');
        $body->setContent("phone", $_SESSION['dati_checkout']['phone'] ?? '');
        $body->setContent("address_dettail", $_SESSION['dati_checkout']['address_dettail'] ?? '');
    }
    unset($_SESSION['dati_checkout']);

}else
{//caso standard entri per la prima volta ti carica ciò che sappiamo di te
    $body->setContent("first_name", $_SESSION['user']['first_name'] ?? '');
    $body->setContent("last_name", htmlspecialchars($_SESSION['user']['last_name'],   ENT_QUOTES) ?? '');
    $body->setContent("address_street", '');
    $body->setContent("town", '');
    $body->setContent("state", '');
    $body->setContent("postcode", '');
    $body->setContent("email", $_SESSION['user']['email'] ?? '');
    $body->setContent("phone", '');
    $body->setContent("address_dettail", '');
}

$userId = $_SESSION['user']['user_id'] ?? 0;
    $stmt = $conn->prepare("
        SELECT pv.price, c.quantity
        FROM carts c
        JOIN product_variants pv ON c.product_id = pv.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $totale = 0;
    while($prod = $res->fetch_assoc())
    {
        $totale += $prod['price'] * $prod['quantity'];
    }
    if ($totale === 0){
        $stop = 1;
        $body->setContent("check_error", "There are no products to checkout.");
        $main->setContent("body", $body->get());
        $main->close();
        exit;
    }else{
        $stop = 0;
        $totale += 10;
        $totale = number_format($totale, 2, '.', ',');
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$stop)
{
    
    //salvo (e controllo che) tutti i campi (obbligatori siano riempiti)
    if(!isset($_POST['ship-box']) && !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['address_street']) && !empty($_POST['town']) && !empty($_POST['state']) && !empty($_POST['postcode']) && !empty($_POST['email']) && !empty($_POST['phone']))
    { //qui tutto è riempito e non è stato selezonato il tic no errore
        $ship_box = 0;
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address_street = $_POST['address_street'];
        $town = $_POST['town'];
        $country = $_POST['country'];
        $state = $_POST['state'];
        $postcode = $_POST['postcode'];
        
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $address_dettail = isset($_POST['address_dettail']) ? $_POST['address_dettail'] : '';
        $checkout_mess = '';
    }else if(isset($_POST['ship-box']) && !empty($_POST['first_name2']) && !empty($_POST['last_name2']) && !empty($_POST['address_street2']) && !empty($_POST['town2']) && !empty($_POST['state2']) && !empty($_POST['postcode2']) && !empty($_POST['email2']) && !empty($_POST['phone2']))
    {//tic e tutti riempiti no errore
        $ship_box = $_POST['ship-box'];
        $first_name = $_POST['first_name2'];
        $last_name = $_POST['last_name2'];
        $address_street = $_POST['address_street2'];
        $town = $_POST['town2'];
        $country = $_POST['country2'];
        $state = $_POST['state2'];
        $postcode = $_POST['postcode2'];
        $email = $_POST['email2'];
        $phone = $_POST['phone2'];
        $address_dettail = isset($_POST['address_dettail2']) ? $_POST['address_dettail2'] : '';
        $checkout_mess = isset($_POST['checkout-mess']) ? $_POST['checkout-mess'] : '';
    }else 
    {
        $_SESSION['dati_checkout'] = $_POST;
        $_SESSION['dati_checkout']['error'] = "Please fill in all required fields.";
        header("Location: index.php?page=checkout");  
        
    }

    //controllo che i campi postcode e phon number siano composti solo da numeri
    if(!isset($_SESSION['dati_checkout']['error']) && !ctype_digit($postcode)){
            $_SESSION['dati_checkout'] = $_POST;
            $_SESSION['dati_checkout']['error'] = "Please entre a plusible Zip/Postcode.";
            header("Location: index.php?page=checkout");
    }
    if(!isset($_SESSION['dati_checkout']['error']) && !ctype_digit($phone)){
            $_SESSION['dati_checkout'] = $_POST;
            $_SESSION['dati_checkout']['error'] = "Please entre a plusible phon number.";
            header("Location: index.php?page=checkout");
    }
    $metodo_pagamento = '';
    //controllo che sia stato selezionato un metodo di pagamento
    if(!isset($_POST['metodo_pagamento']) && !isset($_SESSION['dati_checkout']['error']))
    {
        $_SESSION['dati_checkout'] = $_POST;
        $_SESSION['dati_checkout']['error'] = "Please select a Payment Method.";
        header("Location: index.php?page=checkout");
        
        
    }else if(!isset($_SESSION['dati_checkout']['error']))// faccio le operazioni per creare l'ordine
    {
        $metodo_pagamento = $_POST['metodo_pagamento'];       

        //creo l'id del pacco a partire dal id di dimensione massima
        $stmt = $conn->prepare("SELECT MAX(id) AS id FROM packages");
        $stmt->execute();
        $pidRes = $stmt->get_result()->fetch_assoc();
        $package = $pidRes['id'] + 1;

        //creo ed inserisco il pacco nel db e riduco lo stock
        $stmt = $conn->prepare("
            SELECT c.product_id, c.quantity, pv.stock
            FROM carts c
            JOIN product_variants pv ON c.product_id = pv.id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        // Prepare insert into packages
        $insertPkg = $conn->prepare("
            INSERT INTO packages (id, product_id, quantity)
            VALUES (?, ?, ?)
        ");
        // Prepare update of stock
        $updateStock = $conn->prepare("
            UPDATE product_variants SET stock = ? WHERE id = ?
        ");

        while ($prod = $res->fetch_assoc()) {
            $newStock = $prod['stock'] - $prod['quantity'];
            $insertPkg->bind_param('iii', $package, $prod['product_id'], $prod['quantity']);
            $insertPkg->execute();
            $updateStock->bind_param('ii', $newStock, $prod['product_id']);
            $updateStock->execute();
        }

        // Elimina il carrello dell'utente dopo aver creato il pacco
        $stmt = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        //aggiungo l'ordine
        $stmt = $conn->prepare("
            INSERT INTO shipments 
              (user_id, package_id, method_of_payment, total,
               country, state, town, postcode,
               street, address_dettail,
               first_name, last_name, email, phone,
               message, processed, date_request)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param(
            'iisdsssssssssss',
            $userId,
            $package,
            $metodo_pagamento,
            $totale,
            $country,
            $state,
            $town,
            $postcode,
            $address_street,
            $address_dettail,
            $first_name,
            $last_name,
            $email,
            $phone,
            $checkout_mess
        );
        $stmt->execute();
        $res = $stmt->insert_id;
        
        
    }
    //passo a una delle pagine di riepilogo portandomi dietro i dati necessari
    switch ($metodo_pagamento) {
        case 'paypal':
            header("Location: index.php?page=pagamento&id=$res&total=$totale");
            break;
        case 'bank_transfer':
            header("Location: index.php?page=riepilogo&metodo=bank_transfer&id=$res");
            break;
        case 'cheque':
            header("Location: index.php?page=riepilogo&metodo=cheque&id=$res");
            break;
        default:

                
    }
}



// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();