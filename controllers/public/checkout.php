<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("welcome_message", $welcome);
$main->setContent("buttons", isset($_SESSION['loggedin']) ? $buttons_not_loged : $buttons_loged);

// 2) Istanzio il sotto‐template per la pagina checkout
$body = new Template("dtml/hator/checkout");



// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();