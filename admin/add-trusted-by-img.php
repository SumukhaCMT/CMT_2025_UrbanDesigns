<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

$errors = [];
// Variables to match the partner_logos table schema
$image_filename = $image_alt = "";
$display_order = 0;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $image_alt = !empty($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : ($errors['image_alt'] = "Image alt text is required.");
    $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;

    // ** MODIFIED: Validation for display order **
    if ($display_order <= 0) {
        $errors['display_order'] = "Display order must be a positive number (1 or higher).";
    } else {
        // Check for duplicate display order
        $check_stmt = $pdo->prepare("SELECT id FROM partner_logos WHERE display_order = :display_order");
        $check_stmt->execute(['display_order' => $display_order]);
        if ($check_stmt->fetch()) {
            $errors['display_order'] = "This display order is already in use. Please choose a different one.";
        }
    }

    // Use alt text for the base filename if provided, otherwise use a generic name
    $base_filename = !empty($_POST['image_alt']) ? $_POST['image_alt'] : 'partner-logo';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
        } else {
            // Generate a unique filename while keeping the original extension
            $safe_name = preg_replace('/[^a-zA-Z0-9-]/', '', preg_replace('/\s+/', '-', $base_filename));
            $unique_id = uniqid();
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $image_filename = $safe_name . '-' . $unique_id . '.' . $extension;

            $destination = "assets/images/partners/" . $image_filename;

            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors['image'] = "Failed to save the uploaded image.";
            }
        }
    } else {
        if (empty($errors)) {
            $errors['image'] = "Image upload is required. Please select a file.";
        }
    }

    // Insert data into the database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO partner_logos (image_filename, image_alt, display_order) VALUES (?, ?, ?)");
        $stmt->execute([$image_filename, $image_alt, $display_order]);

        echo "<script>alert('Logo added successfully!'); 
              window.location.href = 'trusted-by-img.php';</script>";
        exit();
    }
}
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Add Partner Logo</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li><a href="trusted-by-img.php">Partner Logos</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Add Partner Logo</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="" method="POST" enctype="multipart/form-data" class="custom-validation">
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Upload Logo Image (Required):</label>
                                        <input type="file" id="image" name="image" class="form-control" accept="image/*"
                                            required>
                                        <small>Supported formats: JPG, PNG, WebP. Max size: 2MB.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image_alt" class="form-label">Image Alt Text (Required):</label>
                                        <input type="text" id="image_alt" name="image_alt" class="form-control"
                                            minlength="2" required placeholder="e.g., Google Logo">
                                    </div>

                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order (Required):</label>
                                        <input type="number" id="display_order" name="display_order"
                                            class="form-control" value="1" min="1" required
                                            placeholder="e.g., 1, 2, 3...">
                                        <small>Must be a unique positive number. Logos with smaller numbers appear
                                            first.</small>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Add
                                            Logo</button>
                                        <a href="trusted-by-img.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>

                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger mt-3">
                                        <strong>Please fix the following errors:</strong>
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
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
</body>

</html>