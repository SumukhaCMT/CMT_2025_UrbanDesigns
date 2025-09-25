<?php
session_start();

require 'shared_components/db.php';
require './shared_components/error.php';
require 'components/pin-generator.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if the email exists in the admins table
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a 4-digit OTP
        $otp = generateOtp();

        // Set OTP expiration (5 minutes from now)
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_email_expires_at'] = time() + (5 * 60);

        // Check if email already exists in the password_reset table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM password_reset WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $email_exists = $stmt->fetchColumn();

        if ($email_exists) {
            // Update the OTP and expiration if the email exists
            $stmt = $pdo->prepare("UPDATE password_reset SET otp = :otp, expires_at = :expires_at WHERE email = :email");
            $stmt->execute(['email' => $email, 'otp' => $otp, 'expires_at' => $expires_at]);
        } else {
            // Insert a new record if the email does not exist
            $stmt = $pdo->prepare("INSERT INTO password_reset (email, otp, expires_at) VALUES (:email, :otp, :expires_at)");
            $stmt->execute(['email' => $email, 'otp' => $otp, 'expires_at' => $expires_at]);
        }

        try {
            $mail = new PHPMailer(true);

            // --- Server Settings ---
            $mail->SMTPDebug = 0; // Set to 0 for production use
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'connect@theurbandesign.in';  // Your full Gmail/Google Workspace address
            $mail->Password = 'TheUrbanDesign@123';           // Your Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // --- Recipients ---
            $mail->setFrom('connect@theurbandesign.in', 'The Urban Design');
            $mail->addAddress($email);

            // --- Content ---
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = "Your password reset OTP is: <b>$otp</b>. This code expires in 5 minutes. <br><br> If you did not initiate this request, please contact the Administrator immediately. <br><br> Regards,<br> The Urban Design";
            $mail->AltBody = "Your password reset OTP is: $otp. This code expires in 5 minutes. If you did not initiate this request, please contact the Administrator immediately.";

            $mail->send();

            // If email is sent successfully, redirect to the OTP validation page
            header("Location: otp-validation.php");
            exit();

        } catch (Exception $e) {
            // If sending fails, this block will execute
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
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
                                        <h5 class="text-primary">Reset Your Password !</h5>

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
                                        <img src="assets/images/favicons/logo_login.webp" alt="rna"
                                            class="rounded-circle" height="74">
                                    </span>
                                </div>


                            </div>
                            <div class="p-2">

                                <div class="alert alert-warning text-center mb-4" role="alert">
                                    Enter your Registed Email to Receive OTP
                                </div>
                                <?php
                                if (isset($error)) {
                                    echo "
                            <div class='alert alert-danger text-center' role='alert'>
                                $error
                            </div>";
                                }
                                ?>
                                <form class="form-horizontal" action="" method="POST">
                                    <div class="mb-3">
                                        <label for="useremail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="useremail" name="email"
                                            placeholder="Enter email" required>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-primary w-md waves-effect waves-light"
                                            type="submit">Receive OTP</button>
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