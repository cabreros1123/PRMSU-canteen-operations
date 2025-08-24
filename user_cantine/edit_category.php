<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $category = mysqli_fetch_assoc($result);
}

if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $category_name = $_POST['category_name'];
    
    $query = "UPDATE categories SET Category = '$category_name' WHERE id = $id";
    mysqli_query($conn, $query);
    header("Location: category.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
</head>
<body>

<div class="container">
    <h2>Edit Category</h2>
    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
        <input type="text" name="category_name" value="<?php echo $category['Category']; ?>" required>
        <button type="submit" name="update_category">Update Category</button>
    </form>
</div>

</body>
</html>
