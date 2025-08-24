<?php
// filepath: c:\xampp\htdocs\POS-PHP\admin_user\line_chart_data.php
include 'db.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Fetch sales data grouped by date
$query = "SELECT DATE(saledate) AS sale_date, 
                 SUM(product_cost) AS total_cost, 
                 SUM(product_sale) AS total_sales, 
                 SUM(product_sale - product_cost) AS total_profit 
          FROM sales 
          WHERE del_status = 0";

if ($startDate && $endDate) {
    $query .= " AND DATE(saledate) BETWEEN '$startDate' AND '$endDate'";
}

$query .= " GROUP BY DATE(saledate) ORDER BY DATE(saledate) ASC";

$result = $conn->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>