<?php

// visita http://localhost/hator2/admin.php?page=prova per l'amministratore di prova

define('IN_ADMIN', true); //mi serve per init.inc.php 
require __DIR__ . '/include/init.inc.php';

// (opzionale) guard per admin
// if (empty($_SESSION['user']['is_admin'])) { ...redirect... }

// determina lo slug – init.inc.php già l’ha fatto se hai seguito lo step1
// $page = $_GET['page'] ?? 'products-list';

// pagina “prova” è consentita
$allowed = ['prova'];
if (!in_array($page, $allowed, true)) {
  $page = 'prova';
}

// 1) apri il layout admin
$main = new Template("dtml/admin/frame");

// 2) includi il controller di quella pagina, che POPOLERÀ $body
switch ($page) {
  case 'prova':
    require __DIR__ . "/controllers/admin/prova.php";
    break;
}

// 3) inietta il body e fai il rendering
$main->setContent("body", $body->get());
$main->close();