<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>
<?php
// Include database connection
include 'shared_components/db.php';

// Include PhpSpreadsheet
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch lead form data
$stmt = $pdo->prepare("SELECT * FROM lead_form ORDER BY created_at DESC");
$stmt->execute();
$lead_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the row from lead_form
    $stmt = $pdo->prepare("DELETE FROM lead_form WHERE id = ?");
    $stmt->execute([$delete_id]);

    echo "<script>alert('Lead deleted successfully!'); 
    window.location.href = 'leads.php';</script>";
    exit();
}

// Handle export to Excel
if (isset($_POST['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Excel header
    $sheet->setCellValue('A1', 'Sl. No')
        ->setCellValue('B1', 'Insurance For')
        ->setCellValue('C1', 'Age')
        ->setCellValue('D1', 'Pre-existing Disease')
        ->setCellValue('E1', 'Disease Details')
        ->setCellValue('F1', 'Name')
        ->setCellValue('G1', 'Phone Number')
        ->setCellValue('H1', 'Email')
        ->setCellValue('I1', 'Created At');

    // Reverse the array to ensure last inserted data is on top
    $lead_forms = array_reverse($lead_forms);

    // Populate data starting from row 2
    $row = 2;
    $sl_no = 1;
    foreach ($lead_forms as $form) {
        $sheet->setCellValue('A' . $row, $sl_no++) // Serial number
            ->setCellValue('B' . $row, $form['insurance_for'])
            ->setCellValue('C' . $row, $form['age'])
            ->setCellValue('D' . $row, $form['pre_disease'])
            ->setCellValue('E' . $row, $form['pre_disease_detail'])
            ->setCellValue('F' . $row, $form['name'])
            ->setCellValue('G' . $row, $form['phone_number'])
            ->setCellValue('H' . $row, $form['email'])
            ->setCellValue('I' . $row, $form['created_at']);
        $row++;
    }

    // Generate the filename with today's date in dd-MMM-yy format
    $today = date('d-M-Y');
    $filename = 'lead-rna-' . $today . '.xlsx';

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
                                <h4 class="mb-sm-0 font-size-18">Leads</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>Leads</li>
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

                                <!-- Lead Forms Table -->
                                <div class="table-rep-plugin">
                                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                                        <!-- <table class="table table-striped" id="lead-forms-table"> -->

                                        <table id="datatable" class="table table-bordered table-striped ">
                                            <thead>
                                                <tr>
                                                    <th>Sl. No</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone Number</th>
                                                    <th>Insurance For</th>
                                                    <th>Age</th>
                                                    <th>Pre-existing Disease</th>
                                                    <th>Disease Details</th>
                                                    <th>Created At</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sl_no = 1;
                                                foreach ($lead_forms as $form) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $sl_no++; ?></td>
                                                        <td><?php echo htmlspecialchars($form['name']); ?></td>
                                                        <td><a
                                                                href="mailto:<?php echo htmlspecialchars($form['email']); ?>"><?php echo htmlspecialchars($form['email']); ?></a>
                                                        </td>
                                                        <td><a
                                                                href="tel:<?php echo htmlspecialchars($form['phone_number']); ?>"><?php echo htmlspecialchars($form['phone_number']); ?></a>
                                                        </td>

                                                        <td><?php echo htmlspecialchars($form['insurance_for']); ?></td>
                                                        <td><?php echo htmlspecialchars($form['age']); ?></td>
                                                        <td><?php echo htmlspecialchars($form['pre_disease']); ?></td>
                                                        <td><?php echo htmlspecialchars($form['pre_disease_detail'] ?? 'N/A'); ?>
                                                        </td>

                                                        <td><?php echo date('d-M-Y', strtotime($form['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="button-container">
                                                                <a href="edit-leads.php?id=<?php echo $form['id']; ?>"
                                                                    class="btn btn-primary" data-bs-toggle="tooltip"
                                                                    title="Edit">
                                                                    <i class="bx bxs-pencil"></i>
                                                                </a>
                                                                <!-- Delete Button with Icon and Tooltip -->
                                                                <form method="POST" class="d-inline"
                                                                    onsubmit="return confirmDelete();">
                                                                    <input type="hidden" name="delete_id"
                                                                        value="<?php echo $form['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger "
                                                                        data-bs-toggle="tooltip" title="Delete">
                                                                        <i class="bx bx-trash"></i>
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
        <script>
            function confirmDelete() {
                return confirm('Are you sure you want to delete this item?');
            }
        </script>

        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php';
    require './shared_components/datatable_scripts.php';
    ?>
</body>

</html>