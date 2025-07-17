<?php



$id = intval($_GET['id'] ?? 0);
$errors = [];


// 1) Preleva tutti i gruppi
$groups = [];
$stmt = $conn->prepare("SELECT id, name FROM `groups` ORDER BY name");
$stmt->execute();
$stmt->bind_result($grpId, $grpName);
while($stmt->fetch()){
    $groups[] = ['id'=>$grpId, 'name'=>$grpName];
}
$stmt->close();

// 2) Preleva i gruppi già assegnati a questo servizio
$assigned = [];
$stmt = $conn->prepare("SELECT group_id FROM groups_has_services WHERE service_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($aid);
while($stmt->fetch()){
    $assigned[] = $aid;
}
$stmt->close();

// 3) Se POST, valido nome e poi sincronizzo i checkbox
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $selected = isset($_POST['groups']) ? array_map('intval', $_POST['groups']) : [];

    // validazione base del nome
    if ($name === '') {
        $errors['name'] = "Please enter a service name.";
    } else {
        // controllo unicità sul nome (escludendo l’attuale $id)

        $stmt = $conn->prepare("SELECT 1 FROM services WHERE name = ? AND id <> ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['name'] = "This service already exists.";
        }
        $stmt->close();
    }

    // se nessun errore sul nome, aggiorno e sync gruppi
    if (empty($errors)) {
        // aggiorna il nome
        $stmt = $conn->prepare("UPDATE services SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();

        // sincronizza associazioni gruppo–servizio
        // 3a) aggiungi i nuovi
        $toAdd = array_diff($selected, $assigned);
        $stmtIns = $conn->prepare("INSERT INTO groups_has_services (service_id,group_id) VALUES (?, ?)");
        foreach ($toAdd as $gid) {
            $stmtIns->bind_param("ii", $id, $gid);
            $stmtIns->execute();
        }
        $stmtIns->close();
        // 3b) rimuovi quelli deselezionati
        $toDel = array_diff($assigned, $selected);
        $stmtDel = $conn->prepare("DELETE FROM groups_has_services WHERE service_id = ? AND group_id = ?");
        foreach ($toDel as $gid) {
            $stmtDel->bind_param("ii", $id, $gid);
            $stmtDel->execute();
        }
        $stmtDel->close();

        header("Location: admin.php?page=services-list");
        exit;
    }
}


// 3) Se GET (o POST con errori), carico il valore attuale (o quello appena inviato)
if (!isset($name)) {
    $stmt = $conn->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
}

// 5) Prepara HTML checkbox per il template
$checkboxes = '';
foreach ($groups as $g) {
    $chk = in_array($g['id'], $assigned) ? 'checked' : '';
    $checkboxes .= "<div class=\"form-check\">";
    $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                  . "name=\"groups[]\" value=\"{$g['id']}\" id=\"grp{$g['id']}\" {$chk}>";
    $checkboxes .=   "<label class=\"form-check-label\" for=\"grp{$g['id']}\">"
                  . htmlspecialchars($g['name'], ENT_QUOTES)."</label>";
    $checkboxes .= "</div>\n";
}



// 4) Renderizzo con il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-service");
$main->setContent("page_title", $page_title);

$body->setContent("form_title", "Edit Service");
$body->setContent("action",      "edit-service&id={$id}");
$body->setContent("name",        htmlspecialchars($name, ENT_QUOTES));
$body->setContent("nameError",      $errors['name']      ?? "");
$body->setContent("nameErrorClass", isset($errors['name']) ? "is-invalid" : "");

// qui passo il placeholder per le checkbox
$body->setContent("groupsCheckboxes", $checkboxes);
$main->setContent("body", $body->get());
$main->close();