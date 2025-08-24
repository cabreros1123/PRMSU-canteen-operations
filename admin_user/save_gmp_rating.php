<?php
require_once "db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cantine_id'])) {
    // Debug: Log the number of items received for each array
    $debug_log = [
        'section_count' => isset($_POST['section']) ? count($_POST['section']) : 0,
        'rating_count' => isset($_POST['rating']) ? count($_POST['rating']) : 0,
        'evidence_count' => isset($_POST['evidence']) ? count($_POST['evidence']) : 0,
        'img_count' => isset($_FILES['img']['name']) ? count($_FILES['img']['name']) : 0,
    ];
    file_put_contents(__DIR__ . '/debug_gmp_save.log', json_encode($debug_log) . PHP_EOL, FILE_APPEND);
    $cantine_id = intval($_POST['cantine_id']);
    $sections = $_POST['section'] ?? [];
    $ratings = $_POST['rating'] ?? [];
    $evidences = $_POST['evidence'] ?? [];
    $imgFiles = $_FILES['img'] ?? null;
    $group_code = uniqid('FSR-');
    for ($i = 0; $i < count($sections); $i++) {
        $section_no = intval($i) + 1;
        $section_title = $conn->real_escape_string($sections[$i]);
        $rating = intval($ratings[$i] ?? 0);
        $evidence = $conn->real_escape_string($evidences[$i] ?? '');
        $imgPath = null;
        if ($imgFiles && isset($imgFiles['tmp_name'][$i]) && $imgFiles['tmp_name'][$i]) {
            $uploadDir = '../food_safety_uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($imgFiles['name'][$i], PATHINFO_EXTENSION);
            $fileName = 'canteen_'.$cantine_id.'_section_'.$section_no.'_'.time().'_'.rand(1000,9999).'.'.$ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($imgFiles['tmp_name'][$i], $targetPath)) {
                $imgPath = 'food_safety_uploads/' . $fileName;
            }
        }
        $imgPathSql = $imgPath ? "'".$conn->real_escape_string($imgPath)."'" : "NULL";
        $conn->query("INSERT INTO food_safety_ratings (cantine_id, section_no, section_title, rating, evidence, img, group_code) VALUES ($cantine_id, $section_no, '$section_title', $rating, '$evidence', $imgPathSql, '$group_code')");
    }
    $overall_note = isset($_POST['overall_note']) ? trim($_POST['overall_note']) : '';
    date_default_timezone_set('Asia/Manila');
    $date_now = date('Y-m-d H:i:s');
    if ($overall_note && $group_code) {
        // Only insert if group_code does not already exist
        $check = $conn->prepare("SELECT COUNT(*) FROM food_safety_category_code WHERE group_code = ?");
        $check->bind_param("s", $group_code);
        $check->execute();
        $check->bind_result($exists);
        $check->fetch();
        $check->close();
        if ($exists == 0) {
            $stmt = $conn->prepare("INSERT INTO food_safety_category_code (group_code, note, date) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $group_code, $overall_note, $date_now);
            $stmt->execute();
            $stmt->close();
        }
    }
    echo json_encode(['success'=>true, 'group_code'=>$group_code]);
    exit;
}
echo json_encode(['success'=>false]);