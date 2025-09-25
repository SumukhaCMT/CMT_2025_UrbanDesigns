<?php

session_start();
require 'shared_components/db.php';
require './shared_components/error.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve OTP from the form
    $digit1 = trim($_POST['digit1']);
    $digit2 = trim($_POST['digit2']);
    $digit3 = trim($_POST['digit3']);
    $digit4 = trim($_POST['digit4']);

    // Validate OTP digits to ensure they are numeric and exactly 1 character long
    if (!ctype_digit($digit1) || !ctype_digit($digit2) || !ctype_digit($digit3) || !ctype_digit($digit4)) {
        $error = "OTP must contain only numeric digits.";
    } else {
        $otp = $digit1 . $digit2 . $digit3 . $digit4;

        // Check if reset_email exists in session
        if (!isset($_SESSION['reset_email'])) {
            $error = "Session expired. Please request a new OTP.";
        } else {
            // Sanitize the email retrieved from the session
            $email = filter_var($_SESSION['reset_email'], FILTER_SANITIZE_EMAIL);

            // Fetch OTP details from the database
            $stmt = $pdo->prepare("SELECT * FROM password_reset WHERE email = :email AND otp = :otp");
            $stmt->execute(['email' => $email, 'otp' => $otp]);
            $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($otp_record) {
                // Check expiration using PHP
                $current_time = new DateTime();
                $expires_at = new DateTime($otp_record['expires_at']);

                if ($current_time <= $expires_at) {
                    // OTP verified successfully

                    // Generate a unique reset token
                    $reset_token = bin2hex(random_bytes(32)); // Generates a 64-character token
                    $reset_token_expiration = time() + (5 * 60); // Token valid for 5 minutes

                    // Update the reset token and expiration in the database
                    $update_stmt = $pdo->prepare("UPDATE password_reset SET reset_token = :reset_token, reset_token_expires_at = :reset_token_expires_at WHERE email = :email");
                    $update_stmt->execute([
                        'reset_token' => $reset_token,
                        'reset_token_expires_at' => date('Y-m-d H:i:s', $reset_token_expiration),
                        'email' => $email
                    ]);

                    // Regenerate the session ID for security
                    session_regenerate_id(true);

                    // Store email and reset token in session for the reset password process
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_email_expires_at'] = time() + (5 * 60);
                    $_SESSION['reset_token'] = $reset_token;
                    $_SESSION['reset_token_expires_at'] = $reset_token_expiration;

                    // Redirect to reset-password.php
                    header("Location: reset-password.php");
                    exit();
                } else {
                    $error = "OTP has expired. Please request a new one.";
                }
            } else {
                $error = "Invalid OTP. Please try again.";
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
                                        <h5 class="text-primary">Verify your email</h5>

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
                            <div class="p-2 mt-4">

                                <div class="alert alert-warning text-center mb-4" role="alert">
                                    Please enter the 4 digit code sent to Registed Email
                                </div>
                                <?php
                                if (isset($error)) {
                                    echo "
                            <div class='alert alert-danger text-center' role='alert'>
                                $error
                            </div>";
                                }
                                ?>
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="col-3">
                                            <div class="mb-3">
                                                <label for="digit1-input" class="visually-hidden">Digit 1</label>
                                                <input type="text"
                                                    class="form-control form-control-lg text-center two-step"
                                                    maxLength="1" id="digit1-input" name="digit1" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="mb-3">
                                                <label for="digit2-input" class="visually-hidden">Digit 2</label>
                                                <input type="text"
                                                    class="form-control form-control-lg text-center two-step"
                                                    maxLength="1" id="digit2-input" name="digit2" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="mb-3">
                                                <label for="digit3-input" class="visually-hidden">Digit 3</label>
                                                <input type="text"
                                                    class="form-control form-control-lg text-center two-step"
                                                    maxLength="1" id="digit3-input" name="digit3" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="mb-3">
                                                <label for="digit4-input" class="visually-hidden">Digit 4</label>
                                                <input type="text"
                                                    class="form-control form-control-lg text-center two-step"
                                                    maxLength="1" id="digit4-input" name="digit4" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-primary w-md waves-effect waves-light"
                                            type="submit">Verify</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">
                        <!-- <div class="mt-5 text-center">
                            <p>Didn't receive a OTP ? <a href="#" class="fw-medium text-primary"> Resend </a> </p>
                        </div> -->
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

    <!-- two-step-verification js -->
    <script src="assets/js/pages/two-step-verification.init.js"></script>
</body>

</html>