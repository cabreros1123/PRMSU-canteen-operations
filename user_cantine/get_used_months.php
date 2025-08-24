<?php
session_start();
require_once "db.php";
$id_cantine = $_SESSION['id_cantine'] ?? 0;
$type = intval($_GET['type'] ?? 0);

$months = [];
if ($id_cantine && in_array($type, [1,2,3])) {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') as ym FROM bills WHERE cantine_id=? AND bills_type=? AND ver_status != 0");
    $stmt->bind_param("ii", $id_cantine, $type);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $months[] = $row['ym'];
    }
    $stmt->close();
}
echo json_encode($months);