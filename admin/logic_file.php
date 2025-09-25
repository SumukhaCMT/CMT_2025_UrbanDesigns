<?php
require './shared_components/session.php';
require './shared_components/error.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared_components/head.php'; ?>
    <!-- Plugins css -->
    <link href="assets/libs/dropzone/min/dropzone.min.css" rel="stylesheet" type="text/css" />
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
                                <h4 class="mb-sm-0 font-size-18">View / Edit Logic File</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li>

                                            <a href="index.php">Dashboard</a>
                                            &nbsp; >&nbsp; &nbsp;
                                        </li>
                                        <li>View / Edit Logic File</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- Breadcrumb end -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <?php

                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    // Define the target directory and default filename for uploads
                                    $targetDir = "../assets/files/";
                                    $targetFilePath = $targetDir . "rna.xlsx";

                                    // Check if a file was uploaded
                                    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                                        $fileTmpPath = $_FILES['file']['tmp_name'];
                                        $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                                        // Allowed file type
                                        $allowedExtension = 'xlsx';

                                        // Check the file extension
                                        if ($fileExtension === $allowedExtension) {
                                            // Check if the file already exists
                                            if (file_exists($targetFilePath)) {
                                                // Attempt to delete the existing file
                                                if (!unlink($targetFilePath)) {
                                                    echo "<div class='alert alert-danger' role='alert'>
                    Error deleting the existing file. Please try again.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                                                    exit;
                                                }
                                            }

                                            // Attempt to move the new file to the target directory
                                            if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                                                echo "<div class='alert text-center text-center alert-success' role='alert'>
                File uploaded successfully as rna.xlsx!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
                                            } else {
                                                echo "<div class='alert text-center  alert-danger' role='alert'>
                Error moving the file. Please try again.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
                                            }
                                        } else {
                                            echo "<div class='alert text-center alert-danger' role='alert'>
                Invalid file type. Only .xlsx files are allowed.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
                                        }
                                    } else {
                                        echo "<div class='alert text-center alert-danger' role='alert'>
                No file uploaded or there was an error during upload.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
                                    }
                                }
                                ?>
                                <div class="input-group border p-3 rounded">
                                    <form action="" method="POST" enctype="multipart/form-data" id="uploadForm" class="d-flex flex-column flex-sm-row w-100">
                                        <input name="file" type="file" accept=".xlsx" required class="form-control w-100 w-sm-25 mb-2 mb-sm-0" />
                                        <button type="button" class="btn btn-primary waves-effect waves-light w-100 w-sm-auto mx-auto" style="max-width: 200px;" onclick="document.getElementById('uploadForm').submit();">Replace Logic File</button>
                                    </form>
                                </div>



                            </div>

                            <!-- Download Button -->
                            <div class='d-flex justify-content-around align-items-center'>
                                <form action="./components/download.php" method="POST" class="m-3 text-center">
                                    <button type="submit" class="btn btn-success">Download Current Excel File</button>
                                </form>
                                <form action="./components/download_reference_file.php" method="POST" class="m-3 text-center">
                                    <button type="submit" class="btn btn-success">Download Reference Excel File</button>
                                </form>
                            </div>



                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div>
        </div>
        <!--  -->




        <?php
        require './shared_components/footer.php';
        ?>
    </div>
    <?php require './shared_components/scripts.php'; ?>


</body>

</html>