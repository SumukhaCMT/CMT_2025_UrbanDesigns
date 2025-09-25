<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'shared_components/db.php';
session_start();

// If session exists, redirect to the admin dashboard
if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging output

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Store user role and ID in session
        $_SESSION['admin'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['admin_name'] = $user['name'];
        header("Location: index.php");
        exit();
    } else {
        if (!$user) {
            $error = "No user found with that email.";
        } else {
            $error = "Invalid email or password.";
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
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p>Log in to continue.......</p>
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
                                        <img src="assets/images/favicons/logo_login.webp" alt="Urban Design"
                                            class="rounded-circle" height="74">
                                    </span>
                                </div>


                            </div>
                            <div class="p-2">


                                <?php
                                if (isset($error)) {
                                    echo "
                            <p class='alert alert-danger text-center mb-4' role='alert'>
                                $error
                            </p>";
                                } else {
                                    echo "<div class='alert alert-success text-center mb-4' role='alert'>
                                    Please Enter Email and Password
                                </div>";
                                }
                                ?>
                                <form class="form-horizontal" action="" method="POST">

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="username"
                                            placeholder="Enter your registed email" name="username">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Enter password" aria-label="Password"
                                                aria-describedby="password-addon">
                                            <button class="btn btn-light " type="button" id="password-addon"><i
                                                    class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>



                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit"
                                            name="login">Log In</button>
                                    </div>



                                    <div class="mt-4 text-center">
                                        <a href="forgot-password.php" class="text-muted"><i
                                                class="mdi mdi-lock me-1"></i> Forgot your password?</a>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="mt-5 text-center">

                        <div>

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