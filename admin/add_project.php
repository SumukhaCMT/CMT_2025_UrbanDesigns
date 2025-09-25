<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$title = $subtitle = $slug = $description = $detailed_description = $main_image_title = $main_image_alt = $main_image_name = "";
$category_id = 0;
$features = [];
$youtube_video_url = $youtube_video_title = '';

// Preserve form data after submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '';
    $subtitle = isset($_POST['subtitle']) ? htmlspecialchars($_POST['subtitle']) : '';
    $slug = isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $detailed_description = isset($_POST['detailed_description']) ? $_POST['detailed_description'] : '';
    $main_image_title = isset($_POST['main_image_title']) ? htmlspecialchars($_POST['main_image_title']) : '';
    $main_image_name_input = isset($_POST['main_image_name']) ? htmlspecialchars($_POST['main_image_name']) : '';
    $main_image_alt = isset($_POST['main_image_alt']) ? htmlspecialchars($_POST['main_image_alt']) : '';
    $youtube_video_url = isset($_POST['youtube_video_url']) ? trim($_POST['youtube_video_url']) : '';
    $youtube_video_title = isset($_POST['youtube_video_title']) ? htmlspecialchars($_POST['youtube_video_title']) : '';

    if (isset($_POST['features']) && is_array($_POST['features'])) {
        $features = array_filter($_POST['features'], function ($feature) {
            return !empty(trim($feature));
        });
    }
}

