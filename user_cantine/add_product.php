<?php
session_start(); // Start the session
include 'db.php'; // Include the database connection

if (!isset($_SESSION['id_cantine'])) {
    die('Error: You are not authorized to perform this action.');
}

$canteenId = $_SESSION['id_cantine']; // Fetch the logged-in user's canteen ID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $deleteImage = isset($_POST['deleteImage']) ? true : false;

    if (!empty($id)) {
        // Fetch the current image of the product
        $query = "SELECT image FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $currentImagePath = $product['image'];
        $stmt->close();
    }

    if (isset($_POST["newDescription"])) {
        if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newDescription"])) {
            $description = $_POST['newDescription'];
            $route = "views/img/products/default/anonymous.png";

            if (isset($_FILES["newProdPhoto"]) && $_FILES["newProdPhoto"]["error"] == 0) {
                $fileType = mime_content_type($_FILES["newProdPhoto"]["tmp_name"]);
                if ($fileType === 'image/jpeg' || $fileType === 'image/png') {
                    $folder = "../views/img/products/";
                    if (!file_exists($folder)) {
                        mkdir($folder, 0755, true);
                    }
                    $random = mt_rand(100, 999);
                    $extension = pathinfo($_FILES["newProdPhoto"]["name"], PATHINFO_EXTENSION);
                    $route = $folder . $random . "." . $extension;

                    if (!move_uploaded_file($_FILES["newProdPhoto"]["tmp_name"], $route)) {
                        header("Location: product.php?message=upload_error");
                        exit();
                    }

                    $imagePath = str_replace("../", "", $route);

                    // Delete the old image if a new one is uploaded
                    if (!empty($currentImagePath) && file_exists("../" . $currentImagePath)) {
                        unlink("../" . $currentImagePath);
                    }
                } else {
                    header("Location: product.php?message=invalid_file_type");
                    exit();
                }
            } else {
                // Use the current image path if no new image is uploaded
                $imagePath = $currentImagePath ?? $route;
            }

            $lifespan_days = isset($_POST['lifespan_days']) ? intval($_POST['lifespan_days']) : 0;
            $lifespan_hours = isset($_POST['lifespan_hours']) ? intval($_POST['lifespan_hours']) : 0;

            if (empty($id)) {
                $sql = "INSERT INTO products (description, image, cantine_id, lifespan_days, lifespan_hours) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiii", $description, $imagePath, $canteenId, $lifespan_days, $lifespan_hours);
            } else {
                $sql = "UPDATE products SET description=?, image=?, cantine_id=?, lifespan_days=?, lifespan_hours=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiiii", $description, $imagePath, $canteenId, $lifespan_days, $lifespan_hours, $id);
            }

            if ($stmt->execute()) {
                header("Location: product.php?message=success");
                exit();
            } else {
                header("Location: product.php?message=error");
                exit();
            }

            $stmt->close();
        } else {
            header("Location: product.php?message=invalid_input");
            exit();
        }
    }
}
?>