<?php
// controllers/admin/delete-product.php



$id = intval($_GET['id'] ?? 0);
// Se ID non valido, torna subito alla lista prodotti
if ($id <= 0) {
    header("Location: admin.php?page=products-list");
    exit;
}

$productName = '';

// 2) Se arriva POST, elimina varianti e prodotto e reindirizza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2a) Elimina varianti collegate
    $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
    if (!$stmt) {
        die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 2b) Elimina il prodotto stesso
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) {
        die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 2c) Redirect alla lista prodotti
    header("Location: admin.php?page=products-list");
    exit;
}

// 3) Se GET, recupera il nome del prodotto per mostrarlo nella conferma
$stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($productName);
$stmt->fetch();
$stmt->close();

// 4) Renderizza la pagina di conferma
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/delete-product");

$main->setContent("page_title", "Delete Product");
$body->setContent("action",      "delete-product&id={$id}");
$body->setContent("productName", htmlspecialchars($productName, ENT_QUOTES));

$main->setContent("body", $body->get());
$main->close();
