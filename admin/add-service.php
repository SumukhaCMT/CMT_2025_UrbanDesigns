<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];

// Fetch all available icons for the selector
$icons_stmt = $pdo->query("SELECT filename FROM service_icons ORDER BY filename ASC");
$available_icons = $icons_stmt->fetchAll(PDO::FETCH_ASSOC);

function handleImageUpload($file, $uploadDir, $baseName)
{
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
    if (!in_array($ext, $allowed))
        return null;
    $finalName = $baseName . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $finalName))
        return $finalName;
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve all form fields
    $service_title = trim($_POST['service_title']);
    $redirection_link = trim($_POST['redirection_link']);
    $service_description = trim($_POST['service_description']);
    $img_name = trim($_POST['img_name']); // Icon filename from the hidden input

    $page_title = trim($_POST['page_title']);

    $content_heading = trim($_POST['content_heading']);
    $detailed_content = $_POST['detailed_content'];

    // --- Validation ---
    if (empty($service_title))
        $errors[] = "Service Title is required.";
    if (empty($redirection_link))
        $errors[] = "URL Slug is required.";
    if (empty($page_title))
        $errors[] = "Detail Page Title is required.";
    if (empty($content_heading))
        $errors[] = "Content Heading is required.";
    if (!isset($_FILES['detail_image']) || $_FILES['detail_image']['error'] != UPLOAD_ERR_OK) {
        $errors[] = "Detail Page Image is required.";
    }

    $detail_image_name = '';

    if (empty($errors)) {
        // Handle Detail Image Upload
        $slug_for_detail = preg_replace('/[^a-z0-9\-]/i', '', str_replace(' ', '-', strtolower($redirection_link)));
        $new_detail_image = handleImageUpload($_FILES['detail_image'], '../assets/img/service/', 'service-' . $slug_for_detail . '-' . time());
        if ($new_detail_image) {
            $detail_image_name = $new_detail_image;
        } else {
            $errors[] = "Failed to upload Detail Page Image. Allowed formats: JPG, PNG, GIF, SVG.";
        }
    }

    // --- Database Insertion ---
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into 'service' table with the selected img_name
            $stmt1 = $pdo->prepare("INSERT INTO service (img_name, service_title, service_description, redirection_link) VALUES (?, ?, ?, ?)");
            $stmt1->execute([$img_name, $service_title, $service_description, $redirection_link]);
            $service_id = $pdo->lastInsertId();

            // Insert into 'service_details' table
            $stmt2 = $pdo->prepare("INSERT INTO service_details (service_id, page_title, detail_image, content_heading, detailed_content) VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$service_id, $page_title, $detail_image_name, $content_heading, $detailed_content]);

            $pdo->commit();
            echo "<script>alert('Service added successfully!'); window.location.href = 'view-services';</script>";
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <script src="ckeditor/ckeditor.js"></script>
    <style>
        .icon-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        .icon-item {
            cursor: pointer;
            padding: 5px;
            border: 2px solid transparent;
            border-radius: 5px;
        }

        .icon-item img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .icon-item.selected {
            border-color: #007bff;
            background-color: #eaf4ff;
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
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Add New Service</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error): ?>
                                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form class="custom-validation" action="" method="POST"
                                        enctype="multipart/form-data">
                                        <h5 class="mb-3">Service Listing Details</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Service Title *</label>
                                                <input type="text" name="service_title" id="service_title"
                                                    class="form-control" required
                                                    value="<?php echo htmlspecialchars($_POST['service_title'] ?? ''); ?>" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">URL Slug *</label>
                                                <input type="text" name="redirection_link" id="redirection_link"
                                                    class="form-control" required
                                                    value="<?php echo htmlspecialchars($_POST['redirection_link'] ?? ''); ?>" />
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Description</label>
                                            <textarea name="service_description" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($_POST['service_description'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Select Icon</label>
                                            <div class="icon-gallery">
                                                <?php foreach ($available_icons as $icon): ?>
                                                    <div class="icon-item"
                                                        onclick="selectIcon(this, '<?php echo htmlspecialchars($icon['filename']); ?>')">
                                                        <img src="assets/images/service_icons/<?php echo htmlspecialchars($icon['filename']); ?>"
                                                            title="<?php echo htmlspecialchars($icon['filename']); ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="hidden" name="img_name" id="selected-img-name" value="">
                                            <div class="mt-2">
                                                <strong>Selected:</strong> <span id="image-name">None</span>
                                            </div>
                                            <small class="text-muted">Upload and delete icons via the <a
                                                    href="manage-icons.php">Manage Icons</a> page.</small>
                                        </div>

                                        <hr class="my-4">
                                        <h5 class="mb-3">Service Detail Page Content</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Detail Page Title *</label>
                                                <input type="text" name="page_title" class="form-control" required
                                                    value="<?php echo htmlspecialchars($_POST['page_title'] ?? ''); ?>" />
                                            </div>

                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Content Heading *</label>
                                            <input type="text" name="content_heading" class="form-control" required
                                                value="<?php echo htmlspecialchars($_POST['content_heading'] ?? ''); ?>" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detail Page Image *</label>
                                            <input type="file" name="detail_image" class="form-control" required
                                                accept="image/*">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detailed Content *</label>
                                            <textarea id="detailed_content"
                                                name="detailed_content"><?php echo htmlspecialchars($_POST['detailed_content'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="m-3 text-center">
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light">Submit</button>
                                            <a href="view-services" class="btn mx-3 btn-secondary">Cancel</a>
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

        <script>
            // Initialize CKEditor
            CKEDITOR.replace('detailed_content');

            // Function to select an icon from the gallery
            function selectIcon(element, filename) {
                document.querySelectorAll('.icon-item').forEach(item => item.classList.remove('selected'));
                element.classList.add('selected');
                document.getElementById('selected-img-name').value = filename;
                document.getElementById('image-name').textContent = filename;
            }

            // --- Live Slug Generation Script ---

            // Get the title and slug input elements
            const titleInput = document.getElementById('service_title');
            const slugInput = document.getElementById('redirection_link');

            // Listen for input events on the title field
            titleInput.addEventListener('input', function () {
                // Get the current value of the title
                const titleValue = this.value;

                // Convert the title to a URL-friendly slug
                const slugValue = titleValue
                    .toLowerCase()                      // Convert to lowercase
                    .trim()                             // Remove leading/trailing whitespace
                    .replace(/\s+/g, '-')               // Replace spaces with hyphens
                    .replace(/[^a-z0-9-]/g, '');        // Remove any characters that aren't letters, numbers, or hyphens

                // Update the value of the slug input field
                slugInput.value = slugValue;
            });
        </script>
</body>

</html>