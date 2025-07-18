<?php
// controllers/admin/add-user.php




// 3) Carica i gruppi per i radio button
$groups = [];
$stmt = $conn->prepare("SELECT id, name FROM `groups` ORDER BY name");
$stmt->execute();
$stmt->bind_result($gid, $gname);
while ($stmt->fetch()) {
    $groups[] = ['id' => $gid, 'name' => $gname];
}
$stmt->close();

$errors = [];
$data = [];

// 4) Se POST, raccogli e valida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? ''),
        'email'      => trim($_POST['email']      ?? ''),
        'password'   => $_POST['password']        ?? '',
        'group_id'   => intval($_POST['group_id'] ?? 0),
    ];

    if ($data['first_name'] === '') {
        $errors['first_name'] = "Enter the first name.";
    }
    if ($data['last_name'] === '') {
        $errors['last_name'] = "Enter the last name.";
    }
    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }
    if ($data['password'] === '') {
        $errors['password'] = "Enter a password.";
    }
    if ($data['group_id'] <= 0) {
        $errors['group_id'] = "Select a group.";
    }

    // 5) Se tutto ok, inserisci in DB
    if (empty($errors)) {
        $hash = cifratura($data['password'], $data['email']);
        $ins = $conn->prepare("
            INSERT INTO users
              (group_id, first_name, last_name, email, password)
            VALUES (?,?,?,?,?)
        ");
        $ins->bind_param(
            "issss",
            $data['group_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hash
        );
        $ins->execute();
        $ins->close();

        header("Location: admin.php?page=users-list");
        exit;
    }

// 6) GET: inizializza form vuoto
} else {
    $data = [
        'first_name' => '',
        'last_name'  => '',
        'email'      => '',
        'password'   => '',
        'group_id'   => 0,
    ];
}

// 7) Costruisci HTML dei radio buttons dei gruppi
$groupRadios = '';
foreach ($groups as $g) {
    $chk = $data['group_id'] === $g['id'] ? 'checked' : '';
    $groupRadios .= "<div class=\"form-check form-check-inline\">";
    $groupRadios .=   "<input class=\"form-check-input\" type=\"radio\" "
                   .   "name=\"group_id\" id=\"group{$g['id']}\" value=\"{$g['id']}\" {$chk}>";
    $groupRadios .=   "<label class=\"form-check-label\" for=\"group{$g['id']}\">"
                   .     htmlspecialchars($g['name'], ENT_QUOTES)
                   .   "</label>";
    $groupRadios .= "</div>\n";
}

// 8) Renderizza il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/add-user");

$main->setContent("page_title", "Add User");

// campi e validazione
$body->setContent("form_title",      "Add User");

$body->setContent("first_name",      htmlspecialchars($data['first_name'], ENT_QUOTES));
$body->setContent("firstNameError",  $errors['first_name'] ?? "");
$body->setContent("firstNameClass",  isset($errors['first_name']) ? "is-invalid" : "");

$body->setContent("last_name",       htmlspecialchars($data['last_name'], ENT_QUOTES));
$body->setContent("lastNameError",   $errors['last_name']  ?? "");
$body->setContent("lastNameClass",   isset($errors['last_name'])  ? "is-invalid" : "");

$body->setContent("email",           htmlspecialchars($data['email'], ENT_QUOTES));
$body->setContent("emailError",      $errors['email'] ?? "");
$body->setContent("emailClass",      isset($errors['email']) ? "is-invalid" : "");

// password non viene ripopolata per sicurezza
$body->setContent("password",        "");
$body->setContent("passwordError",   $errors['password'] ?? "");
$body->setContent("passwordClass",   isset($errors['password']) ? "is-invalid" : "");

$body->setContent("groupRadios",     $groupRadios);
$body->setContent("groupError",      $errors['group_id'] ?? "");
$body->setContent("groupClass",      isset($errors['group_id']) ? "is-invalid" : "");

$main->setContent("body", $body->get());
$main->close();
