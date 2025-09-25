<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    header('Location: categories.php');
    exit;
}

// fetch
$stmt = $pdo->prepare("SELECT name FROM categories WHERE id=?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) {
    header('Location: categories.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    if ($name === '')
        $errors[] = "Name cannot be empty.";
    if (!$errors) {
        $upd = $pdo->prepare("UPDATE categories SET name=? WHERE id=?");
        $upd->execute([$name, $id]);
        header('Location: categories.php?success=cat_updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>Edit Category</title>
</head>

<body>
    <div id="layout-wrapper">
        <?php
        require './shared_components/header.php';
        require './shared_components/navbar.php';
        ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <h4>Edit Category</h4>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $e): ?>
                                <div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="custom-validation">
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input name="category_name" class="form-control" required minlength="2"
                                value="<?= htmlspecialchars($cat['name']) ?>">
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <a href="categories.php" class="btn btn-secondary ms-2">Cancel</a>
                    </form>

                </div>
            </div>
        </div>
        <?php
        require './shared_components/footer.php';
        require './shared_components/scripts.php';
        ?>
    </div>
</body>

</html>