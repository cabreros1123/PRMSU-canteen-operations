<?php
include 'db.php';
include 'add_product.php'; // Include the add_product logic

// Ensure the user is logged in and has a valid id_cantine
if (!isset($_SESSION['id_cantine'])) {
    die('Error: You are not authorized to view this page. Please log in.');
}

// Get the id_cantine of the logged-in user
$id_cantine = $_SESSION['id_cantine'];

// Handle product deletion (soft delete)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "UPDATE products SET del_status = 1 WHERE id = $delete_id";
    $conn->query($query);
    header("Location: product.php");
    exit();
}

// Fetch products from the database where del_status = 0 and cantine_id matches the logged-in user's id_cantine
$query = "
    SELECT 
        products.*, 
        categories.category AS categoryName,
        cantines.name AS canteenName
    FROM 
        products 
    LEFT JOIN 
        categories 
    ON 
        products.idCategory = categories.id 
    LEFT JOIN 
        cantines 
    ON 
        products.cantine_id = cantines.id 
    WHERE 
        products.del_status = 0 
        AND products.cantine_id = $id_cantine
    ORDER BY 
        products.id DESC
";
$products = $conn->query($query);

// Error handling (optional, but good practice)
if (!$products) {
    die("Query failed: " . $conn->error);
}

// Fetch canteens from the database
$canteenQuery = "SELECT id, name FROM cantines WHERE active = 0 AND del_status = 0";
$canteens = $conn->query($canteenQuery);

// Error handling (optional)
if (!$canteens) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Already present -->
    <title>Product Management</title>
    <link rel="stylesheet" href="css/product.css">
</head>
<body>
<?php require_once "header.php"; ?>

<h2>Product Management</h2>

<!-- Add Product Button -->
<button class="btn-add-product" onclick="openModal()">Add Product</button>

<!-- Search Bar -->
<div class="search-container">
    <input type="text" id="searchInput" placeholder="Search by ID, Description, or Category">
</div>

<!-- Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h2>Product</h2>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id">
            <label>
                <span>Canteen:</span>
                <select name="canteenId" id="canteenId" required>
                    <option value="" disabled selected>Select a canteen</option>
                    <?php
                    // Fetch canteens from the database
                    $canteenQuery = "SELECT id, name FROM cantines WHERE active = 0 AND del_status = 0";
                    $canteens = $conn->query($canteenQuery);

                    if ($canteens && $canteens->num_rows > 0) {
                        while ($canteen = $canteens->fetch_assoc()) {
                            echo "<option value='" . $canteen['id'] . "'>" . htmlspecialchars($canteen['name']) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No canteens available</option>";
                    }
                    ?>
                </select>
            </label>
            <label>
                <span>Description:</span>
                <input type="text" name="newDescription" required>
            </label>
            <label>
                <span>Product Photo:</span>
                <input type="file" name="newProdPhoto" accept="image/*" onchange="previewImage(event)">
            </label>
            <div style="margin-top: 10px; position: relative; display: inline-block;">
                <img id="photoPreview" src="views/img/products/default/anonymous.png" alt="Product Preview" style="width: 2in; height: 2in; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
                <button type="button" id="removePhotoButton" onclick="removePhoto()" style="position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; display: none;">&times;</button>
            </div>
            <div id="currentImageSection" style="margin-top: 10px; display: none;">
                <p>Current Image:</p>
                <img id="currentImage" src="" alt="Current Product Image" style="width: 2in; height: 2in; border: 1px solid #ccc; border-radius: 5px; object-fit: cover;">
            </div>
            <label>
                <span>Lifespan (Days):</span>
                <input type="number" name="lifespan_days" id="lifespan_days" min="0" value="0" required>
            </label>
            <label>
                <span>Lifespan (Hours):</span>
                <input type="number" name="lifespan_hours" id="lifespan_hours" min="0" max="23" value="0" required>
            </label>

            <button type="submit">Save Product</button>
        </form>
    </div>
</div>

<!-- Product Table -->
<table id="productTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Image</th>
            <th>Canteen</th>
            <th>Lifespan</th>
            <th>Date Added</th> <!-- Add this line -->
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $products->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['description'] ?></td>
            <td>
                <?php
                $imagePath = !empty($row['image']) 
                    ? $row['image'] 
                    : 'views/img/products/default/anonymous.png';
                ?>
                <img src="/POS-PHP/<?= htmlspecialchars($imagePath) ?>" alt="Product Image" style="width: 60px; height: 60px;">
            </td>
            <td><?= htmlspecialchars($row['canteenName'] ?? 'N/A') ?></td>
            <td>
                <?= ($row['lifespan_days'] ?? 0) . ' day(s), ' . ($row['lifespan_hours'] ?? 0) . ' hour(s)' ?>
            </td>
            <td><?= htmlspecialchars($row['date'] ?? '') ?></td> <!-- Show the date column -->
            <td style="text-align: center;">
                <button onclick="editProduct(
                    '<?= $row['id'] ?>',
                    '<?= $row['description'] ?>',
                    '<?= $row['image'] ?>',
                    '<?= $row['cantine_id'] ?>',
                    '<?= $row['lifespan_days'] ?>',
                    '<?= $row['lifespan_hours'] ?>'
                )" style="background-color: #0096FF; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                    Edit
                </button>
                <a href="product.php?delete_id=<?= $row['id'] ?>" 
                   onclick="return confirm('Are you sure you want to remove this product?');" 
                   style="background-color: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; text-decoration: none;">
                    Remove
                </a>
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

