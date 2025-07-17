<?php


//1) lancio una mail con pasword casuale
$forgot_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])){
    // Pulisco l’input
    $email = $conn->real_escape_string($_POST['email']);
    // Verifico se l'email esiste nel database
    $res = $conn-> query("SELECT email FROM users WHERE email='$email'");
    if (!$res) {
        die("DB error: " . $conn->error);
    } elseif ($res->num_rows === 0) {
        // Email non trovata
        $forgot_error = "Email non trovata.";
    } else {
        // Email trovata, procedo con la logica per il reset della password
        $new_password = bin2hex(random_bytes(8)); // Genera una nuova password casuale
        $hash  = cifratura($new_password, $email);
        $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
        if ($conn->error) {
            die("DB error: " . $conn->error);
        }  
        // Invia la nuova password via email
        $recipient = $email;
        $subject = "Nuova password per il tuo account Hator";
        $email_content = "Salve come da richiesta le habbiamo generato una nuova password per il suo account Hator.\n
        non risponda a questa mail che è auto generata.\n\n la sua nuova password è: " . $new_password;
        
        //$email_headers = "MIME-Version: 1.0\r\n"; tenuto per sicurezza anche se non dovrebbe servire almeno che non vogliamo mandare HTML
        //$email_headers .= "Content-type: text/html; charset=utf-8\r\n";
        $email_headers = "From: Hahor <mtauro569@gmail.com>";

        if (mail($recipient, $subject, $email_content, $email_headers)) {
            // Set a 200 (okay) response code.
            http_response_code(200);
            $_SESSION['ForgottenP'] = "è stata inviuata una mail con la tua nuova password.";
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Oops! Qualcosa è andoto storto e non siamo riusciti a mandare l'email.";
            exit;
        }

        //torno alla pagina di loghin mettendo il seguente messagio nell errore di login
        header("Location: index.php?page=login");
        exit;

    }

}

// 2.1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 2.2) Istanzio il sotto‐template per la home
$body = new Template("dtml/hator/forgot-password");


// 2.3) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();