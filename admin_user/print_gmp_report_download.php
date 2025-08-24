<?php
require_once "db.php";
$cantine_id = intval($_GET['cantine_id'] ?? 0);
$group_code = $_GET['group_code'] ?? '';
// Use $cantine_id and $group_code to fetch and print the correct GMP report
?>