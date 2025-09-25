<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$categories_stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : null;
    $status = $_POST['status'];
    $short_description = trim($_POST['short_description']);
    $long_description = $_POST['long_description'] ?? '';
    $specifications = $_POST['specifications'] ?? '';
    $fabric_details = $_POST['fabric_details'] ?? '';
    $delivery_returns = $_POST['delivery_returns'] ?? '';

    if (empty($name) || empty($slug) || empty($category_id) || empty($price)) {
        $errors[] = "Please fill all required fields (Name, Slug, Category, Price).";
    }
    if (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] != UPLOAD_ERR_OK) {
        $errors[] = "Main image is required.";
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $uploadDir = '../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
            $main_image_name = $slug . '-' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadDir . $main_image_name)) {
                throw new Exception("Failed to upload main image. Check folder permissions.");
            }

            $sql = "INSERT INTO products (name, slug, category_id, price, old_price, status, short_description, long_description, main_image, specifications, fabric_details, delivery_returns) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $slug, $category_id, $price, $old_price, $status, $short_description, $long_description, $main_image_name, $specifications, $fabric_details, $delivery_returns]);
            $product_id = $pdo->lastInsertId();

            if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                $gallery_files = $_FILES['gallery_images'];
                for ($i = 0; $i < count($gallery_files['name']); $i++) {
                    if ($gallery_files['error'][$i] === UPLOAD_ERR_OK) {
                        $gallery_ext = strtolower(pathinfo($gallery_files['name'][$i], PATHINFO_EXTENSION));
                        $gallery_image_name = $slug . '-gallery-' . time() . '-' . $i . '.' . $gallery_ext;
                        if (move_uploaded_file($gallery_files['tmp_name'][$i], $uploadDir . $gallery_image_name)) {
                            $gallery_sql = "INSERT INTO product_gallery (product_id, image_name, alt_text) VALUES (?, ?, ?)";
                            $gallery_stmt = $pdo->prepare($gallery_sql);
                            $gallery_stmt->execute([$product_id, $gallery_image_name, $name]);
                        }
                    }
                }
            }

            $pdo->commit();
            echo "<script>alert('Product added successfully!'); window.location.href = 'view-products.php';</script>";
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <script src="ckeditor/ckeditor.js"></script>
</head>

<body>
    <div id="layout-wrapper">
        <?php require './shared_components/header.php'; ?>
        <?php require './shared_components/navbar.php'; ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Add New Product</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error): ?>
                                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form action="add-product.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3"><label for="productName" class="form-label">Product
                                                        Name</label><input type="text" id="productName" name="name"
                                                        class="form-control" required
                                                        onkeyup="generateSlug(this.value)"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <select name="category_id" class="form-select" required>
                                                        <option value="">Select...</option>
                                                        <?php foreach ($categories as $cat) {
                                                            echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['name']) . "</option>";
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3"><label for="productSlug" class="form-label">URL
                                                        Slug</label><input type="text" id="productSlug" name="slug"
                                                        class="form-control" required></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Price (₹)</label><input
                                                        type="number" name="price" step="0.01" class="form-control"
                                                        required></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Old Price (₹)</label><input
                                                        type="number" name="old_price" step="0.01" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3"><label class="form-label">Main Image</label><input
                                                        type="file" name="main_image" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3"><label class="form-label">Gallery Images</label><input
                                                        type="file" name="gallery_images[]" class="form-control"
                                                        multiple></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Status</label><select
                                                        name="status" class="form-select">
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                    </select></div>
                                            </div>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Short Description</label><textarea
                                                name="short_description" class="form-control" rows="3"></textarea></div>
                                        <div class="mb-3"><label class="form-label">Long Description</label><textarea
                                                name="long_description" id="long_description"></textarea></div>
                                        <div class="mb-3"><label class="form-label">Specifications</label><textarea
                                                name="specifications" id="specifications"></textarea></div>
                                        <div class="mb-3"><label class="form-label">Fabric Details</label><textarea
                                                name="fabric_details" id="fabric_details"></textarea></div>
                                        <div class="mb-3"><label class="form-label">Delivery & Returns</label><textarea
                                                name="delivery_returns" id="delivery_returns"></textarea></div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary me-2">Save Product</button>
                                            <a href="view-products.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script>
        CKEDITOR.replace('long_description');
        CKEDITOR.replace('specifications');
        CKEDITOR.replace('fabric_details');
        CKEDITOR.replace('delivery_returns');

        function generateSlug(value) {
            const slug = value.toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-');
            document.getElementById('productSlug').value = slug;
        }
    </script>
    <?php require './shared_components/scripts.php'; ?>
</body>

</html>