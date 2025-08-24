<?php
require_once 'db.php';
$inspection_id = intval($_POST['inspection_id']);
$conn->query("UPDATE obligations SET status=0 WHERE id=$inspection_id");