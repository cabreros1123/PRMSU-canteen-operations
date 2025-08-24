<?php
include 'db.php'; // Ensure this connects to your posystem database

// Handle category addition
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $category_type = $_POST['category_type']; // Get the selected type
    $date = date("Y-m-d H:i:s");

    $query = "INSERT INTO categories (Category, type, date, del_status) VALUES ('$category_name', '$category_type', '$date', 0)";
    mysqli_query($conn, $query);
    header("Location: category.php"); // Refresh page after adding
    exit();
}

// Handle category deletion (soft delete)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "UPDATE categories SET del_status = 1 WHERE id = $delete_id";
    mysqli_query($conn, $query);
    header("Location: category.php");
    exit();
}

// Handle category update (edit)
if (isset($_POST['update_category'])) {
    $edit_id = $_POST['edit_id'];
    $edit_category_name = $_POST['edit_category_name'];

    $query = "UPDATE categories SET Category = '$edit_category_name' WHERE id = $edit_id";
    mysqli_query($conn, $query);
    header("Location: category.php"); // Refresh page after updating
    exit();
}

// Fetch categories from the database where del_status = 0
$categories = mysqli_query($conn, "SELECT id, Category, type, date FROM categories WHERE del_status = 0 ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="css/category.css">
</head>
<body>
<?php require_once "sidebar.php"; ?>
<?php require_once "header.php"; ?>
<div class="container">
    <h2>Manage Categories</h2>

    <!-- Add Category Form -->
    <form action="" method="POST">
        <input type="text" name="category_name" required placeholder="Enter category name">
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <!-- Display Category Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($categories)) : ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['Category']; ?></td>
                    <td><?php echo isset($row['date']) ? $row['date'] : 'N/A'; ?></td> <!-- Handle missing date -->
                    <td>
                        <button class="btn btn-edit" onclick="openEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['Category']); ?>')">Edit</button>
                        <a href="category.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to Remove this category?');">Remove</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
        <h3>Edit Category</h3>
        <form action="category.php" method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            <label>
                <span>Category Name:</span>
                <input type="text" name="edit_category_name" id="edit_category_name" required>
            </label>
            <button type="submit" name="update_category">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, categoryName) {
        const modal = document.getElementById('editModal');
        const editIdInput = document.getElementById('edit_id');
        const editCategoryNameInput = document.getElementById('edit_category_name');

        // Set the values in the modal
        editIdInput.value = id;
        editCategoryNameInput.value = categoryName;

        // Show the modal
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.style.display = 'none';
    }
</script>

</body>
</html>
