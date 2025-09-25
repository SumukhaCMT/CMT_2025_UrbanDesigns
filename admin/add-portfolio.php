<?php
// Make sure this path is correct for your admin folder structure
require 'shared_components/session.php';
require 'shared_components/db.php';

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT * FROM portfolio_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$errors = [];

// Helper function for file uploads
function handleImageUpload($file, $uploadDir, $baseName)
{
    // Check for upload errors
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File not uploaded or an upload error occurred.'];
    }

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return ['error' => 'Failed to create upload directory.'];
        }
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file type. Allowed types are: ' . implode(', ', $allowed)];
    }

    // Sanitize basename and create final name
    $safeBaseName = preg_replace('/[^a-zA-Z0-9-]/', '', $baseName);
    $finalName = $safeBaseName . '-' . time() . '.' . $ext;

    if (move_uploaded_file($file['tmp_name'], $uploadDir . $finalName)) {
        return ['success' => $finalName];
    }

    return ['error' => 'Failed to move the uploaded file.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //
    // --- THIS IS THE CORRECTED AND COMPLETE SECTION ---
    //
    // Sanitize and retrieve all form inputs into variables
    $project_title = trim($_POST['project_title']);
    $slug = trim($_POST['slug']);
    $category_id = $_POST['category_id'];
    $short_description = trim($_POST['short_description']);
    $client_name = trim($_POST['client_name']);
    $location = trim($_POST['location']);
    $project_year = trim($_POST['project_year']);
    $content_heading = trim($_POST['content_heading']);
    $detailed_content = $_POST['detailed_content']; // CKEditor content doesn't need trim

    // Basic Validation
    if (empty($project_title))
        $errors[] = "Project Title is required.";
    if (empty($slug))
        $errors[] = "URL Slug is required.";
    if (empty($category_id))
        $errors[] = "Category is required.";
    if (empty($_FILES['list_image']) || $_FILES['list_image']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "List Image is required.";
    if (empty($_FILES['detail_image']) || $_FILES['detail_image']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "Detail Image is required.";

    $list_image_name = null;
    $detail_image_name = null;

    if (empty($errors)) {
        $list_upload_result = handleImageUpload($_FILES['list_image'], '../uploads/portfolio/', $slug);
        if (isset($list_upload_result['success'])) {
            $list_image_name = $list_upload_result['success'];
        } else {
            $errors[] = "List Image Error: " . $list_upload_result['error'];
        }

        $detail_upload_result = handleImageUpload($_FILES['detail_image'], '../uploads/portfolio/', $slug . '-detail');
        if (isset($detail_upload_result['success'])) {
            $detail_image_name = $detail_upload_result['success'];
        } else {
            $errors[] = "Detail Image Error: " . $detail_upload_result['error'];
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO portfolio (project_title, slug, category_id, short_description, list_image, detail_image, client_name, location, project_year, content_heading, detailed_content) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            // Use the variables defined above
            $stmt->execute([
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
                $detailed_content
            ]);
            $project_id = $pdo->lastInsertId();

            // Handle gallery images
            if (isset($_FILES['gallery_images']) && count($_FILES['gallery_images']['name']) > 0) {
                $gallery_files = $_FILES['gallery_images'];
                $gallery_stmt = $pdo->prepare("INSERT INTO portfolio_gallery_images (project_id, image_name) VALUES (?, ?)");

                for ($i = 0; $i < count($gallery_files['name']); $i++) {
                    // Skip empty file inputs
                    if ($gallery_files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $file_to_upload = [
                        'name' => $gallery_files['name'][$i],
                        'type' => $gallery_files['type'][$i],
                        'tmp_name' => $gallery_files['tmp_name'][$i],
                        'error' => $gallery_files['error'][$i],
                        'size' => $gallery_files['size'][$i]
                    ];

                    $gallery_upload_result = handleImageUpload($file_to_upload, '../uploads/portfolio/gallery/', $slug . '-gallery-' . ($i + 1));

                    if (isset($gallery_upload_result['success'])) {
                        $gallery_stmt->execute([$project_id, $gallery_upload_result['success']]);
                    } else {
                        // Optionally add gallery upload errors to the errors array
                        $errors[] = "Gallery Image '" . htmlspecialchars($file_to_upload['name']) . "' Error: " . $gallery_upload_result['error'];
                    }
                }
            }

            // If there were any gallery upload errors, we should roll back
            if (!empty($errors)) {
                throw new Exception("One or more gallery images failed to upload.");
            }

            $pdo->commit();
            echo "<script>alert('Project added successfully!'); window.location.href = 'view-portfolio';</script>";
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            // Also delete files that were successfully uploaded before the transaction failed
            if ($list_image_name && file_exists('../uploads/portfolio/' . $list_image_name))
                unlink('../uploads/portfolio/' . $list_image_name);
            if ($detail_image_name && file_exists('../uploads/portfolio/' . $detail_image_name))
                unlink('../uploads/portfolio/' . $detail_image_name);

            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
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
                                    <h4 class="card-title">Add New Portfolio Project</h4>
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error):
                                                echo "<p class='mb-0'>" . htmlspecialchars($error) . "</p>"; endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form action="add-portfolio" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Project Title *</label>
                                                <input type="text" id="project_title" name="project_title"
                                                    class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">URL Slug *</label>
                                                <input type="text" id="slug" name="slug" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Category *</label>
                                            <select name="category_id" class="form-control" required>
                                                <option value="">-- Select Category --</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Description (for listing page)</label>
                                            <textarea name="short_description" class="form-control" rows="3"></textarea>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Project Details Box</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Client Name</label>
                                                <input type="text" name="client_name" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" name="location" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Project Year</label>
                                                <input type="number" name="project_year" class="form-control"
                                                    placeholder="YYYY" min="1900" max="2099">
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Images</h5>
                                        <div class="mb-3">
                                            <label class="form-label">List Image (for portfolio grid) *</label>
                                            <input type="file" name="list_image" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detail Image (big image on details page) *</label>
                                            <input type="file" name="detail_image" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Gallery Images (can select multiple)</label>
                                            <input type="file" name="gallery_images[]" class="form-control" multiple>
                                        </div>
                                        <hr>
                                        <h5 class="mb-3">Detail Page Content</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Content Heading *</label>
                                            <input type="text" name="content_heading" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detailed Content</label>
                                            <textarea name="detailed_content" id="detailed_content"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Project</button>
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

        // Auto-generate slug from title
        document.getElementById('project_title').addEventListener('keyup', function () {
            let title = this.value.toLowerCase();
            let slug = title.replace(/[^a-z0-9\s-]/g, '') // Remove non-alphanumeric characters
                .replace(/\s+/g, '-')       // Replace spaces with hyphens
                .replace(/-+/g, '-');         // Replace multiple hyphens with single
            document.getElementById('slug').value = slug;
        });
    </script>
</body>

</html>