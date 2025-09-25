<?php
session_start();
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$new_name = $current_name = "";

$user_email = $_SESSION['email'];

// Fetch existing user data based on email (use the session email to find the user)
$stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->execute([$user_email]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Prepopulate the current name from the database
$current_name = $user_data['name'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate new name input
    $new_name = !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : $errors['name'] = "Name is required.";

    // Check if the new name is unique
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE name = ?");
        $stmt->execute([$new_name]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $errors['name'] = "The name is already taken. Please choose another name.";
        }
    }

    // Update the user's name if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE admins SET name = ? WHERE email = ?");
        $stmt->execute([$new_name, $user_email]);

        // Update the session name after successful update
        $_SESSION['admin_name'] = $new_name;

        echo "<script>alert('Name updated successfully!');
              window.location.href = 'site_settings.php';</script>";
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
                                    Change Name
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Change Name
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
                                <form action="" method="POST" class="custom-validation">
                                    <!-- Current Name (Read-only) -->
                                    <div class="row">
                                        <!-- Display Validation Errors -->
                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger mt-3">
                                                <p>
                                                    <?php foreach ($errors as $error): ?>
                                                        <?php echo htmlspecialchars($error); ?>
                                                    <?php endforeach; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-md-6 mb-3">
                                            <label for="current_name" class="form-label">Current Name:</label>
                                            <input type="text" id="current_name" name="current_name"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($current_name); ?>" readonly>
                                        </div>

                                        <!-- New Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">New Name:</label>
                                            <input type="text" id="name" name="name" class="form-control" minlength="4"
                                                placeholder="Enter New Name (min 4 characters)">
                                        </div>

                                    </div>
                                    <!-- Submit Button -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update
                                            Name</button>
                                        <a href="site_settings.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>
                </div>

                <!--  -->

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