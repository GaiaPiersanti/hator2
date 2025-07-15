<?php

// 2) Prendo l’ID da GET (o da POST)
$id = intval($_GET['id'] ?? 0);

// 3) Se è POST, eseguo il DELETE e torno alla lista
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM families WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // **IMPORTANTE**: fermo qui l’esecuzione e ridireziono
    header("Location: admin.php?page=families-list");
    exit;
}

// 4) Altrimenti (GET), carico il nome per mostrarlo nel form di conferma
$stmt = $conn->prepare("SELECT name FROM families WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name);
if (! $stmt->fetch()) {
    // ID non valido → 404
    header("HTTP/1.1 404 Not Found");
    echo "Family not found.";
    exit;
}
$stmt->close();

// 5) Render con il tuo template di conferma
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/delete-family");

$main->setContent("page_title", $page_title);
$body->setContent("family_id",   $id);
$body->setContent("family_name", htmlspecialchars($name, ENT_QUOTES));

$main->setContent("body", $body->get());
$main->close();