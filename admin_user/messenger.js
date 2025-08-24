function showMessengerImgModal(src) {
    var modal = document.getElementById('messenger-img-modal');
    var img = document.getElementById('messenger-img-modal-img');
    img.src = src;
    modal.style.display = 'flex';
}
function closeMessengerImgModal() {
    document.getElementById('messenger-img-modal').style.display = 'none';
}

// Image modal closes when clicking outside the image
document.addEventListener('DOMContentLoaded', function() {
    var imgModal = document.getElementById('messenger-img-modal');
    if (imgModal) {
        imgModal.addEventListener('click', function(e){
            if(e.target === this) closeMessengerImgModal();
        });
    }
});

// Messenger modal open/close logic
var openBtn = document.getElementById('openMessengerBtn');
var closeBtn = document.getElementById('closeMessengerBtn');
var modal = document.getElementById('messengerModal');
if (openBtn && modal) {
    openBtn.onclick = function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(function() {
            var input = modal.querySelector('.messenger-input');
            if (input) input.focus();
            var msgBox = modal.querySelector('.messenger-messages');
            if (msgBox) msgBox.scrollTop = msgBox.scrollHeight;
        }, 100);
    };
}
if (closeBtn && modal) {
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };
}
// Close modal when clicking outside the modal content
window.addEventListener('mousedown', function(e) {
    if (modal && modal.style.display === 'flex' && e.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Messenger form AJAX send
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
        form.reset();
        document.getElementById('messenger-file-preview').innerHTML = '';
        document.getElementById('messenger-file-preview').style.display = 'none';
        // ...other logic...
    });
};

// Announcement form AJAX send
document.getElementById('announcementForm').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);

    fetch('messenger_announcement.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        closeAnnouncementModal();
        form.reset();
        document.getElementById('messenger-file-preview').innerHTML = '';
        document.getElementById('messenger-file-preview').style.display = 'none';
        alert('Announcement sent!');
    });
};