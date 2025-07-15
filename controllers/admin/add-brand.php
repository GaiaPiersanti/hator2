<?php


$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-brand");

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

    // 2) Validazione base
    if ($name === '') {
      $errors['name'] = "Please enter a brand name.";
    } else {
      // 3) Pre‐insert uniqueness check sullo slug
      $slug = slugify($name);
      $stmt = $conn->prepare("SELECT 1 FROM brands WHERE slug=?");
      $stmt->bind_param("s", $slug);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $errors['name'] = "This brand already exists.";
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
      $stmt = $conn->prepare("INSERT INTO brands (name,slug) VALUES (?,?)");
      $stmt->bind_param("ss", $name, $slug);
      $stmt->execute();
      header("Location: admin.php?page=brands-list");
      exit;
    }
    break;

  default:
    // passo non riconosciuto, torno al form
    break;
}

// Render finale
$main->setContent("body", $body->get());
$main->close();