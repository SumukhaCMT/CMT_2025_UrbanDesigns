<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Fetch the current values from the table
$stmt = $pdo->prepare("SELECT * FROM about_count WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$about_count = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_1 = trim($_POST['title_1']);
    $count_1 = intval($_POST['count_1']);
    $title_2 = trim($_POST['title_2']);
    $count_2 = intval($_POST['count_2']);
    $title_3 = trim($_POST['title_3']);
    $count_3 = intval($_POST['count_3']);
    $title_4 = trim($_POST['title_4']);
    $count_4 = intval($_POST['count_4']);

    // Validate inputs
    if (
        !empty($title_1) && !empty($title_2) && !empty($title_3) && !empty($title_4) &&
        $count_1 > 0 && $count_2 > 0 && $count_3 > 0 && $count_4 > 0
    ) {
        $update_stmt = $pdo->prepare(
            "UPDATE about_count SET 
            title_1 = :title_1, count_1 = :count_1,
            title_2 = :title_2, count_2 = :count_2,
            title_3 = :title_3, count_3 = :count_3,
            title_4 = :title_4, count_4 = :count_4
            WHERE id = :id"
        );
        $update_stmt->execute([
            'title_1' => $title_1,
            'count_1' => $count_1,
            'title_2' => $title_2,
            'count_2' => $count_2,
            'title_3' => $title_3,
            'count_3' => $count_3,
            'title_4' => $title_4,
            'count_4' => $count_4,
            'id' => 1
        ]);
        echo "<script>alert('About Count Updated Successfully!'); 
        window.location.href = 'about-count.php';</script>";
        exit();
    } else {
        $error = "All fields are required, and counts must be positive numbers.";
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
                                    About Count
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            About Count
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
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="title_1">Title 1 (required) </label>
                                                <input type="text" name="title_1" id="title_1" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['title_1']); ?>"
                                                    minlength="2" maxlength="20" required placeholder="Title 1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="count_1">Count 1 (required)</label>
                                                <input type="number" name="count_1" id="count_1" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['count_1']); ?>"
                                                    min="1" required placeholder="Count 1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="title_2">Title 2 (required) </label>
                                                <input type="text" name="title_2" id="title_2" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['title_2']); ?>"
                                                    minlength="2" maxlength="20" required placeholder="Title 2">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="count_2">Count 2 (required)</label>
                                                <input type="number" name="count_2" id="count_2" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['count_2']); ?>"
                                                    min="1" required placeholder="Count 2">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="title_3">Title 3 (required) </label>
                                                <input type="text" name="title_3" id="title_3" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['title_3']); ?>"
                                                    minlength="2" maxlength="20" required placeholder="Title 3">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="count_3">Count 3 (required)</label>
                                                <input type="number" name="count_3" id="count_3" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['count_3']); ?>"
                                                    min="1" required placeholder="Count 3">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="title_4">Title 4 (required) </label>
                                                <input type="text" name="title_4" id="title_4" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['title_4']); ?>"
                                                    minlength="2" maxlength="20" required placeholder="Title 4">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="count_4">Count 4 (required)</label>
                                                <input type="number" name="count_4" id="count_4" class="form-control"
                                                    value="<?php echo htmlspecialchars($about_count['count_4']); ?>"
                                                    min="1" required placeholder="Count 4">
                                            </div>
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