<?php
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$inspection_id = intval($data['inspection_id']);
$fields = ['food_quality','food_safety','hygiene','service_quality'];
$set = [];
foreach ($fields as $f) {
    if (isset($data[$f])) $set[$f] = intval($data[$f]);
}
if (!$inspection_id || count($set) < 1) { echo json_encode(['success'=>false]); exit; }
if (empty($data['autosave'])) {
    // Insert or update all fields
    $sql = "INSERT INTO canteen_inspection_ratings (inspection_id, food_quality, food_safety, hygiene, service_quality)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE food_quality=VALUES(food_quality), food_safety=VALUES(food_safety), hygiene=VALUES(hygiene), service_quality=VALUES(service_quality)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $inspection_id, $set['food_quality'], $set['food_safety'], $set['hygiene'], $set['service_quality']);
    $stmt->execute();
    echo json_encode(['success'=>true]);
} else {
    // Autosave single field
    // Check if row exists
    $exists = $conn->query("SELECT 1 FROM canteen_inspection_ratings WHERE inspection_id=$inspection_id")->num_rows;
    if (!$exists) {
        // Insert with all fields set to 3 (neutral)
        $conn->query("INSERT INTO canteen_inspection_ratings (inspection_id, food_quality, food_safety, hygiene, service_quality)
            VALUES ($inspection_id, 3, 3, 3, 3)");
    }
    $field = array_keys($set)[0];
    $value = array_values($set)[0];
    $conn->query("UPDATE canteen_inspection_ratings SET $field=$value WHERE inspection_id=$inspection_id");
    echo json_encode(['success'=>true]);
}
?>