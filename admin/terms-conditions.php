<?php
require './shared_components/session.php';
require './shared_components/error.php';

include 'shared_components/db.php';

// Fetch the current value from the table
$stmt = $pdo->prepare("SELECT * FROM terms_conditions WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$terms_conditions = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $terms_conditions_text = trim($_POST['terms_conditions']);

    // Validate input
    if (!empty($terms_conditions_text)) {
        $update_stmt = $pdo->prepare("UPDATE terms_conditions SET 
            terms_conditions = :terms_conditions 
            WHERE id = :id");
        $update_stmt->execute([
            'terms_conditions' => $terms_conditions_text,
            'id' => 1
        ]);
        // Redirect to prevent resubmission and reload
        echo "<script>alert('Terms and Conditions Updated Successfully!'); 
        window.location.href = 'terms-conditions.php';</script>";
        exit();
    } else {
        $error = "Terms and Conditions field is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <script src="ckeditor/ckeditor.js"></script>
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
                                    Terms and Conditions
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Terms and Conditions
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
                                    <div class="mb-3">
                                        <label class="form-label" for="terms_conditions">Terms and Conditions (required)
                                        </label>
                                        <textarea name="terms_conditions" id="terms_conditions" class="form-control"
                                            rows="10" minlength="10" maxlength="10000" required
                                            placeholder="Enter terms and conditions">
                            <?php echo htmlspecialchars($terms_conditions['terms_conditions']); ?>
                        </textarea>
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
    <script>
        CKEDITOR.replace('terms_conditions');
    </script>
</body>

</html>