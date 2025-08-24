<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["user"])) {
    header("Location: 404.php");
    exit();
}
$adminName = $_SESSION["admin_name"];
$adminUser = $_SESSION["user"];

require_once "notification_logic.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sidebar with Dropdown Menu</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" type="image/x-icon" href="img/icono-negro.ico">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  </head>
  <body>
    <!-- Mobile Sidebar Menu Button -->
    <button class="sidebar-menu-button">
      <span class="material-symbols-rounded">menu</span>
    </button>
    <aside class="sidebar">
      <!-- Sidebar Header -->
      <header class="sidebar-header">
        <a href="home.php" class="header-logo">
          <img src="img/icono-negro.png" alt="CodingNepal" />
        </a>
        <button class="sidebar-toggler">
          <span class="material-symbols-rounded">chevron_left</span>
        </button>
      </header>
      <nav class="sidebar-nav">
        <!-- Primary Top Nav -->
        <ul class="nav-list primary-nav">
          <li class="nav-item">
            <a href="home.php" class="nav-link">
              <span class="material-symbols-rounded">home</span>
              <span class="nav-label">Home</span>
            </a>
            <ul class="dropdown-menu">
              <li class="nav-item"><a class="nav-link dropdown-title">Home</a></li>
            </ul>
          </li>
                    <!-- Canteen List and Contract Terms Links -->
          <li class="nav-item">
            <a href="cantine.php" class="nav-link">
                <span class="material-symbols-rounded">store</span>
                <span class="nav-label">Canteen List</span>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-item"><a class="nav-link dropdown-title">Canteen List</a></li>
            </ul>
          </li>
          <!-- Food Safety Rating Separate Link -->
          <li class="nav-item">
            <a href="food_safety_rating.php" class="nav-link">
              <span class="material-symbols-rounded" style="vertical-align:middle;">health_and_safety</span>
              <span class="nav-label">Food Safety Rating</span>
            </a>
            <ul class="dropdown-menu">
              <li class="nav-item"><a class="nav-link dropdown-title">Food Safety Rating</a></li>
            </ul>
                      <li class="nav-item">
            <a href="food_safety.php" class="nav-link">
                <span class="material-symbols-rounded">description</span>
                <span class="nav-label">Term of the contract</span>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-item"><a class="nav-link dropdown-title">Term of the contract</a></li>
            </ul>
          </li>
          </li>
          <!-- Ledger Dropdown -->
          <li class="nav-item dropdown-container">
            <a href="#" class="nav-link dropdown-toggle">
                <span class="material-symbols-rounded">receipt_long</span>
                <span class="nav-label">LEDGER</span>
                <span class="dropdown-icon material-symbols-rounded">keyboard_arrow_down</span>
            </a>
            <ul class="dropdown-menu">  
              <li><a style="color: #fcfcfcff;">LEDGER: </a></li>
                <li class="nav-item">
                    <a href="cantine_bills.php" class="nav-link dropdown-link">Payment Delivery</a>
                </li>
                <li class="nav-item">
                    <a href="bills_payments.php" class="nav-link dropdown-link">Verified Payments Monitoring</a>
                </li>
            </ul>
          </li>
                    <!-- Messages Report Link -->
          <li class="nav-item">
            <a href="report_messages.php" class="nav-link">
              <span class="material-symbols-rounded">chat</span>
              <span class="nav-label">Messages Report</span>
            </a>
            <ul class="dropdown-menu">
              <li class="nav-item"><a class="nav-link dropdown-title">Messages Report</a></li>
            </ul>
          </li>
          <!-- Overall Report Link -->
          <li class="nav-item">
            <a href="overall_report.php" class="nav-link">
                <span class="material-symbols-rounded">bar_chart</span>
                <span class="nav-label">Overall Report</span>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-item"><a class="nav-link dropdown-title">Overall Report</a></li>
            </ul>
          </li>


        </ul>
        <!-- Secondary Bottom Nav -->
        <ul class="nav-list secondary-nav">
          <li class="nav-item">
            <a href="support.php" class="nav-link">
              <span class="material-symbols-rounded">help</span>
              <span class="nav-label">Support</span>
            </a>
            <ul class="dropdown-menu">
              <li class="nav-item"><a class="nav-link dropdown-title">Support</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link" onclick="return confirmLogout();">
              <span class="material-symbols-rounded">logout</span>  
              <span class="nav-label">Sign Out</span>
            </a>
            <ul class="dropdown-menu">
              <li class="nav-item"><a class="nav-link dropdown-title">logout</a></li>
            </ul>
          </li>
          <!-- Admin Profile Nav Item -->
          <li class="nav-item">
            <a href="#" class="nav-link" onclick="showAdminProfileModal('<?php echo htmlspecialchars($adminUser); ?>')">
                <span class="material-symbols-rounded">account_circle</span>
                <span class="nav-label"><?php echo htmlspecialchars($adminName); ?></span>
            </a>
            <ul class="dropdown-menu">
              <a class="nav-link dropdown-title"><?php echo htmlspecialchars($adminName); ?></a>
            </ul>
          </li>
        </ul>
      </nav>
    </aside>
    <!-- Sign Out Confirmation Modal -->
    <div id="signOutModal" class="modal logout-modal">
      <div class="logout-modal-content">
        <div class="logout-modal-title">
          <span class="material-symbols-rounded" style="font-size:2em;color:#d32f2f;margin-right:8px;">logout</span>
          <b>Sign Out</b>
        </div>
        <div style="margin:18px 0 24px 0;color:#444;">Are you sure you want to sign out?</div>
        <div class="logout-modal-buttons">
          <button id="confirmSignOut" class="btn btn-danger">Yes, Sign Out</button>
          <button id="cancelSignOut" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>
    <!-- Admin Profile Modal -->
    <div id="adminProfileModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
      <div id="adminProfileContent" style="background:#fff;padding:32px 24px;border-radius:12px;max-width:400px;width:90vw;box-shadow:0 2px 16px #0008;position:relative;">
        <span onclick="closeAdminProfileModal()" style="position:absolute;top:18px;right:24px;font-size:2em;color:#888;cursor:pointer;">&times;</span>
        <div id="adminProfileBody" style="min-height:200px;text-align:center;">
          <div style="padding:40px 0;color:#888;">Loading...</div>
        </div>
      </div>
    </div>
  <?php if (basename($_SERVER['PHP_SELF']) !== 'edit_canteen_obligations.php' && basename($_SERVER['PHP_SELF']) !== 'support.php'): ?>
        <!-- Messenger Floating Button -->
        <button id="openMessengerBtn" style="
            position: fixed;
            bottom: 32px; /* slightly higher for easier click */
            right: 32px;
            z-index: 100010; /* higher than modal */
            background: #222;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 64px;
            height: 64px;
            box-shadow: 0 2px 16px #0008;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4em;
            cursor: pointer;
            transition: background 0.2s;
            pointer-events: auto;
        ">
            <span class="material-symbols-rounded">campaign</span>
        </button>

        <!-- Messenger Modal -->
        <div id="messengerModal" style="
            display: none;
            position: fixed;
            bottom: 100px; /* above the button */
            right: 32px;
            width: 500px;
            max-width: 98vw;
            height: auto;
            min-height: 300px;
            max-height: 90vh;
            background: #fff;
            border-radius: 18px 18px 12px 12px;
            box-shadow: 0 4px 32px #0004;
            z-index: 100000;
            flex-direction: column;
            overflow: visible;
        ">
            <!-- Messenger Modal Header -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px 8px 18px;background:#f7fafc;border-radius:18px 18px 0 0;">
                <span style="font-weight:bold;font-size:1.1em;color:#1976d2;">
                    Messenger
                </span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <button type="button" class="btn btn-warning" onclick="openAnnouncementModal()" style="margin-bottom:0;">
                        <span class="material-symbols-rounded" style="vertical-align:middle;">campaign</span>
                        Send Announcement
                    </button>
                    <span id="closeMessengerBtn" style="font-size:2em;color:#888;cursor:pointer;">&times;</span>
                </div>
            </div>
            <div style="flex:1;overflow-y:auto;padding:0 0 0 0;">
                <?php
                $selected_canteen = 0;
                require_once "messenger_widget.php";
                ?>
            </div>
        </div>
        <script>
        // Messenger JS functions
        document.getElementById('openMessengerBtn').onclick = function() {
            document.getElementById('messengerModal').style.display = 'flex';
        }
        document.getElementById('closeMessengerBtn').onclick = function() {
            document.getElementById('messengerModal').style.display = 'none';
        }
        // Optional: Close modal when clicking outside
        window.addEventListener('click', function(e) {
            var modal = document.getElementById('messengerModal');
            if (modal.style.display === 'flex' && !modal.contains(e.target) && e.target.id !== 'openMessengerBtn') {
                modal.style.display = 'none';
            }
        });
        function openMessengerModalWithCanteen(canteenId) {
    document.getElementById('openMessengerBtn').click();
    setTimeout(function() {
        let box = document.querySelector('.canteen-softbox[data-canteen-id="'+canteenId+'"]');
        if (box) box.click();
    }, 400);
}
        </script>
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) !== 'support.php'): ?>
<div id="notifBellBox" style="position:fixed;top:32px;right:38px;z-index:11000;">
    <div style="position:relative;">
        <button onclick="toggleNotifDropdown()" style="background:#fff;border:none;box-shadow:0 2px 8px #0001;border-radius:50%;width:48px;height:48px;cursor:pointer;outline:none;position:relative;">
            <span style="font-size:2rem;color:#1976d2;" class="material-icons">&#128276;</span>
            <?php if ($notif_count > 0): ?>
                <span id="notifBadge" style="
                    position:absolute;top:7px;right:7px;
                    background:#e53935;color:#fff;
                    border-radius:50%;font-size:0.95em;
                    padding:2px 7px;font-weight:bold;
                    box-shadow:0 1px 4px #e5393533;
                "><?= $notif_count ?></span>
            <?php endif; ?>
        </button>
        <div id="notifDropdown" style="
            display:none;
            position:absolute;
            top:56px;
            right:0;
            width:320px;
            max-width:95vw;
            max-height:70vh;
            overflow-y:auto;
            background:#fff;
            border-radius:10px;
            box-shadow:0 8px 32px rgba(0,0,0,0.18);
            padding:0 0 8px 0;
            z-index:11001;
        ">
            <div style="padding:14px 18px;border-bottom:1px solid #eee;font-weight:bold;color:#1976d2;">
                Notifications
            </div>
            <?php if ($notif_count == 0): ?>
                <div style="padding:18px;text-align:center;color:#888;">No urgent missing bills.</div>
            <?php else: ?>
                <?php foreach ($notified_cantines as $row): ?>
                    <div style="padding:12px 18px;border-bottom:1px solid #f3f3f3;">
                        <div style="font-weight:600;"><?= htmlspecialchars($row['canteen']) ?></div>
                        <div style="font-size:0.97em;color:#555;">Owner: <?= htmlspecialchars($row['owner']) ?></div>
                        <?php foreach ($row['notify_types'] as $nt): ?>
                            <div style="margin:6px 0 0 0;">
                                <span style="background:#e53935;color:#fff;border-radius:8px;padding:2px 8px;font-size:0.95em;font-weight:600;">
                                    <?= $nt['count'] ?> missing <?= htmlspecialchars($nt['type']) ?> bills
                                </span>
                            </div>
                        <?php endforeach; ?>
                        <?php
                        $missing_for_modal = ['Rental'=>[], 'Electric'=>[], 'Water'=>[]];
                        foreach ($row['notify_types'] as $nt) {
                            $missing_for_modal[$nt['type']] = $nt['months'];
                        }
                        ?>
                        <button 
                            class="btn btn-primary btn-sm"
                            style="margin-top:8px;"
                            onclick='showMissingBillsModal(
                                <?= json_encode($row['canteen']) ?>, 
                                <?= json_encode($missing_for_modal) ?>
                            )'
                        >View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
    <script src="js/script.js"></script>
    <script>
