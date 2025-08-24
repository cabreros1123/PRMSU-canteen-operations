<?php
// Usage: require_once "messenger_widget.php";
// Requires: $selected_canteen (int, can be 0), $conn (mysqli)
?>
<link rel="stylesheet" href="messenger.css">
<div class="messenger-modal-flex">
    <div class="messenger-canteen-sidebar" id="messenger-canteen-sidebar">
        <?php
        // Fetch all canteens for the sidebar
        $canteenList = $conn->query("SELECT id, name, owner FROM cantines WHERE del_status=0 ORDER BY name ASC");
        while ($ct = $canteenList->fetch_assoc()):
            $active = (isset($selected_canteen) && $selected_canteen == $ct['id']) ? 'active' : '';
        ?>
        <div class="canteen-softbox <?= $active ?>" 
             data-canteen-id="<?= $ct['id'] ?>" 
             title="<?= htmlspecialchars($ct['name']) ?> (<?= htmlspecialchars($ct['owner']) ?>)">
            <div class="canteen-softbox-name"><?= htmlspecialchars($ct['name']) ?></div>
            <div class="canteen-softbox-owner"><?= htmlspecialchars($ct['owner']) ?></div>
        </div>
        <?php endwhile; ?>
    </div>
    <div class="messenger-box">
        <div class="messenger-messages" id="messenger-messages">
            <?php
            if (isset($selected_canteen) && $selected_canteen) {
                $msg_query = $conn->query("SELECT m.*, c.name FROM canteen_messages m LEFT JOIN cantines c ON m.canteen_id=c.id WHERE m.canteen_id=$selected_canteen ORDER BY m.date_sent ASC");
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
        </div>
        <form method="post" enctype="multipart/form-data" class="messenger-form-row" id="messenger-form">
            <input type="hidden" name="msg_canteen_id" id="msg_canteen_id" value="<?= isset($selected_canteen) ? $selected_canteen : 0 ?>">
            <input type="text" name="message" class="messenger-input" placeholder="Type your message..." required>
            <label class="messenger-file-label" title="Attach files">
                <span class="material-symbols-rounded">attach_file</span>
                <input type="file" name="files[]" class="messenger-file-input" multiple onchange="showFileNames(this)">
                <span class="file-names"></span>
            </label>
            <button type="submit" class="messenger-send-btn"><span class="material-symbols-rounded">send</span></button>
        </form>
    </div>
</div>
<div id="messenger-img-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;">
    <span onclick="closeMessengerImgModal()" style="position:absolute;top:24px;right:36px;font-size:2em;color:#fff;cursor:pointer;">&times;</span>
    <img id="messenger-img-modal-img" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 2px 16px #0008;">
</div>
<script src="messenger.js"></script>
<script>
document.querySelectorAll('.canteen-softbox').forEach(function(box){
    box.onclick = function() {
        var canteenId = this.getAttribute('data-canteen-id');
        // Remove active from all
        document.querySelectorAll('.canteen-softbox').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // AJAX load messages for this canteen
        fetch('messenger_messages.php?canteen_id=' + canteenId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('messenger-messages').innerHTML = html;
                document.getElementById('msg_canteen_id').value = canteenId;
                // Scroll to bottom
                var msgBox = document.getElementById('messenger-messages');
                msgBox.scrollTop = msgBox.scrollHeight;
            });
    };
});

document.getElementById('messenger-form').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    fetch('messenger_send.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('messenger-messages').innerHTML = data.html;
        document.querySelector('.messenger-input').value = '';
        form.reset();
        var msgBox = document.getElementById('messenger-messages');
        msgBox.scrollTop = msgBox.scrollHeight;
    });
};
</script>