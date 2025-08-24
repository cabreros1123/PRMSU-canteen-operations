<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
$cantineName = isset($_SESSION['name']) ? $_SESSION['name'] : 'Unknown Cantine';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="dist/bootstrap.min.css">
    <link rel="icon" type="image/png" href="img/icono-negro.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .header {
            width: 100%;
            background: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            flex: 0 0 auto; /* Allow the logo to take up only the space it needs */
            display: flex;
            align-items: center;
        }

        .logo img {
            max-height: 40px;
            display: block; /* Ensure the image is displayed */
        }

        .navbar-menu {
            flex: 1; /* Allow the menu to take the remaining space */
            display: flex;
            justify-content: flex-start; /* Align menu items to the left */
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 15px; /* Add spacing between menu items */
        }

        .navbar-menu li {
            margin: 10px;
        }

        .navbar-menu li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .navbar-menu li a:hover {
            text-decoration: underline;
        }

        .logout-container {
            display: flex;
            align-items: center;
            gap: 10px; /* Adjust spacing between username and logout button */
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 5px; /* Add spacing between the icon and username */
            color: white;
            font-size: 16px;
        }

        .user-info i {
            font-size: 18px; /* Adjust icon size */
        }

        .logout {
            color: white;
            background-color: #ff7463;
            border-radius: 3px;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
        }

        .logout:hover {
            color: black;
            background-color: red;
        }

        /* Hamburger Menu Button */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .hamburger div {
            width: 25px;
            height: 3px;
            background-color: white;
            margin: 3px 0;
        }

        /* Red dot for notification */
        .notif-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: red;
            border-radius: 50%;
            margin-left: 4px;
            vertical-align: middle;
        }
        .hamburger-notif-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 10px;
            height: 10px;
            background: red;
            border-radius: 50%;
            display: none;
            z-index: 10;
        }
        .hamburger.has-unread .hamburger-notif-dot {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .logo {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }

            .navbar-menu {
                flex-direction: column;
                display: none;
                width: 100%;
                background: #444;
                padding: 10px 0;
            }

            .navbar-menu.show {
                display: flex;
            }

            .navbar-menu li a:hover {
                background-color: #555;
                color: #fff;
                padding: 5px;
                border-radius: 3px;
            }

            .logout-container {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }

            .hamburger {
                display: flex;
            }
        }
    </style>
</head>
<header class="main-header header">
    <!-- Logo on the Left -->
    <a href="home.php" class="logo">
        <img class="img-responsive" src="/POS-PHP/views/img/template/logo-blanco-lineal.png">
    </a>

    <!-- Hamburger Menu Button -->
    <div class="hamburger" id="hamburgerMenu" onclick="toggleMenu()" style="position:relative;">
        <div></div>
        <div></div>
        <div></div>
        <span class="hamburger-notif-dot" id="hamburgerNotifDot"></span>
    </div>

    <!-- Navigation Menu -->
    <ul class="navbar-menu">
        <li><a href="home.php"><i class="fa fa-home"></i> <span>Home</span></a></li>
        <!--
        <li><a href="product.php"><i class="fa fa-product-hunt"></i> <span>Products</span></a></li>
        <li><a href="act_product.php"><i class="fa fa-product-hunt"></i> <span>Add Active Products</span></a></li>
        <li><a href="view_act_product.php"><i class="fa fa-product-hunt"></i> <span>View Active Products</span></a></li> -->
        <li><a href="add_bills.php"><i class="fa fa-circle"></i> <span>Add Bills</span></a></li>
        <li><a href="verified_payments.php"><i class="fa fa-check"></i> <span>Verified Payments</span></a></li>
        <li><a href="messenger.php" id="messengerLink">
            <i class="fa fa-comments"></i> <span>Messenger</span>
            <span id="messengerNotifDot" class="notif-dot" style="display:none;"></span>
          </a>
        </li>
        <li><a href="#" onclick="showEditCanteenProfileModal(<?php echo (int)$_SESSION['id_cantine']; ?>);return false;"><i class="fa fa-user-circle"></i> <span>Profile</span></a></li>
    </ul>

    <!-- User Info and Logout Button -->
    <div class="logout-container">
        <!-- User Info -->
        <div class="user-info">
            <i class="fa fa-user"></i> 
            <?php if (isset($_SESSION['id_cantine']) && isset($_SESSION['cantine_name'])): ?>
                <span style="cursor:pointer;text-decoration:underline;" onclick="showCanteenProfileModal(<?php echo (int)$_SESSION['id_cantine']; ?>)">
                    <?php echo htmlspecialchars($_SESSION["cantine_name"]); ?>
                </span>
            <?php else: ?>
                <span>Unknown User</span>
            <?php endif; ?>
        </div>
        <!-- Logout Button -->
        <a class="logout" href="logout.php" onclick="return confirmLogout();">Logout</a>
    </div>
</header>

