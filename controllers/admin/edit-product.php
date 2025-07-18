<?php
// controllers/admin/edit-product.php


$errors = [];
$data   = [];

// 2) Recupera l'ID del prodotto da GET
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: admin.php?page=products-list");
    exit;
}

// 3) Carica le opzioni per i radio button
$tables = ['categories','types','families','brands'];
$lists  = [];
foreach ($tables as $tbl) {
    $lists[$tbl] = [];
    $stmt = $conn->prepare("SELECT id, name FROM `{$tbl}` ORDER BY name");
    if (!$stmt) die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    $stmt->execute();
    $stmt->bind_result($rid, $rname);
    while ($stmt->fetch()) {
        $lists[$tbl][] = ['id'=>$rid,'name'=>$rname];
    }
    $stmt->close();
}

// 4) Se POST, raccogli e valida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'              => trim($_POST['name'] ?? ''),
        'slug'              => slugify(trim($_POST['name'] ?? '')),
        'category_id'       => intval($_POST['category_id'] ?? 0),
        'type_id'           => intval($_POST['type_id'] ?? 0),
        'family_id'         => intval($_POST['family_id'] ?? 0),
        'brand_id'          => intval($_POST['brand_id'] ?? 0),
        'best_seller'       => isset($_POST['best_seller']) ? 1 : 0,
        'new_arrival'       => isset($_POST['new_arrival']) ? 1 : 0,
        'short_description' => trim($_POST['short_description'] ?? ''),
        'long_description'  => trim($_POST['long_description'] ?? ''),
    ];

    if ($data['name'] === '') {
        $errors['name'] = "Please enter a product name.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE products SET
               name = ?, slug = ?, category_id = ?, type_id = ?, family_id = ?, brand_id = ?,
               best_seller = ?, new_arrival = ?,
               short_description = ?, long_description = ?
             WHERE id = ?"
        );
        if (!$stmt) die("MySQL prepare error ({$conn->errno}): {$conn->error}");
        $stmt->bind_param(
            "ssiiiissssi",
            $data['name'],
            $data['slug'],
            $data['category_id'],
            $data['type_id'],
            $data['family_id'],
            $data['brand_id'],
            $data['best_seller'],
            $data['new_arrival'],
            $data['short_description'],
            $data['long_description'],
            $id
        );
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php?page=products-list");
        exit;
    }

// 5) GET: prepopola dai valori esistenti
} else {
    $stmt = $conn->prepare(
        "SELECT name, category_id, type_id, family_id, brand_id,
                best_seller, new_arrival, short_description, long_description
           FROM products
          WHERE id = ?"
    );
    if (!$stmt) die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result(
        $name, $catId, $typeId, $famId, $brandId,
        $bestSeller, $newArrival, $shortDesc, $longDesc
    );
    $stmt->fetch();
    $stmt->close();

    $data = [
        'name'              => $name,
        'category_id'       => $catId,
        'type_id'           => $typeId,
        'family_id'         => $famId,
        'brand_id'          => $brandId,
        'best_seller'       => $bestSeller,
        'new_arrival'       => $newArrival,
        'short_description' => $shortDesc,
        'long_description'  => $longDesc,
    ];
}

// 6) Helper per radio buttons
function buildRadios($name, $opts, $sel) {
    $html = '';
    foreach ($opts as $o) {
        $checked = ($o['id'] == $sel) ? 'checked' : '';
        $html .= "<div class=\"form-check form-check-inline\">";
        $html .=   "<input class=\"form-check-input\" type=\"radio\" "
                . "name=\"{$name}\" id=\"{$name}{$o['id']}\" "
                . "value=\"{$o['id']}\" {$checked}>";
        $html .=   "<label class=\"form-check-label\" for=\"{$name}{$o['id']}\">"
                . htmlspecialchars($o['name'], ENT_QUOTES)
                . "</label>";
        $html .= "</div>\n";
    }
    return $html;
}

// 7) Helper per checkbox
function buildCheckbox($name, $checked) {
    $c = $checked ? 'checked' : '';
    return "<div class=\"form-check\">"
         .   "<input class=\"form-check-input\" type=\"checkbox\" "
         .     "name=\"{$name}\" id=\"{$name}\" {$c}>"
         .   "<label class=\"form-check-label\" for=\"{$name}\">"
         .     ucfirst(str_replace('_',' ',$name))
         .   "</label>"
         . "</div>\n";
}

// 8) Renderizza
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-product");

$main->setContent("page_title", "Edit Product");
$body->setContent("action",      "edit-product&id={$id}");
$body->setContent("form_title",  "Edit Product");

// campi e feedback
$body->setContent("name",              htmlspecialchars($data['name'], ENT_QUOTES));
$body->setContent("nameError",         $errors['name']        ?? "");
$body->setContent("nameClass",         isset($errors['name']) ? "is-invalid" : "");

// radio lists
$body->setContent("categoriesRadios",  buildRadios('category_id', $lists['categories'], $data['category_id']));
$body->setContent("typesRadios",       buildRadios('type_id',      $lists['types'],      $data['type_id']));
$body->setContent("familiesRadios",    buildRadios('family_id',    $lists['families'],   $data['family_id']));
$body->setContent("brandsRadios",      buildRadios('brand_id',     $lists['brands'],     $data['brand_id']));

// checkboxes
$body->setContent("bestSellerCheckbox", buildCheckbox('best_seller', $data['best_seller']));
$body->setContent("newArrivalCheckbox", buildCheckbox('new_arrival', $data['new_arrival']));

// descriptions
$body->setContent("short_description", htmlspecialchars($data['short_description'], ENT_QUOTES));
$body->setContent("long_description",  htmlspecialchars($data['long_description'],  ENT_QUOTES));

// cancel link needs product id? just back to list
$main->setContent("body", $body->get());
$main->close();
