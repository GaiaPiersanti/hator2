<?php
    
    session_start();
    $_SESSION = array(); // svuota la sessione
    session_destroy(); // distrugge la sessione 
    header("Location: index.php?page=login"); // reindirizza alla pagina di login
    exit(); // esce dallo script    

?>