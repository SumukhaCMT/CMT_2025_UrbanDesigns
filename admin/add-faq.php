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
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    // Validation
    if (empty($question)) {
        $errors[] = "Question is required.";
    }
    if (empty($answer)) {
        $errors[] = "Answer is required.";
    }

    // If no errors, insert data into the database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO faq (question, answer) VALUES (?, ?)");
            $stmt->execute([$question, $answer]);
            $success = true;
            echo "<script>alert('FAQ added successfully!'); 
                  window.location.href = 'faq.php';</script>";
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
                                        FAQ added successfully!
                                    </div>
                                <?php endif; ?>

                                <!-- Form -->
                                <form method="post" action="" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="question" class="form-label">Question:</label>
                                            <input type="text" class="form-control" id="question" name="question"
                                                minlength="4" maxlength="100" required placeholder="Enter question"
                                                value="<?php echo htmlspecialchars($question ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="answer" class="form-label">Answer:</label>
                                            <textarea class="form-control" id="answer" name="answer" minlength="4"
                                                maxlength="200" required
                                                placeholder="Enter answer"><?php echo htmlspecialchars($answer ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Submit</button>
                                        <a href="faq.php" class="btn btn-secondary waves-effect mx-2">Cancel</a>
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