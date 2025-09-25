<?php
require './shared_components/session.php';
require './shared_components/error.php';

include 'shared_components/db.php';

// Initialize errors array
$errors = [];

// Fetch the current value from the table
$stmt = $pdo->prepare("SELECT * FROM why_choose_us WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$why_choose_us = $stmt->fetch(PDO::FETCH_ASSOC);

// Function to handle icon upload and cropping
function handleIconUpload($point_number, $cropped_data) {
    if (empty($cropped_data)) {
        return null;
    }

    // Decode base64 image
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $cropped_data));
    
    if ($image_data === false) {
        return null;
    }

    // Create upload directory if it doesn't exist
    $uploadDir = "assets/images/custom_icons/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $filename = 'point_' . $point_number . '_icon_' . uniqid() . '.png';
    $filepath = $uploadDir . $filename;

    // Save the image
    if (file_put_contents($filepath, $image_data)) {
        return 'admin/' . $filepath;
    }

    return null;
}

// Function to delete old icon file
function deleteOldIconFile($filepath) {
    if (!empty($filepath) && file_exists(str_replace('admin/', '', $filepath))) {
        unlink(str_replace('admin/', '', $filepath));
    }
}

// Check if form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_description = trim($_POST['section_description']);
    $image_title = isset($_POST['image_title']) ? htmlspecialchars($_POST['image_title']) : '';
    $image_name_input = isset($_POST['image_name']) ? htmlspecialchars(trim(preg_replace('/\s+/', '-', $_POST['image_name']))) : '';
    $image_alt = isset($_POST['image_alt']) ? htmlspecialchars($_POST['image_alt']) : '';

    // Initialize image variables with current values
    $image_filename = $why_choose_us['background_image']; // Keep existing image if no new upload
    $final_image_name = $why_choose_us['image_name'];
    $final_image_title = $why_choose_us['img_title'];
    $final_image_alt = $why_choose_us['img_alt'];

    // Handle background image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Validate file type and size
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } else {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                $errors['image'] = "Only JPG, PNG, and WebP formats are allowed.";
            } else {
                $uploadDir = "assets/images/whychooseus/";

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Get file extension
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

                // Generate unique filename
                $image_filename = uniqid($image_name_input . '-') . '.' . $file_extension;
                $upload_path = $uploadDir . $image_filename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old background image if it exists
                    if (!empty($why_choose_us['background_image']) && file_exists(str_replace('admin/', '', $why_choose_us['background_image']))) {
                        unlink(str_replace('admin/', '', $why_choose_us['background_image']));
                    }
                    
                    // Update image path to include admin/ prefix for database storage
                    $image_filename = 'admin/' . $uploadDir . $image_filename;
                    $final_image_name = $image_name_input;
                    $final_image_title = $image_title;
                    $final_image_alt = $image_alt;
                } else {
                    $errors['image'] = "Failed to upload image.";
                }
            }
        }
    } else {
        // If no new image uploaded, update only the text fields if provided
        if (!empty($image_name_input)) {
            $final_image_name = $image_name_input;
        }
        if (!empty($image_title)) {
            $final_image_title = $image_title;
        }
        if (!empty($image_alt)) {
            $final_image_alt = $image_alt;
        }
    }

    // Check if "same icon for all" is selected
    $same_icon_for_all = isset($_POST['same_icon_for_all']) && $_POST['same_icon_for_all'] == '1';
    $master_icon_file = null;

    // Process all 5 points with custom icons only
    $points = [];
    for ($i = 1; $i <= 5; $i++) {
        $title = !empty(trim($_POST["point_{$i}_title"])) ? trim($_POST["point_{$i}_title"]) : null;
        $description = !empty(trim($_POST["point_{$i}_description"])) ? trim($_POST["point_{$i}_description"]) : null;
        
        $icon_file = $why_choose_us["point_{$i}_icon_file"]; // Keep existing file
        
        // Handle custom icon upload
        if (!empty($_POST["point_{$i}_cropped_icon"])) {
            $uploaded_file = handleIconUpload($i, $_POST["point_{$i}_cropped_icon"]);
            if ($uploaded_file) {
                // Delete old icon file if it exists
                deleteOldIconFile($icon_file);
                $icon_file = $uploaded_file;
                
                // If this is the first uploaded icon and "same for all" is selected
                if ($same_icon_for_all && !$master_icon_file) {
                    $master_icon_file = $uploaded_file;
                }
            }
        }

        $points[$i] = [
            'title' => $title,
            'description' => $description,
            'icon_file' => $icon_file
        ];
    }

    // If "same icon for all" is selected, apply master icon to all points
    if ($same_icon_for_all && $master_icon_file) {
        for ($i = 1; $i <= 5; $i++) {
            // Delete old icons except the master one
            if ($points[$i]['icon_file'] && $points[$i]['icon_file'] !== $master_icon_file) {
                deleteOldIconFile($points[$i]['icon_file']);
            }
            $points[$i]['icon_file'] = $master_icon_file;
        }
    }

    // Validate inputs
    if (!empty($section_description) && empty($errors)) {
        $update_stmt = $pdo->prepare("UPDATE why_choose_us SET 
            section_description = :section_description,
            background_image = :background_image,
            image_name = :image_name,
            img_title = :img_title,
            img_alt = :img_alt,
            point_1_title = :point_1_title,
            point_1_description = :point_1_description,
            point_1_icon_file = :point_1_icon_file,
            point_2_title = :point_2_title,
            point_2_description = :point_2_description,
            point_2_icon_file = :point_2_icon_file,
            point_3_title = :point_3_title,
            point_3_description = :point_3_description,
            point_3_icon_file = :point_3_icon_file,
            point_4_title = :point_4_title,
            point_4_description = :point_4_description,
            point_4_icon_file = :point_4_icon_file,
            point_5_title = :point_5_title,
            point_5_description = :point_5_description,
            point_5_icon_file = :point_5_icon_file,
            updated_at = NOW()
            WHERE id = :id");

        $update_result = $update_stmt->execute([
            'section_description' => $section_description,
            'background_image' => $image_filename,
            'image_name' => $final_image_name,
            'img_title' => $final_image_title,
            'img_alt' => $final_image_alt,
            'point_1_title' => $points[1]['title'],
            'point_1_description' => $points[1]['description'],
            'point_1_icon_file' => $points[1]['icon_file'],
            'point_2_title' => $points[2]['title'],
            'point_2_description' => $points[2]['description'],
            'point_2_icon_file' => $points[2]['icon_file'],
            'point_3_title' => $points[3]['title'],
            'point_3_description' => $points[3]['description'],
            'point_3_icon_file' => $points[3]['icon_file'],
            'point_4_title' => $points[4]['title'],
            'point_4_description' => $points[4]['description'],
            'point_4_icon_file' => $points[4]['icon_file'],
            'point_5_title' => $points[5]['title'],
            'point_5_description' => $points[5]['description'],
            'point_5_icon_file' => $points[5]['icon_file'],
            'id' => 1
        ]);

        if ($update_result) {
            // Redirect to prevent resubmission and reload
            echo "<script>alert('Why Choose Us Updated Successfully!'); 
            window.location.href = 'why-choose-us.php';</script>";
            exit();
        } else {
            $error = "Failed to update database.";
        }
    } else {
        if (empty($section_description)) {
            $error = "Section Description is required.";
        }
    }
}

