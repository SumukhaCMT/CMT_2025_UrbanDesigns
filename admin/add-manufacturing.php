<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hub_title = trim($_POST['hub_title']);
    $hub_description = trim($_POST['hub_description']);
    $img_alt = trim($_POST['img_alt']);
    $img_title = trim($_POST['img_title']);
    $display_order = (int) $_POST['display_order'];
    $status = 'active'; // Default to active status

    // Validate input
    if (!empty($hub_title) && !empty($hub_description) && !empty($img_alt) && !empty($img_title)) {
        // Handle image upload
        $img_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $img_name = uniqid() . '.' . $file_extension;
                $upload_path = '../assets/images/models/' . $img_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Image uploaded successfully
                } else {
                    echo "<script>alert('Failed to upload image!');</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Invalid image type! Only JPEG, PNG, GIF, and WebP are allowed.');</script>";
                exit();
            }
        } else {
            echo "<script>alert('Please select an image!');</script>";
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO manufacturing_hubs (hub_title, hub_description, img_name, img_alt, img_title, display_order, status, is_section_title) VALUES (?, ?, ?, ?, ?, ?, ?, 'no')");
            $stmt->execute([$hub_title, $hub_description, $img_name, $img_alt, $img_title, $display_order, $status]);

            echo "<script>alert('Manufacturing hub added successfully!'); 
                window.location.href = 'manufacturing.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Please fill in all required fields!');</script>";
    }
}

// Get the next display order
$stmt = $pdo->prepare("SELECT MAX(display_order) as max_order FROM manufacturing_hubs WHERE is_section_title = 'no'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$next_order = ($result['max_order'] ?? 0) + 1;
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
                                <h4 class="mb-sm-0 font-size-18">Add Manufacturing Hub</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li><a href="manufacturing.php">Manufacturing Hubs</a> &nbsp; >&nbsp;
                                            &nbsp;</li>
                                        <li>Add Manufacturing Hub</li>
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
                                                minlength="2" maxlength="100" required placeholder="Enter hub title">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="display_order">Display Order</label>
                                            <input type="number" name="display_order" id="display_order"
                                                class="form-control" value="<?php echo $next_order; ?>" min="1"
                                                required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="hub_description">Hub Description
                                                (required)</label>
                                            <textarea name="hub_description" id="hub_description" class="form-control"
                                                rows="4" required placeholder="Enter hub description"></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="image">Image (required)</label>
                                            <input type="file" name="image" id="image" class="form-control"
                                                accept="image/*" required>
                                            <small class="text-muted">Recommended size: 400x300px. Max file size:
                                                2MB</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <!-- Status removed - defaults to active -->
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="img_alt">Image Alt Text (required)</label>
                                            <input type="text" name="img_alt" id="img_alt" class="form-control"
                                                minlength="2" maxlength="255" required
                                                placeholder="Enter alt text for SEO">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="img_title">Image Title (required)</label>
                                            <input type="text" name="img_title" id="img_title" class="form-control"
                                                minlength="2" maxlength="255" required
                                                placeholder="Enter title for image">
                                        </div>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Add
                                            Manufacturing Hub</button>
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