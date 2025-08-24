<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['id_cantine'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_POST['id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$owner = trim($_POST['owner']);
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$stall_no = trim($_POST['stall_no']);

// Check for duplicate stall_no (exclude current canteen)
$stmt = $conn->prepare("SELECT id FROM cantines WHERE stall_no = ? AND id != ?");
$stmt->bind_param("si", $stall_no, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "<script>alert('Stall No. $stall_no already exists!');window.history.back();</script>";
    exit();
}
$stmt->close();

$stmt = $conn->prepare("UPDATE cantines SET name=?, email=?, phone=?, owner=?, username=?, password=?, stall_no=? WHERE id=?");
$stmt->bind_param("sssssssi", $name, $email, $phone, $owner, $username, $password, $stall_no, $id);
$stmt->execute();
$stmt->close();

// Update session values
$_SESSION['cantine_name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['phone'] = $phone;
$_SESSION['owner'] = $owner;
$_SESSION['username'] = $username;
$_SESSION['password'] = $password;
$_SESSION['stall_no'] = $stall_no;

header("Location: home.php");
exit;