<script>
function openModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('active');

    document.querySelector('[name="id"]').value = '';
    document.querySelector('[name="newDescription"]').value = '';
    document.querySelector('[name="canteenId"]').value = '<?= $_SESSION['id_cantine'] ?>';
    document.querySelector('[name="canteenId"]').disabled = true;
    document.querySelector('input[name="newProdPhoto"]').value = '';
    document.getElementById('photoPreview').src = 'views/img/products/default/anonymous.png';
    document.getElementById('removePhotoButton').style.display = 'none';
    document.getElementById('currentImageSection').style.display = 'none';
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
}

function editProduct(id, description, imagePath, canteenId, lifespanDays, lifespanHours) {
    openModal();
    document.querySelector('[name="id"]').value = id;
    document.querySelector('[name="newDescription"]').value = description;
    document.querySelector('[name="canteenId"]').value = canteenId;
    document.querySelector('[name="canteenId"]').disabled = true;
    document.getElementById('lifespan_days').value = lifespanDays || 0;
    document.getElementById('lifespan_hours').value = lifespanHours || 0;

    const currentImageSection = document.getElementById('currentImageSection');
    const currentImage = document.getElementById('currentImage');

    if (imagePath) {
        currentImage.src = `/POS-PHP/${imagePath}`;
        currentImageSection.style.display = 'block';
    } else {
        currentImageSection.style.display = 'none';
    }

    window.scrollTo(0, 0);
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function () {
        const output = document.getElementById('photoPreview');
        const removeButton = document.getElementById('removePhotoButton');
        const currentImage = document.getElementById('currentImage');

        output.src = reader.result; // Set the preview image source to the uploaded file
        removeButton.style.display = 'block'; // Show the "X" button

        // Add blur effect to the current image
        if (currentImage) {
            currentImage.style.filter = 'blur(5px)';
        }
    };
    reader.readAsDataURL(event.target.files[0]); // Read the uploaded file
}

function removePhoto() {
    const photoPreview = document.getElementById('photoPreview');
    const removeButton = document.getElementById('removePhotoButton');
    const fileInput = document.querySelector('input[name="newProdPhoto"]');
    const currentImage = document.getElementById('currentImage');

    // Reset the photo preview to the default image
    photoPreview.src = "views/img/products/default/anonymous.png";
    removeButton.style.display = 'none'; // Hide the "X" button

    // Clear the file input value
    fileInput.value = "";

    // Remove blur effect from the current image
    if (currentImage) {
        currentImage.style.filter = 'none';
    }
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productTable tbody tr');

    rows.forEach(row => {
        const id = row.cells[0].textContent.toLowerCase();
        const description = row.cells[1].textContent.toLowerCase();
        const canteen = row.cells[3].textContent.toLowerCase();
        const date = row.cells[4].textContent.toLowerCase();

        if (id.includes(filter) || description.includes(filter) || canteen.includes(filter) || date.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});


// Call the highlightStockLevels function when the page loads
document.addEventListener('DOMContentLoaded', () => {
    highlightStockLevels();
});


function handleProductTypeChange() {
    const productTypeSelect = document.getElementById('productType');
    const selectedType = productTypeSelect.value;

    const stockInput = document.querySelector('input[name="newStock"]');
    const buyingPriceInput = document.querySelector('input[name="newBuyingPrice"]');
    const sellingPriceInput = document.querySelector('input[name="newSellingPrice"]');

    if (selectedType === '1') { // By Cost
        stockInput.value = 'N/A';
        stockInput.disabled = true;

        buyingPriceInput.value = 'N/A';
        buyingPriceInput.disabled = true;

        sellingPriceInput.value = 'N/A';
        sellingPriceInput.disabled = true;
    } else if (selectedType === '0') { // By Stock
        stockInput.disabled = false;
        stockInput.value = '';

        buyingPriceInput.disabled = false;
        buyingPriceInput.value = '';

        sellingPriceInput.disabled = false;
        sellingPriceInput.value = '';
    }
}

let currentPage = 1;
const rowsPerPage = 10;

function showPage(page) {
    const rows = document.querySelectorAll('#productTable tbody tr');
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    // Ensure the page number is within bounds
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;

    // Hide all rows
    rows.forEach((row, index) => {
        row.style.display = 'none';
    });

    // Show only the rows for the current page
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    for (let i = start; i < end && i < totalRows; i++) {
        rows[i].style.display = '';
    }

    // Update the page info
    document.getElementById('pageInfo').textContent = `Page ${page} of ${totalPages}`;

    // Enable or disable buttons based on the current page
    document.getElementById('prevPage').disabled = page === 1;
    document.getElementById('nextPage').disabled = page === totalPages;

    currentPage = page;
}

function nextPage() {
    showPage(currentPage + 1);
}

function prevPage() {
    showPage(currentPage - 1);
}

// Initialize the table with the first page
document.addEventListener('DOMContentLoaded', () => {
    showPage(1);
});

document.querySelector('form').addEventListener('submit', function () {
    const canteenSelect = document.querySelector('[name="canteenId"]');
    canteenSelect.disabled = false; // Enable the field temporarily
});
</script>


</body>
</html>
