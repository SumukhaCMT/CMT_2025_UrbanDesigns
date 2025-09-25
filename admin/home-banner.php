<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';


// Fetch the current value from the table
$stmt = $pdo->prepare("SELECT * FROM home_banner WHERE id = :id LIMIT 1");
$stmt->execute(['id' => 1]);
$home_banner = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if form is submitted to update the values
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $banner_prefix = trim($_POST['banner_prefix']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $button_1_title = trim($_POST['button_1_title']);
    $button_1_name = trim($_POST['button_1_name']);
    $button_2_title = trim($_POST['button_2_title']);
    $button_2_name = trim($_POST['button_2_name']);

    // Validate inputs
    if (!empty($title) && !empty($content) && !empty($button_1_title) && !empty($button_1_name) && !empty($button_2_title) && !empty($button_2_name)) {
        $update_stmt = $pdo->prepare("UPDATE home_banner SET 
            -- banner_prefix = :banner_prefix,
            title = :title, 
            content = :content,
            button_1_title = :button_1_title,
            button_1_name = :button_1_name,
            button_2_title = :button_2_title,
            button_2_name = :button_2_name
            WHERE id = :id");
        $update_stmt->execute([
            // 'banner_prefix' => $banner_prefix,
            'title' => $title,
            'content' => $content,
            'button_1_title' => $button_1_title,
            'button_1_name' => $button_1_name,
            'button_2_title' => $button_2_title,
            'button_2_name' => $button_2_name,
            'id' => 1
        ]);
        // Redirect to prevent resubmission and reload
        echo "<script>alert('Home Banner Updated Successfully!'); 
        window.location.href = 'home-banner.php';</script>";
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
                                    Home Page Banner
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Home Page Banner
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


                                        <!-- <div class="col-md-6 mb-3">
                                            <label class="form-label" for="title">Banner Prefix (required) </label>
                                            <input type="text" name="banner_prefix" id="banner_prefix" class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['banner_prefix']); ?>" minlength="2" maxlength="250" required
                                                placeholder="Enter Banner Prefix">
                                        </div> -->
                                        <div class="mb-3">
                                            <label class="form-label" for="title">Title (required) </label>
                                            <input type="text" name="title" id="title" class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['title']); ?>"
                                                minlength="2" maxlength="250" required placeholder="Enter title">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="content">Content (required) </label>
                                        <textarea name="content" id="content" class="form-control" rows="5"
                                            minlength="10" maxlength="1000" required
                                            placeholder="Enter content"><?php echo htmlspecialchars($home_banner['content']); ?></textarea>
                                    </div>

                                    <div class="row">


                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="button_1_title">Button 1 Title (required)
                                            </label>
                                            <input type="text" name="button_1_title" id="button_1_title"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['button_1_title']); ?>"
                                                minlength="2" maxlength="100" required
                                                placeholder="Enter Button 1 Title">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="button_1_name">Button 1 Name (required)
                                            </label>
                                            <input type="text" name="button_1_name" id="button_1_name"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['button_1_name']); ?>"
                                                minlength="2" maxlength="50" required placeholder="Enter Button 1 Name">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="button_2_title">Button 2 Title (required)
                                            </label>
                                            <input type="text" name="button_2_title" id="button_2_title"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['button_2_title']); ?>"
                                                minlength="2" maxlength="100" required
                                                placeholder="Enter Button 2 Title">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="button_2_name">Button 2 Name (required)
                                            </label>
                                            <input type="text" name="button_2_name" id="button_2_name"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($home_banner['button_2_name']); ?>"
                                                minlength="2" maxlength="50" required placeholder="Enter Button 2 Name">
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