<?php
if (isset($_SESSION['id_cantine'])) {
    date_default_timezone_set('Asia/Manila');
    require_once 'db.php';
    $id_cantine = $_SESSION['id_cantine'];
    $sql = "SELECT p.description, p.image, ap.date_end
            FROM active_product ap
            JOIN products p ON ap.product_id = p.id
            WHERE ap.cantine_id = $id_cantine AND ap.active = 1 AND ap.del_status = 1";
    $res = $conn->query($sql);
    $expiredProducts = [];
    while ($row = $res->fetch_assoc()) {
        $expiredProducts[] = [
            'description' => $row['description'],
            'image' => $row['image'],
            'date_end' => $row['date_end']
        ];
    }
}
?>
<?php if (!empty($expiredProducts)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if ("Notification" in window && Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    // Use sessionStorage to persist notified products only for this browser session
    function getNotifiedProducts() {
        try {
            return JSON.parse(sessionStorage.getItem('notifiedProducts') || '{}');
        } catch (e) {
            return {};
        }
    }
    function setNotifiedProducts(obj) {
        sessionStorage.setItem('notifiedProducts', JSON.stringify(obj));
    }

    function checkExpirations() {
        let notified = getNotifiedProducts();
        <?php foreach ($expiredProducts as $i => $prod): ?>
        (function() {
            const expire = <?= strtotime($prod['date_end']) ?> * 1000;
            const now = Date.now();
            let diff = Math.floor((expire - now) / 1000);
            const title = <?= json_encode($prod['description']) ?>;
            const imgSrc = "/POS-PHP/<?= htmlspecialchars($prod['image'] ?: 'views/img/products/default/anonymous.png') ?>";
            // Only notify if expired within the last 5 hours
            const expiredAgo = Math.abs(Math.floor((now - expire) / 1000));
            if (diff <= 0 && expiredAgo <= 18000 && !notified[title]) {
                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("Product Expired", {
                        body: `The product "${title}" has expired.`,
                        icon: imgSrc
                    });
                    notified[title] = true;
                    setNotifiedProducts(notified);
                }
            }
        })();
        <?php endforeach; ?>
    }
    setInterval(checkExpirations, 60000); // check every minute
    checkExpirations();
});
</script>
<?php endif; ?>

<!-- JavaScript -->
<script>
    function toggleMenu() {
        var menu = document.querySelector('.navbar-menu');
        if (menu) menu.classList.toggle('show');
    }

    // Canteen Profile Modal
    function showCanteenProfileModal(id) {
        var modal = document.getElementById('canteenProfileModal');
        var body = document.getElementById('canteenProfileBody');
        body.innerHTML = '<div style="padding:40px 0;color:#888;">Loading...</div>';
        modal.style.display = 'flex';
        fetch('canteen_profile_view.php?id=' + id)
            .then(r => r.text())
            .then(html => { body.innerHTML = html; });
    }
    function closeCanteenProfileModal() {
        document.getElementById('canteenProfileModal').style.display = 'none';
    }

    // Clear all storage data
    function clearAllStorage() {
        sessionStorage.clear();
        localStorage.clear();
    }

    // Show Edit Canteen Profile Modal
    function showEditCanteenProfileModal(id) {
        var modal = document.getElementById('editCanteenProfileModal');
        var form = document.getElementById('editCanteenProfileForm');
        // Reset highlights
        Array.from(form.elements).forEach(function(el){
            el.style.backgroundColor = '';
        });
        // Load info via AJAX
        fetch('canteen_profile_api.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data && !data.error) {
                    form.id.value = data.id;
                    form.stall_no.value = data.stall_no;
                    form.name.value = data.name;
                    form.email.value = data.email;
                    form.phone.value = data.phone;
                    form.owner.value = data.owner;
                    form.username.value = data.username;
                    form.password.value = data.password;
                    // Store original values for change detection
                    form.name.setAttribute('data-orig', data.name);
                    form.email.setAttribute('data-orig', data.email);
                    form.phone.setAttribute('data-orig', data.phone);
                    form.owner.setAttribute('data-orig', data.owner);
                    form.username.setAttribute('data-orig', data.username);
                    form.password.setAttribute('data-orig', data.password);
                }
            });
        modal.style.display = 'flex';
    }
    function closeEditCanteenProfileModal() {
        document.getElementById('editCanteenProfileModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('editCanteenProfileForm');
        if (form) {
            ['name','email','phone','owner','username','password'].forEach(function(field){
                var input = form[field];
                if (input) {
                    input.addEventListener('input', function() {
                        if (input.value !== input.getAttribute('data-orig')) {
                            input.style.backgroundColor = 'yellow';
                        } else {
                            input.style.backgroundColor = '';
                        }
                    });
                }
            });
        }
    });

    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        var pwdInput = document.getElementById('editProfilePassword');
        var toggle = document.getElementById('togglePassword');
        if (pwdInput && toggle) {
            toggle.addEventListener('click', function() {
                if (pwdInput.type === "password") {
                    pwdInput.type = "text";
                    toggle.innerHTML = '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
                } else {
                    pwdInput.type = "password";
                    toggle.innerHTML = '<i class="fa fa-eye" aria-hidden="true"></i>';
                }
            });
        }
    });

    function confirmLogout() {
        clearAllStorage();
        return confirm("Are you sure you want to logout?");
    }
