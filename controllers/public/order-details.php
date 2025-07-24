<?php

// // Ensure user is logged in
 $userId = $_SESSION['user']['user_id'] ?? null;
if (!$userId || !isset($_GET['id'])) {
    header("Location: index.php?page=account#orders");
    exit;
}
$orderId = (int) $_GET['id'];

// 1) Frame setup
$main = new Template("dtml/hator/frame");
$main->setContent("page_title", $page_title);
// 2) Instantiate order-details template
$body = new Template("dtml/hator/order-details");

// 3) Fetch shipment record
$stmt = $conn->prepare("SELECT package_id, date_request, processed, total FROM shipments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$shipment = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$shipment) {
    header("Location: index.php?page=account#orders");
    exit;
}
$body->setContent("order_id", $orderId);
$body->setContent("order_date", date('M d, Y', strtotime($shipment['date_request'])));
$body->setContent("order_status", $shipment['processed'] ? 'Completed' : 'Processing');
$body->setContent("order_total", number_format($shipment['total'], 2));

// 4) Fetch package items (join packages → product_variants → products)
$stmt2 = $conn->prepare(
    "SELECT pr.name AS product_name,
            pv.size_ml AS variant_size,
            pv.price AS variant_price,
            pkg.quantity
     FROM packages pkg
     JOIN product_variants pv ON pkg.product_id = pv.ID
     JOIN products pr ON pv.product_id = pr.ID
     WHERE pkg.id = ?"
);
$stmt2->bind_param("i", $shipment['package_id']);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// 5) Generate HTML for items as table rows
$itemsHtml = '';
foreach ($items as $item) {
    $itemsHtml .= '<tr>'
        . '<td>' . htmlspecialchars($item['product_name']) . '</td>'
        . '<td>' . htmlspecialchars($item['variant_size']) . ' ML</td>'
        . '<td>' . intval($item['quantity']) . '</td>'
        . '<td>€' . number_format($item['variant_price'], 2) . '</td>'
        . '</tr>';
}
$body->setContent("orderItems", $itemsHtml);

// 6) Render
$main->setContent("body", $body->get());
$main->close();