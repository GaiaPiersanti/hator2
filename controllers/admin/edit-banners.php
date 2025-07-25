<?php
// controllers/admin/edit-banners.php

// 1) Init (connessione $conn, classi Template, ecc.)
require_once __DIR__ . '/../../include/init.inc.php';

// 2) Preleva tutti i banner per il dropdown
$banners = [];
$stmt = $conn->prepare("SELECT id, nome FROM banners ORDER BY nome");
$stmt->execute();
$stmt->bind_result($bid, $bname);
while ($stmt->fetch()) {
    $banners[] = ['id' => $bid, 'name' => $bname];
}
$stmt->close();

// 3) Preleva tutte le marche per i radio button
$brands = [];
$stmt = $conn->prepare("SELECT id, name, slug FROM brands ORDER BY name");
$stmt->execute();
$stmt->bind_result($br_id, $br_name, $br_slug);
while ($stmt->fetch()) {
    $brands[] = ['id' => $br_id, 'name' => $br_name, 'slug' => $br_slug];
}
$stmt->close();

// 4) Determina banner_id corrente
$bannerId = intval($_REQUEST['banner_id'] ?? 0);

// 5) Gestione POST (upload / delete / save_brand)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']    ?? '';
    $bannerId = intval($_POST['banner_id'] ?? 0);

    if ($bannerId > 0) {
        // Cartella di upload
        $targetDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/hator2/assets/images/banner_shop/';
        @mkdir($targetDir, 0755, true);

        if ($action === 'upload') {
            // Upload file (click o drag&drop)
            if (!empty($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $ext  = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
                $name = "banner{$bannerId}.{$ext}";
                $dest = $targetDir . $name;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $url = "assets/images/banner_shop/{$name}";
                    $u = $conn->prepare(
                        "UPDATE banners SET img_url = ? WHERE id = ?"
                    );
                    $u->bind_param("si", $url, $bannerId);
                    $u->execute();
                    $u->close();
                }
            }
        }
        elseif ($action === 'delete') {
            // Cancella solo l’immagine
            $d = $conn->prepare("UPDATE banners SET img_url = NULL WHERE id = ?");
            $d->bind_param("i", $bannerId);
            $d->execute();
            $d->close();
        }
        elseif ($action === 'save_brand') {
            // Salva solo lo slug selezionato come link
            $brandId = intval($_POST['brand_id'] ?? 0);
            if ($brandId > 0) {
                $s = $conn->prepare("SELECT slug FROM brands WHERE id = ?");
                $s->bind_param("i", $brandId);
                $s->execute();
                $s->bind_result($slug);
                $s->fetch();
                $s->close();

                $u = $conn->prepare("UPDATE banners SET link = ? WHERE id = ?");
                $u->bind_param("si", $slug, $bannerId);
                $u->execute();
                $u->close();
            }
        }
    }

    // Redirect in GET
    header("Location: admin.php?page=edit-banners&banner_id={$bannerId}");
    exit;
}

// 6) GET: recupera i dati del banner selezionato
$banner = ['img_url' => null, 'link' => ''];
if ($bannerId > 0) {
    $r = $conn->prepare("SELECT img_url, link FROM banners WHERE id = ?");
    $r->bind_param("i", $bannerId);
    $r->execute();
    $r->bind_result($banner['img_url'], $banner['link']);
    $r->fetch();
    $r->close();
}

// 7) Costruisci HTML per dropdown e radio
$options = "<option value=\"0\">-- Select banner --</option>\n";
foreach ($banners as $b) {
    $sel = $b['id'] === $bannerId ? ' selected' : '';
    $label = htmlspecialchars($b['name'], ENT_QUOTES);
    $options .= "<option value=\"{$b['id']}\"{$sel}>{$label}</option>\n";
}

$radios = "";
foreach ($brands as $br) {
    $checked = strpos($banner['link'], "brand={$br['slug']}") !== false ? ' checked' : '';
    $brLabel = htmlspecialchars($br['name'], ENT_QUOTES);
    $radios .= <<<HTML
<div class="form-check">
  <input class="form-check-input" type="radio" name="brand_id" id="brand{$br['id']}"
         value="{$br['id']}"{$checked}>
  <label class="form-check-label" for="brand{$br['id']}">{$brLabel}</label>
</div>

HTML;
}

// 8) Costruisci HTML dello slot immagine
$url = htmlspecialchars($banner['img_url'] ?? '', ENT_QUOTES);
$slotHtml  = '<div class="mb-4">';
$slotHtml .= '  <div class="img-slot border rounded" id="imgSlot" style="width:200px;height:100px;position:relative;overflow:hidden;">';
if ($url) {
    $slotHtml .= "<img src=\"{$url}\" style=\"width:100%;height:100%;object-fit:contain;position:absolute;top:0;left:0;\">";
} else {
    $slotHtml .= '<div class="empty-slot text-muted" style="display:flex;align-items:center;justify-content:center;height:100%;">Drop here</div>';
}
$slotHtml .= '</div>';

$slotHtml .= <<<HTML
  <form id="imageForm" method="post" enctype="multipart/form-data" class="mt-2">
    <input type="hidden" name="banner_id" value="{$bannerId}">
    <input type="file" name="image_file" id="fileInput" accept="image/*" style="display:none">
    <button type="button" class="btn btn-outline-primary btn-sm me-1"
            onclick="document.getElementById('fileInput').click()">Upload</button>
HTML;
if ($url) {
    $slotHtml .= '<button type="submit" name="action" value="delete" class="btn btn-outline-danger btn-sm">Delete</button>';
}
$slotHtml .= '</form></div>';

// 9) Render
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-banners");
$main->setContent("page_title",     "Manage Banners");
$body->setContent("bannersOptions", $options);
$body->setContent("brandRadios",    $radios);
$body->setContent("slotHtml",       $slotHtml);
$body->setContent("selectedBanner", $bannerId);
$main->setContent("body",           $body->get());
$main->close();
