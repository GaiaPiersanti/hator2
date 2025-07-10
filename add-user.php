<?php

   // session_start();

    require "include/template2.inc.php";
    require "include/dbms.inc.php"; /* include il database */
   // require "include/auth.inc.php"; 

    $main = new Template("dtml/hator/frame"); /* apre la template principale */
    $main->setContent("welcome_message", $welcome);
    $body = new Template("dtml/hator/add-user");

    /* controllo se il form è stato inviato */

    if (!isset($_POST['step'])) {
        $_POST['step'] = 0; /* step iniziale */
    }

    switch ($_POST['step']) {
        case 0: /* STEP 0 - form */
            $body = new Template("dtml/hator/add-user");
            break;
        case 1:
            echo "Step 1: Validating form data...<br>";
            $body = new Template("dtml/hator/add-user");
            //raccolgo i dati del form
            $email       = trim($_POST['email']);
            $password    = $_POST['password'];
            $password2   = $_POST['password2'];
            $first_name  = trim($_POST['first_name']);
            $last_name   = trim($_POST['last_name']);
            
            //inizializzo un array per gli errori
            $errors = [];

            //controlli sui campi del form
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "insert a valid email address.";
            }
            if (strlen($password) < 8) {
                $errors['password'] = "Password must be at least 8 characters long.";
            }
            if ($password !== $password2) {
                $errors['password2'] = "Passwords do not match.";
            }
            if ($first_name==='') {
                $errors['first_name'] = "First name is required.";
            }
            if ($last_name==='') {
                $errors['last_name'] = "Last name is required.";
            }

            //se ci sono errori, ripopolo il form e mostro gli errori e salto l’INSERT
            if (!empty($errors)) {
                $body = new Template("dtml/hator/add-user");
                // ripopolo campi e messaggi
                foreach (['email','first_name','last_name'] as $field) {
                $body->setContent($field, htmlspecialchars($$field));
                }
                // ogni errore lo setti nel template, es:
                foreach ($errors as $field=>$msg) {
                $body->setContent($field . "Error", $msg);
                }
                // qui NON fai il query INSERT
            } else {
                // 2.5 – Nessun errore di validazione: provo l’INSERT
                $hash = cifratura($password, $email);
                $sql = "INSERT INTO users (email,password,first_name,last_name)
                        VALUES ('$email','$hash', '$first_name','$last_name')";
                if ($conn->query($sql)) {
                // registrazione ok → redirect al login o home
                header("Location: index.php?page=login");
                exit;
                }
                // 2.6 – Se MySQL risponde 1062, email già esistente
                if ($conn->errno==1062) {
                $body = new Template("dtml/hator/add-user");
                $body->setContent("emailError","Email already exists. Please choose another one.");
                // ripopolo gli altri campi
                foreach (['first_name','last_name','title'] as $f) {
                    $body->setContent($f, htmlspecialchars($$f));
                }
                } else {
                // errore generico
                die("DB error: ".$conn->error);
                }
            }
            break;
        default:
            // se lo step non è riconosciuto, torno al form iniziale
            $body = new Template("dtml/hator/add-user");
            $body->setContent("error", "Invalid step. Please try again.");
            break;


        }
        

  
   $main->setContent("body", $body->get());
   $main->close();

 

?>