// Create default record if none exists
if (!$why_choose_us) {
    $create_stmt = $pdo->prepare("INSERT INTO why_choose_us (id, section_description, created_at) VALUES (1, 'Default section description', NOW())");
    $create_stmt->execute();
    
    // Fetch the newly created record
    $stmt = $pdo->prepare("SELECT * FROM why_choose_us WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $why_choose_us = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <script src="ckeditor/ckeditor.js"></script>
    <!-- Cropper.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <!-- Font Awesome for default icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom icon upload section */
        .icon-upload-section {
            margin-top: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .current-custom-icon {
            margin-bottom: 15px;
        }
        
        .current-custom-icon img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        /* Cropper modal styles */
        .crop-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
        }
        
        .crop-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
        }
        
        .crop-container {
            max-height: 400px;
            margin: 20px 0;
        }
        
        .crop-buttons {
            text-align: center;
            margin-top: 20px;
        }
        
        .crop-buttons button {
            margin: 0 10px;
        }

        /* Point sections styling */
        .points-section {
            border-top: 1px solid #e3e6f0;
            padding-top: 30px;
            margin-top: 30px;
        }

        .point-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            background-color: #fff;
        }

        .point-section h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e3e6f0;
        }

        .icon-upload-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .no-icon-message {
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            border: 2px dashed #dee2e6;
            margin-bottom: 15px;
        }

        .upload-area {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            background-color: #e3f2fd;
            border-color: #0056b3;
        }

        .upload-area.dragover {
            background-color: #e3f2fd;
            border-color: #0056b3;
        }

        .upload-area.disabled {
            opacity: 0.5;
            pointer-events: none;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .icon-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .icon-preview img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .success-message {
            color: #28a745;
            font-weight: 500;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
        }

        .file-input-label:hover {
            background-color: #0056b3;
        }

        .file-input-label.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* Same icon for all checkbox styling */
        .same-icon-checkbox {
            background-color: #e8f4fd;
            border: 1px solid #007bff;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .same-icon-checkbox input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .same-icon-checkbox label {
            color: #007bff;
            font-weight: 500;
            cursor: pointer;
            margin: 0;
        }

        .same-icon-info {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
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
                                <h4 class="mb-sm-0 font-size-18">Why Choose Us</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Why Choose Us</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->
                </div>

                <!-- Display errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error_msg): ?>
                            <p><?php echo htmlspecialchars($error_msg); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Main content -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="post" class="custom-validation" enctype="multipart/form-data">

                                    <!-- Current Image Display -->
                                    <?php if (!empty($why_choose_us['background_image'])): ?>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Current Background Image:</label>
                                                <div>
                                                    <img src="<?php echo htmlspecialchars(str_replace('admin/', '', $why_choose_us['background_image'])); ?>"
                                                        alt="<?php echo htmlspecialchars($why_choose_us['img_alt'] ?? 'Background Image'); ?>"
                                                        style="max-width: 200px; height: auto;border-radius: 10px;">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row">
                                        <!-- Image Upload -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Upload New Background Image (Optional):</label>
                                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                            <small class="form-text text-muted">Supported formats: JPG, PNG, WebP. Max size: 2MB. Leave empty to keep current image.</small>
                                        </div>
                                        <!-- Image Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_name" class="form-label">Image Name:</label>
                                            <input type="text" id="image_name" name="image_name" class="form-control"
                                                value="<?php echo htmlspecialchars($why_choose_us['image_name'] ?? ''); ?>"
                                                maxlength="100" placeholder="Enter image name">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Image Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_title" class="form-label">Image Title:</label>
                                            <input type="text" id="image_title" name="image_title" class="form-control"
                                                value="<?php echo htmlspecialchars($why_choose_us['img_title'] ?? ''); ?>"
                                                maxlength="100" placeholder="Enter image title">
                                        </div>
                                        <!-- Image Alt Text -->
                                        <div class="col-md-6 mb-3">
                                            <label for="image_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="image_alt" name="image_alt" class="form-control"
                                                value="<?php echo htmlspecialchars($why_choose_us['img_alt'] ?? ''); ?>"
                                                maxlength="100" placeholder="Enter image alt text">
                                        </div>
                                    </div>

                                    <!-- Section Details -->
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="section_description">Section Description (required)</label>
                                            <textarea name="section_description" id="section_description"
                                                class="form-control" rows="3" minlength="10" maxlength="1000" required
                                                placeholder="Enter section description"><?php echo htmlspecialchars($why_choose_us['section_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Points Section -->
                                    <div class="points-section">
                                        <h5 class="card-title mb-4">Points (5 Fixed Points)</h5>

                                        <!-- Row 1: Points 1 and 2 -->
                                        <div class="row">
                                            <?php for ($i = 1; $i <= 2; $i++): ?>
                                            <div class="col-md-6">
                                                <div class="point-section">
                                                    <h6><i class="fas fa-edit"></i> Point <?php echo $i; ?></h6>
                                                    
                                                    <!-- Custom Icon Upload Section -->
                                                    <div class="icon-upload-section">
                                                        <label class="form-label"><i class="fas fa-image"></i> Custom Icon:</label>
                                                        
                                                        <!-- Same Icon For All Checkbox (will be shown only on first upload) -->
                                                        <div class="same-icon-checkbox" id="sameIconCheckbox_<?php echo $i; ?>" style="display: none;">
                                                            <input type="checkbox" id="same_icon_for_all_<?php echo $i; ?>" name="same_icon_for_all" value="1">
                                                            <label for="same_icon_for_all_<?php echo $i; ?>">
                                                                <i class="fas fa-copy"></i> Use same icon for all points
                                                            </label>
                                                            <div class="same-icon-info">This will apply the uploaded icon to all 5 points</div>
                                                        </div>

                                                        <!-- Current Custom Icon Display -->
                                                        <?php if (!empty($why_choose_us["point_{$i}_icon_file"])): ?>
                                                        <div class="icon-preview">
                                                            <img src="<?php echo htmlspecialchars(str_replace('admin/', '', $why_choose_us["point_{$i}_icon_file"])); ?>" 
                                                                 alt="Custom Icon <?php echo $i; ?>">
                                                            <div>
                                                                <strong>Current Icon</strong>
                                                                <br><small class="text-muted">Upload a new one to replace</small>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="no-icon-message">
                                                            <i class="fas fa-image fa-2x mb-2 text-muted"></i>
                                                            <br>No icon uploaded yet
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="upload-area" id="uploadArea_<?php echo $i; ?>">
                                                            <div class="file-input-wrapper">
                                                                <input type="file" id="icon_upload_<?php echo $i; ?>" 
                                                                       class="form-control" accept="image/*" 
                                                                       onchange="handleIconUpload(<?php echo $i; ?>, this)">
                                                                <label for="icon_upload_<?php echo $i; ?>" class="file-input-label" id="fileLabel_<?php echo $i; ?>">
                                                                    <i class="fas fa-upload"></i> Choose Icon File
                                                                </label>
                                                            </div>
                                                            <small class="form-text text-muted mt-2">
                                                                Upload a square image (recommended: 100x100px or larger). 
                                                                Supported formats: JPG, PNG, WebP. Max size: 1MB.
                                                            </small>
                                                        </div>
                                                        <input type="hidden" name="point_<?php echo $i; ?>_cropped_icon" id="point_<?php echo $i; ?>_cropped_icon">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_<?php echo $i; ?>_title">Title (optional)</label>
                                                        <input type="text" name="point_<?php echo $i; ?>_title" id="point_<?php echo $i; ?>_title"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($why_choose_us["point_{$i}_title"] ?? ''); ?>"
                                                            maxlength="200" placeholder="Enter point title">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_<?php echo $i; ?>_description">Description (optional)</label>
                                                        <textarea name="point_<?php echo $i; ?>_description"
                                                            id="point_<?php echo $i; ?>_description" class="form-control" rows="3"
                                                            maxlength="500"
                                                            placeholder="Enter point description"><?php echo htmlspecialchars($why_choose_us["point_{$i}_description"] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                        </div>

                                        <!-- Row 2: Points 3 and 4 -->
                                        <div class="row">
                                            <?php for ($i = 3; $i <= 4; $i++): ?>
                                            <div class="col-md-6">
                                                <div class="point-section">
                                                    <h6><i class="fas fa-edit"></i> Point <?php echo $i; ?></h6>
                                                    
                                                    <!-- Custom Icon Upload Section -->
                                                    <div class="icon-upload-section">
                                                        <label class="form-label"><i class="fas fa-image"></i> Custom Icon:</label>
                                                        
                                                        <!-- Same Icon For All Checkbox (will be shown only on first upload) -->
                                                        <div class="same-icon-checkbox" id="sameIconCheckbox_<?php echo $i; ?>" style="display: none;">
                                                            <input type="checkbox" id="same_icon_for_all_<?php echo $i; ?>" name="same_icon_for_all" value="1">
                                                            <label for="same_icon_for_all_<?php echo $i; ?>">
                                                                <i class="fas fa-copy"></i> Use same icon for all points
                                                            </label>
                                                            <div class="same-icon-info">This will apply the uploaded icon to all 5 points and disable other uploads.</div>
                                                        </div>

                                                        <!-- Current Custom Icon Display -->
                                                        <?php if (!empty($why_choose_us["point_{$i}_icon_file"])): ?>
                                                        <div class="icon-preview">
                                                            <img src="<?php echo htmlspecialchars(str_replace('admin/', '', $why_choose_us["point_{$i}_icon_file"])); ?>" 
                                                                 alt="Custom Icon <?php echo $i; ?>">
                                                            <div>
                                                                <strong>Current Icon</strong>
                                                                <br><small class="text-muted">Upload a new one to replace</small>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="no-icon-message">
                                                            <i class="fas fa-image fa-2x mb-2 text-muted"></i>
                                                            <br>No icon uploaded yet
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="upload-area" id="uploadArea_<?php echo $i; ?>">
                                                            <div class="file-input-wrapper">
                                                                <input type="file" id="icon_upload_<?php echo $i; ?>" 
                                                                       class="form-control" accept="image/*" 
                                                                       onchange="handleIconUpload(<?php echo $i; ?>, this)">
                                                                <label for="icon_upload_<?php echo $i; ?>" class="file-input-label" id="fileLabel_<?php echo $i; ?>">
                                                                    <i class="fas fa-upload"></i> Choose Icon File
                                                                </label>
                                                            </div>
                                                            <small class="form-text text-muted mt-2">
                                                                Upload a square image (recommended: 100x100px or larger). 
                                                                Supported formats: JPG, PNG, WebP. Max size: 1MB.
                                                            </small>
                                                        </div>
                                                        <input type="hidden" name="point_<?php echo $i; ?>_cropped_icon" id="point_<?php echo $i; ?>_cropped_icon">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_<?php echo $i; ?>_title">Title (optional)</label>
                                                        <input type="text" name="point_<?php echo $i; ?>_title" id="point_<?php echo $i; ?>_title"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($why_choose_us["point_{$i}_title"] ?? ''); ?>"
                                                            maxlength="200" placeholder="Enter point title">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_<?php echo $i; ?>_description">Description (optional)</label>
                                                        <textarea name="point_<?php echo $i; ?>_description"
                                                            id="point_<?php echo $i; ?>_description" class="form-control" rows="3"
                                                            maxlength="500"
                                                            placeholder="Enter point description"><?php echo htmlspecialchars($why_choose_us["point_{$i}_description"] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                        </div>

                                        <!-- Row 3: Point 5 (centered) -->
                                        <div class="row">
                                            <div class="col-md-6 mx-auto">
                                                <div class="point-section">
                                                    <h6><i class="fas fa-edit"></i> Point 5</h6>
                                                    
                                                    <!-- Custom Icon Upload Section -->
                                                    <div class="icon-upload-section">
                                                        <label class="form-label"><i class="fas fa-image"></i> Custom Icon:</label>
                                                        
                                                        <!-- Same Icon For All Checkbox (will be shown only on first upload) -->
                                                        <div class="same-icon-checkbox" id="sameIconCheckbox_5" style="display: none;">
                                                            <input type="checkbox" id="same_icon_for_all_5" name="same_icon_for_all" value="1">
                                                            <label for="same_icon_for_all_5">
                                                                <i class="fas fa-copy"></i> Use same icon for all points
                                                            </label>
                                                            <div class="same-icon-info">This will apply the uploaded icon to all 5 points and disable other uploads.</div>
                                                        </div>

                                                        <!-- Current Custom Icon Display -->
                                                        <?php if (!empty($why_choose_us['point_5_icon_file'])): ?>
                                                        <div class="icon-preview">
                                                            <img src="<?php echo htmlspecialchars(str_replace('admin/', '', $why_choose_us['point_5_icon_file'])); ?>" 
                                                                 alt="Custom Icon 5">
                                                            <div>
                                                                <strong>Current Icon</strong>
                                                                <br><small class="text-muted">Upload a new one to replace</small>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="no-icon-message">
                                                            <i class="fas fa-image fa-2x mb-2 text-muted"></i>
                                                            <br>No icon uploaded yet
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="upload-area" id="uploadArea_5">
                                                            <div class="file-input-wrapper">
                                                                <input type="file" id="icon_upload_5" 
                                                                       class="form-control" accept="image/*" 
                                                                       onchange="handleIconUpload(5, this)">
                                                                <label for="icon_upload_5" class="file-input-label" id="fileLabel_5">
                                                                    <i class="fas fa-upload"></i> Choose Icon File
                                                                </label>
                                                            </div>
                                                            <small class="form-text text-muted mt-2">
                                                                Upload a square image (recommended: 100x100px or larger). 
                                                                Supported formats: JPG, PNG, WebP. Max size: 1MB.
                                                            </small>
                                                        </div>
                                                        <input type="hidden" name="point_5_cropped_icon" id="point_5_cropped_icon">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_5_title">Title (optional)</label>
                                                        <input type="text" name="point_5_title" id="point_5_title"
                                                            class="form-control"
                                                            value="<?php echo htmlspecialchars($why_choose_us['point_5_title'] ?? ''); ?>"
                                                            maxlength="200" placeholder="Enter point title">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="point_5_description">Description (optional)</label>
                                                        <textarea name="point_5_description"
                                                            id="point_5_description" class="form-control" rows="3"
                                                            maxlength="500"
                                                            placeholder="Enter point description"><?php echo htmlspecialchars($why_choose_us['point_5_description'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                           Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Icon Cropping Modal -->
        <div id="cropModal" class="crop-modal">
            <div class="crop-modal-content">
                <h5><i class="fas fa-crop"></i> Crop Icon</h5>
                <p class="text-muted">Drag to position and resize the selection area to crop your icon into a perfect square.</p>
                <div class="crop-container">
                    <img id="cropImage" src="" alt="Crop Image">
                </div>
                <div class="crop-buttons">
                    <button type="button" class="btn btn-success btn-lg" id="cropAndSave">
                        <i class="fas fa-check"></i> Crop & Save
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" id="cancelCrop">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>

        <?php require './shared_components/footer.php'; ?>
    </div>
    
    <?php require './shared_components/scripts.php'; ?>
    <!-- Cropper.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
    
    <script>
        CKEDITOR.replace('section_description');

        let currentCropper = null;
        let currentPointNumber = null;
        let firstUploadDone = false;
        let sameIconCheckboxPoint = null;

        function handleIconUpload(pointNumber, input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (1MB limit)
                if (file.size > 1024 * 1024) {
                    alert('File size exceeds 1MB limit. Please choose a smaller image.');
                    input.value = '';
                    return;
                }

                // Validate file type
                if (!file.type.match(/^image\/(jpeg|jpg|png|webp)$/)) {
                    alert('Please select a valid image file (JPG, PNG, or WebP).');
                    input.value = '';
                    return;
                }

                currentPointNumber = pointNumber;

                // Show "Same icon for all" checkbox only on first upload
                if (!firstUploadDone) {
                    document.getElementById('sameIconCheckbox_' + pointNumber).style.display = 'block';
                    sameIconCheckboxPoint = pointNumber;
                    firstUploadDone = true;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const cropImage = document.getElementById('cropImage');
                    cropImage.src = e.target.result;
                    
                    // Show crop modal
                    document.getElementById('cropModal').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                    
                    // Initialize cropper
                    if (currentCropper) {
                        currentCropper.destroy();
                    }
                    
                    currentCropper = new Cropper(cropImage, {
                        aspectRatio: 1, // Square aspect ratio
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        // Function to toggle other upload areas based on "same icon for all" checkbox
        function toggleOtherUploads(isChecked) {
            for (let i = 1; i <= 5; i++) {
                if (i !== sameIconCheckboxPoint) {
                    const uploadArea = document.getElementById('uploadArea_' + i);
                    const fileInput = document.getElementById('icon_upload_' + i);
                    const fileLabel = document.getElementById('fileLabel_' + i);
                    
                    if (isChecked) {
                        uploadArea.classList.add('disabled');
                        fileInput.disabled = true;
                        fileLabel.classList.add('disabled');
                        fileLabel.innerHTML = '<i class="fas fa-lock"></i> Upload Disabled (Using same icon)';
                    } else {
                        uploadArea.classList.remove('disabled');
                        fileInput.disabled = false;
                        fileLabel.classList.remove('disabled');
                        fileLabel.innerHTML = '<i class="fas fa-upload"></i> Choose Icon File';
                    }
                }
            }
        }

        // Add event listeners for "same icon for all" checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            for (let i = 1; i <= 5; i++) {
                const checkbox = document.getElementById('same_icon_for_all_' + i);
                if (checkbox) {
                    checkbox.addEventListener('change', function() {
                        toggleOtherUploads(this.checked);
                    });
                }
            }
        });

        // Crop and save functionality
        document.getElementById('cropAndSave').addEventListener('click', function() {
            if (currentCropper && currentPointNumber) {
                // Get cropped canvas
                const canvas = currentCropper.getCroppedCanvas({
                    width: 100,
                    height: 100,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                // Convert to base64
                const croppedImageData = canvas.toDataURL('image/png');
                
                // Set the cropped image data to hidden input
                document.getElementById('point_' + currentPointNumber + '_cropped_icon').value = croppedImageData;
                
                // Close modal
                closeCropModal();
                
                // Show success message
                alert('Icon cropped successfully! Don\'t forget to save the form to apply changes.');
            }
        });

        // Cancel crop
        document.getElementById('cancelCrop').addEventListener('click', function() {
            // Clear the file input
            const fileInput = document.getElementById('icon_upload_' + currentPointNumber);
            if (fileInput) fileInput.value = '';
            
            // Hide the checkbox if it was the first upload
            if (sameIconCheckboxPoint === currentPointNumber && !document.getElementById('point_' + currentPointNumber + '_cropped_icon').value) {
                document.getElementById('sameIconCheckbox_' + currentPointNumber).style.display = 'none';
                firstUploadDone = false;
                sameIconCheckboxPoint = null;
            }
            
            closeCropModal();
        });

        function closeCropModal() {
            document.getElementById('cropModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            if (currentCropper) {
                currentCropper.destroy();
                currentCropper = null;
            }
            currentPointNumber = null;
        }

        // Close crop modal when clicking outside
        document.getElementById('cropModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeCropModal();
            }
        });

        // Add drag and drop functionality
        document.querySelectorAll('.upload-area').forEach(area => {
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!this.classList.contains('disabled')) {
                    this.classList.add('dragover');
                }
            });

            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            area.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                if (!this.classList.contains('disabled')) {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        const input = this.querySelector('input[type="file"]');
                        input.files = files;
                        
                        // Get point number from input id
                        const pointNumber = input.id.match(/\d+/)[0];
                        handleIconUpload(parseInt(pointNumber), input);
                    }
                }
            });
        });
    </script>
</body>

</html>