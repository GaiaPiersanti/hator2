<?php

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-brand");

// inizializza valori di default (così in STEP 0 mostri un campo vuoto)
$body->setContent("name", "");
$body->setContent("nameErrorClass", "");
$body->setContent("nameError", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // raccolgo e pulisco input
    $name = trim($_POST['name'] ?? "");
    
    $errors = [];

    // validazione
    if ($name === "") {
        $errors['name'] = "Please enter a brand name.";
    }

    if (empty($errors)) {
        // slugify solo se non ci sono errori
        $slug = slugify($name);
        // provo l’INSERT
        $stmt = $conn->prepare("INSERT INTO brands (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        if ($stmt->execute()) {
            // successo → torno alla lista
            header("Location: admin.php?page=brands-list");
            exit;
        }
        // se errore di duplicato
        if ($conn->errno === 1062) {
            $errors['name'] = "This brand already exists.";
        } else {
            die("DB error: " . $conn->error);
        }
    }

    // se arriviamo qui, ci sono errori: ripopolo il form
    $body->setContent("name", htmlspecialchars($name, ENT_QUOTES));
    $body->setContent("nameErrorClass", isset($errors['name']) ? "is-invalid" : "");
    $body->setContent("nameError", $errors['name'] ?? "");
}

$main->setContent("body", $body->get());
$main->close();

	// 1.	Inizializziamo $body con campi vuoti.
	// 2.	Se il form arriva in POST, puliamo e validiamo.
	// 3.	Se non ci sono errori, inseriamo in DB e redirigiamo su brands-list.
	// 4.	Se ci sono errori (campo mancante o duplicato), li memorizziamo in $errors e li passiamo alla vista.