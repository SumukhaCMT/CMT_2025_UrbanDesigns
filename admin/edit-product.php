<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: view-products.php");
    exit();
}

$errors = [];
$uploadDir = '../uploads/products/';

$categories_stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$product_stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product_stmt->execute([$id]);
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

$gallery_stmt = $pdo->prepare("SELECT * FROM product_gallery WHERE product_id = ?");
$gallery_stmt->execute([$id]);
$gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: view-products.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        if (!empty($_POST['delete_gallery'])) {
            $delete_ids = $_POST['delete_gallery'];
            $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
            $img_stmt = $pdo->prepare("SELECT image_name FROM product_gallery WHERE id IN ($placeholders)");
            $img_stmt->execute($delete_ids);
            $images_to_delete = $img_stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($images_to_delete as $img_name) {
                if ($img_name && file_exists($uploadDir . $img_name))
                    unlink($uploadDir . $img_name);
            }
            $del_stmt = $pdo->prepare("DELETE FROM product_gallery WHERE id IN ($placeholders)");
            $del_stmt->execute($delete_ids);
        }

        if (isset($_FILES['add_gallery_images']) && !empty($_FILES['add_gallery_images']['name'][0])) {
            $gallery_files = $_FILES['add_gallery_images'];
            for ($i = 0; $i < count($gallery_files['name']); $i++) {
                if ($gallery_files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($gallery_files['name'][$i], PATHINFO_EXTENSION));
                    $new_gallery_name = $_POST['slug'] . '-gallery-' . time() . '-' . $i . '.' . $ext;
                    if (move_uploaded_file($gallery_files['tmp_name'][$i], $uploadDir . $new_gallery_name)) {
                        $add_sql = "INSERT INTO product_gallery (product_id, image_name, alt_text) VALUES (?, ?, ?)";
                        $add_stmt = $pdo->prepare($add_sql);
                        $add_stmt->execute([$id, $new_gallery_name, $_POST['name']]);
                    }
                }
            }
        }

        $main_image_name = $product['main_image'];
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
            if ($main_image_name && file_exists($uploadDir . $main_image_name))
                unlink($uploadDir . $main_image_name);
            $ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
            $main_image_name = $_POST['slug'] . '-' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadDir . $main_image_name)) {
                throw new Exception("Could not upload new main image.");
            }
        }

        $sql = "UPDATE products SET name=?, slug=?, category_id=?, price=?, old_price=?, status=?, short_description=?, long_description=?, main_image=?, specifications=?, fabric_details=?, delivery_returns=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['slug']),
            $_POST['category_id'],
            $_POST['price'],
            !empty($_POST['old_price']) ? $_POST['old_price'] : null,
            $_POST['status'],
            trim($_POST['short_description']),
            $_POST['long_description'],
            $main_image_name,
            $_POST['specifications'],
            $_POST['fabric_details'],
            $_POST['delivery_returns'],
            $id
        ]);

        $pdo->commit();
        echo "<script>alert('Product updated successfully!'); window.location.href = 'edit-product.php?id=$id';</script>";
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Update failed: " . $e->getMessage();
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
                                <h4 class="mb-sm-0 font-size-18">Edit Product</h4>
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
                                    <form action="edit-product.php?id=<?php echo $id; ?>" method="POST"
                                        enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3"><label class="form-label">Product Name</label><input
                                                        type="text" name="name" class="form-control" required
                                                        value="<?php echo htmlspecialchars($product['name']); ?>"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Category</label><select
                                                        name="category_id" class="form-select" required><?php foreach ($categories as $cat) {
                                                            $selected = ($product['category_id'] == $cat['id']) ? 'selected' : '';
                                                            echo "<option value='{$cat['id']}' $selected>" . htmlspecialchars($cat['name']) . "</option>";
                                                        } ?></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">URL Slug</label><input
                                                        type="text" name="slug" class="form-control" required
                                                        value="<?php echo htmlspecialchars($product['slug']); ?>"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Price (₹)</label><input
                                                        type="number" name="price" step="0.01" class="form-control"
                                                        required
                                                        value="<?php echo htmlspecialchars($product['price']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Old Price (₹)</label><input
                                                        type="number" name="old_price" step="0.01" class="form-control"
                                                        value="<?php echo htmlspecialchars($product['old_price']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3"><label class="form-label">Status</label><select
                                                        name="status" class="form-select">
                                                        <option value="active" <?php if ($product['status'] == 'active')
                                                            echo 'selected'; ?>>Active</option>
                                                        <option value="inactive" <?php if ($product['status'] == 'inactive')
                                                            echo 'selected'; ?>>
                                                            Inactive</option>
                                                    </select></div>
                                            </div>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Short Description</label><textarea
                                                name="short_description" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Long Description</label><textarea
                                                name="long_description"
                                                id="long_description"><?php echo htmlspecialchars($product['long_description']); ?></textarea>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Specifications</label><textarea
                                                name="specifications"
                                                id="specifications"><?php echo htmlspecialchars($product['specifications']); ?></textarea>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Fabric Details</label><textarea
                                                name="fabric_details"
                                                id="fabric_details"><?php echo htmlspecialchars($product['fabric_details']); ?></textarea>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Delivery & Returns</label><textarea
                                                name="delivery_returns"
                                                id="delivery_returns"><?php echo htmlspecialchars($product['delivery_returns']); ?></textarea>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5 class="card-title">Main Image</h5>
                                                <div class="mb-3">
                                                    <label class="form-label">Current Main Image</label>
                                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>"
                                                        class="img-thumbnail" width="150">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Upload New Main Image (Optional)</label>
                                                    <input type="file" name="main_image" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5 class="card-title">Manage Gallery</h5>
                                                <div class="mb-3">
                                                    <label class="form-label">Existing Gallery Images (Select to
                                                        delete)</label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <?php foreach ($gallery_images as $img): ?>
                                                            <div class="text-center">
                                                                <img src="../uploads/products/<?php echo htmlspecialchars($img['image_name']); ?>"
                                                                    class="img-thumbnail" width="100">
                                                                <div class="form-check mt-2"><input class="form-check-input"
                                                                        type="checkbox" name="delete_gallery[]"
                                                                        value="<?php echo $img['id']; ?>"><label
                                                                        class="form-check-label">Delete</label></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Add More Images</label>
                                                    <input type="file" name="add_gallery_images[]" class="form-control"
                                                        multiple>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary me-2">Update Product</button>
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
    <script>
        CKEDITOR.replace('long_description');
        CKEDITOR.replace('specifications');
        CKEDITOR.replace('fabric_details');
        CKEDITOR.replace('delivery_returns');
    </script>
    <?php require './shared_components/scripts.php'; ?>
</body>

</html>