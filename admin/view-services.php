<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Get search term from GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the search query
$sql = "SELECT * FROM service";
$search_params = [];

// Add search conditions if a search term is provided
if (!empty($search_term)) {
    $sql .= " WHERE (
        service_title LIKE ? OR 
        service_description LIKE ? OR 
        redirection_link LIKE ? OR 
        status LIKE ? OR
        DATE_FORMAT(created_at, '%d-%b-%Y') LIKE ?
    )";
    $search_like = '%' . $search_term . '%';
    $search_params = array_fill(0, 5, $search_like);
}

$sql .= " ORDER BY sort_order ASC, created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($search_params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_services = count($services);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $pdo->beginTransaction();

        // Fetch detail image from service_details to delete the file
        $stmt_img = $pdo->prepare("SELECT detail_image FROM service_details WHERE service_id = ?");
        $stmt_img->execute([$delete_id]);
        $detail = $stmt_img->fetch(PDO::FETCH_ASSOC);

        if ($detail && !empty($detail['detail_image'])) {
            $image_path = "../assets/img/service/" . $detail['detail_image']; // Adjusted path for detail image
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Deleting the service record will also delete the service_details record due to ON DELETE CASCADE
        $stmt_delete = $pdo->prepare("DELETE FROM service WHERE id = ?");
        $stmt_delete->execute([$delete_id]);

        $pdo->commit();
        echo "<script>alert('Service and its details deleted successfully!'); window.location.href = 'view-services';</script>";
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting service: " . $e->getMessage() . "');</script>";
    }
}

// Handle status toggle
if (isset($_POST['toggle_status_id'])) {
    $service_id = $_POST['toggle_status_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';

    $stmt_toggle = $pdo->prepare("UPDATE service SET status = ? WHERE id = ?");
    $stmt_toggle->execute([$new_status, $service_id]);

    echo "<script>alert('Service status updated!'); window.location.href = 'view-services';</script>";
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
                                <h4 class="mb-sm-0 font-size-18">Manage Services</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Manage Services</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <form method="GET" action="" class="d-flex">
                                                <div class="input-group">
                                                    <input type="text" name="search" class="form-control"
                                                        placeholder="Search by title, URL, status..."
                                                        value="<?php echo htmlspecialchars($search_term); ?>">
                                                    <button class="btn btn-primary" type="submit"><i
                                                            class="bx bx-search"></i> Search</button>
                                                    <?php if (!empty($search_term)): ?>
                                                        <a href="view-services" class="btn btn-secondary"><i
                                                                class="bx bx-x"></i> Clear</a>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <a href="add-service.php" class="btn btn-primary"><i class="bx bx-plus"></i>
                                                Add New Service</a>
                                        </div>
                                    </div>

                                    <h5 class="card-title mb-3">All Services <span
                                            class="badge bg-success"><?php echo $total_services; ?></span></h5>

                                    <?php if ($total_services > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Sl. No</th>
                                                        <th>Icon</th>
                                                        <th>Service Title</th>
                                                        <th>URL Slug</th>
                                                        <th>Status</th>
                                                        <th>Created On</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($services as $index => $service): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><img src="assets/images/service_icons/<?php echo htmlspecialchars($service['img_name'] ?? ''); ?>"
                                                                    alt="icon" width="40"></td>
                                                            <td><strong><?php echo htmlspecialchars($service['service_title']); ?></strong>
                                                            </td>
                                                            <td>/<?php echo htmlspecialchars($service['redirection_link']); ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" class="d-inline"
                                                                    onsubmit="return confirm('Toggle status?');">
                                                                    <input type="hidden" name="toggle_status_id"
                                                                        value="<?php echo $service['id']; ?>">
                                                                    <input type="hidden" name="current_status"
                                                                        value="<?php echo $service['status']; ?>">
                                                                    <button type="submit"
                                                                        class="btn btn-sm <?php echo $service['status'] === 'active' ? 'btn-success' : 'btn-secondary'; ?>">
                                                                        <?php echo ucfirst($service['status']); ?>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                            <td><?php echo date('d-M-Y', strtotime($service['created_at'])); ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="edit-service.php?id=<?php echo $service['id']; ?>"
                                                                        class="btn btn-sm btn-primary" title="Edit"><i
                                                                            class="bx bxs-pencil"></i></a>
                                                                    <form method="POST" class="d-inline"
                                                                        onsubmit="return confirm('Are you sure you want to delete this service?');">
                                                                        <input type="hidden" name="delete_id"
                                                                            value="<?php echo $service['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                            title="Delete"><i class="bx bx-trash"></i></button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <h5>No services found.</h5>
                                            <a href="add-service.php" class="btn btn-primary mt-2"><i
                                                    class="bx bx-plus"></i> Add Your First Service</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require './shared_components/footer.php'; ?>
        </div>
        <?php require './shared_components/scripts.php'; ?>
</body>

</html>