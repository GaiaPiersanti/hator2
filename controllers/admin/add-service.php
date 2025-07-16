<?php


$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-service");

// STEP di default
if (!isset($_POST['step'])) {
    $_POST['step'] = 0;
}

switch ($_POST['step']) {

  case 0:
    // solo visualizzo il form vuoto
    break;

  case 1:
    // 1) Raccolgo il dato
    $name = trim($_POST['name']);
    $errors = [];

     // validazione base
     if ($name === '') {
      $errors['name'] = "Please enter a service name.";
  } else {
      // controllo unicità sul nome (escludendo l’attuale $id)
      
      $stmt = $conn->prepare("SELECT 1 FROM services WHERE name = ? AND id <> ?");
      $stmt->bind_param("si", $name, $id);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
          $errors['name'] = "This service already exists.";
      }
      $stmt->close();
  }
    // 4) Se ci sono errori, ripopolo form + messaggi
    if (!empty($errors)) {
      $body->setContent("name", htmlspecialchars($name, ENT_QUOTES));
      $body->setContent("nameError", $errors['name']);
      $body->setContent("nameErrorClass", "is-invalid");
      // il form rimane visibile, non faccio INSERT né redirect
    } else {
      // 5) Nessun errore: inserisco e redirect
      $stmt = $conn->prepare("INSERT INTO services (name) VALUES (?)");
      $stmt->bind_param("s", $name);
      $stmt->execute();
      header("Location: admin.php?page=services-list");
      exit;
    }
    break;

  default:
    // passo non riconosciuto, torno al form
    break;
}

// Render finale
$main->setContent("page_title", $page_title);
$main->setContent("body", $body->get());
$main->close();