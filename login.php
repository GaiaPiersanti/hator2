<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);

    // NOTA: session_start() è già chiamato in index.php
    //session_start();

    require "include/template2.inc.php";
    require "include/dbms.inc.php"; /* include il database */
 //   require "include/auth.inc.php"; /* include il file di autenticazione */


$login_error = "";
// Se arrivo in POST, processa il login
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['email'], $_POST['password'])) {

    // Pulisco l’input e genero l’hash
    $email = $conn->real_escape_string($_POST['email']);
    $hash  = cifratura($_POST['password'], $email);

    // Cerco l’utente
    $result = $conn->query(
      "SELECT email, first_name, last_name 
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


        // popola $_SESSION['services'] per i guard
        $_SESSION['services'] = [
            'index.php',      // se usi front-controller
            'logout.php',
    // …altri controller riservati… in modo che auth.inc.php sappia esattamente quali pagine lasciare vedere a un utente autenticato.
];
        header("Location: index.php?page=home");
        exit;
    }
}

// A questo punto $login_error contiene l’eventuale messaggio
// e $_SESSION['loggedin'] è settato solo dopo login corretto

// Carica il template di login e passagli eventuali dati
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("welcome_message", $welcome);
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