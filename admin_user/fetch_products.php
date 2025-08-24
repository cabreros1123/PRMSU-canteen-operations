<?php
// filepath: c:\xampp\htdocs\POS-PHP\admin_user\fetch_products.php
include 'db.php';

if (!isset($_GET['cantine_id'])) {
    echo json_encode(['error' => 'Canteen ID is required.']);
    exit;
}

$cantine_id = intval($_GET['cantine_id']);

// Fetch products for the selected canteen
$query = "
    SELECT 
        p.id, 
        p.description, 
        p.image, 
        p.stock, 
        p.buyingPrice, 
        p.sellingPrice, 
        p.product_type, 
        c.Category AS category_name 
    FROM 
        products p
    LEFT JOIN 
        categories c 
    ON 
        p.idCategory = c.id 
    WHERE 
        p.cantine_id = ? AND p.del_status = 0
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die(json_encode(['error' => 'Failed to prepare the SQL statement.']));
}

$stmt->bind_param('i', $cantine_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);
$stmt->close();
$conn->close();
?>