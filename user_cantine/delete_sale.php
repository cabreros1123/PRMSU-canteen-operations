<?php
include 'db.php';

header('Content-Type: application/json');

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$id = $data['id'];

// Perform a soft delete by updating the del_status column to 1
$query = "UPDATE sales SET del_status = 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete sale.']);
}

$stmt->close();
$conn->close();
?>