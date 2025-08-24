<?php
// filepath: c:\xampp\htdocs\POS-PHP\user_cantine\canteen_profile_view.php
require_once '../admin_user/db.php'; // adjust path as needed

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo "<div style='color:#d32f2f;'>Invalid canteen ID.</div>";
    exit;
}
$row = $conn->query("SELECT * FROM cantines WHERE id=$id")->fetch_assoc();
if (!$row) {
    echo "<div style='color:#d32f2f;'>Canteen not found.</div>";
    exit;
}
$img = $row['img'] ?? 'views/cantine_profile/cantine.png';
?>
<div style="text-align:center;">
    <img src="/POS-PHP/<?php echo htmlspecialchars($img); ?>" alt="Profile" style="width:100px;height:100px;object-fit:cover;border-radius:10%;margin-bottom:12px;">
    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
    <p><b>Stall No:</b> <?php echo htmlspecialchars($row['stall_no']); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($row['email']); ?></p>
    <p><b>Phone:</b> <?php echo htmlspecialchars($row['phone']); ?></p>
    <p><b>Owner:</b> <?php echo htmlspecialchars($row['owner']); ?></p>
    <p><b>Username:</b> <?php echo htmlspecialchars($row['username']); ?></p>
    <p><b>Last Login:</b> <?php echo htmlspecialchars($row['Last_login']); ?></p>
    <p><b>Register Date:</b> <?php echo htmlspecialchars($row['registerDate']); ?></p>
</div>