<?php

// 1) Istanzio il frame principale
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
// 2) Istanzio il sotto‐template per la pagina account
$body = new Template("dtml/hator/account");

// Fetch logged-in user details
$userId = $_SESSION['user']['user_id'] ?? null;
if ($userId) {
    $stmtUser = $conn->prepare("SELECT email, first_name, last_name, password FROM users WHERE ID = ?");
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $currentUser = $resUser->fetch_assoc() ?: [];
    $stmtUser->close();
} else {
    // not logged in, redirect to login
    header("Location: index.php?page=login");
    exit;
}





//reset password
/* controllo se il form è stato inviato */

    if (!isset($_POST['step'])) {
        $_POST['step'] = 0; /* step iniziale */
    }

    $success = false;

    switch ($_POST['step']) {
        case 0: /* STEP 0 - form */
            $body = new Template("dtml/hator/account");
            // Pre-fill form with existing user data
            $body->setContent('first_name', htmlspecialchars($currentUser['first_name']));
            $body->setContent('last_name',  htmlspecialchars($currentUser['last_name']));
            $body->setContent('email',      htmlspecialchars($currentUser['email']));
            $body->setContent('successMessage', '');
            break;
        case 1:
            
            $body = new Template("dtml/hator/account");
            // initialize error collection before checks
            $errors = [];
            // collect form data
            $password0  = $_POST['password0'] ?? '';
            $password   = $_POST['password']  ?? '';
            $password2  = $_POST['password2'] ?? '';

            // verify current password using custom cifratura
            $storedHash   = $currentUser['password'];
            $currentInput = cifratura($password0, $currentUser['email']);
            if ($currentInput !== $storedHash) {
                $errors['password0'] = "Current password is incorrect.";
            }

            //controlli sui campi del form
            if (strlen($password) < 8) {
                $errors['password'] = "Password must be at least 8 characters long.";
            }
            if ($password !== $password2) {
                $errors['password2'] = "The two passwords do not match.";
            }

            //se ci sono errori, ripopolo il form e mostro gli errori e salto l’UPDATE
            if (!empty($errors)) {
                $body = new Template("dtml/hator/account");
                // ripopolo campi e messaggi
                foreach (['email','first_name','last_name'] as $field) {
                $body->setContent($field, htmlspecialchars($currentUser[$field]));
                }
                $body->setContent('password0Error', $errors['password0'] ?? '');
                $body->setContent('passwordError',  $errors['password']   ?? '');
                $body->setContent('password2Error', $errors['password2']  ?? '');
                $body->setContent('successMessage', '');
                // qui NON fai il query UPDATE
            } else {
                // Encrypt new password using custom cifratura
                $newHash = cifratura($password, $currentUser['email']);
                $stmtUpd = $conn->prepare("UPDATE users SET password = ? WHERE ID = ?");
                $stmtUpd->bind_param("si", $newHash, $userId);
                $stmtUpd->execute();
                $stmtUpd->close();
                // Set success message and redisplay form
                $body = new Template("dtml/hator/account");
                // Pre-fill form with existing user data (names and email unchanged)
                $body->setContent('first_name', htmlspecialchars($currentUser['first_name']));
                $body->setContent('last_name',  htmlspecialchars($currentUser['last_name']));
                $body->setContent('email',      htmlspecialchars($currentUser['email']));
                // Success message placeholder
                $body->setContent('successMessage', 'Your password has been successfully updated.');
                $success = true;
            }
            break;
        default:
            // se lo step non è riconosciuto, torno al form iniziale
            $body = new Template("dtml/hator/account");
            $body->setContent("error", "Invalid step. Please try again.");
            break;


        }
// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();

// If returning to form with errors, activate the Reset Password tab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var triggerEl = document.querySelector(\'a[href="#reset-password"]\');
            if (triggerEl) {
                var tab = bootstrap.Tab.getInstance(triggerEl) || new bootstrap.Tab(triggerEl);
                tab.show();
            }
        });
    </script>';
}
// If password reset succeeded, also activate the Reset Password tab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var triggerEl = document.querySelector(\'a[href="#reset-password"]\');
            if (triggerEl) {
                var tab = bootstrap.Tab.getInstance(triggerEl) || new bootstrap.Tab(triggerEl);
                tab.show();
            }
        });
    </script>';
}?>