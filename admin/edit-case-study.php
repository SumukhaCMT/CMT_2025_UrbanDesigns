<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$errors = [];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: view-case-studies.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM case_studies WHERE id = ?");
$stmt->execute([$id]);
$study = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$study) {
    echo "<script>alert('Case study not found!'); window.location.href='view-case-studies.php';</script>";
    exit();
}

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

    $list_image_name = $study['list_image'];
    $detail_image_name = $study['detail_image'];

    $slug_for_file = preg_replace('/[^a-z0-9\-]/i', '', str_replace(' ', '-', $slug));

    if (isset($_FILES['list_image']) && $_FILES['list_image']['error'] == UPLOAD_ERR_OK) {
        if (file_exists('../assets/img/gallery/' . $list_image_name))
            unlink('../assets/img/gallery/' . $list_image_name);
        $list_image_name = handleImageUpload($_FILES['list_image'], '../assets/img/gallery/', 'cs-list-' . $slug_for_file);
    }

    if (isset($_FILES['detail_image']) && $_FILES['detail_image']['error'] == UPLOAD_ERR_OK) {
        if (file_exists('../assets/img/blog/' . $detail_image_name))
            unlink('../assets/img/blog/' . $detail_image_name);
        $detail_image_name = handleImageUpload($_FILES['detail_image'], '../assets/img/blog/', 'cs-detail-' . $slug_for_file);
    }

    $stmt = $pdo->prepare("UPDATE case_studies SET title = ?, slug = ?, category = ?, list_image = ?, detail_image = ?, content_heading = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $slug, $category, $list_image_name, $detail_image_name, $content_heading, $content, $id]);
    echo "<script>alert('Case study updated!'); window.location.href='view-case-studies.php';</script>";
    exit();
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
                                <h4 class="mb-sm-0 font-size-18">Edit Case Study</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3"><label class="form-label">Title</label><input type="text"
                                                name="title" class="form-control"
                                                value="<?php echo htmlspecialchars($study['title']); ?>"></div>
                                        <div class="mb-3"><label class="form-label">Slug</label><input type="text"
                                                name="slug" class="form-control"
                                                value="<?php echo htmlspecialchars($study['slug']); ?>"></div>
                                        <div class="mb-3"><label class="form-label">Category</label><input type="text"
                                                name="category" class="form-control"
                                                value="<?php echo htmlspecialchars($study['category']); ?>"></div>
                                        <div class="mb-3"><label class="form-label">Content Heading</label><input
                                                type="text" name="content_heading" class="form-control"
                                                value="<?php echo htmlspecialchars($study['content_heading']); ?>">
                                        </div>
                                        <div class="mb-3"><label class="form-label">Current List Image</label>
                                            <div><img
                                                    src="../assets/img/gallery/<?php echo htmlspecialchars($study['list_image']); ?>"
                                                    width="150"></div>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Upload New List Image</label><input
                                                type="file" name="list_image" class="form-control"></div>
                                        <div class="mb-3"><label class="form-label">Current Detail Image</label>
                                            <div><img
                                                    src="../assets/img/blog/<?php echo htmlspecialchars($study['detail_image']); ?>"
                                                    width="200"></div>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Upload New Detail
                                                Image</label><input type="file" name="detail_image"
                                                class="form-control"></div>
                                        <div class="mb-3"><label class="form-label">Full Content</label><textarea
                                                id="content"
                                                name="content"><?php echo htmlspecialchars($study['content']); ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Case Study</button>
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