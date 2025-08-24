<?php
// filepath: c:\xampp\htdocs\POS-PHP\user_cantine\poll_admin_message.php
session_start();
require_once "db.php";
header('Content-Type: application/json');

$id_cantine = $_SESSION['id_cantine'] ?? 0;
if (!$id_cantine) {
    echo json_encode([]);
    exit;
}

// Get the latest unread message from admin to this canteen
$stmt = $conn->prepare("SELECT id, message, sender, date_sent FROM canteen_messages WHERE canteen_id=? AND sender='admin' AND is_read=0 ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $id_cantine);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([]);
}