<?php
session_start();
date_default_timezone_set('Asia/Manila'); // <-- Set timezone before any date/time usage
require_once 'db.php';

$id_cantine = $_SESSION['id_cantine'] ?? 0;

// Fetch available products for this canteen
$products = $conn->query("SELECT * FROM products WHERE cantine_id = $id_cantine");

// Fetch active products for this canteen
$activeProducts = [];
$res = $conn->query("SELECT product_id FROM active_product WHERE cantine_id = $id_cantine AND active = 1");
while ($row = $res->fetch_assoc()) {
    $activeProducts[$row['product_id']] = true;
}

// Handle form submission to save active products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_products'])) {
    $ids = explode(',', $_POST['selected_products']);
    foreach ($ids as $prodId) {
        $prodId = intval($prodId);
        if ($prodId > 0) {
            // Fetch lifespan for this product
            $prodRes = $conn->query("SELECT lifespan_days, lifespan_hours FROM products WHERE id = $prodId");
            $prodRow = $prodRes->fetch_assoc();
            $days = intval($prodRow['lifespan_days']);
            $hours = intval($prodRow['lifespan_hours']);

            // Build the interval string
            $interval = '';
            if ($days > 0) $interval .= "+$days days ";
            if ($hours > 0) $interval .= "+$hours hours";
            if ($interval === '') $interval = '+0 hours'; // fallback if both are zero

            // Calculate date_end: now + lifespan (uses Asia/Manila timezone)
            $date_end = date('Y-m-d H:i:s', strtotime($interval));
            $date_added = date('Y-m-d H:i:s'); // Save current time

            // Save to active_product (set date_end and date_added)
            $conn->query("INSERT INTO active_product (product_id, cantine_id, active, date_end, date_added) VALUES ($prodId, $id_cantine, 1, '$date_end', '$date_added')
                ON DUPLICATE KEY UPDATE active = 1, date_end = '$date_end', date_added = '$date_added'");
        }
    }
    header("Location: act_product.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Active Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .product-img { width: 40px; height: 40px; object-fit: cover; }
        .table td, .table th { vertical-align: middle; }
        .highlight { background: #d4ffd4; }
        @media (max-width: 767px) {
            .container-fluid { padding: 0 2px; }
            .row { flex-direction: column; }
            .col-md-6 { width: 100%; max-width: 100%; }
            table { font-size: 0.95em; }
            .product-img { width: 32px; height: 32px; }
            .btn { padding: 6px 10px; font-size: 0.95em; }
            th, td { padding: 6px 4px !important; }
            .form-control { font-size: 1em; }
        }
        .active-badge { font-size: 0.95em; padding: 2px 8px; border-radius: 12px; }
        .active-yes { background: #28a745; color: #fff; }
        .active-no { background: #ccc; color: #333; }
        .swal2-popup { font-size: 1.1em !important; }
        .table-responsive { overflow-x: auto; }
    </style>
    <!-- SweetAlert2 for warning dialog -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php require_once "header.php"; ?>
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Selected Products (Left) -->
            <div class="col-md-6 mb-3">
                <h4>Selected Products to Active</h4>
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="selectedTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Lifespan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="selectedProductsBody">
                                <!-- JS will fill this -->
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="selected_products" id="selectedProductsInput">
                    <button type="submit" class="btn btn-success w-100 mt-2">Save Active Products</button>
                </form>
            </div>
            <!-- Available Products (Right) -->
            <div class="col-md-6 mb-3">
                <h4>Products</h4>
                <input type="text" id="searchInput" class="form-control mb-2" placeholder="Search for products...">
                <div class="table-responsive">
                    <table class="table table-bordered" id="availableTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Lifespan</th>
                                <th>Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $products->fetch_assoc()): 
                            $isActive = !empty($activeProducts[$row['id']]);
                        ?>
                            <tr data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['description']) ?>" data-img="<?= htmlspecialchars($row['image']) ?>" data-days="<?= $row['lifespan_days'] ?>" data-hours="<?= $row['lifespan_hours'] ?>" data-active="<?= $isActive ? '1' : '0' ?>">
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <img src="/POS-PHP/<?= htmlspecialchars($row['image'] ?: 'views/img/products/default/anonymous.png') ?>" class="product-img">
                                </td>
                                <td><?= $row['lifespan_days'] ?> day(s), <?= $row['lifespan_hours'] ?> hour(s)</td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="active-badge active-yes">Active</span>
                                    <?php else: ?>
                                        <span class="active-badge active-no">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><button type="button" class="btn btn-primary btn-sm add-btn">+</button></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<script>
const selectedProducts = {};

document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const id = row.dataset.id;
        const isActive = row.dataset.active === '1';
        if (isActive) {
            Swal.fire({
                icon: 'warning',
                title: 'Product is already active!',
                text: 'This product is still active. Do you want to continue?',
                showCancelButton: true,
                confirmButtonText: 'Yes, add anyway',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    addProduct(row, id);
                }
            });
        } else {
            addProduct(row, id);
        }
    });
});

function addProduct(row, id) {
    if (!selectedProducts[id]) {
        selectedProducts[id] = {
            id: id,
            name: row.dataset.name,
            img: row.dataset.img,
            days: row.dataset.days,
            hours: row.dataset.hours
        };
        renderSelected();
    }
}

function renderSelected() {
    const tbody = document.getElementById('selectedProductsBody');
    tbody.innerHTML = '';
    let ids = [];
    for (const id in selectedProducts) {
        const prod = selectedProducts[id];
        ids.push(id);
        tbody.innerHTML += `
            <tr>
                <td>
                    <img src="/POS-PHP/${prod.img || 'views/img/products/default/anonymous.png'}" class="product-img">
                    ${prod.name}
                </td>
                <td>${prod.days} day(s), ${prod.hours} hour(s)</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeSelected('${id}')">Remove</button></td>
            </tr>
        `;
    }
    document.getElementById('selectedProductsInput').value = ids.join(',');
}

function removeSelected(id) {
    delete selectedProducts[id];
    renderSelected();
}

// Optional: filter available products
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#availableTable tbody tr').forEach(row => {
        const name = row.dataset.name.toLowerCase();
        row.style.display = name.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>