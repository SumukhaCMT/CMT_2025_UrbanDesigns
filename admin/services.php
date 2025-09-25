<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

// Fetch all services
$stmt = $pdo->prepare("SELECT * FROM service ORDER BY created_at DESC");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the service from the database
    $stmt = $pdo->prepare("DELETE FROM service WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Service deleted successfully!'); 
    window.location.href = 'services';</script>";
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
                                    Services
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>
                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>

                                        <li>
                                            Services
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="m-3 text-center">
                                    <a href="add-services" class="btn btn-primary mb-4">Add Services</a>
                                </div>


                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table id="services-table" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Image</th>
                                                    <th>Service Title</th>
                                                    <th>Description</th>
                                                    <th>Redirection Link</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($services as $service): ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td>

                                                            <img src="../assets/images/rna-icons/<?php echo htmlspecialchars($service['img_name']); ?>"
                                                                alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                                                width="30" height="30">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($service['service_title']); ?></td>
                                                        <td><?php echo htmlspecialchars($service['service_description'] ?? '-'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($service['redirection_link']); ?>
                                                        </td>

                                                        <td>
                                                            <div class="button-container">
                                                                <a href="edit-services?id=<?php echo $service['id']; ?>"
                                                                    class="btn btn-primary" data-bs-toggle="tooltip"
                                                                    title="Edit">
                                                                    <i class="bx bxs-pencil"></i>
                                                                </a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="delete_id"
                                                                        value="<?php echo $service['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger"
                                                                        data-bs-toggle="tooltip" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $sl_no++;
                                                endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div>
            </div>
        </div>
        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
</body>

</html>