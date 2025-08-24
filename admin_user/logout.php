<?php
session_start();

// Include database connection
include 'db.php';

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];

    // Mark the account as inactive in the database
    $sql = "UPDATE users SET status = 0 WHERE user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->close();
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page
header("Location: ../admin_login.php");
exit();
?>