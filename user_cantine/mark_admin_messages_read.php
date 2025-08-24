<?php
session_start();
require_once "db.php";

$id_cantine = $_SESSION['id_cantine'] ?? 0;
if ($id_cantine) {
    // Mark all unread admin messages as read for this canteen
    $stmt = $conn->prepare("UPDATE canteen_messages SET is_read=1 WHERE canteen_id=? AND sender='admin' AND is_read=0");
    $stmt->bind_param("i", $id_cantine);
    $stmt->execute();
}
echo "OK";