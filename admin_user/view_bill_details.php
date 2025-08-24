<?php
require_once "db.php";
header('Content-Type: application/json');

$category_no = $_GET['category_no'] ?? '';
if (!$category_no) { echo json_encode([]); exit; }

// Get bills_img info
$stmt = $conn->prepare("SELECT img, or_no, real_date FROM bills_img WHERE id = ?");
$stmt->bind_param("i", $category_no);
$stmt->execute();
$res = $stmt->get_result();
$imgRow = $res->fetch_assoc();
$stmt->close();

if (!$imgRow) { echo json_encode([]); exit; }

// Get all bills for this category_no
$stmt = $conn->prepare("SELECT bills_type, payment, cantine_id, name_other, date FROM bills WHERE category_no = ? AND ver_status != 0");
$stmt->bind_param("i", $category_no);
$stmt->execute();
$res = $stmt->get_result();
$bills = [];
$total = 0;
$cantine_name = '';
while ($row = $res->fetch_assoc()) {
    $bills[] = $row;
    $total += floatval($row['payment']);
    // Get cantine name (first found)
    if (!$cantine_name && $row['cantine_id']) {
        $stmt2 = $conn->prepare("SELECT name FROM cantines WHERE id = ?");
        $stmt2->bind_param("i", $row['cantine_id']);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($cRow = $res2->fetch_assoc()) $cantine_name = $cRow['name'];
        $stmt2->close();
    }
}
$stmt->close();

echo json_encode([
    'img' => $imgRow['img'],
    'or_no' => $imgRow['or_no'],
    'real_date' => $imgRow['real_date'],
    'cantine_name' => $cantine_name,
    'bills' => $bills,
    'total' => number_format($total, 2, '.', '')
]);