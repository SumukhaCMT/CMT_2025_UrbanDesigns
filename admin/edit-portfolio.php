<?php
// Make sure this path is correct for your admin folder structure
require './shared_components/session.php';
require './shared_components/db.php';

// 1. --- INITIAL DATA FETCHING ---
// Get ID from URL and validate it
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
if (!$id) {
    header("Location: view-portfolio");
    exit();
}

// Fetch the project's main data
$stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo "Project not found.";
    exit();
}

// Fetch existing gallery images for this project
$gallery_stmt = $pdo->prepare("SELECT * FROM portfolio_gallery_images WHERE project_id = ?");
$gallery_stmt->execute([$id]);
$gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for the dropdown
$categories = $pdo->query("SELECT * FROM portfolio_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$files_to_delete_on_success = [];

// Helper function for file uploads
function handleImageUpload($file, $uploadDir, $baseName)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK)
        return ['error' => 'File not uploaded or an upload error occurred.'];
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true))
            return ['error' => 'Failed to create upload directory.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
    if (!in_array($ext, $allowed))
        return ['error' => 'Invalid file type.'];
    $safeBaseName = preg_replace('/[^a-zA-Z0-9-]/', '', $baseName);
    $finalName = $safeBaseName . '-' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $finalName))
        return ['success' => $finalName];
    return ['error' => 'Failed to move the uploaded file.'];
}

