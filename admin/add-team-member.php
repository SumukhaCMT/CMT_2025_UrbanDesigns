<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$name = $designation = $img_name = $img_alt = $img_title = $facebook_link = $twitter_link = $linkedin_link = $gmail_link = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : $errors['name'] = "Name is required.";
    $designation = !empty($_POST['designation']) ? htmlspecialchars($_POST['designation']) : $errors['designation'] = "Designation is required."; // Corrected line
    $img_alt = !empty($_POST['img_alt']) ? htmlspecialchars($_POST['img_alt']) : $errors['img_alt'] = "Image alt text is required.";
    $img_name_input = !empty($_POST['img_name']) ? htmlspecialchars($_POST['img_name']) : $errors['img_name'] = "Image name is required.";
    $img_title = !empty($_POST['img_title']) ? htmlspecialchars($_POST['img_title']) : $errors['img_title'] = "Image title is required.";

    $facebook_link = htmlspecialchars($_POST['facebook_link'] ?? '');
    $twitter_link = htmlspecialchars($_POST['twitter_link'] ?? '');
    $linkedin_link = htmlspecialchars($_POST['linkedin_link'] ?? '');
    $gmail_link = htmlspecialchars($_POST['gmail_link'] ?? '');

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size exceeds the 2MB limit.";
        } elseif (!in_array($_FILES['image']['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'image/x-ms-bmp'])) {
            $errors['image'] = "Only JPG, PNG, GIF, BMP, and TIFF formats are allowed.";
        } else {
            $unique_id = uniqid();
            $original_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $safe_name = preg_replace('/\s+/', '-', strtolower($img_name_input));

            $final_img_name = $safe_name . '-' . $unique_id . '.' . $original_extension;

            convertToImage($_FILES['image'], "assets/images/team/$final_img_name", $original_extension);
            $img_name = $final_img_name;
        }
    } else {
        $errors['image'] = "Image upload failed or no image selected.";
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO team (name, designation, img_name, img_alt, img_title, facebook_link, twitter_link, linkedin_link, gmail_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $designation, $img_name, $img_alt, $img_title, $facebook_link, $twitter_link, $linkedin_link, $gmail_link]);

        echo "<script>alert('Team member added successfully!'); window.location.href = 'team.php';</script>";
        exit();
    }
}

