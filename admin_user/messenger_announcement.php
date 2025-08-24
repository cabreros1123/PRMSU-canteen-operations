
<?php
require_once "db.php";
if (isset($_POST['announcement_message'])) {
    $message = trim($_POST['announcement_message']);
    $sender = 'admin';
    $uploaded_files = [];
    if (!empty($_FILES['announcement_files']['name'][0])) {
        $upload_dir = dirname(__DIR__) . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        foreach ($_FILES['announcement_files']['name'] as $i => $name) {
            $tmp_name = $_FILES['announcement_files']['tmp_name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $safe_name = time() . '_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $safe_name;
            if (move_uploaded_file($tmp_name, $target)) {
                $uploaded_files[] = 'uploads/' . $safe_name;
            }
        }
    }
    $image_json = $uploaded_files ? json_encode($uploaded_files) : null;

    // Fetch all active, not deleted canteens
    $canteens = $conn->query("SELECT id FROM cantines WHERE active=0 AND del_status=0");
    while ($ct = $canteens->fetch_assoc()) {
        $canteen_id = $ct['id'];
        $stmt = $conn->prepare("INSERT INTO canteen_messages (canteen_id, message, sender, date_sent, image, is_announcement) VALUES (?, ?, ?, NOW(), ?, 1)");
        $stmt->bind_param("isss", $canteen_id, $message, $sender, $image_json);
        $stmt->execute();
        $stmt->close();
    }
    echo "success";
    exit;
}