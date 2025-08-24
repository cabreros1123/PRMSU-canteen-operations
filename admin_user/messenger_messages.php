<?php
require_once "db.php";
$canteen_id = intval($_GET['canteen_id'] ?? 0);
$last_id = intval($_GET['last_id'] ?? 0);

$html = '';
$last_id_found = 0;
$last_sender = '';
$last_sender_name = '';
$last_message = '';

if (isset($_GET['all']) && $_GET['all'] == 1) {
    $canteens = [];
    $q = $conn->query("SELECT c.id, c.name, 
        (SELECT MAX(m.id) FROM canteen_messages m WHERE m.canteen_id=c.id) as last_id,
        (SELECT m.sender FROM canteen_messages m WHERE m.canteen_id=c.id ORDER BY m.id DESC LIMIT 1) as last_sender,
        (SELECT m.message FROM canteen_messages m WHERE m.canteen_id=c.id ORDER BY m.id DESC LIMIT 1) as last_message
        FROM cantines c WHERE c.del_status=0");
    while ($row = $q->fetch_assoc()) {
        $canteens[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'last_id' => intval($row['last_id']),
            'last_sender' => $row['last_sender'],
            'last_message' => $row['last_message']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['canteens' => $canteens]);
    exit;
}

if ($canteen_id) {
    $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$canteen_id ORDER BY m.date_sent ASC");
    while ($msg = $msg_query->fetch_assoc()) {
        $is_admin = $msg['sender'] === 'admin';
        $html .= '<div class="messenger-message '.($is_admin ? 'admin' : '').'">';
        $html .= '<div class="messenger-bubble">';
        if (!empty($msg['is_announcement']) && $msg['is_announcement']) {
            $html .= '<span class="material-symbols-rounded" style="vertical-align:middle;color:#fbc02d;font-size:1.5em;margin-right:6px;">campaign</span>';
            $html .= '<span style="color:#fbc02d;font-weight:bold;margin-right:8px;">Announcement:</span>';
        }
        $html .= nl2br(htmlspecialchars($msg['message']));
        if ($msg['image']) {
            $files = json_decode($msg['image'], true);
            if (is_array($files) && count($files)) {
                $html .= '<div class="messenger-files">';
                foreach ($files as $file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
                        $imgPath = (strpos($file, 'uploads/') === 0) ? '/POS-PHP/' . $file : $file;
                        $html .= '<img src="'.htmlspecialchars($imgPath).'" alt="img" style="cursor:pointer;" onclick="showMessengerImgModal(this.src)">';
                    } else {
                        $fileName = basename($file);
                        $filePath = '/POS-PHP/uploads/' . $fileName;
                        $html .= '<a href="'.htmlspecialchars($filePath).'" download="'.htmlspecialchars($fileName).'">'.$fileName.'</a>';
                    }
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        $html .= '<div class="messenger-meta">'.($is_admin ? 'You' : htmlspecialchars($msg['name'])).' &bull; '.date('M d, Y H:i', strtotime($msg['date_sent'])).'</div>';
        $html .= '</div>';
        $last_id_found = $msg['id'];
        $last_sender = $msg['sender'];
        $last_sender_name = $msg['name'];
        $last_message = $msg['message'];
    }
}
header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'last_id' => $last_id_found,
    'last_sender' => $last_sender,
    'last_sender_name' => $last_sender_name,
    'last_message' => $last_message
]);
?>