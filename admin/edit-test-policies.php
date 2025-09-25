<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';


require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
// Load categories from Excel (Sheet 1)
$filePath = '../assets/files/rna.xlsx';
if (file_exists($filePath)) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

    for ($row = 1; $row <= $highestRow; $row++) {
        $main_category = $sheet->getCell("A$row")->getValue();
        $sub_categories = [];
        for ($col = 2; $col <= $highestColumnIndex; $col++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $value = $sheet->getCell("$columnLetter$row")->getValue();
            if (!empty($value)) {
                $sub_categories[] = $value;
            }
        }
        if (!empty($main_category)) {
            $categories[$main_category] = $sub_categories;
        }
    }
}

$errors = [];
$insurance_for = $main_category = $sub_category = $sum_insured = $annual_premium = $age = $phone_number = $email = "";
$policy_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch policy enquiry data based on ID
$stmt = $pdo->prepare("SELECT * FROM policy_enquiry WHERE id = ?");
$stmt->execute([$policy_id]);
$policy_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$policy_data) {
    echo "<script>alert('Policy enquiry not found!');
          window.location.href = 'policy-enquiries.php';</script>";
    exit();
}


$insurance_for = $policy_data['insurance_for'];
$main_category = $policy_data['main_category'];
$sub_category = $policy_data['sub_category'];
$sum_insured = $policy_data['sum_insured'];
$annual_premium = $policy_data['annual_premium'];
$age = $policy_data['age'];
$email = $policy_data['email'];
$phone_number = $policy_data['phone_number'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // var_dump($errors);
    // exit();
    // Validate insurance_for
    $insurance_for = !empty($_POST['insurance_for']) ? htmlspecialchars($_POST['insurance_for']) : $errors['insurance_for'] = "Insurance For is required.";

    // Validate phone number
    $phone_number = !empty($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : $errors['phone_number'] = "Phone number is required.";
    if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $errors['phone_number'] = "Invalid phone number format. It should be 10 digits only.";
    }


    // Validate main_category
    $main_category = !empty($_POST['main_category']) ? htmlspecialchars($_POST['main_category']) : $errors['main_category'] = "Main Category is required.";

    // Validate sub_category
    $sub_category = !empty($_POST['sub_category']) ? htmlspecialchars($_POST['sub_category']) : $errors['sub_category'] = "Sub Category is required.";

    // Validate sum_insured
    $sum_insured = !empty($_POST['sum_insured']) ? htmlspecialchars($_POST['sum_insured']) : $errors['sum_insured'] = "Sum Insured is required.";
    if (!is_numeric($sum_insured) || $sum_insured <= 0) {
        $errors['sum_insured'] = "Invalid Sum Insured.";
    }

    // Validate annual_premium
    $annual_premium = !empty($_POST['annual_premium']) ? htmlspecialchars($_POST['annual_premium']) : $errors['annual_premium'] = "Annual Premium is required.";
    if (!is_numeric($annual_premium) || $annual_premium <= 0) {
        $errors['annual_premium'] = "Invalid Annual Premium.";
    }

    // Validate age
    $age = !empty($_POST['age']) ? htmlspecialchars($_POST['age']) : $errors['age'] = "Age is required.";
    if (!is_numeric($age) || $age <= 0 || $age > 100) {
        $errors['age'] = "Invalid Age.";
    }

    // Validate email
    $email = !empty($_POST['email']) ? (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : $errors['email'] = "Invalid email.") : $errors['email'] = "Email is required.";


    // Update policy enquiry data if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE policy_enquiry SET insurance_for = ?, main_category = ?, sub_category = ?, sum_insured = ?, annual_premium = ?, age = ?, phone_number=?, email=? WHERE id = ?");
        $stmt->execute([$insurance_for, $main_category, $sub_category, $sum_insured, $annual_premium, $age, $phone_number, $email, $policy_id]);

        echo "<script>alert('Policy enquiry updated successfully!');
              window.location.href = 'test-policies.php';</script>";
        exit();
    }
    ;
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
                                    Edit Test Policies
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>

                                            <a href="test-policies.php">Test Policies</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Edit Test Policies
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
                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email:</label>
                                            <input type="email" id="email" name="email" class="form-control"
                                                value="<?php echo htmlspecialchars($email); ?>" required
                                                placeholder="Enter email" required>
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-md-6 mb-3">
                                            <label for="phone_number" class="form-label">Phone Number:</label>
                                            <input type="number" id="phone_number" name="phone_number"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($phone_number); ?>" required
                                                placeholder="Enter phone number (10 digits)">
                                        </div>

                                    </div>
                                    <div class="row">
                                        <!-- Insurance For -->
                                        <div class="col-md-6 mb-3">
                                            <label for="insurance_for" class="form-label">Insurance For:</label>
                                            <select id="insurance_for" name="insurance_for" class="form-control"
                                                required>
                                                <option value="" disabled selected>Select Insurance For</option>
                                                <option value="1adult" <?php echo ($insurance_for == "1adult") ? "selected" : ""; ?>>One Adult</option>
                                                <option value="2adults" <?php echo ($insurance_for == "2adults") ? "selected" : ""; ?>>Two Adults</option>
                                                <option value="2adult-2children" <?php echo ($insurance_for == "2adult-2children") ? "selected" : ""; ?>>Two
                                                    Adults Two Children</option>
                                                <option value="1adult-1child" <?php echo ($insurance_for == "1adult-1child") ? "selected" : ""; ?>>One Adult
                                                    One Child</option>
                                                <option value="2adult-1child" <?php echo ($insurance_for == "2adult-1child") ? "selected" : ""; ?>>Two Adults
                                                    One Child</option>
                                                <option value="1adult-2children" <?php echo ($insurance_for == "1adult-2children") ? "selected" : ""; ?>>One Adult
                                                    Two Children</option>
                                            </select>

                                        </div>

                                        <!-- Age -->
                                        <div class="col-md-6 mb-3">
                                            <label for="age" class="form-label">Age:</label>
                                            <input type="number" id="age" name="age" class="form-control"
                                                value="<?php echo htmlspecialchars($age); ?>" required
                                                placeholder="Enter age">
                                        </div>

                                        <!-- Main Category -->
                                        <div class="col-md-6 mb-3">
                                            <label for="main_category" class="form-label">Main Category:</label>
                                            <select id="main_category" name="main_category"
                                                class="form-control input-style" onchange="updateSub_categories()"
                                                required>
                                                <option value="<?php echo htmlspecialchars($main_category); ?>">
                                                    <?php echo htmlspecialchars($main_category); ?>
                                                </option>
                                                <?php foreach ($categories as $main => $subs): ?>
                                                    <option value="<?= htmlspecialchars($main) ?>">
                                                        <?= htmlspecialchars($main) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Sub Category -->
                                        <div class="col-md-6 mb-3">
                                            <label for="sub_category" class="form-label">Sub Category:</label>
                                            <select id="sub_category" name="sub_category"
                                                class="form-control input-style" required>
                                                <option value="<?php echo htmlspecialchars($sub_category); ?>">
                                                    <?php echo htmlspecialchars($sub_category); ?>
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Sum Insured -->
                                        <div class="col-md-6 mb-3">
                                            <label for="sum_insured" class="form-label">Sum Insured:</label>
                                            <input type="number" id="sum_insured" name="sum_insured"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($sum_insured); ?>" required
                                                placeholder="Enter sum insured">
                                        </div>

                                        <!-- Annual Premium -->
                                        <div class="col-md-6 mb-3">
                                            <label for="annual_premium" class="form-label">Annual Premium:</label>
                                            <input type="number" id="annual_premium" name="annual_premium"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($annual_premium); ?>" required
                                                placeholder="Enter annual premium">
                                        </div>




                                    </div>
                                    <!-- Submit Button -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                        <a href="test-policies.php" class="btn btn-secondary">Cancel</a>
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

    <script>
        // Handle category updates
        const categories = <?= json_encode($categories) ?>;

        // Make sure the function is defined and accessible
        function updateSub_categories() {
            const main_category = document.getElementById("main_category").value;
            const sub_categorySelect = document.getElementById("sub_category");

            // Clear previous options
            sub_categorySelect.innerHTML = '<option value="">Select a subcategory</option>';

            // Add new subcategories based on main category
            if (main_category && categories[main_category]) {
                categories[main_category].forEach(subcategory => {
                    const option = document.createElement("option");
                    option.value = subcategory;
                    option.textContent = subcategory;
                    sub_categorySelect.appendChild(option);
                });
            }
        }

        // Wait until the DOM is fully loaded
        document.addEventListener("DOMContentLoaded", function () {
            // Fetch all forms with the 'needs-validation' class
            const forms = document.querySelectorAll('.needs-validation');

            // Loop through the forms and apply validation
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                    styles
                }, false);
            });
        });
    </script>
</body>

</html>