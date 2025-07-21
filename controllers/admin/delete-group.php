<?php

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/delete-group");

// 1) Recupera l'ID del gruppo da GET (prima visita) o da POST (submit)
if (isset($_GET['id'])) {
    $groupId = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $groupId = intval($_POST['id']);
} else {
    $groupId = 0;
}
if ($groupId <= 0) {
    die("Invalid group ID");
}

// 2) Determina se siamo in conferma (step 0) o esecuzione delete (step 1)
$step = isset($_POST['step']) ? intval($_POST['step']) : 0;

if ($step === 1) {
    // Esegui cancellazione: prima le associazioni, poi il gruppo

    // 2a) Elimina tutte le righe in groups_has_services
    $stmt = $conn->prepare(
        "DELETE FROM groups_has_services WHERE group_id = ?"
    );
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->close();

    // 2b) Elimina il gruppo
    $stmt = $conn->prepare(
        "DELETE FROM `groups` WHERE id = ?"
    );
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->close();

    // 2c) Redirect alla lista gruppi
    header("Location: admin.php?page=groups-list");
    exit;
} else {
    // step 0: mostra pagina di conferma

    // 3) Carica il nome del gruppo per il messaggio
    $stmt = $conn->prepare(
        "SELECT name FROM `groups` WHERE id = ?"
    );
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->bind_result($groupName);
    if (!$stmt->fetch()) {
        die("Group not found");
    }
    $stmt->close();

    // 4) Passa i dati al template
    $body->setContent("id",   $groupId);
    $body->setContent("name", htmlspecialchars($groupName, ENT_QUOTES));
}

// 5) Render finale
$main->setContent("page_title", $page_title);
$main->setContent("body",       $body->get());
$main->close();
