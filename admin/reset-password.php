<?php
session_start();
require 'shared_components/db.php';
require './shared_components/error.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the session has a valid reset email and token
    if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_token'])) {
        $error = "Invalid or expired request. Please request a new password reset.";
    } else {
        $email = $_SESSION['reset_email'];
        $reset_token = $_SESSION['reset_token'];


        // First check the expiration of the reset token
        if (time() > $_SESSION['reset_token_expires_at']) {
            $error = "Reset token has expired. Please request a new password reset.";
            // Optionally clear expired session
            unset($_SESSION['reset_email'], $_SESSION['reset_token'], $_SESSION['reset_token_expires_at']);
        } else {
            // Check if the token matches the one in the database
            $stmt = $pdo->prepare("SELECT * FROM password_reset WHERE email = :email AND reset_token = :reset_token");
            $stmt->execute(['email' => $email, 'reset_token' => $reset_token]);
            $reset_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reset_record) {
                // Validate the passwords
                $password = trim($_POST['password']);
                $confirm_password = trim($_POST['confirm_password']);

                if ($password === $confirm_password) {
                    // Hash the password before saving
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Update the password in the admins table
                    $update_stmt = $pdo->prepare("UPDATE admins SET password = :password WHERE email = :email");
                    $update_stmt->execute(['password' => $hashed_password, 'email' => $email]);

                    // Clear the reset session
                    unset($_SESSION['reset_email'], $_SESSION['reset_token'], $_SESSION['reset_token_expires_at']);

                    // Optionally clear the reset token from the password_reset table (if you want to invalidate the reset request)
                    $update_reset_token_stmt = $pdo->prepare("UPDATE password_reset SET reset_token = NULL WHERE email = :email");
                    $update_reset_token_stmt->execute(['email' => $email]);

                    // Redirect to success page or login page
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Passwords do not match. Please try again.";
                }
            } else {
                $error = "Invalid reset token. Please request a new password reset.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'shared_components/head.php'; ?>
</head>

<body>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Reset Password</h5>
                                        <!-- <p>Sign in to continue.......</p> -->
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div class="auth-logo">

                                <div class="avatar-md profile-user-wid mb-4">
                                    <span class="avatar-title rounded-circle bg-light">
                                        <img src="../assets/images/favicons/Royal.png" alt="rna" class="rounded-circle"
                                            height="74">
                                    </span>
                                </div>


                            </div>

                            <div class="p-2">
                                <div class="alert alert-info text-center mb-4" role="alert">
                                    Please Set the New Password
                                </div>
                                <?php
                                if (isset($error)) {
                                    echo "<div class='alert alert-danger text-center' role='alert'>$error</div>";
                                }
                                ?>
                                <form class="form-horizontal" action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Enter password" aria-label="Password"
                                                aria-describedby="password-addon">
                                            <button class="btn btn-light" type="button" id="password-addon"><i
                                                    class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="confirm_password" class="form-control"
                                                placeholder="Confirm password" aria-label="Confirm Password"
                                                aria-describedby="password-addon">
                                            <button class="btn btn-light" type="button" id="password-addon"><i
                                                    class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit"
                                            name="reset">Reset Password</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">

                        <div>
                            <p>Remember your password?<a href="login.php" class="fw-medium text-primary"> Login In
                                    here</a> </p>
                            <p>&copy; <?php echo date('Y') ?> | The Urban Design | All Rights Reserved</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php require 'shared_components/scripts.php'; ?>


</body>

</html>