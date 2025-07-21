<?php
// controllers/admin/add-variant.php

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-variants");

// 1) Recupera product_id da GET (prima visita) o da POST (submit)
$productId = intval($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
if ($productId <= 0) {
    header("Location: admin.php?page=edit-variants");
    exit;
}

// 2) Carica il nome del prodotto per il titolo
$stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->bind_result($productName);
if (!$stmt->fetch()) {
    die("Product not found");
}
$stmt->close();

// 3) Determina lo “step”: 0 = mostra form, 1 = processa POST
if (!isset($_POST['step'])) {
    $_POST['step'] = 0;
}

switch ($_POST['step']) {
  case 0:
    // step 0: mostra form vuoto
    $body->setContent("productName", htmlspecialchars($productName, ENT_QUOTES));
    $body->setContent("productId",   $productId);
    break;

  case 1:
    // step 1: inserisci i variants
    $variants = $_POST['variants'] ?? [];

    if (!empty($variants)) {
      $sql = "INSERT INTO product_variants
                (product_id, size_ml, price, currency, stock)
              VALUES (?,?,?,?,?)";
      $stmt = $conn->prepare($sql);
      foreach ($variants as $v) {
        $size     = intval($v['size_ml']);
        $price    = floatval($v['price']);
        $currency = $v['currency'];
        $stock    = intval($v['stock']);
        $stmt->bind_param("iidsi",
          $productId,
          $size,
          $price,
          $currency,
          $stock
        );
        $stmt->execute();
      }
      $stmt->close();
    }

    header("Location: admin.php?page=edit-variants&product_id={$productId}");
    exit;
}

// render finale
$main->setContent("page_title", "Add Variant");
$main->setContent("body",        $body->get());
$main->close();
