<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

$errors = [];
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    // Add Category
    if ($formType === 'add-cat') {
        $name = trim($_POST['category_name'] ?? '');
        if ($name === '') {
            $errors[] = "Category name cannot be empty.";
        }
        if (empty($errors)) {
            try {
                $pdo->prepare("INSERT INTO categories (name) VALUES (:name)")
                    ->execute([':name' => $name]);
                header("Location: categories.php?success=cat_added");
                exit;
            } catch (PDOException $e) {
                $errors[] = $e->errorInfo[1] === 1062
                    ? "That Insurance already exists."
                    : "Database error: " . $e->getMessage();
            }
        }
    }

    // Add Subcategory
    if ($formType === 'add-subcat') {
        $cid = $_POST['parent_id'] ?? '';
        $sname = trim($_POST['subcategory_name'] ?? '');
        if (!ctype_digit($cid)) {
            $errors[] = "Please select a Insurance Name.";
        }
        if ($sname === '') {
            $errors[] = "Product name cannot be empty.";
        }
        if (empty($errors)) {
            try {
                $pdo->prepare("
                  INSERT INTO subcategories (category_id, name)
                  VALUES (:cid, :name)
                ")->execute([':cid' => $cid, ':name' => $sname]);
                header("Location: categories.php?success=subcat_added");
                exit;
            } catch (PDOException $e) {
                $errors[] = $e->errorInfo[1] === 1062
                    ? "That Product Name already exists under this Insurance."
                    : "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch ALL categories (not just those with subcategory_settings)
$allCats = $pdo
    ->query("
      SELECT c.id,
             c.name,
             c.created_at
      FROM categories c
      ORDER BY c.name
    ")
    ->fetchAll();

// Fetch ALL subcategories (not just those with subcategory_settings)
$allSubcats = $pdo
    ->query("
      SELECT sc.id,
             c.name    AS category,
             sc.name   AS subcategory,
             sc.created_at,
             CASE WHEN ss.id IS NOT NULL THEN 'Yes' ELSE 'No' END AS has_policy
      FROM subcategories sc
      JOIN categories c ON c.id = sc.category_id
      LEFT JOIN subcategory_settings ss ON ss.subcategory_id = sc.id
      ORDER BY c.name, sc.name
    ")
    ->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>Policy Creation</title>
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

                    <!-- Page Title & Breadcrumb -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="font-size-18">Policy Creation</h4>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">

                            <!-- Success Alerts -->
                            <?php if ($success === 'cat_added'): ?>
                                <div class="alert alert-success">Insurance added successfully.</div>
                            <?php elseif ($success === 'subcat_added'): ?>
                                <div class="alert alert-success">Product added successfully.</div>
                            <?php endif; ?>

                            <!-- Validation Errors -->
                            <?php if ($errors): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $e): ?>
                                        <div><?= htmlspecialchars($e) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Add Category Form -->
                            <form method="post" class="custom-validation mb-4">
                                <input type="hidden" name="form_type" value="add-cat">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Insurance Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="category_name" class="form-control" required
                                            minlength="2" placeholder="Enter Insurance name">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Add Insurance
                                </button>
                            </form>

                            <hr>

                            <!-- Add Subcategory Form -->
                            <form method="post" class="custom-validation mb-4">
                                <input type="hidden" name="form_type" value="add-subcat">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Insurance Name <span
                                                class="text-danger">*</span></label>
                                        <select name="parent_id" class="form-control" required>
                                            <option value="">-- Select Insurance --</option>
                                            <?php foreach ($allCats as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="subcategory_name" class="form-control" required
                                            minlength="2" placeholder="Enter Product name">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Add Product
                                </button>
                            </form>

                            <hr>

                            <!-- Categories List -->
                            <h5>Insurance List</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>slno</th>
                                            <th>Name</th>
                                            <th>Created On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allCats as $c): ?>
                                            <tr>
                                                <td><?= $c['id'] ?></td>
                                                <td><?= htmlspecialchars($c['name']) ?></td>
                                                <td><?= $c['created_at'] ?></td>
                                                <td>
                                                    <a href="edit_category.php?id=<?= $c['id'] ?>"
                                                        class="btn btn-sm btn-primary me-1">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </a>
                                                    <a href="delete_category.php?id=<?= $c['id'] ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this Insurance and all its products?')">
                                                        <i class="bx bx-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <hr>
                            <!-- Subcategories List -->
                            <h5>Products List</h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>slno</th>
                                            <th>Insurance</th>
                                            <th>Product</th>
                                            <th>Has Policy</th>
                                            <th>Created On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allSubcats as $s): ?>
                                            <tr>
                                                <td><?= $s['id'] ?></td>
                                                <td><?= htmlspecialchars($s['category']) ?></td>
                                                <td><?= htmlspecialchars($s['subcategory']) ?></td>
                                                <td>
                                                    <span
                                                        class="badge <?= $s['has_policy'] === 'Yes' ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= $s['has_policy'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $s['created_at'] ?></td>
                                                <td>
                                                    <a href="edit_subcategory.php?id=<?= $s['id'] ?>"
                                                        class="btn btn-sm btn-primary me-1">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </a>
                                                    <a href="delete_subcategory.php?id=<?= $s['id'] ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this product?')">
                                                        <i class="bx bx-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- /.card -->

                </div><!-- /.container-fluid -->
            </div><!-- /.page-content -->
        </div><!-- /.main-content -->

        <?php
        require './shared_components/footer.php';
        require './shared_components/scripts.php';
        ?>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/pages/form-validation.init.js"></script>
    </div><!-- /#layout-wrapper -->
</body>

</html>