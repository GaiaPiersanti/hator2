<?php

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
$main->setContent("buttons", isset($_SESSION['loggedin']) ? $buttons_not_loged : $buttons_loged);


// 2) Istanzio il sotto‐template per la pagina about
$body = new Template("dtml/hator/404");



// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();