<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>

<?php
// Include database connection
include 'shared_components/db.php';

// Initialize error messages and success flag
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // If no errors, insert data into the database
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO testimonials (name, designation, review) VALUES (?, ?, ?)");
        $stmt->execute([$name, $designation, $review]);
        $success = true;

        // Redirect to testimonials page after successful insertion
        echo "<script>alert('Testimonial added successfully!'); 
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
                                    Add Testimonials
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
                                            Add Testimonials
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

                                <!-- Success Message -->
                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success">
                                        Testimonial added successfully!
                                    </div>
                                <?php endif; ?>

                                <!-- Form -->
                                <form method="post" action="" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name (Max 20 characters):</label>
                                            <input type="text" class="form-control" id="name" name="name" maxlength="20"
                                                minlength="2" value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                                required placeholder="Enter name (2-20 characters)">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="designation" class="form-label">Designation (Max 20
                                                characters):</label>
                                            <input type="text" class="form-control" id="designation" name="designation"
                                                maxlength="20" minlength="2"
                                                value="<?php echo htmlspecialchars($designation ?? ''); ?>" required
                                                placeholder="Enter designation (2-20 characters)">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="review" class="form-label">Review (Max 400 characters):</label>
                                        <textarea class="form-control" id="review" name="review" maxlength="400"
                                            minlength="10" rows="4" required
                                            placeholder="Write your review (10-400 characters)"><?php echo htmlspecialchars($review ?? ''); ?></textarea>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Add New
                                            Testimonial</button>
                                        <a href="testimonials.php"
                                            class="btn btn-secondary waves-effect mx-2">Cancel</a>
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