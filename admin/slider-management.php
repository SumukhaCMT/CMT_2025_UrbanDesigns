<?php
require './shared_components/session.php';
require './shared_components/error.php';

include 'shared_components/db.php';

// Initialize errors array
$errors = [];

// Fetch all slider items
$stmt = $pdo->prepare("SELECT * FROM main_slider ORDER BY slide_order ASC");
$stmt->execute();
$sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding/updating slider
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $slide_id = isset($_POST['slide_id']) ? intval($_POST['slide_id']) : 0;
        $title = trim($_POST['title']);
        $subtitle = trim($_POST['subtitle']);
        $slide_order = intval($_POST['slide_order']);
        $image_alt = isset($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : '';

        // Handle image upload
        $image_filename = '';
        if ($action === 'update' && $slide_id > 0) {
            // Get current image for update
            $current_stmt = $pdo->prepare("SELECT image FROM main_slider WHERE id = :id");
            $current_stmt->execute(['id' => $slide_id]);
            $current = $current_stmt->fetch(PDO::FETCH_ASSOC);
            $image_filename = $current['image'] ?? '';
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Validate file type and size
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $errors['image'] = "Image size exceeds the 5MB limit.";
            } else {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $file_type = $_FILES['image']['type'];

                if (!in_array($file_type, $allowed_types)) {
                    $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
                } else {
                    $uploadDir = "assets/images/backgrounds/";

                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Get file extension
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

                    // Generate unique filename
                    $image_filename = 'slider-' . uniqid() . '.' . $file_extension;
                    $upload_path = $uploadDir . $image_filename;

                    // Move uploaded file
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $errors['image'] = "Failed to upload image.";
                    }
                }
            }
        }

        // Validate inputs
        if (!empty($title) && !empty($image_filename) && empty($errors)) {
            if ($action === 'add') {
                $insert_stmt = $pdo->prepare("INSERT INTO main_slider (title, subtitle, image, image_alt, slide_order, created_at) 
                                            VALUES (:title, :subtitle, :image, :image_alt, :slide_order, NOW())");

                $result = $insert_stmt->execute([
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'image' => $image_filename,
                    'image_alt' => $image_alt,
                    'slide_order' => $slide_order
                ]);

                if ($result) {
                    echo "<script>alert('Slider added successfully!'); window.location.href = 'slider-management.php';</script>";
                    exit();
                }
            } else {
                $update_stmt = $pdo->prepare("UPDATE main_slider SET 
                    title = :title, 
                    subtitle = :subtitle, 
                    image = :image, 
                    image_alt = :image_alt, 
                    slide_order = :slide_order, 
                    updated_at = NOW()
                    WHERE id = :id");

                $result = $update_stmt->execute([
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'image' => $image_filename,
                    'image_alt' => $image_alt,
                    'slide_order' => $slide_order,
                    'id' => $slide_id
                ]);

                if ($result) {
                    echo "<script>alert('Slider updated successfully!'); window.location.href = 'slider-management.php';</script>";
                    exit();
                }
            }
        } else {
            if (empty($title)) {
                $errors['title'] = "Title is required.";
            }
            if (empty($image_filename)) {
                $errors['image'] = "Image is required.";
            }
        }
    }

    // Handle delete action
    if ($action === 'delete' && isset($_POST['slide_id'])) {
        $slide_id = intval($_POST['slide_id']);

        // Get image filename before deletion
        $img_stmt = $pdo->prepare("SELECT image FROM main_slider WHERE id = :id");
        $img_stmt->execute(['id' => $slide_id]);
        $img_data = $img_stmt->fetch(PDO::FETCH_ASSOC);

        $delete_stmt = $pdo->prepare("DELETE FROM main_slider WHERE id = :id");
        if ($delete_stmt->execute(['id' => $slide_id])) {
            // Delete image file
            if ($img_data && !empty($img_data['image'])) {
                $image_path = "assets/images/backgrounds/" . $img_data['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            echo "<script>alert('Slider deleted successfully!'); window.location.href = 'slider-management.php';</script>";
            exit();
        }
    }
}

// Get slider for editing
$edit_slider = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_stmt = $pdo->prepare("SELECT * FROM main_slider WHERE id = :id");
    $edit_stmt->execute(['id' => $_GET['edit']]);
    $edit_slider = $edit_stmt->fetch(PDO::FETCH_ASSOC);
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
                                <h4 class="mb-sm-0 font-size-18">Slider Management</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Slider Management</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->
                </div>

                <!-- Display errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error_msg): ?>
                            <p><?php echo htmlspecialchars($error_msg); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Slider Form -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <?php echo $edit_slider ? 'Edit Slider' : 'Add New Slider'; ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <form method="post" class="custom-validation" enctype="multipart/form-data">
                                    <input type="hidden" name="action"
                                        value="<?php echo $edit_slider ? 'update' : 'add'; ?>">
                                    <?php if ($edit_slider): ?>
                                        <input type="hidden" name="slide_id" value="<?php echo $edit_slider['id']; ?>">
                                    <?php endif; ?>

                                    <!-- Current Image Display (for edit) -->
                                    <?php if ($edit_slider && !empty($edit_slider['image'])): ?>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Current Image:</label>
                                                <div>
                                                    <img src="assets/images/backgrounds/<?php echo htmlspecialchars($edit_slider['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($edit_slider['image_alt']); ?>"
                                                        style="max-width: 300px; height: auto; border-radius: 10px;">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row">
                                        <!-- Image Upload -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">
                                                Slider Image
                                                <?php echo $edit_slider ? '(Optional - Leave empty to keep current)' : '(Required)'; ?>:
                                            </label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/*" <?php echo !$edit_slider ? 'required' : ''; ?>>
                                            <small class="form-text text-muted">Supported formats: JPG, PNG, WebP. Max
                                                size: 5MB. Recommended: 1920x1080px</small>
                                        </div>
                                        <!-- Image Alt Text -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="image_alt" name="image_alt" class="form-control"
                                                value="<?php echo $edit_slider ? htmlspecialchars($edit_slider['image_alt']) : ''; ?>"
                                                maxlength="100" placeholder="Enter image alt text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="title">Title (Required)</label>
                                            <textarea name="title" id="title" class="form-control" rows="3"
                                                minlength="10" maxlength="200" required
                                                placeholder="Enter slider title"><?php echo $edit_slider ? htmlspecialchars($edit_slider['title']) : ''; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="subtitle">Subtitle (Optional)</label>
                                            <textarea name="subtitle" id="subtitle" class="form-control" rows="2"
                                                maxlength="300"
                                                placeholder="Enter slider subtitle"><?php echo $edit_slider ? htmlspecialchars($edit_slider['subtitle']) : ''; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="slide_order">Slide Order</label>
                                            <input type="number" name="slide_order" id="slide_order"
                                                class="form-control"
                                                value="<?php echo $edit_slider ? $edit_slider['slide_order'] : '1'; ?>"
                                                min="1" max="100" required>
                                        </div>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            <?php echo $edit_slider ? 'Update Slider' : 'Add Slider'; ?>
                                        </button>
                                        <?php if ($edit_slider): ?>
                                            <a href="slider-management.php"
                                                class="btn btn-secondary waves-effect waves-light">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Sliders List -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Existing Sliders</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Subtitle</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sliders)): ?>
                                                <?php foreach ($sliders as $slider): ?>
                                                    <tr>
                                                        <td><?php echo $slider['slide_order']; ?></td>
                                                        <td>
                                                            <?php if (!empty($slider['image'])): ?>
                                                                <img src="assets/images/backgrounds/<?php echo htmlspecialchars($slider['image']); ?>"
                                                                    alt="<?php echo htmlspecialchars($slider['image_alt']); ?>"
                                                                    style="width: 100px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                            <?php else: ?>
                                                                <span class="text-muted">No image</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars(substr($slider['title'], 0, 50)) . (strlen($slider['title']) > 50 ? '...' : ''); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars(substr($slider['subtitle'], 0, 50)) . (strlen($slider['subtitle']) > 50 ? '...' : ''); ?>
                                                        </td>
                                                        <td>
                                                            <a href="slider-management.php?edit=<?php echo $slider['id']; ?>"
                                                                class="btn btn-sm btn-primary">Edit</a>
                                                            <form method="post" style="display: inline-block;"
                                                                onsubmit="return confirm('Are you sure you want to delete this slider?');">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="slide_id"
                                                                    value="<?php echo $slider['id']; ?>">
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-danger">Delete</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No sliders found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    <script>
        // Initialize CKEditor for title and subtitle if needed
        // CKEDITOR.replace('title');
        // CKEDITOR.replace('subtitle');
    </script>
</body>

</html>