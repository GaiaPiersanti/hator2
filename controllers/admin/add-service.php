<?php


$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-service");
///////////////////////////////////CODICE AGGIUNTO/////////////////////////
$groups = [];
$stmt = $conn->prepare("SELECT id, name FROM `groups` ORDER BY name");
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->execute();
$stmt->bind_result($grpId, $grpName);
while ($stmt->fetch()) {
    $groups[] = ['id'=>$grpId, 'name'=>$grpName];
}
$stmt->close();


// default step
if (!isset($_POST['step'])) {
  $_POST['step'] = 0;
}

switch ($_POST['step']) {
case 0:
  // visualizzo il form vuoto e preparo checkbox
  $checkboxes = '';
  foreach ($groups as $g) {
      $checkboxes .= "<div class=\"form-check\">";
      $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                    . "name=\"groups[]\" value=\"{$g['id']}\" id=\"grp{$g['id']}\">";
      $checkboxes .=   "<label class=\"form-check-label\" for=\"grp{$g['id']}\">"
                    . htmlspecialchars($g['name'], ENT_QUOTES)."</label>";
      $checkboxes .= "</div>\n";
  }
  $body->setContent("groupsCheckboxes", $checkboxes);
  break;

case 1:
  // 1) Raccolgo i dati
  $name = trim($_POST['name']);
  $selected = isset($_POST['groups']) ? array_map('intval', $_POST['groups']) : [];
  $errors = [];

  // validazione base
  if ($name === '') {
      $errors['name'] = "Please enter a service name.";
  } else {
      // controllo unicità (non serve id <> per insert)
      $stmt = $conn->prepare("SELECT 1 FROM services WHERE name = ?");
      $stmt->bind_param("s", $name);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
          $errors['name'] = "This service already exists.";
      }
      $stmt->close();
  }
 // se ci sono errori ripopolo form + checkbox con le selezioni
  if (!empty($errors)) {
   
    $body->setContent("name", htmlspecialchars($name, ENT_QUOTES));
    $body->setContent("nameError", $errors['name']);
    $body->setContent("nameErrorClass", "is-invalid");

    $checkboxes = '';
    foreach ($groups as $g) {
        $chk = in_array($g['id'], $selected) ? 'checked' : '';
        $checkboxes .= "<div class=\"form-check\">";
        $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                      . "name=\"groups[]\" value=\"{$g['id']}\" id=\"grp{$g['id']}\" {$chk}>";
        $checkboxes .=   "<label class=\"form-check-label\" for=\"grp{$g['id']}\">"
                      . htmlspecialchars($g['name'], ENT_QUOTES)."</label>";
        $checkboxes .= "</div>\n";
    }
    $body->setContent("groupsCheckboxes", $checkboxes);

  } else {
    // 5) Nessun errore: inserisco e sync gruppi
    $stmt = $conn->prepare("INSERT INTO services (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();

    // inserisco in group_has_service
    if (!empty($selected)) {
      $stmtIns = $conn->prepare(
        "INSERT INTO groups_has_services (service_id, group_id) VALUES (?, ?)"
      );
      foreach ($selected as $gid) {
        $stmtIns->bind_param("ii", $newId, $gid);
        $stmtIns->execute();
      }
      $stmtIns->close();
    }

    header("Location: admin.php?page=services-list");
    exit;
  }
  break;

default:
  // passo non riconosciuto → come case 0
  break;
}

// Render finale
$main->setContent("page_title", $page_title);
$main->setContent("body", $body->get());
$main->close();