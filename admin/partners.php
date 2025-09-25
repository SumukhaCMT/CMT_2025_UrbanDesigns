<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$success_msg = '';

// Define the upload directory for logos
$uploadDir = "assets/images/partners/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// --- LOGIC TO HANDLE FORM SUBMISSIONS ---

// 1. Handle Deleting a Logo
if (isset($_GET['delete_logo']) && !empty($_GET['delete_logo'])) {
    $logo_id_to_delete = intval($_GET['delete_logo']);

    // First, get the filename to delete the actual file
    $stmt = $pdo->prepare("SELECT image_filename FROM partner_logos WHERE id = ?");
    $stmt->execute([$logo_id_to_delete]);
    $logo = $stmt->fetch();

    if ($logo && file_exists($uploadDir . $logo['image_filename'])) {
        unlink($uploadDir . $logo['image_filename']); // Delete the file from server
    }

    // Then, delete the record from the database
    $delete_stmt = $pdo->prepare("DELETE FROM partner_logos WHERE id = ?");
    if ($delete_stmt->execute([$logo_id_to_delete])) {
        $success_msg = "Logo deleted successfully!";
    }
}

// 2. Handle POST requests for updating text or adding a logo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. Handle "Update Section Content" form
    if (isset($_POST['update_content'])) {
        $partner_count = intval($_POST['partner_count']);
        $heading_text = trim($_POST['heading_text']);

        $update_stmt = $pdo->prepare("UPDATE partner_section SET partner_count = ?, heading_text = ? WHERE id = 1");
        if ($update_stmt->execute([$partner_count, $heading_text])) {
            $success_msg = "Section content updated successfully!";
        } else {
            $errors[] = "Failed to update content.";
        }
    }

    // B. Handle "Add New Logo" form
    if (isset($_POST['add_logo'])) {
        if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == UPLOAD_ERR_OK) {
            // Your validation logic here (size, type, etc.)
            $new_filename = uniqid('logo-') . '.' . strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $uploadDir . $new_filename)) {
                $alt_text = trim($_POST['image_alt'] ?? 'Partner Logo');
                $insert_stmt = $pdo->prepare("INSERT INTO partner_logos (image_filename, image_alt) VALUES (?, ?)");
                if ($insert_stmt->execute([$new_filename, $alt_text])) {
                    $success_msg = "New logo added successfully!";
                } else {
                    $errors[] = "Failed to save new logo to database.";
                }
            } else {
                $errors[] = "Failed to upload new logo file.";
            }
        } else {
            $errors[] = "Please select a file to upload.";
        }
    }
}

// --- LOGIC TO FETCH DATA FOR DISPLAY ---
$section_data = $pdo->query("SELECT * FROM partner_section WHERE id = 1")->fetch();
$logos = $pdo->query("SELECT * FROM partner_logos ORDER BY display_order ASC, id ASC")->fetchAll();

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
                                <h4 class="mb-sm-0 font-size-18">Partner Section</h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error)
                                echo "<p>$error</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Update Section Content</h4>
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Partner Count</label>
                                                <input type="number" name="partner_count" class="form-control"
                                                    value="<?php echo htmlspecialchars($section_data['partner_count']); ?>">
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Heading Text</label>
                                                <input type="text" name="heading_text" class="form-control"
                                                    value="<?php echo htmlspecialchars($section_data['heading_text']); ?>">
                                            </div>
                                        </div>
                                        <button type="submit" name="update_content" class="btn btn-primary">Update
                                            Content</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Manage Partner Logos</h4>

                                    <div class="d-flex flex-wrap align-items-center">
                                        <?php foreach ($logos as $logo): ?>
                                            <div class="text-center p-2 border m-2">
                                                <img src="<?php echo $uploadDir . htmlspecialchars($logo['image_filename']); ?>"
                                                    alt="<?php echo htmlspecialchars($logo['image_alt']); ?>"
                                                    style="height: 60px; max-width: 150px;">
                                                <a href="partners.php?delete_logo=<?php echo $logo['id']; ?>"
                                                    class="d-block text-danger mt-1"
                                                    onclick="return confirm('Are you sure you want to delete this logo?');">Delete</a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="card-title mb-3">Add New Logo</h5>
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Logo Image</label>
                                                <input type="file" name="logo_image" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Alt Text</label>
                                                <input type="text" name="image_alt" class="form-control"
                                                    placeholder="e.g., Company Name Logo">
                                            </div>
                                        </div>
                                        <button type="submit" name="add_logo" class="btn btn-success">Add Logo</button>
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
    </div>
</body>

</html>