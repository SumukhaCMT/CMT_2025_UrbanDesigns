<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

// Get hub ID from URL
$hub_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($hub_id === 0) {
    echo "<script>alert('Invalid manufacturing hub ID!'); window.location.href = 'manufacturing.php';</script>";
    exit();
}

// Fetch existing hub data
$stmt = $pdo->prepare("SELECT * FROM manufacturing_hubs WHERE id = ? AND is_section_title = 'no'");
$stmt->execute([$hub_id]);
$hub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hub) {
    echo "<script>alert('Manufacturing hub not found!'); window.location.href = 'manufacturing.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hub_title = trim($_POST['hub_title']);
    $hub_description = trim($_POST['hub_description']);
    $img_alt = trim($_POST['img_alt']);
    $img_title = trim($_POST['img_title']);
    $display_order = (int) $_POST['display_order'];
    $status = $hub['status']; // Keep existing status

    // Validate input
    if (!empty($hub_title) && !empty($hub_description) && !empty($img_alt) && !empty($img_title)) {
        $img_name = $hub['img_name']; // Keep existing image by default

        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_img_name = uniqid() . '.' . $file_extension;
                $upload_path = '../assets/images/models/' . $new_img_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists
                    if ($hub['img_name'] && file_exists('../assets/images/models/' . $hub['img_name'])) {
                        unlink('../assets/images/models/' . $hub['img_name']);
                    }
                    $img_name = $new_img_name;
                } else {
                    echo "<script>alert('Failed to upload image!');</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Invalid image type! Only JPEG, PNG, GIF, and WebP are allowed.');</script>";
                exit();
            }
        }

        try {
            $stmt = $pdo->prepare("UPDATE manufacturing_hubs SET hub_title = ?, hub_description = ?, img_name = ?, img_alt = ?, img_title = ?, display_order = ?, status = ? WHERE id = ? AND is_section_title = 'no'");
            $stmt->execute([$hub_title, $hub_description, $img_name, $img_alt, $img_title, $display_order, $status, $hub_id]);

            echo "<script>alert('Manufacturing hub updated successfully!'); 
                window.location.href = 'manufacturing.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Please fill in all required fields!');</script>";
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
                                <h4 class="mb-sm-0 font-size-18">Edit Manufacturing Hub</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li><a href="manufacturing.php">Manufacturing Hubs</a> &nbsp; >&nbsp;
                                            &nbsp;</li>
                                        <li>Edit Manufacturing Hub</li>
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
                                <form method="POST" action="" enctype="multipart/form-data" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="hub_title">Hub Title (required)</label>
                                            <input type="text" name="hub_title" id="hub_title" class="form-control"
                                                value="<?php echo htmlspecialchars($hub['hub_title']); ?>" minlength="2"
                                                maxlength="100" required placeholder="Enter hub title">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="display_order">Display Order</label>
                                            <input type="number" name="display_order" id="display_order"
                                                class="form-control" value="<?php echo $hub['display_order']; ?>"
                                                min="1" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="hub_description">Hub Description
                                                (required)</label>
                                            <textarea name="hub_description" id="hub_description" class="form-control"
                                                rows="4" required
                                                placeholder="Enter hub description"><?php echo htmlspecialchars($hub['hub_description']); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Current Image:</label>


                                            <?php if ($hub['img_name']): ?>
                                                <div class="mt-2">
                                                    <img src="../assets/images/models/<?php echo htmlspecialchars($hub['img_name']); ?>"
                                                        alt="Current image" width="200" height="auto"
                                                        class="img-thumbnail"></img>

                                                </div>

                                            <?php endif; ?>
                                            <small>
                                                <?php echo htmlspecialchars($hub['img_name']); ?></small>
                                            <br>
                                            <label class="form-label" for="image">Image (optional - leave blank to keep
                                                current)</label>
                                            <input type="file" name="image" id="image" class="form-control"
                                                accept="image/*">
                                            <small class="text-muted">Recommended size: 400x300px. Max file size:
                                                2MB</small>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="img_alt">Image Alt Text (required)</label>
                                            <input type="text" name="img_alt" id="img_alt" class="form-control"
                                                value="<?php echo htmlspecialchars($hub['img_alt']); ?>" minlength="2"
                                                maxlength="255" required placeholder="Enter alt text for SEO">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="img_title">Image Title (required)</label>
                                            <input type="text" name="img_title" id="img_title" class="form-control"
                                                value="<?php echo htmlspecialchars($hub['img_title']); ?>" minlength="2"
                                                maxlength="255" required placeholder="Enter title for image">
                                        </div>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update
                                        </button>
                                        <a href="manufacturing.php" class="btn btn-secondary waves-effect">Cancel</a>
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
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>