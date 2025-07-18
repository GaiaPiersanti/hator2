<?php
// controllers/admin/edit-variants.php



// 2) Recupera product_id da GET e validalo
$productId = intval($_GET['product_id'] ?? 0);
if ($productId <= 0) {
    header("Location: admin.php?page=products-list");
    exit;
}

// 3) Recupera il nome del prodotto (per il titolo)
$stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
if (!$stmt) die("MySQL prepare error ({$conn->errno}): {$conn->error}");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->bind_result($productName);
$stmt->fetch();
$stmt->close();

// 4) Recupera tutte le varianti di quel prodotto
$variants = [];
$sql = "
    SELECT id, size_ml, price, currency, stock
    FROM product_variants
    WHERE product_id = ?
    ORDER BY size_ml
";
$stmt = $conn->prepare($sql);
if (!$stmt) die("MySQL prepare error ({$conn->errno}): {$conn->error}");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->bind_result($vid, $size, $price, $currency, $stock);
while ($stmt->fetch()) {
    $variants[] = [
        'id'       => $vid,
        'size_ml'  => $size,
        'price'    => $price,
        'currency' => $currency,
        'stock'    => $stock,
    ];
}
$stmt->close();

// 5) Genera le righe HTML delle varianti
$rowsHtml = '';
foreach ($variants as $v) {
    $rowsHtml .= "<tr>\n";
    $rowsHtml .= "  <td>" . htmlspecialchars($v['size_ml'], ENT_QUOTES) . " ml</td>\n";
    $rowsHtml .= "  <td>" . htmlspecialchars(number_format($v['price'], 2), ENT_QUOTES) . "</td>\n";
    $rowsHtml .= "  <td>" . htmlspecialchars($v['currency'], ENT_QUOTES) . "</td>\n";
    $rowsHtml .= "  <td>" . htmlspecialchars($v['stock'], ENT_QUOTES) . "</td>\n";
    $rowsHtml .= "  <td class=\"text-end\">\n";
    $rowsHtml .= "    <a href=\"admin.php?page=modify-variants&variant_id={$v['id']}\" class=\"btn btn-sm btn-primary me-1\">Edit</a>\n";
    $rowsHtml .= "    <a href=\"admin.php?page=delete-variants&variant_id={$v['id']}\" class=\"btn btn-sm btn-danger\">Delete</a>\n";
    $rowsHtml .= "  </td>\n";
    $rowsHtml .= "</tr>\n";
}

// 6) Renderizza il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-variants");

$main->setContent("page_title", "Variants for " . htmlspecialchars($productName, ENT_QUOTES));
$body->setContent("productName", htmlspecialchars($productName, ENT_QUOTES));
$body->setContent("productId",    $productId);
$body->setContent("variantsRows", $rowsHtml);

$main->setContent("body", $body->get());
$main->close();
