<?php
// Start session and include necessary files
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Initialize errors array to store validation messages
$errors = [];

// Define the tabs and their corresponding database IDs
$tab_ids = ['about' => 1, 'index' => 2, 'footer' => 3];

// Determine the active tab from the URL, default to 'about'
$active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tab_ids) ? $_GET['tab'] : 'about';
$active_id = $tab_ids[$active_tab];

// Fetch content for all defined sections from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM about_us WHERE id IN (1, 2, 3)");
    $stmt->execute();
    $all_about_us_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}


// Organize the fetched data by ID for easy access in the forms
$about_us_content = [];
foreach ($all_about_us_data as $data) {
    $about_us_content[$data['id']] = $data;
}

// Ensure there is default placeholder data for each section to prevent errors if a row is missing
foreach ($tab_ids as $id) {
    if (!isset($about_us_content[$id])) {
        $about_us_content[$id] = [
            'subtitle' => '',
            'title' => '',
            'content' => '',
            'image' => '',
            'image_name' => '',
            'img_title' => '',
            'img_alt' => '',
            'image_2' => '',
            'image_name_2' => '',
            'img_title_2' => '',
            'img_alt_2' => '' // NEW: Default for image 2
        ];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the ID of the section being updated from the hidden form field
    $id_to_update = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    // Determine which tab to redirect back to after the update
    $tab_to_redirect = array_search($id_to_update, $tab_ids);

    if ($id_to_update && $tab_to_redirect) {
        $current_data = $about_us_content[$id_to_update];

        // Sanitize and retrieve form data
        $title = trim($_POST['title']);
        $subtitle = trim($_POST['subtitle']);
        $content = trim($_POST['content']);

        // --- Image 1 Handling ---
        $image_title = htmlspecialchars(trim($_POST['image_title']));
        $image_alt = htmlspecialchars(trim($_POST['image_alt']));
        $image_name_input = htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['image_name'])));
        $image_filename = $current_data['image'];
        $final_image_name = $current_data['image_name'];
        $final_image_title = $current_data['img_title'];
        $final_image_alt = $current_data['img_alt'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $errors['image'] = "Image 1 size cannot exceed 2MB.";
            } else {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!in_array($_FILES['image']['type'], $allowed_types)) {
                    $errors['image'] = "Invalid file type for Image 1. Only JPG, PNG, and WebP are allowed.";
                } else {
                    $uploadDir = "assets/images/about/";
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $image_filename = uniqid($image_name_input . '-', true) . '.' . $file_extension;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image_filename)) {
                        $final_image_name = $image_name_input;
                        $final_image_title = $image_title;
                        $final_image_alt = $image_alt;
                    } else {
                        $errors['image'] = "Failed to upload Image 1.";
                    }
                }
            }
        } else {
            if (!empty($image_name_input))
                $final_image_name = $image_name_input;
            if (!empty($image_title))
                $final_image_title = $image_title;
            if (!empty($image_alt))
                $final_image_alt = $image_alt;
        }

        // --- NEW: Image 2 Handling (Only for Index tab) ---
        if ($id_to_update == 2) {
            $image_title_2 = htmlspecialchars(trim($_POST['image_title_2']));
            $image_alt_2 = htmlspecialchars(trim($_POST['image_alt_2']));
            $image_name_input_2 = htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['image_name_2'])));
            $image_filename_2 = $current_data['image_2'];
            $final_image_name_2 = $current_data['image_name_2'];
            $final_image_title_2 = $current_data['img_title_2'];
            $final_image_alt_2 = $current_data['img_alt_2'];

            if (isset($_FILES['image_2']) && $_FILES['image_2']['error'] == UPLOAD_ERR_OK) {
                if ($_FILES['image_2']['size'] > 2 * 1024 * 1024) {
                    $errors['image_2'] = "Image 2 size cannot exceed 2MB.";
                } else {
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    if (!in_array($_FILES['image_2']['type'], $allowed_types)) {
                        $errors['image_2'] = "Invalid file type for Image 2. Only JPG, PNG, and WebP are allowed.";
                    } else {
                        $uploadDir = "assets/images/about/";
                        $file_extension_2 = strtolower(pathinfo($_FILES['image_2']['name'], PATHINFO_EXTENSION));
                        $image_filename_2 = uniqid($image_name_input_2 . '-', true) . '.' . $file_extension_2;
                        if (move_uploaded_file($_FILES['image_2']['tmp_name'], $uploadDir . $image_filename_2)) {
                            $final_image_name_2 = $image_name_input_2;
                            $final_image_title_2 = $image_title_2;
                            $final_image_alt_2 = $image_alt_2;
                        } else {
                            $errors['image_2'] = "Failed to upload Image 2.";
                        }
                    }
                }
            } else {
                if (!empty($image_name_input_2))
                    $final_image_name_2 = $image_name_input_2;
                if (!empty($image_title_2))
                    $final_image_title_2 = $image_title_2;
                if (!empty($image_alt_2))
                    $final_image_alt_2 = $image_alt_2;
            }
        }

        // Validate required fields
        if (empty($title) || empty($content)) {
            $error = "Title and Content fields are required.";
        }

        // If there are no errors, proceed with database update
        if (empty($errors) && !isset($error)) {
            // MODIFIED: Use different queries based on the ID
            if ($id_to_update == 2) {
                // Query for Index page with two images
                $sql = "UPDATE about_us SET title = :title, subtitle = :subtitle, content = :content, image = :image, image_name = :image_name, img_title = :img_title, img_alt = :img_alt, image_2 = :image_2, image_name_2 = :image_name_2, img_title_2 = :img_title_2, img_alt_2 = :img_alt_2 WHERE id = :id";
                $params = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'content' => $content,
                    'image' => $image_filename,
                    'image_name' => $final_image_name,
                    'img_title' => $final_image_title,
                    'img_alt' => $final_image_alt,
                    'image_2' => $image_filename_2,
                    'image_name_2' => $final_image_name_2,
                    'img_title_2' => $final_image_title_2,
                    'img_alt_2' => $final_image_alt_2,
                    'id' => $id_to_update
                ];
            } else {
                // Original query for other pages with one image
                $sql = "UPDATE about_us SET title = :title, subtitle = :subtitle, content = :content, image = :image, image_name = :image_name, img_title = :img_title, img_alt = :img_alt WHERE id = :id";
                $params = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'content' => $content,
                    'image' => $image_filename,
                    'image_name' => $final_image_name,
                    'img_title' => $final_image_title,
                    'img_alt' => $final_image_alt,
                    'id' => $id_to_update
                ];
            }

            $update_stmt = $pdo->prepare($sql);
            $update_result = $update_stmt->execute($params);

            if ($update_result) {
                echo "<script>
                        alert('Section updated successfully!'); 
                        window.location.href = 'about-us?tab=" . $tab_to_redirect . "';
                      </script>";
                exit();
            } else {
                $error = "Failed to update the database. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>About Us Sections Management</title>
    <script src="ckeditor/ckeditor.js"></script>
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
                                <h4 class="mb-sm-0 font-size-18">About Us Sections</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">About Us</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($errors) || isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php
                            if (isset($error))
                                echo "<p>" . htmlspecialchars($error) . "</p>";
                            foreach ($errors as $error_msg)
                                echo "<p>" . htmlspecialchars($error_msg) . "</p>";
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link <?php if ($active_tab == 'about')
                                    echo 'active'; ?>" href="?tab=about">About</a></li>
                                <li class="nav-item"><a class="nav-link <?php if ($active_tab == 'index')
                                    echo 'active'; ?>" href="?tab=index">Index</a></li>
                                <li class="nav-item"><a class="nav-link <?php if ($active_tab == 'footer')
                                    echo 'active'; ?>" href="?tab=footer">Footer</a></li>
                            </ul>

                            <div class="tab-content pt-4">
                                <?php foreach ($tab_ids as $tab_name => $id): ?>
                                    <div class="tab-pane fade <?php if ($active_tab == $tab_name)
                                        echo 'show active'; ?>" id="<?php echo $tab_name; ?>" role="tabpanel">
                                        <form method="post" enctype="multipart/form-data" class="custom-validation">
                                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                                            <?php $current_content = $about_us_content[$id]; ?>

                                            <h5>Image 1 Details</h5>
                                            <hr>
                                            <?php if (!empty($current_content['image'])): ?>
                                                <div class="mb-3">
                                                    <label class="form-label">Current Image 1:</label>
                                                    <div><img
                                                            src="assets/images/about/<?php echo htmlspecialchars($current_content['image']); ?>"
                                                            alt="<?php echo htmlspecialchars($current_content['img_alt']); ?>"
                                                            style="max-width: 200px; height: auto; border-radius: 8px;"></div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row">
                                                <div class="col-md-6 mb-3"><label for="image_<?php echo $id; ?>"
                                                        class="form-label">Upload New Image 1</label><input type="file"
                                                        id="image_<?php echo $id; ?>" name="image" class="form-control"
                                                        accept="image/jpeg,image/png,image/webp"><small
                                                        class="form-text text-muted">Max 2MB. Leave empty to keep the
                                                        current image.</small></div>
                                                <div class="col-md-6 mb-3"><label for="image_name_<?php echo $id; ?>"
                                                        class="form-label">Image 1 Name</label><input type="text"
                                                        id="image_name_<?php echo $id; ?>" name="image_name"
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($current_content['image_name']); ?>"
                                                        placeholder="e.g., team-photo"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3"><label for="image_title_<?php echo $id; ?>"
                                                        class="form-label">Image 1 Title</label><input type="text"
                                                        id="image_title_<?php echo $id; ?>" name="image_title"
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($current_content['img_title']); ?>"
                                                        placeholder="Title attribute for the image"></div>
                                                <div class="col-md-6 mb-3"><label for="image_alt_<?php echo $id; ?>"
                                                        class="form-label">Image 1 Alt Text</label><input type="text"
                                                        id="image_alt_<?php echo $id; ?>" name="image_alt"
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($current_content['img_alt']); ?>"
                                                        placeholder="Descriptive text for accessibility"></div>
                                            </div>

                                            <?php if ($tab_name == 'index'): ?>
                                                <h5 class="mt-4">Image 2 Details</h5>
                                                <hr>
                                                <?php if (!empty($current_content['image_2'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label">Current Image 2:</label>
                                                        <div><img
                                                                src="assets/images/about/<?php echo htmlspecialchars($current_content['image_2']); ?>"
                                                                alt="<?php echo htmlspecialchars($current_content['img_alt_2']); ?>"
                                                                style="max-width: 200px; height: auto; border-radius: 8px;"></div>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3"><label for="image_2_<?php echo $id; ?>"
                                                            class="form-label">Upload New Image 2</label><input type="file"
                                                            id="image_2_<?php echo $id; ?>" name="image_2" class="form-control"
                                                            accept="image/jpeg,image/png,image/webp"><small
                                                            class="form-text text-muted">Max 2MB. Leave empty to keep the
                                                            current image.</small></div>
                                                    <div class="col-md-6 mb-3"><label for="image_name_2_<?php echo $id; ?>"
                                                            class="form-label">Image 2 Name</label><input type="text"
                                                            id="image_name_2_<?php echo $id; ?>" name="image_name_2"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($current_content['image_name_2']); ?>"
                                                            placeholder="e.g., office-interior"></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3"><label for="image_title_2_<?php echo $id; ?>"
                                                            class="form-label">Image 2 Title</label><input type="text"
                                                            id="image_title_2_<?php echo $id; ?>" name="image_title_2"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($current_content['img_title_2']); ?>"
                                                            placeholder="Title attribute for the image"></div>
                                                    <div class="col-md-6 mb-3"><label for="image_alt_2_<?php echo $id; ?>"
                                                            class="form-label">Image 2 Alt Text</label><input type="text"
                                                            id="image_alt_2_<?php echo $id; ?>" name="image_alt_2"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($current_content['img_alt_2']); ?>"
                                                            placeholder="Descriptive text for accessibility"></div>
                                                </div>
                                            <?php endif; ?>

                                            <h5 class="mt-4">Content Details</h5>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12 mb-3"><label for="title_<?php echo $id; ?>"
                                                        class="form-label">Title <span
                                                            class="text-danger">*</span></label><input type="text"
                                                        name="title" id="title_<?php echo $id; ?>" class="form-control"
                                                        value="<?php echo htmlspecialchars($current_content['title']); ?>"
                                                        required></div>
                                                <div class="col-md-12 mb-3"><label for="subtitle_<?php echo $id; ?>"
                                                        class="form-label">Subtitle</label><input type="text"
                                                        name="subtitle" id="subtitle_<?php echo $id; ?>"
                                                        class="form-control"
                                                        value="<?php echo htmlspecialchars($current_content['subtitle']); ?>">
                                                </div>
                                            </div>
                                            <div class="mb-3"><label for="content_<?php echo $id; ?>"
                                                    class="form-label">Content <span
                                                        class="text-danger">*</span></label><textarea name="content"
                                                    id="content_<?php echo $id; ?>" class="form-control" rows="10"
                                                    required><?php echo htmlspecialchars($current_content['content']); ?></textarea>
                                            </div>
                                            <div class="text-center mt-4"><button type="submit"
                                                    class="btn btn-primary waves-effect waves-light">Update Section</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    <script>
        CKEDITOR.replace('content_1');
        CKEDITOR.replace('content_2');
        CKEDITOR.replace('content_3');
    </script>
</body>

</html>