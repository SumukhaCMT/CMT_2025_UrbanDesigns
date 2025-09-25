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
$stmt = $pdo->prepare("
  SELECT category_id,name 
    FROM subcategories 
   WHERE id=?
");
$stmt->execute([$id]);
$sub = $stmt->fetch();
if (!$sub) {
    header('Location: categories.php');
    exit;
}

$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid = $_POST['parent_id'] ?? '';
    $name = trim($_POST['subcategory_name'] ?? '');
    if (!ctype_digit($cid))
        $errors[] = "Select parent.";
    if ($name === '')
        $errors[] = "Name cannot be empty.";
    if (!$errors) {
        $upd = $pdo->prepare("
          UPDATE subcategories 
             SET category_id=?,name=?
           WHERE id=?
        ");
        $upd->execute([$cid, $name, $id]);
        header('Location: categories.php?success=subcat_updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>Edit Subcategory</title>
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

                    <h4>Edit Subcategory</h4>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $e): ?>
                                <div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="custom-validation">
                        <div class="mb-3">
                            <label class="form-label">Parent Category *</label>
                            <select name="parent_id" class="form-control" required>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $sub['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subcategory Name *</label>
                            <input name="subcategory_name" class="form-control" required minlength="2"
                                value="<?= htmlspecialchars($sub['name']) ?>">
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