// 2. --- FORM SUBMISSION HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve all form inputs
    $project_title = trim($_POST['project_title']);
    $slug = trim($_POST['slug']);
    $category_id = $_POST['category_id'];
    $short_description = trim($_POST['short_description']);
    $client_name = trim($_POST['client_name']);
    $location = trim($_POST['location']);
    $project_year = trim($_POST['project_year']);
    $content_heading = trim($_POST['content_heading']);
    $detailed_content = $_POST['detailed_content'];
    $status = $_POST['status'];

    // Handle List Image Replacement
    $list_image_name = $_POST['existing_list_image'];
    if (isset($_FILES['list_image']) && $_FILES['list_image']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handleImageUpload($_FILES['list_image'], '../uploads/portfolio/', $slug);
        if (isset($upload_result['success'])) {
            $files_to_delete_on_success[] = '../uploads/portfolio/' . $list_image_name; // Queue old file for deletion
            $list_image_name = $upload_result['success']; // Use new filename
        } else {
            $errors[] = 'List Image Error: ' . $upload_result['error'];
        }
    }

    // Handle Detail Image Replacement
    $detail_image_name = $_POST['existing_detail_image'];
    if (isset($_FILES['detail_image']) && $_FILES['detail_image']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handleImageUpload($_FILES['detail_image'], '../uploads/portfolio/', $slug . '-detail');
        if (isset($upload_result['success'])) {
            $files_to_delete_on_success[] = '../uploads/portfolio/' . $detail_image_name; // Queue old file for deletion
            $detail_image_name = $upload_result['success']; // Use new filename
        } else {
            $errors[] = 'Detail Image Error: ' . $upload_result['error'];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // A. Update the main portfolio table
            $update_stmt = $pdo->prepare(
                "UPDATE portfolio SET project_title = ?, slug = ?, category_id = ?, short_description = ?, list_image = ?, detail_image = ?, client_name = ?, location = ?, project_year = ?, content_heading = ?, detailed_content = ?, status = ? WHERE id = ?"
            );
            $update_stmt->execute([
                $project_title,
                $slug,
                $category_id,
                $short_description,
                $list_image_name,
                $detail_image_name,
                $client_name,
                $location,
                $project_year,
                $content_heading,
                $detailed_content,
                $status,
                $id
            ]);

            // B. Delete gallery images that were marked for deletion
            if (isset($_POST['delete_gallery']) && is_array($_POST['delete_gallery'])) {
                $delete_gallery_stmt = $pdo->prepare("DELETE FROM portfolio_gallery_images WHERE id = ?");
                foreach ($_POST['delete_gallery'] as $img_id) {
                    // Fetch filename to delete the file later
                    $img_file_stmt = $pdo->prepare("SELECT image_name FROM portfolio_gallery_images WHERE id = ?");
                    $img_file_stmt->execute([$img_id]);
                    $img_to_delete = $img_file_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($img_to_delete) {
                        $files_to_delete_on_success[] = '../uploads/portfolio/gallery/' . $img_to_delete['image_name'];
                        $delete_gallery_stmt->execute([$img_id]);
                    }
                }
            }

            // C. Add new gallery images
            if (isset($_FILES['gallery_images']) && count($_FILES['gallery_images']['name']) > 0) {
                $gallery_files = $_FILES['gallery_images'];
                $gallery_stmt = $pdo->prepare("INSERT INTO portfolio_gallery_images (project_id, image_name) VALUES (?, ?)");

                for ($i = 0; $i < count($gallery_files['name']); $i++) {
                    if ($gallery_files['error'][$i] === UPLOAD_ERR_NO_FILE)
                        continue;

                    $file_to_upload = ['name' => $gallery_files['name'][$i], 'type' => $gallery_files['type'][$i], 'tmp_name' => $gallery_files['tmp_name'][$i], 'error' => $gallery_files['error'][$i], 'size' => $gallery_files['size'][$i]];
                    $upload_result = handleImageUpload($file_to_upload, '../uploads/portfolio/gallery/', $slug . '-gallery-' . ($i + 1));

                    if (isset($upload_result['success'])) {
                        $gallery_stmt->execute([$id, $upload_result['success']]);
                    } else {
                        throw new Exception("Gallery Image '" . htmlspecialchars($file_to_upload['name']) . "' Error: " . $upload_result['error']);
                    }
                }
            }

            // D. If all database operations were successful, commit them
            $pdo->commit();

            // E. Now, delete the actual files from the server
            foreach ($files_to_delete_on_success as $file_path) {
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            echo "<script>alert('Project updated successfully!'); window.location.href = 'view-portfolio';</script>";
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
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        .gallery-image-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .gallery-image-item img {
            max-width: 150px;
            margin-right: 15px;
        }
    </style>
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
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Edit Portfolio Project:
                                        <?php echo htmlspecialchars($project['project_title']); ?></h4>
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error):
                                                echo "<p class='mb-0'>" . htmlspecialchars($error) . "</p>"; endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form action="edit-portfolio?id=<?php echo $id; ?>" method="POST"
                                        enctype="multipart/form-data">

                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label class="form-label">Project Title
                                                    *</label><input type="text" name="project_title"
                                                    class="form-control" required
                                                    value="<?php echo htmlspecialchars($project['project_title']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3"><label class="form-label">URL Slug
                                                    *</label><input type="text" name="slug" class="form-control"
                                                    required value="<?php echo htmlspecialchars($project['slug']); ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category *</label>
                                            <select name="category_id" class="form-control" required>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($project['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Short Description</label><textarea
                                                name="short_description" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($project['short_description']); ?></textarea>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="active" <?php echo ($project['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($project['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Project Details Box</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><label class="form-label">Client
                                                    Name</label><input type="text" name="client_name"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($project['client_name']); ?>">
                                            </div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Location</label><input
                                                    type="text" name="location" class="form-control"
                                                    value="<?php echo htmlspecialchars($project['location']); ?>"></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Project
                                                    Year</label><input type="number" name="project_year"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($project['project_year']); ?>"
                                                    placeholder="YYYY"></div>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Manage Images</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Current List Image</label>
                                            <div><img
                                                    src="../uploads/portfolio/<?php echo htmlspecialchars($project['list_image']); ?>"
                                                    alt="List Image" style="max-width: 200px;"></div>
                                            <label class="form-label mt-2">Upload New List Image (optional)</label>
                                            <input type="file" name="list_image" class="form-control">
                                            <input type="hidden" name="existing_list_image"
                                                value="<?php echo htmlspecialchars($project['list_image']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Current Detail Image</label>
                                            <div><img
                                                    src="../uploads/portfolio/<?php echo htmlspecialchars($project['detail_image']); ?>"
                                                    alt="Detail Image" style="max-width: 200px;"></div>
                                            <label class="form-label mt-2">Upload New Detail Image (optional)</label>
                                            <input type="file" name="detail_image" class="form-control">
                                            <input type="hidden" name="existing_detail_image"
                                                value="<?php echo htmlspecialchars($project['detail_image']); ?>">
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Manage Gallery Images</h5>
                                        <?php if (!empty($gallery_images)): ?>
                                            <p>Check the box to delete an existing gallery image upon saving.</p>
                                            <?php foreach ($gallery_images as $img): ?>
                                                <div class="gallery-image-item d-flex align-items-center">
                                                    <img src="../uploads/portfolio/gallery/<?php echo htmlspecialchars($img['image_name']); ?>"
                                                        alt="Gallery Image">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="delete_gallery[]"
                                                            value="<?php echo $img['id']; ?>"
                                                            id="del_img_<?php echo $img['id']; ?>">
                                                        <label class="form-check-label" for="del_img_<?php echo $img['id']; ?>">
                                                            Mark for Deletion</label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No gallery images have been uploaded for this project yet.</p>
                                        <?php endif; ?>
                                        <div class="mb-3 mt-3">
                                            <label class="form-label">Upload New Gallery Images</label>
                                            <input type="file" name="gallery_images[]" class="form-control" multiple>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Detail Page Content</h5>
                                        <div class="mb-3"><label class="form-label">Content Heading *</label><input
                                                type="text" name="content_heading" class="form-control" required
                                                value="<?php echo htmlspecialchars($project['content_heading']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detailed Content</label>
                                            <textarea name="detailed_content"
                                                id="detailed_content"><?php echo htmlspecialchars($project['detailed_content']); ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Update Project</button>
                                        <a href="view-portfolio" class="btn btn-secondary">Cancel</a>
                                    </form>
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
        CKEDITOR.replace('detailed_content');
    </script>
</body>

</html>