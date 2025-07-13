<?php

// visita http://localhost/hator2/admin.php?page=prova per l'amministratore di prova
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/include/init.inc.php';

define('IN_ADMIN', true); //mi serve per init.inc.php 

// (opzionale) guard per admin
// if (empty($_SESSION['user']['is_admin'])) { ...redirect... }

// determina lo slug – init.inc.php già l’ha fatto se hai seguito lo step1
// $page = $_GET['page'] ?? 'products-list';

// pagina “prova” è consentita




// 2) includi il controller di quella pagina, che POPOLERÀ $body
switch ($page) {
  case 'home':
    require __DIR__ . "/controllers/admin/home.php";
    break;
  case 'tab-crud':
   require __DIR__ .  "/controllers/admin/tab-crud.php";  
    break;
}

