<?php
require_once 'db.php';
$user = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : '';
if (!$user) {
    echo "<div style='color:#d32f2f;'>Invalid admin user.</div>";
    exit;
}
$row = $conn->query("SELECT * FROM users WHERE user='$user'")->fetch_assoc();
if (!$row) {
    echo "<div style='color:#d32f2f;'>Admin not found.</div>";
    exit;
}
$photo = $row['photo'] ?: 'views/cantine_profile/admin.png';
?>
<div style="text-align:center;">
    <img src="/POS-PHP/<?php echo htmlspecialchars($photo); ?>" alt="Profile" style="width:100px;height:100px;object-fit:cover;border-radius:50%;margin-bottom:12px;">
    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
    <p><b>Username:</b> <?php echo htmlspecialchars($row['user']); ?></p>
    <p><b>Profile:</b> <?php echo htmlspecialchars($row['profile']); ?></p>
    <p><b>Date Registered:</b> <?php echo htmlspecialchars($row['lastLogin']); ?></p>
    <p><b>Last Login:</b> <?php echo htmlspecialchars($row['date']); ?></p>
</div>