// Function to convert image to specified format (JPEG/PNG/GIF/BMP/TIFF)
function convertToImage($file, $outputPath, $outputFormat)
{
    $image = null;
    $imageType = exif_imagetype($file['tmp_name']);

    // Create image resource based on input type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file['tmp_name']);
            break;
        case IMAGETYPE_BMP:
            if (function_exists('imagecreatefrombmp')) {
                $image = imagecreatefrombmp($file['tmp_name']);
            } else {
                // Fallback for older PHP versions
                $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
            }
            break;
        case IMAGETYPE_TIFF_II:
        case IMAGETYPE_TIFF_MM:
            if (function_exists('imagecreatefromstring')) {
                $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
            } else {
                die("TIFF format is not supported on this server.");
            }
            break;
        case IMAGETYPE_XBM:
            if (function_exists('imagecreatefromxbm')) {
                $image = imagecreatefromxbm($file['tmp_name']);
            } else {
                die("XBM format is not supported on this server.");
            }
            break;
        case IMAGETYPE_WBMP:
            if (function_exists('imagecreatefromwbmp')) {
                $image = imagecreatefromwbmp($file['tmp_name']);
            } else {
                die("WBMP format is not supported on this server.");
            }
            break;
        default:
            die("Unsupported image type for conversion.");
    }

    if (!$image) {
        die("Could not create image resource.");
    }

    // Ensure output directory exists
    $outputDir = dirname($outputPath);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Save the image in the specified format
    $success = false;
    switch (strtolower($outputFormat)) {
        case 'jpeg':
        case 'jpg':
            $success = imagejpeg($image, $outputPath, 90);
            break;
        case 'png':
            // Preserve transparency for PNG
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $success = imagepng($image, $outputPath);
            break;
        case 'gif':
            $success = imagegif($image, $outputPath);
            break;
        case 'bmp':
            if (function_exists('imagebmp')) {
                $success = imagebmp($image, $outputPath);
            } else {
                // Convert to PNG if BMP output is not supported
                $success = imagepng($image, str_replace('.bmp', '.png', $outputPath));
            }
            break;
        case 'tiff':
        case 'tif':
            // TIFF output is not directly supported by GD, convert to PNG
            $success = imagepng($image, str_replace(['.tiff', '.tif'], '.png', $outputPath));
            break;
        case 'xbm':
            if (function_exists('imagexbm')) {
                $success = imagexbm($image, $outputPath);
            } else {
                // Convert to PNG if XBM output is not supported
                $success = imagepng($image, str_replace('.xbm', '.png', $outputPath));
            }
            break;
        case 'wbmp':
            if (function_exists('imagewbmp')) {
                $success = imagewbmp($image, $outputPath);
            } else {
                // Convert to PNG if WBMP output is not supported
                $success = imagepng($image, str_replace('.wbmp', '.png', $outputPath));
            }
            break;
        default:
            die("Unsupported output format specified.");
    }

    imagedestroy($image);

    if (!$success) {
        die("Failed to save the converted image.");
    }

    // Verify the file was actually created
    if (!file_exists($outputPath)) {
        die("Image file was not created successfully.");
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
                                <h4 class="mb-sm-0 font-size-18">
                                    Add Team Members
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="team.php">Team Members</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Add Team Members
                                        </li>
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
                                    <div class="alert alert-danger">
                                        <p>
                                            <?php foreach ($errors as $error): ?>
                                                <?php echo htmlspecialchars($error); ?>
                                            <?php endforeach; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <form action="" method="POST" enctype="multipart/form-data" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Team Member Name:</label>
                                            <input type="text" id="name" name="name" class="form-control"
                                                value="<?php echo htmlspecialchars($name ?? ''); ?>" maxlength="50"
                                                minlength="2" required placeholder="Enter team member's name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="designation" class="form-label">Designation:</label>
                                            <input type="text" id="designation" name="designation" class="form-control"
                                                value="<?php echo htmlspecialchars($designation ?? ''); ?>"
                                                maxlength="50" minlength="2" required placeholder="Enter designation">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="img_name" class="form-label">Image Name (without
                                                extension):</label>
                                            <input type="text" id="img_name" name="img_name" class="form-control"
                                                value="<?php echo htmlspecialchars($img_name_input ?? ''); ?>"
                                                maxlength="50" required
                                                placeholder="Enter image name (without extension)">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="image" class="form-label">Upload Image:</label>
                                            <input type="file" id="image" name="image" class="form-control"
                                                accept="image/jpeg,image/png,image/gif,image/bmp,image/tiff" required>
                                            <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, BMP,
                                                TIFF. Max
                                                size: 2MB.</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="img_alt" class="form-label">Image Alt Text:</label>
                                            <input type="text" id="img_alt" name="img_alt" class="form-control"
                                                value="<?php echo htmlspecialchars($img_alt ?? ''); ?>" maxlength="100"
                                                required placeholder="Enter image alt text">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="img_title" class="form-label">Image Title:</label>
                                            <input type="text" id="img_title" name="img_title" class="form-control"
                                                value="<?php echo htmlspecialchars($img_title ?? ''); ?>"
                                                maxlength="100" required placeholder="Enter image title text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="facebook_link" class="form-label">Facebook Link:</label>
                                            <input type="url" id="facebook_link" name="facebook_link"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($facebook_link ?? ''); ?>"
                                                placeholder="Enter Facebook profile link (optional)">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="twitter_link" class="form-label">Twitter Link:</label>
                                            <input type="url" id="twitter_link" name="twitter_link" class="form-control"
                                                value="<?php echo htmlspecialchars($twitter_link ?? ''); ?>"
                                                placeholder="Enter Twitter profile link (optional)">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="linkedin_link" class="form-label">LinkedIn Link:</label>
                                            <input type="url" id="linkedin_link" name="linkedin_link"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($linkedin_link ?? ''); ?>"
                                                placeholder="Enter LinkedIn profile link (optional)">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="gmail_link" class="form-label">Gmail Link:</label>
                                            <input type="url" id="gmail_link" name="gmail_link" class="form-control"
                                                value="<?php echo htmlspecialchars($gmail_link ?? ''); ?>"
                                                placeholder="Enter Gmail link (optional)">
                                        </div>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Add Team Member</button>
                                        <a href="team.php" class="btn btn-secondary mx-2">Cancel</a>
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
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>