function toggleNotifDropdown() {
    var dd = document.getElementById('notifDropdown');
    dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
    if (dd.style.display === 'block') {
        setTimeout(() => {
            document.addEventListener('click', notifOutsideClick);
        }, 10);
    }
}
function notifOutsideClick(e) {
    if (!document.getElementById('notifBellBox').contains(e.target)) {
        document.getElementById('notifDropdown').style.display = 'none';
        document.removeEventListener('click', notifOutsideClick);
    }
}
function showMissingBillsModal(canteen, missing) {
    let html = `<h3 style="margin-top:0;">Missing Bills for <span style="color:#1976d2;">${canteen}</span></h3>`;
    let hasAny = false;
    ['Rental', 'Electric', 'Water'].forEach(type => {
        if (missing[type] && missing[type].length > 0) {
            hasAny = true;
            html += `<div style="margin-top:18px;margin-bottom:6px;font-weight:bold;color:#1976d2;">For ${type}:</div>`;
            html += `<ul style="margin:0 0 0 18px;padding:0;color:#c0392b;">`;
            missing[type].forEach(month => {
                html += `<li>${month}</li>`;
            });
            html += `</ul>`;
        }
    });
    if (!hasAny) {
        html += `<div style="margin-top:12px;font-size:1.1em;color:#43a047;">No missing bills!</div>`;
    }
    // You need a modal container in your layout for this to work
    document.getElementById('missingBillsModalContent').innerHTML = html;
    document.getElementById('missingBillsModal').style.display = 'flex';
}
function closeMissingBillsModal() {
    document.getElementById('missingBillsModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    var notifCount = <?= json_encode($notif_count) ?>;
    // Only notify if not already shown in this session
    if (notifCount > 0 && "Notification" in window && !sessionStorage.getItem('billsNotifShown')) {
        if (Notification.permission === "granted") {
            sendBillsNotification();
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(function(permission) {
                if (permission === "granted") {
                    sendBillsNotification();
                }
            });
        }
    }
    function sendBillsNotification() {
        var notifMsg = <?= json_encode($notif_message_str) ?>;
        var notification = new Notification("Missing Bills Alert", {
            body: notifMsg || "There are canteens with missing bills. Click to review.",
            icon: "img/icono-negro.png"
        });
        notification.onclick = function() {
            window.focus();
            var bell = document.getElementById('notifBellBox');
            if (bell) bell.scrollIntoView({behavior: "smooth"});
        };
        // Mark as shown for this session
        sessionStorage.setItem('billsNotifShown', '1');
    }
});
    </script>
    <!-- Missing Bills Modal -->
