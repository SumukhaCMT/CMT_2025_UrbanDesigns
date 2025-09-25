<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Fetch the current value from the table
$stmt = $pdo->prepare("SELECT * FROM trusted_by WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$trusted_by = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_heading = trim($_POST['section_heading']);
    $first_part = trim($_POST['first_part']);
    $number = !empty(intval($_POST['number'])) ? intval($_POST['number']) : null;
    ;
    $second_part = trim($_POST['second_part']);

    // Validate inputs
    if (!empty($first_part) && !empty($second_part) && !empty($section_heading)) {
        $update_stmt = $pdo->prepare("UPDATE trusted_by SET section_heading=:section_heading, first_part = :first_part, number = :number, second_part = :second_part WHERE id = :id");
        $update_stmt->execute([
            'section_heading' => $section_heading,
            'first_part' => $first_part,
            'number' => $number,
            'second_part' => $second_part,
            'id' => 1
        ]);
        // Redirect to prevent resubmission and reload
        // exit();
        echo "<script>alert('Trusted By Updated Successfully!'); 
        window.location.href = 'trusted-by.php';</script>";
        exit();
    } else {
        $error = "All fields are required.";
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
                                    Trusted By
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Trusted By
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

                                <form method="post" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label" for="section_heading">Section Heading
                                                (required)</label>
                                            <input type="text" name="section_heading" id="section_heading"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($trusted_by['section_heading']); ?>"
                                                minlength="1" maxlength="50" required placeholder="Minimum 1 character">
                                        </div>


                                        <div class="col-md-5 mb-3">
                                            <label class="form-label" for="first_part">First Part (required)</label>
                                            <input type="text" name="first_part" id="first_part" class="form-control"
                                                value="<?php echo htmlspecialchars($trusted_by['first_part']); ?>"
                                                minlength="2" maxlength="50" required
                                                placeholder="Minimum 2 characters">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label" for="number">Number (optional)</label>
                                            <input type="number" name="number" id="number" class="form-control"
                                                value="<?php echo !empty($trusted_by['number']) ? htmlspecialchars($trusted_by['number']) : ''; ?>"
                                                min="1" placeholder="Minimum value is 1">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label" for="second_part">Second Part (required)</label>
                                            <input type="text" name="second_part" id="second_part" class="form-control"
                                                value="<?php echo htmlspecialchars($trusted_by['second_part']); ?>"
                                                minlength="2" maxlength="100" required
                                                placeholder="Minimum 2 characters">
                                        </div>
                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Update</button>

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