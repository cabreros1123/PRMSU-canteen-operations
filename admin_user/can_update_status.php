<?php
require_once "db.php";
$category_no = intval($_POST['category_no'] ?? 0);
$status = intval($_POST['status'] ?? -1);

$stmt = $conn->prepare("SELECT or_no FROM bills_img WHERE id = ?");
$stmt->bind_param("i", $category_no);
$stmt->execute();
$stmt->bind_result($or_no);
if (!$stmt->fetch()) {
    echo json_encode(['can_update' => false]);
    exit;
}
$stmt->close();

if ($status == 1 || $status == 2) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bills WHERE or_no = ? AND category_no != ? AND ver_status = 0");
    $stmt->bind_param("si", $or_no, $category_no);
    $stmt->execute();
    $stmt->bind_result($declined_count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM bills WHERE or_no = ? AND category_no != ? AND ver_status IN (1,2)");
    $stmt->bind_param("si", $or_no, $category_no);
    $stmt->execute();
    $stmt->bind_result($active_count);
    $stmt->fetch();
    $stmt->close();

    if ($declined_count > 0 && $active_count > 0) {
        echo json_encode(['can_update' => false]);
        exit;
    }
}
echo json_encode(['can_update' => true]);