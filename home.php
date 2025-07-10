<?php
session_start();
require_once "include/template2.inc.php";

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");

// 2) Istanzio il sotto‐template per la home
$body = new Template("dtml/hator/home");

// 3) eventuali setContent, 
//    es. se vuoi mostrare username quando loggato:
//    if (isset($_SESSION['first_name'])) {
//      $body->setContent("first_name", $_SESSION['first_name']);
//    }

// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();