<?php
require_once "db.php";
session_start();

$canteen_id = intval($_POST['msg_canteen_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$sender = 'admin'; // or get from session

if ($canteen_id && $message !== '') {
    $stmt = $conn->prepare("INSERT INTO canteen_messages (canteen_id, message, sender, date_sent) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $canteen_id, $message, $sender);
    $stmt->execute();
    $stmt->close();
}

$html = '';
if ($canteen_id) {
    $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$canteen_id ORDER BY m.date_sent ASC");
    while ($msg = $msg_query->fetch_assoc()) {
        $is_admin = $msg['sender'] === 'admin';
        $html .= '<div class="messenger-message '.($is_admin ? 'admin' : '').'">';
        $html .= '<div class="messenger-bubble">'.nl2br(htmlspecialchars($msg['message'])).'</div>';
        $html .= '<div class="messenger-meta">'.($is_admin ? 'You' : htmlspecialchars($msg['name'])).' &bull; '.date('M d, Y H:i', strtotime($msg['date_sent'])).'</div>';
        $html .= '</div>';
    }
}

header('Content-Type: application/json');
echo json_encode(['html' => $html]);
?>