<?php

$id = intval($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = $conn->real_escape_string(trim($_POST['name']));
  $conn->query("UPDATE brands SET name='$name' WHERE id=$id");
  header("Location: admin.php?page=brands-list");
  exit;
}
// GET: prendo i dati correnti
$res = $conn->query("SELECT name FROM brands WHERE id=$id");
$row = $res->fetch_assoc();
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/brand-form");
$body->setContent("form_title", "Edit brand");
$body->setContent("action", "edit-brand");
$body->setContent("brand.name", htmlspecialchars($row['name'],ENT_QUOTES));
$main->setContent("body", $body->get());
$main->close();