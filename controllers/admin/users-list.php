<?php
// controllers/admin/user-list.php



$rows = [];

// 2) Query per prendere gli utenti con il nome del gruppo
$sql = "
  SELECT 
    u.id,
    g.name AS group_name,
    u.first_name,
    u.last_name,
    u.email
  FROM users u
  LEFT JOIN `groups` g ON u.group_id = g.id
  ORDER BY u.id
";
$stmt = $conn->prepare($sql);
if (! $stmt) {
    die("MySQL prepare error ({$conn->errno}): {$conn->error}");
}
$stmt->execute();
$stmt->bind_result($id, $groupName, $firstName, $lastName, $email);
while ($stmt->fetch()) {
    $rows[] = [
      'id'         => $id,
      'group_name' => $groupName,
      'first_name' => $firstName,
      'last_name'  => $lastName,
      'email'      => $email,
    ];
}
$stmt->close();

// 3) Costruisci HTML delle righe
$userRows = '';
foreach ($rows as $u) {
    $userRows .= "<tr>\n";
    $userRows .= "  <td>" . htmlspecialchars($u['id'], ENT_QUOTES) . "</td>\n";
    $userRows .= "  <td>" . htmlspecialchars($u['group_name'], ENT_QUOTES) . "</td>\n";
    $userRows .= "  <td>" . htmlspecialchars($u['first_name'], ENT_QUOTES) . "</td>\n";
    $userRows .= "  <td>" . htmlspecialchars($u['last_name'], ENT_QUOTES) . "</td>\n";
    $userRows .= "  <td>" . htmlspecialchars($u['email'], ENT_QUOTES) . "</td>\n";
    $userRows .= "  <td class=\"text-end\">\n";
    $userRows .= "    <a href=\"admin.php?page=edit-user&id={$u['id']}\" class=\"btn btn-sm btn-primary me-1\">Edit</a>\n";
    $userRows .= "    <a href=\"admin.php?page=delete-user&id={$u['id']}\" class=\"btn btn-sm btn-danger\">Delete</a>\n";
    $userRows .= "  </td>\n";
    $userRows .= "</tr>\n";
}

// 4) Renderizza con il template
$main = new Template("dtml/admin/frame");
$body = new Template("dtml/admin/users-list");

$main->setContent("page_title", "Users");
$body->setContent("userRows", $userRows);

$main->setContent("body", $body->get());
$main->close();
