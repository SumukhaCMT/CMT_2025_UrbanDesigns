<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: view-services");
    exit();
}

// NEW: Fetch all available icons from the database for the selector
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
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $finalName)) {
        return $finalName;
    }
    return null;
}

// Fetch existing data by joining the tables
$stmt = $pdo->prepare("SELECT s.*, sd.page_title, sd.detail_image, sd.content_heading, sd.detailed_content
                       FROM service s 
                       LEFT JOIN service_details sd ON s.id = sd.service_id 
                       WHERE s.id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<script>alert('Service not found!'); window.location.href = 'view-services';</script>";
    exit();
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve all form fields
    $service_title = trim($_POST['service_title']);
    $redirection_link = trim($_POST['redirection_link']);
    $service_description = trim($_POST['service_description']);
    $img_name = trim($_POST['img_name']); // Icon filename from the hidden input

    $page_title = trim($_POST['page_title']);

    $content_heading = trim($_POST['content_heading']);
    $detailed_content = $_POST['detailed_content']; // CKEditor content

    // --- Validation ---
    if (empty($service_title))
        $errors[] = "Service Title is required.";
    if (empty($redirection_link))
        $errors[] = "URL Slug (Redirection Link) is required.";

    $detail_image_name = $service['detail_image'];
    if (isset($_FILES['detail_image']) && $_FILES['detail_image']['error'] == UPLOAD_ERR_OK) {
        if ($detail_image_name && file_exists('../assets/img/service/' . $detail_image_name)) {
            unlink('../assets/img/service/' . $detail_image_name);
        }
        $slug_for_filename = preg_replace('/[^a-z0-9\-]/i', '', str_replace(' ', '-', strtolower($redirection_link)));
        $new_image = handleImageUpload($_FILES['detail_image'], '../assets/img/service/', 'service-' . $slug_for_filename . '-' . time());
        if ($new_image) {
            $detail_image_name = $new_image;
        } else {
            $errors[] = "Failed to upload new Detail Page Image.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("UPDATE service SET img_name = ?, service_title = ?, service_description = ?, redirection_link = ? WHERE id = ?");
            $stmt1->execute([$img_name, $service_title, $service_description, $redirection_link, $id]);

            $stmt_check = $pdo->prepare("SELECT id FROM service_details WHERE service_id = ?");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetch()) {
                $stmt2 = $pdo->prepare("UPDATE service_details SET page_title = ?, detail_image = ?, content_heading = ?, detailed_content = ? WHERE service_id = ?");
                $stmt2->execute([$page_title, $detail_image_name, $content_heading, $detailed_content, $id]);
            } else {
                $stmt2 = $pdo->prepare("INSERT INTO service_details (service_id, page_title, detail_image, content_heading, detailed_content) VALUES (?, ?, ?, ?, ?)");
                $stmt2->execute([$id, $page_title, $detail_image_name, $content_heading, $detailed_content]);
            }

            $pdo->commit();
            echo "<script>alert('Service updated successfully!'); window.location.href = 'view-services';</script>";
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
                                <h4 class="mb-sm-0 font-size-18">Edit Service</h4>
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
                                                <input type="text" name="service_title" class="form-control" required
                                                    value="<?php echo htmlspecialchars($service['service_title'] ?? ''); ?>" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">URL Slug (Redirection Link) *</label>
                                                <input type="text" name="redirection_link" class="form-control" required
                                                    value="<?php echo htmlspecialchars($service['redirection_link'] ?? ''); ?>" />
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Description</label>
                                            <textarea name="service_description" class="form-control"
                                                rows="3"><?php echo htmlspecialchars($service['service_description'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Select Icon</label>
                                            <div class="icon-gallery">
                                                <?php foreach ($available_icons as $icon): ?>
                                                    <?php $isSelected = (($service['img_name'] ?? '') === $icon['filename']) ? 'selected' : ''; ?>
                                                    <div class="icon-item <?php echo $isSelected; ?>"
                                                        onclick="selectIcon(this, '<?php echo htmlspecialchars($icon['filename']); ?>')">
                                                        <img src="assets/images/service_icons/<?php echo htmlspecialchars($icon['filename']); ?>"
                                                            title="<?php echo htmlspecialchars($icon['filename']); ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="hidden" name="img_name" id="selected-img-name"
                                                value="<?php echo htmlspecialchars($service['img_name'] ?? ''); ?>">
                                            <div class="mt-2">
                                                <strong>Selected:</strong> <span
                                                    id="image-name"><?php echo htmlspecialchars($service['img_name'] ?? 'None'); ?></span>
                                                <button type="button" class="btn btn-sm btn-secondary ms-2"
                                                    onclick="removeIcon()">Remove Icon</button>
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
                                                    value="<?php echo htmlspecialchars($service['page_title'] ?? ''); ?>" />
                                            </div>

                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Content Heading *</label>
                                            <input type="text" name="content_heading" class="form-control" required
                                                value="<?php echo htmlspecialchars($service['content_heading'] ?? ''); ?>" />
                                        </div>
                                        <?php if (!empty($service['detail_image'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Current Detail Page Image</label>
                                                <div><img
                                                        src="../assets/img/service/<?php echo htmlspecialchars($service['detail_image']); ?>"
                                                        style="max-width: 200px; border-radius: 5px;"></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label class="form-label">Upload New Detail Image</label>
                                            <input type="file" name="detail_image" class="form-control"
                                                accept="image/*">
                                            <small class="text-muted">To replace the current image, upload a new one
                                                here.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Detailed Content *</label>
                                            <textarea id="detailed_content" name="detailed_content"
                                                required><?php echo htmlspecialchars($service['detailed_content'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="m-3 text-center">
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light">Update Service</button>
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
            CKEDITOR.replace('detailed_content');

            function selectIcon(element, filename) {
                // Visually deselect all other icons
                document.querySelectorAll('.icon-item').forEach(item => item.classList.remove('selected'));
                // Visually select the clicked icon
                element.classList.add('selected');
                // Update the hidden input with the filename
                document.getElementById('selected-img-name').value = filename;
                // Update the display text
                document.getElementById('image-name').textContent = filename;
            }

            // NEW: Function to remove/un-assign an icon
            function removeIcon() {
                // Visually deselect all icons
                document.querySelectorAll('.icon-item').forEach(item => item.classList.remove('selected'));
                // Clear the hidden input value
                document.getElementById('selected-img-name').value = '';
                // Update the display text to "None"
                document.getElementById('image-name').textContent = 'None';
            }
        </script>
</body>

</html>