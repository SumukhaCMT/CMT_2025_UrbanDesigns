<?php
session_start();

// Redirect to login if no session exists
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Optional: Restrict access based on role

//get the role linke admin or super-admin from session
$role = $_SESSION['admin'];
$restricted_pages = [
    'admin' => ['users.php', 'contact-enquiries.php', 'test-policies.php', 'logic_file.php', 'leads.php', 'admins.php'],
    'super-admin' => [],
    'logic-admin' => ['admins.php', 'users.php', 'leads.php']
];

$current_page = basename($_SERVER['PHP_SELF']);
if (isset($restricted_pages[$role]) && in_array($current_page, $restricted_pages[$role])) {
    die("Access denied: You do not have permission to access this page.");
}

// if ($_SESSION['admin'] !== 'super-admin') {
//     die("Access denied: Super admin access required.");

// }
