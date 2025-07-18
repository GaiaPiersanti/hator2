<?php
// controllers/admin/add-product.php


$errors = [];
$data   = [];

// 2) Carica opzioni per radio buttons
$tables = ['categories','types','families','brands'];
$lists  = [];
foreach ($tables as $tbl) {
    $lists[$tbl] = [];
    $stmt = $conn->prepare("SELECT id, name FROM `{$tbl}` ORDER BY name");
    if (!$stmt) {
        die("MySQL prepare error ({$conn->errno}): {$conn->error}");
    }
    $stmt->execute();
    $stmt->bind_result($rid, $rname);
    while ($stmt->fetch()) {
        $lists[$tbl][] = ['id'=>$rid, 'name'=>$rname];
    }
    $stmt->close();
}

// 3) Gestione step del form
$step = intval($_POST['step'] ?? 0);

if ($step !== 1) {
    // Step 0: valori di default (senza immagini)
    $data = [
      'name'              => '',
      'slug'              => '',
      'category_id'       => 0,
      'type_id'           => 0,
      'family_id'         => 0,
      'brand_id'          => 0,
      'best_seller'       => 0,
      'new_arrival'       => 0,
      'short_description' => '',
      'long_description'  => '',
      'variants'          => [
        ['size_ml'=>50, 'price'=>0, 'currency'=>'EUR', 'stock'=>0],
        ['size_ml'=>100,'price'=>0, 'currency'=>'EUR', 'stock'=>0],
      ],
    ];
} else {
    // Step 1: raccogli dati e genera slug
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
      'variants'          => $_POST['variants'] ?? [],
    ];

    // validazione minima
    if ($data['name'] === '') {
        $errors['name'] = 'Please enter a product name.';
    }

    if (empty($errors)) {
        // 4) Inserisci in products (senza immagini)
        $sql = "INSERT INTO products
          (name, slug, category_id, type_id, family_id, brand_id,
           best_seller, new_arrival,
           short_description, long_description)
         VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("MySQL prepare error ({$conn->errno}): {$conn->error}");
        }
        $stmt->bind_param(
          "ssiiiissss",
          $data['name'],
          $data['slug'],
          $data['category_id'],
          $data['type_id'],
          $data['family_id'],
          $data['brand_id'],
          $data['best_seller'],
          $data['new_arrival'],
          $data['short_description'],
          $data['long_description']
        );
        $stmt->execute();
        $pid = $conn->insert_id;
        $stmt->close();

        // 5) Inserisci varianti
        if (!empty($data['variants'])) {
            $sql2 = "INSERT INTO product_variants
              (product_id, size_ml, price, currency, stock)
             VALUES (?,?,?,?,?)";
            $stmt2 = $conn->prepare($sql2);
            if (!$stmt2) {
                die("MySQL prepare error ({$conn->errno}): {$conn->error}");
            }
            foreach ($data['variants'] as $v) {
                $size     = intval($v['size_ml']);
                $price    = floatval($v['price']);
                $currency = $v['currency'];
                $stock    = intval($v['stock']);
                $stmt2->bind_param("iidsi", $pid, $size, $price, $currency, $stock);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        // 6) Redirect
        header("Location: admin.php?page=products-list");
        exit;
    }
}

// --- Helpers per generare il form dinamico ---

function buildRadios($name, $opts, $sel) {
    $html = '';
    foreach ($opts as $o) {
        $checked = ($o['id']==$sel) ? 'checked' : '';
        $html .= "<div class=\"form-check form-check-inline\">";
        $html .=   "<input class=\"form-check-input\" type=\"radio\" "
               .   "name=\"{$name}\" id=\"{$name}{$o['id']}\" "
               .   "value=\"{$o['id']}\" {$checked}>";
        $html .=   "<label class=\"form-check-label\" for=\"{$name}{$o['id']}\">"
               .   htmlspecialchars($o['name'], ENT_QUOTES)
               . "</label>";
        $html .= "</div>\n";
    }
    return $html;
}

function buildCheckbox($name, $checked) {
    $c = $checked ? 'checked' : '';
    return "<div class=\"form-check\">"
         .   "<input class=\"form-check-input\" type=\"checkbox\" "
         .     "name=\"{$name}\" id=\"{$name}\" {$c}>"
         .   "<label class=\"form-check-label\" for=\"{$name}\">"
         .     ucfirst(str_replace('_',' ',$name))
         . "</label>"
         . "</div>\n";
}

// genera blocchi varianti
$variantsHtml = '';
foreach ($data['variants'] as $i=>$v) {
    $variantsHtml .= "<div class=\"variant-block mb-3\" data-index=\"{$i}\">\n"
                   . "  <h6>Variant #".($i+1)."</h6>\n"
                   . "  <div class=\"row\">\n"
                   . "    <div class=\"col\">\n"
                   . "      <label>Size (ml)</label>\n"
                   . "      <input type=\"number\" name=\"variants[{$i}][size_ml]\" class=\"form-control\" value=\"{$v['size_ml']}\" min=\"1\">\n"
                   . "    </div>\n"
                   . "    <div class=\"col\">\n"
                   . "      <label>Price</label>\n"
                   . "      <input type=\"number\" step=\"0.01\" name=\"variants[{$i}][price]\" class=\"form-control\" value=\"{$v['price']}\">\n"
                   . "    </div>\n"
                   . "    <div class=\"col\">\n"
                   . "      <label>Currency</label><br>\n"
                   .        buildRadios("variants[{$i}][currency]", [
                            ['id'=>'EUR','name'=>'EUR'],
                            ['id'=>'USD','name'=>'USD'],
                            ['id'=>'GBP','name'=>'GBP'],
                        ], $v['currency'])
                   . "    </div>\n"
                   . "    <div class=\"col\">\n"
                   . "      <label>Stock</label>\n"
                   . "      <input type=\"number\" name=\"variants[{$i}][stock]\" class=\"form-control\" value=\"{$v['stock']}\" min=\"0\">\n"
                   . "    </div>\n"
                   . "  </div>\n"
                   . "</div>\n";
}

// 7) Renderizza
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-product");

$main->setContent("page_title", "Add Product");
$body->setContent("name",              htmlspecialchars($data['name'], ENT_QUOTES));
$body->setContent("nameError",         $errors['name'] ?? '');
$body->setContent("nameClass",         isset($errors['name']) ? 'is-invalid' : '');

$body->setContent("categoriesRadios",  buildRadios('category_id',$lists['categories'],$data['category_id']));
$body->setContent("typesRadios",       buildRadios('type_id',     $lists['types'],     $data['type_id']));
$body->setContent("familiesRadios",    buildRadios('family_id',   $lists['families'],  $data['family_id']));
$body->setContent("brandsRadios",      buildRadios('brand_id',    $lists['brands'],    $data['brand_id']));

$body->setContent("bestSellerCheckbox", buildCheckbox('best_seller',$data['best_seller']));
$body->setContent("newArrivalCheckbox", buildCheckbox('new_arrival',$data['new_arrival']));

$body->setContent("short_description", htmlspecialchars($data['short_description'], ENT_QUOTES));
$body->setContent("long_description",  htmlspecialchars($data['long_description'],  ENT_QUOTES));

$body->setContent("variantsBlocks", $variantsHtml);
$main->setContent("body", $body->get());
$main->close();
