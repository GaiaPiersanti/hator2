<?php


if (!isset($_SESSION['loggedin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
        $query = "SELECT email, first_name, last_name, title FROM users WHERE email = '" . $_POST['email'] . "' AND password = '" . cifratura($_POST['password'], $_POST['email']) . "'";  

        $result = $conn->query($query);

        if (!$result) {
            die("Error: " . $conn->error . " ({$conn->errno}) ");
        } else {
            if ($result->num_rows == 0) {
                // Instead of die, set an error variable
                $login_error = "Email or password incorrect.";
            } else {
                $user = $result->fetch_assoc();
                $_SESSION['loggedin'] = true;
                $_SESSION['user'] = $user;
                $_SESSION['services'] = ['login.php', 'add-user.php', 'logout.php'];
            }
        }
    }
} else {
    if (!in_array(basename($_SERVER['SCRIPT_NAME']), $_SESSION['services'])) {
        die("Access denied: You do not have permission to access this page.");
    }
}

?>
