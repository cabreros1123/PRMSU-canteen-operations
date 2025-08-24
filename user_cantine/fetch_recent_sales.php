<?php
include 'db.php';

if (!isset($_GET['cantine_id'])) {
    echo json_encode(['error' => 'Canteen ID is required.']);
    exit;
}

$cantine_id = intval($_GET['cantine_id']);

// Fetch the most recent sale for the given canteen
$query = "SELECT products FROM recent_sales WHERE cantine_id = ? ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $cantine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $products = json_decode($row['products'], true); // Decode the JSON string into an array

    // Return the products as JSON
    echo json_encode($products);
} else {
    echo json_encode(['error' => 'No recent sales found for this canteen.']);
}

$stmt->close();
$conn->close();
?>