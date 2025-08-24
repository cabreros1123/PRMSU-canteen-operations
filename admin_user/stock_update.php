<?php
include 'db.php';

$base_url = 'http://localhost/POS-PHP/';

// Fetch products with zero or negative stock, not soft-deleted, and with product_type = 0
// Also ensure the canteen is not soft-deleted (del_status = 0) and is active (active = 0)
$productQuery = "SELECT p.id, p.cantine_id, p.description, p.image, p.stock, 
                        COALESCE(c.name, 'Unknown Canteen') AS cantine_name
                 FROM products p
                 LEFT JOIN cantines c ON p.cantine_id = c.id
                 WHERE p.stock <= 20 
                   AND p.del_status = 0 
                   AND p.product_type = 0
                   AND c.del_status = 0 
                   AND c.active = 0
                 ORDER BY p.id DESC"; // Ensure newest updates are at the top
$productResult = $conn->query($productQuery);

$products = [];
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'cantine_name' => $row['cantine_name'], // Fetch the canteen name
            'description' => $row['description'],
            'image' => !empty($row['image']) ? $base_url . $row['image'] : $base_url . 'default-image.png',
            'stock' => $row['stock']
        ];
    }
}

// Fetch stock update logs
// Ensure the canteen is not soft-deleted (del_status = 0) and is active (active = 0)
$logQuery = "SELECT ps.id, ps.product_id, ps.user_id, ps.user_admin, ps.update_stock, ps.stock_date, 
                    p.description AS product_name, 
                    COALESCE(c.name, 'Unknown Canteen') AS cantine_name
             FROM product_stocks ps
             LEFT JOIN products p ON ps.product_id = p.id
             LEFT JOIN cantines c ON p.cantine_id = c.id
             WHERE p.del_status = 0 
               AND p.product_type = 0
               AND (c.del_status = 0 AND c.active = 0) -- Ensure canteen is active and not soft-deleted
             ORDER BY ps.stock_date DESC"; // Ensure newest updates are at the top
$logResult = $conn->query($logQuery);

$logs = [];
if ($logResult) {
    while ($row = $logResult->fetch_assoc()) {
        // Determine the user name
        if (!empty($row['user_admin']) && $row['user_admin'] !== '0') {
            $userName = $row['user_admin'] . " (ADMIN)";
        } elseif (!empty($row['cantine_name']) && $row['cantine_name'] !== 'Unknown Canteen') {
            $userName = $row['cantine_name'] . " (CANTEEN)";
        } else {
            $userName = "Unknown User";
        }

        $logs[] = [
            'id' => $row['id'],
            'product_name' => $row['product_name'],
            'user_name' => $userName,
            'update_stock' => $row['update_stock'],
            'stock_date' => $row['stock_date']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Update</title>
    <style>
            body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: #333;
            margin: 20px 0;
        }
        .container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 20px;
        }
        .table-container {
            width: 48%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table-container h2 {
            background-color: #007BFF;
            color: #fff;
            padding: 10px;
            margin: 0;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .sidebar, .header {
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>
    <script>
        function searchTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
                const cells = rows[i].getElementsByTagName('td');
                let match = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].innerText.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }

                rows[i].style.display = match ? '' : 'none';
            }
        }
    </script>
</head>
<body>
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
    <h1>Stock Update</h1>
    <div class="container">
        <!-- Products with zero or negative stock -->
        <div class="table-container">
            <h2>Products with 20 to Below Stock</h2>
            <input type="text" id="productSearch" placeholder="Search products..." onkeyup="searchTable('productSearch', 'productTable')">
            <table id="productTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Canteen Name</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['cantine_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td><img src="<?php echo $product['image']; ?>" alt="Product Image"></td>
                            <td><?php echo $product['stock']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Stock update logs -->
        <div class="table-container">
            <h2>Stock Update Logs</h2>
            <input type="text" id="logSearch" placeholder="Search logs..." onkeyup="searchTable('logSearch', 'logTable')">
            <table id="logTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>User Name</th>
                        <th>Stock Change</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo htmlspecialchars($log['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                            <td><?php echo $log['update_stock']; ?></td>
                            <td><?php echo $log['stock_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>