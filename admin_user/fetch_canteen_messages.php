<?php
require_once "db.php";
$canteen_id = isset($_GET['canteen_id']) ? intval($_GET['canteen_id']) : 0;
$messages = [];
if ($canteen_id) {
    $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$canteen_id ORDER BY m.date_sent ASC");
    while ($msg = $msg_query->fetch_assoc()) {
        $messages[] = $msg;
    }
}
header('Content-Type: application/json');
echo json_encode($messages);
?>