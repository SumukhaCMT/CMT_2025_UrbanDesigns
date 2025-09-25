<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = !empty($_POST['name']) ? trim($_POST['name']) : $errors['name'] = "Category name is required.";
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;

    // Clean and format slug properly - FIXED VERSION
    if (!empty($slug)) {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug); // Allow hyphens and spaces
        $slug = preg_replace('/\s+/', '-', $slug); // Replace spaces with hyphens
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single
        $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    } else if (!empty($name)) {
        // Auto-generate slug from name
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s]/', '', $slug); // Remove special chars but keep spaces
        $slug = preg_replace('/\s+/', '-', $slug); // Replace spaces with hyphens
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single
        $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    }

    if (empty($errors)) {
        try {
            // Check if slug already exists
            $check_stmt = $pdo->prepare("SELECT id FROM project_categories WHERE slug = ?");
            $check_stmt->execute([$slug]);

            if ($check_stmt->fetch()) {
                $errors['slug'] = "This slug already exists. Please choose a different one.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO project_categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $sort_order]);

                echo "<script>alert('Category added successfully!'); window.location.href = 'manage_categories.php';</script>";
                exit();
            }
        } catch (PDOException $e) {
            $errors['general'] = "Error adding category: " . $e->getMessage();
        }
    }
}

// Handle edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = intval($_POST['category_id']);
    $name = !empty($_POST['edit_name']) ? trim($_POST['edit_name']) : $errors['edit_name'] = "Category name is required.";
    $slug = !empty($_POST['edit_slug']) ? trim($_POST['edit_slug']) : '';
    $description = isset($_POST['edit_description']) ? trim($_POST['edit_description']) : '';
    $sort_order = isset($_POST['edit_sort_order']) ? intval($_POST['edit_sort_order']) : 0;
    $status = isset($_POST['edit_status']) ? $_POST['edit_status'] : 'active';

    // Clean and format slug properly - FIXED VERSION
    if (!empty($slug)) {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug); // Allow hyphens and spaces
        $slug = preg_replace('/\s+/', '-', $slug); // Replace spaces with hyphens
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single
        $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    } else if (!empty($name)) {
        // Auto-generate slug from name
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s]/', '', $slug); // Remove special chars but keep spaces
        $slug = preg_replace('/\s+/', '-', $slug); // Replace spaces with hyphens
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single
        $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    }

    if (empty($errors)) {
        try {
            // Check if slug already exists for other categories
            $check_stmt = $pdo->prepare("SELECT id FROM project_categories WHERE slug = ? AND id != ?");
            $check_stmt->execute([$slug, $category_id]);

            if ($check_stmt->fetch()) {
                $errors['edit_slug'] = "This slug already exists. Please choose a different one.";
            } else {
                $stmt = $pdo->prepare("UPDATE project_categories SET name = ?, slug = ?, description = ?, sort_order = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $sort_order, $status, $category_id]);

                echo "<script>alert('Category updated successfully!'); window.location.href = 'manage_categories.php';</script>";
                exit();
            }
        } catch (PDOException $e) {
            $errors['general'] = "Error updating category: " . $e->getMessage();
        }
    }
}

// Handle delete category
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    try {
        // Check if category has projects
        $check_stmt = $pdo->prepare("SELECT COUNT(*) as project_count FROM projects WHERE category_id = ?");
        $check_stmt->execute([$delete_id]);
        $result = $check_stmt->fetch();

        if ($result['project_count'] > 0) {
            echo "<script>alert('Cannot delete category! It has " . $result['project_count'] . " project(s) assigned to it. Please move or delete the projects first.');</script>";
        } else {
            $stmt = $pdo->prepare("DELETE FROM project_categories WHERE id = ?");
            $stmt->execute([$delete_id]);

            echo "<script>alert('Category deleted successfully!'); window.location.href = 'manage_categories.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting category: " . $e->getMessage() . "');</script>";
    }
}

