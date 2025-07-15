<?php



$id = intval($_GET['id'] ?? 0);
$errors = [];

// 2) Se arriva il POST, valido e salvo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    // validazione base
    if ($name === '') {
        $errors['name'] = "Please enter a brand name.";
    } else {
        // controllo unicità sullo slug (escludendo l’attuale $id)
        $slug = slugify($name);
        $stmt = $conn->prepare("SELECT 1 FROM brands WHERE slug = ? AND id <> ?");
        $stmt->bind_param("si", $slug, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['name'] = "This brand already exists.";
        }
        $stmt->close();
    }

    // se nessun errore, aggiorno e redirigo
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE brands SET name = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $slug, $id);
        $stmt->execute();
        header("Location: admin.php?page=brands-list");
        exit;
    }
}

// 3) Se GET (o POST con errori), carico il valore attuale (o quello appena inviato)
if (!isset($name)) {
    $stmt = $conn->prepare("SELECT name FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
}

// 4) Renderizzo con il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-brand");

$body->setContent("form_title", "Edit Brand");
$body->setContent("action",      "edit-brand&id={$id}");
$body->setContent("name",        htmlspecialchars($name, ENT_QUOTES));
$body->setContent("nameError",      $errors['name']      ?? "");
$body->setContent("nameErrorClass", isset($errors['name']) ? "is-invalid" : "");

$main->setContent("body", $body->get());
$main->close();