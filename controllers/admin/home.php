<?php


// 1) Istanzio il frame principale
// 1) Istanzio il frame principale
$main = new Template("dtml/admin/frame");
$main->setContent("page_title", $page_title);

// Calculate total earnings in the last year
$earningsRes = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS sum_total
    FROM shipments
    WHERE date_request >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
");
$earningsRow      = $earningsRes->fetch_assoc();
$annualEarnings   = (float)$earningsRow['sum_total'];

// Calculate total earnings for the current month
$monthlyRes = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS sum_total
    FROM shipments
    WHERE DATE_FORMAT(date_request, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
");
$monthlyRow      = $monthlyRes->fetch_assoc();
$monthlyEarnings = (float)$monthlyRow['sum_total'];


// 2) Istanzio il sotto‐template per la home
$body = new Template("dtml/admin/home");
$body->setContent("annual_earnings", number_format($annualEarnings, 2, '.', ','));
$body->setContent("monthly_earnings", number_format($monthlyEarnings, 2, '.', ','));

// Calculate number of users with group_id = 1
$userRes = $conn->query("
    SELECT COUNT(*) AS cnt
    FROM users
    WHERE group_id = 1
");
$userRow    = $userRes->fetch_assoc();
$userCount  = (int)$userRow['cnt'];

// Pass user count to template
$body->setContent("user_count", $userCount);

// Calculate percentage of product variants with stock = 0
$totalVarRes = $conn->query("
    SELECT COUNT(*) AS total_variants
    FROM product_variants
");
$totalVarRow      = $totalVarRes->fetch_assoc();
$totalVariants    = (int)$totalVarRow['total_variants'];

$zeroStockRes     = $conn->query("
    SELECT COUNT(*) AS zero_variants
    FROM product_variants
    WHERE stock = 0
");
$zeroStockRow     = $zeroStockRes->fetch_assoc();
$zeroVariants     = (int)$zeroStockRow['zero_variants'];


$outOfStockPct    = $totalVariants > 0
                   ? ($zeroVariants / $totalVariants) * 100
                   : 0;

// Pass out-of-stock percentage formatted to template
$body->setContent("out_of_stock_pct", number_format($outOfStockPct, 2, '.', ','));

// Calculate revenue by country for pie chart
$countryRes = $conn->query("
    SELECT country, COALESCE(SUM(total), 0) AS revenue
    FROM shipments
    GROUP BY country
");
$countries = [];
$revenues  = [];
while ($row = $countryRes->fetch_assoc()) {
    $countries[] = $row['country'];
    $revenues[]  = (float)$row['revenue'];
}
// Pass JSON-encoded arrays to template

$body->setContent('pie_countries', json_encode($countries));
$body->setContent('pie_revenues',  json_encode($revenues));

// Calculate earnings by month for last 12 months
$earningsRes = $conn->query("
    SELECT DATE_FORMAT(date_request, '%Y-%m') AS month,
           COALESCE(SUM(total),0) AS revenue
      FROM shipments
     WHERE date_request >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
     GROUP BY month
     ORDER BY month
");
$areaMonths = [];
$areaEarnings = [];
while ($row = $earningsRes->fetch_assoc()) {
    $areaMonths[]   = $row['month'];
    $areaEarnings[] = (float)$row['revenue'];
}
// Pass to template for area chart
$body->setContent('area_months',   json_encode($areaMonths));
$body->setContent('area_earnings', json_encode($areaEarnings));


// 4) Inietto il body nel frame e chiudo
$main->setContent("body", $body->get());
$main->close();