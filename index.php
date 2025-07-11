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




//routing
$page = $_GET['page'] ?? 'home';
$publicPages    = ['home','login','shop','about','contact','productdetails','404', 'logout', 'add-user'];
$protectedPages = ['cart','checkout','orders'];

// Se provo ad andare in area protetta senza login, vado al login
if (in_array($page, $protectedPages, true) && empty($_SESSION['loggedin'])) {
    header('Location: index.php?page=login');
    exit;
}

//se provo ad andare in una pagina che non è nè pubblica nè protetta, vado alla 404
// (es. se provo a scrivere index.php?page=qualcosa)
if(!in_array($page, $publicPages, true) && !in_array($page, $protectedPages, true)) {
    header('Location: index.php?page=404');
    exit;
}

// Poi includi il controller vero
switch ($page) {
    case 'home':
    require 'home.php';
    break;

    case 'about':
    require 'about.php';
    break;

    case 'contact':
    require 'contact.php';
    break;

    case 'login':
    require 'login.php';
    break;

    case 'add-user':
    require "add-user.php";
    break;
        
    case 'logout':
    require "logout.php";
    break;
  
    case 'shop':
    require 'shop.php';
    break;

    case 'productdetails':
    require 'productdetails.php';
    break;

    case 'cart':
    require 'cart.php';
    break;

    case 'checkout':
    require 'checkout.php';
    break;

    case '404':
        require '404.php';
        break;

  default:
        require "home.php"; 
        break;
}

?>