<?php
require_once "db.php";
session_start();

$canteen_id = intval($_POST['msg_canteen_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$sender = 'admin'; // or get from session

$uploaded_files = [];
if (!empty($_FILES['files']['name'][0])) {
    // Save to main project 'uploads' folder
    $upload_dir = dirname(__DIR__) . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    foreach ($_FILES['files']['name'] as $i => $name) {
        $tmp_name = $_FILES['files']['tmp_name'][$i];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $safe_name = time() . '_' . uniqid() . '.' . $ext;
        $target = $upload_dir . $safe_name;
        if (move_uploaded_file($tmp_name, $target)) {
            // Save as 'uploads/filename.jpg' (relative path)
            $uploaded_files[] = 'uploads/' . $safe_name;
        }
    }
}

$image_json = $uploaded_files ? json_encode($uploaded_files) : null;

if ($canteen_id && $message !== '') {
    $stmt = $conn->prepare("INSERT INTO canteen_messages (canteen_id, message, sender, date_sent, image) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("isss", $canteen_id, $message, $sender, $image_json);
    $stmt->execute();
    $stmt->close();
} elseif ($canteen_id && $image_json) {
    // Allow sending only files (no message)
    $stmt = $conn->prepare("INSERT INTO canteen_messages (canteen_id, message, sender, date_sent, image) VALUES (?, ?, ?, NOW(), ?)");
    $empty = '';
    $stmt->bind_param("isss", $canteen_id, $empty, $sender, $image_json);
    $stmt->execute();
    $stmt->close();
}

$html = '';
if ($canteen_id) {
    $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$canteen_id ORDER BY m.date_sent ASC");
    while ($msg = $msg_query->fetch_assoc()) {
        $is_admin = $msg['sender'] === 'admin';
        $html .= '<div class="messenger-message '.($is_admin ? 'admin' : '').'">';
        $html .= '<div class="messenger-bubble">'.nl2br(htmlspecialchars($msg['message']));
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
                        $html .= '<a href="'.htmlspecialchars($file).'" target="_blank">'.basename($file).'</a>';
                    }
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        $html .= '<div class="messenger-meta">'.($is_admin ? 'You' : htmlspecialchars($msg['name'])).' &bull; '.date('M d, Y H:i', strtotime($msg['date_sent'])).'</div>';
        $html .= '</div>';
    }
}

header('Content-Type: application/json');
echo json_encode(['html' => $html]);
?>