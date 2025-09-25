<?php
require './shared_components/session.php';
require './shared_components/db.php';

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $pdo->beginTransaction();

        // Fetch all images associated with the project to delete files
        $stmt_img = $pdo->prepare("SELECT list_image, detail_image FROM portfolio WHERE id = ?");
        $stmt_img->execute([$delete_id]);
        $project_images = $stmt_img->fetch(PDO::FETCH_ASSOC);

        $stmt_gallery = $pdo->prepare("SELECT image_name FROM portfolio_gallery_images WHERE project_id = ?");
        $stmt_gallery->execute([$delete_id]);
        $gallery_images = $stmt_gallery->fetchAll(PDO::FETCH_ASSOC);

        // Delete main images
        if ($project_images) {
            if (file_exists('../uploads/portfolio/' . $project_images['list_image']))
                unlink('../uploads/portfolio/' . $project_images['list_image']);
            if (file_exists('../uploads/portfolio/' . $project_images['detail_image']))
                unlink('../uploads/portfolio/' . $project_images['detail_image']);
        }
        // Delete gallery images
        foreach ($gallery_images as $img) {
            if (file_exists('../uploads/portfolio/gallery/' . $img['image_name']))
                unlink('../uploads/portfolio/gallery/' . $img['image_name']);
        }

        // Deleting the portfolio record will also delete gallery images due to ON DELETE CASCADE
        $stmt_delete = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
        $stmt_delete->execute([$delete_id]);

        $pdo->commit();
        echo "<script>alert('Project deleted successfully!'); window.location.href = 'view-portfolio';</script>";
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting project: " . $e->getMessage() . "');</script>";
    }
}

// Fetch all projects with category name
$projects = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM portfolio p 
    JOIN portfolio_categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'shared_components/head.php'; ?>
</head>

<body>
    <div id="layout-wrapper">
        <?php require 'shared_components/header.php'; ?>
        <?php require 'shared_components/navbar.php'; ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Manage Portfolio</h4>
                                <div class="page-title-right">
                                    <a href="add-portfolio" class="btn btn-primary"><i class="bx bx-plus"></i> Add New
                                        Project</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>List Image</th>
                                                    <th>Project Title</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Created On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projects as $index => $project): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><img src="../uploads/portfolio/<?php echo htmlspecialchars($project['list_image']); ?>"
                                                                alt="image" width="80"></td>
                                                        <td><strong><?php echo htmlspecialchars($project['project_title']); ?></strong>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($project['category_name']); ?></td>
                                                        <td><span
                                                                class="badge <?php echo $project['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo ucfirst($project['status']); ?></span>
                                                        </td>
                                                        <td><?php echo date('d-M-Y', strtotime($project['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="edit-portfolio?id=<?php echo $project['id']; ?>"
                                                                    class="btn btn-sm btn-primary" title="Edit"><i
                                                                        class="bx bxs-pencil"></i></a>
                                                                <form method="POST" class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this project?');">
                                                                    <input type="hidden" name="delete_id"
                                                                        value="<?php echo $project['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                                        title="Delete"><i class="bx bx-trash"></i></button>
                                                                </form>
                                                            </div>
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
            <?php require 'shared_components/footer.php'; ?>
        </div>
    </div>
    <?php require 'shared_components/scripts.php'; ?>
</body>

</html>