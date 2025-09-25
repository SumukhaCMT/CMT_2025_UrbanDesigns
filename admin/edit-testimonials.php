<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$name = $designation = $review = "";
$testimonial_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
$stmt->execute([$testimonial_id]);
$testimonial_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testimonial_data) {
    echo "<script>alert('Testimonial not found!'); window.location.href = 'testimonials.php';</script>";
    exit();
}

// Prepopulate existing data
$name = $testimonial_data['name'];
$designation = $testimonial_data['designation'];
$review = $testimonial_data['review'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $review = trim($_POST['review']);

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) > 20) {
        $errors[] = "Name cannot exceed 20 characters.";
    }

    if (empty($designation)) {
        $errors[] = "Designation is required.";
    } elseif (strlen($designation) > 20) {
        $errors[] = "Designation cannot exceed 20 characters.";
    }

    if (empty($review)) {
        $errors[] = "Review is required.";
    } elseif (strlen($review) > 400) {
        $errors[] = "Review cannot exceed 400 characters.";
    }

    // Update database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, designation = ?, review = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $designation, $review, $testimonial_id]);

        echo "<script>alert('Testimonial updated successfully!');
          window.location.href = 'testimonials.php';</script>";
        exit();
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
                                <h4 class="mb-sm-0 font-size-18">
                                    Edit Testimonial
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="testimonials.php">Testimonials</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Edit Testimonial
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
                                <!-- Display errors if any -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form action="" method="POST" class="custom-validation">
                                    <div class="row">
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name (Max 20 characters):</label>
                                            <input type="text" id="name" name="name" class="form-control"
                                                value="<?php echo htmlspecialchars($name); ?>" maxlength="20"
                                                minlength="2" required placeholder="Enter name (2-20 characters)">
                                        </div>

                                        <!-- Designation -->
                                        <div class="col-md-6 mb-3">
                                            <label for="designation" class="form-label">Designation (Max 20
                                                characters):</label>
                                            <input type="text" id="designation" name="designation" class="form-control"
                                                value="<?php echo htmlspecialchars($designation); ?>" maxlength="20"
                                                minlength="2" required
                                                placeholder="Enter designation (2-20 characters)">
                                        </div>
                                    </div>

                                    <!-- Review -->
                                    <div class="mb-3">
                                        <label for="review" class="form-label">Review (Max 400 characters):</label>
                                        <textarea id="review" name="review" class="form-control" maxlength="400"
                                            minlength="10" rows="4" required
                                            placeholder="Write your review (10-400 characters)"><?php echo htmlspecialchars($review); ?></textarea>
                                    </div>

                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="testimonials.php" class="btn mx-3 btn-secondary">Cancel</a>
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
    <!-- validation -->
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>