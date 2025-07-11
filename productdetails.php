<?php
//session_start();
require_once "include/template2.inc.php";

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("welcome_message", $welcome);

// 2) Istanzio il sotto‐template per la pagina productdetails
$body = new Template("dtml/hator/productdetails");


// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();