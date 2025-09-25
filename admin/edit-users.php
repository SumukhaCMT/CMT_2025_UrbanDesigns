<?php
session_start();
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$name = $email = $phone_number = $dob = $gender = "";
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch user data based on ID
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "<script>alert('User not found!');
          window.location.href = 'users.php';</script>";
    exit();
}

// Prepopulate user data
$name = $user_data['name'];
$email = $user_data['email'];
$phone_number = $user_data['phone_number'];
$dob = $user_data['dob'];
$gender = $user_data['gender'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    $name = !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : $errors['name'] = "Name is required.";

    // Validate email
    $email = !empty($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : $errors['email'] = "Invalid email." : $errors['email'] = "Email is required.";

    // Validate phone number
    $phone_number = !empty($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : null;

    // Validate date of birth
    $dob = !empty($_POST['dob']) ? htmlspecialchars($_POST['dob']) : null;

    // Validate gender
    $gender = isset($_POST['gender']) && $_POST['gender'] !== '' ? htmlspecialchars($_POST['gender']) : null;

    // Check if email is unique
    if (empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing_user) {
            $errors['email'] = "The email is already in use. Please choose another one.";
        }
    }

    // Update user data if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_number = ?, dob = ?, gender = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone_number, $dob, $gender, $user_id]);

        echo "<script>alert('User updated successfully!');
              window.location.href = 'users.php';</script>";
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
                                    Edit User
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="users.php">Users</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        <li>
                                            Edit User
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
                                    <div class="row">


                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name:</label>
                                            <input type="text" id="name" name="name" class="form-control"
                                                value="<?php echo htmlspecialchars($name); ?>" minlength="2" required
                                                placeholder="Enter name (min 2 characters)" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo htmlspecialchars($email); ?>" required
                                                placeholder="Enter email">
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-md-6 mb-3">
                                            <label for="phone_number" class="form-label">Phone Number:</label>
                                            <input type="number" id="phone_number" name="phone_number"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($phone_number); ?>"
                                                placeholder="Enter phone number">
                                        </div>

                                        <!-- Date of Birth -->
                                        <div class="col-md-6 mb-3">
                                            <label for="dob" class="form-label">Date of Birth:</label>
                                            <input type="date" id="dob" name="dob" class="form-control"
                                                value="<?php echo htmlspecialchars($dob); ?>">
                                        </div>

                                        <!-- Gender -->
                                        <div class="col-md-6 mb-3">
                                            <label for="gender" class="form-label">Gender:</label>
                                            <select class="form-control" id="gender" name="gender">
                                                <option value="" <?php echo is_null($gender) ? 'selected' : ''; ?>>
                                                    Select One</option>
                                                <option value="Male" <?php echo $gender == 'Male' ? 'selected' : ''; ?>>
                                                    Male</option>
                                                <option value="Female" <?php echo $gender == 'Female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo $gender == 'Other' ? 'selected' : ''; ?>>
                                                    Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="users.php" class="btn mx-3 btn-secondary">Cancel</a>
                                    </div>
                                </form>

                                <!-- Display Validation Errors -->
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger mt-3">
                                        <ul>
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
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