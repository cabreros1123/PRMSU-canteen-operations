<?php
$servername = "localhost";  // Change if using a different host
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password (leave empty if none)
$database = "posystem";     // Your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
