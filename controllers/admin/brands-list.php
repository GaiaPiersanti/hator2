<?php
// 1) Prendi i dati dal DB
$res = $conn->query("SELECT id, name FROM brands ORDER BY name");
$brands = $res->fetch_all(MYSQLI_ASSOC);

// 2) Costruisci in PHP le righe di tabella
$rowsHtml = '';
foreach ($brands as $b) {
    $id        = $b['id'];
    $nameEsc   = htmlspecialchars($b['name'], ENT_QUOTES);
    $editUrl   = "admin.php?page=edit-brand&id=$id";
    $deleteUrl = "admin.php?page=delete-brand&id=$id";

    $rowsHtml .= "
    <tr>
      <td>$id</td>
      <td>$nameEsc</td>
      <td>
        <a href=\"$editUrl\" class=\"btn btn-sm btn-warning btn-icon-split\">
          <span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span>
          <span class=\"text\">Update a brand</span>
        </a>
        <a href=\"$deleteUrl\" class=\"btn btn-sm btn-danger btn-icon-split\">
          <span class=\"icon text-white-50\">
            <i class=\"fas fa-trash fa-fw\"></i>
          </span>
          <span class=\"text\">Delete</span>
        </a>
      </td>
    </tr>";
}

// 3) Render
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/brands-list");

// invece di setContent("brands", $brands),
// passiamo direttamente l’HTML già pronto:
$body->setContent("brands_rows", $rowsHtml);

$main->setContent("body", $body->get());
$main->close();




