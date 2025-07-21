<?php

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-group");

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

// 2) Carica tutti i servizi disponibili
$services = [];
$stmt = $conn->prepare("SELECT id, name FROM `services` ORDER BY name");
$stmt->execute();
$stmt->bind_result($srvId, $srvName);
while ($stmt->fetch()) {
    $services[] = ['id'=>$srvId, 'name'=>$srvName];
}
$stmt->close();

// 3) Determina in quale “step” siamo: 0 = caricamento form, 1 = submit
if (!isset($_POST['step'])) {
    $_POST['step'] = 0;
}

switch ($_POST['step']) {
case 0:
    // --- step 0: mostra il form popolato ---
    // 3a) Carica i dati correnti del gruppo
    $stmt = $conn->prepare("SELECT name FROM `groups` WHERE id = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->bind_result($groupName);
    if (!$stmt->fetch()) {
        die("Group not found");
    }
    $stmt->close();

    // 3b) Carica i servizi già associati
    $selected = [];
    $stmt = $conn->prepare("SELECT service_id FROM groups_has_services WHERE group_id = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->bind_result($sid);
    while ($stmt->fetch()) {
        $selected[] = $sid;
    }
    $stmt->close();

    // 3c) Popola template
    $body->setContent("id",   $groupId);
    $body->setContent("name", htmlspecialchars($groupName, ENT_QUOTES));

    // 3d) Costruisci le checkbox
    $checkboxes = '';
    foreach ($services as $s) {
        $chk = in_array($s['id'], $selected) ? ' checked' : '';
        $checkboxes .= "<div class=\"form-check\">";
        $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                      . "name=\"services[]\" value=\"{$s['id']}\" id=\"srv{$s['id']}\"{$chk}>";
        $checkboxes .=   "<label class=\"form-check-label\" for=\"srv{$s['id']}\">"
                      . htmlspecialchars($s['name'], ENT_QUOTES) . "</label>";
        $checkboxes .= "</div>\n";
    }
    $body->setContent("servicesCheckboxes", $checkboxes);
    break;

case 1:
    // --- step 1: gestisci submit ---
    $name     = trim($_POST['name']);
    $selected = isset($_POST['services']) ? array_map('intval', $_POST['services']) : [];
    $errors   = [];

    // 4) Validazione nome
    if ($name === '') {
        $errors['name'] = "Please enter a group name.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM `groups` WHERE name = ? AND id <> ?");
        $stmt->bind_param("si", $name, $groupId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['name'] = "Another group with this name already exists.";
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        // 5) Se ci sono errori, ripopola form con messaggi
        $body->setContent("id",            $groupId);
        $body->setContent("name",          htmlspecialchars($name, ENT_QUOTES));
        $body->setContent("nameError",     $errors['name']);
        $body->setContent("nameErrorClass","is-invalid");

        $checkboxes = '';
        foreach ($services as $s) {
            $chk = in_array($s['id'], $selected) ? ' checked' : '';
            $checkboxes .= "<div class=\"form-check\">";
            $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                          . "name=\"services[]\" value=\"{$s['id']}\" id=\"srv{$s['id']}\"{$chk}>";
            $checkboxes .=   "<label class=\"form-check-label\" for=\"srv{$s['id']}\">"
                          . htmlspecialchars($s['name'], ENT_QUOTES) . "</label>";
            $checkboxes .= "</div>\n";
        }
        $body->setContent("servicesCheckboxes", $checkboxes);

    } else {
        // 6) Aggiorna nome gruppo
        $stmt = $conn->prepare("UPDATE `groups` SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $groupId);
        $stmt->execute();
        $stmt->close();

        // 7) Recupera associazioni esistenti
        $existing = [];
        $stmt = $conn->prepare("SELECT service_id FROM groups_has_services WHERE group_id = ?");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $stmt->bind_result($sid);
        while ($stmt->fetch()) {
            $existing[] = $sid;
        }
        $stmt->close();

        // 8) Calcola aggiunte / rimozioni
        $toAdd    = array_diff($selected, $existing);
        $toRemove = array_diff($existing, $selected);

        // 9) Inserisci nuove associazioni
        if (!empty($toAdd)) {
            $stmtIns = $conn->prepare("INSERT INTO groups_has_services (group_id, service_id) VALUES (?, ?)");
            foreach ($toAdd as $srv) {
                $stmtIns->bind_param("ii", $groupId, $srv);
                $stmtIns->execute();
            }
            $stmtIns->close();
        }
        // 10) Rimuovi associazioni deselezionate
        if (!empty($toRemove)) {
            $stmtDel = $conn->prepare("DELETE FROM groups_has_services WHERE group_id = ? AND service_id = ?");
            foreach ($toRemove as $srv) {
                $stmtDel->bind_param("ii", $groupId, $srv);
                $stmtDel->execute();
            }
            $stmtDel->close();
        }

        // 11) Redirect su lista gruppi
        header("Location: admin.php?page=groups-list");
        exit;
    }
    break;
}

// 12) Render finale
$main->setContent("page_title", $page_title);
$main->setContent("body",       $body->get());
$main->close();
