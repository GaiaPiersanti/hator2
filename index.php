<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  $page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'login':
        require "login.php";
        break;
    case 'dashboard':
        require "dashboard.php";
        break;
    case 'register':
        require "register.php";
        break;
    // add more cases as needed
    default:
        require "home.php"; // create this file for your homepage logic
        break;
}

?>