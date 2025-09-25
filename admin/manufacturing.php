<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

// Handle title and section heading update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_title']) && isset($_POST['section_heading'])) {
    $section_title = trim($_POST['section_title']);
    $section_heading = trim($_POST['section_heading']);

    // Validate and update title
    if (!empty($section_title) && strlen($section_title) >= 2 && strlen($section_title) <= 200 && !empty($section_heading) && strlen($section_heading) >= 1 && strlen($section_heading) <= 100) {
        try {
            $update_stmt = $pdo->prepare("UPDATE manufacturing_hubs SET section_title = :section_title, section_heading = :section_heading WHERE is_section_title = 'yes'");
            $update_stmt->execute([
                ':section_title' => $section_title,
                ':section_heading' => $section_heading
            ]);

            echo "<script>alert('Section Heading, Title Updated Successfully!'); 
                window.location.href = 'manufacturing.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid title or heading! Title must be between 2 and 200 characters, heading between 1 and 100 characters.');</script>";
    }
}

// Fetch all manufacturing hubs (excluding section title record)
$stmt = $pdo->prepare("SELECT * FROM manufacturing_hubs WHERE is_section_title = 'no' ORDER BY display_order ASC, created_at DESC");
$stmt->execute();
$manufacturing_hubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch manufacturing hub to get the image name
    $stmt = $pdo->prepare("SELECT img_name FROM manufacturing_hubs WHERE id = ? AND is_section_title = 'no'");
    $stmt->execute([$delete_id]);
    $hub = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($hub && $hub['img_name']) {
        $image_path = "../assets/images/models/" . $hub['img_name'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image from the server
        }
    }

    // Delete the manufacturing hub from the database
    $stmt = $pdo->prepare("DELETE FROM manufacturing_hubs WHERE id = ? AND is_section_title = 'no'");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Manufacturing hub deleted successfully!'); 
    window.location.href = 'manufacturing.php';</script>";
    exit();
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $hub_id = $_POST['toggle_status'];
    $new_status = $_POST['new_status'];

    $stmt = $pdo->prepare("UPDATE manufacturing_hubs SET status = ? WHERE id = ? AND is_section_title = 'no'");
    $stmt->execute([$new_status, $hub_id]);

    echo "<script>alert('Status updated successfully!'); 
    window.location.href = 'manufacturing.php';</script>";
    exit();
}

// Fetch existing section title for the form
$section_stmt = $pdo->prepare("SELECT * FROM manufacturing_hubs WHERE is_section_title = 'yes' LIMIT 1");
$section_stmt->execute();
$section_data = $section_stmt->fetch(PDO::FETCH_ASSOC);
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
                                    Manufacturing Hubs
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Manufacturing Hubs
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
                                <form method="POST" action="" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="section_heading">Section Heading
                                                (required)</label>
                                            <input type="text" name="section_heading" id="section_heading"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($section_data['section_heading']); ?>"
                                                minlength="1" maxlength="100" required
                                                placeholder="Minimum 1 character">
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label" for="section_title">Section Title
                                                (required)</label>
                                            <input type="text" name="section_title" id="section_title"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($section_data['section_title']); ?>"
                                                minlength="2" maxlength="200" required
                                                placeholder="Minimum 2 characters">
                                        </div>
                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Update</button>
                                    </div>
                                </form>
                                <br>
                                <hr>
                                <div class="manufacturing-hubs">
                                    <div class="m-3 text-center">
                                        <a href="add-manufacturing.php" class="btn btn-primary mt-4">Add New
                                            Manufacturing Hub</a>
                                    </div>

                                    <div class="table-rep-plugin">
                                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sl. No</th>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Image</th>
                                                        <th>Img Alt</th>
                                                        <th>Img Title</th>
                                                        <th>Order</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sl_no = 1;
                                                    foreach ($manufacturing_hubs as $hub): ?>
                                                        <tr>
                                                            <td><?php echo $sl_no; ?></td>
                                                            <td><?php echo htmlspecialchars($hub['hub_title']); ?></td>
                                                            <td><?php echo htmlspecialchars(substr($hub['hub_description'], 0, 50)) . '...'; ?>
                                                            </td>
                                                            <td>
                                                                <img src="<?php echo htmlspecialchars($hub['img_name']) ? '../assets/images/models/' . htmlspecialchars($hub['img_name']) : '../assets/images/models/default.png'; ?>"
                                                                    alt="<?php echo htmlspecialchars($hub['img_alt']); ?>"
                                                                    width="50" height="50" class="img-thumbnail">
                                                            </td>
                                                            <td><?php echo htmlspecialchars($hub['img_alt']); ?></td>
                                                            <td><?php echo htmlspecialchars($hub['img_title']); ?></td>
                                                            <td><?php echo $hub['display_order']; ?></td>
                                                            <td>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="toggle_status"
                                                                        value="<?php echo $hub['id']; ?>">
                                                                    <input type="hidden" name="new_status"
                                                                        value="<?php echo $hub['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                                    <button type="submit"
                                                                        class="btn btn-sm <?php echo $hub['status'] === 'active' ? 'btn-success' : 'btn-secondary'; ?>">
                                                                        <?php echo ucfirst($hub['status']); ?>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                            <td>
                                                                <div class="button-container">
                                                                    <a href="edit-manufacturing.php?id=<?php echo $hub['id']; ?>"
                                                                        class="btn btn-primary" data-bs-toggle="tooltip"
                                                                        title="Edit">
                                                                        <i class="bx bxs-pencil"></i>
                                                                    </a>
                                                                    <form method="POST" class="d-inline"
                                                                        onsubmit="return confirmDelete();">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $hub['id']; ?>">
                                                                        <button type="submit" class="btn btn-danger"
                                                                            data-bs-toggle="tooltip" title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $sl_no++;
                                                    endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this manufacturing hub?');
            }
        </script>
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