<?php

// visita http://localhost/hator2/admin.php?page=prova per l'amministratore di prova
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$websiteName = 'Admin';
require __DIR__ . '/include/init.inc.php';

define('IN_ADMIN', true); //mi serve per init.inc.php 

// (opzionale) guard per admin
// if (empty($_SESSION['user']['is_admin'])) { ...redirect... }

// determina lo slug – init.inc.php già l’ha fatto se hai seguito lo step1
// $page = $_GET['page'] ?? 'products-list';

// pagina “prova” è consentita




// 2) includi il controller di quella pagina, che POPOLERÀ $body
switch ($page) {
  case 'home':
    require __DIR__ . "/controllers/admin/home.php";
    break;
  case 'tab-crud':
   require __DIR__ .  "/controllers/admin/tab-crud.php";  
    break;
  case 'buttons':
    require __DIR__ .  "/controllers/admin/buttons.php";  
    break;
  case 'buttons-crud':
    require __DIR__ .  "/controllers/admin/buttons-crud.php";  
    break;
    //brands crud
  case 'brands-list':
    require __DIR__ . '/controllers/admin/brands-list.php';
    break;
  case 'add-brand':
    require __DIR__ . '/controllers/admin/add-brand.php';
    break;
  case 'edit-brand':
    require __DIR__ . '/controllers/admin/edit-brand.php';
    break;
  case 'delete-brand':
    require __DIR__ . '/controllers/admin/delete-brand.php';
    break;
    //types crud
  case 'types-list':
    require __DIR__ . '/controllers/admin/types-list.php';
    break;
  case 'add-type':
    require __DIR__ . '/controllers/admin/add-type.php';
    break;
  case 'edit-type':
    require __DIR__ . '/controllers/admin/edit-type.php';
    break;
  case 'delete-type':
    require __DIR__ . '/controllers/admin/delete-type.php';
    break;
  //categories crud
  case 'categories-list':
    require __DIR__ . '/controllers/admin/categories-list.php';
    break;
  case 'add-category':
    require __DIR__ . '/controllers/admin/add-category.php';
    break;
  case 'edit-category':
    require __DIR__ . '/controllers/admin/edit-category.php';
    break;
  case 'delete-category':
    require __DIR__ . '/controllers/admin/delete-category.php';
    break;
  //families crud
  case 'families-list':
    require __DIR__ . '/controllers/admin/families-list.php';
    break;
  case 'add-family':
    require __DIR__ . '/controllers/admin/add-family.php';
    break;
  case 'edit-family':
    require __DIR__ . '/controllers/admin/edit-family.php';
    break;
  case 'delete-family':
    require __DIR__ . '/controllers/admin/delete-family.php';
    break;
    //services crud
    case 'services-list':
      require __DIR__ . '/controllers/admin/services-list.php';
      break;
      case 'add-service':
        require __DIR__ . '/controllers/admin/add-service.php';
        break;
      case 'edit-service':
        require __DIR__ . '/controllers/admin/edit-service.php';
        break;
      case 'delete-service':
        require __DIR__ . '/controllers/admin/delete-service.php';
        break;
        //products crud
       case 'products-list':
         require __DIR__ . '/controllers/admin/products-list.php';
          break;
          case 'add-product':
            require __DIR__ . '/controllers/admin/add-product.php';
            break;
          case 'edit-product':
            require __DIR__ . '/controllers/admin/edit-product.php';
            break;
          case 'delete-product':
            require __DIR__ . '/controllers/admin/delete-product.php';
            break;
            //variants crud
       case 'edit-variants'://equivale a list-variants
        require __DIR__ . '/controllers/admin/edit-variants.php';
         break;
         case 'add-variants':
           require __DIR__ . '/controllers/admin/add-variants.php';
           break;
         case 'modify-variants':
           require __DIR__ . '/controllers/admin/modify-variants.php';//equivale a edit-variants
           break;
         case 'delete-variants':
           require __DIR__ . '/controllers/admin/delete-variants.php';
           break;
        
    
  case '404':
  require __DIR__ .  "/controllers/admin/404.php";  
    break;
}

