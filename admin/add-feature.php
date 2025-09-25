<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

$errors = [];
$feature = $img_name = $img_alt = $img_title = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $feature = !empty($_POST['feature']) ? ($_POST['feature']) : $errors['feature'] = "Feature name is required.";
    $img_name = !empty($_POST['img_name']) ? htmlspecialchars($_POST['img_name']) : $errors['img_name'] = "Image name is required.";
    $img_title = !empty($_POST['img_title']) ? htmlspecialchars($_POST['img_title']) : $errors['img_title'] = "Image title is required.";
    $img_alt = isset($_POST['img_alt']) ? htmlspecialchars($_POST['img_alt']) : '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validate file type and size
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
        } else {
            // Use the image name from input to rename the uploaded image (SEO-friendly)
            $original_name = $img_name;
            $safe_name = preg_replace('/\s+/', '-', strtolower($original_name));
            $unique_id = uniqid(); // Ensure uniqueness
            $final_img_name = $safe_name . '-' . $unique_id . '.webp';

            // Convert and save image to WebP format
            convertToWebP($_FILES['image'], "../assets/images/features/$final_img_name");
        }
    } else {
        $errors['image'] = "Image upload failed. Please try again.";
    }

    // Insert data into the database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO features (feature, img_name, img_alt, img_title) VALUES (?, ?, ?, ?)");
        $stmt->execute([$feature, $final_img_name, $img_alt, $img_title]);

        echo
            "<script>alert('Feature added successfully!'); 
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
                                    Add Feature
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
                                            Add Feature
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
                                <!-- Display errors if any -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <p>
                                            <?php foreach ($errors as $error): ?>
                                                <?php echo htmlspecialchars($error); ?>
                                            <?php endforeach; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <form action="" method="POST" enctype="multipart/form-data" class="custom-validation">
                                    <!-- Feature Name -->
                                    <div class="mb-3">
                                        <label for="feature" class="form-label">Feature Name:</label>
                                        <textarea type="text" id="feature" name="feature" class="form-control"
                                            minlength="2" required placeholder="Enter feature name (min 2 characters)">
                                        </textarea>
                                    </div>
                                    <div class="row">
                                        <!-- Image Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="img_name" class="form-label">Image Name :</label>
                                            <input type="text" id="img_name" name="img_name" class="form-control"
                                                minlength="2" required
                                                placeholder="Enter image name (min 2 characters)">
                                            <small>image name</small>
                                        </div>
                                        <!-- Image Upload -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Upload Image:</label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/*" required>
                                            <small>Supported formats: JPG, PNG, WebP. Max size: 2MB.</small>
                                        </div>

                                    </div>


                                    <div class="row">
                                        <!-- Image Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="img_title" class="form-label">Image Title:</label>
                                            <input type="text" id="img_title" name="img_title" class="form-control"
                                                minlength="2" required
                                                placeholder="Enter image title (min 2 characters)">
                                        </div>

                                        <!-- Image Alt Text -->
                                        <div class="col-md-6 mb-3">
                                            <label for="img_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="img_alt" name="img_alt" class="form-control" required
                                                placeholder="Alt text for the image">
                                        </div>

                                    </div>


                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Add
                                            Feature</button>
                                        <a href="features.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>

                                <!-- Display Validation Errors -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger mt-3">
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