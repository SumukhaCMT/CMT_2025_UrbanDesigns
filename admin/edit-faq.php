<?php
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

$errors = [];
$question = $answer = "";
$faq_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM faq WHERE id = ?");
$stmt->execute([$faq_id]);
$faq_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$faq_data) {
    echo "<script>alert('FAQ not found!'); window.location.href = 'faq.php';</script>";
    exit();
}

// Prepopulate existing data
$question = $faq_data['question'];
$answer = $faq_data['answer'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $question = !empty($_POST['question']) && strlen($_POST['question']) >= 4 && strlen($_POST['question']) <= 100
        ? htmlspecialchars($_POST['question'])
        : $errors['question'] = "Question must be between 4 and 100 characters.";

    $answer = !empty($_POST['answer']) && strlen($_POST['answer']) >= 4 && strlen($_POST['answer']) <= 200
        ? htmlspecialchars($_POST['answer'])
        : $errors['answer'] = "Answer must be between 4 and 200 characters.";

    // Update database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE faq SET question = ?, answer = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$question, $answer, $faq_id]);

        echo "<script>alert('FAQ updated successfully!');
          window.location.href = 'faq.php';</script>";
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
                                    Site Settings
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Site Settings
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
                                        <!-- Question -->
                                        <div class="col-md-12 mb-3">
                                            <label for="question" class="form-label">Question:</label>
                                            <input type="text" id="question" name="question" class="form-control"
                                                value="<?php echo htmlspecialchars($question); ?>" minlength="4"
                                                maxlength="100" required
                                                placeholder="Enter question (4-100 characters)">
                                        </div>

                                        <!-- Answer -->
                                        <div class="col-md-12 mb-3">
                                            <label for="answer" class="form-label">Answer:</label>
                                            <input type="text" id="answer" name="answer" class="form-control"
                                                value="<?php echo htmlspecialchars($answer); ?>" minlength="4"
                                                maxlength="200" required placeholder="Enter answer (4-200 characters)">
                                        </div>
                                    </div>

                                    <!-- Submit and Cancel Buttons -->
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save
                                            Changes</button>
                                        <a href="faq.php" class="btn mx-3 btn-secondary">Cancel</a>
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
    <!-- validation -->
    <?php require './shared_components/scripts.php'; ?>
    <script src="assets/libs/parsleyjs/parsley.min.js"></script>
    <script src="assets/js/pages/form-validation.init.js"></script>
</body>

</html>