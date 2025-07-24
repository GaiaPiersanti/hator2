<?php


// 1) invia un'email con una password casuale
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
        $forgot_error = "Email not found.";
    } else {
        // Email trovata, procedo con la logica per il reset della password
        $new_password = bin2hex(random_bytes(8)); // Generate a new random password
        $hash  = cifratura($new_password, $email);
        $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
        if ($conn->error) {
            die("DB error: " . $conn->error);
        }  
        // Invia la nuova password via email
        $recipient = $email;
        $subject = "New Password for your hator account";
        $email_content = "Hello, as requested we have generated a new password for your Hator account.\n" .
                         "Please do not reply to this email as it is auto-generated.\n\n" .
                         "Your new password is: " . $new_password;
        
        // $email_headers = "MIME-Version: 1.0\r\n"; tenuto per sicurezza anche se non dovrebbe servire almeno che non vogliamo mandare HTML
        //$email_headers .= "Content-type: text/html; charset=utf-8\r\n";
        $email_headers = "From: Hator <mtauro569@gmail.com>";

        if (mail($recipient, $subject, $email_content, $email_headers, '-fmtauro569@gmail.com')) {
            // Imposta un codice di risposta 200 (OK)
            http_response_code(200);
            $_SESSION['ForgottenP'] = "An email with your new password has been sent.";
        } else {
            // Imposta un codice di risposta 500 (errore interno del server)
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send the email.";
            exit;
        }

        // torno alla pagina di login mettendo il seguente messagio nell errore di login
        header("Location: index.php?page=login");
        exit;

    }

}

// 2.1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);

// 2.2) Istanzio il sotto‐template per la home
$body = new Template("dtml/hator/forgot-password");

// Pass error message to the forgot-password template
$body->setContent("forgotError", $forgot_error);

// 2.3) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();