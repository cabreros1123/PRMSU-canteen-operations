// Messenger AJAX for user_cantine messenger.php

document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to latest message on load
    var msgBox = document.querySelector('.messenger-messages');
    if (msgBox) {
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    // AJAX send message
    var form = document.querySelector('.messenger-form-row');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'messenger_send.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    form.reset();
                    // Refresh messages after send
                    fetchMessages();
                }
            };
            xhr.send(formData);
        });
    }

    // Poll for new messages every 2 seconds
    setInterval(fetchMessages, 2000);

    function fetchMessages() {
        var msgBox = document.querySelector('.messenger-messages');
        if (!msgBox) return;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_canteen_messages.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var oldScroll = msgBox.scrollTop + msgBox.clientHeight >= msgBox.scrollHeight - 10;
                msgBox.innerHTML = xhr.responseText;
                // Auto-scroll to bottom if already at bottom
                if (oldScroll) {
                    msgBox.scrollTop = msgBox.scrollHeight;
                }
                // After displaying messages in the chat
                fetch('mark_admin_messages_read.php', {method: 'POST'});
            }
        };
        xhr.send();
    }

    function loadMessages() {
        // ...your AJAX code to fetch and display messages...

        // After displaying messages:
        fetch('mark_admin_messages_read.php', {method: 'POST'});
    }
});
