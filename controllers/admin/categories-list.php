<?php
// 1) Prendi i dati dal DB
$res = $conn->query("SELECT id, name FROM categories ORDER BY name");
$categories = $res->fetch_all(MYSQLI_ASSOC);

// 2) Costruisci in PHP le righe di tabella
$rowsHtml = '';
foreach ($categories as $b) {
    $id        = $b['id'];
    $nameEsc   = htmlspecialchars($b['name'], ENT_QUOTES);
    $editUrl   = "admin.php?page=edit-category&id=$id";
    $deleteUrl = "admin.php?page=delete-category&id=$id";

    $rowsHtml .= "
    <tr>
      <td>$id</td>
      <td>$nameEsc</td>
      <td>
        <a href=\"$editUrl\" class=\"btn btn-sm btn-warning btn-icon-split\">
          <span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span>
          <span class=\"text\">Update a category</span>
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
$body = new Template("dtml/admin/categories-list");

// invece di setContent("categories", $categories),
// passiamo direttamente l’HTML già pronto:
$body->setContent("categories_rows", $rowsHtml);

$main->setContent("body", $body->get());
$main->close();




