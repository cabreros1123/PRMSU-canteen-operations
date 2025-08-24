<?php
require_once 'db.php';
$inspection_id = intval($_POST['inspection_id']);
$obligations = isset($_POST['obligations']) ? json_decode($_POST['obligations'], true) : [];

$res = $conn->query("SELECT obligation_and_status FROM obligations WHERE id=$inspection_id AND status=1");
if ($row = $res->fetch_assoc()) {
    $data = json_decode($row['obligation_and_status'], true);
    // Update all obligations in one go
    foreach ($data as &$item) {
        foreach ($obligations as $ob) {
            if ($item['id'] == $ob['id']) {
                $item['status'] = $ob['status'];
            }
        }
    }
    $json = $conn->real_escape_string(json_encode($data));
    $conn->query("UPDATE obligations SET obligation_and_status='$json' WHERE id=$inspection_id");
    echo "OK";
} else {
    echo "Not found";
}