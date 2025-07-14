<?php

// se POST, processa l’inserimento
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = $conn->real_escape_string(trim($_POST['name']));
  if ($name!=='') {
    $conn->query("INSERT INTO brands (name) VALUES ('$name')");
    header("Location: admin.php?page=brands-list");
    exit;
  }
}
// altrimenti mostro il form
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/brand-form");
$body->setContent("form_title", "Create new brand");
$body->setContent("action", "add-brand");
$body->setContent("brand.name", "");
$main->setContent("body", $body->get());
$main->close();