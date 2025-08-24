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
        <div style="padding:10px 0;">
    <input type="text" id="messageSearchInput" placeholder="Search messages..." style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid #ccc;">
</div>
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
                            <span style="color:#fbc02d;font-weight:bold;margin-right:8px;">Announcement:</span>
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
                                        $imgPath = (strpos($file, 'uploads/') === 0) ? '/POS-PHP/' . $file : $file;
                                        echo '<img src="'.htmlspecialchars($imgPath).'" alt="img" style="cursor:pointer;" onclick="showMessengerImgModal(this.src)">';
                                    } else {
                                        // Always use /POS-PHP/uploads/ for file links
                                        $fileName = basename($file);
                                        $filePath = '/POS-PHP/uploads/' . $fileName;
                                        echo '<a href="'.htmlspecialchars($filePath).'" download="'.htmlspecialchars($fileName).'">'.$fileName.'</a>';
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
        <div class="messenger-file-preview" id="messenger-file-preview"></div>
        <form class="messenger-form-row" id="messenger-form" enctype="multipart/form-data">
            <input type="hidden" name="msg_canteen_id" id="msg_canteen_id" value="<?= isset($selected_canteen) ? $selected_canteen : 0 ?>">
            <input type="text" name="message" class="messenger-input" placeholder="Type your message..." required>
            <label class="messenger-file-label" title="Attach files" style="position:relative;">
                <span class="material-symbols-rounded">attach_file</span>
                <input type="file" name="files[]" class="messenger-file-input" multiple style="display:none;" onchange="showFileNamePreview(this)">
                <span class="file-name-preview"></span>
            </label>
            <button type="submit" class="messenger-send-btn"><span class="material-symbols-rounded">send</span></button>
        </form>
    </div>
</div>
<div id="messenger-img-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;">
    <span onclick="closeMessengerImgModal()" style="position:absolute;top:24px;right:36px;font-size:2em;color:#fff;cursor:pointer;">&times;</span>
    <img id="messenger-img-modal-img" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 2px 16px #0008;">
</div>
<!-- Announcement Modal -->
<div id="announcement-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 24px;border-radius:16px;max-width:420px;width:90vw;box-shadow:0 2px 16px #0008;position:relative;">
        <span onclick="closeAnnouncementModal()" style="position:absolute;top:18px;right:24px;font-size:2em;color:#888;cursor:pointer;">&times;</span>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <h3 style="margin:0;">Send Announcement to All Canteens</h3>
            <button type="submit" form="announcementForm" class="btn btn-warning" style="margin-left:12px;">
                <span class="material-symbols-rounded" style="vertical-align:middle;">campaign</span>
                Send
            </button>
        </div>
        <form id="announcementForm" method="post" enctype="multipart/form-data">
            <div class="messenger-row">
                <label class="messenger-file-label" title="Attach files">
                    <span class="material-symbols-rounded">attach_file</span>
                    <input type="file" name="announcement_files[]" class="messenger-file-input" multiple style="display:none;">
                </label>
                <input type="text" name="announcement_message" class="messenger-input" placeholder="Type your announcement..." required>
            </div>
        </form>
    </div>
</div>
<script src="messenger.js"></script>
<script>
    function resetMessengerFileInput() {
    const fileInput = document.querySelector('.messenger-file-input');
    const preview = document.getElementById('messenger-file-preview');

    // Clear file input manually (form.reset() doesn’t always clear FileList)
    const newInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(newInput, fileInput);

    // Hide and clear preview
    preview.innerHTML = '';
    preview.style.display = 'none';
}

document.querySelectorAll('.canteen-softbox').forEach(function(box){
    box.addEventListener('click', function() {
        var canteenId = this.getAttribute('data-canteen-id');
        selectedCanteen = canteenId;
        setCurrentMessengerCanteenId(canteenId); // <-- Add this line
        // Remove active from all
        document.querySelectorAll('.canteen-softbox').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // AJAX load messages for this canteen
        fetch('messenger_messages.php?canteen_id=' + canteenId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('messenger-messages').innerHTML = html;
                document.getElementById('msg_canteen_id').value = canteenId;
                // Always scroll to bottom after loading messages
                var msgBox = document.getElementById('messenger-messages');
                msgBox.scrollTop = msgBox.scrollHeight;
            });
    });
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

        resetMessengerFileInput(); // <--- Call the new function here

        scrollMessagesToBottom();
    });
};


let selectedCanteen = document.getElementById('msg_canteen_id').value || 0;
let lastMessageId = 0;

// On initial load, get the last message id
document.addEventListener('DOMContentLoaded', function() {
    if (selectedCanteen && selectedCanteen != 0) {
        fetch('messenger_messages.php?canteen_id=' + selectedCanteen)
            .then(res => res.json())
            .then(data => {
                lastMessageId = data.last_id || 0;
            });
    }
    scrollMessagesToBottom();
});

// Poll for new messages every 2 seconds
setInterval(function() {
    if (!selectedCanteen || selectedCanteen == 0) return;
    fetch('messenger_messages.php?canteen_id=' + selectedCanteen + '&last_id=' + lastMessageId)
        .then(res => res.json())
        .then(data => {
            if (data.last_id && data.last_id > lastMessageId) {
                document.getElementById('messenger-messages').innerHTML = data.html;
                scrollMessagesToBottom();
                lastMessageId = data.last_id;
            }
        });
}, 2000);

