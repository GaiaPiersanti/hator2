<?php


// 1) Istanzio il frame principale
$main = new Template("dtml/admin/frame");
$main->setContent("page_title", $page_title);
$main->setContent('page_css',
  '<link rel="stylesheet" href="dtml/admin/assets/vendor/select2/css/btn.css">'
);
//$main->setContent("welcome_message", $welcome);


// 2) Istanzio il sotto‐template per la home
$body = new Template("dtml/admin/buttons-crud");


// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();