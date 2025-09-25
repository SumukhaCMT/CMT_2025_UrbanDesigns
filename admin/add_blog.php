<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$title = $author = $content = $image_title = $image_alt = $image_name = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $title = !empty($_POST['title']) ? htmlspecialchars($_POST['title']) : $errors['title'] = "Title is required.";
    $url_slug = !empty($_POST['url_slug']) ? htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['url_slug']))) : $errors['url_slug'] = "URL Slug is required.";
    $date = !empty($_POST['date']) ? htmlspecialchars($_POST['date']) : $errors['date'] = "Date is required.";
    $author = !empty($_POST['author']) ? htmlspecialchars($_POST['author']) : $errors['author'] = "Author is required.";
    $content = !empty($_POST['content']) ? $_POST['content'] : $errors['content'] = "Content is required.";
    $image_title = isset($_POST['image_title']) ? htmlspecialchars($_POST['image_title']) : '';
    $image_name_input = isset($_POST['image_name']) ? htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['image_name']))) : '';
    $image_alt = isset($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validate file type and size
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
        } else {
            // Convert to WebP and save
            $image_name = uniqid($image_name_input . '_') . '.webp';
            convertToWebP($_FILES['image'], "../assets/images/blog/$image_name");
        }
    }

    // Insert data into the database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO blog (title, url_slug, date, author, content, image_name, image_title, image_alt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $url_slug, $date, $author, $content, $image_name, $image_title, $image_alt]);

        echo "<script>alert('Blog added successfully!'); window.location.href = 'view_blog.php';</script>";
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
                                <h4 class="mb-sm-0 font-size-18">Add Blog</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="view_blog.php">View Blog</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Add Bolg</li>
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
                                <form action="" method="POST" enctype="multipart/form-data" class="needs-validation"
                                    novalidate>
                                    <div class="row">
                                        <!-- Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Blog Title:</label>
                                            <input type="text" id="title" name="title" class="form-control"
                                                maxlength="100" minlength="2" required placeholder="Enter blog title">
                                            <div class="invalid-feedback">Please provide a valid title (min 2
                                                characters).</div>
                                        </div>
                                        <!-- Author -->
                                        <div class="col-md-6 mb-3 ">
                                            <label for="author" class="form-label">Author:</label>
                                            <input type="text" id="author" name="author" class="form-control"
                                                maxlength="50" minlength="2" required placeholder="Enter author name">
                                            <div class="invalid-feedback">Please provide a valid author name (min 2
                                                characters).</div>
                                        </div>


                                    </div>
                                    <div class="row">
                                        <!-- date -->
                                        <div class="mb-3 col-md-6 ">
                                            <label for="date" class="form-label">Date:</label>
                                            <input type="date" id="date" name="date" class="form-control" required
                                                max="<?php echo date('Y-m-d'); ?>" placeholder="Select date">

                                            <div class="invalid-feedback">Please Enter/Select a date.</div>
                                        </div>
                                        <!-- Url Slug -->
                                        <div class="col-md-6 mb-3">
                                            <label for="url_slug" class="form-label">URL-Slug:</label>
                                            <input type="text" id="url_slug" name="url_slug" class="form-control"
                                                maxlength="250" minlength="4" required placeholder="Enter URL Slug">
                                            <div class="invalid-feedback">Please provide a URL Slug (min 4 characters).
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Content -->
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content:</label>
                                        <textarea id="content" name="content" class="form-control" rows="5"
                                            maxlength="5000" minlength="50" required
                                            placeholder="Enter blog content"></textarea>
                                        <div class="invalid-feedback">Content must be at least 50 characters long.</div>
                                    </div>

                                    <div class="row">

                                    </div>
                                    <div class="row">
                                        <!-- Image Upload -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Upload Image:</label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/*" required>
                                            <small class="form-text text-muted">Supported formats: JPG, PNG, WebP. Max
                                                size: 2MB.</small>
                                            <div class="invalid-feedback">Please upload a valid image file.</div>
                                        </div>
                                        <!-- Image Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_name" class="form-label">Image Name:</label>
                                            <input type="text" id="image_name" name="image_name" class="form-control"
                                                maxlength="100" minlength="2" required placeholder="Enter image name">
                                            <div class="invalid-feedback">Please provide a valid image name (min 2
                                                characters).</div>
                                        </div>
                                    </div>


                                    <div class="row">


                                        <!-- Image Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_title" class="form-label">Image Title:</label>
                                            <input type="text" id="image_title" name="image_title" class="form-control"
                                                maxlength="100" placeholder="Enter image title " required>
                                        </div>

                                        <!-- Image Alt Text -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="image_alt" name="image_alt" class="form-control"
                                                maxlength="100" placeholder="Enter image alt text " required>
                                        </div>
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Add New Blog</button>
                                        <a href="view_blog.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



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
        CKEDITOR.replace('content');
    </script>
</body>

</html>