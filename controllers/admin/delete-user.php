<?php
// controllers/admin/delete-user.php



$userId = intval($_REQUEST['id'] ?? 0);

if ($userId <= 0) {
    header("Location: admin.php?page=users-list");
    exit;
}

// Se POST → cancella e redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php?page=users-list");
    exit;
}

// Se GET → mostra conferma
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $email);
$stmt->fetch();
$stmt->close();

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/delete-user");

$main->setContent("page_title", "Delete User");
$body->setContent("userId", $userId);
$body->setContent("first_name", htmlspecialchars($firstName, ENT_QUOTES));
$body->setContent("last_name",  htmlspecialchars($lastName,  ENT_QUOTES));
$body->setContent("email",      htmlspecialchars($email,      ENT_QUOTES));

$main->setContent("body", $body->get());
$main->close();
