<?php
// filepath: c:\xampp\htdocs\POS-PHP\user_cantine\canteen_profile_api.php
session_start();
require_once "db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$q = $conn->prepare("SELECT id, stall_no, name, email, phone, owner, username, password FROM cantines WHERE id=? LIMIT 1");
$q->bind_param("i", $id);
$q->execute();
$res = $q->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Not found']);
}