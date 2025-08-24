<?php
// messenger_send.php: handle AJAX message send for canteen messenger
session_start();
require_once "db.php";
$selected_canteen = isset($_SESSION['id_cantine']) ? intval($_SESSION['id_cantine']) : 0;
if (!$selected_canteen) {
    http_response_code(400);
    echo json_encode(["error" => "Canteen not found"]);
    exit;
}
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$file_paths = [];
if (!empty($_FILES['files']['name'][0])) {
    $target_dir = __DIR__ . '/../uploads/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    foreach ($_FILES['files']['name'] as $i => $file_name) {
        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
            $file_path = time() . '_' . basename($file_name);
            move_uploaded_file($_FILES['files']['tmp_name'][$i], $target_dir . $file_path);
            $file_paths[] = 'uploads/' . $file_path;
        }
    }
}
$files_json = json_encode($file_paths);
if ($message !== '') {
    $conn->query("INSERT INTO canteen_messages (canteen_id, sender, message, image) VALUES ($selected_canteen, 'canteen', '".$conn->real_escape_string($message)."', '".$conn->real_escape_string($files_json)."')");
    echo json_encode(["success" => true]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Message is empty"]);
}