// Handle status toggle
if (isset($_POST['toggle_status_id'])) {
    $category_id = intval($_POST['toggle_status_id']);
    $current_status = $_POST['current_status'];
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';

    $stmt = $pdo->prepare("UPDATE project_categories SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $category_id]);

    echo "<script>alert('Category status updated!'); window.location.href = 'manage_categories.php';</script>";
    exit();
}

// Fetch all categories
$stmt = $pdo->prepare("SELECT pc.*, COUNT(p.id) as project_count FROM project_categories pc LEFT JOIN projects p ON pc.id = p.category_id GROUP BY pc.id ORDER BY pc.sort_order ASC, pc.created_at DESC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
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
                    <!-- Breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Manage Categories</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="view_projects.php">Projects</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Manage Categories</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->
                </div>

                <!-- Main content -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Display errors if any -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Add New Category Form -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Add New Category</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="" class="custom-validation">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label" for="name">Category Name *</label>
                                                    <input type="text" name="name" id="name" class="form-control"
                                                        minlength="2" maxlength="100" required
                                                        placeholder="e.g., Life Size Aircraft Models">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label" for="slug">Slug *</label>
                                                    <input type="text" name="slug" id="slug" class="form-control"
                                                        minlength="2" maxlength="100"
                                                        placeholder="e.g., life-size-aircraft-models">
                                                    <small class="text-muted">Spaces will be converted to hyphens
                                                        automatically</small>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label" for="description">Description</label>
                                                    <input type="text" name="description" id="description"
                                                        class="form-control" maxlength="255"
                                                        placeholder="Brief description of the category">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label" for="sort_order">Sort Order</label>
                                                    <input type="number" name="sort_order" id="sort_order"
                                                        class="form-control" value="0" min="0">
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" name="add_category" class="btn btn-primary">
                                                    <i class="bx bx-plus"></i> Add Category
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Categories Table -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">All Categories</h5>
                                    <a href="view_projects.php" class="btn btn-outline-primary">
                                        <i class="bx bx-arrow-back"></i> Back to Projects
                                    </a>
                                </div>

                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Category Name</th>
                                                    <th>Slug</th>
                                                    <th>Description</th>
                                                    <th>Projects Count</th>
                                                    <th>Status</th>
                                                    <th>Sort Order</th>
                                                    <th>Created</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($categories as $category) {
                                                    $status_badge = $category['status'] === 'active' ? 'badge-success' : 'badge-secondary';
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                        </td>
                                                        <td><code><?php echo htmlspecialchars($category['slug']); ?></code>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                        <td>
                                                            <span
                                                                class="badge badge-info"><?php echo $category['project_count']; ?>
                                                                projects</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $status_badge; ?>">
                                                                <?php echo ucfirst($category['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $category['sort_order']; ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($category['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editCategoryModal"
                                                                    onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)"
                                                                    title="Edit Category">
                                                                    <i class="bx bxs-pencil"></i>
                                                                </button>

                                                                <form method="post" class="d-inline"
                                                                    onsubmit="return confirmStatusToggle();">
                                                                    <input type="hidden" name="toggle_status_id"
                                                                        value="<?php echo $category['id']; ?>">
                                                                    <input type="hidden" name="current_status"
                                                                        value="<?php echo $category['status']; ?>">
                                                                    <button type="submit"
                                                                        class="btn btn-sm <?php echo $category['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>"
                                                                        title="Toggle Status">
                                                                        <i
                                                                            class="bx <?php echo $category['status'] === 'active' ? 'bx-pause' : 'bx-play'; ?>"></i>
                                                                    </button>
                                                                </form>

                                                                <?php if ($category['project_count'] == 0): ?>
                                                                    <form method="post" class="d-inline"
                                                                        onsubmit="return confirmDelete();">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $category['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                            title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                                        disabled title="Cannot delete - has projects">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $sl_no++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Category Modal -->
                <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" id="edit_category_id">
                                    <div class="mb-3">
                                        <label for="edit_name" class="form-label">Category Name *</label>
                                        <input type="text" name="edit_name" id="edit_name" class="form-control"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_slug" class="form-label">Slug *</label>
                                        <input type="text" name="edit_slug" id="edit_slug" class="form-control"
                                            required>
                                        <small class="text-muted">Spaces will be converted to hyphens
                                            automatically</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_description" class="form-label">Description</label>
                                        <textarea name="edit_description" id="edit_description" class="form-control"
                                            rows="2"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_sort_order" class="form-label">Sort Order</label>
                                            <input type="number" name="edit_sort_order" id="edit_sort_order"
                                                class="form-control" min="0">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_status" class="form-label">Status</label>
                                            <select name="edit_status" id="edit_status" class="form-control">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="edit_category" class="btn btn-primary">Update
                                        Category</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function editCategory(category) {
                document.getElementById('edit_category_id').value = category.id;
                document.getElementById('edit_name').value = category.name;
                document.getElementById('edit_slug').value = category.slug;
                document.getElementById('edit_description').value = category.description || '';
                document.getElementById('edit_sort_order').value = category.sort_order;
                document.getElementById('edit_status').value = category.status;
            }

            function confirmDelete() {
                return confirm('Are you sure you want to delete this category? This action cannot be undone.');
            }

            function confirmStatusToggle() {
                return confirm('Are you sure you want to change the status of this category?');
            }

            // FIXED: Auto-generate slug from name - properly handles spaces to hyphens
            document.getElementById('name').addEventListener('input', function () {
                const slugInput = document.getElementById('slug');
                if (!slugInput.value || slugInput.value === '') {
                    let slug = this.value.toLowerCase()
                        .replace(/[^a-z0-9\s]/g, '')  // Remove special chars but keep spaces
                        .replace(/\s+/g, '-')         // Replace spaces with hyphens
                        .replace(/-+/g, '-')          // Replace multiple hyphens with single
                        .replace(/^-|-$/g, '');       // Remove leading/trailing hyphens
                    slugInput.value = slug;
                }
            });

            // FIXED: Also handle manual slug input - convert spaces to hyphens on blur
            document.getElementById('slug').addEventListener('blur', function () {
                let slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s\-]/g, '')  // Allow hyphens and spaces
                    .replace(/\s+/g, '-')           // Replace spaces with hyphens
                    .replace(/-+/g, '-')            // Replace multiple hyphens with single
                    .replace(/^-|-$/g, '');         // Remove leading/trailing hyphens
                this.value = slug;
            });

            // FIXED: Handle edit slug field the same way
            document.getElementById('edit_slug').addEventListener('blur', function () {
                let slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s\-]/g, '')  // Allow hyphens and spaces
                    .replace(/\s+/g, '-')           // Replace spaces with hyphens
                    .replace(/-+/g, '-')            // Replace multiple hyphens with single
                    .replace(/^-|-$/g, '');         // Remove leading/trailing hyphens
                this.value = slug;
            });
        </script>

        <?php require './shared_components/footer.php'; ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>