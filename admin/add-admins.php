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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (empty($role)) {
        $errors[] = "Role is required.";
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $email_exists = $stmt->fetchColumn();

            if ($email_exists > 0) {
                $errors[] = "Email is already in use.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }

    // If no errors, insert data into the database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role]);
            $success = true;
            echo "<script>alert('Admin added successfully!'); 
                  window.location.href = 'admins.php';</script>";
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
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
                                    Add Admin
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>

                                            <a href="admins.php">Admins</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Add Admin
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
                                        <p>
                                            <?php foreach ($errors as $error): ?>
                                                <?php echo htmlspecialchars($error); ?>
                                            <?php endforeach; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- Success Message -->
                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        Admin added successfully!
                                    </div>
                                <?php endif; ?>

                                <!-- Form -->
                                <form method="post" action="" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name:</label>
                                            <input type="text" class="form-control" id="name" name="name" required
                                                placeholder="Enter name"
                                                value="<?php echo htmlspecialchars($name ?? ''); ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" class="form-control" id="email" name="email" required
                                                placeholder="Enter email"
                                                value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="row">


                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password:</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                required placeholder="Enter password">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password:</label>
                                            <input type="password" class="form-control" id="confirm_password"
                                                name="confirm_password" required placeholder="Confirm password">
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Role:</label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="" disabled selected>Select Role</option>
                                                <option value="super-admin" <?php echo isset($role) && $role == 'super-admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                <option value="admin" <?php echo isset($role) && $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>


                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Submit</button>
                                        <a href="admins.php" class="btn btn-secondary waves-effect mx-2">Cancel</a>
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