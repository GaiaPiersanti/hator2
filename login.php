<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);

    
    session_start();

    require "include/template2.inc.php";
    require "include/dbms.inc.php"; /* include il database */
    require "include/auth.inc.php"; /* include il file di autenticazione */

    $main = new Template("dtml/hator/frame"); /* apre la template principale */
    $body = new Template("dtml/hator/login"); /* apre il body (sotto template) */


// Show login error if set
if (isset($login_error)) {
    $body->setContent("login_error", "<p class='error-message'>$login_error</p>");
} else {
    $body->setContent("login_error", "");
}

// Only set these if user is logged in
if (isset($_SESSION['user'])) {
    $body->setContent("first_name", $_SESSION['user']['first_name']);
    $body->setContent("last_name", $_SESSION['user']['last_name']);
} else {
    $body->setContent("first_name", "");
    $body->setContent("last_name", "");
}

$main->setContent("body", $body->get());
$main->close();



    

?>