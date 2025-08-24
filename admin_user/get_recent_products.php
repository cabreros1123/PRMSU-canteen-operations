<?php
include 'db.php';

$base_url = 'http://localhost/POS-PHP/';

// Fetch the recent products from the database where del_status = 0
$query = "SELECT description, image, DATE_FORMAT(date, '%Y-%m-%d %h:%i %p') AS formatted_date 
          FROM products 
          WHERE del_status = 0 
          ORDER BY date DESC 
          LIMIT 10";
$result = $conn->query($query);

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'description' => $row['description'],
            'image' => $base_url . $row['image'], // Prepend the base URL
            'date' => $row['formatted_date']
        ];
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($products);
?>