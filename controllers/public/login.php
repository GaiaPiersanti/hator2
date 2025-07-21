<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);
//	__DIR__ è la costante che PHP risolve nella cartella in cui risiede il file corrente.
if(isset($_SESSION['loggedin']) && isset($_SESSION['user'])&& $_SESSION['user']['group_id'] !== 1) {
    header("Location: index.php?page=logout");
    exit;
}

$login_error = "";
if (isset($_SESSION['ForgottenP'])) {
    $login_error = $_SESSION['ForgottenP'];
    unset($_SESSION['ForgottenP']);
}
// Se arrivo in POST, processa il login
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['email'], $_POST['password'])) {

    // Pulisco l’input e genero l’hash
    $email = $conn->real_escape_string($_POST['email']);
    $hash  = cifratura($_POST['password'], $email);

    // Cerco l’utente
    $result = $conn->query(
      "SELECT email, first_name, last_name, group_id, id AS user_id
       FROM users 
       WHERE email='$email' 
       AND password='$hash'"
    );

    if (!$result) {
        die("DB error: " . $conn->error);
    } elseif ($result->num_rows === 0) {
        // email o password sbagliati
        $login_error = "Incorrect email or password.";
        // preservo la mail inserita per ripopolare il campo
        $old_email = htmlspecialchars($_POST['email'], ENT_QUOTES);
    } else {
         // login OK: setto la sessione e redirect
        $user = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['user']     = $user;

 // Debug: log session after successful login
//error_log("DEBUG SESSION after login: " . print_r($_SESSION, true));


      //se ti slogghi e ti rilogghi nel frattempo la stock
    if (isset($_SESSION['cart'])) {
            // Se l'utente ha un carrello in sessione, lo sposto nel database
            $userId = $_SESSION['user']['user_id'];            
            foreach ($_SESSION['cart'] as $variantId => $quantity) {
                //controlla che nel carrello dell'utente ci sia un prodotto con l'id di cui andare a controllare lo stock, nel caso nel carrelo 
                //ci sia già prende la qtity del carrello e fa la differenza con lo stock, se la quantità è minore ce la mette altrimenti ci mette il risultato della differenza (ovvero la differenza)
                $res = ($conn->query("SELECT * FROM carts AS c  WHERE c.product_id = $variantId AND c.user_id = $userId"));
                $num_righe_affette = $conn->affected_rows;

                if($num_righe_affette > 0 ){
                    error_log("res: " . print_r($res, true));
                    //prende dei prodotti che stanno nel carrello per fare la differenza e controllare che non supera la stock
                    $prod = ($conn->query("SELECT pv.stock, c.quantity FROM product_variants AS pv JOIN carts AS c ON pv.id = c.product_id WHERE c.product_id = $variantId AND c.user_id = $userId"))->fetch_assoc();
                    if (($prod['stock'] - $prod['quantity']) < $quantity) {
                        // Se la quantità richiesta è superiore allo stock, imposta la quantità massima disponibile
                        $quantity = ($prod['stock'] - $prod['quantity']);
                    }
                }
                //aggiorna il carrello caricando quello che sta in sessione nel carrello del db
                $conn->query("INSERT INTO carts (user_id, product_id, quantity) 
                              VALUES ($userId, $variantId, $quantity)
                              ON DUPLICATE KEY UPDATE quantity = quantity + $quantity");
            }
            // Pulisci il carrello di sessione
            unset($_SESSION['cart']);
        }
        switch($user['group_id']) {
            case '1':   header("Location: index.php?page=home"); break;
            case '2':   header("Location: admin.php?page=home");break;
            case '3':   header("Location: admin.php?page=home");break;
            default:    header("Location: index.php?page=home"); break;
        }
        exit;
    }
}

// A questo punto $login_error contiene l’eventuale messaggio
// e $_SESSION['loggedin'] è settato solo dopo login corretto

// Carica il template di login e passagli eventuali dati
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

$body = new Template("dtml/hator/login");
// Se c'è un errore di login, lo passo al template
$body->setContent("login_error", $login_error);
// Se c'è un'email vecchia da ripopolare, la passo al template
if (isset($old_email)) {
        $body->setContent("email", $old_email);
    }

$main->setContent("body", $body->get());
$main->close();

    

?>