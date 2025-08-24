<?php
require_once 'db.php';
$id = intval($_GET['id']);
$row = $conn->query("SELECT food_quality,food_safety,hygiene,service_quality FROM canteen_inspection_ratings WHERE inspection_id=$id")->fetch_assoc();
echo json_encode($row ?: []);
?>