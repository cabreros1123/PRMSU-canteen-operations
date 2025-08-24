<?php
// filepath: c:\xampp\htdocs\POS-PHP\user_cantine\line_chart_data.php
include 'db.php';
session_start(); // Ensure the session is started

// Check if the user is logged in
if (!isset($_SESSION['id_cantine'])) {
    die(json_encode(['error' => 'Unauthorized access. Please log in.']));
}

$id_cantine = $_SESSION['id_cantine']; // Get the logged-in canteen's ID
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Base query to fetch sales data grouped by date for the logged-in canteen
$query = "SELECT DATE(saledate) AS sale_date, 
                 SUM(product_cost) AS total_cost, 
                 SUM(product_sale) AS total_sales, 
                 SUM(product_sale - product_cost) AS total_profit 
          FROM sales 
          WHERE id_cantine = ? AND del_status = 0"; // Add condition to exclude soft-deleted sales

// Add date filtering if both start_date and end_date are provided
if ($startDate && $endDate) {
    $query .= " AND DATE(saledate) BETWEEN ? AND ?";
}

$query .= " GROUP BY DATE(saledate) 
            ORDER BY DATE(saledate) ASC";

$stmt = $conn->prepare($query);

// Bind parameters based on whether date filtering is applied
if ($startDate && $endDate) {
    $stmt->bind_param('iss', $id_cantine, $startDate, $endDate);
} else {
    $stmt->bind_param('i', $id_cantine);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
$totalSales = 0; // Initialize total sales

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $totalSales += $row['total_sales']; // Accumulate total sales
}

// Include total sales in the response
$response = [
    'data' => $data,
    'total_sales' => $totalSales
];

header('Content-Type: application/json');
echo json_encode($response);
?>