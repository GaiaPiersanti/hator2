<?php
// controllers/admin/edit-images.php



// 2) Carica tutti i prodotti per il dropdown
$products = [];
$sql = "
  SELECT 
    p.id,
    CONCAT(p.name, ' | ', b.name, ' | ', t.name, ' | ', c.name) AS label
  FROM products p
  JOIN brands     b ON p.brand_id    = b.id
  JOIN types      t ON p.type_id     = t.id
  JOIN categories c ON p.category_id = c.id
  ORDER BY p.name
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($pid, $plabel);
while ($stmt->fetch()) {
    $products[] = ['id'=>$pid,'label'=>$plabel];
}
$stmt->close();

// 3) Determina product_id (da GET o POST)
$productId = intval($_REQUEST['product_id'] ?? 0);

// 4) Gestione POST: upload, delete o swap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slot   = intval($_POST['slot'] ?? 0);

    if ($productId > 0 && $slot >= 1 && $slot <= 4) {
        $col = "img{$slot}_url";

        // percorso assoluto via DOCUMENT_ROOT
        // es: /opt/lampp/htdocs/hator2/assets/images/products/
        $targetDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/hator2/assets/images/products/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if ($action === 'upload'
            && !empty($_FILES['image_file'])
            && $_FILES['image_file']['error'] === UPLOAD_ERR_OK
        ) {
            // genera nome univoco
            $ext   = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $name  = uniqid("prod{$productId}_slot{$slot}_") . ".$ext";
            $dest  = $targetDir . $name;

            if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                error_log("edit-images.php: upload fallito tmp={$_FILES['image_file']['tmp_name']} dest=$dest");
            } else {
                // solo se il file è scritto, aggiorna DB
                $url = "assets/images/products/$name";
                $u   = $conn->prepare("UPDATE products SET {$col} = ? WHERE id = ?");
                $u->bind_param("si", $url, $productId);
                $u->execute();
                $u->close();
            }

        } elseif ($action === 'delete') {
            $d = $conn->prepare("UPDATE products SET {$col} = NULL WHERE id = ?");
            $d->bind_param("i", $productId);
            $d->execute();
            $d->close();

        } elseif ($action === 'swap') {
            $slot2 = intval($_POST['slot2'] ?? 0);
            if ($slot2 >= 1 && $slot2 <= 4 && $slot2 !== $slot) {
                $col2 = "img{$slot2}_url";
                // leggi le due URL
                $r = $conn->prepare("SELECT {$col}, {$col2} FROM products WHERE id = ?");
                $r->bind_param("i", $productId);
                $r->execute();
                $r->bind_result($url1, $url2);
                $r->fetch();
                $r->close();
                // scrivi lo swap
                $s = $conn->prepare("UPDATE products SET {$col} = ?, {$col2} = ? WHERE id = ?");
                $s->bind_param("ssi", $url2, $url1, $productId);
                $s->execute();
                $s->close();
            }
        }
    }

    // ricarica pagina
    header("Location: admin.php?page=edit-images&product_id={$productId}");
    exit;
}

// 5) GET: recupera le immagini correnti
$images = [null,null,null,null];
if ($productId > 0) {
    $r = $conn->prepare("SELECT img1_url, img2_url, img3_url, img4_url FROM products WHERE id = ?");
    $r->bind_param("i", $productId);
    $r->execute();
    $r->bind_result($images[0], $images[1], $images[2], $images[3]);
    $r->fetch();
    $r->close();
}

// 6) Costruisci HTML per dropdown e slot
$options  = "<option value=\"0\">-- Select product --</option>\n";
foreach ($products as $p) {
    $sel = $p['id']==$productId ? ' selected' : '';
    $options .= "<option value=\"{$p['id']}\"{$sel}>{$p['label']}</option>\n";
}

$slotsHtml = "<div class=\"row\">\n";
for ($i=1; $i<=4; $i++) {
    $url = htmlspecialchars($images[$i-1] ?? '', ENT_QUOTES);
    $has = !empty($url);
    $slotsHtml .= "  <div class=\"col-3 text-center mb-4\">\n"
                . "    <div class=\"img-slot border rounded\" data-slot=\"$i\" "
                . "style=\"position:relative;min-height:150px;\">\n";
    if ($has) {
        $slotsHtml .= "      <img src=\"$url\" class=\"img-fluid h-100\" style=\"object-fit:contain\">\n";
    } else {
        $slotsHtml .= "      <div class=\"empty-slot text-muted\" style=\"line-height:150px;\">Drop here</div>\n";
    }
    $slotsHtml .= "    </div>\n"
                . "    <form method=\"post\" enctype=\"multipart/form-data\">\n"
                . "      <input type=\"hidden\" name=\"product_id\" value=\"$productId\">\n"
                . "      <input type=\"hidden\" name=\"slot\" value=\"$i\">\n"
                . "      <input type=\"hidden\" name=\"action\" value=\"upload\">\n"
                . "      <input type=\"file\" name=\"image_file\" id=\"file$i\" accept=\"image/*\" style=\"display:none\" onchange=\"this.form.submit()\">\n"
                . "      <button type=\"button\" class=\"btn btn-sm btn-outline-primary me-1\" onclick=\"document.getElementById('file$i').click()\">Upload</button>\n";
    if ($has) {
        $slotsHtml .= "      <button type=\"submit\" name=\"action\" value=\"delete\" class=\"btn btn-sm btn-outline-danger\">Delete</button>\n";
    }
    $slotsHtml .= "    </form>\n"
                . "  </div>\n";
}
$slotsHtml .= "</div>\n";

// 7) Render
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-images");
$main->setContent("page_title",      "Manage Product Images");
$body->setContent("productsOptions", $options);
$body->setContent("slotsHtml",       $slotsHtml);
$body->setContent("selectedProduct", $productId);
$main->setContent("body",            $body->get());
$main->close();
