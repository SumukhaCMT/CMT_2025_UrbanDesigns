<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM case_studies";
$params = [];

if (!empty($search_term)) {
    $sql .= " WHERE title LIKE ? OR category LIKE ? OR slug LIKE ?";
    $params = array_fill(0, 3, '%' . $search_term . '%');
}

$sql .= " ORDER BY sort_order ASC, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$case_studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("SELECT list_image, detail_image FROM case_studies WHERE id = ?");
    $stmt->execute([$delete_id]);
    $images = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($images) {
        if (file_exists("../assets/img/gallery/" . $images['list_image']))
            unlink("../assets/img/gallery/" . $images['list_image']);
        if (file_exists("../assets/img/blog/" . $images['detail_image']))
            unlink("../assets/img/blog/" . $images['detail_image']);
    }

    $stmt = $pdo->prepare("DELETE FROM case_studies WHERE id = ?");
    $stmt->execute([$delete_id]);
    echo "<script>alert('Case study deleted.'); window.location.href='view-case-studies.php';</script>";
    exit();
}

// Handle Status Toggle
if (isset($_POST['toggle_status_id'])) {
    $id = $_POST['toggle_status_id'];
    $status = $_POST['current_status'] === 'active' ? 'inactive' : 'active';
    $stmt = $pdo->prepare("UPDATE case_studies SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo "<script>alert('Status updated.'); window.location.href='view-case-studies.php';</script>";
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
        <?php require './shared_components/header.php'; ?>
        <?php require './shared_components/navbar.php'; ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Manage Case Studies</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-4">
                                        <a href="add-case-study.php" class="btn btn-primary"><i class="bx bx-plus"></i>
                                            Add New Case Study</a>
                                        <form method="GET" class="d-flex">
                                            <input type="text" name="search" class="form-control me-2"
                                                placeholder="Search..."
                                                value="<?php echo htmlspecialchars($search_term); ?>">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                        </form>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Image</th>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($case_studies as $index => $study): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><img src="../assets/img/gallery/<?php echo htmlspecialchars($study['list_image']); ?>"
                                                                width="100" alt="List Image"></td>
                                                        <td><?php echo htmlspecialchars($study['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($study['category']); ?></td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="toggle_status_id"
                                                                    value="<?php echo $study['id']; ?>">
                                                                <input type="hidden" name="current_status"
                                                                    value="<?php echo $study['status']; ?>">
                                                                <button type="submit"
                                                                    class="btn btn-sm <?php echo $study['status'] === 'active' ? 'btn-success' : 'btn-secondary'; ?>">
                                                                    <?php echo ucfirst($study['status']); ?>
                                                                </button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <a href="edit-case-study.php?id=<?php echo $study['id']; ?>"
                                                                class="btn btn-sm btn-primary"><i
                                                                    class="bx bxs-pencil"></i></a>
                                                            <form method="POST" class="d-inline"
                                                                onsubmit="return confirm('Delete this case study?');">
                                                                <input type="hidden" name="delete_id"
                                                                    value="<?php echo $study['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger"><i
                                                                        class="bx bx-trash"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
</body>

</html>