<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


// prepara il welcome_message se l'utente è loggato
if (!empty($_SESSION['user']['first_name'])) {
    $welcome = 'Welcome back '
             . htmlspecialchars($_SESSION['user']['first_name'], ENT_QUOTES)
             . ' '
             . htmlspecialchars($_SESSION['user']['last_name'], ENT_QUOTES)
             . '!';
} else {
    $welcome = '';
}

  $page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'login':
        require "login.php";
        break;
    case 'dashboard':
        require "dashboard.php";
        break;
    case 'add-user':
        require "add-user.php";
        break;
    // add more cases as needed
    default:
        require "home.php"; 
        break;
}// index.php


//routing
$page = $_GET['page'] ?? 'home';
$publicPages    = ['home','login','register','shop','productdetails'];
$protectedPages = ['dashboard','cart','checkout','orders'];

// Se provo ad andare in area protetta senza login, vado al login
if (in_array($page, $protectedPages, true) && empty($_SESSION['loggedin'])) {
    header('Location: index.php?page=login');
    exit;
}

// Poi includi il controller vero
switch ($page) {
  case 'login':
    require 'login.php';
    break;
  case 'dashboard':
    require 'dashboard.php';
    break;
  // ecc.
}

?>