<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$blog_id = $_GET['id'] ?? null;
if (!$blog_id) {
    echo "<script>alert('Invalid blog ID!'); window.location.href = 'view_blog.php';</script>";
    exit();
}

// Fetch blog data based on ID
$stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    echo "<script>alert('Blog not found!'); window.location.href = 'view_blog.php';</script>";
    exit();
}

$title = $blog['title'];
$author = $blog['author'];
$content = $blog['content'];
$image_name = $blog['image_name'];
$image_title = $blog['image_title'];
$image_alt = $blog['image_alt'];
$url_slug = $blog['url_slug'];
$date = $blog['date'];

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and assign input values
    $title = htmlspecialchars($_POST['title'] ?? $title);
    $author = htmlspecialchars($_POST['author'] ?? $author);
    $date = htmlspecialchars($_POST['date'] ?? $date);
    $content = $_POST['content'] ?? $content;
    $image_title = htmlspecialchars($_POST['image_title'] ?? $image_title);
    $image_alt = htmlspecialchars($_POST['image_alt'] ?? $image_alt);
    $url_slug = htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['url_slug'] ?? ''))) ?? $url_slug;

    // Image name validation and update
    if (!empty($_POST['image_name'])) {
        $img_name_input = htmlspecialchars($_POST['image_name']);
        // Generate new image name
        $new_image_name = preg_replace('/\s+/', '-', strtolower(pathinfo($img_name_input, PATHINFO_FILENAME))) . '-' . $blog_id . '.webp';

        // Rename old image if necessary
        if ($new_image_name !== $image_name) {
            $old_image_path = "../assets/images/blog/" . $image_name;
            $new_image_path = "../assets/images/blog/" . $new_image_name;

            if (file_exists($old_image_path)) {
                rename($old_image_path, $new_image_path);
            }

            // Update the image name variable
            $image_name = $new_image_name;
        }
    }

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Check file type and size
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            echo "<script>alert('Image size exceeds the 2MB limit!');</script>";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
            echo "<script>alert('Only JPG, PNG, and WebP formats are allowed!');</script>";
        } else {
            // Delete old image if a new one is uploaded
            $current_image_path = "../assets/images/blog/" . $image_name;
            if (file_exists($current_image_path)) {
                unlink($current_image_path);
            }

            // Save new image with the generated name
            move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/blog/$image_name");
        }
    }

    // Update the database
    $stmt = $pdo->prepare("UPDATE blog SET title = ?, url_slug=?, date=?, author = ?, content = ?, image_name = ?, image_title = ?, image_alt = ? WHERE id = ?");
    $stmt->execute([$title, $url_slug, $date, $author, $content, $image_name, $image_title, $image_alt, $blog_id]);

    echo "<script>alert('Blog updated successfully!'); window.location.href = 'view_blog.php';</script>";
    exit();
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
                                <h4 class="mb-sm-0 font-size-18">Edit Blog</h4>

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
                                        <li>Edit Bolg</li>
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
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <p>
                                            <?php foreach ($errors as $error): ?>
                                                <?php echo htmlspecialchars($error); ?>
                                            <?php endforeach; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <form method="post" enctype="multipart/form-data">
                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Blog Title</label>
                                            <input type="text" class="form-control" id="title" name="title"
                                                value="<?php echo htmlspecialchars($title); ?>" maxlength="200"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="url_slug" class="form-label">URL-Slug:</label>
                                            <input type="text" id="url_slug" name="url_slug" class="form-control"
                                                value="<?php echo htmlspecialchars($url_slug); ?>" maxlength="250"
                                                minlength="4" required placeholder="Enter URL Slug">
                                            <div class="invalid-feedback">Please provide a URL Slug (min 4 characters).
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="date" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="date" name="date"
                                                max="<?php echo date('Y-m-d'); ?>"
                                                value="<?php echo htmlspecialchars($date); ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="author" class="form-label">Author</label>
                                            <input type="text" class="form-control" id="author" name="author"
                                                value="<?php echo htmlspecialchars($author); ?>" maxlength="50"
                                                required>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="content" class="form-label">Content</label>
                                            <textarea class="form-control" id="content" name="content" rows="5"
                                                required><?php echo htmlspecialchars($content); ?></textarea>
                                        </div>

                                        <div class="col-md-12dfs mb-3">
                                            <label for="current_image" class="form-label">Current Image</label>
                                            <?php if ($image_name): ?>
                                                <div>
                                                    <img src="../assets/images/blog/<?php echo htmlspecialchars($image_name); ?>"
                                                        alt="Blog Image" width="auto" height="100">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Image Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_name" class="form-label">Image Name:</label>
                                            <input type="text" id="image_name" name="image_name" class="form-control"
                                                value="<?php echo htmlspecialchars(pathinfo($image_name, PATHINFO_FILENAME)); ?>"
                                                maxlength="100" minlength="2" required placeholder="Enter image name">
                                            <div class="invalid-feedback">Please provide a valid image name (min 2
                                                characters).</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Replace Image</label>
                                            <input class="form-control" type="file" id="image" name="image">
                                            <small class="text-muted">Upload only if you want to replace the current
                                                image.</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="image_title" class="form-label">Image Title</label>
                                            <input type="text" class="form-control" id="image_title" name="image_title"
                                                value="<?php echo htmlspecialchars($image_title); ?>" maxlength="50">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="image_alt" class="form-label">Image Alt Text</label>
                                            <input type="text" class="form-control" id="image_alt" name="image_alt"
                                                value="<?php echo htmlspecialchars($image_alt); ?>" maxlength="50">
                                        </div>

                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Update Blog</button>
                                        <a href="view_blog.php" class="btn mx-3 btn-secondary">Cancel</a>
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
        CKEDITOR.replace('content');
    </script>
</body>

</html>