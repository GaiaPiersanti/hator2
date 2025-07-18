<?php
// controllers/admin/modify-variants.php



$variantId = intval($_GET['variant_id'] ?? $_POST['variant_id'] ?? 0);
if ($variantId <= 0) {
    header("Location: admin.php?page=edit-variants");
    exit;
}

$errors = [];
$data   = [];

// 2) Carica i dati esistenti della variante (per GET e per POST falliti)
$stmt = $conn->prepare("
    SELECT product_id, size_ml, price, currency, stock
      FROM product_variants
     WHERE id = ?
");
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->bind_param("i", $variantId);
$stmt->execute();
$stmt->bind_result($productId, $size_ml, $price, $currency, $stock);
if (!$stmt->fetch()) {
    // Variante non trovata
    $stmt->close();
    header("Location: admin.php?page=edit-variants");
    exit;
}
$stmt->close();

// 3) Se arriva POST, valida e aggiorna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'size_ml'  => trim($_POST['size_ml']  ?? $size_ml),
        'price'    => trim($_POST['price']    ?? $price),
        'currency' => trim($_POST['currency'] ?? $currency),
        'stock'    => trim($_POST['stock']    ?? $stock),
    ];

    // validazione minima
    if ($data['size_ml'] === '' || !is_numeric($data['size_ml']) || (int)$data['size_ml'] <= 0) {
        $errors['size_ml'] = "Enter a valid size in ml.";
    }
    if ($data['price'] === '' || !is_numeric($data['price']) || (float)$data['price'] < 0) {
        $errors['price'] = "Enter a valid price.";
    }
    if (!in_array($data['currency'], ['EUR','USD','GBP'], true)) {
        $errors['currency'] = "Select a currency.";
    }
    if ($data['stock'] === '' || !ctype_digit($data['stock'])) {
        $errors['stock'] = "Enter a valid stock quantity.";
    }

    // se validi, aggiorna e redirect
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE product_variants
               SET size_ml = ?, price = ?, currency = ?, stock = ?
             WHERE id = ?
        ");
        if (!$stmt) {
            die("MySQL prepare error ({$conn->errno}): {$conn->error}");
        }
        $s = (int)$data['size_ml'];
        $p = (float)$data['price'];
        $c = $data['currency'];
        $st= (int)$data['stock'];
        $stmt->bind_param("idssi", $s, $p, $c, $st, $variantId);
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php?page=edit-variants&product_id={$productId}");
        exit;
    }
} else {
    // in GET, prepopola $data dai valori dal DB
    $data = [
        'size_ml'  => $size_ml,
        'price'    => $price,
        'currency' => $currency,
        'stock'    => $stock,
    ];
}

// 4) Renderizza il form di modifica
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/modify-variants");

$main->setContent("page_title", "Edit Variant");
$body->setContent("form_title",  "Edit Variant");
$body->setContent("action",      "modify-variants&variant_id={$variantId}");

// campi e validazione
$fields = ['size_ml','price','currency','stock'];
foreach ($fields as $f) {
    $body->setContent($f,         htmlspecialchars($data[$f], ENT_QUOTES));
    $body->setContent("{$f}Error", $errors[$f] ?? "");
    $body->setContent("{$f}Class", isset($errors[$f]) ? "is-invalid" : "");
}

// radio currency
$currHtml = '';
foreach (['EUR','USD','GBP'] as $opt) {
    $chk = ($data['currency'] === $opt) ? 'checked' : '';
    $currHtml .= "<div class=\"form-check form-check-inline\">";
    $currHtml .=   "<input class=\"form-check-input\" type=\"radio\" "
               .    "name=\"currency\" id=\"currency{$opt}\" value=\"{$opt}\" {$chk}>";
    $currHtml .=   "<label class=\"form-check-label\" for=\"currency{$opt}\">{$opt}</label>";
    $currHtml .= "</div>\n";
}
$body->setContent("currencyOptions", $currHtml);

$body->setContent("productId", $productId);

$main->setContent("body", $body->get());
$main->close();
