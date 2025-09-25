<?php
require './shared_components/session.php';
require './shared_components/error.php';
include './shared_components/db.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$project_id) {
    echo "<script>alert('Invalid project ID!'); window.location.href = 'view_projects.php';</script>";
    exit();
}

// === NEW, SIMPLIFIED IMAGE UPLOAD FUNCTION ===
// This function handles JPG, PNG, GIF uploads without converting them.
function handleImageUpload($file, $baseOutputPath)
{
    $upload_dir = dirname($baseOutputPath) . '/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return null; // Failed to create directory
        }
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_extension, $allowed_extensions)) {
        return null; // Invalid file type
    }

    // Use the base name provided and append the original, safe extension
    $final_filename = basename($baseOutputPath) . '.' . $file_extension;
    $final_path = $upload_dir . $final_filename;

    if (move_uploaded_file($file['tmp_name'], $final_path)) {
        return $final_filename; // Return the new filename on success
    }

    return null; // Return null on failure
}


// Handle gallery image deletion FIRST if requested
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_gallery_id'])) {
    $gallery_id_to_delete = intval($_POST['delete_gallery_id']);

    // Fetch the image name to delete the file
    $gallery_stmt = $pdo->prepare("SELECT image_name FROM project_gallery WHERE id = ? AND project_id = ?");
    $gallery_stmt->execute([$gallery_id_to_delete, $project_id]);
    $gallery_image = $gallery_stmt->fetch(PDO::FETCH_ASSOC);

    if ($gallery_image) {
        $image_path = "../assets/images/projects/" . $gallery_image['image_name'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Delete the record from the database
        $delete_stmt = $pdo->prepare("DELETE FROM project_gallery WHERE id = ? AND project_id = ?");
        $delete_stmt->execute([$gallery_id_to_delete, $project_id]);

        // Redirect back to the edit page to show the result
        echo "<script>alert('Gallery image deleted successfully!'); window.location.href = 'edit_project.php?id=$project_id';</script>";
        exit();
    }
}

// Fetch project data
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo "<script>alert('Project not found!'); window.location.href = 'view_projects.php';</script>";
    exit();
}

// Fetch categories for dropdown
$categories_stmt = $pdo->prepare("SELECT * FROM project_categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch gallery images
$gallery_stmt = $pdo->prepare("SELECT * FROM project_gallery WHERE project_id = ? ORDER BY sort_order ASC");
$gallery_stmt->execute([$project_id]);
$gallery_images = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode features
$features = json_decode($project['features'], true) ?: [];

// Create upload directory if it doesn't exist
$upload_dir = "../assets/images/projects/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Preserve form data
$title = $subtitle = $slug = $description = $detailed_description = $main_image_title = $main_image_alt = "";
$category_id = 0;
$youtube_video_url = $youtube_video_title = '';
$submitted_features = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use submitted data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $detailed_description = isset($_POST['detailed_description']) ? $_POST['detailed_description'] : '';
    $main_image_title = isset($_POST['main_image_title']) ? trim($_POST['main_image_title']) : '';
    $main_image_alt = isset($_POST['main_image_alt']) ? trim($_POST['main_image_alt']) : '';
    $youtube_video_url = isset($_POST['youtube_video_url']) ? trim($_POST['youtube_video_url']) : '';
    $youtube_video_title = isset($_POST['youtube_video_title']) ? trim($_POST['youtube_video_title']) : '';

    if (isset($_POST['features']) && is_array($_POST['features'])) {
        $submitted_features = array_filter($_POST['features'], function ($feature) {
            return !empty(trim($feature));
        });
    }
} else {
    // Use database values
    $title = $project['title'];
    $subtitle = $project['subtitle'];
    $slug = $project['slug'];
    $category_id = $project['category_id'];
    $description = $project['description'];
    $detailed_description = $project['detailed_description'];
    $main_image_title = $project['main_image_title'];
    $main_image_alt = $project['main_image_alt'];
    $youtube_video_url = $project['youtube_video_url'] ?? '';
    $youtube_video_title = $project['youtube_video_title'] ?? '';
    $submitted_features = $features;
}


