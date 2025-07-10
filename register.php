<?php
session_start();

    require "include/template2.inc.php";
    require "include/dbms.inc.php"; /* include il database */
    require "include/auth.inc.php"; /* include il file di autenticazione */

    $main = new Template("dtml/hator/frame"); /* apre la template principale */
    $body = new Template("dtml/hator/register"); /* apre il body (sotto template) */

    $main->setContent("body", $body->get());
$main->close();