// Fetch categories for dropdown
$categories_stmt = $pdo->prepare("SELECT * FROM project_categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    if (empty($title)) {
        $errors['title'] = "Title is required.";
    }

    if (empty($slug)) {
        $errors['slug'] = "URL Slug is required.";
    } else {
        // Clean up slug
        $slug = trim(preg_replace('/\s+/', '-', $slug));
        $slug = preg_replace('/[^a-z0-9\-]/i', '', $slug);
        $slug = trim($slug, '-');
    }

    if ($category_id == 0) {
        $errors['category_id'] = "Category is required.";
    } else {
        // Verify category exists
        $check_category = $pdo->prepare("SELECT id FROM project_categories WHERE id = ? AND status = 'active'");
        $check_category->execute([$category_id]);
        if (!$check_category->fetch()) {
            $errors['category_id'] = "Invalid category selected.";
        }
    }

    if (empty($description)) {
        $errors['description'] = "Description is required.";
    }

    if (empty($detailed_description)) {
        $errors['detailed_description'] = "Detailed description is required.";
    }

    // Validate YouTube URL if provided
    if (!empty($youtube_video_url)) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_video_url, $matches)) {
            $youtube_id = $matches[1];
            $youtube_video_url = "https://www.youtube.com/embed/" . $youtube_id;
        } else {
            $errors['youtube_video'] = "Invalid YouTube URL format.";
        }
    }

    // Validate main image upload
    if (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] != UPLOAD_ERR_OK) {
        $errors['main_image'] = "Main image is required.";
    } else {
        // Check file size first (before processing)
        if ($_FILES['main_image']['size'] > 2 * 1024 * 1024) {
            $errors['main_image'] = "Image size exceeds the 2MB limit. Please choose a smaller image.";
        } elseif (!in_array($_FILES['main_image']['type'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            $errors['main_image'] = "Only JPG, PNG, WebP, and GIF formats are allowed.";
        } else {
            try {
                // Create a base name for the image
                $file_extension = pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION);
                $base_image_name = uniqid($main_image_name_input . '_') . '.' . $file_extension;
                $output_path = "../assets/images/projects/" . $base_image_name;

                // Process the image and get the REAL saved path
                $saved_image_path = convertToWebP($_FILES['main_image'], $output_path);

                if ($saved_image_path) {
                    // Extract just the filename from the full path
                    $main_image_name = basename($saved_image_path);
                } else {
                    $errors['main_image'] = "Failed to process the image. Please try again.";
                }
            } catch (Exception $e) {
                $errors['main_image'] = "Error processing image: " . $e->getMessage();
            }
        }
    }

    // Insert project data into the database if no errors
    if (empty($errors)) {
        try {
            error_log("Inserting project with category_id: " . $category_id);

            // Remove sort_order and status fields - let database handle auto-sorting
            $stmt = $pdo->prepare("INSERT INTO projects (title, subtitle, slug, category_id, main_image, main_image_title, main_image_alt, description, detailed_description, features, youtube_video_url, youtube_video_title, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");

            $features_json = json_encode($features);
            $result = $stmt->execute([
                $title,
                $subtitle,
                $slug,
                $category_id,
                $main_image_name,
                $main_image_title,
                $main_image_alt,
                $description,
                $detailed_description,
                $features_json,
                $youtube_video_url,
                $youtube_video_title
            ]);
            $project_id = $pdo->lastInsertId();

            // Handle gallery images
            if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['tmp_name'])) {
                for ($i = 0; $i < count($_FILES['gallery_images']['tmp_name']); $i++) {
                    if ($_FILES['gallery_images']['error'][$i] == UPLOAD_ERR_OK) {
                        $gallery_image_name = uniqid('gallery_' . $project_id . '_') . '.jpg';
                        $gallery_title = isset($_POST['gallery_titles'][$i]) ? htmlspecialchars($_POST['gallery_titles'][$i]) : '';
                        $gallery_alt = isset($_POST['gallery_alts'][$i]) ? htmlspecialchars($_POST['gallery_alts'][$i]) : '';
                        $gallery_description = isset($_POST['gallery_descriptions'][$i]) ? htmlspecialchars($_POST['gallery_descriptions'][$i]) : '';

                        $file_info = [
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'type' => $_FILES['gallery_images']['type'][$i],
                            'size' => $_FILES['gallery_images']['size'][$i],
                            'name' => $_FILES['gallery_images']['name'][$i]
                        ];

                        try {
                            if (convertToWebP($file_info, "../assets/images/projects/$gallery_image_name")) {
                                $gallery_stmt = $pdo->prepare("INSERT INTO project_gallery (project_id, image_name, image_title, image_alt, image_description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                                $gallery_stmt->execute([$project_id, $gallery_image_name, $gallery_title, $gallery_alt, $gallery_description, $i]);
                            }
                        } catch (Exception $e) {
                            // Log error but don't stop the process
                            error_log("Gallery image upload failed: " . $e->getMessage());
                        }
                    }
                }
            }

            echo "<script>alert('Project added successfully!'); window.location.href = 'view_projects.php';</script>";
            exit();
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Function to convert image to WebP format with fallbacks
function convertToWebP($file, $outputPath)
{
    // Check if GD extension is loaded
    if (!extension_loaded('gd')) {
        throw new Exception("GD extension is not loaded. Cannot process images.");
    }

    // Create directory if it doesn't exist
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new Exception("Failed to create directory: $dir");
        }
    }

    $image = null;
    $imageType = exif_imagetype($file['tmp_name']);

    // Create image resource based on type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file['tmp_name']);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $image = imagecreatefromwebp($file['tmp_name']);
            } else {
                if (copy($file['tmp_name'], $outputPath)) {
                    return $outputPath;
                }
            }
            break;
        default:
            throw new Exception("Unsupported image type. Please use JPG, PNG, GIF, or WebP.");
    }

    if (!$image) {
        throw new Exception("Failed to create image resource from uploaded file.");
    }

    // Try to save as WebP first
    if (function_exists('imagewebp')) {
        $webp_path = preg_replace('/\.[^.]+$/', '.webp', $outputPath);
        if (imagewebp($image, $webp_path, 85)) {
            imagedestroy($image);
            return $webp_path;
        }
    }

    // Fallback to JPEG
    $jpeg_path = preg_replace('/\.[^.]+$/', '.jpg', $outputPath);
    if (imagejpeg($image, $jpeg_path, 85)) {
        imagedestroy($image);
        return $jpeg_path;
    }

    // Last resort: copy original file
    $original_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fallback_path = preg_replace('/\.[^.]+$/', '.' . $original_ext, $outputPath);
    if (copy($file['tmp_name'], $fallback_path)) {
        imagedestroy($image);
        return $fallback_path;
    }

    imagedestroy($image);
    throw new Exception("Failed to save processed image.");
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
                                <h4 class="mb-sm-0 font-size-18">Add Project</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="view_projects.php">View Projects</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Add Project</li>
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

                                <form action="" method="POST" enctype="multipart/form-data" class="needs-validation"
                                    novalidate>
                                    <!-- Basic Information -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Project Title *</label>
                                            <input type="text" id="title" name="title" class="form-control"
                                                maxlength="200" minlength="2" required placeholder="Enter project title"
                                                value="<?php echo htmlspecialchars($title); ?>">
                                            <div class="invalid-feedback">Please provide a valid title (min 2
                                                characters).</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="subtitle" class="form-label">Subtitle</label>
                                            <input type="text" id="subtitle" name="subtitle" class="form-control"
                                                maxlength="200" placeholder="Enter project subtitle"
                                                value="<?php echo htmlspecialchars($subtitle); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="slug" class="form-label">URL Slug *</label>
                                            <input type="text" id="slug" name="slug" class="form-control"
                                                maxlength="200" minlength="2" required placeholder="Enter URL slug"
                                                value="<?php echo htmlspecialchars($slug); ?>">
                                            <div class="invalid-feedback">Please provide a valid URL slug.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="category_id" class="form-label">Category *</label>
                                            <select id="category_id" name="category_id" class="form-control" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Please select a category.</div>
                                            <small class="form-text text-muted">
                                                <a href="manage_categories.php" target="_blank"
                                                    class="text-primary">Manage Categories</a>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Descriptions -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Short Description *</label>
                                        <textarea id="description" name="description" class="form-control" rows="3"
                                            maxlength="500" minlength="10" required
                                            placeholder="Enter short description"><?php echo htmlspecialchars($description); ?></textarea>
                                        <div class="invalid-feedback">Description must be at least 10 characters long.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="detailed_description" class="form-label">Detailed Description
                                            *</label>
                                        <textarea id="detailed_description" name="detailed_description"
                                            class="form-control" rows="8" required
                                            placeholder="Enter detailed description"><?php echo htmlspecialchars($detailed_description); ?></textarea>
                                        <div class="invalid-feedback">Detailed description is required.</div>
                                    </div>

                                    <!-- Features Section -->
                                    <div class="mb-3">
                                        <label class="form-label">Project Features</label>
                                        <div id="features-container">
                                            <?php if (empty($features)): ?>
                                                <div class="input-group mb-2">
                                                    <input type="text" name="features[]" class="form-control"
                                                        placeholder="Enter feature">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="addFeature()">Add More</button>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($features as $index => $feature): ?>
                                                    <div class="input-group mb-2">
                                                        <input type="text" name="features[]" class="form-control"
                                                            placeholder="Enter feature"
                                                            value="<?php echo htmlspecialchars($feature); ?>">
                                                        <?php if ($index === 0): ?>
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                onclick="addFeature()">Add More</button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-danger"
                                                                onclick="removeFeature(this)">Remove</button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Main Image -->
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="main_image" class="form-label">Main Image *</label>
                                            <input type="file" id="main_image" name="main_image" class="form-control"
                                                accept="image/*" required>
                                            <small class="form-text text-muted">Supported formats: JPG, PNG, WebP, GIF.
                                                Max size: 2MB.</small>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="main_image_name" class="form-label">Main Image Name *</label>
                                            <input type="text" id="main_image_name" name="main_image_name"
                                                class="form-control" maxlength="100" required
                                                placeholder="Enter image name"
                                                value="<?php echo htmlspecialchars($main_image_name_input ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="main_image_title" class="form-label">Main Image Title</label>
                                            <input type="text" id="main_image_title" name="main_image_title"
                                                class="form-control" maxlength="255" placeholder="Enter image title"
                                                value="<?php echo htmlspecialchars($main_image_title); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="main_image_alt" class="form-label">Main Image Alt Text</label>
                                        <input type="text" id="main_image_alt" name="main_image_alt"
                                            class="form-control" maxlength="255" placeholder="Enter image alt text"
                                            value="<?php echo htmlspecialchars($main_image_alt); ?>">
                                    </div>

                                    <!-- Gallery Images -->
                                    <div class="mb-3">
                                        <label class="form-label">Gallery Images</label>
                                        <div id="gallery-container">
                                            <div class="p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <input type="file" name="gallery_images[]" class="form-control"
                                                            accept="image/*">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="gallery_titles[]" class="form-control"
                                                            placeholder="Image title">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="gallery_alts[]" class="form-control"
                                                            placeholder="Alt text">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="gallery_descriptions[]"
                                                            class="form-control" placeholder="Description">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="addGalleryImage()">Add More Images</button>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="mb-3">YouTube Video</h5>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="youtube_video_url" class="form-label">YouTube Video
                                                    URL</label>
                                                <input type="text" id="youtube_video_url" name="youtube_video_url"
                                                    class="form-control" maxlength="255" placeholder=""
                                                    value="<?php echo htmlspecialchars($youtube_video_url); ?>">
                                                <small class="text-muted">Paste the full YouTube URL. Supported formats:
                                                    youtube.com/watch?v=ID or youtu.be/ID</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="youtube_video_title" class="form-label">Video
                                                    Title(optional)</label>
                                                <input type="text" id="youtube_video_title" name="youtube_video_title"
                                                    class="form-control" maxlength="255" placeholder="video title"
                                                    value="<?php echo htmlspecialchars($youtube_video_title); ?>">
                                            </div>
                                        </div>
                                        <div id="youtube-preview" class="mt-2" style="display: none;">
                                            <p class="text-muted mb-2">Preview:</p>
                                            <div class="ratio ratio-16x9" style="max-width: 400px;">
                                                <iframe id="youtube-preview-iframe" src="" frameborder="0"
                                                    allowfullscreen></iframe>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Add Project</button>
                                        <a href="view_projects.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require './shared_components/footer.php'; ?>
    </div>

    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    <script>
        CKEDITOR.replace('detailed_description');

        function addFeature() {
            const container = document.getElementById('features-container');
            const newFeature = document.createElement('div');
            newFeature.className = 'input-group mb-2';
            newFeature.innerHTML = `
                <input type="text" name="features[]" class="form-control" placeholder="Enter feature">
                <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">Remove</button>
            `;
            container.appendChild(newFeature);
        }

        function removeFeature(button) {
            button.parentElement.remove();
        }

        function addGalleryImage() {
            const container = document.getElementById('gallery-container');
            const newItem = document.createElement('div');
            newItem.className = 'gallery-item border p-3 mb-3';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="gallery_titles[]" class="form-control" placeholder="Image title">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="gallery_alts[]" class="form-control" placeholder="Alt text">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="gallery_descriptions[]" class="form-control" placeholder="Description">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger" onclick="removeGalleryImage(this)">×</button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        }

        function removeGalleryImage(button) {
            button.closest('.gallery-item').remove();
        }

        // Live slug generation from title
        document.getElementById('title').addEventListener('input', function () {
            const slugInput = document.getElementById('slug');
            let slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        });

        // Auto-fill image alt text from title
        document.getElementById('title').addEventListener('input', function () {
            const altInput = document.getElementById('main_image_alt');
            if (!altInput.value || altInput.value === '') {
                altInput.value = this.value;
            }
        });

        // Image preview functionality
        document.getElementById('main_image').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size before preview
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size exceeds 2MB limit. Please choose a smaller image.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    // Remove existing preview
                    const existingPreview = document.getElementById('image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Create new preview
                    const preview = document.createElement('div');
                    preview.id = 'image-preview';
                    preview.className = 'mt-2';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                        <p class="small text-muted mt-1">Preview: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</p>
                    `;
                    document.getElementById('main_image').parentNode.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });

        // YouTube URL preview
        document.getElementById('youtube_video_url').addEventListener('input', function () {
            const url = this.value;
            const preview = document.getElementById('youtube-preview');
            const iframe = document.getElementById('youtube-preview-iframe');

            if (url) {
                const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
                const match = url.match(regex);

                if (match) {
                    const videoId = match[1];
                    iframe.src = `https://www.youtube.com/embed/${videoId}`;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            } else {
                preview.style.display = 'none';
            }
        });

        // Trigger YouTube preview on page load if URL exists
        document.addEventListener('DOMContentLoaded', function () {
            const youtubeInput = document.getElementById('youtube_video_url');
            if (youtubeInput.value) {
                youtubeInput.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>

</html>