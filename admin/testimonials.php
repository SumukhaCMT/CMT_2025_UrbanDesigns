<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Handle form submission for updating features title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && isset($_POST['section_heading'])) {
    $title = trim($_POST['title']);
    $section_heading = trim($_POST['section_heading']);

    // Validate and update title
    if (!empty($title) && strlen($title) >= 2 && strlen($title) <= 50 && !empty($section_heading) && strlen($section_heading) >= 1 && strlen($section_heading) <= 50) {
        try {
            $update_stmt = $pdo->prepare("UPDATE testimonials_title SET title = :title, section_heading=:section_heading WHERE id = :id");
            $update_stmt->execute([
                ':title' => $title,
                ':section_heading' => $section_heading,
                ':id' => 1
            ]);

            // Redirect to prevent form resubmission
            echo "<script>alert('Section Heading, Title Updated Successfully!'); 
                window.location.href = 'testimonials.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid title or heading! Title must be between 2 and 50 characters.');</script>";
    }
}

// Fetch all testimonials
$stmt = $pdo->prepare("SELECT * FROM testimonials ORDER BY created_at DESC");
$stmt->execute();
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the testimonial from the database
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Testimonial deleted successfully!'); 
    window.location.href = 'testimonials.php';</script>";
    exit();
}

// Fetch existing title for the form
$testimonials_title_stmt = $pdo->prepare("SELECT * FROM testimonials_title WHERE id = 1");
$testimonials_title_stmt->execute();
$result = $testimonials_title_stmt->fetch(PDO::FETCH_ASSOC);
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
                                    Testimonials
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Testimonials
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

                                <!-- Add New Testimonials Button -->
                                <div class="m-3 text-center">
                                    <a href="add-testimonials.php" class="btn btn-primary mt-4">Add Testimonials</a>
                                </div>

                                <!-- Table with data -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Name</th>
                                                    <th>Designation</th>
                                                    <th>Review</th>
                                                    <th>Created On</th>
                                                    <th>Updated On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($testimonials as $testimonial) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><?php echo htmlspecialchars($testimonial['name'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($testimonial['designation'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($testimonial['review'] ?? '-'); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($testimonial['created_at'])); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($testimonial['updated_at'])); ?></td>
                                                        <td>
                                                            <!-- Action Buttons -->
                                                            <div class="button-container">
                                                                <a href="edit-testimonials.php?id=<?php echo $testimonial['id']; ?>"
                                                                    class="btn btn-primary"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Edit">
                                                                    <i class="bx bxs-pencil"></i> <!-- Edit Icon -->
                                                                </a>
                                                                <form method="POST" class="d-inline" onsubmit="return confirmDelete();">
                                                                    <input type="hidden" name="delete_id" value="<?php echo $testimonial['id']; ?>">
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