<div id="missingBillsModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:10000;align-items:center;justify-content:center;">
    <div style="
        background:#fff;
        width:95vw;
        max-width:400px;
        max-height:80vh;
        overflow-y:auto;
        border-radius:10px;
        box-shadow:0 8px 32px rgba(0,0,0,0.18);
        padding:24px 12px 18px 12px;
        position:relative;
        margin:0 auto;
        ">
        <span onclick="closeMissingBillsModal()" style="position:absolute;top:10px;right:18px;font-size:1.6rem;cursor:pointer;color:#888;">&times;</span>
        <div id="missingBillsModalContent"></div>
    </div>
</div>
<script>
(function(){
    // Store the last message id for each canteen
    let lastChatMsgId = localStorage.getItem('lastChatMsgId') ? JSON.parse(localStorage.getItem('lastChatMsgId')) : {};

    // Request notification permission on load
    if ("Notification" in window && Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    // Poll for new chat messages for all canteens
    function pollAllCanteenMessages() {
        fetch('messenger_messages.php?all=1')
            .then(res => res.json())
            .then(data => {
                if (data && Array.isArray(data.canteens)) {
                    data.canteens.forEach(function(ct){
                        let prevId = lastChatMsgId[ct.id] || 0;
                        // Only notify if:
                        // - new message
                        // - not from admin
                        // - not currently viewing this canteen in messenger
                        if (
                            ct.last_id > prevId &&
                            ct.last_sender !== 'admin' &&
                            String(window.currentMessengerCanteenId || '') !== String(ct.id)
                        ) {
                            showCanteenNotification(ct.name, ct.last_message, ct.id);
                            lastChatMsgId[ct.id] = ct.last_id;
                        }
                    });
                    localStorage.setItem('lastChatMsgId', JSON.stringify(lastChatMsgId));
                }
            });
    }
    setInterval(pollAllCanteenMessages, 3000);

    function showCanteenNotification(canteenName, message, canteenId) {
        if ("Notification" in window && Notification.permission === "granted") {
            const notification = new Notification("New message from " + canteenName, {
                body: message,
                icon: "/POS-PHP/img/icono-negro.png"
            });
            notification.onclick = function() {
                window.focus();
                // Optionally, open the messenger modal and select the canteen
                if (typeof openMessengerModalWithCanteen === 'function') {
                    openMessengerModalWithCanteen(canteenId);
                }
            };
        }
    }
})();
</script>
<script>
window.currentMessengerCanteenId = null;
function confirmLogout() {
    // If you have a clearAllStorage function, call it here
    if (typeof clearAllStorage === "function") clearAllStorage();
    return confirm("Are you sure you want to logout?");
}
</script>
  </body>
</html>