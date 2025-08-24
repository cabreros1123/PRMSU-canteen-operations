<?php
require_once "../db.php";
session_start();

$id_cantine = $_SESSION['id_cantine'] ?? 0;
$type = $_POST['type'] ?? '';
$response = ['exists' => false];

// Check Official Receipt No.
if ($type === 'or_no' && isset($_POST['or_no'])) {
    $or_no = $_POST['or_no'];
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
    $response['exists'] = $stmt->num_rows > 0;
    $stmt->close();
}

// Check duplicate bill month
if ($type === 'bill_month' && isset($_POST['bill_type'], $_POST['date'])) {
    $bill_type = intval($_POST['bill_type']);
    $date = $_POST['date'];
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $stmt = $conn->prepare("SELECT 1 FROM bills WHERE cantine_id=? AND bills_type=? AND MONTH(date)=? AND YEAR(date)=?");
    $stmt->bind_param("iiii", $id_cantine, $bill_type, $month, $year);
    $stmt->execute();
    $stmt->store_result();
    $response['exists'] = $stmt->num_rows > 0;
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);