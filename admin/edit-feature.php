<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

$errors = [];
$feature = $img_name = $img_alt = $img_title = "";
$feature_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM features WHERE id = ?");
$stmt->execute([$feature_id]);
$feature_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$feature_data) {
    echo "<script>alert('Feature not found!'); window.location.href = 'features.php';</script>";
    exit();
}

// Prepopulate existing data
$feature = $feature_data['feature'];
$img_name = $feature_data['img_name'];
$img_alt = $feature_data['img_alt'];
$img_title = $feature_data['img_title'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $feature = !empty($_POST['feature']) ? ($_POST['feature']) : $errors['feature'] = "Feature name is required.";
    $img_title = !empty($_POST['img_title']) ? htmlspecialchars($_POST['img_title']) : $errors['img_title'] = "Image title is required.";
    $img_alt = isset($_POST['img_alt']) ? htmlspecialchars($_POST['img_alt']) : '';
    $img_name_input = !empty($_POST['img_name']) ? htmlspecialchars($_POST['img_name']) : $errors['img_name'] = "Image name is required.";

    $new_img_name = preg_replace('/\s+/', '-', strtolower(pathinfo($img_name_input, PATHINFO_FILENAME))) . '-' . $feature_id . '.webp';

    // Check if the image name has been updated
    if ($new_img_name !== $img_name) {
        $old_image_path = "../assets/images/features/" . $img_name;
        $new_image_path = "../assets/images/features/" . $new_img_name;

        // Rename the file on the server
        if (file_exists($old_image_path)) {
            rename($old_image_path, $new_image_path);
        }

        // Update the image name variable
        $img_name = $new_img_name;
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validate file type and size
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
        } else {
            // Unlink the current image
            $current_image_path = "../assets/images/features/" . $img_name;
            if (file_exists($current_image_path)) {
                unlink($current_image_path);
            }

            // Convert and save the new image
            convertToWebP($_FILES['image'], "../assets/images/features/$img_name");
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE features SET feature = ?, img_name = ?, img_alt = ?, img_title = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$feature, $img_name, $img_alt, $img_title, $feature_id]);

        echo "<script>alert('Feature updated successfully!');
          window.location.href = 'features.php';</script>";
        exit();
    }
}

// Function to convert image to WebP format
function convertToWebP($file, $outputPath)
{
    $image = null;
    $imageType = exif_imagetype($file['tmp_name']);

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            die("Unsupported image type.");
    }

    imagewebp($image, $outputPath, 100);
    imagedestroy($image);
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
                                <h4 class="mb-sm-0 font-size-18">
                                    Edit Features
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="features.php">Our Features</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Edit Features
                                        </li>
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
                                <h4 class="card-title">Edit Feature</h4>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger mt-3">
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                <form action="" method="POST" enctype="multipart/form-data" class="custom-validation">
                                    <!-- Feature Name -->
                                    <div class="mb-3">
                                        <label for="feature" class="form-label">Feature Name:</label>
                                        <textarea type="text" id="feature" name="feature" class="form-control"
                                            value="<?php echo ($feature); ?>" minlength="2" required
                                            placeholder="Enter feature name (min 2 characters)">
                                            <?php echo ($feature); ?>
                                        </textarea>
                                    </div>
                                    <div class="row">

                                        <div class="col-md-12 mb-3">
                                            <label for="current_image" class="form-label">Current Image</label>
                                            <?php if ($img_name): ?>
                                                <div>
                                                    <img src="../assets/images/features/<?php echo htmlspecialchars($img_name); ?>"
                                                        alt="Features Image" max-width="100%" height="100">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Image Name -->
                                        <div class="col-md-3 mb-3">
                                            <label for="img_name" class="form-label">Image Name (SEO-Friendly):</label>
                                            <input type="text" id="img_name" name="img_name" class="form-control"
                                                value="<?php echo htmlspecialchars(pathinfo($img_name, PATHINFO_FILENAME)); ?>"
                                                minlength="2" required
                                                placeholder="Enter image name (min 2 characters)">
                                            <small>This name will be used to rename the image (spaces will be replaced
                                                with dashes).</small>
                                        </div>
                                        <!-- Image Title -->
                                        <div class="col-md-3 mb-3">
                                            <label for="img_title" class="form-label">Image Title:</label>
                                            <input type="text" id="img_title" name="img_title" class="form-control"
                                                value="<?php echo htmlspecialchars($img_title); ?>" minlength="2"
                                                required placeholder="Enter image title (min 2 characters)">
                                        </div>

                                        <!-- Image Alt Text -->
                                        <div class="col-md-3 mb-3">
                                            <label for="img_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="img_alt" name="img_alt" class="form-control"
                                                value="<?php echo htmlspecialchars($img_alt); ?>"
                                                placeholder="Optional alt text for the image">
                                        </div>



                                        <!-- Upload New Image -->
                                        <div class="col-md-3 mb-3">
                                            <label for="image" class="form-label">Repalce with New Image
                                                (optional):</label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/*">
                                            <small>Supported formats: JPG, PNG, WebP. Max size: 2MB.</small>
                                        </div>

                                    </div>




                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="features.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>



                <!--  -->

            </div>
        </div>


        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    <script>
        CKEDITOR.replace('feature');
    </script>
</body>

</html>