<?php

// Usa l’engine del prof
require "include/template2.inc.php";

// oppure, per usare il nostro engine personalizzato
// require "include/engine_custom/template2.inc.php";

// Carica il template principale (frame)
$main = new Template("dtml/hator/frame");

// Carica il contenuto della pagina iniziale (es. home.html)
$body = new Template("dtml/hator/home");

// Inserisce il body nel frame
$main->setContent("body", $body->get());

// Mostra la pagina finale
$main->close();