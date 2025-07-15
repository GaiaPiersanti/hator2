<?php

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("welcome_message", $welcome);
$main->setContent("buttons", isset($_SESSION['loggedin']) ? $buttons_loged : $buttons_not_loged);
$main->setContent("settings", isset($_SESSION['loggedin']) ? $settings : "");


// 2) Istanzio il sotto‐template per la pagina account
$body = new Template("dtml/hator/account");


// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();