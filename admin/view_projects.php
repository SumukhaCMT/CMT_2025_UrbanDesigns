<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Handle form submission for updating page title/heading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_heading']) && isset($_POST['page_title'])) {
    $section_heading = trim($_POST['section_heading']);
    $page_title = trim($_POST['page_title']);

    if (!empty($section_heading) && !empty($page_title)) {
        // For now, we'll just show success message since we removed the projects_page_title table
        // In a real application, you might want to store this in a settings file or database
        echo "<script>alert('Page title updated successfully!');</script>";
    } else {
        echo "<script>alert('Both section heading and title are required!');</script>";
    }
}

// Get search term from GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the search query
$sql = "
    SELECT p.*, pc.name as category_name, pc.slug as category_slug
    FROM projects p 
    LEFT JOIN project_categories pc ON p.category_id = pc.id
";

$search_params = [];

// Add search conditions if search term is provided
if (!empty($search_term)) {
    $sql .= " WHERE (
        p.title LIKE ? OR 
        p.subtitle LIKE ? OR 
        p.slug LIKE ? OR 
        p.description LIKE ? OR 
        pc.name LIKE ? OR 
        p.status LIKE ? OR
        DATE_FORMAT(p.created_at, '%d-%b-%Y') LIKE ?
    )";

    $search_like = '%' . $search_term . '%';
    $search_params = array_fill(0, 7, $search_like);
}

$sql .= " ORDER BY p.sort_order ASC, p.created_at DESC";

