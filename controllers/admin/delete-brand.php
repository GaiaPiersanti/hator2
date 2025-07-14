<?php

$id = intval($_GET['id'] ?? 0);
$conn->query("DELETE FROM brands WHERE id=$id");
header("Location: admin.php?page=brands-list");
exit;