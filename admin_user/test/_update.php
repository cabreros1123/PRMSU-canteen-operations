<?php
require_once 'db.php';
$inspection_id = intval($_POST['inspection_id']);
$obligation_id = intval($_POST['obligation_id']);
$status = $_POST['status'];
$res = $conn->query("SELECT obligation_and_status FROM obligations WHERE id=$inspection_id AND status=1");
if ($row = $res->fetch_assoc()) {
    $data = json_decode($row['obligation_and_status'], true);
    foreach ($data as &$item) {
        if ($item['id'] == $obligation_id) $item['status'] = $status;
    }
    $json = $conn->real_escape_string(json_encode($data));
    $conn->query("UPDATE obligations SET obligation_and_status='$json' WHERE id=$inspection_id");
}