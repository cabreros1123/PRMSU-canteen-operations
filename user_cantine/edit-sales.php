<?php
session_start();
include 'db.php';
if (!isset($_SESSION['id_cantine'])) {
  die('Error: You are not authorized to view this page. Please log in.');
}

// Get the id_cantine of the logged-in user
$id_cantine = $_SESSION['id_cantine'];


$editId = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;
$saleData = null;

if ($editId) {
    $query = "SELECT s.*, p.image AS product_image 
              FROM sales s
              LEFT JOIN products p ON JSON_CONTAINS(s.products, JSON_OBJECT('id', p.id))
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $saleData = $result->fetch_assoc();
    $stmt->close();

    // Decode the products JSON to retrieve quantities for type 0 products
    if ($saleData && isset($saleData['products'])) {
        $productsData = json_decode($saleData['products'], true);
        foreach ($productsData as &$product) {
            // Fetch the product type from the database
            $productQuery = "SELECT product_type FROM products WHERE id = ?";
            $productStmt = $conn->prepare($productQuery);
            $productStmt->bind_param('i', $product['id']);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $productRow = $productResult->fetch_assoc();
            $product['product_type'] = $productRow['product_type'] ?? 0; // Default to type 0 if not found
            $productStmt->close();
        }
        $saleData['products'] = json_encode($productsData); // Re-encode the updated products data
    }
}

