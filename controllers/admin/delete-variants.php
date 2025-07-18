<?php
// controllers/admin/delete-variant.php



$variantId = intval($_GET['variant_id'] ?? 0);
if ($variantId <= 0) {
    // ID non valido → torna alla lista prodotti
    header("Location: admin.php?page=products-list");
    exit;
}

// 2) Recupera product_id e descrizione breve (size) per redirect e conferma
$stmt = $conn->prepare("
    SELECT product_id, size_ml
      FROM product_variants
     WHERE id = ?
");
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->bind_param("i", $variantId);
$stmt->execute();
$stmt->bind_result($productId, $sizeMl);
if (!$stmt->fetch()) {
    // Variante non esiste → torna alla lista varianti generica
    $stmt->close();
    header("Location: admin.php?page=edit-variants");
    exit;
}
$stmt->close();

// 3) Se POST, elimina e redirect a edit-variants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM product_variants WHERE id = ?");
    if (!$stmt) {
        die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    }
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $stmt->close();

    // Torna alla lista varianti per il prodotto
    header("Location: admin.php?page=edit-variants&product_id={$productId}");
    exit;
}

// 4) Renderizza conferma eliminazione
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/delete-variants");

$main->setContent("page_title", "Delete Variant");
$body->setContent("action",      "delete-variant&variant_id={$variantId}");
$body->setContent("sizeMl",      htmlspecialchars($sizeMl, ENT_QUOTES));
$body->setContent("productId",   $productId);

$main->setContent("body", $body->get());
$main->close();
