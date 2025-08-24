function showMessengerImgModal(src) {
    var modal = document.getElementById('messenger-img-modal');
    var img = document.getElementById('messenger-img-modal-img');
    img.src = src;
    modal.style.display = 'flex';
}
function closeMessengerImgModal() {
    document.getElementById('messenger-img-modal').style.display = 'none';
}

// Ensure image modal closes when clicking outside the image
document.addEventListener('DOMContentLoaded', function() {
    var msgBox = document.getElementById('messenger-messages');
    if (msgBox) {
        msgBox.scrollTop = msgBox.scrollHeight;
    }
    var imgModal = document.getElementById('messenger-img-modal');
    if (imgModal) {
        imgModal.addEventListener('click', function(e){
            if(e.target === this) closeMessengerImgModal();
        });
    }

    // Modal open/close logic for blending with sidebar modal
    var openBtn = document.getElementById('openMessengerBtn');
    var closeBtn = document.getElementById('closeMessengerBtn');
    var modal = document.getElementById('messengerModal');
    if (openBtn && modal) {
        openBtn.onclick = function() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // Focus input and scroll to bottom
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
    // Optional: Close modal when clicking outside the modal content
    window.addEventListener('mousedown', function(e) {
        if (modal && modal.style.display === 'flex' && e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
});

function showFileNames(input) {
    var label = input.closest('.messenger-file-label');
    var fileNamesSpan = label.querySelector('.file-names');
    if (input.files.length > 0) {
        var names = Array.from(input.files).map(f => f.name).join(', ');
        fileNamesSpan.textContent = names;
        label.classList.add('has-files');
    } else {
        fileNamesSpan.textContent = '';
        label.classList.remove('has-files');
    }
}