// Fetch products and their categories from the database
$products = mysqli_query($conn, "
    SELECT 
    p.id, 
    p.description, 
    p.image, 
    p.stock, 
    p.buyingPrice, 
    p.sellingPrice, 
    p.product_type, -- Include the product_type column
    c.Category AS category_name 
FROM products p
LEFT JOIN categories c ON p.idCategory = c.id
WHERE p.del_status = 0 AND p.cantine_id = $id_cantine
");

// Fetch only active and not soft-deleted canteens
$canteens = mysqli_query($conn, "
    SELECT id, name 
    FROM cantines 
    WHERE active = 0 AND del_status = 0
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }

        .table-container {
            width: 100%; /* Ensure both tables take the full width */
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 20px; /* Add spacing between tables */
        }

        table {
            width: 100%; /* Ensure both tables take the full width of their container */
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
        }

        .btn-add {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-remove {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .stock-input {
            width: 60px;
            text-align: center;
        }

        #canteenSelect {
            margin-bottom: 10px;
            padding: 5px;
            width: 100%;
        }

        .cost-input, .sell-input {
            width: 80px; /* Ensure consistent input widths */
            text-align: center;
        }
    </style>
</head>
<body>
<?php require_once "header.php"; ?>
    <div class="container">
        <!-- Left Table: Selected Products -->
        <div class="table-container">
            <h3>Selected Products</h3>
            <label for="canteenSelect">Canteen:</label>
            <select id="canteenSelect" name="canteenSelect" disabled>
                <option value="<?php echo $id_cantine; ?>">
                    <?php echo htmlspecialchars($_SESSION['cantine_name']); ?>
                </option>
            </select>
            <table id="selectedProductsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Cost Price</th>
                        <th>Sales Price</th>
                        <th>Profit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Selected products will be dynamically added here -->
                </tbody>
            </table>
        <div class="table-container" id="type1TableContainer" style="display: none;">
            <h3>(Custom Sales)</h3>
            <table id="selectedProductsTableType1">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Cost Price</th>
                        <th>Sales Price</th>
                        <th>Profit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Selected products with product_type = 1 will be dynamically added here -->
                </tbody>
            </table>
        </div>
        <div id="totalCostContainer" style="margin-top: 10px; text-align: right; font-weight: bold;">
            Total Cost: ₱<span id="totalCost">0.00</span>
        </div>
        <div id="totalSalesContainer" style="margin-top: 10px; text-align: right; font-weight: bold;">
            Total Sales: ₱<span id="totalSales">0.00</span>
        </div>
        <div id="totalProfitContainer" style="margin-top: 10px; text-align: right; font-weight: bold;">
            Total Profit: ₱<span id="totalProfit">0.00</span>
        </div>
            <button id="saveSalesButton" onclick="saveSales()" style="margin-top: 10px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Save Sales
            </button>
            <button id="saveNewSalesButton" onclick="saveNewSales()" style="margin-top: 10px; padding: 10px 20px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Save as New Sales
            </button>
        </div>
        <!-- Right Table: Available Products -->
        <div class="table-container">
            <h3>Available Products</h3>
            <input
                type="text"
                id="searchBar"
                placeholder="Search for products..."
                onkeyup="filterProducts()"
                style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;"
            />
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Stock</th>
                        <th>Category</th> <!-- Make the category column visible -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="availableProductsTable">
    <?php while ($row = mysqli_fetch_assoc($products)) : ?>
        <tr id="product-<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td>
                <img src="/POS-PHP/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" style="width: 50px; height: 50px;">
            </td>
            <td>
                <?php echo $row['product_type'] == 1 ? 'Custom' : htmlspecialchars($row['stock']); ?>
            </td>
            <td class="category-column"><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td>
            <button 
    class="btn-add" 
    onclick="addProduct(
    '<?php echo $row['id']; ?>',
    '<?php echo htmlspecialchars($row['description']); ?>',
    '<?php echo htmlspecialchars($row['image']); ?>',
    '<?php echo htmlspecialchars($row['stock']); ?>',
    '<?php echo htmlspecialchars($row['buyingPrice']); ?>',
    '<?php echo htmlspecialchars($row['sellingPrice']); ?>',
    1, // quantity (default to 1 when adding)
    <?php echo $row['product_type']; ?> // productType (proper value now)
)"

    <?php echo ($row['stock'] <= 0 && $row['product_type'] != 1) ? 'disabled style="background-color: #ccc; cursor: not-allowed;"' : ''; ?>
>
    +
</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
            </table>
            <div id="paginationControls" style="margin-top: 10px; text-align: center;">
                <button id="prevPage" onclick="prevPage()" style="padding: 5px 10px; margin-right: 5px;">Previous</button>
                <span id="pageInfo"></span>
                <button id="nextPage" onclick="nextPage()" style="padding: 5px 10px; margin-left: 5px;">Next</button>
            </div>
        </div>
    </div>

    <script>
        const originalQuantities = {}; // Object to store original quantities

        const saleData = <?php echo json_encode($saleData); ?>;
        if (saleData) {
    document.getElementById('canteenSelect').value = saleData.id_cantine;
    const products = JSON.parse(saleData.products);

    // Populate the products table with the data
    products.forEach(product => {
        originalQuantities[product.id] = product.quantity; // Store the original quantity

        // Check the product type and add to the appropriate table
        if (product.product_type == 1) {
            // Add to Type 1 Table
            addProduct(
                product.id,
                product.description,
                product.image || '',
                0, // Stock is not applicable for type 1
                product.cost, // Use the cost directly
                product.sell, // Use the sell price directly
                1, // Quantity is always 1 for type 1
                1 // Indicate product_type = 1
            );
        } else {
            // Add to Type 0 Table
            addProduct(
                product.id,
                product.description,
                product.image || '',
                product.stock, // Use the stock from the database
                product.cost / product.quantity, // Calculate buying price per unit
                product.sell / product.quantity, // Calculate selling price per unit
                product.quantity, // Pass the correct quantity from the sales data
                0 // Indicate product_type = 0
            );
        }
    });
}

        function handleCanteenChange() {
            const canteenSelect = document.getElementById('canteenSelect');
            const selectedCanteenId = canteenSelect.value;
            const selectedCanteenName = canteenSelect.options[canteenSelect.selectedIndex].text;

            if (selectedCanteenId) {
                alert(`Selected Canteen: ${selectedCanteenName} (ID: ${selectedCanteenId})`);
            } else {
                alert('No canteen selected.');
            }
        }

        function addProduct(id, description, image, stock, buyingPrice, sellingPrice, quantity = 1, productType = 0) {
    if (productType == 1) {
        // Handle product_type = 1
        if (document.getElementById('selected-type1-' + id)) {
            alert('This product is already added.');
            return;
        }

        // Show the Type 1 table if it's hidden
        const type1TableContainer = document.getElementById('type1TableContainer');
        type1TableContainer.style.display = 'block';

        const table = document.getElementById('selectedProductsTableType1').querySelector('tbody');
        const row = document.createElement('tr');
        row.id = 'selected-type1-' + id;

        row.innerHTML = `
            <td>${description}</td>
            <td>
                <input type="number" class="cost-input" min="0" step="0.01" value="${buyingPrice}" placeholder="Cost Price" 
                    onchange="updateType1Profit('${id}')">
            </td>
            <td>
                <input type="number" class="sell-input" min="0" step="0.01" value="${sellingPrice}" placeholder="Sell Price" 
                    onchange="updateType1Profit('${id}')">
            </td>
            <td>
                <div id="profit-type1-${id}">₱${(sellingPrice - buyingPrice).toFixed(2)}</div>
            </td>
            <td>
                <button class="btn-remove" onclick="removeProductType1('${id}')">Remove</button>
            </td>
        `;

        table.appendChild(row);
        updateTotalProfit();
    } else {
        // Handle product_type = 0
        if (document.getElementById('selected-type0-' + id)) {
            alert('This product is already added.');
            return;
        }

        const table = document.getElementById('selectedProductsTable').querySelector('tbody');
        const row = document.createElement('tr');
        row.id = 'selected-type0-' + id;

        const profit = (sellingPrice - buyingPrice) * quantity;

        row.innerHTML = `
            <td>${description}</td>
            <td>
                <input type="number" class="stock-input" min="1" max="${stock}" value="${quantity}" 
                    onchange="updateType0Prices('${id}', ${buyingPrice}, ${sellingPrice}, ${stock})">
            </td>
            <td>₱${(buyingPrice * quantity).toFixed(2)}</td>
            <td>₱${(sellingPrice * quantity).toFixed(2)}</td>
            <td>
                <div id="profit-type0-${id}">₱${profit.toFixed(2)}</div>
            </td>
            <td>
                <button class="btn-remove" onclick="removeProductType0('${id}')">Remove</button>
            </td>
        `;

        table.appendChild(row);
        updateTotalProfit();
    }
}

        function updatePrices(id, buyingPrice, sellingPrice, stock, isType1) {
            // Get the current values of Cost Price and Sell Price from the inputs
            const costInput = document.querySelector(`#selected-${id} .cost-input`);
            const sellInput = document.querySelector(`#selected-${id} .sell-input`);
            const currentBuyingPrice = parseFloat(costInput?.value) || 0;
            const currentSellingPrice = parseFloat(sellInput?.value) || 0;

            // For type 1 products, quantity is always 1
            const quantity = isType1 ? 1 : parseInt(document.querySelector(`#selected-${id} .stock-input`)?.value) || 0;

            if (!isType1 && quantity <= 0) {
                alert('Quantity must be at least 1.');
                return;
            }

            // Calculate total cost, total sell, and profit
            const totalCost = currentBuyingPrice * quantity;
            const totalSell = currentSellingPrice * quantity;
            const totalProfit = isType1
                ? currentSellingPrice - currentBuyingPrice // For type 1 products, profit is sell - cost
                : (currentSellingPrice - currentBuyingPrice) * quantity; // For other products, profit is calculated per quantity

            // Update the Cost, Sell, and Profit fields in the table
            const costCell = document.querySelector(`#selected-${id} td:nth-child(3)`);
            const sellCell = document.querySelector(`#selected-${id} td:nth-child(4)`);
            const profitDiv = document.getElementById(`profit-${id}`);

            costCell.innerHTML = isType1 ? `
                <input type="number" class="cost-input" min="0" step="0.01" value="${currentBuyingPrice}" placeholder="Cost Price" 
                    onchange="updatePrices('${id}', this.value, document.querySelector('#sell-${id}').value, ${stock}, ${isType1})">
            ` : `₱${totalCost.toFixed(2)}`;

            sellCell.innerHTML = isType1 ? `
                <input type="number" id="sell-${id}" class="sell-input" min="0" step="0.01" value="${currentSellingPrice}" placeholder="Sell Price" 
                    onchange="updatePrices('${id}', document.querySelector('#cost-${id}').value, this.value, ${stock}, ${isType1})">
            ` : `₱${totalSell.toFixed(2)}`;

            profitDiv.innerHTML = `₱${totalProfit.toFixed(2)}`;

            // Update the total profit for all products
            updateTotalProfit();
        }

        function updateTotalProfit() {
            let totalCost = 0;
            let totalSales = 0;
            let totalProfit = 0;

            // Calculate totals for product_type = 0
            const rowsType0 = document.querySelectorAll('#selectedProductsTable tbody tr');
            rowsType0.forEach(row => {
                const cost = parseFloat(row.cells[2].innerText.replace('₱', '').trim()) || 0;
                const sales = parseFloat(row.cells[3].innerText.replace('₱', '').trim()) || 0;
                const profit = parseFloat(row.querySelector('[id^="profit-type0-"]').innerText.replace('₱', '').trim()) || 0;

                totalCost += cost;
                totalSales += sales;
                totalProfit += profit;
            });

            // Calculate totals for product_type = 1
            const rowsType1 = document.querySelectorAll('#selectedProductsTableType1 tbody tr');
            rowsType1.forEach(row => {
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const sales = parseFloat(row.querySelector('.sell-input').value) || 0;
                const profit = parseFloat(row.querySelector('[id^="profit-type1-"]').innerText.replace('₱', '').trim()) || 0;

                totalCost += cost;
                totalSales += sales;
                totalProfit += profit;
            });

            // Update the containers
            document.getElementById('totalCost').innerText = totalCost.toFixed(2);
            document.getElementById('totalSales').innerText = totalSales.toFixed(2);
            document.getElementById('totalProfit').innerText = totalProfit.toFixed(2);
        }

        function removeProduct(id) {
            const row = document.getElementById('selected-' + id);
            if (row) {
                row.remove();
            }

            updateTotalProfit();
        }

        function saveSales() {
            const canteenSelect = document.getElementById('canteenSelect');
            const selectedCanteenId = canteenSelect.value;

            if (!selectedCanteenId) {
                alert('Please select a canteen before saving sales.');
                return;
            }

            const rowsType0 = document.querySelectorAll('#selectedProductsTable tbody tr');
            const rowsType1 = document.querySelectorAll('#selectedProductsTableType1 tbody tr');

            if (rowsType0.length === 0 && rowsType1.length === 0) {
                alert('No products selected to save.');
                return;
            }

            const salesData = [];
            const quantityAdjustments = {}; // Object to store quantity adjustments
            let totalCost = 0;
            let totalSales = 0;
            let totalProducts = 0;

            // Process products with product_type = 0
            rowsType0.forEach(row => {
                const id = row.id.replace('selected-type0-', '');
                const description = row.querySelector('td:nth-child(1)').innerText;
                const quantity = parseInt(row.querySelector('.stock-input').value);
                const cost = parseFloat(row.cells[2].innerText.replace('₱', '').trim());
                const sell = parseFloat(row.cells[3].innerText.replace('₱', '').trim());
                const profit = parseFloat(row.querySelector('#profit-type0-' + id).innerText.replace('₱', '').trim());

                salesData.push({
                    id,
                    description,
                    quantity,
                    cost,
                    sell,
                    profit
                });

                // Calculate quantity adjustment
                const originalQuantity = originalQuantities[id] || 0;
                const quantityDifference = quantity - originalQuantity; // Positive if increased, negative if decreased
                quantityAdjustments[id] = -quantityDifference; // Reverse the sign for stock adjustment

                totalCost += cost;
                totalSales += sell;
                totalProducts += quantity;
            });

            // Process products with product_type = 1
            rowsType1.forEach(row => {
                const id = row.id.replace('selected-type1-', '');
                const description = row.querySelector('td:nth-child(1)').innerText;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const sell = parseFloat(row.querySelector('.sell-input').value) || 0;
                const profit = parseFloat(row.querySelector('#profit-type1-' + id).innerText.replace('₱', '').trim());
                const quantity = 1; // Default quantity for product_type = 1

                salesData.push({
                    id,
                    description,
                    quantity,
                    cost,
                    sell,
                    profit
                });

                totalCost += cost;
                totalSales += sell;
                totalProducts += quantity;
            });

            const totalProfit = document.getElementById('totalProfit').innerText;

            // Check if we are editing an existing sale
            const editId = <?php echo json_encode($editId); ?>;

            // Send the data to the server
            fetch('save_sales.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_cantine: selectedCanteenId,
                    products: salesData,
                    total_profit: totalProfit,
                    total_cost: totalCost.toFixed(2),
                    total_sales: totalSales.toFixed(2),
                    total_products: totalProducts,
                    quantity_adjustments: quantityAdjustments, // Include quantity adjustments
                    edit_id: editId // Include the edit_id if editing
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Data sent to server:', {
                    id_cantine: selectedCanteenId,
                    products: salesData,
                    total_profit: totalProfit,
                    total_cost: totalCost.toFixed(2),
                    total_sales: totalSales.toFixed(2),
                    total_products: totalProducts,
                    quantity_adjustments: quantityAdjustments,
                    edit_id: editId
                });
                console.log('Server Response:', data);
                if (data.success) {
                    alert('Sales saved successfully!');
                    window.location.href = 'view_sales.php';
                } else {
                    alert('Failed to save sales: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Sales saved successfully!');
                window.location.href = 'view_sales.php';
            });
        }

        let currentPage = 1;
        const rowsPerPage = 12;

        function filterProducts() {
            const searchInput = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('#availableProductsTable tr');

            tableRows.forEach(row => {
                const productName = row.querySelector('td:first-child').innerText.toLowerCase();
                const categoryName = row.querySelector('.category-column').innerText.toLowerCase(); // Get the category name

                if (productName.includes(searchInput) || categoryName.includes(searchInput)) {
                    row.style.display = ''; // Show the row
                    row.classList.remove('hidden'); // Mark it as visible for pagination
                } else {
                    row.style.display = 'none'; // Hide the row
                    row.classList.add('hidden'); // Mark it as hidden for pagination
                }
            });

            // Reset pagination after filtering
            currentPage = 1;
            paginateProducts();
        }

        function paginateProducts() {
            const tableRows = document.querySelectorAll('#availableProductsTable tr');
            const visibleRows = Array.from(tableRows).filter(row => !row.classList.contains('hidden')); // Only visible rows
            const totalRows = visibleRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            // Hide all rows
            tableRows.forEach(row => {
                row.style.display = 'none';
            });

            // Show only rows for the current page
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            for (let i = start; i < end && i < totalRows; i++) {
                visibleRows[i].style.display = ''; // Show the row
            }

            // Update pagination controls
            document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage'). disabled = currentPage === totalPages;
        }

        function nextPage() {
            const tableRows = document.querySelectorAll('#availableProductsTable tr');
            const visibleRows = Array.from(tableRows).filter(row => !row.classList.contains('hidden')); // Only visible rows
            const totalRows = visibleRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            if (currentPage < totalPages) {
                currentPage++;
                paginateProducts();
            }
        }

        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                paginateProducts();
            }
        }

        // Initialize pagination on page load
        document.addEventListener('DOMContentLoaded', () => {
            paginateProducts();
            highlightStockLevels();
        });

        // Highlight stock levels in the right-side table
        function highlightStockLevels() {
            const rows = document.querySelectorAll('#availableProductsTable tr');
            rows.forEach(row => {
                const stockCell = row.querySelector('td:nth-child(3)'); // Stock column
                const stock = parseInt(stockCell.innerText);

                if (stock <= 5) {
                    stockCell.style.backgroundColor = '#FF8080';
                    stockCell.style.color = 'white';
                } else if (stock <= 10) {
                    stockCell.style.backgroundColor = '#FFC55C';
                    stockCell.style.color = 'white';
                } else if (stock <= 20) {
                    stockCell.style.backgroundColor = '#FFFF80';
                    stockCell.style.color = 'black';
                } else {
                    stockCell.style.backgroundColor = '#98FF98'; // Reset to default
                    stockCell.style.color = 'black'; // Reset to default
                }
            });
        }

        function updateType1Profit(id) {
            const costInput = document.querySelector(`#selected-type1-${id} .cost-input`);
            const sellInput = document.querySelector(`#selected-type1-${id} .sell-input`);
            const currentBuyingPrice = parseFloat(costInput?.value) || 0;
            const currentSellingPrice = parseFloat(sellInput?.value) || 0;

            const profit = currentSellingPrice - currentBuyingPrice;

            const profitDiv = document.getElementById(`profit-type1-${id}`);
            profitDiv.innerHTML = `₱${profit.toFixed(2)}`;

            updateTotalProfit();
        }

        function updateType0Prices(id, buyingPrice, sellingPrice, stock) {
            const quantityInput = document.querySelector(`#selected-type0-${id} .stock-input`);
            const quantity = parseInt(quantityInput.value) || 0;

            if (quantity > stock) {
            const confirmMessage = `The available stock for this product is ${stock}. If you continue, the stock will become negative. Do you want to proceed?`;
            if (!confirm(confirmMessage)) {
                // Revert to the previous quantity if the user cancels
                quantityInput.value = stock;
                return;
            }
        }

            const totalCost = buyingPrice * quantity;
            const totalSell = sellingPrice * quantity;
            const profit = (sellingPrice - buyingPrice) * quantity;

            const row = document.getElementById(`selected-type0-${id}`);
            row.cells[2].innerHTML = `₱${totalCost.toFixed(2)}`;
            row.cells[3].innerHTML = `₱${totalSell.toFixed(2)}`;
            const profitDiv = document.getElementById(`profit-type0-${id}`);
            profitDiv.innerHTML = `₱${profit.toFixed(2)}`;

            updateTotalProfit();
        }

        function removeProductType0(id) {
            const row = document.getElementById('selected-type0-' + id);
            if (row) {
                row.remove();
            }
            updateTotalProfit();
        }

        function removeProductType1(id) {
            const row = document.getElementById('selected-type1-' + id);
            if (row) {
                row.remove();
            }

            // Check if there are any remaining rows in the Type 1 table
            const type1Table = document.getElementById('selectedProductsTableType1').querySelector('tbody');
            if (type1Table.children.length === 0) {
                // Hide the Type 1 table if no rows remain
                const type1TableContainer = document.getElementById('type1TableContainer');
                type1TableContainer.style.display = 'none';
            }

            updateTotalProfit();
        }
        function saveNewSales() {
    const canteenSelect = document.getElementById('canteenSelect');
    const selectedCanteenId = canteenSelect.value;

    if (!selectedCanteenId) {
        alert('Please select a canteen before saving sales.');
        return;
    }

    const rowsType0 = document.querySelectorAll('#selectedProductsTable tbody tr');
    const rowsType1 = document.querySelectorAll('#selectedProductsTableType1 tbody tr');

    if (rowsType0.length === 0 && rowsType1.length === 0) {
        alert('No products selected to save.');
        return;
    }

    const salesData = [];
    const quantityAdjustments = {}; // Object to store quantity adjustments
    let totalCost = 0;
    let totalSales = 0;
    let totalProducts = 0;

    // Process products with product_type = 0
    rowsType0.forEach(row => {
        const id = row.id.replace('selected-type0-', '');
        const description = row.querySelector('td:nth-child(1)').innerText;
        const quantity = parseInt(row.querySelector('.stock-input').value);
        const cost = parseFloat(row.cells[2].innerText.replace('₱', '').trim());
        const sell = parseFloat(row.cells[3].innerText.replace('₱', '').trim());
        const profit = parseFloat(row.querySelector('#profit-type0-' + id).innerText.replace('₱', '').trim());

        salesData.push({
            id,
            description,
            quantity,
            cost,
            sell,
            profit
        });

        // Calculate quantity adjustment
        const originalQuantity = originalQuantities[id] || 0;
        const quantityDifference = quantity - originalQuantity; // Positive if increased, negative if decreased
        quantityAdjustments[id] = -quantityDifference; // Reverse the sign for stock adjustment

        totalCost += cost;
        totalSales += sell;
        totalProducts += quantity;
    });

    // Process products with product_type = 1
    rowsType1.forEach(row => {
        const id = row.id.replace('selected-type1-', '');
        const description = row.querySelector('td:nth-child(1)').innerText;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const sell = parseFloat(row.querySelector('.sell-input').value) || 0;
        const profit = parseFloat(row.querySelector('#profit-type1-' + id).innerText.replace('₱', '').trim());
        const quantity = 1; // Default quantity for product_type = 1

        salesData.push({
            id,
            description,
            quantity,
            cost,
            sell,
            profit
        });

        totalCost += cost;
        totalSales += sell;
        totalProducts += quantity;
    });

    const totalProfit = document.getElementById('totalProfit').innerText;

    // Send the data to the server
    fetch('save_updatesales.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_cantine: selectedCanteenId,
            products: salesData,
            total_profit: totalProfit,
            total_cost: totalCost.toFixed(2),
            total_sales: totalSales.toFixed(2),
            total_products: totalProducts,
            quantity_adjustments: quantityAdjustments // Include quantity adjustments
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Data sent to server:', {
            id_cantine: selectedCanteenId,
            products: salesData,
            total_profit: totalProfit,
            total_cost: totalCost.toFixed(2),
            total_sales: totalSales.toFixed(2),
            total_products: totalProducts,
            quantity_adjustments: quantityAdjustments
        });
        console.log('Server Response:', data);
        if (data.success) {
            alert('New sale saved successfully!');
            window.location.href = 'view_sales.php';
        } else {
            alert('Failed to save new sale: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('New sale saved successfully!');
        window.location.href = 'view_sales.php';
    });
}
    </script>
</body>
</html>