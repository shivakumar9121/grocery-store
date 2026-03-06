<?php
@include 'config.php';
session_start();

// Check admin session
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Fetch product details
if (isset($_GET['update'])) {
    $update_id = $_GET['update'];
    $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $select_product->execute([$update_id]);
    if ($select_product->rowCount() > 0) {
        $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
    } else {
        header('location:admin_products.php');
        exit();
    }
} else {
    header('location:admin_products.php');
    exit();
}

// Handle update form submission
if (isset($_POST['update_product'])) {
    $update_id = $_POST['update_id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
    $unit = filter_var($_POST['unit'], FILTER_SANITIZE_STRING);

    // Update product details
    $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ?, quantity = ?, unit = ? WHERE id = ?");
    $update_product->execute([$name, $category, $details, $price, $quantity, $unit, $update_id]);

    // Handle image update
    if (!empty($_FILES['image']['name'])) {
        $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = 'uploaded_img/' . $image;

        if ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            // Remove old image
            $select_old_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
            $select_old_image->execute([$update_id]);
            $old_image = $select_old_image->fetch(PDO::FETCH_ASSOC)['image'];
            if ($old_image && file_exists('uploaded_img/' . $old_image)) {
                unlink('uploaded_img/' . $old_image);
            }

            // Update image in DB and move new file
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $update_id]);
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Image updated successfully!';
        }
    }

    $message[] = 'Product updated successfully!';
    header('location:admin_products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="update-product">
    <h1 class="title">Update Product</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="update_id" value="<?= $fetch_product['id']; ?>">

        <div class="flex">
            <div class="inputBox">
                <input type="text" name="name" class="box" required value="<?= $fetch_product['name']; ?>">
                <select name="category" class="box" required>
                    <option value="vegetables" <?= $fetch_product['category'] == 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                    <option value="fruits" <?= $fetch_product['category'] == 'fruits' ? 'selected' : ''; ?>>Fruits</option>
                    <option value="meat" <?= $fetch_product['category'] == 'meat' ? 'selected' : ''; ?>>Meat</option>
                    <option value="fish" <?= $fetch_product['category'] == 'fish' ? 'selected' : ''; ?>>Fish</option>
                </select>
            </div>

            <div class="inputBox">
                <input type="number" name="price" class="box" required min="0" value="<?= $fetch_product['price']; ?>">
                <input type="number" name="quantity" class="box" required min="0" value="<?= $fetch_product['quantity']; ?>">
                <select name="unit" class="box" required>
                    <option value="pcs" <?= $fetch_product['unit'] == 'pcs' ? 'selected' : ''; ?>>Pieces (pcs)</option>
                    <option value="kg" <?= $fetch_product['unit'] == 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
                    <option value="g" <?= $fetch_product['unit'] == 'g' ? 'selected' : ''; ?>>Grams (g)</option>
                    <option value="bunch" <?= $fetch_product['unit'] == 'bunch' ? 'selected' : ''; ?>>Bunch</option>
                    <option value="ml" <?= $fetch_product['unit'] == 'ml' ? 'selected' : ''; ?>>Milliliters</option>
                </select>
            </div>

            <div class="inputBox">
                <img src="uploaded_img/<?= $fetch_product['image']; ?>" alt="Current Image" class="image-preview">
                <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
            </div>
        </div>

        <textarea name="details" class="box" required cols="30" rows="10"><?= $fetch_product['details']; ?></textarea>

        <input type="submit" value="Update Product" name="update_product" class="btn">
        <a href="admin_products.php" class="option-btn">Go Back</a>
    </form>
</section>

<script src="js/script.js"></script>

</body>
</html>
