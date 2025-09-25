<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Handle form submission for updating features title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && isset($_POST['section_heading'])) {
    $title = trim($_POST['title']);
    $section_heading = trim($_POST['section_heading']);

    // Validate and update title
    if (!empty($title) && strlen($title) >= 2 && strlen($title) <= 50 && !empty($section_heading) && strlen($section_heading) >= 1 && strlen($section_heading) <= 50) {
        try {
            $update_stmt = $pdo->prepare("UPDATE blog_title SET title = :title, section_heading=:section_heading WHERE id = :id");
            $update_stmt->execute([
                ':title' => $title,
                ':section_heading' => $section_heading,
                ':id' => 1
            ]);

            // Redirect to prevent form resubmission
            echo "<script>alert('Section Heading, Title Updated Successfully!'); 
                window.location.href = 'view_blog.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid title or heading! Title must be between 2 and 50 characters.');</script>";
    }
}

// Fetch all blogs
$stmt = $pdo->prepare("SELECT * FROM blog ORDER BY created_at DESC");
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    // Fetch blog to get the image name
    $stmt = $pdo->prepare("SELECT image_name FROM blog WHERE id = ?");
    $stmt->execute([$delete_id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($blog && $blog['image_name']) {
        $image_path = "../assets/images/blog/" . $blog['image_name'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image from the server
        }
    }

    // Delete the blog from the database
    $stmt = $pdo->prepare("DELETE FROM blog WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Blog deleted successfully!'); 
    window.location.href = 'view_blog.php';</script>";
    exit();
}
// Fetch existing title for the form
$blog_title_stmt = $pdo->prepare("SELECT * FROM blog_title WHERE id = 1");
$blog_title_stmt->execute();
$result = $blog_title_stmt->fetch(PDO::FETCH_ASSOC);
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
                                <h4 class="mb-sm-0 font-size-18">View Blogs</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>View Bolgs</li>
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
                                <form method="POST" action="" class="custom-validation">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="section_heading">Section Heading
                                                (required)</label>
                                            <input type="text" name="section_heading" id="section_heading"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($result['section_heading']); ?>"
                                                minlength="1" maxlength="50" required placeholder="Minimum 1 character">
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label" for="title">Tagline (required)</label>
                                            <input type="text" name="title" id="title" class="form-control"
                                                value="<?php echo htmlspecialchars($result['title']); ?>" minlength="2"
                                                maxlength="50" required placeholder="Minimum 2 characters">
                                        </div>

                                    </div>
                                    <div class="m-3 text-center">
                                        <button type="submit"
                                            class="btn btn-primary waves-effect waves-light">Update</button>
                                    </div>
                                </form>
                                <br>
                                <hr>

                                <!-- Add Blog Button -->
                                <div class="m-3 text-center">
                                    <a href="add_blog.php" class="btn btn-primary mt-4">Add a New Blog</a>
                                </div>

                                <!-- Blogs Table -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Image</th>
                                                    <th>Blog Title</th>
                                                    <th>URL Slug</th>
                                                    <th>Image Name</th>
                                                    <th>Image Alt</th>
                                                    <th>Image Title</th>
                                                    <th>Blog Date</th>
                                                    <th>Updated On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($blogs as $blog) {
                                                    $title_preview = strlen($blog['title']) > 20 ? substr($blog['title'], 0, 20) . '...' : $blog['title'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><img src="../assets/images/blog/<?php echo $blog['image_name']; ?>"
                                                                alt="Blog Image" width="50" height="50"></td>
                                                        <td><?php echo htmlspecialchars($title_preview); ?></td>
                                                        <td><?php echo htmlspecialchars($blog['url_slug']); ?></td>
                                                        <td><?php echo htmlspecialchars($blog['image_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($blog['image_alt']); ?></td>
                                                        <td><?php echo htmlspecialchars($blog['image_title']); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($blog['date'])); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($blog['updated_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <a href="edit_blog.php?id=<?php echo $blog['id']; ?>"
                                                                class="btn btn-primary" data-bs-toggle="tooltip"
                                                                title="Edit">
                                                                <i class="bx bxs-pencil"></i>
                                                            </a>
                                                            <form method="post" class="d-inline"
                                                                onsubmit="return confirmDelete();">
                                                                <input type="hidden" name="delete_id"
                                                                    value="<?php echo $blog['id']; ?>">
                                                                <button type="submit" class="btn btn-danger"
                                                                    data-bs-toggle="tooltip" title="Delete">
                                                                    <i class="bx bx-trash"></i> <!-- Trash Icon -->
                                                                </button>
                                                            </form>

                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $sl_no++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>



                <!--  -->

            </div>
        </div>
        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this item?');
            }
        </script>

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