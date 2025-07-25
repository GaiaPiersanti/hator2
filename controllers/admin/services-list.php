<?php

// 1) Prendi i dati dal DB
$res = $conn->query("SELECT id, name FROM services ORDER BY name");
$services = $res->fetch_all(MYSQLI_ASSOC);

// 2) Costruisci in PHP le righe di tabella
$rowsHtml = '';
foreach ($services as $b) {
    $id        = $b['id'];
    $nameEsc   = htmlspecialchars($b['name'], ENT_QUOTES);
    $editUrl   = "admin.php?page=edit-service&id=$id";
    $deleteUrl = "admin.php?page=delete-service&id=$id";

    $rowsHtml .= "
    <tr>
      <td>$id</td>
      <td>$nameEsc</td>
      <td>
        <a href=\"$editUrl\" class=\"btn btn-sm btn-warning btn-icon-split\">
          <span class=\"icon text-white-50\">
            <i class=\"fas fa-exclamation-triangle fa-fw\"></i>
          </span>
          <span class=\"text\">Update a service</span>
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
$body = new Template("dtml/admin/services-list");

// invece di setContent("familes", $familes),
// passiamo direttamente l’HTML già pronto:
$body->setContent("services_rows", $rowsHtml);
$main->setContent("page_title", $page_title);
$main->setContent("body", $body->get());
$main->close();




