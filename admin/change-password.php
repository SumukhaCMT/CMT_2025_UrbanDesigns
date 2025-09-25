<?php
session_start();
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$current_password = $new_password = $confirm_password = "";

// Get the user email from session
$user_email = $_SESSION['email'];

// Fetch existing user data based on email (use the session email to find the user)
$stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->execute([$user_email]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate current password input
    $current_password = !empty($_POST['current_password']) ? $_POST['current_password'] : $errors['current_password'] = "Current password is required.";

    // Validate new password input
    $new_password = !empty($_POST['new_password']) ? $_POST['new_password'] : $errors['new_password'] = "New password is required.";

    // Validate confirm new password input
    $confirm_password = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : $errors['confirm_password'] = "Please confirm your new password.";

    // Check if the new password matches the confirmation
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "New password and confirmation do not match.";
    }

    // Check if the current password matches the password in the database
    if (empty($errors)) {
        if (password_verify($current_password, $user_data['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $user_email]);

            echo "<script>alert('Password updated successfully!');
                  window.location.href = 'site_settings.php';</script>";
            exit();
        } else {
            $errors['current_password'] = "The current password you entered is incorrect.";
        }
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
                                    Change Password
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Change Password
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
                                <form action="change-password.php" method="POST" class="custom-validation">
                                    <!-- Current Password -->
                                    <div class="row">

                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Current Password:</label>
                                            <input type="password" id="current_password" name="current_password"
                                                class="form-control" value="" required
                                                placeholder="Enter current password">
                                        </div>

                                        <!-- New Password -->
                                        <div class="col-md-4 mb-3">
                                            <label for="new_password" class="form-label">New Password:</label>
                                            <input type="password" id="new_password" name="new_password"
                                                class="form-control" value="" required placeholder="Enter new password">
                                        </div>

                                        <!-- Confirm New Password -->
                                        <div class="col-md-4 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New
                                                Password:</label>
                                            <input type="password" id="confirm_password" name="confirm_password"
                                                class="form-control" value="" required
                                                placeholder="Confirm new password">
                                        </div>
                                    </div>

                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update
                                            Password</button>
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