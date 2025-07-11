<?php
//session_start();
require_once "include/template2.inc.php";

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");


// 2) Istanzio il sotto‐template per la pagina about
$body = new Template("dtml/hator/404");



// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();