<?php
// filepath: c:\xampp\htdocs\POS-PHP\admin_user\get_total_sales.php
include 'db.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Fetch the total sales from the database where del_status = 0
$query = "SELECT SUM(product_sale) AS total_sales FROM sales WHERE del_status = 0";

if ($startDate && $endDate) {
    $query .= " AND DATE(saledate) BETWEEN '$startDate' AND '$endDate'";
}

$result = $conn->query($query);
$totalSales = 0;

if ($result && $row = $result->fetch_assoc()) {
    $totalSales = $row['total_sales'] ?? 0; // Default to 0 if no sales
}

// Return the total sales as JSON
header('Content-Type: application/json');
echo json_encode(['total_sales' => $totalSales]);
?>