<?php

$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-group");

/////////////////////////////////// PREPARE SERVICES //////////////////////////
$services = [];
$stmt = $conn->prepare("SELECT id, name FROM `services` ORDER BY name");
if (!$stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->execute();
$stmt->bind_result($srvId, $srvName);
while ($stmt->fetch()) {
    $services[] = ['id'=>$srvId, 'name'=>$srvName];
}
$stmt->close();

// default step
if (!isset($_POST['step'])) {
    $_POST['step'] = 0;
}

switch ($_POST['step']) {
case 0:
    // show empty form + checkboxes
    $checkboxes = '';
    foreach ($services as $s) {
        $checkboxes .= "<div class=\"form-check\">";
        $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                      . "name=\"services[]\" value=\"{$s['id']}\" id=\"srv{$s['id']}\">";
        $checkboxes .=   "<label class=\"form-check-label\" for=\"srv{$s['id']}\">"
                      . htmlspecialchars($s['name'], ENT_QUOTES) . "</label>";
        $checkboxes .= "</div>\n";
    }
    $body->setContent("servicesCheckboxes", $checkboxes);
    break;

case 1:
    // 1) Collect data
    $name     = trim($_POST['name']);
    $selected = isset($_POST['services']) ? array_map('intval', $_POST['services']) : [];
    $errors   = [];

    // 2) Validate
    if ($name === '') {
        $errors['name'] = "Please enter a group name.";
    } else {
        $stmt = $conn->prepare("SELECT 1 FROM `groups` WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['name'] = "This group already exists.";
        }
        $stmt->close();
    }

    // 3) Errors → redisplay form
    if (!empty($errors)) {
        $body->setContent("name", htmlspecialchars($name, ENT_QUOTES));
        $body->setContent("nameError", $errors['name']);
        $body->setContent("nameErrorClass", "is-invalid");

        // rebuild checkboxes with checked values
        $checkboxes = '';
        foreach ($services as $s) {
            $checked = in_array($s['id'], $selected) ? ' checked' : '';
            $checkboxes .= "<div class=\"form-check\">";
            $checkboxes .=   "<input class=\"form-check-input\" type=\"checkbox\" "
                          . "name=\"services[]\" value=\"{$s['id']}\" id=\"srv{$s['id']}\"{$checked}>";
            $checkboxes .=   "<label class=\"form-check-label\" for=\"srv{$s['id']}\">"
                          . htmlspecialchars($s['name'], ENT_QUOTES) . "</label>";
            $checkboxes .= "</div>\n";
        }
        $body->setContent("servicesCheckboxes", $checkboxes);

    } else {
        // 4) Insert new group
        $stmt = $conn->prepare("INSERT INTO `groups` (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();

        // 5) Link services to group
        if (!empty($selected)) {
            $stmtIns = $conn->prepare(
              "INSERT INTO groups_has_services (group_id, service_id) VALUES (?, ?)"
            );
            foreach ($selected as $srv) {
                $stmtIns->bind_param("ii", $newId, $srv);
                $stmtIns->execute();
            }
            $stmtIns->close();
        }

        header("Location: admin.php?page=groups-list");
        exit;
    }
    break;

default:
    // unexpected step → fallback to form
    break;
}

// final render
$main->setContent("page_title", $page_title);
$main->setContent("body", $body->get());
$main->close();
