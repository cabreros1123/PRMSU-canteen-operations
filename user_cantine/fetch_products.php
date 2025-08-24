<?php
include 'db.php';

// Fetch products from the database
$query = "
    SELECT 
        products.*, 
        categories.category AS categoryName 
    FROM 
        products 
    LEFT JOIN 
        categories 
    ON 
        products.idCategory = categories.id 
    ORDER BY 
        products.id DESC
";
$products = $conn->query($query);

// Check for errors
if (!$products) {
    die("Query failed: " . $conn->error);
}

// Generate table rows
while ($row = $products->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['categoryName']) ?></td>
        <td><?= $row['code'] ?></td>
        <td><?= $row['description'] ?></td>
        <td>
            <?php
            $imagePath = !empty($row['image']) 
                ? $row['image'] 
                : 'views/img/products/default/anonymous.png';

            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/POS-PHP/' . $imagePath;

            if (!file_exists($absolutePath)) {
                $imagePath = 'views/img/products/default/anonymous.png';
                error_log("File not found: " . $absolutePath); // Log the missing file
            }
            ?>
            <img src="/POS-PHP/<?= htmlspecialchars($imagePath) ?>" alt="Product Image" style="width: 60px; height: 60px;">
        </td>
        <td><?= $row['stock'] ?></td>
        <td><?= number_format($row['buyingPrice'], 2) ?></td>
        <td><?= number_format($row['sellingPrice'], 2) ?></td>
        <td><?= $row['date'] ?></td>
        <td>
            <button onclick="editProduct(
                '<?= $row['id'] ?>',
                '<?= $row['idCategory'] ?>',
                '<?= $row['code'] ?>',
                '<?= $row['description'] ?>',
                '<?= $row['stock'] ?>',
                '<?= $row['buyingPrice'] ?>',
                '<?= $row['sellingPrice'] ?>'
            )">Edit</button>
        </td>
    </tr>
<?php endwhile; ?>