<?php
// Start session if not already
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "include/dbms.inc.php";
require_once "include/template2.inc.php";

$main = new Template("dtml/hator/frame");
$body = new Template("dtml/hator/login");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    // Hash password
    $hash = cifratura($_POST['password'] ?? '', $email);

    // Check credentials
    $sql = "SELECT first_name, last_name, titolo
            FROM users
            WHERE email='$email' AND password='$hash'
            LIMIT 1";
    $res = $conn->query($sql);

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        // Set session variables
        $_SESSION['loggedin']    = true;
        $_SESSION['first_name']  = $row['first_name'];
        $_SESSION['last_name']   = $row['last_name'];
        $_SESSION['title']       = $row['titolo'];
        // Redirect to home
        header("Location: index.php?page=home");
        exit;
    } else {
        // Invalid credentials: show error and repopulate email
        $body->setContent("errorMessage", "Email o password non validi");
        $body->setContent("email", htmlspecialchars($email));
    }
}

// Render login form
$main->setContent("body", $body->get());
$main->close();