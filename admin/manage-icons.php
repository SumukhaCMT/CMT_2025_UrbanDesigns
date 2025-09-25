<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$upload_dir = "assets/images/service_icons/";
$errors = [];

// Ensure the upload directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle Icon Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['icon_file'])) {
    $file = $_FILES['icon_file'];

    // --- Validation ---
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "An error occurred during upload.";
    } elseif ($file['size'] > 1 * 1024 * 1024) { // 1 MB limit
        $errors[] = "File is too large. Maximum size is 1MB.";
    } else {
        $allowed_types = ['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only SVG, PNG, JPG, and WEBP are allowed.";
        } else {
            // --- Process and Save File ---
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $safe_filename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($file['name'], "." . $file_extension));
            $new_filename = uniqid($safe_filename . '-', true) . '.' . $file_extension;

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                // Add to database
                $stmt = $pdo->prepare("INSERT INTO service_icons (filename) VALUES (?)");
                $stmt->execute([$new_filename]);
                echo "<script>alert('Icon uploaded successfully!'); window.location.href='manage-icons.php';</script>";
                exit();
            } else {
                $errors[] = "Failed to move the uploaded file.";
            }
        }
    }
}


// Handle Icon Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_icon'])) {
    $filename_to_delete = $_POST['delete_icon'];

    // 1. Check if the icon is currently used by any service
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM service WHERE img_name = ?");
    $stmt->execute([$filename_to_delete]);
    $usage_count = $stmt->fetchColumn();

    if ($usage_count > 0) {
        $errors[] = "Cannot delete this icon because it is currently being used by " . $usage_count . " service(s). Please un-assign it first.";
    } else {
        // 2. Delete file from server
        if (file_exists($upload_dir . $filename_to_delete)) {
            unlink($upload_dir . $filename_to_delete);
        }
        // 3. Delete record from database
        $stmt = $pdo->prepare("DELETE FROM service_icons WHERE filename = ?");
        $stmt->execute([$filename_to_delete]);
        echo "<script>alert('Icon deleted successfully!'); window.location.href='manage-icons.php';</script>";
        exit();
    }
}


// Fetch all icons for display
$icons_stmt = $pdo->query("SELECT filename FROM service_icons ORDER BY uploaded_at DESC");
$icons = $icons_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <title>Manage Service Icons</title>
    <style>
        .icon-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .icon-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 150px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .icon-card img {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
            object-fit: contain;
            margin-left: auto;
            margin-right: auto;
        }

        .icon-card .filename {
            font-size: 12px;
            word-wrap: break-word;
            color: #666;
            flex-grow: 1;
        }

        .icon-card form {
            margin-top: 15px;
        }
    </style>
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
                                <h4 class="mb-sm-0 font-size-18">Manage Service Icons</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Upload New Icon</h4>
                            <p class="card-title-desc">Upload SVG, PNG, JPG, or WEBP files. Max size 1MB.</p>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="icon_file" class="form-label">Icon File</label>
                                    <input class="form-control" type="file" name="icon_file" id="icon_file" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Icon</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Uploaded Icons</h4>
                            <div class="icon-gallery mt-4">
                                <?php if (empty($icons)): ?>
                                    <p>No icons have been uploaded yet.</p>
                                <?php else: ?>
                                    <?php foreach ($icons as $icon): ?>
                                        <div class="icon-card">
                                            <img src="<?php echo htmlspecialchars($upload_dir . $icon['filename']); ?>"
                                                alt="Service Icon">
                                            <p class="filename"><?php echo htmlspecialchars($icon['filename']); ?></p>
                                            <form method="POST"
                                                onsubmit="return confirm('Are you sure you want to permanently delete this icon? This cannot be undone.');">
                                                <input type="hidden" name="delete_icon"
                                                    value="<?php echo htmlspecialchars($icon['filename']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
        <?php require './shared_components/scripts.php'; ?>
</body>

</html>