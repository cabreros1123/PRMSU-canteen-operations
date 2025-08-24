<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    background: #18191a;
    color: #e4e6eb;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    width: 100vw;
    /* Remove overflow: hidden; to allow normal page scroll */
}
.center-container {
    min-height: 100vh;
    width: 100vw;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #18191a;
    /* Remove position: fixed; so header is always visible */
    z-index: 1;
}
.messenger-box {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    height: 80vh;
    border-radius: 12px;
    background: #242526;
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    margin: 32px auto 0 auto;
    box-shadow: 0 2px 16px #0008;
    border: none;
    position: relative;
}
.messenger-box h3 {
    color: #e4e6eb;
    background: #242526;
    border-radius: 12px 12px 0 0;
    padding: 18px 24px 8px 24px;
    margin: 0;
    font-size: 1.2em;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 2;
}
.messenger-messages {
    flex: 1 1 auto;
    min-height: 0;
    max-height: 100%;
    overflow-y: auto;
    margin-bottom: 0;
    padding: 18px 18px 0 18px;
    background: linear-gradient(135deg, #f7fafc 60%, #e3f2fd 100%);
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #18191a;
    border-radius: 0 0 0 0;
}
.messenger-message {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.messenger-message.canteen {
    align-items: flex-end;
}
.messenger-bubble {
    background: #3a3b3c;
    color: #e4e6eb;
    padding: 10px 16px;
    border-radius: 18px;
    word-break: break-word;
    margin-bottom: 2px;
    display: inline-block;
    max-width: 80vw;
    min-width: 60px;
    width: auto;
    box-sizing: border-box;
    font-size: 1.05em;
    line-height: 1.4;
    box-shadow: none;
}
.messenger-message.canteen .messenger-bubble {
    background: #0084ff;
    color: #fff;
    border-bottom-right-radius: 6px;
    border-bottom-left-radius: 18px;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
}
.messenger-message.admin .messenger-bubble {
    background: #3a3b3c;
    color: #e4e6eb;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 18px;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
}
.messenger-files {
    margin-top: 4px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.messenger-files a, .messenger-files img {
    display: inline-block;
    margin-top: 4px;
    max-width: 120px;
    max-height: 80px;
    border-radius: 4px;
    vertical-align: top;
}
.messenger-meta {
    font-size: 0.85em;
    color: #b0b3b8;
    margin-top: 2px;
    align-self: flex-end;
    padding-left: 8px;
    padding-right: 8px;
}
.messenger-form-row {
    display: flex;
    gap: 8px;
    align-items: center;
    background: #242526;
    border-radius: 0 0 12px 12px;
    padding: 12px 16px;
    box-shadow: none;
    margin-top: 0;
    border-top: 1px solid #393a3b;
}
.messenger-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px 16px;
    border-radius: 20px;
    background: #3a3b3c;
    color: #e4e6eb;
    font-size: 1em;
    margin-right: 6px;
    transition: background 0.2s;
}
.messenger-input:focus {
    background: #23272b;
}
.messenger-file-label {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-right: 6px;
    color: #0084ff;
    font-size: 1.3em;
    transition: color 0.2s;
}
.messenger-file-label:hover {
    color: #00c853;
}
.messenger-file-input {
    display: none;
}
.messenger-file-label .file-name-preview {
    display: none;
    position: absolute;
    left: 110%;
    top: 50%;
    transform: translateY(-50%);
    background: #242526;
    color: #e4e6eb;
    border: 1px solid #393a3b;
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
.messenger-send-btn {
    background: #0084ff;
    border: none;
    color: #fff;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 1.5em;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}
.messenger-send-btn:hover {
    background: #00c853;
}
@media (max-width: 600px) {
    .center-container {
        align-items: flex-start;
        padding: 0;
    }
    .messenger-box {
        min-width: 0;
        max-width: 100vw;
        width: 100vw;
        height: 100vh;
        margin: 0;
        border-radius: 0;
    }
    .messenger-messages {
        max-height: 70vh;
        padding: 10px 4px 4px 4px;
    }
    .messenger-bubble {
        max-width: 95vw;
        font-size: 1em;
    }
    .messenger-form-row {
        padding: 10px 6px;
        border-radius: 0 0 12px 12px;
    }
}
</style>
<?php
require_once "db.php";
require_once "header.php";
$selected_canteen = isset($_SESSION['id_cantine']) ? intval($_SESSION['id_cantine']) : 0;
?>
<div class="center-container">
    <div style="display: flex; gap: 32px; align-items: flex-start; justify-content: center;">
        <div style="flex: 0 0 420px;">
            <div class="messenger-box">
                <h3 style="margin-bottom:12px;">Messenger to Admin</h3>
                <div class="messenger-messages" id="messengerMessages">
                    <!-- Messages will be loaded here by AJAX -->
                </div>
                <form method="post" enctype="multipart/form-data" class="messenger-form-row">
                    <input type="hidden" name="msg_canteen_id" value="<?= $selected_canteen ?>">
                    <input type="text" name="message" class="messenger-input" placeholder="Type your message..." required>
                    <label class="messenger-file-label" title="Attach files">
                        <span class="material-symbols-rounded">attach_file</span>
                        <input type="file" name="files[]" class="messenger-file-input" multiple style="display:none;" onchange="showFileNamePreview(this)">
                        <span class="file-name-preview" id="fileNamePreview"></span>
                    </label>
                    <button type="submit" name="send_message" class="messenger-send-btn" title="Send">
                        <span class="material-symbols-rounded">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Messenger Image Modal -->
<div id="messengerImgModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;">
    <img id="messengerImgModalImg" src="" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 2px 16px #0008;">
</div>
<script>
function showMessengerImgModal(src) {
    var modal = document.getElementById('messengerImgModal');
    var modalImg = document.getElementById('messengerImgModalImg');
    modalImg.src = src;
    modal.style.display = 'flex';
}
document.getElementById('messengerImgModal').onclick = function() {
    this.style.display = 'none';
}
function showFileNamePreview(input) {
    const preview = input.parentNode.querySelector('.file-name-preview');
    if (input.files && input.files.length > 0) {
        let names = Array.from(input.files).map(f => f.name).join(', ');
        preview.textContent = names;
        preview.style.display = 'block';
    } else {
        preview.textContent = '';
        preview.style.display = 'none';
    }
}
function loadMessages() {
    // ... your AJAX code to load/display messages ...
    fetch('mark_admin_messages_read.php', {method: 'POST'});
}
document.addEventListener('DOMContentLoaded', function() {
    // Mark all admin messages as read
    fetch('mark_admin_messages_read.php', {method: 'POST'})
        .then(() => {
            // Get the latest admin message (even if now read)
            fetch('poll_admin_message.php')
                .then(r => r.json())
                .then(data => {
                    if (data && data.id) {
                        localStorage.setItem('lastAdminMsgId', data.id);
                    }
                });
        });
});
</script>
<script src="messenger.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" /><?php
require_once 'header.php';
?>
<div style="padding:32px 0;text-align:center;">
    <h2>Messenger</h2>
    <p>Use the messenger below to chat with admin or other canteens.</p>
</div>
