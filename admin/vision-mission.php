<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];

// Fetch from the 'mission_vision' table
$stmt = $pdo->prepare("SELECT * FROM mission_vision WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Error: Could not find the record in the 'mission_vision' table. Please ensure you have inserted the initial row with id=1.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve all form fields
    $section_subtitle = trim($_POST['section_subtitle'] ?? '');
    $section_title = trim($_POST['section_title'] ?? '');
    $mission_heading = trim($_POST['mission_heading'] ?? '');
    $mission_content = trim($_POST['mission_content'] ?? '');
    $vision_heading = trim($_POST['vision_heading'] ?? '');
    $vision_content = trim($_POST['vision_content'] ?? '');

    // Initialize image filenames with existing values
    $mission_image_filename = $data['mission_image_url'];
    $vision_image_filename = $data['vision_image_url'];

    // Handle image uploads
    $uploadDir = "assets/images/section/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // Helper function for image uploads
    function handleImageUpload($file_input_name, $current_filename, $upload_dir, &$errors_array)
    {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            if ($_FILES[$file_input_name]['size'] > 2 * 1024 * 1024) {
                $errors_array[] = ucfirst($file_input_name) . " size exceeds 2MB.";
                return $current_filename;
            }
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array(mime_content_type($_FILES[$file_input_name]['tmp_name']), $allowed_types)) {
                $errors_array[] = "Invalid file type for " . $file_input_name . ".";
                return $current_filename;
            }
            $new_filename = uniqid($file_input_name . '-') . '.' . strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $upload_dir . $new_filename)) {
                return $new_filename;
            } else {
                $errors_array[] = "Failed to upload " . $file_input_name . ".";
            }
        }
        return $current_filename;
    }

    $mission_image_filename = handleImageUpload('mission_image', $mission_image_filename, $uploadDir, $errors);
    $vision_image_filename = handleImageUpload('vision_image', $vision_image_filename, $uploadDir, $errors);

    if (empty($errors)) {
        // Update the 'mission_vision' table (background_image_url removed)
        $update_stmt = $pdo->prepare("UPDATE mission_vision SET 
            section_subtitle = :section_subtitle, section_title = :section_title,
            mission_heading = :mission_heading, mission_content = :mission_content,
            vision_heading = :vision_heading, vision_content = :vision_content,
            mission_image_url = :mission_image_url,
            vision_image_url = :vision_image_url
            WHERE id = :id");

        $update_result = $update_stmt->execute([
            'section_subtitle' => $section_subtitle,
            'section_title' => $section_title,
            'mission_heading' => $mission_heading,
            'mission_content' => $mission_content,
            'vision_heading' => $vision_heading,
            'vision_content' => $vision_content,
            'mission_image_url' => $mission_image_filename,
            'vision_image_url' => $vision_image_filename,
            'id' => 1
        ]);

        if ($update_result) {
            echo "<script>alert('Section Updated Successfully!'); window.location.href = 'vision-mission.php';</script>";
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
                                <h4 class="mb-sm-0 font-size-18">Vision & Mission</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Vision & Mission</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error_msg)
                                echo "<p>" . htmlspecialchars($error_msg) . "</p>"; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error))
                        echo "<div class='alert alert-danger'><p>" . htmlspecialchars($error) . "</p></div>"; ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="post" enctype="multipart/form-data">

                                    
                                        <h5 class="card-title text-primary mt-4">Mission content</h5>
                                      
                                        <div class="mb-3">
                                           
                                            <textarea name="mission_content" id="mission_content" class="form-control"
                                                rows="5"><?php echo htmlspecialchars($data['mission_content'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <hr>

                                        <h5 class="card-title text-primary mt-4">Vision content</h5>
                                       
                                        <div class="mb-3">
                                           
                                            <textarea name="vision_content" id="vision_content" class="form-control"
                                                rows="5"><?php echo htmlspecialchars($data['vision_content'] ?? ''); ?></textarea>
                                        </div>
                                       

                                        <div class="m-3 text-center">
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light">Update Section</button>
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
            CKEDITOR.replace('mission_content');
            CKEDITOR.replace('vision_content');
        </script>
</body>

</html>