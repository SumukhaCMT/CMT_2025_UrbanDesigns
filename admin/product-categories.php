<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$edit_mode = false;
$category_to_edit = ['id' => '', 'name' => '', 'slug' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $id = $_POST['id'] ?? null;

    if (!empty($name) && !empty($slug)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE product_categories SET name = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
        }
        echo "<script>alert('Category saved successfully!'); window.location.href = 'product-categories.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Category deleted successfully!'); window.location.href = 'product-categories.php';</script>";
    exit();
}

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);
    $category_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
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
                                <h4 class="mb-sm-0 font-size-18">Manage Product Categories</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">
                                        <?php echo $edit_mode ? 'Edit Category' : 'Add New Category'; ?>
                                    </h4>
                                    <form action="product-categories.php" method="POST">
                                        <input type="hidden" name="id"
                                            value="<?php echo htmlspecialchars($category_to_edit['id']); ?>">
                                        <div class="mb-3">
                                            <label for="categoryName" class="form-label">Category Name</label>
                                            <input type="text" id="categoryName" name="name" class="form-control"
                                                required
                                                value="<?php echo htmlspecialchars($category_to_edit['name']); ?>"
                                                onkeyup="generateSlug(this.value)">
                                        </div>
                                        <div class="mb-3">
                                            <label for="categorySlug" class="form-label">URL Slug</label>
                                            <input type="text" id="categorySlug" name="slug" class="form-control"
                                                required placeholder="e.g., furniture"
                                                value="<?php echo htmlspecialchars($category_to_edit['slug']); ?>">
                                        </div>
                                        <div>
                                            <button type="submit" name="submit_category"
                                                class="btn btn-primary"><?php echo $edit_mode ? 'Update Category' : 'Add Category'; ?></button>
                                            <?php if ($edit_mode): ?>
                                                <a href="product-categories.php" class="btn btn-secondary">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Existing Categories</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Slug</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categories as $index => $cat): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                                        <td>
                                                            <a href="product-categories.php?edit_id=<?php echo $cat['id']; ?>"
                                                                class="btn btn-sm btn-primary"><i
                                                                    class="bx bxs-pencil"></i></a>
                                                            <form action="product-categories.php" method="POST"
                                                                class="d-inline"
                                                                onsubmit="return confirm('Are you sure? This cannot be undone.');">
                                                                <input type="hidden" name="delete_id"
                                                                    value="<?php echo $cat['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger"><i
                                                                        class="bx bx-trash"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script>
        function generateSlug(value) {
            const slug = value.toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-');
            document.getElementById('categorySlug').value = slug;
        }
    </script>
</body>

</html>