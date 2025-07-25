<?php
// controllers/admin/products-list.php


$rows = [];

// 2) Query per prendere i prodotti con i nomi di category, type e brand
$sql = "
  SELECT 
    p.id,
    p.name AS product_name,
    c.name AS category_name,
    t.name AS type_name,
    b.name AS brand_name
  FROM products p
  JOIN categories c ON p.category_id = c.id
  JOIN types      t ON p.type_id     = t.id
  JOIN brands     b ON p.brand_id    = b.id
  ORDER BY p.name
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->execute();
$stmt->bind_result($id, $prodName, $catName, $typeName, $brandName);
while ($stmt->fetch()) {
    $rows[] = [
      'id'           => $id,
      'product_name' => $prodName,
      'category'     => $catName,
      'type'         => $typeName,
      'brand'        => $brandName,
    ];
}
$stmt->close();

// 3) Prepara HTML delle righe per il template
$tr = '';
foreach ($rows as $r) {
  $tr .= "<tr>\n";
  $tr .= "  <td>" . htmlspecialchars($r['product_name'], ENT_QUOTES) . "</td>\n";
  $tr .= "  <td>" . htmlspecialchars($r['category'],     ENT_QUOTES) . "</td>\n";
  $tr .= "  <td>" . htmlspecialchars($r['type'],         ENT_QUOTES) . "</td>\n";
  $tr .= "  <td>" . htmlspecialchars($r['brand'],        ENT_QUOTES) . "</td>\n";
  $tr .= "  <td class=\"text-end\">\n";
  $tr .= "    <a href=\"admin.php?page=edit-product&id={$r['id']}\" class=\"btn btn-sm btn-warning btn-icon-split me-1\"><span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span><span class=\"text\">Edit Product</span></a>\n";
  $tr .= "    <a href=\"admin.php?page=edit-variants&product_id={$r['id']}\" class=\"btn btn-sm btn-warning btn-icon-split me-1\"><span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span><span class=\"text\">Edit Variants</span></a>\n";
  $tr .= "    <a href=\"admin.php?page=edit-images&product_id={$r['id']}\" class=\"btn btn-sm btn-warning btn-icon-split me-1\"><span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span><span class=\"text\">Edit Images</span></a>\n";
  $tr .= "    <a href=\"admin.php?page=delete-product&id={$r['id']}\" class=\"btn btn-sm btn-danger btn-icon-split\"><span class=\"icon text-white-50\">
            <i class=\"fas fa-trash fa-fw\"></i>
          </span><span class=\"text\">Delete</span></a>\n";
  $tr .= "</tr>\n";
}

// 4) Renderizza con il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/products-list");

$main->setContent("page_title", "Products");
$body->setContent("productsRows", $tr);

$main->setContent("body", $body->get());
$main->close();