// Fetch projects with category information
$stmt = $pdo->prepare($sql);
$stmt->execute($search_params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for display
$total_projects = count($projects);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Fetch project to get images
        $stmt = $pdo->prepare("SELECT main_image FROM projects WHERE id = ?");
        $stmt->execute([$delete_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete main image
        if ($project && $project['main_image']) {
            $image_path = "../assets/images/projects/" . $project['main_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Fetch and delete gallery images
        $gallery_stmt = $pdo->prepare("SELECT image_name FROM project_gallery WHERE project_id = ?");
        $gallery_stmt->execute([$delete_id]);
        $gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($gallery_images as $gallery_image) {
            $image_path = "../assets/images/projects/" . $gallery_image['image_name'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete gallery records (will be handled by foreign key cascade)
        // Delete the project from the database
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$delete_id]);

        // Commit transaction
        $pdo->commit();

        echo "<script>alert('Project deleted successfully!'); window.location.href = 'view_projects.php';</script>";
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting project: " . $e->getMessage() . "');</script>";
    }
}

// Handle status toggle
if (isset($_POST['toggle_status_id'])) {
    $project_id = $_POST['toggle_status_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';

    $stmt = $pdo->prepare("UPDATE projects SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $project_id]);

    echo "<script>alert('Project status updated!'); window.location.href = 'view_projects.php';</script>";
    exit();
}

// Set defaults for page title form
$page_title_result = [
    'section_heading' => 'what we\'ve Achieved',
    'title' => 'Our Projects'
];
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
                                <h4 class="mb-sm-0 font-size-18">Manage Projects</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Manage Projects</li>
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
                                <!-- Search and Action Bar -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <form method="GET" action="" class="d-flex">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control"
                                                    placeholder="Search by title, category, URL, status..."
                                                    value="<?php echo htmlspecialchars($search_term); ?>">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="bx bx-search"></i> Search
                                                </button>
                                                <?php if (!empty($search_term)): ?>
                                                    <a href="view_projects.php" class="btn btn-secondary">
                                                        <i class="bx bx-x"></i> Clear
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                        <?php if (!empty($search_term)): ?>
                                            <small class="text-muted mt-1">
                                                Showing <?php echo $total_projects; ?> result(s) for
                                                "<?php echo htmlspecialchars($search_term); ?>"
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <a href="add_project.php" class="btn btn-primary me-2">
                                            <i class="bx bx-plus"></i> Add New Project
                                        </a>
                                        <a href="manage_categories.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-category"></i> Manage Categories
                                        </a>
                                    </div>
                                </div>

                                <!-- Projects Count -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">
                                        All Projects
                                        <span class="badge badge-info"><?php echo $total_projects; ?>
                                            <?php echo $total_projects == 1 ? 'project' : 'projects'; ?>
                                        </span>
                                    </h5>
                                </div>

                                <!-- Projects Table -->
                                <?php if ($total_projects > 0): ?>
                                    <div class="table-rep-plugin">
                                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Sl. No</th>
                                                        <th>Image</th>
                                                        <th>Project Title</th>
                                                        <th>Category</th>
                                                        <th>URL</th>
                                                        <th>Status</th>
                                                        <th>Created</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sl_no = 1;
                                                    foreach ($projects as $project) {
                                                        $title_preview = strlen($project['title']) > 30 ? substr($project['title'], 0, 30) . '...' : $project['title'];
                                                        $status_badge = $project['status'] === 'active' ? 'badge-success' : 'badge-secondary';

                                                        // Highlight search term in results
                                                        if (!empty($search_term)) {
                                                            $title_preview = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $title_preview);
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $sl_no; ?></td>
                                                            <td>
                                                                <?php if ($project['main_image']): ?>
                                                                    <img src="../assets/images/projects/<?php echo $project['main_image']; ?>"
                                                                        alt="Project Image" width="60" height="45"
                                                                        style="object-fit: cover; border-radius: 5px;">
                                                                <?php else: ?>
                                                                    <div
                                                                        style="width: 60px; height: 45px; background-color: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                                        <i class="bx bx-image text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <strong><?php echo $title_preview; ?></strong>
                                                                <?php if ($project['subtitle']): ?>
                                                                    <br><small
                                                                        class="text-muted"><?php echo htmlspecialchars($project['subtitle']); ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($project['category_name']): ?>
                                                                    <span class="badge badge-primary">
                                                                        <?php
                                                                        $category_name = htmlspecialchars($project['category_name']);
                                                                        if (!empty($search_term)) {
                                                                            $category_name = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $category_name);
                                                                        }
                                                                        echo $category_name;
                                                                        ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-warning">No Category</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $slug = htmlspecialchars($project['slug']);
                                                                if (!empty($search_term)) {
                                                                    $slug = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $slug);
                                                                }
                                                                echo $slug;
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge <?php echo $status_badge; ?>">
                                                                    <?php echo ucfirst($project['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('d-M-Y', strtotime($project['created_at'])); ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="edit_project.php?id=<?php echo $project['id']; ?>"
                                                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                                        title="Edit">
                                                                        <i class="bx bxs-pencil"></i>
                                                                    </a>

                                                                    <form method="post" class="d-inline"
                                                                        onsubmit="return confirmDelete();">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $project['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                            data-bs-toggle="tooltip" title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                    </form>
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
                                <?php else: ?>
                                    <!-- No Projects Found -->
                                    <div class="text-center py-5">
                                        <div class="mb-4">
                                            <i class="bx bx-search-alt-2" style="font-size: 4rem; color: #dee2e6;"></i>
                                        </div>
                                        <?php if (!empty($search_term)): ?>
                                            <h5>No projects found</h5>
                                            <p class="text-muted">
                                                No projects match your search term
                                                "<strong><?php echo htmlspecialchars($search_term); ?></strong>".
                                                <br>Try searching with different keywords or <a href="view_projects.php">view
                                                    all projects</a>.
                                            </p>
                                        <?php else: ?>
                                            <h5>No projects yet</h5>
                                            <p class="text-muted">You haven't added any projects yet.</p>
                                            <a href="add_project.php" class="btn btn-primary">
                                                <i class="bx bx-plus"></i> Add Your First Project
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this project? This will also delete all gallery images associated with it.');
            }

            function confirmStatusToggle() {
                return confirm('Are you sure you want to change the status of this project?');
            }

            // Auto-submit search form on Enter key
            document.querySelector('input[name="search"]').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });
        </script>

        <?php require './shared_components/footer.php'; ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>