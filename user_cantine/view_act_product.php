<?php
session_start();
date_default_timezone_set('Asia/Manila'); // <-- Set to your local timezone
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_id'])) {
        $removeId = intval($_POST['remove_id']);
        $now = date('Y-m-d H:i:s');
        // Get the expiration date for this product
        $result = $conn->query("SELECT date_end FROM active_product WHERE id = $removeId");
        $row = $result->fetch_assoc();
        if ($row && $now < $row['date_end']) {
            // Not expired yet, set remove_status = 1 and true_date_end to now
            $conn->query("UPDATE active_product SET del_status = 0, active = 0, remove_status = 1, true_date_end = '$now' WHERE id = $removeId");
        } else {
            // Already expired, set remove_status = 0 and true_date_end to now
            $conn->query("UPDATE active_product SET del_status = 0, active = 0, remove_status = 0, true_date_end = '$now' WHERE id = $removeId");
        }
        header("Location: view_act_product.php");
        exit;
    }
    if (isset($_POST['remove_all'])) {
        $id_cantine = $_SESSION['id_cantine'] ?? 0;
        $now = date('Y-m-d H:i:s');
        // Set remove_status and true_date_end for all
        $conn->query("UPDATE active_product SET del_status = 0, active = 0, remove_status = CASE WHEN '$now' < date_end THEN 1 ELSE 0 END, true_date_end = '$now' WHERE cantine_id = $id_cantine AND active = 1 AND del_status = 1");
        header("Location: view_act_product.php");
        exit;
    }
}

$id_cantine = $_SESSION['id_cantine'] ?? 0;

// Add p.image to your SELECT
$sql = "SELECT p.description, p.image, ap.date_added, p.lifespan_days, p.lifespan_hours, ap.date_end, ap.id
        FROM active_product ap
        JOIN products p ON ap.product_id = p.id
        WHERE ap.cantine_id = $id_cantine AND ap.active = 1 AND ap.del_status = 1";
$res = $conn->query($sql);
$products = [];
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Active Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f7f7f7; }
        .app-header { background: #0096FF; color: #fff; padding: 16px; text-align: center; font-size: 1.3em; font-weight: bold; letter-spacing: 1px; }
        .product-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 18px; padding: 18px 16px; display: flex; flex-direction: row; gap: 16px; align-items: center; }
        .product-details { flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .product-title { font-size: 1.2em; font-weight: 600; color: #0096FF; }
        .product-info { font-size: 1em; color: #333; }
        .countdown { font-weight: bold; font-size: 1.1em; color: #d9534f; }
        .expired { color: #888 !important; }
        .product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        @media (max-width: 767px) {
            .container { padding: 0 2px; }
            .product-card { flex-direction: column; align-items: flex-start; }
            .product-img { width: 60px; height: 60px; margin-bottom: 8px; }
            .app-header { font-size: 1.1em; padding: 12px; }
        }
    </style>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="app-header">
        Active Products for <?php echo htmlspecialchars($_SESSION['cantine_name']); ?>
    </div>
    <div class="container mt-3 mb-4">
        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center mt-4">No active products.</div>
        <?php endif; ?>
        <?php foreach ($products as $i => $row): ?>
            <div class="product-card" id="product-card-<?= $i ?>">
                <img src="/POS-PHP/<?= htmlspecialchars($row['image'] ?: 'views/img/products/default/anonymous.png') ?>" class="product-img" alt="Product Image">
                <div class="product-details">
                    <div class="product-title"><?= htmlspecialchars($row['description']) ?></div>
                    <div class="product-info">
                        <strong>Date Added:</strong>
                        <?= (new DateTime($row['date_added']))->format('Y-m-d h:i:s A') ?>
                    </div>
                    <div class="product-info">
                        <strong>Lifespan:</strong>
                        <?= $row['lifespan_days'] ?> day(s), <?= $row['lifespan_hours'] ?> hour(s)
                    </div>
                    <div class="product-info">
                        <strong>Expiration Date:</strong>
                        <?= (new DateTime($row['date_end']))->format('Y-m-d h:i:s A') ?>
                    </div>
                    <div class="product-info">
                        <strong>Time Remaining:</strong>
                        <span class="countdown" data-expire="<?= strtotime($row['date_end']) ?>" data-title="<?= htmlspecialchars($row['description']) ?>" id="countdown-<?= $i ?>"></span>
                    </div>
                    <form method="post" style="margin-top:8px;" onsubmit="return confirm('Are you sure you want to remove this active product?');">
    <input type="hidden" name="remove_id" value="<?= $row['id'] ?>">
    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
</form>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-end mb-2">
    <form method="post" onsubmit="return confirm('Are you sure you want to remove ALL active products?');">
        <input type="hidden" name="remove_all" value="1">
        <button type="submit" class="btn btn-danger btn-sm">Remove All</button>
    </form>
</div>
    </div>
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

        function updateCountdowns() {
            let notified = getNotifiedProducts();
            document.querySelectorAll('.countdown').forEach(function(el) {
                const expire = parseInt(el.getAttribute('data-expire')) * 1000;
                const now = Date.now();
                let diff = Math.floor((expire - now) / 1000);
                const title = el.getAttribute('data-title');
                const card = el.closest('.product-card');
                const imgEl = card.querySelector('.product-img');
                const imgSrc = imgEl ? imgEl.src : "https://cdn-icons-png.flaticon.com/512/565/565547.png";
                if (diff > 0) {
                    const days = Math.floor(diff / 86400);
                    diff %= 86400;
                    const hours = Math.floor(diff / 3600);
                    diff %= 3600;
                    const minutes = Math.floor(diff / 60);
                    const seconds = diff % 60;
                    el.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    el.classList.remove('expired');
                } else {
                    el.textContent = "Expired";
                    el.classList.add('expired');
                    // Only notify if expired within the last 5 hours (18000 seconds) and not already notified in this session
                    const expiredAgo = Math.abs(Math.floor((now - expire) / 1000));
                    if (
                        "Notification" in window &&
                        Notification.permission === "granted" &&
                        !notified[title] &&
                        expiredAgo <= 18000 // 5 hours in seconds
                    ) {
                        new Notification("Product Expired", {
                            body: `The product "${title}" has expired.`,
                            icon: imgSrc
                        });
                        notified[title] = true;
                        setNotifiedProducts(notified);
                    }
                }
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    });
    </script>
</body>
</html>