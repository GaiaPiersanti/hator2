<?php




// 3) Carica i gruppi per i radio button
$groups = [];
$stmt = $conn->prepare("SELECT id, name FROM `groups` ORDER BY name");
$stmt->execute();
$stmt->bind_result($gid, $gname);
while ($stmt->fetch()) {
    $groups[] = ['id' => $gid, 'name' => $gname];
}
$stmt->close();

// 4) Determina user ID
$userId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($userId <= 0) {
    header("Location: admin.php?page=users-list");
    exit;
}

$errors = [];
$data = [];

// 5) POST: raccogli, valida e aggiorna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? ''),
        'email'      => trim($_POST['email']      ?? ''),
        'password'   => $_POST['password']        ?? '',
        'group_id'   => intval($_POST['group_id'] ?? 0),
    ];

    // validazione
    if ($data['first_name'] === '') {
        $errors['first_name'] = "Enter the first name.";
    }
    if ($data['last_name'] === '') {
        $errors['last_name'] = "Enter the last name.";
    }
    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }
    if ($data['group_id'] <= 0) {
        $errors['group_id'] = "Select a group.";
    }

    if (empty($errors)) {
        // costruisci SQL dinamico
        if ($data['password'] !== '') {
            // con nuova password
            $hash = cifratura($data['password'], $data['email']);
            $sql = "
                UPDATE users SET
                  first_name = ?,
                  last_name  = ?,
                  email      = ?,
                  password   = ?,
                  group_id   = ?
                WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssii",
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hash,
                $data['group_id'],
                $userId
            );
        } else {
            // senza toccare la password
            $sql = "
                UPDATE users SET
                  first_name = ?,
                  last_name  = ?,
                  email      = ?,
                  group_id   = ?
                WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssii",
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['group_id'],
                $userId
            );
        }
        $stmt->execute();
        $stmt->close();

        header("Location: admin.php?page=users-list");
        exit;
    }

// 6) GET: carica dati esistenti
} else {
    $stmt = $conn->prepare("
        SELECT first_name, last_name, email, group_id
          FROM users
         WHERE id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result(
        $fn, $ln, $em, $gidSel
    );
    $stmt->fetch();
    $stmt->close();

    $data = [
        'first_name' => $fn,
        'last_name'  => $ln,
        'email'      => $em,
        'password'   => '',
        'group_id'   => $gidSel,
    ];
}

// 7) Costruisci radio button dei gruppi
$groupRadios = '';
foreach ($groups as $g) {
    $chk = $data['group_id'] === $g['id'] ? 'checked' : '';
    $groupRadios .= "<div class=\"form-check form-check-inline\">";
    $groupRadios .=   "<input class=\"form-check-input\" type=\"radio\" "
                   .   "name=\"group_id\" id=\"group{$g['id']}\" "
                   .   "value=\"{$g['id']}\" {$chk}>";
    $groupRadios .=   "<label class=\"form-check-label\" for=\"group{$g['id']}\">"
                   .     htmlspecialchars($g['name'], ENT_QUOTES)
                   .   "</label>";
    $groupRadios .= "</div>\n";
}

// 8) Renderizza form
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/edit-user");

$main->setContent("page_title", "Edit User");
$body->setContent("form_title", "Edit User");
$body->setContent("userId",      $userId);

$body->setContent("first_name",     htmlspecialchars($data['first_name'], ENT_QUOTES));
$body->setContent("firstNameError", $errors['first_name']  ?? "");
$body->setContent("firstNameClass", isset($errors['first_name'])  ? "is-invalid" : "");

$body->setContent("last_name",      htmlspecialchars($data['last_name'], ENT_QUOTES));
$body->setContent("lastNameError",  $errors['last_name']   ?? "");
$body->setContent("lastNameClass",  isset($errors['last_name'])   ? "is-invalid" : "");

$body->setContent("email",          htmlspecialchars($data['email'], ENT_QUOTES));
$body->setContent("emailError",     $errors['email']       ?? "");
$body->setContent("emailClass",     isset($errors['email'])       ? "is-invalid" : "");

// password sempre vuoto
$body->setContent("password",       "");
$body->setContent("passwordError",  $errors['password']    ?? "");
$body->setContent("passwordClass",  isset($errors['password'])    ? "is-invalid" : "");

$body->setContent("groupRadios",    $groupRadios);
$body->setContent("groupError",     $errors['group_id']     ?? "");
$body->setContent("groupClass",     isset($errors['group_id'])     ? "is-invalid" : "");

$main->setContent("body", $body->get());
$main->close();
