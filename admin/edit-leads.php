<?php
require './shared_components/session.php';
require './shared_components/error.php';
require 'shared_components/db.php';

$errors = [];
$insurance_for = $age = $pre_disease = $pre_disease_detail = $name = $phone_number = $email = "";
$insurance_for_options = ['self', 'husband', 'spouse', 'son', 'daughter', 'father', 'mother', 'family'];

// Get the ID of the lead to edit
$lead_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

if ($lead_id > 0) {
    // Fetch existing lead data
    $stmt = $pdo->prepare("SELECT * FROM lead_form WHERE id = ?");
    $stmt->execute([$lead_id]);
    $lead_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lead_data) {
        $insurance_for = $lead_data['insurance_for'];
        $age = $lead_data['age'];
        $pre_disease = $lead_data['pre_disease'];
        $pre_disease_detail = $lead_data['pre_disease_detail'];
        $name = $lead_data['name'];
        $phone_number = $lead_data['phone_number'];
        $email = $lead_data['email'];

        // Convert insurance_for string into an array for checkboxes
        $selected_insurance_for = explode(',', $insurance_for);
    } else {
        echo "<script>alert('Lead not found!'); window.location.href = 'leads.php';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate input values
    $insurance_for = isset($_POST['insurance_for']) ? implode(',', $_POST['insurance_for']) : null;
    $age = isset($_POST['age']) && is_numeric($_POST['age']) ? (int)$_POST['age'] : null;

    $pre_disease = isset($_POST['pre_disease']) ? $_POST['pre_disease'] : 'No';
    $pre_disease_detail = ($pre_disease === 'Yes' && !empty($_POST['pre_disease_detail'])) ? htmlspecialchars(trim($_POST['pre_disease_detail'])) : '';

    $name = !empty($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : null;
    $phone_number = !empty($_POST['phone_number']) ? preg_replace('/[^0-9]/', '', $_POST['phone_number']) : null;
    $email = !empty($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : null;

    // Validation
    if (empty($insurance_for)) {
        $errors['insurance_for'] = "Please select at least one option for 'Insurance For'.";
    }
    if (empty($age) || !is_numeric($age) || $age < 1 || $age > 100) {
        $errors['age'] = "Please enter a valid age between 1 and 100.";
    }
    if ($pre_disease === 'Yes' && empty($pre_disease_detail)) {
        $errors['pre_disease_detail'] = "Please mention the pre-existing disease.";
    }
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }
    if (empty($phone_number) || !preg_match("/^[0-9]{10}$/", $phone_number)) {
        $errors['phone_number'] = "Please enter a valid phone number (10 to 14 digits).";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    // var_dump($errors, $pre_disease, $pre_disease_detail);
    // exit();
    // Update lead data in the database if no errors
    if (empty($errors)) {

        try {
            $update_sql = "UPDATE lead_form SET 
                            insurance_for = :insurance_for, 
                            age = :age, 
                            pre_disease = :pre_disease, 
                            pre_disease_detail = :pre_disease_detail, 
                            name = :name, 
                            phone_number = :phone_number, 
                            email = :email 
                          WHERE id = :id";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([
                ':insurance_for' => $insurance_for,
                ':age' => $age,
                ':pre_disease' => $pre_disease,
                ':pre_disease_detail' => $pre_disease_detail,
                ':name' => $name,
                ':phone_number' => $phone_number,
                ':email' => $email,
                ':id' => $lead_id,
            ]);

            echo "<script>alert('Lead updated successfully!');
                  window.location.href = 'leads.php';</script>";
            exit();
        } catch (PDOException $e) {
            $errors['db'] = "Database error: " . $e->getMessage();
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
                                    Edit Leads
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            <a href="leads.php">Leads</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        <li>
                                            Edit Leads
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
                                <form action="" method="POST" class="custom-validation">
                                    <div class="row">
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name:</label>
                                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" minlength="2" required placeholder="Enter name (min 2 characters)">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="age" class="form-label">Age:</label>
                                            <input type="number" id="age" name="age" class="form-control" value="<?php echo htmlspecialchars($age); ?>" min="1" max="100" required placeholder="Enter age (1-100)">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required placeholder="Enter email">
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-md-6 mb-3">
                                            <label for="phone_number" class="form-label">Phone Number:</label>
                                            <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>" required placeholder="Enter phone number (10 digits)">
                                        </div>


                                    </div>
                                    <div class="row">


                                        <!-- Pre-Existing Disease -->
                                        <div class="col-md-6 mb-3">
                                            <label for="pre_disease" class="form-label">Pre-Existing Disease:</label>
                                            <select class="form-select" id="pre_disease" name="pre_disease" required>
                                                <option value="" <?= empty($pre_disease) ? 'selected' : '' ?>>Select...</option>
                                                <option value="Yes" <?= $pre_disease === 'Yes' ? 'selected' : '' ?>>Yes</option>
                                                <option value="No" <?= $pre_disease === 'No' ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </div>

                                        <!-- Pre-Existing Disease Detail -->
                                        <div class="col-md-6 mb-3">
                                            <label for="pre_disease_detail" class="form-label">Disease Detail:</label>
                                            <input type="text" id="pre_disease_detail" name="pre_disease_detail" class="form-control" value="<?php
                                                                                                                                                echo htmlspecialchars(empty($pre_disease_detail) ? "" : $pre_disease_detail);
                                                                                                                                                ?>" placeholder="Enter disease detail (if applicable)">
                                        </div>

                                    </div>


                                    <div class="row">

                                        <!-- Insurance For -->
                                        <div class="col-md-6 mb-3">
                                            <label for="insurance_for" class="form-label">Insurance For:</label>
                                            <div class="form-check">
                                                <?php foreach ($insurance_for_options as $option): ?>
                                                    <div>
                                                        <input type="checkbox"
                                                            class="form-check-input"
                                                            id="insurance_for_<?= $option ?>"
                                                            name="insurance_for[]"
                                                            value="<?= $option ?>"
                                                            <?= in_array($option, $selected_insurance_for) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="insurance_for_<?= $option ?>">
                                                            <?= ucfirst($option) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mb-3 text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                        <a href="leads.php" class="btn btn-secondary">Cancel</a>
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