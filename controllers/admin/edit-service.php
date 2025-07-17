<?php



$id = intval($_GET['id'] ?? 0);
$errors = [];

// 2) Se arriva il POST, valido e salvo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

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

    // se nessun errore, aggiorno e redirigo
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE services SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name,  $id);
        $stmt->execute();
        header("Location: admin.php?page=services-list");
        exit;
    }
}

// 3) Se GET (o POST con errori), carico il valore attuale (o quello appena inviato)
if (!isset($name)) {
    $stmt = $conn->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
}

// 4) Renderizzo con il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-service");
$main->setContent("page_title", $page_title);

$body->setContent("form_title", "Edit Service");
$body->setContent("action",      "edit-service&id={$id}");
$body->setContent("name",        htmlspecialchars($name, ENT_QUOTES));
$body->setContent("nameError",      $errors['name']      ?? "");
$body->setContent("nameErrorClass", isset($errors['name']) ? "is-invalid" : "");

$main->setContent("body", $body->get());
$main->close();