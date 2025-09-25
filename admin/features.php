<?php
// Session and error handling
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

// Handle form submission for updating features title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && isset($_POST['section_heading'])) {
    $title = trim($_POST['title']);
    $section_heading = trim($_POST['section_heading']);

    // Validate and update title
    if (!empty($title) && strlen($title) >= 2 && strlen($title) <= 50 && !empty($section_heading) && strlen($section_heading) >= 1 && strlen($section_heading) <= 50) {
        try {
            $update_stmt = $pdo->prepare("UPDATE features_title SET title = :title, section_heading=:section_heading WHERE id = :id");
            $update_stmt->execute([
                ':title' => $title,
                ':section_heading' => $section_heading,
                ':id' => 1
            ]);

            // Redirect to prevent form resubmission
            echo "<script>alert('Section Heading, Title Updated Successfully!'); 
                window.location.href = 'features.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid title or heading! Title must be between 2 and 50 characters.');</script>";
    }
}

// Fetch all features
$stmt = $pdo->prepare("SELECT * FROM features ORDER BY created_at DESC");
$stmt->execute();
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch image details to unlink the image file
    $stmt = $pdo->prepare("SELECT img_name FROM features WHERE id = ?");
    $stmt->execute([$delete_id]);
    $feature = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feature && $feature['img_name']) {
        $image_path = "../assets/images/features/" . $feature['img_name'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete the feature from the database
    $stmt = $pdo->prepare("DELETE FROM features WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Feature deleted successfully!'); 
    window.location.href = 'features.php';</script>";
    exit();
}

// Fetch existing title for the form
$features_title_stmt = $pdo->prepare("SELECT * FROM features_title WHERE id = 1");
$features_title_stmt->execute();
$result = $features_title_stmt->fetch(PDO::FETCH_ASSOC);
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
                                    Our Features
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Our Features
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
                                            <label class="form-label" for="section_heading">Section Heading (required)</label>
                                            <input type="text" name="section_heading" id="section_heading" class="form-control"
                                                value="<?php echo htmlspecialchars($result['section_heading']); ?>"
                                                minlength="1" maxlength="50" required placeholder="Minimum 1 character">
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label" for="title">Tagline (required)</label>
                                            <input type="text" name="title" id="title" class="form-control"
                                                value="<?php echo htmlspecialchars($result['title']); ?>"
                                                minlength="2" maxlength="50" required placeholder="Minimum 2 characters">
                                        </div>

                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
                                    </div>
                                </form>
                                <br>
                                <hr>

                                <!-- Add New Feature Button -->
                                <div class="m-3 text-center">
                                    <a href="add-feature.php" class="btn btn-primary mt-4">Add a New Feature</a>
                                </div>

                                <!-- Features Table -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Feature</th>
                                                    <th>Image</th>
                                                    <th>Image Name</th>
                                                    <th>Alt Text</th>
                                                    <th>Image Title</th>
                                                    <th>Added On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($features as $feature) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><?php echo htmlspecialchars_decode($feature['feature']); ?></td>
                                                        <td>
                                                            <img src="../assets/images/features/<?php echo $feature['img_name']; ?>"
                                                                alt="<?php echo htmlspecialchars($feature['img_alt']); ?>"
                                                                title="<?php echo htmlspecialchars($feature['img_title']); ?>"
                                                                width="100" height="50" class="img-thumbnail">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($feature['img_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($feature['img_alt']); ?></td>
                                                        <td><?php echo htmlspecialchars($feature['img_title']); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($feature['created_at'])); ?></td>
                                                        <td>
                                                            <!-- Action Buttons -->
                                                            <div class="button-container">
                                                                <!-- Edit Button with Tooltip -->
                                                                <a href="edit-feature.php?id=<?php echo $feature['id']; ?>"
                                                                    class="btn btn-primary"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Edit">
                                                                    <i class="bx bxs-pencil"></i> <!-- Edit Icon -->
                                                                </a>
                                                                <!-- Delete Button with Tooltip -->
                                                                <form method="POST" class="d-inline" onsubmit="return confirmDelete();">
                                                                    <input type="hidden" name="delete_id" value="<?php echo $feature['id']; ?>">
                                                                    <button type="submit"
                                                                        class="btn btn-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        title="Delete">
                                                                        <i class="bx bx-trash"></i> <!-- Delete Icon -->
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    $sl_no++;
                                                }
                                                ?>
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
        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this item?');
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