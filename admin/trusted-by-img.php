<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Database connection
include 'shared_components/db.php';

// Fetch all logos from the corrected table 'partner_logos'
$stmt = $pdo->prepare("SELECT * FROM partner_logos ORDER BY display_order ASC, created_at DESC");
$stmt->execute();
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch logo details using the correct column name 'image_filename'
    $stmt = $pdo->prepare("SELECT image_filename FROM partner_logos WHERE id = ?");
    $stmt->execute([$delete_id]);
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($logo && $logo['image_filename']) {
        // Using a consistent path for uploads
        $image_path = "assets/images/partners/" . $logo['image_filename'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image from the server
        }
    }

    // Delete the record from the 'partner_logos' table
    $stmt = $pdo->prepare("DELETE FROM partner_logos WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Partner Logo deleted successfully!'); 
    window.location.href = 'trusted-by-img.php';</script>";
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">
                                    Our Clients
                                </h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li><a href="index.php">Dashboard</a> &nbsp; >&nbsp; &nbsp;</li>
                                        <li>Our Clients</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="m-3 text-center">
                                    <a href="add-trusted-by-img.php" class="btn btn-primary mt-4">Add a New Logo</a>
                                </div>

                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Logo</th>
                                                    <th>Filename</th>
                                                    <th>Alt Text</th>
                                                    <th>Display Order</th>
                                                    <th>Added On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($logos as $logo) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td>
                                                            <img src="assets/images/partners/<?php echo htmlspecialchars($logo['image_filename']); ?>"
                                                                alt="<?php echo htmlspecialchars($logo['image_alt']); ?>"
                                                                title="<?php echo htmlspecialchars($logo['image_alt']); ?>"
                                                                width="100" height="50" class="img-thumbnail">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($logo['image_filename']); ?></td>
                                                        <td><?php echo htmlspecialchars($logo['image_alt']); ?></td>
                                                        <td><?php echo htmlspecialchars($logo['display_order']); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($logo['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="button-container">
                                                                <a href="edit-trusted-by-img.php?id=<?php echo $logo['id']; ?>"
                                                                    class="btn btn-primary" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top" title="Edit">
                                                                    <i class="bx bxs-pencil"></i> </a>
                                                                <form method="POST" class="d-inline"
                                                                    onsubmit="return confirmDelete();">
                                                                    <input type="hidden" name="delete_id"
                                                                        value="<?php echo $logo['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="Delete">
                                                                        <i class="bx bx-trash"></i> </button>
                                                                </form>
                                                            </div>
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
                    </div>
                </div>
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
</body>

</html>