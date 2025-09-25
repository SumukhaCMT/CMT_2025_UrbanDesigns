<?php
// Core setup for session and error handling
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

// Initialize errors array
$errors = [];

// Fetch the current founder details from the database
$stmt = $pdo->prepare("SELECT * FROM founder_details WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$founder_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim and retrieve POST data for all fields
    $content = trim($_POST['content']);
    $subtext = trim($_POST['subtext']); // Added subtext field
    $facebook_link = trim($_POST['facebook_link']);
    $linkedin_link = trim($_POST['linkedin_link']);
    $instagram_link = trim($_POST['instagram_link']);
    $image_name_input = isset($_POST['image_name']) ? htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['image_name']))) : '';
    $image_alt = isset($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : '';

    // Initialize image variables with current values
    $image_filename = $founder_details['image_name'];
    $final_image_name = $founder_details['image_name'];
    $final_image_alt = $founder_details['image_alt'];

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } else {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
            } else {

                // ================== FIXED LINE HERE ==================
                // Path is now relative to the admin folder, without ../
                $uploadDir = "assets/images/about/";
                // ===================================================

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image_filename = uniqid($image_name_input . '-') . '.' . $file_extension;
                $upload_path = $uploadDir . $image_filename;

                // This is line 53, where the error occurs
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $final_image_name = $image_name_input;
                    $final_image_alt = $image_alt;
                } else {
                    $errors['image'] = "Failed to upload image. Check folder permissions and path.";
                }
            }
        }
    } else {
        if (!empty($image_name_input))
            $final_image_name = $image_name_input;
        if (!empty($image_alt))
            $final_image_alt = $image_alt;
    }

    // If no errors, update the database
    if (empty($errors)) {
        $update_stmt = $pdo->prepare("UPDATE founder_details SET 
            content = :content, 
            subtext = :subtext,
            facebook_link = :facebook_link, 
            linkedin_link = :linkedin_link, 
            instagram_link = :instagram_link, 
            image_name = :image,
            image_alt = :image_alt
            WHERE id = :id");

        $update_result = $update_stmt->execute([
            'content' => $content,
            'subtext' => $subtext, // Added subtext parameter
            'facebook_link' => $facebook_link,
            'linkedin_link' => $linkedin_link,
            'instagram_link' => $instagram_link,
            'image' => $image_filename,
            'image_alt' => $final_image_alt,
            'id' => 1
        ]);

        if ($update_result) {
            echo "<script>alert('Founder Details Updated Successfully!'); window.location.href = 'founder-details.php';</script>";
            exit();
        } else {
            $error = "Failed to update database.";
        }
    }
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Founder Details</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Founder Details</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $err)
                                echo "<p>" . htmlspecialchars($err) . "</p>"; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="post" class="custom-validation" enctype="multipart/form-data">

                                        <?php if (!empty($founder_details['image_name'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Current Image:</label>
                                                <div>
                                                    <img src="assets/images/about/<?php echo htmlspecialchars($founder_details['image_name'] ?? ''); ?>"
                                                        alt="<?php echo htmlspecialchars($founder_details['image_alt'] ?? ''); ?>"
                                                        style="max-width: 200px; height: auto; border-radius: 10px;">
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="image" class="form-label">Upload New Image
                                                    (Optional):</label>
                                                <input type="file" id="image" name="image" class="form-control"
                                                    accept="image/*">
                                                <small class="form-text text-muted">Max size: 2MB. Leave empty to keep
                                                    current image.</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="image_alt" class="form-label">Image Alt Text:</label>
                                                <input type="text" id="image_alt" name="image_alt" class="form-control"
                                                    value="<?php echo htmlspecialchars($founder_details['image_alt'] ?? ''); ?>"
                                                    placeholder="Enter image alt text">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="content">Content</label>
                                            <textarea name="content" id="content" class="form-control"
                                                rows="10"><?php echo htmlspecialchars($founder_details['content'] ?? ''); ?></textarea>
                                        </div>

                                        <!-- NEW SUBTEXT FIELD -->
                                        <div class="mb-3">
                                            <label class="form-label" for="subtext">Quote/Subtext</label>
                                            <textarea name="subtext" id="subtext" class="form-control" rows="3"
                                                placeholder="Enter a quote or subtext that will appear in the highlighted box"><?php echo htmlspecialchars($founder_details['subtext'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">This text will appear in the highlighted
                                                quote box below the main content. Leave empty to hide the quote
                                                box.</small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" for="facebook_link">Facebook URL</label>
                                                <input type="url" name="facebook_link" id="facebook_link"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($founder_details['facebook_link'] ?? ''); ?>"
                                                    placeholder="https://facebook.com/username">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" for="linkedin_link">LinkedIn URL</label>
                                                <input type="url" name="linkedin_link" id="linkedin_link"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($founder_details['linkedin_link'] ?? ''); ?>"
                                                    placeholder="https://linkedin.com/in/username">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label" for="instagram_link">Instagram URL</label>
                                                <input type="url" name="instagram_link" id="instagram_link"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($founder_details['instagram_link'] ?? ''); ?>"
                                                    placeholder="https://instagram.com/username">
                                            </div>
                                        </div>

                                        <div class="m-3 text-center">
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light">Update Details</button>
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
            // Initialize CKEditor for the single content area
            CKEDITOR.replace('content');
        </script>
</body>

</html>