// Handle form submission for UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_gallery_id'])) {

    // Validate fields...
    if (empty($title))
        $errors['title'] = "Title is required.";
    // ... other validations ...

    $main_image_name = $project['main_image'];

    // Handle main image upload
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['main_image']['size'] > 2 * 1024 * 1024) {
            $errors['main_image'] = "Image size exceeds 2MB.";
        } else {
            // Delete old image if it exists
            if ($project['main_image'] && file_exists($upload_dir . $project['main_image'])) {
                unlink($upload_dir . $project['main_image']);
            }
            // USE THE NEW UPLOAD FUNCTION
            $new_main_image = handleImageUpload($_FILES['main_image'], $upload_dir . 'main_' . $project_id . '_' . time());
            if ($new_main_image) {
                $main_image_name = $new_main_image;
            } else {
                $errors['main_image'] = "Failed to upload the main image. Allowed formats: JPG, PNG, GIF.";
            }
        }
    }

    // Update project if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, subtitle = ?, slug = ?, category_id = ?, main_image = ?, main_image_title = ?, main_image_alt = ?, description = ?, detailed_description = ?, features = ?, youtube_video_url = ?, youtube_video_title = ?, updated_at = NOW() WHERE id = ?");
            $features_json = json_encode($submitted_features);
            $stmt->execute([$title, $subtitle, $slug, $category_id, $main_image_name, $main_image_title, $main_image_alt, $description, $detailed_description, $features_json, $youtube_video_url, $youtube_video_title, $project_id]);

            // Handle gallery images upload
            if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['tmp_name'])) {
                $gallery_count = count($_FILES['gallery_images']['tmp_name']);
                for ($i = 0; $i < $gallery_count; $i++) {
                    if ($_FILES['gallery_images']['error'][$i] == UPLOAD_ERR_OK) {
                        $file_info = ['tmp_name' => $_FILES['gallery_images']['tmp_name'][$i], 'name' => $_FILES['gallery_images']['name'][$i]];
                        // USE THE NEW UPLOAD FUNCTION
                        $new_gallery_image = handleImageUpload($file_info, $upload_dir . 'gallery_' . $project_id . '_' . time() . '_' . $i);
                        if ($new_gallery_image) {
                            $gallery_title = isset($_POST['gallery_titles'][$i]) ? trim($_POST['gallery_titles'][$i]) : '';
                            $gallery_alt = isset($_POST['gallery_alts'][$i]) ? trim($_POST['gallery_alts'][$i]) : '';
                            $gallery_description = isset($_POST['gallery_descriptions'][$i]) ? trim($_POST['gallery_descriptions'][$i]) : '';
                            $gallery_stmt = $pdo->prepare("INSERT INTO project_gallery (project_id, image_name, image_title, image_alt, image_description) VALUES (?, ?, ?, ?, ?)");
                            $gallery_stmt->execute([$project_id, $new_gallery_image, $gallery_title, $gallery_alt, $gallery_description]);
                        }
                    }
                }
            }
            echo "<script>alert('Project updated successfully!'); window.location.href = 'view_projects.php';</script>";
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
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
                                <h4 class="mb-sm-0 font-size-18">Edit Project</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Edit Project:
                                        <?php echo htmlspecialchars($project['title']); ?></h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <h6>Please fix the following errors:</h6>
                                            <ul class="mb-0">
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo htmlspecialchars($error); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <form action="edit_project.php?id=<?php echo $project_id; ?>" method="POST"
                                        enctype="multipart/form-data" id="projectForm">

                                        <h5 class="mb-3">Basic Information</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label for="title" class="form-label">Project
                                                    Title *</label><input type="text" id="title" name="title"
                                                    class="form-control" required
                                                    value="<?php echo htmlspecialchars($title); ?>"></div>
                                            <div class="col-md-6 mb-3"><label for="subtitle"
                                                    class="form-label">Subtitle</label><input type="text" id="subtitle"
                                                    name="subtitle" class="form-control"
                                                    value="<?php echo htmlspecialchars($subtitle); ?>"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3"><label for="slug" class="form-label">URL Slug
                                                    *</label><input type="text" id="slug" name="slug"
                                                    class="form-control" required
                                                    value="<?php echo htmlspecialchars($slug); ?>"></div>
                                            <div class="col-md-6 mb-3"><label for="category_id"
                                                    class="form-label">Category *</label><select id="category_id"
                                                    name="category_id" class="form-control" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($category['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select></div>
                                        </div>

                                        <h5 class="mb-3 mt-4">Content</h5>
                                        <div class="mb-3"><label for="description" class="form-label">Short Description
                                                *</label><textarea id="description" name="description"
                                                class="form-control" rows="3"
                                                required><?php echo htmlspecialchars($description); ?></textarea></div>
                                        <div class="mb-3"><label for="detailed_description" class="form-label">Detailed
                                                Description *</label><textarea id="detailed_description"
                                                name="detailed_description" class="form-control" rows="8"
                                                required><?php echo htmlspecialchars($detailed_description); ?></textarea>
                                        </div>
                                        <div class="mb-4"><label class="form-label">Project Features</label>
                                            <div id="features-container">
                                                <?php if (!empty($submitted_features)): ?>    <?php foreach ($submitted_features as $index => $feature): ?>
                                                        <div class="input-group mb-2"><input type="text" name="features[]"
                                                                class="form-control" placeholder="Enter feature"
                                                                value="<?php echo htmlspecialchars($feature); ?>"><?php if ($index === 0): ?><button
                                                                    type="button" class="btn btn-outline-secondary"
                                                                    onclick="addFeature()">Add More</button><?php else: ?><button
                                                                    type="button" class="btn btn-outline-danger"
                                                                    onclick="removeFeature(this)">Remove</button><?php endif; ?>
                                                        </div><?php endforeach; ?><?php else: ?>
                                                    <div class="input-group mb-2"><input type="text" name="features[]"
                                                            class="form-control" placeholder="Enter feature"><button
                                                            type="button" class="btn btn-outline-secondary"
                                                            onclick="addFeature()">Add More</button></div><?php endif; ?>
                                            </div>
                                        </div>

                                        <h5 class="mb-3 mt-4">YouTube Video</h5>
                                        <div class="row">
                                            <div class="col-md-8 mb-3"><label for="youtube_video_url"
                                                    class="form-label">YouTube Video URL</label><input type="text"
                                                    id="youtube_video_url" name="youtube_video_url" class="form-control"
                                                    value="<?php echo htmlspecialchars($youtube_video_url); ?>"></div>
                                            <div class="col-md-4 mb-3"><label for="youtube_video_title"
                                                    class="form-label">Video Title</label><input type="text"
                                                    id="youtube_video_title" name="youtube_video_title"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($youtube_video_title); ?>"></div>
                                        </div>

                                        <h5 class="mb-3 mt-4">Main Image</h5>
                                        <?php if ($project['main_image']): ?>
                                            <div class="mb-3"><label class="form-label">Current Main Image</label>
                                                <div><img
                                                        src="../assets/images/projects/<?php echo htmlspecialchars($project['main_image']); ?>"
                                                        alt="Current Image"
                                                        style="max-width: 300px; height: auto; border-radius: 5px;"></div>
                                            </div><?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><label for="main_image" class="form-label">Main
                                                    Image
                                                    <?php echo $project['main_image'] ? '(Upload to replace)' : '*'; ?></label><input
                                                    type="file" id="main_image" name="main_image" class="form-control"
                                                    accept="image/jpeg,image/png,image/gif"></div>
                                            <div class="col-md-4 mb-3"><label for="main_image_title"
                                                    class="form-label">Image Title</label><input type="text"
                                                    id="main_image_title" name="main_image_title" class="form-control"
                                                    value="<?php echo htmlspecialchars($main_image_title); ?>"></div>
                                            <div class="col-md-4 mb-3"><label for="main_image_alt"
                                                    class="form-label">Image Alt Text</label><input type="text"
                                                    id="main_image_alt" name="main_image_alt" class="form-control"
                                                    value="<?php echo htmlspecialchars($main_image_alt); ?>"></div>
                                        </div>

                                        <hr>

                                        <h5 class="mb-3 mt-4">Gallery</h5>
                                        <?php if (!empty($gallery_images)): ?>
                                            <div class="mb-4">
                                                <h6>Existing Gallery Images</h6>
                                                <div class="row">
                                                    <?php foreach ($gallery_images as $gallery_image): ?>
                                                        <div class="col-md-4 col-lg-3 mb-3">
                                                            <div class="card">
                                                                <img src="../assets/images/projects/<?php echo htmlspecialchars($gallery_image['image_name']); ?>"
                                                                    class="card-img-top"
                                                                    style="height: 150px; object-fit: cover;"
                                                                    alt="<?php echo htmlspecialchars($gallery_image['image_alt']); ?>">
                                                                <div class="card-body p-2 text-center">
                                                                    <button type="button" class="btn btn-sm btn-danger"
                                                                        onclick="confirmDelete(<?php echo $gallery_image['id']; ?>)">
                                                                        <i class="bx bx-trash"></i> Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mb-4">
                                            <h6>Add New Gallery Images</h6>
                                            <div id="gallery-container">
                                                <div class="gallery-item border p-3 mb-3 rounded">
                                                    <div class="row">
                                                        <div class="col-md-3"><label class="form-label small">Image
                                                                File</label><input type="file" name="gallery_images[]"
                                                                class="form-control"
                                                                accept="image/jpeg,image/png,image/gif"></div>
                                                        <div class="col-md-3"><label
                                                                class="form-label small">Title</label><input type="text"
                                                                name="gallery_titles[]" class="form-control"
                                                                placeholder="Image title"></div>
                                                        <div class="col-md-3"><label class="form-label small">Alt
                                                                Text</label><input type="text" name="gallery_alts[]"
                                                                class="form-control" placeholder="Alt text"></div>
                                                        <div class="col-md-3"><label
                                                                class="form-label small">Description</label><input
                                                                type="text" name="gallery_descriptions[]"
                                                                class="form-control" placeholder="Description"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="addGalleryImage()"><i class="bx bx-plus"></i> Add More
                                                Images</button>
                                        </div>

                                        <div class="m-3 text-center">
                                            <button type="submit" class="btn btn-primary" id="submitBtn"
                                                name="update_project">Update Project</button>
                                            <a href="view_projects.php" class="btn mx-3 btn-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './shared_components/footer.php'; ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script>
        CKEDITOR.replace('detailed_description');

        function addGalleryImage() {
            const container = document.getElementById('gallery-container');
            const newItem = document.createElement('div');
            newItem.className = 'gallery-item border p-3 mb-3 rounded';
            newItem.innerHTML = `<div class="row"><div class="col-md-3"><label class="form-label small">Image File</label><input type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/gif"></div><div class="col-md-3"><label class="form-label small">Title</label><input type="text" name="gallery_titles[]" class="form-control" placeholder="Title"></div><div class="col-md-3"><label class="form-label small">Alt Text</label><input type="text" name="gallery_alts[]" class="form-control" placeholder="Alt text"></div><div class="col-md-2"><label class="form-label small">Description</label><input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Description"></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeGalleryImage(this)">×</button></div></div>`;
            container.appendChild(newItem);
        }

        function removeGalleryImage(button) {
            button.closest('.gallery-item').remove();
        }

        function confirmDelete(galleryId) {
            if (confirm('Are you sure you want to delete this image? This will happen immediately.')) {
                const mainForm = document.getElementById('projectForm');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete_gallery_id';
                hiddenInput.value = galleryId;
                mainForm.appendChild(hiddenInput);
                mainForm.submit();
            }
        }

        // Other JS functions like addFeature, removeFeature etc. remain the same.
    </script>
</body>

</html>