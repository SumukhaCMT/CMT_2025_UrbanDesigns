<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$logo_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($logo_id === 0) {
    header("Location: trusted-by-img.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM partner_logos WHERE id = ?");
$stmt->execute([$logo_id]);
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$logo) {
    echo "<script>alert('Logo not found!'); window.location.href = 'trusted-by-img.php';</script>";
    exit();
}

$current_filename = $logo['image_filename'];
$current_alt = $logo['image_alt'];
$current_order = $logo['display_order'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $image_alt = !empty($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : ($errors['image_alt'] = "Image alt text is required.");
    $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
    $new_filename = $current_filename;

    // ** MODIFIED: Validation for display order **
    if ($display_order <= 0) {
        $errors['display_order'] = "Display order must be a positive number (1 or higher).";
    } else {
        // Check for duplicate display order on OTHER logos
        $check_stmt = $pdo->prepare("SELECT id FROM partner_logos WHERE display_order = :display_order AND id != :logo_id");
        $check_stmt->execute(['display_order' => $display_order, 'logo_id' => $logo_id]);
        if ($check_stmt->fetch()) {
            $errors['display_order'] = "This display order is already in use by another logo. Please choose a different one.";
        }
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
        } else {
            $old_image_path = "assets/images/partners/" . $current_filename;
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }

            $safe_name = preg_replace('/[^a-zA-Z0-9-]/', '', preg_replace('/\s+/', '-', $image_alt));
            $unique_id = uniqid();
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = $safe_name . '-' . $unique_id . '.' . $extension;

            $destination = "assets/images/partners/" . $new_filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors['image'] = "Failed to save the new image.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE partner_logos SET image_filename = ?, image_alt = ?, display_order = ? WHERE id = ?");
        $stmt->execute([$new_filename, $image_alt, $display_order, $logo_id]);

        echo "<script>alert('Logo updated successfully!');
              window.location.href = 'trusted-by-img.php';</script>";
        exit();
    } else {
        $current_alt = $image_alt;
        $current_order = $display_order;
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
                                <h4 class="mb-sm-0 font-size-18">Edit Partner Logo</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li><a href="trusted-by-img.php">Partner Logos</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Edit Partner Logo</li>
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
                                <form action="" method="POST" enctype="multipart/form-data" class="custom-validation">
                                    <div class="mb-3">
                                        <label class="form-label">Current Logo</label>
                                        <div>
                                            <img src="assets/images/partners/<?php echo htmlspecialchars($current_filename); ?>"
                                                alt="<?php echo htmlspecialchars($current_alt); ?>" width="150"
                                                class="img-thumbnail">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Upload New Logo (Optional):</label>
                                        <input type="file" id="image" name="image" class="form-control"
                                            accept="image/*">
                                        <small>To keep the current logo, leave this field empty.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image_alt" class="form-label">Image Alt Text (Required):</label>
                                        <input type="text" id="image_alt" name="image_alt" class="form-control"
                                            value="<?php echo htmlspecialchars($current_alt); ?>" minlength="2"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="display_order" class="form-label">Display Order (Required):</label>
                                        <input type="number" id="display_order" name="display_order"
                                            class="form-control" value="<?php echo htmlspecialchars($current_order); ?>"
                                            min="1" required>
                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="trusted-by-img.php" class="btn mx-3 btn-secondary">Cancel</a>
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
</body>

</html>