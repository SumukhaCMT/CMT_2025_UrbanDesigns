<?php

$file = '../../assets/files/rna.xlsx';

// Check if the file exists
if (file_exists($file)) {
    // Set headers to initiate download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    // Clear output buffer
    ob_clean();
    flush();


    readfile($file);
    exit;
} else {
    echo "File not found.";
}
