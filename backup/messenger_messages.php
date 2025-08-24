<?php
require_once "db.php";
$canteen_id = intval($_GET['canteen_id'] ?? 0);
if ($canteen_id) {
    $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$canteen_id ORDER BY m.date_sent ASC");
    while ($msg = $msg_query->fetch_assoc()):
        $is_admin = $msg['sender'] === 'admin';
?>
    <div class="messenger-message <?= $is_admin ? 'admin' : '' ?>">
        <div class="messenger-bubble">
            <?php if (!empty($msg['is_announcement']) && $msg['is_announcement']): ?>
                <span class="material-symbols-rounded" style="vertical-align:middle;color:#fbc02d;font-size:1.5em;margin-right:6px;">campaign</span>
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
                            echo '<img src="'.htmlspecialchars($imgPath).'" alt="img" style="cursor:pointer;" onclick="showMessengerImgModal(this.src)">';
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
            <?= $is_admin ? 'You' : htmlspecialchars($msg['name']) ?> &bull; <?= date('M d, Y H:i', strtotime($msg['date_sent'])) ?>
        </div>
    </div>
<?php endwhile;
} else {
    echo "<div style='color:#888;'>Select a canteen to view messages.</div>";
}
?>