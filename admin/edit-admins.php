<?php
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$name = $email = $role = "";
$admin_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin_data) {
    echo "<script>alert('Admin not found!'); window.location.href = 'admins.php';</script>";
    exit();
}

// Prepopulate existing data
$name = $admin_data['name'];
$email = $admin_data['email'];
$role = $admin_data['role'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $name = !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : $errors['name'] = "Name is required.";
    $email = !empty($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? htmlspecialchars($_POST['email']) : $errors['email'] = "Invalid email format." : $errors['email'] = "Email is required.";
    $role = isset($_POST['role']) && in_array($_POST['role'], ['super-admin', 'admin']) ? $_POST['role'] : $errors['role'] = "Role is required.";

    // Check for existing name and email in the database
    if (empty($errors)) {
        // Check if name already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE name = ? AND id != ?");
        $stmt->execute([$name, $admin_id]);
        if ($stmt->fetch()) {
            $errors['name'] = "Name already exists, please choose a different name.";
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->fetch()) {
            $errors['email'] = "Email already exists, please choose a different email.";
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, role = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $email, $role, $admin_id]);

        echo "<script>alert('Admin updated successfully!');
          window.location.href = 'admins.php';</script>";
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
                                    Edit Admin
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
                                            Edit Admin
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
                                <form action="" method="POST" class="custom-validation">
                                    <div class="row">


                                        <!-- Name -->
                                        <div class="col-md-4 mb-3">
                                            <label for="name" class="form-label">Name:</label>
                                            <input type="text" id="name" name="name" class="form-control"
                                                value="<?php echo htmlspecialchars($name); ?>" minlength="2" required
                                                placeholder="Enter name (min 2 characters)">
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-4 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo htmlspecialchars($email); ?>" required
                                                placeholder="Enter email">
                                        </div>

                                        <!-- Role -->
                                        <div class="col-md-4 mb-3">
                                            <label for="role" class="form-label">Role:</label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="super-admin" <?php echo isset($role) && $role == 'super-admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                <option value="admin" <?php echo isset($role) && $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="admins.php" class="btn mx-3 btn-secondary">Cancel</a>
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