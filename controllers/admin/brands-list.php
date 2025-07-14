<?php
// 1) Prendi i dati dal DB
$res = $conn->query("SELECT id, name FROM brands ORDER BY name");
$brands = $res->fetch_all(MYSQLI_ASSOC);

// 2) Costruisci in PHP le righe di tabella
$rowsHtml = '';
foreach ($brands as $b) {
    $rowsHtml .= '<tr>'
               .  "<td>{$b['id']}</td>"
               .  "<td>" . htmlspecialchars($b['name'], ENT_QUOTES) . "</td>"
               .  '<td>'
               .    "<a href=\"admin.php?page=edit-brand&id={$b['id']}\" class=\"btn btn-sm btn-warning\">Edit</a> "
               .    "<a href=\"admin.php?page=delete-brand&id={$b['id']}\" class=\"btn btn-sm btn-danger\">Delete</a>"
               .  '</td>'
               . '</tr>';
}

// 3) Render
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/brands-list");

// invece di setContent("brands", $brands),
// passiamo direttamente l’HTML già pronto:
$body->setContent("brands_rows", $rowsHtml);

$main->setContent("body", $body->get());
$main->close();