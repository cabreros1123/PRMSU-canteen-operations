<?php
// fetch_canteen_messages.php: returns HTML for all messages for AJAX refresh
session_start();
require_once "db.php";
$selected_canteen = isset($_SESSION['id_cantine']) ? intval($_SESSION['id_cantine']) : 0;
if (!$selected_canteen) {
    echo "<div style='color:#888;'>Messenger unavailable: canteen not found.</div>";
    exit;
}
$msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$selected_canteen ORDER BY m.date_sent ASC");
while ($msg = $msg_query->fetch_assoc()):
    $is_canteen = $msg['sender'] === 'canteen';
?>
<div class="messenger-message <?= $is_canteen ? 'canteen' : 'admin' ?>">
    <div class="messenger-bubble">
        <?php if (!empty($msg['is_announcement']) && $msg['is_announcement']): ?>
            <span class="material-symbols-rounded" style="vertical-align:middle;color:#fbc02d;font-size:1.5em;margin-right:6px;">campaign</span>
        <?php endif; ?>
        <?php if (!empty($msg['is_payment_bill']) && $msg['is_payment_bill']): ?>
            <span class="material-symbols-rounded" style="vertical-align:middle;color:#388e3c;font-size:1.5em;margin-right:6px;">receipt_long</span>
        <?php endif; ?>
        <?= nl2br(htmlspecialchars($msg['message'])) ?>
        <?php
        if ($msg['image']) {
            $files = json_decode($msg['image'], true);
            if (is_array($files) && count($files)) {
                echo '<div class="messenger-files">';
                foreach ($files as $file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
                        if (strpos($file, 'uploads/') === 0 || strpos($file, 'views/or_img/') === 0) {
                            $imgPath = '/POS-PHP/' . $file;
                        } else {
                            $imgPath = $file;
                        }
                        echo '<img src="'.htmlspecialchars($imgPath).'" alt="img" style="cursor:pointer;max-width:120px;max-height:80px;border-radius:4px;vertical-align:top;" onclick="showMessengerImgModal(this.src)">';
                    } else {
                        echo '<a href="'.htmlspecialchars($file).'" target="_blank">'.basename($file).'</a>';
                    }
                }
                echo '</div>';
            }
        }
        ?>
    </div>
    <div class="messenger-meta">
        <?= date('M d, Y H:i', strtotime($msg['date_sent'])) ?>
    </div>
</div>
<?php endwhile; ?>
