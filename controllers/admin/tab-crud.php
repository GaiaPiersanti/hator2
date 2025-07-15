<?php



// 1) Istanzio il frame principale
$main = new Template("dtml/admin/frame");

// 2) Istanzio il sotto‐template per la home
$body = new Template("dtml/admin/tab-crud");

$main->setContent("page_title", $page_title);
$main->setContent('page_css',
  '<link rel="stylesheet" href="dtml/admin/assets/vendor/select2/css/tab-crud.css">'
);
$main->setContent('page_js',
  '<script src="dtml/admin/assets/vendor/select2/js/tab-crud.js"></script>'
);
//$main->setContent("welcome_message", $welcome);





// 4) Inietto il body nel frame e chiudo
$main->setContent("page_title", $page_title);
$main->setContent("body", $body->get());
$main->close();