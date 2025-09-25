<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';

// Fetch all users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    // Fetch the user's email to use for related deletions
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$delete_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_email = $user['email'];

        // Delete rows from `contact_enquiry` if they exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_enquiry WHERE email = ?");
        $stmt->execute([$user_email]);
        if ($stmt->fetchColumn() > 0) {
            $stmt = $pdo->prepare("DELETE FROM contact_enquiry WHERE email = ?");
            $stmt->execute([$user_email]);
        }

        // Delete rows from `policy_enquiry` if they exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM policy_enquiry WHERE email = ?");
        $stmt->execute([$user_email]);
        if ($stmt->fetchColumn() > 0) {
            $stmt = $pdo->prepare("DELETE FROM policy_enquiry WHERE email = ?");
            $stmt->execute([$user_email]);
        }

        // Finally, delete the user from the `users` table
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_user_id]);

        echo "<script>alert('User and related records deleted successfully!'); 
        window.location.href = 'users.php';</script>";
    } else {
        echo "<script>alert('User not found.'); 
        window.location.href = 'users.php';</script>";
    }
}

// Handle Excel Export
if (isset($_POST['export_excel'])) {
    require '../vendor/autoload.php';
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Sl. No');
    $sheet->setCellValue('B1', 'Name');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Phone Number');
    $sheet->setCellValue('E1', 'DOB');
    $sheet->setCellValue('F1', 'Gender');
    $sheet->setCellValue('G1', 'Created At');

    $row = 2;
    foreach ($users as $key => $user) {
        $sheet->setCellValue('A' . $row, $key + 1);
        $sheet->setCellValue('B' . $row, $user['name'] ?: '-');
        $sheet->setCellValue('C' . $row, $user['email'] ?: '-');
        $sheet->setCellValue('D' . $row, $user['phone_number'] ?: '-');
        $sheet->setCellValue('E' . $row, $user['dob'] ?: '-');
        $sheet->setCellValue('F' . $row, $user['gender'] ?: '-');
        $sheet->setCellValue('G' . $row, $user['created_at']);
        $row++;
    }

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'users-rna-' . date('d-M-Y') . '.xlsx';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    $writer->save('php://output');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <!-- table styles -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />

    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />
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
                                <h4 class="mb-sm-0 font-size-18">Users</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Users</li>
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

                                <!-- Form for Export at the Top -->
                                <div class="m-3 text-center">
                                    <form method="POST">
                                        <button type="submit" name="export_excel" class="btn btn-success mb-3">Export to
                                            Excel</button>
                                    </form>
                                </div>

                                <!-- Users Table -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <!-- <table class="table table-striped"> -->
                                        <table id="datatable" class="table table-bordered table-striped ">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Sl. No</th>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Phone Number</th>
                                                    <th scope="col">DOB</th>
                                                    <th scope="col">Gender</th>
                                                    <th scope="col">Created At</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($users as $user) {
                                                    ?>
                                                    <tr class="text-center">
                                                        <td><?php echo $sl_no++; ?></td>
                                                        <td><?php echo $user['name'] ?: '-'; ?></td>
                                                        <td><a
                                                                href="mailto:<?php echo $user['email'] ?: '-'; ?>"><?php echo $user['email'] ?: '-'; ?></a>
                                                        </td>
                                                        <td><a
                                                                href="tel:<?php echo $user['phone_number'] ?: '-'; ?>"><?php echo $user['phone_number'] ?: '-'; ?></a>
                                                        </td>
                                                        <td><?php echo $user['dob'] ?: '-'; ?></td>
                                                        <td><?php echo $user['gender'] ?: '-'; ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($user['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <a href="edit-users.php?id=<?php echo $user['id']; ?>"
                                                                class="btn btn-primary" data-bs-toggle="tooltip"
                                                                title="Edit">
                                                                <i class="bx bxs-pencil"></i>
                                                            </a>
                                                            <!-- Action Buttons (Edit/Delete Icons with Tooltip) -->
                                                            <div class="button-container">
                                                                <form method="POST" class="d-inline"
                                                                    onsubmit="return confirmDelete();">
                                                                    <input type="hidden" name="delete_user_id"
                                                                        value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="Delete">
                                                                        <i class="bx bx-trash"></i> <!-- Trash Icon -->
                                                                    </button>
                                                                </form>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
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
    <?php require './shared_components/scripts.php';
    require './shared_components/datatable_scripts.php';
    ?>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
</body>

</html>