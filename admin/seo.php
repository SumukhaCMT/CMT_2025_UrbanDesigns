<?php
require './shared_components/session.php';
require './shared_components/error.php';

// Database connection
include 'shared_components/db.php';

// Fetch all SEO entries
$stmt = $pdo->prepare("SELECT * FROM seo ORDER BY created_at DESC");
$stmt->execute();
$seo_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the SEO entry from the database
    $stmt = $pdo->prepare("DELETE FROM seo WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('SEO entry deleted successfully!'); 
    window.location.href = 'seo.php';</script>";
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
                                    SEO
                                </h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>
                                            SEO
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
                                <!-- Add New SEO Button -->
                                <div class="m-3 text-center">
                                    <a href="add-seo.php" class="btn btn-primary">Add New SEO Entry</a>
                                </div>

                                <!-- Table with data -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Page Name</th>
                                                    <th>Meta Title</th>
                                                    <th>Meta Keywords</th>
                                                    <th>Meta Description</th>
                                                    <!-- <th>Canonical Link</th> -->
                                                    <th>Created On</th>
                                                    <th>Updated On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($seo_entries as $entry) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><?php echo htmlspecialchars($entry['page_name'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['meta_title'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['meta_keywords'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['meta_description'] ?? '-'); ?></td>
                                                        <!-- <td><?php echo htmlspecialchars($entry['meta_canonical_link'] ?? '-'); ?></td> -->
                                                        <td><?php echo date('d-M-Y', strtotime($entry['created_at'])); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($entry['updated_at'])); ?></td>
                                                        <td>
                                                            <!-- Action Buttons -->
                                                            <div class="button-container">
                                                                <a href="edit-seo.php?id=<?php echo $entry['id']; ?>"
                                                                    class="btn btn-primary"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Edit">
                                                                    <i class="bx bxs-pencil"></i> <!-- Edit Icon -->
                                                                </a>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="delete_id" value="<?php echo $entry['id']; ?>">
                                                                    <button type="submit"
                                                                        class="btn btn-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        title="Delete">
                                                                        <i class="bx bx-trash"></i> <!-- Delete Icon -->
                                                                    </button>
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



                <!--  -->

            </div>
        </div>


        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>
</body>

</html>