// When you select a canteen, update selectedCanteen and lastMessageId
document.querySelectorAll('.canteen-softbox').forEach(function(box){
    box.addEventListener('click', function() {
        var canteenId = this.getAttribute('data-canteen-id');
        selectedCanteen = canteenId;
        fetch('messenger_messages.php?canteen_id=' + canteenId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('messenger-messages').innerHTML = data.html;
                document.getElementById('msg_canteen_id').value = canteenId;
                scrollMessagesToBottom();
                lastMessageId = data.last_id || 0;
            });
    });
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
        scrollMessagesToBottom();
    });
};

function showFileNames(input) {
    var preview = document.getElementById('messenger-file-preview');
    preview.innerHTML = '';
    if (input.files && input.files.length) {
        Array.from(input.files).forEach(function(file, idx) {
            var ext = file.name.split('.').pop().toLowerCase();
            var el;
            if (['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
                el = document.createElement('img');
                el.src = URL.createObjectURL(file);
                el.style.maxWidth = '80px';
                el.style.maxHeight = '80px';
                el.style.margin = '4px';
                el.style.borderRadius = '8px';
            } else {
                el = document.createElement('div');
                el.textContent = file.name;
                el.style.margin = '4px';
                el.style.padding = '4px 8px';
                el.style.background = '#e3f2fd';
                el.style.borderRadius = '6px';
                el.style.display = 'inline-block';
            }
            // Add remove button
            var removeBtn = document.createElement('span');
            removeBtn.textContent = '×';
            removeBtn.style.marginLeft = '8px';
            removeBtn.style.cursor = 'pointer';
            removeBtn.style.color = '#d32f2f';
            removeBtn.onclick = function() {
                var dt = new DataTransfer();
                Array.from(input.files).forEach(function(f, i) {
                    if (i !== idx) dt.items.add(f);
                });
                input.files = dt.files;
                showFileNames(input);
            };
            el.appendChild(removeBtn);
            preview.appendChild(el);
        });
        preview.style.display = 'flex';
        preview.style.flexWrap = 'wrap';
        preview.style.alignItems = 'center';
        preview.style.gap = '8px';
        preview.style.margin = '8px 0 0 0';
    } else {
        preview.innerHTML = '';
        preview.style.display = 'none';
    }
}

function showFileNamePreview(input) {
    const preview = input.parentNode.querySelector('.file-name-preview');
    if (input.files && input.files.length > 0) {
        let names = [];
        for (let i = 0; i < input.files.length; i++) {
            names.push(input.files[i].name);
        }
        preview.textContent = names.join(', ');
    } else {
        preview.textContent = '';
    }
}

document.getElementById('closeMessengerBtn').onclick = function() {
    document.getElementById('messengerModal').style.display = 'none';
    setCurrentMessengerCanteenId(null); // <-- Add this line
}

document.getElementById('messageSearchInput').addEventListener('input', function() {
    var search = this.value.toLowerCase();
    var messages = document.querySelectorAll('.messenger-message');
    let firstMatch = null;
    messages.forEach(function(msg) {
        var text = msg.textContent.toLowerCase();
        if (search && text.includes(search)) {
            msg.style.background = '#fffde7'; // highlight
            if (!firstMatch) firstMatch = msg;
        } else {
            msg.style.background = '';
        }
    });
    // Auto-scroll to first match
    if (firstMatch) {
        firstMatch.scrollIntoView({behavior: 'smooth', block: 'center'});
    } else if (!search) {
        // If search is empty, scroll to bottom
        var msgBox = document.getElementById('messenger-messages');
        msgBox.scrollTop = msgBox.scrollHeight;
    }
});

// After selecting a canteen in the messenger
function setCurrentMessengerCanteenId(id) {
    window.currentMessengerCanteenId = id;
}

document.getElementById('openMessengerBtn').onclick = function() {
    document.getElementById('messengerModal').style.display = 'flex';
    setCurrentMessengerCanteenId(selectedCanteen || null); // <-- Add this line
}
</script>
<?php
if (isset($_POST['announcement_message'])) {
    require_once "db.php";
    $message = trim($_POST['announcement_message']);
    $sender = 'admin';
    $uploaded_files = [];
    if (!empty($_FILES['announcement_files']['name'][0])) {
        $upload_dir = dirname(__DIR__) . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        foreach ($_FILES['announcement_files']['name'] as $i => $name) {
            $tmp_name = $_FILES['announcement_files']['tmp_name'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $safe_name = time() . '_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $safe_name;
            if (move_uploaded_file($tmp_name, $target)) {
                $uploaded_files[] = 'uploads/' . $safe_name;
            }
        }
    }
    $image_json = $uploaded_files ? json_encode($uploaded_files) : null;

    // Fetch all active, not deleted canteens
    $canteens = $conn->query("SELECT id FROM cantines WHERE active=0 AND del_status=0");
    while ($ct = $canteens->fetch_assoc()) {
        $canteen_id = $ct['id'];
        $stmt = $conn->prepare("INSERT INTO canteen_messages (canteen_id, message, sender, date_sent, image, is_announcement) VALUES (?, ?, ?, NOW(), ?, 1)");
        $stmt->bind_param("isss", $canteen_id, $message, $sender, $image_json);
        $stmt->execute();
        $stmt->close();
    }
    // Just echo a response for AJAX
    echo "success";
    exit;
}
?>
<style>
.messenger-file-label .file-name-preview {
    display: none;
    position: absolute;
    left: 110%;
    top: 50%;
    transform: translateY(-50%);
    background: #fff;
    color: #333;
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 0.95em;
    white-space: nowrap;
    box-shadow: 0 2px 8px #0001;
    z-index: 10;
}
.messenger-file-label:hover .file-name-preview {
    display: block;
}
#messenger-messages {
    scroll-behavior: smooth;
}
</style>