<?php
require './shared_components/session.php';
require './shared_components/error.php';

include 'shared_components/db.php';

// Fetch the current value from the table
$stmt = $pdo->prepare("SELECT * FROM about_our_company WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$about_our_company = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate inputs
    if (!empty($title)) {
        $update_stmt = $pdo->prepare("UPDATE about_our_company SET 
            title = :title, 
            content = :content 
            WHERE id = :id");
        $update_stmt->execute([
            'title' => $title,
            'content' => !empty($content) ? $content : null,
            'id' => 1
        ]);
        // Redirect to prevent resubmission and reload
        echo "<script>alert('About Our Company Updated Successfully!'); 
        window.location.href = 'about-company.php';</script>";
        exit();
    } else {
        $error = "Title is required.";
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
                                    About Our Company
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            About Our Company
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
                                        <label class="form-label" for="title">Title (required) </label>
                                        <input type="text" name="title" id="title" class="form-control"
                                            value="<?php echo htmlspecialchars($about_our_company['title']); ?>"
                                            minlength="2" maxlength="250" required placeholder="Enter title">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="content">Content (required)</label>
                                        <textarea name="content" id="content" class="form-control" rows="5"
                                            maxlength="2000" required
                                            placeholder="Enter content (Optional)"><?php echo htmlspecialchars($about_our_company['content']); ?></textarea>
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