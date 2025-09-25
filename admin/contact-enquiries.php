<?php
require './shared_components/session.php';
require './shared_components/error.php';
include 'shared_components/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch all contact enquiries
$stmt = $pdo->prepare("SELECT * FROM contact_enquiry ORDER BY created_at DESC");
$stmt->execute();
$contact_enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM contact_enquiry WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Contact enquiry deleted successfully!'); 
    window.location.href = 'contact-enquiries.php';</script>";
    exit();
}

// Handle export to Excel
if (isset($_POST['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Excel header
    $sheet->setCellValue('A1', 'Sl. No')
        ->setCellValue('B1', 'Name')
        ->setCellValue('C1', 'Email')
        ->setCellValue('D1', 'Phone Number')
        ->setCellValue('E1', 'Subject')
        ->setCellValue('F1', 'Message')
        ->setCellValue('G1', 'Created At');

    // Reverse the array to ensure last inserted data is on top
    $contact_enquiries = array_reverse($contact_enquiries);

    // Populate data starting from row 2
    $row = 2;
    $sl_no = 1;
    foreach ($contact_enquiries as $enquiry) {
        $sheet->setCellValue('A' . $row, $sl_no++) // Serial number
            ->setCellValue('B' . $row, $enquiry['name'])
            ->setCellValue('C' . $row, $enquiry['email'])
            ->setCellValue('D' . $row, $enquiry['phone_number'])
            ->setCellValue('E' . $row, $enquiry['subject'])
            ->setCellValue('F' . $row, $enquiry['message'])
            ->setCellValue('G' . $row, $enquiry['created_at']);
        $row++;
    }

    // Generate the filename with today's date in dd-MMM-yy format
    $today = date('d-M-Y');
    $filename = 'contact-enquiries-' . $today . '.xlsx';

    // Set HTTP headers for Excel download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
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
                                <h4 class="mb-sm-0 font-size-18">Contact Enquiries</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Contact Enquiries</li>
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
                                        <button type="submit" name="export_excel" class="btn btn-success mb-4">Download
                                            in Excel</button>
                                    </form>
                                </div>
                                <!-- Contact Enquiries Table -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <!-- <table class="table table-striped"> -->
                                        <table id="datatable" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone Number</th>
                                                    <th>Subject</th>
                                                    <th>Message</th>
                                                    <th>Contacted On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($contact_enquiries as $enquiry) {
                                                    $message_preview = strlen($enquiry['message']) > 50
                                                        ? substr($enquiry['message'], 0, 50) . '...'
                                                        : $enquiry['message'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sl_no; ?></td>
                                                        <td><?php echo htmlspecialchars($enquiry['name']); ?></td>
                                                        <td><a
                                                                href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>">
                                                                <?php echo htmlspecialchars($enquiry['email']); ?>
                                                            </a>
                                                        </td>
                                                        <td><a
                                                                href="tel:<?php echo htmlspecialchars($enquiry['phone_number']); ?>">
                                                                <?php echo htmlspecialchars($enquiry['phone_number']); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($enquiry['subject']); ?></td>
                                                        <td><?php echo htmlspecialchars($message_preview); ?></td>
                                                        <td><?php echo date('d-M-Y', strtotime($enquiry['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <a href="edit-contact-enquiries.php?id=<?php echo $enquiry['id']; ?>"
                                                                class="btn btn-primary" data-bs-toggle="tooltip"
                                                                title="Edit">
                                                                <i class="bx bxs-pencil"></i>
                                                            </a>
                                                            <form method="post" class="d-inline"
                                                                onsubmit="return confirmDelete();">
                                                                <input type="hidden" name="delete_id"
                                                                    value="<?php echo $enquiry['id']; ?>">
                                                                <button type="submit" class="btn btn-danger "
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