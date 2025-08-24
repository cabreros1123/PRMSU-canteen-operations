<?php
session_start();
require_once "db.php";
$id_cantine = $_SESSION['id_cantine'] ?? 0;
$or_no = $_GET['or_no'] ?? '';
if (!$or_no || !$id_cantine) {
    echo json_encode(['exists' => false]);
    exit;
}

// Only block if there is a bills_img with this or_no AND a bills record with ver_status != 0
$stmt = $conn->prepare("
    SELECT 1 
    FROM bills_img 
    WHERE or_no = ? 
    AND EXISTS (
        SELECT 1 FROM bills WHERE or_no = bills_img.or_no AND ver_status != 0
    )
");
$stmt->bind_param("s", $or_no);
$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();
echo json_encode(['exists' => $exists]);