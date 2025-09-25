<?php
require './shared_components/session.php';
require './shared_components/error.php';

// Include database connection
include 'shared_components/db.php';

// Initialize error messages and success flag
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = trim($_POST['page_name']);
    $meta_title = trim($_POST['meta_title']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $meta_description = trim($_POST['meta_description']);
    $meta_canonical_link = trim($_POST['meta_canonical_link']);

    // Validation
    if (empty($page_name)) {
        $errors[] = "Page Name is required.";
    } elseif (strlen($page_name) > 50) {
        $errors[] = "Page Name cannot exceed 50 characters.";
    }

    if (empty($meta_title)) {
        $errors[] = "Meta Title is required.";
    } elseif (strlen($meta_title) > 70) {
        $errors[] = "Meta Title cannot exceed 70 characters.";
    }

    if (empty($meta_keywords)) {
        $errors[] = "Meta Keywords are required.";
    } elseif (strlen($meta_keywords) > 150) {
        $errors[] = "Meta Keywords cannot exceed 150 characters.";
    }

    if (empty($meta_description)) {
        $errors[] = "Meta Description is required.";
    } elseif (strlen($meta_description) > 160) {
        $errors[] = "Meta Description cannot exceed 160 characters.";
    }

    if (!empty($meta_canonical_link) && !filter_var($meta_canonical_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Meta Canonical Link must be a valid URL.";
    }

    // If no errors, insert data into the database
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO seo (page_name, meta_title, meta_keywords, meta_description, meta_canonical_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$page_name, $meta_title, $meta_keywords, $meta_description, $meta_canonical_link]);
        $success = true;

        // Redirect to SEO page after successful insertion

        echo "<script>alert('SEO record added successfully!'); window.location.href = 'seo.php';</script>";
        // header("Location: seo.php");
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
                    <!-- Breadcrumb -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">
                                    Add SEO
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>

                                            <a href="seo.php">SEO</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Add SEO
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
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Success Message -->
                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success">
                                        SEO entry added successfully!
                                    </div>
                                <?php endif; ?>

                                <!-- Form -->
                                <form method="post" action="" class="custom-validation">
                                    <div class="mb-3">
                                        <label for="page_name" class="form-label">Page Name (Max 50 characters):</label>
                                        <input type="text" class="form-control" id="page_name" name="page_name"
                                            maxlength="50" minlength="2"
                                            value="<?php echo htmlspecialchars($page_name ?? ''); ?>" required
                                            placeholder="Enter the page name (2-50 characters)">
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title (Max 70
                                            characters):</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                                            maxlength="70" minlength="5"
                                            value="<?php echo htmlspecialchars($meta_title ?? ''); ?>" required
                                            placeholder="Enter meta title (5-70 characters)">
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords (Max 150
                                            characters):</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                                            maxlength="150"
                                            value="<?php echo htmlspecialchars($meta_keywords ?? ''); ?>" required
                                            placeholder="Enter meta keywords, separated by commas">
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description (Max 160
                                            characters):</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description"
                                            maxlength="160" rows="3" required
                                            placeholder="Enter meta description (up to 160 characters)"><?php echo htmlspecialchars($meta_description ?? ''); ?></textarea>
                                    </div>

                                    <div class="mb-3 d-none">
                                        <label for="meta_canonical_link" class="form-label">Canonical Link
                                            (Optional):</label>
                                        <input type="url" class="form-control" id="meta_canonical_link"
                                            name="meta_canonical_link"
                                            value="<?php echo htmlspecialchars($meta_canonical_link ?? ''); ?>"
                                            placeholder="Enter canonical link (optional)">
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Submit</button>
                                        <a href="seo.php" class="btn btn-secondary waves-effect mx-2">Cancel</a>
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
</body>

</html>