<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Include database connection
include 'shared_components/db.php';

// Fetch SEO record ID
$seo_id = $_GET['id'] ?? null;
if (!$seo_id) {
    echo "<script>alert('Invalid SEO ID!'); window.location.href = 'view_seo.php';</script>";
    exit();
}

// Fetch SEO data based on ID
$stmt = $pdo->prepare("SELECT * FROM seo WHERE id = ?");
$stmt->execute([$seo_id]);
$seo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seo) {
    echo "<script>alert('SEO record not found!'); window.location.href = 'view_seo.php';</script>";
    exit();
}

// Pre-fill form fields with existing data
$page_name = $seo['page_name'];
$meta_title = $seo['meta_title'];
$meta_keywords = $seo['meta_keywords'];
$meta_description = $seo['meta_description'];
$meta_canonical_link = $seo['meta_canonical_link'];

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $page_name = htmlspecialchars(trim($_POST['page_name']));
    $meta_title = htmlspecialchars(trim($_POST['meta_title']));
    $meta_keywords = htmlspecialchars(trim($_POST['meta_keywords']));
    $meta_description = htmlspecialchars(trim($_POST['meta_description']));
    $meta_canonical_link = htmlspecialchars(trim($_POST['meta_canonical_link']));

    // Update SEO record in the database
    $stmt = $pdo->prepare("
        UPDATE seo 
        SET page_name = ?, 
            meta_title = ?, 
            meta_keywords = ?, 
            meta_description = ?, 
            meta_canonical_link = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([
        $page_name,
        $meta_title,
        $meta_keywords,
        $meta_description,
        $meta_canonical_link,
        $seo_id
    ]);

    echo "<script>alert('SEO record updated successfully!'); window.location.href = 'seo.php';</script>";
    exit();
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
                                    Edit SEO
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>

                                            <a href="seo.php">SEO</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            Edit SEO
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
                                <h4 class="card-title">Update SEO</h4>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="page_name" class="form-label">Page Name</label>
                                        <input type="text" class="form-control" id="page_name" name="page_name"
                                            value="<?php echo htmlspecialchars($page_name); ?>" maxlength="200"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                                            value="<?php echo htmlspecialchars($meta_title); ?>" maxlength="200"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <textarea class="form-control" id="meta_keywords" name="meta_keywords" rows="3"
                                            required><?php echo htmlspecialchars($meta_keywords); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description"
                                            rows="5"
                                            required><?php echo htmlspecialchars($meta_description); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_canonical_link" class="form-label">Canonical Link</label>
                                        <input type="text" class="form-control" id="meta_canonical_link"
                                            name="meta_canonical_link"
                                            value="<?php echo htmlspecialchars($meta_canonical_link); ?>"
                                            maxlength="100">
                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit" class="btn btn-primary">Update SEO</button>
                                        <a href="view_seo.php" class="btn mx-3 btn-secondary">Cancel</a>
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