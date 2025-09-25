<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];

function handleImageUpload($file, $uploadDir, $baseName)
{
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed))
        return null;
    $finalName = $baseName . '-' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $finalName)) {
        return $finalName;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $category = trim($_POST['category']);
    $content_heading = trim($_POST['content_heading']);
    $content = $_POST['content'];

    // Validation
    if (empty($title) || empty($slug) || empty($category))
        $errors[] = "Title, Slug, and Category are required.";
    if (!isset($_FILES['list_image']) || $_FILES['list_image']['error'] != UPLOAD_ERR_OK)
        $errors[] = "List Image is required.";
    if (!isset($_FILES['detail_image']) || $_FILES['detail_image']['error'] != UPLOAD_ERR_OK)
        $errors[] = "Detail Image is required.";

    $list_image_name = null;
    $detail_image_name = null;

    if (empty($errors)) {
        $slug_for_file = preg_replace('/[^a-z0-9\-]/i', '', str_replace(' ', '-', $slug));
        $list_image_name = handleImageUpload($_FILES['list_image'], '../assets/img/gallery/', 'cs-list-' . $slug_for_file);
        $detail_image_name = handleImageUpload($_FILES['detail_image'], '../assets/img/blog/', 'cs-detail-' . $slug_for_file);

        if (!$list_image_name || !$detail_image_name)
            $errors[] = "Image upload failed. Check permissions and file types.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO case_studies (title, slug, category, list_image, detail_image, content_heading, content) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $category, $list_image_name, $detail_image_name, $content_heading, $content]);
        echo "<script>alert('Case study added!'); window.location.href='view-case-studies.php';</script>";
        exit();
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
        <?php require './shared_components/header.php'; ?>
        <?php require './shared_components/navbar.php'; ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Add Case Study</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error)
                                                echo "<p>$error</p>"; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3"><label class="form-label">Title *</label><input type="text"
                                                name="title" class="form-control" required></div>
                                        <div class="mb-3"><label class="form-label">Slug *</label><input type="text"
                                                name="slug" class="form-control" required></div>
                                        <div class="mb-3"><label class="form-label">Category *</label><input type="text"
                                                name="category" class="form-control" required></div>
                                        <div class="mb-3"><label class="form-label">Content Heading</label><input
                                                type="text" name="content_heading" class="form-control"></div>
                                        <div class="mb-3"><label class="form-label">List Image (for listing page)
                                                *</label><input type="file" name="list_image" class="form-control"
                                                required></div>
                                        <div class="mb-3"><label class="form-label">Detail Image (for detail page)
                                                *</label><input type="file" name="detail_image" class="form-control"
                                                required></div>
                                        <div class="mb-3"><label class="form-label">Full Content</label><textarea
                                                id="content" name="content"></textarea></div>
                                        <button type="submit" class="btn btn-primary">Add Case Study</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
    </div>
    <?php require './shared_components/scripts.php'; ?>
    <script>CKEDITOR.replace('content');</script>
</body>

</html>