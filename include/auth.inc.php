<?php
// auth.inc.php
if (empty($_SESSION['loggedin'])|| !isset($_SESSION['loggedin'])) {
    header("Location: index.php?page=login");
    exit;
}
// altrimenti lasci passare


?>