</script>

<!-- Canteen Profile Modal -->
<div id="canteenProfileModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
  <div id="canteenProfileContent" style="background:#fff;padding:32px 24px;border-radius:12px;max-width:400px;width:90vw;box-shadow:0 2px 16px #0008;position:relative;">
    <span onclick="closeCanteenProfileModal()" style="position:absolute;top:18px;right:24px;font-size:2em;color:#888;cursor:pointer;">&times;</span>
    <div id="canteenProfileBody" style="min-height:200px;text-align:center;">
      <!-- Profile content will be loaded here -->
      <div style="padding:40px 0;color:#888;">Loading...</div>
    </div>
  </div>
</div>

<!-- Edit Canteen Profile Modal -->
<div id="editCanteenProfileModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
  <div style="background:#fff;padding:32px 24px;border-radius:12px;max-width:400px;width:90vw;box-shadow:0 2px 16px #0008;position:relative;">
    <span onclick="closeEditCanteenProfileModal()" style="position:absolute;top:18px;right:24px;font-size:2em;color:#888;cursor:pointer;">&times;</span>
    <form id="editCanteenProfileForm" method="post" action="edit_canteen_profile.php">
      <input type="hidden" name="id">
      <div class="form-group">
        <label>Stall No.</label>
        <input type="text" name="stall_no" class="form-control" readonly>
      </div>
      <div class="form-group">
        <label>Canteen Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Owner</label>
        <input type="text" name="owner" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="form-group" style="position:relative;">
        <label>Password</label>
        <input type="password" name="password" id="editProfilePassword" class="form-control" required style="padding-right:36px;">
        <span id="togglePassword" style="position:absolute;top:36px;right:12px;cursor:pointer;color:#888;font-size:1.2em;">
          <i class="fa fa-eye" aria-hidden="true"></i>
        </span>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:10px;">Save Changes</button>
    </form>
  </div>
</div>

<script>
(function(){
    // Don't show notification if on messenger.php
    if (window.location.pathname.includes('messenger.php')) return;

    // Only poll if logged in
    <?php if (isset($_SESSION['id_cantine'])): ?>
    let lastMsgId = localStorage.getItem('lastAdminMsgId') || 0;

    function pollAdminMessages() {
        fetch('poll_admin_message.php')
            .then(r => r.json())
            .then(data => {
                if (data && data.id && data.id > lastMsgId) {
                    // Only notify if message is from admin and not seen yet
                    if (data.sender === 'admin') {
                        if ("Notification" in window) {
                            if (Notification.permission === "granted") {
                                let notif = new Notification("New Message from Admin", {
                                    body: data.message,
                                    icon: "/POS-PHP/img/icono-negro.png"
                                });
                                notif.onclick = function(event) {
                                    event.preventDefault();
                                    window.location.href = 'messenger.php';
                                };
                            } else if (Notification.permission !== "denied") {
                                Notification.requestPermission();
                            }
                        }
                        lastMsgId = data.id;
                        localStorage.setItem('lastAdminMsgId', data.id);
                    }
                }
            });
    }
    setInterval(pollAdminMessages, 5000); // Poll every 5 seconds
    pollAdminMessages();
    <?php endif; ?>
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('mark_admin_messages_read.php', {method: 'POST'});
    // Optionally, update localStorage so notification doesn't show again
    fetch('poll_admin_message.php')
        .then(r => r.json())
        .then(data => {
            if (data && data.id) {
                localStorage.setItem('lastAdminMsgId', data.id);
            }
        });
});
</script>
<script>
(function(){
    // Don't show notification dot if on messenger.php
    if (window.location.pathname.includes('messenger.php')) return;

    <?php if (isset($_SESSION['id_cantine'])): ?>
    function checkUnreadAdminMessages() {
        fetch('poll_admin_message.php')
            .then(r => r.json())
            .then(data => {
                var dot = document.getElementById('messengerNotifDot');
                var hamburgerDot = document.getElementById('hamburgerNotifDot');
                var hamburger = document.getElementById('hamburgerMenu');
                if (data && data.id && data.sender === 'admin') {
                    // Show red dot
                    if (dot) dot.style.display = '';
                    if (hamburgerDot && hamburger) {
                        hamburger.classList.add('has-unread');
                        hamburgerDot.style.display = 'block';
                    }
                } else {
                    // Hide red dot
                    if (dot) dot.style.display = 'none';
                    if (hamburgerDot && hamburger) {
                        hamburger.classList.remove('has-unread');
                        hamburgerDot.style.display = 'none';
                    }
                }
            });
    }
    setInterval(checkUnreadAdminMessages, 5000); // Check every 5 seconds
    checkUnreadAdminMessages();
    <?php endif; ?>
})();
</script>
</html>